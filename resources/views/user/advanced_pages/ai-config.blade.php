<x-app-layout>
    @section('title', 'AI 配置')

    <x-advanced-shell page="ai-config" title="AI 配置">
        <style>
            .ai-config-wrapper { display: grid; gap: 12px; max-width: 720px; }
            .ai-config-selector-row { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; }
            .ai-config-panel { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; overflow: hidden; }
            .ai-config-panel-head { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
            .ai-config-panel-title { font-size: 14px; font-weight: 700; color: #0f172a; }
            .ai-config-panel-sub { font-size: 12px; color: #64748b; }
            .ai-config-panel-body { padding: 14px; display: grid; gap: 12px; }
            .ai-config-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ai-config-span-2 { grid-column: 1 / -1; }
            .ai-config-status { margin-top: 10px; min-height: 36px; border: 1px solid #dbe2ea; border-radius: 10px; background: #f8fafc; padding: 8px 12px; font-size: 12px; color: #334155; display: flex; align-items: center; }
            .ai-config-status.success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
            .ai-config-status.error { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
            .ai-config-remote-panel { border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; padding: 12px; display: grid; gap: 10px; }
            .ai-config-remote-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
            .ai-config-remote-list { display: flex; flex-wrap: wrap; gap: 8px; }
            .ai-config-remote-item {
                min-height: 32px; padding: 0 12px; border-radius: 999px;
                border: 1px solid #cbd5e1; background: #fff; color: #334155;
                font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
            }
            .ai-config-remote-item.active { border-color: #93c5fd; background: #dbeafe; color: #1d4ed8; }
            .ai-config-remote-item.is-default { border-color: #fbbf24; background: #fef3c7; color: #92400e; }
            .ai-config-actions { display: flex; gap: 8px; flex-wrap: wrap; }
            .ai-config-checks { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ai-config-note { font-size: 12px; color: #475569; line-height: 1.7; }
            @media (max-width: 640px) {
                .ai-config-selector-row { grid-template-columns: 1fr; }
                .ai-config-grid { grid-template-columns: 1fr; }
                .ai-config-span-2 { grid-column: auto; }
                .ai-config-checks { grid-template-columns: 1fr; }
            }
        </style>

        <section class="ai-config-wrapper" id="ai-config-app">
            <section class="ai-config-panel">
                <div class="ai-config-panel-head">
                    <div>
                        <div class="ai-config-panel-title">能力边界</div>
                        <div class="ai-config-panel-sub">避免把当前页误解成 intelligence 主引擎切换器。</div>
                    </div>
                </div>
                <div class="ai-config-panel-body">
                    <div class="adv-toolbar-sub">
                        上方 provider 配置只影响 AI 提示词与多模态能力；管理员可在下方单独指定图片识别引擎，
                        用于控制上传后打 tag、摘要与 OCR 文本提取的正式写入链路。
                    </div>
                </div>
            </section>

            <div class="ai-config-selector-row">
                <label class="adv-field">
                    <span>编辑提供商</span>
                    <select id="ai-provider-select" class="adv-select"></select>
                </label>
                <label class="adv-field">
                    <span>当前启用提供商</span>
                    <select id="ai-active-provider" class="adv-select"></select>
                </label>
            </div>

            <section class="ai-config-panel">
                <div class="ai-config-panel-head">
                    <div>
                        <div class="ai-config-panel-title" id="ai-panel-title">等待加载</div>
                        <div class="ai-config-panel-sub" id="ai-panel-sub">从 API 加载配置</div>
                    </div>
                    <span class="adv-chip muted" id="ai-active-chip">未启用</span>
                </div>
                <div class="ai-config-panel-body">
                    <div class="ai-config-grid">
                        <label class="adv-field ai-config-span-2">
                            <span>API Key</span>
                            <input id="ai-api-key" class="adv-input adv-mono" placeholder="请输入当前提供商的 API Key" />
                        </label>
                        <label class="adv-field ai-config-span-2">
                            <span>Base URL</span>
                            <input id="ai-base-url" class="adv-input adv-mono" placeholder="请输入 API Base URL" />
                        </label>
                    </div>
                    <div class="ai-config-remote-panel">
                        <div class="ai-config-remote-head">
                            <div>
                                <div class="ai-config-panel-title" style="font-size:13px;">模型目录</div>
                                <div class="ai-config-panel-sub" id="ai-remote-status">单击切换选中，双击设为默认模型。</div>
                            </div>
                            <div class="ai-config-actions">
                                <button type="button" class="adv-btn" id="ai-models-fetch"><i class="fas fa-cloud-download-alt"></i>从 API 获取</button>
                                <button type="button" class="adv-btn" id="ai-models-reset"><i class="fas fa-undo"></i>恢复默认</button>
                                <span class="adv-chip muted" id="ai-remote-count">0 个模型</span>
                            </div>
                        </div>
                        <div class="ai-config-remote-list" id="ai-remote-models"></div>
                    </div>
                    <div class="ai-config-actions">
                        <button type="button" class="adv-btn primary" id="ai-config-save"><i class="fas fa-save"></i>保存此提供商配置</button>
                        <button type="button" class="adv-btn" id="ai-config-reload"><i class="fas fa-sync"></i>重新加载</button>
                    </div>
                    <div class="ai-config-status" id="ai-config-status">等待加载 AI 配置</div>
                </div>
            </section>

            @if(auth()->user()?->is_adminer)
                <section class="ai-config-panel" id="intelligence-config-panel">
                    <div class="ai-config-panel-head">
                        <div>
                            <div class="ai-config-panel-title">图片识别配置</div>
                            <div class="ai-config-panel-sub">独立决定上传自动识别、手动回填和定时回填使用的引擎。</div>
                        </div>
                        <span class="adv-chip muted" id="intelligence-engine-chip">等待加载</span>
                    </div>
                    <div class="ai-config-panel-body">
                        <div class="ai-config-note">
                            这里的配置不会改变上方“通用 AI 提供商”的编辑内容，但在 `provider` 模式下会复用已配置的 provider 凭证与模型目录。
                        </div>
                        <div class="ai-config-grid">
                            <label class="adv-field">
                                <span>识别引擎</span>
                                <select id="intelligence-engine" class="adv-select">
                                    <option value="disabled">关闭自动识别</option>
                                    <option value="local">本地引擎</option>
                                    <option value="provider">AI Provider</option>
                                </select>
                            </label>
                            <label class="adv-field">
                                <span>识别提供商</span>
                                <select id="intelligence-provider" class="adv-select"></select>
                            </label>
                            <label class="adv-field ai-config-span-2">
                                <span>识别模型</span>
                                <select id="intelligence-model" class="adv-select"></select>
                            </label>
                            <label class="adv-field">
                                <span>定时规则</span>
                                <input id="intelligence-schedule-cron" class="adv-input adv-mono" placeholder="例如 0 * * * *" />
                            </label>
                            <label class="adv-field">
                                <span>模式说明</span>
                                <input id="intelligence-mode-note" class="adv-input" type="text" value="provider 模式不会默认补跑本地 OCR 主链" readonly />
                            </label>
                        </div>
                        <div class="ai-config-checks">
                            <label class="adv-control-check">
                                <input id="intelligence-enable-labels" type="checkbox" />
                                <span>生成标签</span>
                            </label>
                            <label class="adv-control-check">
                                <input id="intelligence-enable-summary" type="checkbox" />
                                <span>生成摘要</span>
                            </label>
                            <label class="adv-control-check">
                                <input id="intelligence-enable-ocr-text" type="checkbox" />
                                <span>提取 OCR 文本</span>
                            </label>
                            <label class="adv-control-check">
                                <input id="intelligence-auto-on-upload" type="checkbox" />
                                <span>上传后自动识别</span>
                            </label>
                            <label class="adv-control-check">
                                <input id="intelligence-schedule-enabled" type="checkbox" />
                                <span>启用定时回填</span>
                            </label>
                            <label class="adv-control-check">
                                <input id="intelligence-retry-failed" type="checkbox" />
                                <span>重试失败任务</span>
                            </label>
                        </div>
                        <div class="ai-config-actions">
                            <button type="button" class="adv-btn primary" id="intelligence-config-save"><i class="fas fa-save"></i>保存图片识别配置</button>
                            <button type="button" class="adv-btn" id="intelligence-config-reload"><i class="fas fa-sync"></i>重新加载</button>
                        </div>
                        <div class="ai-config-status" id="intelligence-config-status">等待加载图片识别配置</div>
                    </div>
                </section>
            @endif
        </section>

        @push('scripts')
            <script>
                (() => {
                    const endpoint = '/advanced-api/ai/config';
                    const intelligenceEndpoint = '/advanced-api/intelligence/config';
                    const els = {
                        providerSelect: document.getElementById('ai-provider-select'),
                        panelTitle: document.getElementById('ai-panel-title'),
                        panelSub: document.getElementById('ai-panel-sub'),
                        activeChip: document.getElementById('ai-active-chip'),
                        activeProvider: document.getElementById('ai-active-provider'),
                        apiKey: document.getElementById('ai-api-key'),
                        baseUrl: document.getElementById('ai-base-url'),
                        modelsFetch: document.getElementById('ai-models-fetch'),
                        modelsReset: document.getElementById('ai-models-reset'),
                        remoteStatus: document.getElementById('ai-remote-status'),
                        remoteCount: document.getElementById('ai-remote-count'),
                        remoteModels: document.getElementById('ai-remote-models'),
                        save: document.getElementById('ai-config-save'),
                        reload: document.getElementById('ai-config-reload'),
                        status: document.getElementById('ai-config-status'),
                    };
                    const intelligenceEls = {
                        panel: document.getElementById('intelligence-config-panel'),
                        engine: document.getElementById('intelligence-engine'),
                        provider: document.getElementById('intelligence-provider'),
                        model: document.getElementById('intelligence-model'),
                        scheduleCron: document.getElementById('intelligence-schedule-cron'),
                        enableLabels: document.getElementById('intelligence-enable-labels'),
                        enableSummary: document.getElementById('intelligence-enable-summary'),
                        enableOcrText: document.getElementById('intelligence-enable-ocr-text'),
                        autoOnUpload: document.getElementById('intelligence-auto-on-upload'),
                        scheduleEnabled: document.getElementById('intelligence-schedule-enabled'),
                        retryFailed: document.getElementById('intelligence-retry-failed'),
                        save: document.getElementById('intelligence-config-save'),
                        reload: document.getElementById('intelligence-config-reload'),
                        status: document.getElementById('intelligence-config-status'),
                        engineChip: document.getElementById('intelligence-engine-chip'),
                    };

                    const defaultModels = {
                        gpt: ['gpt-4.1-mini', 'gpt-4.1', 'gpt-4o-mini'],
                        deepseek: ['deepseek-chat', 'deepseek-reasoner'],
                        qwen: ['qwen-vl-max', 'qwen-vl-plus', 'qwen2.5-vl-72b-instruct'],
                        gemini: ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'],
                    };

                    const state = { editingProvider: 'gpt', activeProvider: 'gpt', providers: {}, loading: false };
                    const intelligenceState = {
                        loading: false,
                        config: {
                            engine: 'local',
                            provider: 'gpt',
                            model: '',
                            enable_labels: true,
                            enable_summary: true,
                            enable_ocr_text: true,
                            auto_on_upload: true,
                            schedule_enabled: false,
                            schedule_cron: '0 * * * *',
                            retry_failed: true,
                        },
                    };

                    const setStatus = (msg, type) => {
                        els.status.textContent = msg || '';
                        els.status.classList.remove('success', 'error');
                        if (type) els.status.classList.add(type);
                    };
                    const setIntelligenceStatus = (msg, type) => {
                        if (!intelligenceEls.status) return;
                        intelligenceEls.status.textContent = msg || '';
                        intelligenceEls.status.classList.remove('success', 'error');
                        if (type) intelligenceEls.status.classList.add(type);
                    };

                    const normModels = (v) => {
                        const rows = Array.isArray(v) ? v : String(v || '').split(/\n+/g);
                        return [...new Set(rows.map(s => String(s || '').trim()).filter(Boolean))];
                    };

                    const cur = () => state.providers[state.editingProvider] || null;

                    const setLoading = (on) => {
                        state.loading = on;
                        [els.providerSelect, els.activeProvider, els.apiKey, els.baseUrl, els.modelsFetch, els.modelsReset, els.save, els.reload].forEach(el => { if (el) el.disabled = on; });
                        els.remoteModels.style.pointerEvents = on ? 'none' : 'auto';
                        els.remoteModels.style.opacity = on ? '.65' : '1';
                    };
                    const setIntelligenceLoading = (on) => {
                        intelligenceState.loading = on;
                        [
                            intelligenceEls.engine,
                            intelligenceEls.provider,
                            intelligenceEls.model,
                            intelligenceEls.scheduleCron,
                            intelligenceEls.enableLabels,
                            intelligenceEls.enableSummary,
                            intelligenceEls.enableOcrText,
                            intelligenceEls.autoOnUpload,
                            intelligenceEls.scheduleEnabled,
                            intelligenceEls.retryFailed,
                            intelligenceEls.save,
                            intelligenceEls.reload,
                        ].forEach(el => { if (el) el.disabled = on; });
                    };

                    const syncDropdowns = () => {
                        els.providerSelect.textContent = '';
                        Object.entries(state.providers).forEach(([p, item]) => {
                            const opt = document.createElement('option');
                            opt.value = p;
                            opt.textContent = (item.label || p) + (item.ready ? ' \u2713' : '');
                            opt.selected = (p === state.editingProvider);
                            els.providerSelect.appendChild(opt);
                        });
                        els.activeProvider.textContent = '';
                        Object.entries(state.providers).forEach(([p, item]) => {
                            const opt = document.createElement('option');
                            opt.value = p;
                            opt.textContent = item.label || p;
                            opt.selected = (p === state.activeProvider);
                            els.activeProvider.appendChild(opt);
                        });
                    };
                    const normalizeIntelligenceConfig = (input) => {
                        const config = input || {};
                        return {
                            engine: ['disabled', 'local', 'provider'].includes(String(config.engine || '').trim()) ? String(config.engine).trim() : 'local',
                            provider: String(config.provider || state.activeProvider || Object.keys(state.providers)[0] || 'gpt').trim() || 'gpt',
                            model: String(config.model || '').trim(),
                            enable_labels: Boolean(config.enable_labels),
                            enable_summary: Boolean(config.enable_summary),
                            enable_ocr_text: Boolean(config.enable_ocr_text),
                            auto_on_upload: Boolean(config.auto_on_upload),
                            schedule_enabled: Boolean(config.schedule_enabled),
                            schedule_cron: String(config.schedule_cron || '0 * * * *').trim() || '0 * * * *',
                            retry_failed: Boolean(config.retry_failed),
                        };
                    };
                    const syncIntelligenceProviderOptions = () => {
                        if (!intelligenceEls.provider) return;
                        const current = intelligenceState.config.provider;
                        intelligenceEls.provider.textContent = '';
                        Object.entries(state.providers).forEach(([provider, item]) => {
                            const opt = document.createElement('option');
                            opt.value = provider;
                            opt.textContent = item.label || provider;
                            opt.selected = provider === current;
                            intelligenceEls.provider.appendChild(opt);
                        });
                    };
                    const syncIntelligenceModelOptions = () => {
                        if (!intelligenceEls.model) return;
                        const providerKey = intelligenceState.config.provider;
                        const provider = state.providers[providerKey] || null;
                        const models = provider
                            ? normModels(provider.remote_models && provider.remote_models.length ? provider.remote_models : provider.models)
                            : [];
                        const modelValue = intelligenceState.config.model;
                        intelligenceEls.model.textContent = '';
                        if (models.length === 0 && modelValue === '') {
                            const opt = document.createElement('option');
                            opt.value = '';
                            opt.textContent = '暂无模型目录';
                            intelligenceEls.model.appendChild(opt);
                            return;
                        }
                        const modelOptions = modelValue !== '' && !models.includes(modelValue)
                            ? [modelValue, ...models]
                            : models;
                        modelOptions.forEach((model) => {
                            const opt = document.createElement('option');
                            opt.value = model;
                            opt.textContent = model;
                            opt.selected = model === modelValue;
                            intelligenceEls.model.appendChild(opt);
                        });
                    };
                    const syncIntelligenceForm = () => {
                        if (!intelligenceEls.panel) return;
                        syncIntelligenceProviderOptions();
                        syncIntelligenceModelOptions();
                        intelligenceEls.engine.value = intelligenceState.config.engine;
                        intelligenceEls.provider.value = intelligenceState.config.provider;
                        intelligenceEls.model.value = intelligenceState.config.model;
                        intelligenceEls.scheduleCron.value = intelligenceState.config.schedule_cron;
                        intelligenceEls.enableLabels.checked = intelligenceState.config.enable_labels;
                        intelligenceEls.enableSummary.checked = intelligenceState.config.enable_summary;
                        intelligenceEls.enableOcrText.checked = intelligenceState.config.enable_ocr_text;
                        intelligenceEls.autoOnUpload.checked = intelligenceState.config.auto_on_upload;
                        intelligenceEls.scheduleEnabled.checked = intelligenceState.config.schedule_enabled;
                        intelligenceEls.retryFailed.checked = intelligenceState.config.retry_failed;
                        const labels = {
                            disabled: '已关闭',
                            local: '本地引擎',
                            provider: 'AI Provider',
                        };
                        const chipLabel = labels[intelligenceState.config.engine] || '待定';
                        intelligenceEls.engineChip.textContent = chipLabel;
                        intelligenceEls.engineChip.className = 'adv-chip ' + (intelligenceState.config.engine === 'disabled' ? 'warn' : 'success');
                    };
                    const collectIntelligenceForm = () => {
                        if (!intelligenceEls.panel) return;
                        intelligenceState.config = normalizeIntelligenceConfig({
                            engine: intelligenceEls.engine.value,
                            provider: intelligenceEls.provider.value,
                            model: intelligenceEls.model.value,
                            enable_labels: intelligenceEls.enableLabels.checked,
                            enable_summary: intelligenceEls.enableSummary.checked,
                            enable_ocr_text: intelligenceEls.enableOcrText.checked,
                            auto_on_upload: intelligenceEls.autoOnUpload.checked,
                            schedule_enabled: intelligenceEls.scheduleEnabled.checked,
                            schedule_cron: intelligenceEls.scheduleCron.value,
                            retry_failed: intelligenceEls.retryFailed.checked,
                        });
                    };
                    const loadIntelligenceConfig = async () => {
                        if (!intelligenceEls.panel) return;
                        setIntelligenceLoading(true);
                        setIntelligenceStatus('正在加载图片识别配置');
                        try {
                            const { data } = await axios.get(intelligenceEndpoint);
                            if (!data?.status) throw new Error(data?.message || '加载失败');
                            intelligenceState.config = normalizeIntelligenceConfig(data?.data?.config || data?.data || {});
                            syncIntelligenceForm();
                            setIntelligenceStatus('图片识别配置已加载', 'success');
                        } catch (err) {
                            setIntelligenceStatus(err?.response?.data?.message || err?.message || '加载失败', 'error');
                            if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                        } finally {
                            setIntelligenceLoading(false);
                        }
                    };

                    const syncRemote = () => {
                        const item = cur();
                        if (!item) return;
                        const selected = normModels(item.models);
                        const remote = normModels(item.remote_models && item.remote_models.length ? item.remote_models : item.models);
                        els.remoteCount.textContent = remote.length + ' 个模型';
                        els.remoteStatus.textContent = remote.length ? '单击切换选中，双击设为默认模型。' : '当前还没有模型目录，可先点击"从 API 获取"。';
                        els.remoteModels.textContent = '';
                        if (remote.length) {
                            remote.forEach(m => {
                                const isSelected = selected.includes(m);
                                const isDefault = m === item.default_model;
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ai-config-remote-item' + (isDefault ? ' is-default' : isSelected ? ' active' : '');
                                btn.setAttribute('data-remote-model', m);
                                const icon = document.createElement('i');
                                icon.className = 'fas ' + (isDefault ? 'fa-star' : isSelected ? 'fa-check-circle' : 'fa-circle');
                                const label = document.createElement('span');
                                label.textContent = m;
                                btn.appendChild(icon);
                                btn.appendChild(label);
                                els.remoteModels.appendChild(btn);
                            });
                        } else {
                            const empty = document.createElement('span');
                            empty.className = 'ai-config-panel-sub';
                            empty.textContent = '暂无模型目录';
                            els.remoteModels.appendChild(empty);
                        }
                    };

                    const syncForm = () => {
                        const item = cur();
                        if (!item) return;
                        const models = normModels(item.models);
                        item.models = models;
                        if (!models.includes(item.default_model)) item.default_model = models[0] || '';
                        els.panelTitle.textContent = (item.label || state.editingProvider) + ' 配置';
                        els.panelSub.textContent = (item.transport === 'gemini' ? 'Gemini 原生接口' : 'OpenAI Compatible');
                        const isActive = state.editingProvider === state.activeProvider;
                        els.activeChip.textContent = isActive ? '启用中' : '未启用';
                        els.activeChip.className = 'adv-chip ' + (isActive && item.ready ? 'success' : 'warn');
                        els.apiKey.value = item.api_key || '';
                        els.baseUrl.value = item.base_url || '';
                        syncRemote();
                    };

                    const collectForm = () => {
                        const item = cur();
                        if (!item) return;
                        item.api_key = String(els.apiKey.value || '').trim();
                        item.base_url = String(els.baseUrl.value || '').trim();
                        item.ready = item.api_key !== '' && (item.default_model || '') !== '';
                    };

                    const syncAll = () => {
                        syncDropdowns();
                        syncForm();
                        if (intelligenceEls.panel) {
                            if (!state.providers[intelligenceState.config.provider]) {
                                intelligenceState.config.provider = state.activeProvider || Object.keys(state.providers)[0] || 'gpt';
                            }
                            const provider = state.providers[intelligenceState.config.provider];
                            const models = provider ? normModels(provider.remote_models && provider.remote_models.length ? provider.remote_models : provider.models) : [];
                            if (models.length > 0 && !models.includes(intelligenceState.config.model)) {
                                intelligenceState.config.model = intelligenceState.config.model || models[0];
                            }
                            syncIntelligenceForm();
                        }
                    };

                    const load = async () => {
                        setLoading(true);
                        setStatus('正在加载 AI 配置');
                        try {
                            const { data } = await axios.get(endpoint);
                            if (!data?.status) throw new Error(data?.message || '加载失败');
                            state.activeProvider = String(data?.data?.active_provider || 'gpt');
                            state.providers = data?.data?.providers || {};
                            if (!state.providers[state.editingProvider]) state.editingProvider = Object.keys(state.providers)[0] || 'gpt';
                            syncAll();
                            setStatus('AI 配置已加载', 'success');
                            if (intelligenceEls.panel) {
                                await loadIntelligenceConfig();
                            }
                        } catch (err) {
                            setStatus(err?.response?.data?.message || err?.message || '加载失败', 'error');
                            if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                        } finally { setLoading(false); }
                    };

                    els.providerSelect.addEventListener('change', () => {
                        collectForm();
                        state.editingProvider = String(els.providerSelect.value || 'gpt');
                        syncAll();
                    });

                    els.activeProvider.addEventListener('change', async () => {
                        const newActive = String(els.activeProvider.value || 'gpt');
                        setLoading(true);
                        setStatus('正在切换启用提供商');
                        try {
                            const { data } = await axios.put(endpoint + '/active', { active_provider: newActive });
                            if (!data?.status) throw new Error(data?.message || '切换失败');
                            state.activeProvider = String(data?.data?.active_provider || newActive);
                            state.providers = data?.data?.providers || state.providers;
                            syncAll();
                            setStatus('已切换启用提供商为 ' + (state.providers[state.activeProvider]?.label || state.activeProvider), 'success');
                            if (window.toastr) window.toastr.success('启用提供商已切换');
                        } catch (err) {
                            setStatus(err?.response?.data?.message || err?.message || '切换失败', 'error');
                            if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                        } finally { setLoading(false); }
                    });

                    els.apiKey.addEventListener('input', () => {
                        const item = cur();
                        if (!item) return;
                        item.api_key = String(els.apiKey.value || '').trim();
                        item.ready = item.api_key !== '' && (item.default_model || '') !== '';
                    });

                    els.baseUrl.addEventListener('input', () => {
                        const item = cur();
                        if (!item) return;
                        item.base_url = String(els.baseUrl.value || '').trim();
                    });
                    if (intelligenceEls.engine) {
                        intelligenceEls.engine.addEventListener('change', () => {
                            collectIntelligenceForm();
                            syncIntelligenceForm();
                        });
                    }
                    if (intelligenceEls.provider) {
                        intelligenceEls.provider.addEventListener('change', () => {
                            collectIntelligenceForm();
                            const provider = state.providers[intelligenceState.config.provider];
                            const models = provider ? normModels(provider.remote_models && provider.remote_models.length ? provider.remote_models : provider.models) : [];
                            if (models.length > 0 && !models.includes(intelligenceState.config.model)) {
                                intelligenceState.config.model = models[0];
                            }
                            syncIntelligenceForm();
                        });
                    }
                    if (intelligenceEls.model) {
                        intelligenceEls.model.addEventListener('change', () => {
                            collectIntelligenceForm();
                        });
                    }
                    if (intelligenceEls.scheduleCron) {
                        intelligenceEls.scheduleCron.addEventListener('input', () => {
                            collectIntelligenceForm();
                        });
                    }
                    [
                        intelligenceEls.enableLabels,
                        intelligenceEls.enableSummary,
                        intelligenceEls.enableOcrText,
                        intelligenceEls.autoOnUpload,
                        intelligenceEls.scheduleEnabled,
                        intelligenceEls.retryFailed,
                    ].forEach((el) => {
                        if (!el) return;
                        el.addEventListener('change', () => {
                            collectIntelligenceForm();
                            syncIntelligenceForm();
                        });
                    });

                    els.modelsReset.addEventListener('click', () => {
                        const item = cur();
                        if (!item) return;
                        item.models = [...(defaultModels[state.editingProvider] || [])];
                        item.remote_models = [...item.models];
                        item.default_model = item.models[0] || '';
                        syncForm();
                    });

                    // Single click: toggle selection
                    els.remoteModels.addEventListener('click', (e) => {
                        const btn = e.target.closest('[data-remote-model]');
                        if (!btn) return;
                        const item = cur();
                        if (!item) return;
                        const model = String(btn.getAttribute('data-remote-model') || '').trim();
                        if (!model) return;
                        const selected = normModels(item.models);
                        item.models = selected.includes(model) ? selected.filter(m => m !== model) : [...selected, model];
                        if (!item.models.includes(item.default_model)) item.default_model = item.models[0] || '';
                        item.ready = (item.api_key || '') !== '' && (item.default_model || '') !== '';
                        syncForm();
                    });

                    // Double click: set as default model
                    els.remoteModels.addEventListener('dblclick', (e) => {
                        const btn = e.target.closest('[data-remote-model]');
                        if (!btn) return;
                        const item = cur();
                        if (!item) return;
                        const model = String(btn.getAttribute('data-remote-model') || '').trim();
                        if (!model) return;
                        const selected = normModels(item.models);
                        if (!selected.includes(model)) {
                            item.models = [...selected, model];
                        }
                        item.default_model = model;
                        item.ready = (item.api_key || '') !== '' && model !== '';
                        syncForm();
                        if (window.toastr) window.toastr.info('已将 ' + model + ' 设为默认模型');
                    });

                    els.modelsFetch.addEventListener('click', async () => {
                        const provider = state.editingProvider;
                        if (!provider) return;
                        collectForm();
                        setLoading(true);
                        setStatus('正在从 ' + provider + ' 获取模型列表');
                        try {
                            const { data } = await axios.post('/advanced-api/ai/config/providers/' + encodeURIComponent(provider) + '/models:fetch', {
                                api_key: String(els.apiKey.value || '').trim(),
                                base_url: String(els.baseUrl.value || '').trim(),
                            });
                            if (!data?.status) throw new Error(data?.message || '模型列表获取失败');
                            const payload = data.data || {};
                            const item = cur();
                            if (!item) throw new Error('当前提供商不存在');
                            item.remote_models = normModels(payload.models);
                            item.models = normModels(payload.selected_models);
                            item.default_model = String(payload.default_model || item.models[0] || '').trim();
                            item.ready = (item.api_key || '') !== '' && (item.default_model || '') !== '';
                            syncAll();
                            setStatus('已获取 ' + (payload.count || 0) + ' 个模型', 'success');
                            if (window.toastr) window.toastr.success('远端模型列表已同步');
                        } catch (err) {
                            setStatus(err?.response?.data?.message || err?.message || '模型列表获取失败', 'error');
                            if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                        } finally { setLoading(false); }
                    });

                    els.reload.addEventListener('click', load);
                    if (intelligenceEls.reload) {
                        intelligenceEls.reload.addEventListener('click', loadIntelligenceConfig);
                    }

                    els.save.addEventListener('click', async () => {
                        try {
                            collectForm();
                            const item = cur();
                            if (!item) throw new Error('未找到当前提供商');
                            setLoading(true);
                            setStatus('正在保存 ' + (item.label || state.editingProvider) + ' 配置');
                            const { data } = await axios.put(endpoint, {
                                provider: state.editingProvider,
                                api_key: item.api_key || '',
                                base_url: item.base_url || '',
                                default_model: item.default_model || '',
                                models: normModels(item.models),
                            });
                            if (!data?.status) throw new Error(data?.message || '保存失败');
                            state.activeProvider = String(data?.data?.active_provider || state.activeProvider);
                            state.providers = data?.data?.providers || state.providers;
                            syncAll();
                            setStatus((item.label || state.editingProvider) + ' 配置已保存', 'success');
                            if (window.toastr) window.toastr.success('配置已保存');
                        } catch (err) {
                            setStatus(err?.response?.data?.message || err?.message || '保存失败', 'error');
                            if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                        } finally { setLoading(false); }
                    });
                    if (intelligenceEls.save) {
                        intelligenceEls.save.addEventListener('click', async () => {
                            try {
                                collectIntelligenceForm();
                                setIntelligenceLoading(true);
                                setIntelligenceStatus('正在保存图片识别配置');
                                const { data } = await axios.put(intelligenceEndpoint, intelligenceState.config);
                                if (!data?.status) throw new Error(data?.message || '保存失败');
                                intelligenceState.config = normalizeIntelligenceConfig(data?.data?.config || data?.data || intelligenceState.config);
                                syncIntelligenceForm();
                                setIntelligenceStatus('图片识别配置已保存', 'success');
                                if (window.toastr) window.toastr.success('图片识别配置已保存');
                            } catch (err) {
                                setIntelligenceStatus(err?.response?.data?.message || err?.message || '保存失败', 'error');
                                if (window.toastr) window.toastr.error(err?.response?.data?.message || err?.message);
                            } finally {
                                setIntelligenceLoading(false);
                            }
                        });
                    }

                    load();
                })();
            </script>
        @endpush
    </x-advanced-shell>
</x-app-layout>
