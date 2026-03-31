<?php

namespace Tests\Feature\Intelligence;

use App\Services\ImageIntelligence\ImageIntelligenceService;
use App\Services\ImageIntelligence\LocalImageIntelligenceAnalyzer;
use App\Services\ImageIntelligence\ProviderBackedImageIntelligenceAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ImageIntelligenceWriteFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_analyze_and_store_falls_back_to_provider_and_projects_searchable_terms(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'socks-searchable',
            'name' => '123.jpg',
            'origin_name' => '123.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpeg',
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        $localAnalyzer = Mockery::mock(LocalImageIntelligenceAnalyzer::class);
        $localAnalyzer->shouldReceive('analyze')
            ->once()
            ->andThrow(new \RuntimeException('missing classify_ocr.py'));

        $providerAnalyzer = Mockery::mock(ProviderBackedImageIntelligenceAnalyzer::class);
        $providerAnalyzer->shouldReceive('analyze')
            ->once()
            ->andReturn([
                'status' => 'ready',
                'source' => 'ai_provider:qwen',
                'source_version' => 2,
                'ocr_text' => '',
                'caption' => '一双浅色袜子产品图',
                'summary' => '主体是一双袜子，适合商品检索。',
                'prompt_hint' => '袜子 产品图 电商 白底',
                'labels' => ['袜子', '服饰'],
                'keywords' => ['袜子', '棉袜', '产品图'],
                'metadata' => [
                    'provider' => 'qwen',
                    'fallback' => false,
                ],
                'analyzed_at' => now(),
                'last_error' => null,
            ]);

        $this->app->instance(LocalImageIntelligenceAnalyzer::class, $localAnalyzer);
        $this->app->instance(ProviderBackedImageIntelligenceAnalyzer::class, $providerAnalyzer);

        $record = $this->app->make(ImageIntelligenceService::class)->analyzeAndStore($imageId);

        $this->assertNotNull($record);
        $this->assertSame('ai_provider:qwen', $record->source);
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

    public function test_analyze_and_store_does_not_project_placeholder_terms_when_all_analyzers_fail(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'placeholder-only',
            'name' => '123.jpg',
            'origin_name' => '123.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpeg',
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        DB::table('image_intelligence_terms')->insert([
            'image_id' => $imageId,
            'user_id' => $user->id,
            'source' => 'label',
            'term' => '旧标签',
            'normalized_term' => '旧标签',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $localAnalyzer = Mockery::mock(LocalImageIntelligenceAnalyzer::class);
        $localAnalyzer->shouldReceive('analyze')
            ->once()
            ->andThrow(new \RuntimeException('missing classify_ocr.py'));

        $providerAnalyzer = Mockery::mock(ProviderBackedImageIntelligenceAnalyzer::class);
        $providerAnalyzer->shouldReceive('analyze')
            ->once()
            ->andThrow(new \RuntimeException('provider unavailable'));
        $providerAnalyzer->shouldReceive('activeProviderSnapshot')
            ->once()
            ->andReturn([
                'provider' => 'qwen',
                'model' => 'qwen-image-edit-plus',
                'transport' => 'openai_compatible',
            ]);

        $this->app->instance(LocalImageIntelligenceAnalyzer::class, $localAnalyzer);
        $this->app->instance(ProviderBackedImageIntelligenceAnalyzer::class, $providerAnalyzer);

        $record = $this->app->make(ImageIntelligenceService::class)->analyzeAndStore($imageId);

        $this->assertNotNull($record);
        $this->assertSame('metadata_placeholder', $record->source);
        $this->assertSame(
            0,
            DB::table('image_intelligence_terms')->where('image_id', $imageId)->count()
        );
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
}
