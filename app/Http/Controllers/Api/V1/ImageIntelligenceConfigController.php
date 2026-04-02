<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageIntelligence\ImageIntelligenceConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ImageIntelligenceConfigController extends Controller
{
    public function show(ImageIntelligenceConfigService $service): Response
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->success('success', $service->forUser($user));
    }

    public function update(Request $request, ImageIntelligenceConfigService $service): Response
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->is_adminer) {
            return $this->fail('仅管理员可修改图片识别配置')->setStatusCode(403);
        }

        try {
            $validated = $request->validate([
                'engine' => 'required|string|in:disabled,local,provider',
                'provider' => 'nullable|string|in:gpt,deepseek,qwen,gemini',
                'model' => 'nullable|string|max:120',
                'enable_labels' => 'nullable|boolean',
                'enable_summary' => 'nullable|boolean',
                'enable_ocr_text' => 'nullable|boolean',
                'auto_on_upload' => 'nullable|boolean',
                'schedule_enabled' => 'nullable|boolean',
                'schedule_cron' => 'nullable|string|max:120',
                'retry_failed' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        try {
            $config = $service->save($user, $validated);
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success('图片识别配置已保存', $config);
    }
}
