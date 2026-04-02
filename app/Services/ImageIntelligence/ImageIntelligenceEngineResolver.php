<?php

namespace App\Services\ImageIntelligence;

class ImageIntelligenceEngineResolver
{
    public function __construct(
        private readonly ImageIntelligenceConfigService $configService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        $config = $this->configService->all();

        return [
            'engine' => (string) ($config['engine'] ?? 'local'),
            'provider' => (string) ($config['provider'] ?? ''),
            'model' => (string) ($config['model'] ?? ''),
            'enable_labels' => (bool) ($config['enable_labels'] ?? true),
            'enable_summary' => (bool) ($config['enable_summary'] ?? true),
            'enable_ocr_text' => (bool) ($config['enable_ocr_text'] ?? true),
            'auto_on_upload' => (bool) ($config['auto_on_upload'] ?? true),
            'schedule_enabled' => (bool) ($config['schedule_enabled'] ?? true),
            'schedule_cron' => (string) ($config['schedule_cron'] ?? '0 * * * *'),
            'retry_failed' => (bool) ($config['retry_failed'] ?? true),
        ];
    }

    public function engine(): string
    {
        return (string) $this->resolve()['engine'];
    }
}
