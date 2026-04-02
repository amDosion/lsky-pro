<?php

namespace Tests\Feature\Intelligence;

use App\Models\Image;
use App\Services\ImageIntelligence\ImageIntelligenceService;
use App\Services\ImageIntelligence\LocalImageIntelligenceAnalyzer;
use App\Services\ImageIntelligence\LocalImageIntelligenceProcessRunner;
use App\Services\ImageIntelligence\ProviderBackedImageIntelligenceAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class LocalImageIntelligenceTaggerContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_process_runner_forwards_local_tagger_backend_env_to_python_script(): void
    {
        $previousBackend = getenv('LSKY_LOCAL_TAGGER_BACKEND');
        putenv('LSKY_LOCAL_TAGGER_BACKEND=blip_legacy');

        $script = $this->makePythonScript(<<<'PY'
import argparse
import json
import os

parser = argparse.ArgumentParser()
parser.add_argument("image_path")
parser.add_argument("--top", type=int, default=3)
parser.add_argument("--origin-name", default="")
parser.parse_args()

print(json.dumps({
    "caption": "",
    "summary": "",
    "labels": [os.getenv("LSKY_LOCAL_TAGGER_BACKEND", "")],
    "keywords": [],
    "ocr_text": "",
    "classifications": [],
    "metadata": {"backend": os.getenv("LSKY_LOCAL_TAGGER_BACKEND", "")},
    "elapsed_ms": 5,
}, ensure_ascii=False))
PY
        );

        try {
            $payload = (new LocalImageIntelligenceProcessRunner())->run($script, '/tmp/dummy-image.png', '', 3);
            $this->assertSame(['blip_legacy'], $payload['labels']);
            $this->assertSame('blip_legacy', $payload['metadata']['backend']);
        } finally {
            if ($previousBackend === false) {
                putenv('LSKY_LOCAL_TAGGER_BACKEND');
            } else {
                putenv('LSKY_LOCAL_TAGGER_BACKEND='.$previousBackend);
            }
        }
    }

    public function test_local_analyzer_maps_wd_tagger_payload_to_compatible_write_side_shape(): void
    {
        $runtimeFile = $this->writeRuntimeImage('wd-analyzer-contract.png');

        $image = new Image();
        $image->path = '';
        $image->name = basename($runtimeFile);
        $image->origin_name = '123.jpg';

        $runner = Mockery::mock(LocalImageIntelligenceProcessRunner::class);
        $runner->shouldReceive('run')
            ->once()
            ->andReturn([
                'caption' => '一张包含袜子的图片',
                'summary' => '识别结果：袜子、服饰',
                'prompt_hint' => '标签：袜子、服饰',
                'labels' => ['袜子', '服饰'],
                'keywords' => ['袜子', 'sock', '服饰'],
                'ocr_text' => '',
                'classifications' => [
                    ['zh' => '袜子', 'en' => 'sock', 'confidence' => 0.93],
                ],
                'metadata' => [
                    'backend' => 'wd_tagger',
                    'provider_label' => 'WD ViT Tagger v3 + Tesseract',
                    'model' => 'SmilingWolf/wd-vit-tagger-v3',
                    'visual_tags' => [
                        ['name' => 'socks', 'score' => 0.93],
                    ],
                ],
                'elapsed_ms' => 42,
            ]);

        $payload = (new LocalImageIntelligenceAnalyzer($runner))->analyze($image);

        $this->assertSame('ready', $payload['status']);
        $this->assertSame('local_intelligence', $payload['source']);
        $this->assertSame(2, $payload['source_version']);
        $this->assertSame(['袜子', '服饰'], $payload['labels']);
        $this->assertSame(['袜子', 'sock', '服饰'], $payload['keywords']);
        $this->assertSame('一张包含袜子的图片', $payload['caption']);
        $this->assertSame('识别结果：袜子、服饰', $payload['summary']);
        $this->assertSame('标签：袜子、服饰', $payload['prompt_hint']);
        $this->assertSame('wd_tagger', $payload['metadata']['backend']);
        $this->assertSame('WD ViT Tagger v3 + Tesseract', $payload['metadata']['provider_label']);
        $this->assertSame('SmilingWolf/wd-vit-tagger-v3', $payload['metadata']['model']);
    }

    public function test_local_tagger_labels_are_projected_into_searchable_terms(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'wd-socks-searchable',
            'name' => '123.jpg',
            'origin_name' => '123.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpeg',
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        $localAnalyzer = Mockery::mock(LocalImageIntelligenceAnalyzer::class);
        $localAnalyzer->shouldReceive('analyze')
            ->once()
            ->andReturn([
                'status' => 'ready',
                'source' => 'local_intelligence',
                'source_version' => 2,
                'ocr_text' => '',
                'caption' => '一张包含袜子的图片',
                'summary' => '识别结果：袜子、服饰',
                'prompt_hint' => '标签：袜子、服饰',
                'labels' => ['袜子', '服饰'],
                'keywords' => ['袜子', 'sock', 'socks'],
                'metadata' => [
                    'backend' => 'wd_tagger',
                    'provider' => 'local',
                    'provider_label' => 'WD ViT Tagger v3 + Tesseract',
                    'model' => 'SmilingWolf/wd-vit-tagger-v3',
                    'transport' => 'symfony_process',
                    'fallback' => false,
                    'fallback_reason' => null,
                    'generated_by' => 'image_intelligence.local.v2',
                ],
                'analyzed_at' => now(),
                'last_error' => null,
            ]);

        $providerAnalyzer = Mockery::mock(ProviderBackedImageIntelligenceAnalyzer::class);
        $providerAnalyzer->shouldNotReceive('analyze');
        $providerAnalyzer->shouldNotReceive('activeProviderSnapshot');

        $this->app->instance(LocalImageIntelligenceAnalyzer::class, $localAnalyzer);
        $this->app->instance(ProviderBackedImageIntelligenceAnalyzer::class, $providerAnalyzer);

        $record = $this->app->make(ImageIntelligenceService::class)->analyzeAndStore($imageId);

        $this->assertNotNull($record);
        $this->assertSame('local_intelligence', $record->source);
        $this->assertDatabaseHas('image_intelligence_terms', [
            'image_id' => $imageId,
            'source' => 'label',
            'normalized_term' => '袜子',
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/images/ai-search?q=袜子')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.data.0.key', $key);
    }

    public function test_service_can_disable_provider_fallback_for_local_failures(): void
    {
        $previousFallback = getenv('LSKY_LOCAL_INTELLIGENCE_PROVIDER_FALLBACK');
        putenv('LSKY_LOCAL_INTELLIGENCE_PROVIDER_FALLBACK=false');

        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'wd-no-provider-fallback',
            'name' => '123.jpg',
            'origin_name' => '123.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpeg',
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        $localAnalyzer = Mockery::mock(LocalImageIntelligenceAnalyzer::class);
        $localAnalyzer->shouldReceive('analyze')
            ->once()
            ->andThrow(new \RuntimeException('wd_tagger runtime unavailable'));

        $providerAnalyzer = Mockery::mock(ProviderBackedImageIntelligenceAnalyzer::class);
        $providerAnalyzer->shouldNotReceive('analyze');
        $providerAnalyzer->shouldReceive('activeProviderSnapshot')
            ->once()
            ->andReturn([
                'provider' => 'qwen',
                'model' => 'qwen-image-edit-plus',
                'transport' => 'openai_compatible',
            ]);

        $this->app->instance(LocalImageIntelligenceAnalyzer::class, $localAnalyzer);
        $this->app->instance(ProviderBackedImageIntelligenceAnalyzer::class, $providerAnalyzer);

        try {
            $record = $this->app->make(ImageIntelligenceService::class)->analyzeAndStore($imageId);

            $this->assertNotNull($record);
            $this->assertSame('metadata_placeholder', $record->source);
            $this->assertStringContainsString('local_analysis_failed', (string) data_get($record->metadata, 'fallback_reason'));
            $this->assertSame(
                0,
                DB::table('image_intelligence_terms')->where('image_id', $imageId)->count()
            );
        } finally {
            if ($previousFallback === false) {
                putenv('LSKY_LOCAL_INTELLIGENCE_PROVIDER_FALLBACK');
            } else {
                putenv('LSKY_LOCAL_INTELLIGENCE_PROVIDER_FALLBACK='.$previousFallback);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertImage(\App\Models\User $user, array $overrides = []): string
    {
        $strategyId = (int) DB::table('strategies')->value('id');
        $key = (string) ($overrides['key'] ?? ('i'.substr(sha1((string) microtime(true).random_int(1000, 9999)), 0, 23)));

        DB::table('images')->insert(array_merge([
            'user_id' => $user->id,
            'album_id' => null,
            'group_id' => $user->group_id,
            'strategy_id' => $strategyId,
            'key' => $key,
            'path' => '',
            'name' => $key.'.png',
            'origin_name' => 'intelligence-test.png',
            'alias_name' => '',
            'size' => 12,
            'mimetype' => 'image/png',
            'extension' => 'png',
            'md5' => md5($key),
            'sha1' => sha1($key),
            'width' => 100,
            'height' => 80,
            'permission' => 0,
            'is_unhealthy' => false,
            'uploaded_ip' => '127.0.0.1',
            'ocr_text' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return $key;
    }

    private function makePythonScript(string $contents): string
    {
        $path = storage_path('framework/testing/'.uniqid('local-intelligence-backend-', true).'.py');
        file_put_contents($path, $contents);

        return $path;
    }

    private function writeRuntimeImage(string $filename): string
    {
        $directory = '/var/www/html/storage/app/uploads';
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory.'/'.$filename;
        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAIAAAAmkwkpAAAAFElEQVR4nGP8f+A0AwwwMSAB3BwAgCwCkoAWUnkAAAAASUVORK5CYII=')
        );

        return $path;
    }
}
