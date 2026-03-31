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
}
