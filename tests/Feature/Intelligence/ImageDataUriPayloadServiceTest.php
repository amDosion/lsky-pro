<?php

namespace Tests\Feature\Intelligence;

use App\Services\ImageIntelligence\ImageDataUriPayloadService;
use Tests\TestCase;

class ImageDataUriPayloadServiceTest extends TestCase
{
    public function test_small_image_payload_is_kept_as_is(): void
    {
        $service = app(ImageDataUriPayloadService::class);
        $blob = $this->makePngBlob(256, 256);

        [$mimeType, $base64Contents] = $service->prepare('image/png', $blob);

        $this->assertSame('image/png', $mimeType);
        $this->assertSame($blob, base64_decode($base64Contents, true));
    }

    public function test_large_image_payload_is_reencoded_under_provider_limit(): void
    {
        $service = app(ImageDataUriPayloadService::class);
        $blob = $this->makePngBlob(3000, 3000, 'plasma:fractal');

        $this->assertGreaterThan(10_485_760, strlen('data:image/png;base64,'.base64_encode($blob)));

        [$mimeType, $base64Contents] = $service->prepare('image/png', $blob);

        $this->assertSame('image/jpeg', $mimeType);
        $this->assertLessThanOrEqual(
            10_485_760,
            strlen('data:'.$mimeType.';base64,'.$base64Contents)
        );
    }

    private function makePngBlob(int $width, int $height, string $pattern = 'xc:#4f46e5'): string
    {
        $image = new \Imagick();
        $image->newPseudoImage($width, $height, $pattern);
        $image->setImageFormat('png');
        $blob = $image->getImageBlob();
        $image->clear();
        $image->destroy();

        return $blob;
    }
}
