<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\InstallSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected array $links = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->links = \config('filesystems.links');
        Cache::forget('configs');
        $this->seed(InstallSeeder::class);
        Cache::forget('configs');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (array_keys($this->links) as $link) {
            if (! is_string($link) || $link === '') {
                continue;
            }

            if (file_exists($link) || is_link($link)) {
                @unlink($link);
            }

            $projectRootLink = str_replace('/public', '', $link);
            if ($projectRootLink !== $link && (file_exists($projectRootLink) || is_link($projectRootLink))) {
                @unlink($projectRootLink);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createTestUser(array $overrides = []): User
    {
        $now = now();
        $groupId = (int) (DB::table('groups')->where('is_default', true)->value('id')
            ?? DB::table('groups')->value('id'));
        $configOverrides = $overrides['configs'] ?? [];
        unset($overrides['configs']);

        $payload = array_merge([
            'uuid' => (string) Str::uuid(),
            'group_id' => $groupId,
            'name' => 'Test User '.Str::upper(Str::random(6)),
            'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_adminer' => false,
            'capacity' => 512000,
            'url' => '',
            'configs' => json_encode(array_merge(
                config('convention.user', []),
                [User::CONFIG_PASSWORD_LOGIN_READY => true],
                is_array($configOverrides) ? $configOverrides : []
            ), JSON_UNESCAPED_UNICODE),
            'image_num' => 0,
            'album_num' => 0,
            'registered_ip' => '127.0.0.1',
            'status' => 1,
            'provider' => null,
            'provider_id' => null,
            'provider_avatar' => null,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides);

        $id = DB::table('users')->insertGetId($payload);

        return User::query()->findOrFail($id);
    }
}
