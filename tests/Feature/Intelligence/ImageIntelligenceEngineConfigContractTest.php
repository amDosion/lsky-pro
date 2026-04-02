<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ImageIntelligenceEngineConfigContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_image_intelligence_config_endpoint_returns_required_contract_fields(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/advanced-api/intelligence/config')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('engine', $response);
        $this->assertArrayHasKey('provider', $response);
        $this->assertArrayHasKey('model', $response);
        $this->assertArrayHasKey('enable_labels', $response);
        $this->assertArrayHasKey('enable_summary', $response);
        $this->assertArrayHasKey('enable_ocr_text', $response);
        $this->assertArrayHasKey('auto_on_upload', $response);
        $this->assertArrayHasKey('schedule_enabled', $response);
        $this->assertArrayHasKey('schedule_cron', $response);
        $this->assertArrayHasKey('retry_failed', $response);
        $this->assertArrayHasKey('provider_options', $response);
        $this->assertArrayHasKey('can_manage', $response);
    }
}
