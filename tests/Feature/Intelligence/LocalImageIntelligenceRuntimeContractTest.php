<?php

namespace Tests\Feature\Intelligence;

use App\Services\ImageIntelligence\LocalImageIntelligenceAnalyzer;
use Tests\TestCase;

class LocalImageIntelligenceRuntimeContractTest extends TestCase
{
    public function test_local_image_intelligence_script_is_versioned_in_repo(): void
    {
        $reflection = new \ReflectionClass(LocalImageIntelligenceAnalyzer::class);
        $relativePath = (string) $reflection->getConstant('SCRIPT_RELATIVE_PATH');

        $this->assertSame('scripts/image_intelligence/classify_ocr.py', $relativePath);
        $this->assertFileExists(base_path($relativePath));
        $this->assertFileExists(base_path('scripts/image_intelligence/requirements.txt'));
    }

    public function test_local_image_intelligence_python_requirements_are_pinned_for_runtime_compatibility(): void
    {
        $requirements = file(
            base_path('scripts/image_intelligence/requirements.txt'),
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        $this->assertIsArray($requirements);
        $this->assertContains('numpy==1.26.4', $requirements);
        $this->assertContains('onnxruntime==1.18.1', $requirements);
        $this->assertContains('transformers==4.26.1', $requirements);
        $this->assertContains('pytesseract==0.3.10', $requirements);
    }

    public function test_startup_health_check_supports_local_wd_tagger_rollout_contract(): void
    {
        $script = file_get_contents(base_path('scripts/health/startup.sh'));

        $this->assertIsString($script);
        $this->assertStringContainsString('LSKY_LOCAL_TAGGER_BACKEND:-wd_tagger', $script);
        $this->assertStringContainsString('LSKY_LOCAL_TAGGER_MODEL:-$HF_CACHE_ROOT/wd-vit-tagger-v3', $script);
        $this->assertStringContainsString('LSKY_LOCAL_LEGACY_VISION_MODEL', $script);
        $this->assertStringContainsString('LSKY_LOCAL_TAGGER_MODEL', $script);
        $this->assertStringContainsString('model.onnx', $script);
        $this->assertStringContainsString('selected_tags.csv', $script);
        $this->assertStringContainsString('onnxruntime', $script);
        $this->assertStringContainsString('transformers', $script);
        $this->assertStringNotContainsString('huggingface_hub', $script);
    }
}
