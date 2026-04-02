<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AiConfigContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_admin_ai_config_page_shows_provider_editor_and_image_intelligence_panel(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)
            ->get('/advanced/ai-config')
            ->assertOk()
            ->assertSee('上方 provider 配置只影响 AI 提示词与多模态能力')
            ->assertSee('编辑提供商')
            ->assertSee('当前启用提供商')
            ->assertSee('图片识别配置')
            ->assertSee('识别引擎')
            ->assertSee('识别提供商')
            ->assertSee('上传后自动识别');
    }

    public function test_non_admin_ai_config_page_does_not_render_image_intelligence_panel(): void
    {
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->get('/advanced/ai-config')
            ->assertOk()
            ->assertSee('编辑提供商')
            ->assertDontSee('图片识别配置')
            ->assertDontSee('识别引擎')
            ->assertDontSee('上传后自动识别');
    }
}
