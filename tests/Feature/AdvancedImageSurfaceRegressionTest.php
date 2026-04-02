<?php

namespace Tests\Feature;

use App\Enums\ImageReviewStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdvancedImageSurfaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_review_center_api_returns_real_preview_and_filename_fields(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);
        $owner = $this->createTestUser();
        $imageId = $this->insertImage($owner, 'review-thumb-check');

        $response = $this->actingAs($admin)
            ->getJson('/advanced-api/admin/reviews?status=review_pending')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.data.0.id', $imageId)
            ->assertJsonPath('data.data.0.filename', 'review-thumb-check.png');

        $item = data_get($response->json(), 'data.data.0');
        $this->assertNotSame('', trim((string) ($item['thumb_url'] ?? '')));
        $this->assertNotSame('', trim((string) ($item['url'] ?? '')));
    }

    public function test_advanced_shell_uses_fa5_compatible_performance_icon(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)
            ->get('/advanced/performance')
            ->assertOk()
            ->assertSee('fa-tachometer-alt', false)
            ->assertDontSee('fa-gauge-high', false);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('fa-tachometer-alt', false)
            ->assertDontSee('fa-gauge-high', false);
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
            'review_status' => ImageReviewStatus::Pending,
            'review_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'uploaded_ip' => '127.0.0.1',
            'ocr_text' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('images')->where('key', $key)->value('id');
    }
}
