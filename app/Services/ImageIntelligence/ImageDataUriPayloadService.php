<?php

namespace App\Services\ImageIntelligence;

class ImageDataUriPayloadService
{
    private const MAX_DATA_URI_BYTES = 10_485_760;

    /**
     * Keep the largest candidates first to preserve OCR/detail quality when possible.
     *
     * @var array<int, int>
     */
    private const MAX_DIMENSIONS = [2048, 1600, 1280, 1024];

    /**
     * @var array<int, int>
     */
    private const JPEG_QUALITIES = [85, 75, 65, 55];

    /**
     * @return array{0: string, 1: string}
     */
    public function prepare(string $mimeType, string $contents): array
    {
        $resolvedMimeType = trim($mimeType) !== '' ? trim($mimeType) : 'application/octet-stream';
        $base64Contents = base64_encode($contents);

        if ($this->dataUriLength($resolvedMimeType, $base64Contents) <= self::MAX_DATA_URI_BYTES) {
            return [$resolvedMimeType, $base64Contents];
        }

        return $this->compressForProvider($contents);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function compressForProvider(string $contents): array
    {
        if (! class_exists(\Imagick::class)) {
            throw new \RuntimeException('图片过大且 Imagick 不可用，无法压缩到 provider 限制以内');
        }

        $source = new \Imagick();

        try {
            $source->readImageBlob($contents);
        } catch (\Throwable $e) {
            throw new \RuntimeException('图片过大且无法为 AI provider 重新编码', 0, $e);
        }

        foreach (self::MAX_DIMENSIONS as $maxDimension) {
            foreach (self::JPEG_QUALITIES as $quality) {
                $blob = $this->renderCandidateBlob($source, $maxDimension, $quality);
                $base64Contents = base64_encode($blob);

                if ($this->dataUriLength('image/jpeg', $base64Contents) <= self::MAX_DATA_URI_BYTES) {
                    return ['image/jpeg', $base64Contents];
                }
            }
        }

        throw new \RuntimeException('图片过大，压缩后仍超过 AI provider 的 data-uri 限制');
    }

    private function renderCandidateBlob(\Imagick $source, int $maxDimension, int $quality): string
    {
        $candidate = clone $source;

        if (method_exists($candidate, 'autoOrient')) {
            $candidate->autoOrient();
        }

        $candidate = $this->flattenToJpeg($candidate);
        $candidate->thumbnailImage($maxDimension, $maxDimension, true, true);
        $candidate->stripImage();
        $candidate->setImageColorspace(\Imagick::COLORSPACE_SRGB);
        $candidate->setImageFormat('jpeg');
        $candidate->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $candidate->setImageCompressionQuality($quality);
        $candidate->setOption('jpeg:optimize-coding', 'true');
        $candidate->setInterlaceScheme(\Imagick::INTERLACE_JPEG);

        $blob = $candidate->getImageBlob();
        $candidate->clear();
        $candidate->destroy();

        return $blob;
    }

    private function flattenToJpeg(\Imagick $image): \Imagick
    {
        $image->setImageBackgroundColor('white');
        $flattened = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $image->clear();
        $image->destroy();

        return $flattened;
    }

    private function dataUriLength(string $mimeType, string $base64Contents): int
    {
        return strlen('data:'.$mimeType.';base64,'.$base64Contents);
    }
}
