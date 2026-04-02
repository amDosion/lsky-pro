<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImageIntelligencePresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_user_images_list_exposes_ready_intelligence_state_for_card_badges(): void
    {
        $user = $this->createTestUser();
        $imageId = $this->insertImage($user, 'presentation-user');
        $this->insertIntelligenceRecord($user->id, $imageId, [
            'summary' => '一双浅色袜子的商品展示图',
        ]);

        $this->actingAs($user)
            ->getJson('/user/images')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.images.data.0.id', $imageId)
            ->assertJsonPath('data.images.data.0.intelligence.ready', true)
            ->assertJsonPath('data.images.data.0.intelligence.fallback', false)
            ->assertJsonPath('data.images.data.0.intelligence.display_summary', '一双浅色袜子的商品展示图');
    }

    public function test_gallery_images_list_exposes_ready_intelligence_state_for_card_badges(): void
    {
        $owner = $this->createTestUser();
        $viewer = $this->createTestUser();
        $albumId = $this->insertAlbum($owner->id, '共享相册');
        $imageId = $this->insertImage($owner, 'presentation-gallery', [
            'album_id' => $albumId,
        ]);
        $this->insertIntelligenceRecord($owner->id, $imageId, [
            'summary' => '共享相册中的袜子展示图',
        ]);

        DB::table('album_shares')->insert([
            'album_id' => $albumId,
            'user_id' => $viewer->id,
            'shared_by' => $owner->id,
            'permission' => 'view',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($viewer)
            ->getJson('/gallery/images?album_id='.$albumId)
            ->assertOk()
            ->assertJsonPath('data.0.id', $imageId)
            ->assertJsonPath('data.0.intelligence.ready', true)
            ->assertJsonPath('data.0.intelligence.display_summary', '共享相册中的袜子展示图');
    }

    public function test_admin_images_json_exposes_ready_intelligence_state_for_card_badges(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);
        $owner = $this->createTestUser();
        $imageId = $this->insertImage($owner, 'presentation-admin');
        $this->insertIntelligenceRecord($owner->id, $imageId, [
            'caption' => '后台图片列表中的袜子卡片',
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/images?json=1')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.images.0.id', $imageId)
            ->assertJsonPath('data.images.0.intelligence.ready', true)
            ->assertJsonPath('data.images.0.intelligence.display_summary', '后台图片列表中的袜子卡片');
    }

    public function test_noisy_ocr_text_is_not_exposed_as_display_summary(): void
    {
        $user = $this->createTestUser();
        $imageId = $this->insertImage($user, 'presentation-noisy');
        $this->insertIntelligenceRecord($user->id, $imageId, [
            'summary' => null,
            'caption' => null,
            'ocr_text' => '5 2 本 Fe f a = . y Om is a yy, pr SN ae i y 4 Af gs eee aad se.',
        ]);

        $this->actingAs($user)
            ->getJson('/user/images/'.$imageId)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.image.intelligence.ready', true)
            ->assertJsonPath('data.image.intelligence.display_summary', null);
    }

    public function test_image_pages_include_recognition_badge_and_summary_labels(): void
    {
        $user = $this->createTestUser();
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($user)
            ->get('/images')
            ->assertOk()
            ->assertSee('识别摘要')
            ->assertSee('已识别');

        $this->actingAs($admin)
            ->get('/admin/images')
            ->assertOk()
            ->assertSee('识别摘要')
            ->assertSee('已识别');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertImage(\App\Models\User $user, string $key, array $overrides = []): int
    {
        $strategyId = (int) DB::table('strategies')->value('id');

        DB::table('images')->insert(array_merge([
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
        ], $overrides));

        return (int) DB::table('images')->where('key', $key)->value('id');
    }

    private function insertAlbum(int $userId, string $name): int
    {
        return (int) DB::table('albums')->insertGetId([
            'user_id' => $userId,
            'parent_id' => null,
            'name' => $name,
            'intro' => '',
            'image_num' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertIntelligenceRecord(int $userId, int $imageId, array $overrides = []): void
    {
        DB::table('image_intelligence_records')->insert(array_merge([
            'image_id' => $imageId,
            'user_id' => $userId,
            'status' => 'ready',
            'source' => 'local_intelligence',
            'source_version' => 1,
            'ocr_text' => 'sock product photo',
            'caption' => null,
            'summary' => null,
            'prompt_hint' => null,
            'labels' => json_encode(['袜子'], JSON_UNESCAPED_UNICODE),
            'keywords' => json_encode(['袜子'], JSON_UNESCAPED_UNICODE),
            'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
            'analyzed_at' => now(),
            'last_error' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
