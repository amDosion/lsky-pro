<?php

namespace Tests\Feature\Intelligence;

use App\Enums\ConfigKey;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ImageIntelligenceConfigApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_any_authenticated_user_can_read_global_image_intelligence_config(): void
    {
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->getJson('/advanced-api/intelligence/config')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.engine', 'local')
            ->assertJsonPath('data.provider', 'gpt')
            ->assertJsonPath('data.can_manage', false)
            ->assertJsonPath('data.enable_labels', true)
            ->assertJsonPath('data.enable_summary', true)
            ->assertJsonPath('data.enable_ocr_text', true)
            ->assertJsonPath('data.auto_on_upload', true)
            ->assertJsonPath('data.schedule_enabled', true)
            ->assertJsonPath('data.retry_failed', true);
    }

    public function test_non_admin_cannot_update_global_image_intelligence_config(): void
    {
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->putJson('/advanced-api/intelligence/config', [
                'engine' => 'disabled',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_global_image_intelligence_config(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->seedReadyProviderConfig();

        $this->actingAs($admin)
            ->putJson('/advanced-api/intelligence/config', [
                'engine' => 'provider',
                'provider' => 'gpt',
                'model' => 'gpt-4.1-mini',
                'enable_labels' => true,
                'enable_summary' => true,
                'enable_ocr_text' => false,
                'auto_on_upload' => true,
                'schedule_enabled' => false,
                'schedule_cron' => '15 * * * *',
                'retry_failed' => true,
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.engine', 'provider')
            ->assertJsonPath('data.provider', 'gpt')
            ->assertJsonPath('data.model', 'gpt-4.1-mini')
            ->assertJsonPath('data.enable_ocr_text', false)
            ->assertJsonPath('data.schedule_enabled', false)
            ->assertJsonPath('data.schedule_cron', '15 * * * *')
            ->assertJsonPath('data.can_manage', true);

        $this->assertDatabaseHas('configs', [
            'name' => ConfigKey::ImageIntelligenceEngine,
            'value' => 'provider',
        ]);
        $this->assertDatabaseHas('configs', [
            'name' => ConfigKey::ImageIntelligenceModel,
            'value' => 'gpt-4.1-mini',
        ]);
        $this->assertDatabaseHas('configs', [
            'name' => ConfigKey::ImageIntelligenceScheduleCron,
            'value' => '15 * * * *',
        ]);
    }

    public function test_provider_mode_requires_ready_provider(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)
            ->putJson('/advanced-api/intelligence/config', [
                'engine' => 'provider',
                'provider' => 'gpt',
                'model' => 'gpt-4.1-mini',
            ])
            ->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', '当前提供商未完成配置，无法用于图片识别');
    }

    private function seedReadyProviderConfig(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        Config::query()->upsert([
            [
                'name' => ConfigKey::AiProvider,
                'value' => 'gpt',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => ConfigKey::AiProviderSettings,
                'value' => json_encode([
                    'gpt' => [
                        'label' => 'OpenAI GPT',
                        'base_url' => 'https://api.openai.com/v1',
                        'api_key' => 'test-key',
                        'default_model' => 'gpt-4.1-mini',
                        'models' => ['gpt-4.1-mini', 'gpt-4.1'],
                        'remote_models' => ['gpt-4.1-mini', 'gpt-4.1'],
                    ],
                    'deepseek' => [
                        'label' => 'DeepSeek',
                        'base_url' => 'https://api.deepseek.com/v1',
                        'api_key' => '',
                        'default_model' => 'deepseek-chat',
                        'models' => ['deepseek-chat'],
                    ],
                    'qwen' => [
                        'label' => '阿里千问',
                        'base_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
                        'api_key' => '',
                        'default_model' => 'qwen-vl-max',
                        'models' => ['qwen-vl-max'],
                    ],
                    'gemini' => [
                        'label' => 'Google Gemini',
                        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                        'api_key' => '',
                        'default_model' => 'gemini-2.0-flash',
                        'models' => ['gemini-2.0-flash'],
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['value', 'updated_at']);

        Cache::forget('configs');
    }
}
