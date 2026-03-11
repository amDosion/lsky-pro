@section('title', '仪表盘')

@push('styles')
    <style>
        .dashboard-v4 {
            width: 100%;
            margin: 0;
            color: #111827;
        }

        .dashboard-v4 .core-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .dashboard-v4 .card {
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .dashboard-v4 .core-card {
            padding: 14px;
        }

        .dashboard-v4 .core-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .dashboard-v4 .core-value {
            margin-top: 8px;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 700;
            word-break: break-word;
        }

        .dashboard-v4 .core-hint {
            margin-top: 5px;
            font-size: 12px;
            color: #64748b;
        }

        .dashboard-v4 .body-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 14px;
        }

        .dashboard-v4 .panel-head {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .dashboard-v4 .panel-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .dashboard-v4 .panel-sub {
            font-size: 12px;
            color: #64748b;
        }

        .dashboard-v4 .panel-body {
            padding: 12px 14px 14px;
        }

        .dashboard-v4 .usage-row {
            margin-bottom: 12px;
        }

        .dashboard-v4 .usage-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #334155;
            margin-bottom: 6px;
        }

        .dashboard-v4 .usage-track {
            height: 10px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .dashboard-v4 .usage-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e 0%, #06b6d4 100%);
        }

        .dashboard-v4 .limit-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .dashboard-v4 .limit-item {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 10px;
        }

        .dashboard-v4 .limit-k {
            font-size: 12px;
            color: #64748b;
        }

        .dashboard-v4 .limit-v {
            margin-top: 4px;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .dashboard-v4 .strategy-list {
            display: grid;
            gap: 8px;
        }

        .dashboard-v4 .strategy-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .dashboard-v4 .strategy-name {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .dashboard-v4 .strategy-intro {
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
            word-break: break-word;
        }

        .dashboard-v4 .tag-default {
            flex: 0 0 auto;
            white-space: nowrap;
            border-radius: 999px;
            border: 1px solid #86efac;
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            font-size: 12px;
            line-height: 1.4;
        }

        .dashboard-v4 .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .dashboard-v4 .quick-link {
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #f8fafc;
            color: #0f172a;
            padding: 10px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: .2s ease;
        }

        .dashboard-v4 .quick-link:hover {
            background: #ecfeff;
            border-color: #67e8f9;
        }

        .dashboard-v4 .quick-link.disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .dashboard-v4 .kv {
            display: grid;
            gap: 8px;
        }

        .dashboard-v4 .kv-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 7px;
            font-size: 13px;
        }

        .dashboard-v4 .kv-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .dashboard-v4 .kv-k {
            color: #64748b;
            flex: 0 0 45%;
        }

        .dashboard-v4 .kv-v {
            color: #0f172a;
            text-align: right;
            flex: 1 1 auto;
            word-break: break-word;
        }

        .dashboard-v4 .verify-alert {
            margin-top: 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #b91c1c;
            padding: 11px 14px;
            font-size: 13px;
        }

        .dashboard-v4 .verify-alert a {
            color: #0f766e;
            text-decoration: underline;
            font-weight: 700;
        }

        .dashboard-v4 .admin-console-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .dashboard-v4 .admin-console-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 12px;
        }

        .dashboard-v4 .admin-console-k {
            font-size: 12px;
            color: #64748b;
        }

        .dashboard-v4 .admin-console-v {
            margin-top: 6px;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .dashboard-v4 .admin-console-chart {
            margin-top: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 10px;
            height: 320px;
        }

        @media (max-width: 1100px) {
            .dashboard-v4 .body-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .dashboard-v4 .core-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-v4 .quick-grid,
            .dashboard-v4 .limit-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-v4 .core-value {
                font-size: 24px;
            }

            .dashboard-v4 .admin-console-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

<x-app-layout>
    @php
        $usedBytes = $user->use_capacity * 1024;
        $totalBytes = max(0, $user->capacity * 1024);
        $freeBytes = max(0, ($user->capacity - $user->use_capacity) * 1024);
        $usagePercent = $user->capacity > 0 ? round(($user->use_capacity / $user->capacity) * 100, 1) : 0;
        $usagePercent = min(100, max(0, $usagePercent));
        $defaultStrategyId = (int) $user->configs->get(\App\Enums\UserConfigKey::DefaultStrategy, 0);
        $defaultStrategy = $strategies->firstWhere('id', $defaultStrategyId);
        $enabledApi = (bool) \App\Utils::config(\App\Enums\ConfigKey::IsEnableApi);
        $needVerify = (bool) \App\Utils::config(\App\Enums\ConfigKey::IsUserNeedVerify);
        $dailyLimit = (int) $configs->get(\App\Enums\GroupConfigKey::LimitPerDay);
        $monthlyLimit = (int) $configs->get(\App\Enums\GroupConfigKey::LimitPerMonth);
        $concurrentLimit = (int) $configs->get(\App\Enums\GroupConfigKey::ConcurrentUploadNum);
        $maxFileBytes = (int) $configs->get(\App\Enums\GroupConfigKey::MaximumFileSize) * 1024;
    @endphp

    <div class="dashboard-v4">
        <div class="core-grid">
            <div class="card core-card">
                <div class="core-label">图片总数</div>
                <div class="core-value">{{ $user->image_num }}</div>
                <div class="core-hint">当前账号下已上传图片数量</div>
            </div>
            <div class="card core-card">
                <div class="core-label">剩余空间</div>
                <div class="core-value">{{ \App\Utils::formatSize($freeBytes) }}</div>
                <div class="core-hint">总计 {{ \App\Utils::formatSize($totalBytes) }}，已用 {{ \App\Utils::formatSize($usedBytes) }}</div>
            </div>
        </div>

        <div class="body-grid">
            <div style="display:grid; gap:14px;">
                <section class="card">
                    <div class="panel-head">
                        <h3 class="panel-title">存储与上传限制</h3>
                        <span class="panel-sub">按当前用户组配置</span>
                    </div>
                    <div class="panel-body">
                        <div class="usage-row">
                            <div class="usage-meta">
                                <span>容量使用率</span>
                                <strong>{{ $usagePercent }}%</strong>
                            </div>
                            <div class="usage-track">
                                <div class="usage-fill" style="width: {{ $usagePercent }}%"></div>
                            </div>
                        </div>

                        <div class="limit-grid">
                            <div class="limit-item">
                                <div class="limit-k">单文件上限</div>
                                <div class="limit-v">{{ \App\Utils::formatSize($maxFileBytes) }}</div>
                            </div>
                            <div class="limit-item">
                                <div class="limit-k">并发上传</div>
                                <div class="limit-v">{{ $concurrentLimit }} 张</div>
                            </div>
                            <div class="limit-item">
                                <div class="limit-k">每日上传限制</div>
                                <div class="limit-v">{{ $dailyLimit > 0 ? $dailyLimit.' 张' : '不限' }}</div>
                            </div>
                            <div class="limit-item">
                                <div class="limit-k">每月上传限制</div>
                                <div class="limit-v">{{ $monthlyLimit > 0 ? $monthlyLimit.' 张' : '不限' }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="panel-head">
                        <h3 class="panel-title">可用存储策略</h3>
                        <span class="panel-sub">{{ $strategies->count() }} 个</span>
                    </div>
                    <div class="panel-body">
                        @if($strategies->isEmpty())
                            <x-no-data message="您所在的组还没有可用的储存策略，请联系管理员。" />
                        @else
                            <div class="strategy-list">
                                @foreach ($strategies as $strategy)
                                    <div class="strategy-item">
                                        <div>
                                            <p class="strategy-name">{{ $strategy->name }}</p>
                                            <p class="strategy-intro">{{ $strategy->intro ?: '暂无描述' }}</p>
                                        </div>
                                        @if($strategy->id === $defaultStrategyId)
                                            <span class="tag-default">默认</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div style="display:grid; gap:14px; align-content:start;">
                <section class="card">
                    <div class="panel-head">
                        <h3 class="panel-title">快捷入口</h3>
                    </div>
                    <div class="panel-body">
                        <div class="quick-grid">
                            <a href="{{ route('images') }}" class="quick-link"><i class="fas fa-cloud-upload-alt"></i> 立即上传</a>
                            <a href="{{ route('images') }}" class="quick-link"><i class="fas fa-images"></i> 图片管理</a>
                            <a href="{{ route('settings') }}" class="quick-link"><i class="fas fa-user-cog"></i> 账号设置</a>
                            @if($enabledApi)
                                <a href="{{ route('api') }}" class="quick-link"><i class="fas fa-link"></i> API 文档</a>
                            @else
                                <a href="javascript:void(0)" class="quick-link disabled"><i class="fas fa-link"></i> API 未启用</a>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="panel-head">
                        <h3 class="panel-title">账号信息</h3>
                    </div>
                    <div class="panel-body">
                        <div class="kv">
                            <div class="kv-row">
                                <span class="kv-k">账号邮箱</span>
                                <span class="kv-v">{{ $user->email }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-k">所属组</span>
                                <span class="kv-v">{{ $user->group ? $user->group->name : '系统默认组' }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-k">默认策略</span>
                                <span class="kv-v">{{ $defaultStrategy ? $defaultStrategy->name : '未设置' }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-k">邮箱验证</span>
                                <span class="kv-v">{{ $user->email_verified_at ? '已验证' : '未验证' }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        @if(!empty($adminConsole))
            <section class="card" style="margin-top:14px;">
                <div class="panel-head">
                    <h3 class="panel-title">站点控制台（管理员）</h3>
                    <span class="panel-sub">已迁移到仪表盘展示</span>
                </div>
                <div class="panel-body">
                    <div class="admin-console-grid">
                        <div class="admin-console-card">
                            <div class="admin-console-k">图片总量</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['overview']['images']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">相册总量</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['overview']['albums']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">用户总量</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['overview']['users']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">占用存储</div>
                            <div class="admin-console-v" style="font-size:20px;">{{ \App\Utils::formatSize($adminConsole['overview']['storage'] * 1024) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">今日上传</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['numbers']['today']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">昨日上传</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['numbers']['yesterday']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">本周上传</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['numbers']['week']) }}</div>
                        </div>
                        <div class="admin-console-card">
                            <div class="admin-console-k">本月上传</div>
                            <div class="admin-console-v">{{ \App\Utils::shortenNumber($adminConsole['numbers']['month']) }}</div>
                        </div>
                    </div>
                    <div id="dashboard-admin-chart" class="admin-console-chart"></div>
                </div>
            </section>
        @endif

        @if($needVerify && !$user->email_verified_at)
            <div class="verify-alert">
                你的账号尚未激活，功能受限，请根据激活邮件指引激活账号，如果你没有收到邮件，请点击
                <a id="send-verify-email" href="javascript:void(0)">这里</a>
                重新发送。
            </div>
        @endif
    </div>

    @if($needVerify && !$user->email_verified_at)
        @push('scripts')
            <script>
                $('#send-verify-email').click(function () {
                    if (! $(this).attr('disabled')) {
                        $(this).text('发送中...').attr('disabled');
                        axios.post('{{ route('verification.send') }}').then(response => {
                            toastr.success('发送成功，请注意查收。');
                        }).catch(error => {
                            if (error.response.status === 429) {
                                toastr.error('操作频繁，请稍后再试');
                            }
                        }).finally(_ => {
                            $(this).text('这里').attr('disabled');
                        });
                    }
                });
            </script>
        @endpush
    @endif

    @if(!empty($adminConsole))
        @push('scripts')
            <script src="{{ asset('js/echarts/echarts.min.js') }}"></script>
            <script>
                (function () {
                    const chartDom = document.getElementById('dashboard-admin-chart');
                    if (!chartDom || typeof echarts === 'undefined') return;
                    const chart = echarts.init(chartDom);
                    chart.setOption({
                        tooltip: {trigger: 'axis'},
                        legend: {
                            type: 'scroll',
                            data: @json($adminConsole['fields']),
                        },
                        grid: {
                            left: '3%',
                            right: '3%',
                            bottom: '3%',
                            containLabel: true,
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: @json($adminConsole['dates']),
                        },
                        yAxis: {
                            type: 'value',
                            minInterval: 1,
                        },
                        series: @json($adminConsole['datasets']),
                    });
                    window.addEventListener('resize', function () {
                        chart.resize();
                    });
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
