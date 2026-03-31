<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiSearchReadSideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_ai_search_matches_real_intelligence_ocr_text(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'search-real',
            'origin_name' => 'search-real.png',
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        $this->insertRecord($imageId, $user->id, [
            'status' => 'ready',
            'source' => 'ai_provider:gpt',
            'source_version' => 2,
            'caption' => '真实 OCR 搜索样本',
            'ocr_text' => 'invoice-real-token',
            'metadata' => json_encode([
                'fallback' => false,
                'provider' => 'gpt',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/images/ai-search?q=invoice-real-token')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.data.0.key', $key);
    }

    public function test_ai_search_does_not_treat_placeholder_ocr_as_real_ocr_signal(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'search-placeholder',
            'origin_name' => 'neutral-name.png',
            'alias_name' => '',
            'ocr_text' => null,
        ]);
        $imageId = (int) DB::table('images')->where('key', $key)->value('id');

        $this->insertRecord($imageId, $user->id, [
            'status' => 'ready',
            'source' => 'metadata_placeholder',
            'caption' => '占位结果',
            'summary' => '只用于验证占位 OCR 不应参与搜索。',
            'prompt_hint' => '',
            'ocr_text' => 'placeholder-ocr-token',
            'metadata' => json_encode([
                'fallback' => true,
                'fallback_reason' => 'local_analysis_failed:test',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/images/ai-search?q=placeholder-ocr-token')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.data', []);
    }

    public function test_ai_search_still_falls_back_to_legacy_ocr_when_no_intelligence_record_exists(): void
    {
        $user = $this->createTestUser();
        $key = $this->insertImage($user, [
            'key' => 'search-legacy',
            'origin_name' => 'legacy-only.png',
            'ocr_text' => 'legacy-ocr-token',
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/images/ai-search?q=legacy-ocr-token')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.data.0.key', $key);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertImage(\App\Models\User $user, array $overrides = []): string
    {
        $strategyId = (int) DB::table('strategies')->value('id');
        $key = (string) ($overrides['key'] ?? ('s'.substr(sha1((string) microtime(true).random_int(1000, 9999)), 0, 23)));

        DB::table('images')->insert(array_merge([
            'user_id' => $user->id,
            'album_id' => null,
            'group_id' => $user->group_id,
            'strategy_id' => $strategyId,
            'key' => $key,
            'path' => '',
            'name' => $key.'.png',
            'origin_name' => 'search-test.png',
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertRecord(int $imageId, int $userId, array $overrides): void
    {
        DB::table('image_intelligence_records')->insert(array_merge([
            'image_id' => $imageId,
            'user_id' => $userId,
            'status' => 'ready',
            'source' => 'metadata_placeholder',
            'source_version' => 1,
            'caption' => '',
            'summary' => '',
            'prompt_hint' => '',
            'ocr_text' => '',
            'labels' => json_encode([], JSON_UNESCAPED_UNICODE),
            'keywords' => json_encode([], JSON_UNESCAPED_UNICODE),
            'metadata' => json_encode(['generated_by' => 'test'], JSON_UNESCAPED_UNICODE),
            'analyzed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
