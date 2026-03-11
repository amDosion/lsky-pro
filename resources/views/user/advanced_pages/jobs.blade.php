<x-app-layout>
    @section('title', '作业中心')
    <x-advanced-shell page="jobs" title="作业中心">
        <style>
            .job-layout { margin-top: 12px; display: grid; gap: 12px; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); }
            .job-panel { border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; padding: 10px; }
            .job-panel-title { font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
            .job-msg { margin-top: 10px; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; font-size: 12px; }
            .job-msg.success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
            .job-msg.error { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
            .job-poll { margin-top: 8px; font-size: 12px; color: #64748b; }
            .job-table tr.is-selected { background: #eff6ff; }
            .job-table td.actions { white-space: nowrap; }
            .job-status { display: inline-flex; min-height: 22px; align-items: center; padding: 0 8px; border-radius: 999px; border: 1px solid #dbe2ea; background: #f8fafc; font-size: 11px; color: #334155; }
            .job-status.pending, .job-status.retrying { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
            .job-status.processing { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
            .job-status.success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
            .job-status.partial_success { border-color: #c7d2fe; background: #eef2ff; color: #3730a3; }
            .job-status.failed, .job-status.cancelled { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
            .job-summary { margin-top: 8px; display: grid; gap: 8px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .job-summary-item { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc; }
            .job-summary-item span { display: block; font-size: 11px; color: #64748b; }
            .job-summary-item strong { display: block; margin-top: 4px; font-size: 13px; color: #0f172a; }
            .job-recent { margin-top: 10px; }
            .job-recent-tags { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; }
            .job-recent-empty { font-size: 12px; color: #94a3b8; }
            .job-result-layout { margin-top: 10px; display: grid; gap: 10px; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); }
            .job-detail { margin: 0; min-height: 220px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 10px; font-size: 12px; color: #334155; white-space: pre-wrap; word-break: break-word; }
            @media (max-width: 1200px) {
                .job-layout { grid-template-columns: 1fr; }
                .job-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .job-result-layout { grid-template-columns: 1fr; }
            }
        </style>

        <div class="adv-grid">
            <label class="adv-field">
                <span>状态筛选</span>
                <select id="job-status" class="adv-select">
                    <option value="">全部</option>
                    <option value="pending">pending</option>
                    <option value="retrying">retrying</option>
                    <option value="processing">processing</option>
                    <option value="success">success</option>
                    <option value="partial_success">partial_success</option>
                    <option value="failed">failed</option>
                    <option value="cancelled">cancelled</option>
                </select>
            </label>
            <label class="adv-field">
                <span>任务 ID（单任务查询）</span>
                <input id="job-id" class="adv-input" placeholder="输入或从最近任务选择" />
            </label>
        </div>

        <div class="job-recent">
            <label class="adv-field" style="margin:0;">
                <span>最近任务（localStorage）</span>
                <select id="job-recent" class="adv-select">
                    <option value="">暂无最近任务</option>
                </select>
            </label>
            <div id="job-recent-tags" class="job-recent-tags"><span class="job-recent-empty">最近查询/操作过的任务会显示在这里</span></div>
        </div>

        <div class="adv-actions">
            <button class="adv-btn primary" id="job-list">刷新列表</button>
            <button class="adv-btn" id="job-detail">查询任务</button>
            <button class="adv-btn" id="job-retry">重试任务</button>
            <button class="adv-btn" id="job-cancel">取消任务</button>
            <button class="adv-btn" id="job-poll-start">开始轮询</button>
            <button class="adv-btn" id="job-poll-stop">停止轮询</button>
        </div>

        <div id="job-msg" class="job-msg">准备就绪：先刷新列表或输入任务 ID 查询。</div>
        <div id="job-poll" class="job-poll">轮询未启动</div>

        <div class="job-layout">
            <section class="job-panel">
                <div class="job-panel-title">任务列表（最多 50 条）</div>
                <table class="adv-table job-table">
                    <thead>
                    <tr>
                        <th>job_id</th>
                        <th>状态</th>
                        <th>模板</th>
                        <th>进度</th>
                        <th>成功/失败</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="job-list-body">
                    <tr>
                        <td colspan="6">等待加载任务列表...</td>
                    </tr>
                    </tbody>
                </table>
            </section>

            <section class="job-panel">
                <div class="job-panel-title">任务摘要</div>
                <div class="job-summary">
                    <div class="job-summary-item"><span>job_id</span><strong id="job-s-id">-</strong></div>
                    <div class="job-summary-item"><span>状态</span><strong id="job-s-status">-</strong></div>
                    <div class="job-summary-item"><span>进度</span><strong id="job-s-progress">-</strong></div>
                    <div class="job-summary-item"><span>成功/失败</span><strong id="job-s-count">-</strong></div>
                </div>

                <div class="job-result-layout">
                    <div>
                        <div class="job-panel-title" style="margin-top: 10px;">结果表格</div>
                        <table class="adv-table job-table">
                            <thead>
                            <tr>
                                <th>类型</th>
                                <th>标识</th>
                                <th>说明</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="job-result-body">
                            <tr>
                                <td colspan="4">查询任务后显示结果项</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <div class="job-panel-title" style="margin-top: 10px;">详情面板</div>
                        <pre id="job-detail-panel" class="job-detail">等待选择结果项...</pre>
                    </div>
                </div>
            </section>
        </div>

        @push('scripts')
            <script>
                (() => {
                    const RETRYABLE_STATUS = ['failed', 'partial_success', 'cancelled'];
                    const CANCELLABLE_STATUS = ['pending', 'retrying', 'processing'];
                    const TERMINAL_STATUS = ['success', 'failed', 'partial_success', 'cancelled'];
                    const POLL_INTERVAL = 3000;
                    const RECENT_LIMIT = 20;
                    const RECENT_STORAGE_KEY = 'lsky.advanced.jobs.recent.v2';

                    const el = {
                        status: document.getElementById('job-status'),
                        jobId: document.getElementById('job-id'),
                        recent: document.getElementById('job-recent'),
                        recentTags: document.getElementById('job-recent-tags'),
                        listBtn: document.getElementById('job-list'),
                        detailBtn: document.getElementById('job-detail'),
                        retryBtn: document.getElementById('job-retry'),
                        cancelBtn: document.getElementById('job-cancel'),
                        pollStartBtn: document.getElementById('job-poll-start'),
                        pollStopBtn: document.getElementById('job-poll-stop'),
                        msg: document.getElementById('job-msg'),
                        poll: document.getElementById('job-poll'),
                        listBody: document.getElementById('job-list-body'),
                        resultBody: document.getElementById('job-result-body'),
                        detailPanel: document.getElementById('job-detail-panel'),
                        sId: document.getElementById('job-s-id'),
                        sStatus: document.getElementById('job-s-status'),
                        sProgress: document.getElementById('job-s-progress'),
                        sCount: document.getElementById('job-s-count'),
                    };

                    const state = {
                        jobs: [],
                        statusMap: {},
                        selectedJobId: '',
                        selectedJob: null,
                        resultRows: [],
                        selectedResultIndex: -1,
                        recentJobs: [],
                        loading: {
                            list: false,
                            detail: false,
                            retry: false,
                            cancel: false,
                        },
                        polling: false,
                        pollingJobId: '',
                        pollTimer: null,
                        pollLock: false,
                    };

                    const escapeHtml = (value) => String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');

                    const prettyJson = (value) => {
                        try {
                            return JSON.stringify(value ?? {}, null, 2);
                        } catch (error) {
                            return String(value ?? '');
                        }
                    };

                    const ensureSuccess = (payload) => {
                        if (payload && payload.status === false) {
                            const error = new Error(payload.message || '请求失败');
                            error.payload = payload;
                            throw error;
                        }
                        return payload || { status: true, message: 'success', data: {} };
                    };

                    const parseError = (error) => {
                        if (error && error.payload) return error.payload;
                        return error?.response?.data || {
                            status: false,
                            message: error?.message || '请求失败',
                            data: {},
                        };
                    };

                    const isBusy = () => Object.values(state.loading).some(Boolean);

                    const setLoading = (key, value) => {
                        state.loading[key] = value;
                        renderButtons();
                    };

                    const setMessage = (message, type) => {
                        el.msg.textContent = message || '完成';
                        el.msg.classList.remove('success', 'error');
                        if (type === 'success') el.msg.classList.add('success');
                        if (type === 'error') el.msg.classList.add('error');
                    };

                    const setPollText = (text) => {
                        el.poll.textContent = text;
                    };

                    const readJobId = () => String(el.jobId.value || '').trim();

                    const setSelectedJobId = (jobId, syncInput) => {
                        state.selectedJobId = String(jobId || '').trim();
                        if (syncInput !== false) {
                            el.jobId.value = state.selectedJobId;
                        }
                        if (!state.selectedJob || String(state.selectedJob.job_id || '') !== state.selectedJobId) {
                            state.selectedJob = null;
                            state.resultRows = [];
                            state.selectedResultIndex = -1;
                        }
                        renderSummary();
                        renderResultRows();
                        renderListTable();
                        renderButtons();
                    };

                    const canRetry = (status) => RETRYABLE_STATUS.includes(String(status || ''));
                    const canCancel = (status) => CANCELLABLE_STATUS.includes(String(status || ''));

                    const currentSelectedStatus = () => {
                        if (state.selectedJob && String(state.selectedJob.job_id || '') === state.selectedJobId) {
                            return String(state.selectedJob.status || '');
                        }
                        return String(state.statusMap[state.selectedJobId] || '');
                    };

                    const extractKey = (item, fallback) => {
                        if (item === null || item === undefined) return fallback;
                        if (typeof item === 'string' || typeof item === 'number') return String(item);
                        const fields = ['key', 'source_key', 'origin_key', 'original_key', 'input_key', 'image_key'];
                        for (let i = 0; i < fields.length; i += 1) {
                            const value = item[fields[i]];
                            if (value !== null && value !== undefined && String(value).trim() !== '') {
                                return String(value).trim();
                            }
                        }
                        return fallback;
                    };

                    const readRecentJobs = () => {
                        try {
                            const raw = window.localStorage.getItem(RECENT_STORAGE_KEY) || '[]';
                            const parsed = JSON.parse(raw);
                            if (!Array.isArray(parsed)) return [];
                            return parsed
                                .map((item) => String(item || '').trim())
                                .filter((item) => item !== '');
                        } catch (error) {
                            return [];
                        }
                    };

                    const saveRecentJobs = () => {
                        window.localStorage.setItem(RECENT_STORAGE_KEY, JSON.stringify(state.recentJobs.slice(0, RECENT_LIMIT)));
                    };

                    const pushRecentJob = (jobId) => {
                        const normalized = String(jobId || '').trim();
                        if (!normalized) return;
                        state.recentJobs = [normalized]
                            .concat(state.recentJobs.filter((item) => item !== normalized))
                            .slice(0, RECENT_LIMIT);
                        saveRecentJobs();
                        renderRecentJobs();
                    };

                    const renderRecentJobs = () => {
                        if (!state.recentJobs.length) {
                            el.recent.innerHTML = '<option value="">暂无最近任务</option>';
                            el.recentTags.innerHTML = '<span class="job-recent-empty">最近查询/操作过的任务会显示在这里</span>';
                            return;
                        }

                        el.recent.innerHTML = '<option value="">选择最近任务</option>' + state.recentJobs.map((jobId) => {
                            const safe = escapeHtml(jobId);
                            return `<option value="${safe}">${safe}</option>`;
                        }).join('');

                        el.recentTags.innerHTML = state.recentJobs.map((jobId) => {
                            const safe = escapeHtml(jobId);
                            return `<button type="button" class="adv-btn js-job-recent-pick" data-job-id="${safe}" style="min-height:32px;">${safe}</button>`;
                        }).join('');
                    };

                    const buildResultRows = (job) => {
                        const result = job?.result || {};
                        const successes = Array.isArray(result.successes) ? result.successes : [];
                        const failures = Array.isArray(result.failures) ? result.failures : [];

                        const rows = successes.map((item, index) => ({
                            type: 'success',
                            key: extractKey(item, `success_${index + 1}`),
                            message: '执行成功',
                            payload: item,
                        })).concat(failures.map((item, index) => ({
                            type: 'failure',
                            key: extractKey(item, `failure_${index + 1}`),
                            message: String(item?.message || '执行失败'),
                            payload: item,
                        })));

                        if (!rows.length && job?.error_message) {
                            rows.push({
                                type: 'failure',
                                key: '-',
                                message: String(job.error_message),
                                payload: { error_message: job.error_message },
                            });
                        }

                        return rows;
                    };

                    const renderSummary = () => {
                        const job = state.selectedJob;
                        if (!job) {
                            el.sId.textContent = state.selectedJobId || '-';
                            el.sStatus.textContent = currentSelectedStatus() || '-';
                            el.sProgress.textContent = '-';
                            el.sCount.textContent = '-';
                            return;
                        }

                        el.sId.textContent = job.job_id || '-';
                        el.sStatus.textContent = job.status || '-';
                        el.sProgress.textContent = `${Number(job.progress || 0)}% (${Number(job.processed || 0)}/${Number(job.total || 0)})`;
                        el.sCount.textContent = `${Number(job.success || 0)} / ${Number(job.failed || 0)}`;
                    };

                    const renderDetailPanel = () => {
                        if (state.selectedResultIndex >= 0 && state.resultRows[state.selectedResultIndex]) {
                            el.detailPanel.textContent = prettyJson(state.resultRows[state.selectedResultIndex].payload);
                            return;
                        }
                        if (state.selectedJob) {
                            el.detailPanel.textContent = prettyJson(state.selectedJob);
                            return;
                        }
                        el.detailPanel.textContent = '等待选择结果项...';
                    };

                    const renderResultRows = () => {
                        if (!state.resultRows.length) {
                            el.resultBody.innerHTML = '<tr><td colspan="4">查询任务后显示结果项</td></tr>';
                            renderDetailPanel();
                            return;
                        }

                        el.resultBody.innerHTML = state.resultRows.map((row, index) => {
                            const typeText = row.type === 'success' ? '成功' : '失败';
                            const selectedClass = index === state.selectedResultIndex ? 'is-selected' : '';
                            const hint = row.message ? escapeHtml(row.message) : '-';
                            return `
                                <tr class="${selectedClass}">
                                    <td>${escapeHtml(typeText)}</td>
                                    <td>${escapeHtml(row.key || '-')}</td>
                                    <td>${hint}</td>
                                    <td class="actions"><button type="button" class="adv-btn js-job-result-pick" data-index="${index}" ${isBusy() ? 'disabled' : ''}>查看详情</button></td>
                                </tr>
                            `;
                        }).join('');

                        renderDetailPanel();
                    };

                    const renderListTable = () => {
                        if (!state.jobs.length) {
                            el.listBody.innerHTML = '<tr><td colspan="6">暂无任务，可先去模板页 dispatch。</td></tr>';
                            return;
                        }

                        el.listBody.innerHTML = state.jobs.map((item) => {
                            const jobId = String(item.job_id || '');
                            const status = String(item.status || '');
                            const templateName = item?.template?.name || '-';
                            const selectedClass = jobId === state.selectedJobId ? 'is-selected' : '';
                            return `
                                <tr class="${selectedClass}">
                                    <td>${escapeHtml(jobId)}</td>
                                    <td><span class="job-status ${escapeHtml(status)}">${escapeHtml(status || '-')}</span></td>
                                    <td>${escapeHtml(templateName)}</td>
                                    <td>${Number(item.progress || 0)}%</td>
                                    <td>${Number(item.success || 0)} / ${Number(item.failed || 0)}</td>
                                    <td class="actions">
                                        <button type="button" class="adv-btn js-job-pick" data-id="${escapeHtml(jobId)}" ${isBusy() ? 'disabled' : ''}>详情</button>
                                        <button type="button" class="adv-btn js-job-retry" data-id="${escapeHtml(jobId)}" ${isBusy() || !canRetry(status) ? 'disabled' : ''}>重试</button>
                                        <button type="button" class="adv-btn js-job-cancel" data-id="${escapeHtml(jobId)}" ${isBusy() || !canCancel(status) ? 'disabled' : ''}>取消</button>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    };

                    const renderButtons = () => {
                        const busy = isBusy();
                        const jobId = readJobId();
                        const status = currentSelectedStatus();

                        el.listBtn.disabled = busy;
                        el.detailBtn.disabled = busy || !jobId;
                        el.retryBtn.disabled = busy || !jobId || !canRetry(status);
                        el.cancelBtn.disabled = busy || !jobId || !canCancel(status);
                        el.pollStartBtn.disabled = busy || !jobId || state.polling;
                        el.pollStopBtn.disabled = !state.polling;

                        el.listBtn.textContent = state.loading.list ? '加载中...' : '刷新列表';
                        el.detailBtn.textContent = state.loading.detail ? '查询中...' : '查询任务';
                        el.retryBtn.textContent = state.loading.retry ? '重试中...' : '重试任务';
                        el.cancelBtn.textContent = state.loading.cancel ? '取消中...' : '取消任务';
                        el.pollStartBtn.textContent = state.polling ? '轮询中...' : '开始轮询';

                        renderListTable();
                        renderResultRows();
                    };

                    const fetchJobList = async (silent) => {
                        setLoading('list', true);
                        if (!silent) setMessage('正在加载任务列表...');
                        try {
                            const status = String(el.status.value || '').trim();
                            const response = ensureSuccess((await axios.get('/advanced-api/process-jobs', {
                                params: status ? { status } : {},
                            })).data);
                            const items = Array.isArray(response?.data?.items) ? response.data.items : [];
                            state.jobs = items;
                            state.statusMap = {};
                            items.forEach((item) => {
                                const jobId = String(item?.job_id || '').trim();
                                if (jobId) {
                                    state.statusMap[jobId] = String(item.status || '');
                                }
                            });
                            renderListTable();
                            if (!silent) {
                                setMessage(`任务列表已刷新，共 ${items.length} 条。`, 'success');
                            }
                        } catch (error) {
                            const parsed = parseError(error);
                            setMessage(parsed.message || '任务列表加载失败', 'error');
                        } finally {
                            setLoading('list', false);
                        }
                    };

                    const fetchJobDetail = async (jobId, silent) => {
                        const normalized = String(jobId || '').trim();
                        if (!normalized) {
                            setMessage('请先输入 job_id', 'error');
                            return;
                        }

                        setLoading('detail', true);
                        if (!silent) setMessage(`正在查询任务 ${normalized} ...`);
                        try {
                            const response = ensureSuccess((await axios.get('/advanced-api/process-jobs/' + encodeURIComponent(normalized))).data);
                            const job = response?.data || {};
                            const resolvedId = String(job?.job_id || normalized);
                            state.selectedJobId = resolvedId;
                            el.jobId.value = resolvedId;
                            state.selectedJob = job;
                            state.statusMap[resolvedId] = String(job.status || '');
                            state.resultRows = buildResultRows(job);
                            state.selectedResultIndex = state.resultRows.length ? 0 : -1;
                            pushRecentJob(resolvedId);
                            renderSummary();
                            renderResultRows();
                            renderListTable();

                            if (!silent) {
                                setMessage(response.message || '任务查询成功', 'success');
                            }

                            if (state.polling && resolvedId === state.pollingJobId && TERMINAL_STATUS.includes(String(job.status || ''))) {
                                stopPolling(`任务 ${resolvedId} 已到终态：${job.status}`);
                            }
                        } catch (error) {
                            const parsed = parseError(error);
                            setMessage(parsed.message || '任务查询失败', 'error');
                        } finally {
                            setLoading('detail', false);
                        }
                    };

                    const retryJob = async (jobId) => {
                        const normalized = String(jobId || '').trim();
                        if (!normalized) {
                            setMessage('请先输入 job_id', 'error');
                            return;
                        }
                        const status = currentSelectedStatus();
                        if (!canRetry(status)) {
                            setMessage('当前任务状态不可重试', 'error');
                            return;
                        }

                        setLoading('retry', true);
                        setMessage(`正在重试任务 ${normalized} ...`);
                        try {
                            const response = ensureSuccess((await axios.post('/advanced-api/process-jobs/' + encodeURIComponent(normalized) + '/retry')).data);
                            const job = response?.data || {};
                            const resolvedId = String(job?.job_id || normalized);
                            pushRecentJob(resolvedId);
                            setSelectedJobId(resolvedId);
                            setMessage(response.message || '任务已重试', 'success');
                            await fetchJobDetail(resolvedId, true);
                            await fetchJobList(true);
                        } catch (error) {
                            const parsed = parseError(error);
                            setMessage(parsed.message || '任务重试失败', 'error');
                        } finally {
                            setLoading('retry', false);
                        }
                    };

                    const cancelJob = async (jobId) => {
                        const normalized = String(jobId || '').trim();
                        if (!normalized) {
                            setMessage('请先输入 job_id', 'error');
                            return;
                        }
                        const status = currentSelectedStatus();
                        if (!canCancel(status)) {
                            setMessage('当前任务状态不可取消', 'error');
                            return;
                        }

                        setLoading('cancel', true);
                        setMessage(`正在取消任务 ${normalized} ...`);
                        try {
                            const response = ensureSuccess((await axios.post('/advanced-api/process-jobs/' + encodeURIComponent(normalized) + '/cancel')).data);
                            const job = response?.data || {};
                            const resolvedId = String(job?.job_id || normalized);
                            pushRecentJob(resolvedId);
                            setSelectedJobId(resolvedId);
                            setMessage(response.message || '任务已取消', 'success');
                            await fetchJobDetail(resolvedId, true);
                            await fetchJobList(true);
                        } catch (error) {
                            const parsed = parseError(error);
                            setMessage(parsed.message || '任务取消失败', 'error');
                        } finally {
                            setLoading('cancel', false);
                        }
                    };

                    const stopPolling = (text) => {
                        if (state.pollTimer) {
                            window.clearInterval(state.pollTimer);
                            state.pollTimer = null;
                        }
                        state.polling = false;
                        state.pollingJobId = '';
                        state.pollLock = false;
                        setPollText(text || '轮询未启动');
                        renderButtons();
                    };

                    const startPolling = async () => {
                        const jobId = readJobId();
                        if (!jobId) {
                            setMessage('请先输入 job_id 再启动轮询', 'error');
                            return;
                        }

                        stopPolling();
                        state.polling = true;
                        state.pollingJobId = jobId;
                        setPollText(`轮询中（${POLL_INTERVAL / 1000}s）：${jobId}`);
                        renderButtons();

                        await fetchJobDetail(jobId, false);

                        state.pollTimer = window.setInterval(async () => {
                            if (!state.polling || state.pollLock) return;
                            state.pollLock = true;
                            try {
                                await fetchJobDetail(state.pollingJobId, true);
                            } catch (error) {
                                // fetchJobDetail 已处理错误提示
                            } finally {
                                state.pollLock = false;
                            }
                        }, POLL_INTERVAL);
                    };

                    el.jobId.addEventListener('input', () => {
                        setSelectedJobId(readJobId(), false);
                    });

                    el.status.addEventListener('change', () => {
                        fetchJobList(false);
                    });

                    el.recent.addEventListener('change', async () => {
                        const jobId = String(el.recent.value || '').trim();
                        if (!jobId) return;
                        setSelectedJobId(jobId);
                        await fetchJobDetail(jobId, false);
                    });

                    el.listBtn.addEventListener('click', () => fetchJobList(false));
                    el.detailBtn.addEventListener('click', () => fetchJobDetail(readJobId(), false));
                    el.retryBtn.addEventListener('click', () => retryJob(readJobId()));
                    el.cancelBtn.addEventListener('click', () => cancelJob(readJobId()));
                    el.pollStartBtn.addEventListener('click', startPolling);
                    el.pollStopBtn.addEventListener('click', () => stopPolling('轮询已停止'));

                    el.listBody.addEventListener('click', async (event) => {
                        const pickBtn = event.target.closest('.js-job-pick');
                        const retryBtn = event.target.closest('.js-job-retry');
                        const cancelBtn = event.target.closest('.js-job-cancel');
                        const node = pickBtn || retryBtn || cancelBtn;
                        if (!node || isBusy()) return;

                        const jobId = String(node.dataset.id || '').trim();
                        if (!jobId) return;

                        setSelectedJobId(jobId);
                        if (pickBtn) {
                            await fetchJobDetail(jobId, false);
                            return;
                        }
                        if (retryBtn) {
                            await retryJob(jobId);
                            return;
                        }
                        if (cancelBtn) {
                            await cancelJob(jobId);
                        }
                    });

                    el.resultBody.addEventListener('click', (event) => {
                        const button = event.target.closest('.js-job-result-pick');
                        if (!button || isBusy()) return;
                        const index = Number(button.dataset.index || -1);
                        if (!Number.isInteger(index) || index < 0 || index >= state.resultRows.length) return;
                        state.selectedResultIndex = index;
                        renderResultRows();
                    });

                    el.recentTags.addEventListener('click', async (event) => {
                        const button = event.target.closest('.js-job-recent-pick');
                        if (!button || isBusy()) return;
                        const jobId = String(button.dataset.jobId || '').trim();
                        if (!jobId) return;
                        setSelectedJobId(jobId);
                        await fetchJobDetail(jobId, false);
                    });

                    window.addEventListener('beforeunload', () => {
                        stopPolling();
                    });

                    state.recentJobs = readRecentJobs();
                    renderRecentJobs();
                    renderSummary();
                    renderResultRows();
                    renderButtons();
                    fetchJobList(false);
                })();
            </script>
        @endpush
    </x-advanced-shell>
</x-app-layout>
