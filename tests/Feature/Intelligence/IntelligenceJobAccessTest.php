<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IntelligenceJobAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('configs');
    }

    public function test_non_admin_cannot_open_intelligence_jobs_page(): void
    {
        $user = $this->createTestUser([
            'is_adminer' => false,
        ]);

        $this->actingAs($user)
            ->get('/advanced/jobs')
            ->assertForbidden();
    }

    public function test_admin_can_open_intelligence_jobs_page(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)
            ->get('/advanced/jobs')
            ->assertOk()
            ->assertSee('作业中心')
            ->assertSee('单图立即重识别')
            ->assertSee('定时回填');
    }

    public function test_admin_images_page_contains_single_image_reanalyze_entry_hook(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)
            ->get('/images')
            ->assertOk()
            ->assertSee('single-intelligence-dispatch', false)
            ->assertSee('立即重识别');
    }

    public function test_regular_user_can_still_read_personal_intelligence_status(): void
    {
        $user = $this->createTestUser([
            'is_adminer' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/advanced-api/intelligence/status')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_legacy_global_intelligence_job_api_routes_are_not_exposed(): void
    {
        $admin = $this->createTestUser([
            'is_adminer' => true,
        ]);

        $this->actingAs($admin)->getJson('/advanced-api/intelligence/job-status')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-start')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-pause')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-resume')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-stop')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-clear')->assertNotFound();
        $this->actingAs($admin)->getJson('/advanced-api/intelligence/job-logs')->assertNotFound();
        $this->actingAs($admin)->getJson('/advanced-api/intelligence/job-schedule')->assertNotFound();
        $this->actingAs($admin)->postJson('/advanced-api/intelligence/job-schedule')->assertNotFound();
    }
}
