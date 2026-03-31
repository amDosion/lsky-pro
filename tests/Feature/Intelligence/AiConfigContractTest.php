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

    public function test_ai_config_page_explains_local_first_with_provider_fallback_boundary(): void
    {
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->get('/advanced/ai-config')
            ->assertOk()
            ->assertSee('当前页只影响 AI 提示词与多模态能力配置')
            ->assertSee('系统优先使用本地分析链路')
            ->assertSee('本地分析不可用时可使用已配置的多模态 provider 作为降级补位');
    }
}
