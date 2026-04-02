<?php

namespace App\Services\ImageIntelligence;

use App\Enums\ConfigKey;
use App\Models\Config;
use App\Models\User;
use App\Services\AiProviderConfigService;
use Illuminate\Support\Facades\Cache;

class ImageIntelligenceConfigService
{
    private const ENGINES = ['disabled', 'local', 'provider'];

    public function __construct(
        private readonly AiProviderConfigService $aiProviderConfigService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return array_merge(
            $this->all(),
            [
                'can_manage' => (bool) ($user->is_adminer ?? false),
                'provider_options' => $this->providerOptions(),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'engine' => $this->normalizeEngine((string) $this->config(ConfigKey::ImageIntelligenceEngine, 'local')),
            'provider' => trim((string) $this->config(ConfigKey::ImageIntelligenceProvider, 'gpt')) ?: 'gpt',
            'model' => trim((string) $this->config(ConfigKey::ImageIntelligenceModel, '')),
            'enable_labels' => $this->configBool(ConfigKey::ImageIntelligenceEnableLabels, true),
            'enable_summary' => $this->configBool(ConfigKey::ImageIntelligenceEnableSummary, true),
            'enable_ocr_text' => $this->configBool(ConfigKey::ImageIntelligenceEnableOcrText, true),
            'auto_on_upload' => $this->configBool(ConfigKey::ImageIntelligenceAutoOnUpload, true),
            'schedule_enabled' => $this->configBool(ConfigKey::ImageIntelligenceScheduleEnabled, true),
            'schedule_cron' => trim((string) $this->config(ConfigKey::ImageIntelligenceScheduleCron, '0 * * * *')) ?: '0 * * * *',
            'retry_failed' => $this->configBool(ConfigKey::ImageIntelligenceRetryFailed, true),
            'engine_options' => self::ENGINES,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function save(User $user, array $payload): array
    {
        $engine = $this->normalizeEngine((string) ($payload['engine'] ?? 'local'));
        $provider = trim((string) ($payload['provider'] ?? 'gpt')) ?: 'gpt';
        $model = trim((string) ($payload['model'] ?? ''));

        $providerOptions = collect($this->providerOptions())
            ->keyBy('provider');

        if ($engine === 'provider') {
            $selectedProvider = $providerOptions->get($provider);
            if (! is_array($selectedProvider)) {
                throw new \InvalidArgumentException('图片识别提供商不存在');
            }
            if (! ($selectedProvider['ready'] ?? false)) {
                throw new \InvalidArgumentException('当前提供商未完成配置，无法用于图片识别');
            }

            $models = is_array($selectedProvider['models'] ?? null) ? $selectedProvider['models'] : [];
            if ($model === '') {
                $model = trim((string) ($selectedProvider['default_model'] ?? ''));
            }
            if ($model === '' || ($models !== [] && ! in_array($model, $models, true))) {
                throw new \InvalidArgumentException('图片识别模型不可用');
            }
        }

        $now = now()->format('Y-m-d H:i:s');
        Config::query()->upsert([
            $this->row(ConfigKey::ImageIntelligenceEngine, $engine, $now),
            $this->row(ConfigKey::ImageIntelligenceProvider, $provider, $now),
            $this->row(ConfigKey::ImageIntelligenceModel, $model, $now),
            $this->row(ConfigKey::ImageIntelligenceEnableLabels, (int) ! empty($payload['enable_labels']), $now),
            $this->row(ConfigKey::ImageIntelligenceEnableSummary, (int) ! empty($payload['enable_summary']), $now),
            $this->row(ConfigKey::ImageIntelligenceEnableOcrText, (int) ! empty($payload['enable_ocr_text']), $now),
            $this->row(ConfigKey::ImageIntelligenceAutoOnUpload, (int) ! empty($payload['auto_on_upload']), $now),
            $this->row(ConfigKey::ImageIntelligenceScheduleEnabled, (int) ! empty($payload['schedule_enabled']), $now),
            $this->row(
                ConfigKey::ImageIntelligenceScheduleCron,
                trim((string) ($payload['schedule_cron'] ?? '0 * * * *')) ?: '0 * * * *',
                $now
            ),
            $this->row(ConfigKey::ImageIntelligenceRetryFailed, (int) ! empty($payload['retry_failed']), $now),
        ], ['name'], ['value', 'updated_at']);

        Cache::forget('configs');

        return $this->forUser($user);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function providerOptions(): array
    {
        return $this->aiProviderConfigService->globalProviderCatalog();
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return Config::query()->where('name', $key)->value('value') ?? $default;
    }

    private function configBool(string $key, bool $default): bool
    {
        $value = $this->config($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function normalizeEngine(string $engine): string
    {
        $engine = strtolower(trim($engine));

        return in_array($engine, self::ENGINES, true) ? $engine : 'local';
    }

    /**
     * @return array<string, string>
     */
    private function row(string $name, string|int $value, string $timestamp): array
    {
        return [
            'name' => $name,
            'value' => (string) $value,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
