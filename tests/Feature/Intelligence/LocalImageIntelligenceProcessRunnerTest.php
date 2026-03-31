<?php

namespace Tests\Feature\Intelligence;

use App\Services\ImageIntelligence\LocalImageIntelligenceProcessRunner;
use Tests\TestCase;

class LocalImageIntelligenceProcessRunnerTest extends TestCase
{
    public function test_runner_can_capture_large_stderr_without_deadlocking(): void
    {
        $script = $this->makePythonScript(<<<'PY'
import argparse
import json
import sys

parser = argparse.ArgumentParser()
parser.add_argument("image_path")
parser.add_argument("--top", type=int, default=3)
parser.add_argument("--origin-name", default="")
parser.parse_args()

sys.stderr.write("warning:" + ("x" * 70000))
sys.stderr.flush()
print(json.dumps({
    "caption": "一张包含袜子的图片",
    "labels": ["袜子", "服饰"],
    "keywords": ["袜子", "sock"],
    "ocr_text": "",
    "classifications": [{"zh": "袜子", "en": "sock", "confidence": 0.91}],
    "elapsed_ms": 12,
}, ensure_ascii=False))
PY
        );

        $runner = new LocalImageIntelligenceProcessRunner();
        $payload = $runner->run($script, '/tmp/dummy-image.png', 'image_pink_sock_model.jpeg', 3);

        $this->assertSame(['袜子', '服饰'], $payload['labels']);
        $this->assertSame(['袜子', 'sock'], $payload['keywords']);
    }

    public function test_runner_surfaces_process_timeout_clearly(): void
    {
        $previousTimeout = getenv('LSKY_LOCAL_INTELLIGENCE_PROCESS_TIMEOUT');
        putenv('LSKY_LOCAL_INTELLIGENCE_PROCESS_TIMEOUT=15');

        $script = $this->makePythonScript(<<<'PY'
import argparse
import time

parser = argparse.ArgumentParser()
parser.add_argument("image_path")
parser.add_argument("--top", type=int, default=3)
parser.add_argument("--origin-name", default="")
parser.parse_args()

time.sleep(16)
print("{}")
PY
        );

        try {
            $runner = new LocalImageIntelligenceProcessRunner();

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('执行超时');

            $runner->run($script, '/tmp/dummy-image.png', '', 3);
        } finally {
            if ($previousTimeout === false) {
                putenv('LSKY_LOCAL_INTELLIGENCE_PROCESS_TIMEOUT');
            } else {
                putenv('LSKY_LOCAL_INTELLIGENCE_PROCESS_TIMEOUT='.$previousTimeout);
            }
        }
    }

    public function test_real_script_can_project_searchable_terms_from_origin_name_hint(): void
    {
        if (! shell_exec('command -v python3')) {
            $this->markTestSkipped('python3 runtime is not available');
        }

        $imagePath = storage_path('framework/testing/'.uniqid('local-intelligence-origin-', true).'.png');
        file_put_contents(
            $imagePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAIAAAAmkwkpAAAAFElEQVR4nGP8f+A0AwwwMSAB3BwAgCwCkoAWUnkAAAAASUVORK5CYII=')
        );

        $runner = new LocalImageIntelligenceProcessRunner();
        $payload = $runner->run(
            base_path('scripts/image_intelligence/classify_ocr.py'),
            $imagePath,
            'image_pink_sock_model.jpeg',
            3
        );

        $this->assertContains('袜子', $payload['labels']);
        $this->assertContains('sock', $payload['keywords']);
    }

    private function makePythonScript(string $contents): string
    {
        $path = storage_path('framework/testing/'.uniqid('local-intelligence-runner-', true).'.py');
        file_put_contents($path, $contents);

        return $path;
    }
}
