<x-app-layout>
    @section('title', '作业中心')

    <x-advanced-shell page="jobs" title="作业中心">
        <style>
            .jobs-grid { display:grid; gap:12px; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); }
            .jobs-card { border:1px solid #e2e8f0; border-radius:10px; background:#fff; padding:14px; }
            .jobs-k { font-size:12px; color:#64748b; margin-bottom:6px; }
            .jobs-v { font-size:24px; font-weight:700; color:#0f172a; line-height:1; }
            .jobs-muted { font-size:12px; color:#64748b; line-height:1.7; }
            .jobs-panel { border:1px solid #e2e8f0; border-radius:12px; background:#fff; padding:14px; }
            .jobs-form { display:grid; gap:10px; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); }
            .jobs-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
            .jobs-run-grid { display:grid; gap:10px; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); }
            .jobs-output { margin-top:12px; border:1px solid #e2e8f0; border-radius:10px; background:#0f172a; color:#e2e8f0; padding:12px; min-height:180px; overflow:auto; font:12px/1.7 SFMono-Regular,Consolas,monospace; white-space:pre-wrap; word-break:break-word; }
        </style>

        <div class="adv-panel">
            <div class="adv-panel-head">
                <div class="adv-panel-title">正式控制面</div>
            </div>
            <div class="adv-panel-body">
                <div class="adv-toolbar-sub">
                    这里只保留正式的 intelligence 控制链路：读取 `/advanced-api/intelligence/status`，以及管理员通过
                    preview / dispatch 触发回填。不再使用旧的后台 shell job API。
                </div>
            </div>
        </div>

        <div class="adv-panel">
            <div class="adv-panel-head">
                <div class="adv-panel-title">当前状态</div>
            </div>
            <div class="adv-panel-body">
                <div class="jobs-grid">
                    <div class="jobs-card"><div class="jobs-k">图片总数</div><div class="jobs-v" id="jobs-total">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">真实已分析</div><div class="jobs-v" id="jobs-analyzed">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">待处理 / 回退</div><div class="jobs-v" id="jobs-pending">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">缺失记录</div><div class="jobs-v" id="jobs-missing">-</div></div>
                </div>
                <div class="jobs-actions">
                    <button type="button" class="adv-btn" id="jobs-refresh"><i class="fas fa-rotate"></i>刷新状态</button>
                    <span class="adv-chip muted" id="jobs-coverage">覆盖率 -</span>
                    <span class="adv-chip muted" id="jobs-updated">最近分析 -</span>
                </div>
            </div>
        </div>

        <div class="adv-panel">
            <div class="adv-panel-head">
                <div class="adv-panel-title">回填操作</div>
            </div>
            <div class="adv-panel-body">
                <div class="jobs-form">
                    <label class="adv-field">
                        <span>limit</span>
                        <input id="jobs-limit" class="adv-input" type="number" min="1" max="200" value="25" />
                    </label>
                    <label class="adv-field">
                        <span>chunk</span>
                        <input id="jobs-chunk" class="adv-input" type="number" min="1" max="100" value="25" />
                    </label>
                    <label class="adv-field">
                        <span>older_than_minutes</span>
                        <input id="jobs-older-than" class="adv-input" type="number" min="0" max="10080" value="30" />
                    </label>
                    <label class="adv-field">
                        <span>sample_limit</span>
                        <input id="jobs-sample-limit" class="adv-input" type="number" min="0" max="50" value="10" />
                    </label>
                    <label class="adv-control-check">
                        <input id="jobs-missing-only" type="checkbox" />
                        <span>仅缺失记录</span>
                    </label>
                    <label class="adv-control-check">
                        <input id="jobs-force" type="checkbox" />
                        <span>强制重跑</span>
                    </label>
                </div>
                <div class="jobs-actions">
                    <button type="button" class="adv-btn" id="jobs-preview"><i class="fas fa-vial"></i>Preview</button>
                    <button type="button" class="adv-btn primary" id="jobs-dispatch"><i class="fas fa-paper-plane"></i>Dispatch</button>
                </div>
                <div class="jobs-output" id="jobs-output">等待操作结果...</div>
            </div>
        </div>

        <div class="adv-panel">
            <div class="adv-panel-head">
                <div class="adv-panel-title">最近运行</div>
            </div>
            <div class="adv-panel-body">
                <div class="jobs-run-grid">
                    <div class="jobs-card"><div class="jobs-k">run_id</div><div class="jobs-v" id="jobs-run-id">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">status</div><div class="jobs-v" id="jobs-run-status">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">matched</div><div class="jobs-v" id="jobs-run-matched">-</div></div>
                    <div class="jobs-card"><div class="jobs-k">dispatched</div><div class="jobs-v" id="jobs-run-dispatched">-</div></div>
                </div>
                <div class="jobs-actions">
                    <span class="adv-chip muted" id="jobs-run-trigger">trigger -</span>
                    <span class="adv-chip muted" id="jobs-run-updated">updated -</span>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                (() => {
                    if (!window.axios) {
                        return;
                    }

                    const endpoints = {
                        status: @json(route('advanced.api.intelligence.status')),
                        preview: @json(route('advanced.api.intelligence.backfill.preview')),
                        dispatch: @json(route('advanced.api.intelligence.backfill.dispatch')),
                    };

                    const output = document.getElementById('jobs-output');

                    const readOptions = () => ({
                        limit: Number(document.getElementById('jobs-limit').value || 25),
                        chunk: Number(document.getElementById('jobs-chunk').value || 25),
                        older_than_minutes: Number(document.getElementById('jobs-older-than').value || 30),
                        sample_limit: Number(document.getElementById('jobs-sample-limit').value || 10),
                        missing_only: document.getElementById('jobs-missing-only').checked,
                        force: document.getElementById('jobs-force').checked,
                    });

                    const renderStatus = (payload) => {
                        const intelligence = payload?.intelligence || {};
                        document.getElementById('jobs-total').textContent = String(intelligence.images_total ?? 0);
                        document.getElementById('jobs-analyzed').textContent = String(intelligence.analyzed_count ?? 0);
                        document.getElementById('jobs-pending').textContent = String(intelligence.pending_count ?? 0);
                        document.getElementById('jobs-missing').textContent = String(intelligence.missing_count ?? 0);
                        document.getElementById('jobs-coverage').textContent = '覆盖率 ' + String(intelligence.coverage_label || '-');
                        document.getElementById('jobs-updated').textContent = '最近分析 ' + String(intelligence.latest_analyzed_at || '暂无');

                        const run = intelligence.control_plane?.latest_run || null;
                        document.getElementById('jobs-run-id').textContent = run?.run_id ? String(run.run_id) : '-';
                        document.getElementById('jobs-run-status').textContent = String(run?.status || '-');
                        document.getElementById('jobs-run-matched').textContent = String(run?.matched ?? 0);
                        document.getElementById('jobs-run-dispatched').textContent = String(run?.dispatched ?? 0);
                        document.getElementById('jobs-run-trigger').textContent = 'trigger ' + String(run?.trigger_source || '-');
                        document.getElementById('jobs-run-updated').textContent = 'updated ' + String(run?.updated_at || run?.created_at || '-');
                    };

                    const renderOutput = (title, payload) => {
                        output.textContent = title + "\n\n" + JSON.stringify(payload, null, 2);
                    };

                    const parseError = (error) => error?.response?.data || {
                        status: false,
                        message: error?.message || '请求失败',
                    };

                    const loadStatus = async () => {
                        const { data } = await axios.get(endpoints.status);
                        if (!data?.status) {
                            throw new Error(data?.message || '状态加载失败');
                        }
                        renderStatus(data.data);
                        return data.data;
                    };

                    const runAction = async (kind) => {
                        const url = kind === 'preview' ? endpoints.preview : endpoints.dispatch;
                        const title = kind === 'preview' ? 'Preview result' : 'Dispatch result';
                        try {
                            const { data } = await axios.post(url, readOptions());
                            renderOutput(title, data);
                            if (data?.data) {
                                renderStatus(data.data);
                            } else {
                                await loadStatus();
                            }
                        } catch (error) {
                            renderOutput(title + ' error', parseError(error));
                        }
                    };

                    document.getElementById('jobs-refresh').addEventListener('click', () => {
                        loadStatus().catch((error) => renderOutput('Status error', parseError(error)));
                    });
                    document.getElementById('jobs-preview').addEventListener('click', () => runAction('preview'));
                    document.getElementById('jobs-dispatch').addEventListener('click', () => runAction('dispatch'));

                    loadStatus().catch((error) => renderOutput('Status error', parseError(error)));
                })();
            </script>
        @endpush
    </x-advanced-shell>
</x-app-layout>
