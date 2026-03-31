<?php

namespace App\Jobs;

use App\Models\Image;
use App\Services\ImageIntelligence\ImageIntelligenceService;
use App\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeImageIntelligenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 420;
    public int $imageId;
    public ?int $runId;

    public function __construct(int $imageId, ?int $runId = null)
    {
        $this->imageId = $imageId;
        $this->runId = $runId && $runId > 0 ? $runId : null;
    }

    public function handle(
        ImageIntelligenceService $service,
        \App\Services\ImageIntelligence\ImageIntelligenceRunLedgerService $runLedger
    ): void
    {
        try {
            if (! Image::query()->whereKey($this->imageId)->exists()) {
                $service->cleanupMissingImageArtifacts($this->imageId);
                $runLedger->recordJobSkipped($this->runId, $this->imageId, '图片不存在或已删除，跳过 intelligence 分析');

                return;
            }

            $runLedger->markJobProcessing($this->runId, $this->imageId);
            $service->markProcessing($this->imageId);
            $record = $service->analyzeAndStore($this->imageId);
            if (! $record) {
                $service->cleanupMissingImageArtifacts($this->imageId);
                $runLedger->recordJobSkipped($this->runId, $this->imageId, '图片不存在或已删除，跳过 intelligence 分析');

                return;
            }

            $runLedger->recordJobSucceeded($this->runId, $this->imageId);
        } finally {
            $service->releaseDispatchLock($this->imageId);
        }
    }

    public function failed(\Throwable $e): void
    {
        try {
            $service = app(ImageIntelligenceService::class);
            $runLedger = app(\App\Services\ImageIntelligence\ImageIntelligenceRunLedgerService::class);
            $service->markFailed($this->imageId, $e);
            $runLedger->recordJobFailed($this->runId, $this->imageId, $e);
            $service->releaseDispatchLock($this->imageId);
        } catch (\Throwable $statusError) {
            Utils::e($statusError, '图像智能分析失败状态写回失败');
        }

        Utils::e($e, '图像智能分析任务失败');
    }
}
