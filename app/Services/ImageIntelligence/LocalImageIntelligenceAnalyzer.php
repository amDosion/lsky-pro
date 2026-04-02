<?php

namespace App\Services\ImageIntelligence;

use App\Models\Image;

class LocalImageIntelligenceAnalyzer
{
    private const SCRIPT_RELATIVE_PATH = 'scripts/image_intelligence/classify_ocr.py';
    private const UPLOADS_ROOT = '/var/www/html/storage/app/uploads';
    private const DEFAULT_PROVIDER_LABEL = 'Local image tagger + OCR';

    public function __construct(
        private readonly LocalImageIntelligenceProcessRunner $processRunner
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(Image $image): array
    {
        $filePath = $this->resolveFilePath($image);

        if (! file_exists($filePath)) {
            throw new \RuntimeException("图片文件不存在: {$filePath}");
        }

        $scriptPath = base_path(self::SCRIPT_RELATIVE_PATH);

        if (! file_exists($scriptPath)) {
            throw new \RuntimeException('本地图像分析脚本不存在: '.$scriptPath);
        }

        $decoded = $this->processRunner->run(
            $scriptPath,
            $filePath,
            (string) $image->origin_name,
            3
        );

        $labels = $this->normalizeList($decoded['labels'] ?? []);
        $keywords = $this->normalizeList($decoded['keywords'] ?? []);
        $caption = $this->truncate((string) ($decoded['caption'] ?? ''), 5000);
        $summary = $this->truncate((string) ($decoded['summary'] ?? ''), 5000);
        $promptHint = $this->truncate((string) ($decoded['prompt_hint'] ?? ''), 5000);
        $ocrText = $this->truncate((string) ($decoded['ocr_text'] ?? ''), 10000);
        $elapsedMs = (int) ($decoded['elapsed_ms'] ?? 0);
        $rawMetadata = is_array($decoded['metadata'] ?? null) ? $decoded['metadata'] : [];

        if ($summary === '' && ! empty($decoded['classifications'])) {
            $parts = [];
            foreach (array_slice($decoded['classifications'], 0, 3) as $cls) {
                $zh = trim((string) ($cls['zh'] ?? ''));
                $en = trim((string) ($cls['en'] ?? ''));
                $confidence = (float) ($cls['confidence'] ?? 0);

                if ($zh !== '' && $en !== '' && $zh !== $en) {
                    $parts[] = sprintf('%s(%s) %.1f%%', $zh, $en, $confidence * 100);
                    continue;
                }

                $label = $zh !== '' ? $zh : $en;
                if ($label !== '') {
                    $parts[] = sprintf('%s %.1f%%', $label, $confidence * 100);
                }
            }
            $summary = $parts !== [] ? '识别结果：' . implode('、', $parts) : '';
        }

        $providerLabel = trim((string) ($rawMetadata['provider_label'] ?? self::DEFAULT_PROVIDER_LABEL));
        $model = trim((string) ($rawMetadata['model'] ?? ($rawMetadata['backend'] ?? 'local')));
        $backend = trim((string) ($rawMetadata['backend'] ?? 'local'));
        $generatedBy = trim((string) ($rawMetadata['generated_by'] ?? 'image_intelligence.local.v2'));

        $metadata = array_merge($rawMetadata, [
            'provider' => 'local',
            'provider_label' => $providerLabel !== '' ? $providerLabel : self::DEFAULT_PROVIDER_LABEL,
            'model' => $model !== '' ? $model : 'local',
            'backend' => $backend !== '' ? $backend : 'local',
            'transport' => 'symfony_process',
            'base_url' => '',
            'fallback' => false,
            'fallback_reason' => null,
            'elapsed_ms' => $elapsedMs,
            'generated_by' => $generatedBy !== '' ? $generatedBy : 'image_intelligence.local.v2',
        ]);

        return [
            'status' => 'ready',
            'source' => 'local_intelligence',
            'source_version' => 2,
            'ocr_text' => $ocrText,
            'caption' => $caption,
            'summary' => $this->truncate($summary, 5000),
            'prompt_hint' => $promptHint,
            'labels' => $labels,
            'keywords' => $keywords,
            'metadata' => $metadata,
            'analyzed_at' => now(),
            'last_error' => null,
        ];
    }

    private function resolveFilePath(Image $image): string
    {
        $path = $image->path ? rtrim($image->path, '/') . '/' : '';
        return self::UPLOADS_ROOT . '/' . $path . $image->name;
    }

    /**
     * @param  iterable<mixed>|mixed  $values
     * @return array<int, string>
     */
    private function normalizeList($values): array
    {
        return collect(is_iterable($values) ? $values : [])
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->take(12)
            ->all();
    }

    private function truncate(string $value, int $limit): string
    {
        return mb_substr(trim($value), 0, $limit);
    }
}
