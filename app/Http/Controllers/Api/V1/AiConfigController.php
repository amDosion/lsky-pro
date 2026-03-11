<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AiProviderConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AiConfigController extends Controller
{
    public function show(AiProviderConfigService $service): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_adminer) {
            return $this->fail('仅管理员可查看 AI 配置');
        }

        return $this->success('success', $service->all());
    }

    public function update(Request $request, AiProviderConfigService $service): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_adminer) {
            return $this->fail('仅管理员可修改 AI 配置');
        }

        try {
            $validated = $request->validate([
                'active_provider' => 'required|string|in:gpt,deepseek,qwen,gemini',
                'providers' => 'required|array',
                'providers.gpt.api_key' => 'nullable|string|max:500',
                'providers.gpt.base_url' => 'nullable|string|max:500',
                'providers.gpt.default_model' => 'nullable|string|max:120',
                'providers.gpt.models' => 'nullable|array|max:20',
                'providers.gpt.models.*' => 'nullable|string|max:120',
                'providers.deepseek.api_key' => 'nullable|string|max:500',
                'providers.deepseek.base_url' => 'nullable|string|max:500',
                'providers.deepseek.default_model' => 'nullable|string|max:120',
                'providers.deepseek.models' => 'nullable|array|max:20',
                'providers.deepseek.models.*' => 'nullable|string|max:120',
                'providers.qwen.api_key' => 'nullable|string|max:500',
                'providers.qwen.base_url' => 'nullable|string|max:500',
                'providers.qwen.default_model' => 'nullable|string|max:120',
                'providers.qwen.models' => 'nullable|array|max:20',
                'providers.qwen.models.*' => 'nullable|string|max:120',
                'providers.gemini.api_key' => 'nullable|string|max:500',
                'providers.gemini.base_url' => 'nullable|string|max:500',
                'providers.gemini.default_model' => 'nullable|string|max:120',
                'providers.gemini.models' => 'nullable|array|max:20',
                'providers.gemini.models.*' => 'nullable|string|max:120',
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        $config = $service->save($validated);

        return $this->success('AI 配置保存成功', $config);
    }

    public function models(Request $request, string $provider, AiProviderConfigService $service): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_adminer) {
            return $this->fail('仅管理员可获取模型列表');
        }

        try {
            $validated = $request->validate([
                'api_key' => 'nullable|string|max:500',
                'base_url' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        try {
            $payload = $service->fetchModels(
                $provider,
                $validated['api_key'] ?? null,
                $validated['base_url'] ?? null
            );
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success('模型列表获取成功', $payload);
    }
}
