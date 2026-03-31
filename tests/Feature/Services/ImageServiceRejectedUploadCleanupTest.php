<?php

namespace Tests\Feature\Services;

use App\Services\ImageService;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Filesystem;
use Mockery;
use Tests\TestCase;

class ImageServiceRejectedUploadCleanupTest extends TestCase
{
    public function test_cleanup_rejected_upload_logs_delete_failure(): void
    {
        Log::spy();

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('delete')
            ->once()
            ->with('banned/source.png')
            ->andThrow(new \RuntimeException('delete failed'));

        $service = new class extends ImageService
        {
            public function cleanupRejectedUploadForTest(Filesystem $filesystem, string $pathname): void
            {
                $this->cleanupRejectedUpload($filesystem, $pathname);
            }
        };

        $service->cleanupRejectedUploadForTest($filesystem, 'banned/source.png');

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                '拒绝违规上传时删除源文件失败',
                Mockery::on(static function (array $context): bool {
                    return ($context['message'] ?? null) === 'delete failed';
                })
            );
    }
}
