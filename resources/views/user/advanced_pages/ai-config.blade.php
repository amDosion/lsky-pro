<x-app-layout>
    @section('title', 'AI 配置')

    <x-advanced-shell page="ai-config" title="AI 配置">
        <style>
            .ai-config-layout { display: grid; gap: 12px; grid-template-columns: minmax(260px, 320px) minmax(0, 1fr); }
            .ai-config-card-grid { display: grid; gap: 8px; }
            .ai-config-card {
                border: 1px solid #dbe2ea;
                border-radius: 12px;
                background: #fff;
                padding: 12px;
                display: grid;
                gap: 8px;
                cursor: pointer;
                transition: .18s ease;
            }
            .ai-config-card:hover { border-color: #bfdbfe; background: #f8fbff; }
            .ai-config-card.active { border-color: #93c5fd; background: #eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
            .ai-config-card-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
            .ai-config-card-name { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a; }
            .ai-config-card-meta { font-size: 12px; color: #64748b; line-height: 1.6; }
            .ai-config-card-model { font-size: 12px; color: #334155; }
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
            .ai-config-model-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: -4px; }
            .ai-config-model-help { font-size: 12px; color: #64748b; }
            .ai-config-chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
            .ai-config-chip { min-height: 28px; padding: 0 10px; border-radius: 999px; border: 1px solid #dbe2ea; background: #f8fafc; color: #475569; font-size: 11px; display: inline-flex; align-items: center; gap: 6px; }
            .ai-config-chip.ok { border-color: #86efac; background: #dcfce7; color: #166534; }
            .ai-config-chip.warn { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
            .ai-config-remote-panel { border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; padding: 12px; display: grid; gap: 10px; }
            .ai-config-remote-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
            .ai-config-remote-list { display: flex; flex-wrap: wrap; gap: 8px; }
            .ai-config-remote-item {
                min-height: 32px;
                padding: 0 12px;
                border-radius: 999px;
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #334155;
                font-size: 12px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .ai-config-remote-item.active { border-color: #93c5fd; background: #dbeafe; color: #1d4ed8; }
            .ai-config-actions { display: flex; gap: 8px; flex-wrap: wrap; }
            @media (max-width: 1100px) {
                .ai-config-layout { grid-template-columns: 1fr; }
                .ai-config-grid { grid-template-columns: 1fr; }
                .ai-config-span-2 { grid-column: auto; }
            }
        </style>

        <section class="adv-toolbar">
            <div class="adv-toolbar-head">
                <div>
                    <div class="adv-toolbar-title">模型供应商与默认模型</div>
                    <div class="adv-toolbar-sub">统一配置 AI 提示词使用的提供商、API Key、Base URL 与模型列表。当前仅管理员可保存。</div>
                </div>
                <div class="ai-config-chip-row" id="ai-config-summary"></div>
            </div>
        </section>

        <section class="ai-config-layout" id="ai-config-app">
            <aside class="ai-config-card-grid" id="ai-provider-list"></aside>
            <section class="ai-config-panel">
                <div class="ai-config-panel-head">
                    <div>
                        <div class="ai-config-panel-title" id="ai-panel-title">等待加载</div>
                        <div class="ai-config-panel-sub" id="ai-panel-sub">从 /advanced-api/ai/config 加载配置</div>
                    </div>
                    <span class="adv-chip muted" id="ai-active-chip">未启用</span>
                </div>
                <div class="ai-config-panel-body">
                    <div class="ai-config-grid">
                        <label class="adv-field">
                            <span>当前启用提供商</span>
                            <select id="ai-active-provider" class="adv-select"></select>
                        </label>
                        <label class="adv-field">
                            <span>默认模型</span>
                            <select id="ai-default-model" class="adv-select"></select>
                        </label>
                        <label class="adv-field ai-config-span-2">
                            <span>API Key</span>
                            <input id="ai-api-key" class="adv-input adv-mono" placeholder="请输入当前提供商的 API Key" />
                        </label>
                        <label class="adv-field ai-config-span-2">
                            <span>Base URL</span>
                            <input id="ai-base-url" class="adv-input adv-mono" placeholder="请输入 API Base URL" />
                        </label>
                        <label class="adv-field ai-config-span-2">
                            <span>模型列表</span>
                            <textarea id="ai-models" class="adv-textarea" placeholder="每行一个模型，例如：gpt-4.1-mini"></textarea>
                        </label>
                    </div>
                    <div class="ai-config-model-toolbar">
                        <div class="ai-config-model-help">模型列表会同步到“默认模型”下拉框。优先把你实际会用到的模型放进来。</div>
                        <div class="ai-config-actions">
                            <button type="button" class="adv-btn" id="ai-models-fetch"><i class="fas fa-cloud-download-alt"></i>从 API 获取模型</button>
                            <button type="button" class="adv-btn" id="ai-models-reset"><i class="fas fa-undo"></i>恢复当前供应商默认列表</button>
                        </div>
                    </div>
                    <div class="ai-config-remote-panel">
                        <div class="ai-config-remote-head">
                            <div>
                                <div class="ai-config-panel-title" style="font-size:13px;">远端模型目录</div>
                                <div class="ai-config-panel-sub" id="ai-remote-status">点击“从 API 获取模型”后可直接勾选需要保存的模型。</div>
                            </div>
                            <span class="adv-chip muted" id="ai-remote-count">0 个模型</span>
                        </div>
                        <div class="ai-config-remote-list" id="ai-remote-models"></div>
                    </div>
                    <div class="ai-config-actions">
                        <button type="button" class="adv-btn primary" id="ai-config-save"><i class="fas fa-save"></i>保存 AI 配置</button>
                        <button type="button" class="adv-btn" id="ai-config-reload"><i class="fas fa-sync"></i>重新加载</button>
                    </div>
                    <div class="ai-config-status" id="ai-config-status">等待加载 AI 配置</div>
                </div>
            </section>
        </section>

        @push('scripts')
            <script>
                (() => {
                    const endpoint = '/advanced-api/ai/config';
                    const els = {
                        providerList: document.getElementById('ai-provider-list'),
                        summary: document.getElementById('ai-config-summary'),
                        panelTitle: document.getElementById('ai-panel-title'),
                        panelSub: document.getElementById('ai-panel-sub'),
                        activeChip: document.getElementById('ai-active-chip'),
                        activeProvider: document.getElementById('ai-active-provider'),
                        defaultModel: document.getElementById('ai-default-model'),
                        apiKey: document.getElementById('ai-api-key'),
                        baseUrl: document.getElementById('ai-base-url'),
                        models: document.getElementById('ai-models'),
                        modelsFetch: document.getElementById('ai-models-fetch'),
                        modelsReset: document.getElementById('ai-models-reset'),
                        remoteStatus: document.getElementById('ai-remote-status'),
                        remoteCount: document.getElementById('ai-remote-count'),
                        remoteModels: document.getElementById('ai-remote-models'),
                        save: document.getElementById('ai-config-save'),
                        reload: document.getElementById('ai-config-reload'),
                        status: document.getElementById('ai-config-status'),
                    };

                    const defaults = {
                        gpt: ['gpt-4.1-mini', 'gpt-4.1', 'gpt-4o-mini'],
                        deepseek: ['deepseek-chat', 'deepseek-reasoner'],
                        qwen: ['qwen-vl-max', 'qwen-vl-plus', 'qwen2.5-vl-72b-instruct'],
                        gemini: ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'],
                    };

                    const state = {
                        activeProvider: 'gpt',
                        providers: {},
                        loading: false,
                        canEdit: true,
                    };

                    const escapeHtml = (value) => String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');

                    const setStatus = (message, type = '') => {
                        els.status.textContent = message || '完成';
                        els.status.classList.remove('success', 'error');
                        if (type === 'success') els.status.classList.add('success');
                        if (type === 'error') els.status.classList.add('error');
                    };

                    const normalizeModels = (value) => {
                        const rows = Array.isArray(value)
                            ? value
                            : String(value || '').split(/\n+/g);
                        return Array.from(new Set(rows.map((item) => String(item || '').trim()).filter(Boolean)));
                    };

                    const currentProvider = () => state.providers[state.activeProvider] || null;

                    const setLoading = (loading) => {
                        state.loading = loading;
                        [els.activeProvider, els.defaultModel, els.apiKey, els.baseUrl, els.models, els.modelsFetch, els.modelsReset, els.save, els.reload].forEach((el) => {
                            if (!el) return;
                            const requiresEdit = [els.activeProvider, els.defaultModel, els.apiKey, els.baseUrl, els.models, els.modelsFetch, els.modelsReset, els.save].includes(el);
                            el.disabled = loading || (!state.canEdit && requiresEdit);
                        });
                        els.remoteModels.style.pointerEvents = loading || !state.canEdit ? 'none' : 'auto';
                        els.remoteModels.style.opacity = loading || !state.canEdit ? '.65' : '1';
                    };

                    const syncSummary = () => {
                        const chips = Object.entries(state.providers).map(([provider, item]) => {
                            const cls = item.ready ? 'ok' : 'warn';
                            const model = item.default_model || '未选模型';
                            return `<span class="ai-config-chip ${cls}"><strong>${escapeHtml(item.label || provider)}</strong><span>${escapeHtml(model)}</span></span>`;
                        });
                        els.summary.innerHTML = chips.join('');
                    };

                    const syncProviderCards = () => {
                        els.providerList.innerHTML = Object.entries(state.providers).map(([provider, item]) => {
                            const readyText = item.ready ? '已配置' : '待配置';
                            return `
                                <button type="button" class="ai-config-card ${provider === state.activeProvider ? 'active' : ''}" data-provider="${escapeHtml(provider)}">
                                    <div class="ai-config-card-head">
                                        <div class="ai-config-card-name"><i class="fas ${provider === 'gemini' ? 'fa-gem' : 'fa-robot'}"></i><span>${escapeHtml(item.label || provider)}</span></div>
                                        <span class="adv-chip ${item.ready ? 'success' : 'warn'}">${readyText}</span>
                                    </div>
                                    <div class="ai-config-card-meta">${escapeHtml(item.base_url || '-')}</div>
                                    <div class="ai-config-card-model">默认模型：${escapeHtml(item.default_model || '-')}</div>
                                </button>
                            `;
                        }).join('');
                    };

                    const syncProviderSelect = () => {
                        els.activeProvider.innerHTML = Object.entries(state.providers).map(([provider, item]) => {
                            return `<option value="${escapeHtml(provider)}" ${provider === state.activeProvider ? 'selected' : ''}>${escapeHtml(item.label || provider)}</option>`;
                        }).join('');
                    };

                    const syncRemoteModelsPanel = () => {
                        const item = currentProvider();
                        if (!item) return;
                        const selected = normalizeModels(item.models);
                        const remoteModels = normalizeModels(item.remote_models && item.remote_models.length ? item.remote_models : item.models);
                        els.remoteCount.textContent = `${remoteModels.length} 个模型`;
                        els.remoteStatus.textContent = remoteModels.length
                            ? '点击模型即可切换是否纳入保存列表，默认模型下拉框会随选中列表更新。'
                            : '当前还没有远端模型目录，可先点击“从 API 获取模型”。';
                        els.remoteModels.innerHTML = remoteModels.length
                            ? remoteModels.map((model) => `
                                <button type="button" class="ai-config-remote-item ${selected.includes(model) ? 'active' : ''}" data-remote-model="${escapeHtml(model)}">
                                    <i class="fas ${selected.includes(model) ? 'fa-check-circle' : 'fa-circle'}"></i>
                                    <span>${escapeHtml(model)}</span>
                                </button>
                            `).join('')
                            : '<span class="ai-config-panel-sub">暂无模型目录</span>';
                    };

                    const syncForm = () => {
                        const item = currentProvider();
                        if (!item) return;
                        const models = normalizeModels(item.models);
                        item.models = models;
                        if (!models.includes(item.default_model)) {
                            item.default_model = models[0] || '';
                        }
                        els.panelTitle.textContent = `${item.label || state.activeProvider} 配置`;
                        els.panelSub.textContent = `${item.transport === 'gemini' ? 'Gemini 原生接口' : 'OpenAI Compatible'} · 当前启用：${state.activeProvider}`;
                        els.activeChip.textContent = state.activeProvider === els.activeProvider.value ? '启用中' : '未启用';
                        els.activeChip.className = `adv-chip ${item.ready ? 'success' : 'warn'}`;
                        els.apiKey.value = item.api_key || '';
                        els.baseUrl.value = item.base_url || '';
                        els.models.value = models.join('\n');
                        els.defaultModel.innerHTML = models.map((model) => {
                            return `<option value="${escapeHtml(model)}" ${model === item.default_model ? 'selected' : ''}>${escapeHtml(model)}</option>`;
                        }).join('') || '<option value="">暂无可选模型</option>';
                        syncRemoteModelsPanel();
                    };

                    const syncAll = () => {
                        syncSummary();
                        syncProviderCards();
                        syncProviderSelect();
                        syncForm();
                    };

                    const collectPayload = () => {
                        const provider = currentProvider();
                        if (!provider) {
                            throw new Error('未找到当前提供商');
                        }

                        provider.api_key = String(els.apiKey.value || '').trim();
                        provider.base_url = String(els.baseUrl.value || '').trim();
                        provider.models = normalizeModels(els.models.value);
                        provider.default_model = String(els.defaultModel.value || '').trim();
                        provider.ready = provider.api_key !== '' && provider.default_model !== '';

                        return {
                            active_provider: state.activeProvider,
                            providers: Object.fromEntries(Object.entries(state.providers).map(([name, item]) => [name, {
                                api_key: item.api_key || '',
                                base_url: item.base_url || '',
                                default_model: item.default_model || '',
                                models: normalizeModels(item.models),
                            }])),
                        };
                    };

                    const load = async () => {
                        setLoading(true);
                        setStatus('正在加载 AI 配置');
                        try {
                            const { data } = await axios.get(endpoint);
                            if (!data?.status) {
                                throw new Error(data?.message || '加载失败');
                            }
                            state.activeProvider = String(data?.data?.active_provider || 'gpt');
                            state.providers = data?.data?.providers || {};
                            state.canEdit = true;
                            syncAll();
                            setStatus('AI 配置已加载', 'success');
                        } catch (error) {
                            state.canEdit = false;
                            const message = error?.response?.data?.message || error?.message || '加载失败';
                            setStatus(message, 'error');
                            if (window.toastr) window.toastr.error(message);
                        } finally {
                            setLoading(false);
                        }
                    };

                    els.providerList.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-provider]');
                        if (!button) return;
                        const provider = String(button.getAttribute('data-provider') || '').trim();
                        if (!provider || !state.providers[provider]) return;
                        collectPayload();
                        state.activeProvider = provider;
                        syncAll();
                    });

                    els.activeProvider.addEventListener('change', () => {
                        collectPayload();
                        state.activeProvider = String(els.activeProvider.value || 'gpt');
                        syncAll();
                    });

                    els.models.addEventListener('input', () => {
                        const item = currentProvider();
                        if (!item) return;
                        item.models = normalizeModels(els.models.value);
                        const previous = String(els.defaultModel.value || '').trim();
                        const models = item.models;
                        const next = models.includes(previous) ? previous : (models[0] || '');
                        item.default_model = next;
                        syncForm();
                    });

                    els.defaultModel.addEventListener('change', () => {
                        const item = currentProvider();
                        if (!item) return;
                        item.default_model = String(els.defaultModel.value || '').trim();
                        syncSummary();
                        syncProviderCards();
                    });

                    els.apiKey.addEventListener('input', () => {
                        const item = currentProvider();
                        if (!item) return;
                        item.api_key = String(els.apiKey.value || '').trim();
                        item.ready = item.api_key !== '' && String(item.default_model || '').trim() !== '';
                        syncSummary();
                        syncProviderCards();
                    });

                    els.baseUrl.addEventListener('input', () => {
                        const item = currentProvider();
                        if (!item) return;
                        item.base_url = String(els.baseUrl.value || '').trim();
                        syncProviderCards();
                    });

                    els.modelsReset.addEventListener('click', () => {
                        const item = currentProvider();
                        if (!item) return;
                        item.models = [...(defaults[state.activeProvider] || [])];
                        item.default_model = item.models[0] || '';
                        syncForm();
                        syncSummary();
                        syncProviderCards();
                    });

                    els.remoteModels.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-remote-model]');
                        if (!button) return;
                        const item = currentProvider();
                        if (!item) return;
                        const model = String(button.getAttribute('data-remote-model') || '').trim();
                        if (!model) return;
                        const selected = normalizeModels(item.models);
                        item.models = selected.includes(model)
                            ? selected.filter((entry) => entry !== model)
                            : [...selected, model];
                        if (!item.models.includes(item.default_model)) {
                            item.default_model = item.models[0] || '';
                        }
                        syncForm();
                        syncSummary();
                        syncProviderCards();
                    });

                    els.modelsFetch.addEventListener('click', async () => {
                        const provider = state.activeProvider;
                        if (!provider) return;
                        setLoading(true);
                        setStatus(`正在从 ${provider} 获取模型列表`);
                        try {
                            collectPayload();
                            const { data } = await axios.post(`/advanced-api/ai/config/providers/${encodeURIComponent(provider)}/models:fetch`, {
                                api_key: String(els.apiKey.value || '').trim(),
                                base_url: String(els.baseUrl.value || '').trim(),
                            });
                            if (!data?.status) {
                                throw new Error(data?.message || '模型列表获取失败');
                            }
                            const payload = data.data || {};
                            const item = currentProvider();
                            if (!item) {
                                throw new Error('当前提供商不存在');
                            }
                            item.remote_models = normalizeModels(payload.models);
                            item.models = normalizeModels(payload.selected_models);
                            item.default_model = String(payload.default_model || item.models[0] || '').trim();
                            syncAll();
                            setStatus(`已获取 ${payload.count || item.remote_models.length || 0} 个模型`, 'success');
                            if (window.toastr) window.toastr.success('远端模型列表已同步');
                        } catch (error) {
                            const message = error?.response?.data?.message || error?.message || '模型列表获取失败';
                            setStatus(message, 'error');
                            if (window.toastr) window.toastr.error(message);
                        } finally {
                            setLoading(false);
                        }
                    });

                    els.reload.addEventListener('click', load);

                    els.save.addEventListener('click', async () => {
                        try {
                            const payload = collectPayload();
                            setLoading(true);
                            setStatus('正在保存 AI 配置');
                            const { data } = await axios.put(endpoint, payload);
                            if (!data?.status) {
                                throw new Error(data?.message || '保存失败');
                            }
                            state.activeProvider = String(data?.data?.active_provider || payload.active_provider || 'gpt');
                            state.providers = data?.data?.providers || state.providers;
                            syncAll();
                            setStatus('AI 配置已保存', 'success');
                            if (window.toastr) window.toastr.success('AI 配置已保存');
                        } catch (error) {
                            const message = error?.response?.data?.message || error?.message || '保存失败';
                            setStatus(message, 'error');
                            if (window.toastr) window.toastr.error(message);
                        } finally {
                            setLoading(false);
                        }
                    });

                    load();
                })();
            </script>
        @endpush
    </x-advanced-shell>
</x-app-layout>
