<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntelligenceCoverageStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_status_endpoint_excludes_placeholder_records_from_real_analysis_coverage(): void
    {
        $user = $this->createTestUser();

        $placeholderImageId = $this->insertImage($user, 'coverage-placeholder');
        $realImageId = $this->insertImage($user, 'coverage-real');
        $placeholderAnalyzedAt = now()->addHour()->startOfMinute();
        $realAnalyzedAt = now()->subHour()->startOfMinute();

        DB::table('image_intelligence_records')->insert([
            [
                'image_id' => $placeholderImageId,
                'user_id' => $user->id,
                'status' => 'ready',
                'source' => 'metadata_placeholder',
                'source_version' => 1,
                'ocr_text' => 'placeholder coverage',
                'caption' => 'placeholder coverage',
                'summary' => 'placeholder coverage',
                'prompt_hint' => 'placeholder coverage',
                'labels' => json_encode([], JSON_UNESCAPED_UNICODE),
                'keywords' => json_encode([], JSON_UNESCAPED_UNICODE),
                'metadata' => json_encode(['fallback' => true], JSON_UNESCAPED_UNICODE),
                'analyzed_at' => $placeholderAnalyzedAt,
                'created_at' => $placeholderAnalyzedAt,
                'updated_at' => $placeholderAnalyzedAt,
            ],
            [
                'image_id' => $realImageId,
                'user_id' => $user->id,
                'status' => 'ready',
                'source' => 'local_intelligence',
                'source_version' => 2,
                'ocr_text' => 'real coverage',
                'caption' => 'real coverage',
                'summary' => 'real coverage',
                'prompt_hint' => 'real coverage',
                'labels' => json_encode([], JSON_UNESCAPED_UNICODE),
                'keywords' => json_encode([], JSON_UNESCAPED_UNICODE),
                'metadata' => json_encode(['fallback' => false], JSON_UNESCAPED_UNICODE),
                'analyzed_at' => $realAnalyzedAt,
                'created_at' => $realAnalyzedAt,
                'updated_at' => $realAnalyzedAt,
            ],
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/intelligence/status')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.intelligence.images_total', 2)
            ->assertJsonPath('data.intelligence.analyzed_count', 1)
            ->assertJsonPath('data.intelligence.pending_count', 1)
            ->assertJsonPath('data.intelligence.coverage_rate', 50)
            ->assertJsonPath('data.intelligence.coverage_label', '50%')
            ->assertJsonPath('data.intelligence.latest_analyzed_at', $realAnalyzedAt->format('Y-m-d H:i:s'));
    }

    private function insertImage(\App\Models\User $user, string $key): int
    {
        $strategyId = (int) DB::table('strategies')->value('id');

        DB::table('images')->insert([
            'user_id' => $user->id,
            'album_id' => null,
            'group_id' => $user->group_id,
            'strategy_id' => $strategyId,
            'key' => $key,
            'path' => '',
            'name' => $key.'.png',
            'origin_name' => $key.'.png',
            'alias_name' => '',
            'size' => 12,
            'mimetype' => 'image/png',
            'extension' => 'png',
            'md5' => md5($key),
            'sha1' => sha1($key),
            'width' => 1280,
            'height' => 720,
            'permission' => 0,
            'is_unhealthy' => false,
            'uploaded_ip' => '127.0.0.1',
            'ocr_text' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('images')->where('key', $key)->value('id');
    }
}
