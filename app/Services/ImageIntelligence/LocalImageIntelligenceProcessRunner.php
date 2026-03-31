<?php

namespace App\Services\ImageIntelligence;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class LocalImageIntelligenceProcessRunner
{
    /**
     * @return array<string, mixed>
     */
    public function run(string $scriptPath, string $filePath, string $originName = '', int $top = 3): array
    {
        $process = new Process(
            ['python3', $scriptPath, $filePath, '--top', (string) max($top, 1), '--origin-name', $originName],
            base_path(),
            $this->environmentOverrides()
        );

        $process->setTimeout($this->timeoutSeconds());

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            throw new \RuntimeException('本地图像分析脚本执行超时: '.$e->getMessage(), 0, $e);
        }

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($this->buildFailureMessage($process->getExitCode(), $errorOutput, $output));
        }

        if ($output === '') {
            throw new \RuntimeException($errorOutput !== '' ? $errorOutput : '本地图像分析脚本未返回结果');
        }

        $decoded = json_decode($output, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('本地图像分析脚本返回了无效 JSON');
        }

        if (isset($decoded['error'])) {
            throw new \RuntimeException('本地图像分析失败: '.trim((string) $decoded['error']));
        }

        return $decoded;
    }

    /**
     * @return array<string, string>
     */
    private function environmentOverrides(): array
    {
        return [
            'HF_HOME' => (string) env('HF_HOME', '/opt/models/huggingface'),
            'OMP_NUM_THREADS' => '1',
            'OPENBLAS_NUM_THREADS' => '1',
            'MKL_NUM_THREADS' => '1',
            'NUMEXPR_NUM_THREADS' => '1',
            'TOKENIZERS_PARALLELISM' => 'false',
            'TRANSFORMERS_NO_ADVISORY_WARNINGS' => '1',
        ];
    }

    private function timeoutSeconds(): int
    {
        return max((int) env('LSKY_LOCAL_INTELLIGENCE_PROCESS_TIMEOUT', 240), 15);
    }

    private function buildFailureMessage(?int $exitCode, string $errorOutput, string $output): string
    {
        $prefix = '本地图像分析脚本执行失败';
        $detail = $errorOutput !== '' ? $errorOutput : $output;
        $detail = mb_substr(trim($detail), 0, 500);

        if ($detail === '') {
            return $prefix.'，退出码='.$this->formatExitCode($exitCode);
        }

        return sprintf('%s，退出码=%s: %s', $prefix, $this->formatExitCode($exitCode), $detail);
    }

    private function formatExitCode(?int $exitCode): string
    {
        return $exitCode === null ? 'unknown' : (string) $exitCode;
    }
}
