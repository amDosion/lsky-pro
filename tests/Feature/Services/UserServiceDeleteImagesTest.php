<?php

namespace Tests\Feature\Services;

use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserServiceDeleteImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_delete_images_recomputes_user_count_without_repeated_image_count_queries(): void
    {
        config([
            'lifecycle.recycle_bin.enabled' => false,
            'queue.image_delete.async' => false,
        ]);

        $user = $this->createTestUser([
            'image_num' => 3,
        ]);

        $imageIds = [
            $this->insertImage($user, 'delete-images-a'),
            $this->insertImage($user, 'delete-images-b'),
            $this->insertImage($user, 'delete-images-c'),
        ];

        DB::flushQueryLog();
        DB::enableQueryLog();

        $deleted = app(UserService::class)->deleteImages($imageIds, $user);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $imageCountQueries = collect($queries)
            ->pluck('query')
            ->filter(fn (string $query) => str_contains(strtolower($query), 'count(') && str_contains(strtolower($query), 'from "images"'))
            ->count();

        $this->assertSame(3, $deleted);
        $this->assertSame(0, (int) DB::table('images')->where('user_id', $user->id)->count());
        $this->assertSame(0, (int) DB::table('users')->where('id', $user->id)->value('image_num'));
        $this->assertLessThanOrEqual(1, $imageCountQueries);
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
