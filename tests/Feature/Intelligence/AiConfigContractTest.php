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

    public function test_ai_config_page_explains_that_it_does_not_switch_intelligence_write_side(): void
    {
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->get('/advanced/ai-config')
            ->assertOk()
            ->assertSee('当前页只影响 AI 提示词与多模态能力配置')
            ->assertSee('不会切换当前图片 intelligence 的主写入链路');
    }
}
