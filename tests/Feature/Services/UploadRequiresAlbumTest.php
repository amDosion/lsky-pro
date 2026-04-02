<?php

namespace Tests\Feature\Services;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadRequiresAlbumTest extends TestCase
{
    public function test_authenticated_user_without_albums_cannot_upload(): void
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->post('/upload', [
            'file' => UploadedFile::fake()->image('sock.jpg'),
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => false,
                'message' => '请先创建相册后再上传图片',
            ]);
    }
}
