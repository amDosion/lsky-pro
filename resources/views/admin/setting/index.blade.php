@section('title', '系统设置')

@php
    $sections = [
        ['id' => 'general', 'eyebrow' => '基础', 'title' => '通用信息', 'description' => '维护站点名称、SEO 元信息和首页公告。', 'status' => '核心配置'],
        ['id' => 'control', 'eyebrow' => '访问', 'title' => '系统开关', 'description' => '控制注册、画廊、接口和游客上传等入口。', 'status' => '即时生效'],
        ['id' => 'user', 'eyebrow' => '容量', 'title' => '用户默认配额', 'description' => '设置新用户的初始可用容量。', 'status' => '运营规则'],
        ['id' => 'cost', 'eyebrow' => '成本', 'title' => '成本估算', 'description' => '维护存储单价和币种，用于统计页估算。', 'status' => '分析使用'],
        ['id' => 'mail', 'eyebrow' => '通知', 'title' => '邮件配置', 'description' => '校准 SMTP 参数并执行在线测试。', 'status' => '需要验证'],
        ['id' => 'upgrade', 'eyebrow' => '运维', 'title' => '系统升级', 'description' => '检查版本、查看更新说明并执行升级。', 'status' => '管理员专属'],
    ];

    $controlFlags = [
        (bool) $configs->get('is_enable_registration'),
        (bool) $configs->get('is_enable_gallery'),
        (bool) $configs->get('is_enable_api'),
        (bool) $configs->get('is_allow_guest_upload'),
        (bool) $configs->get('is_user_need_verify'),
    ];

    $enabledControlCount = collect($controlFlags)->filter()->count();
    $mailHost = $configs['mail']['mailers']['smtp']['host'] ?? '';
    $mailConfigured = filled($mailHost);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/markdown-css/github-markdown-light.css') }}">
    <style>
        .settings-shell {
            display: flex;
            flex-direction: column;
            gap: 16px;
            color: #0f172a;
        }

        .settings-hero {
            position: relative;
            overflow: hidden;
            border: 1px solid #dbe2ea;
            border-radius: 22px;
            padding: 22px;
            background:
                radial-gradient(circle at top right, rgba(251, 191, 36, .26), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #38bdf8 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .14);
        }

        .settings-hero::after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            right: -60px;
            bottom: -120px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            filter: blur(6px);
        }

        .settings-hero-copy {
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .settings-hero-eyebrow {
            font-size: 11px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .72);
        }

        .settings-hero-title {
            margin-top: 10px;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 800;
        }

        .settings-hero-text {
            margin-top: 10px;
            max-width: 680px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, .86);
        }

        .settings-stat-grid {
            position: relative;
            z-index: 1;
            margin-top: 18px;
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }

        .settings-stat-card {
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 16px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, .10);
            backdrop-filter: blur(8px);
        }

        .settings-stat-k {
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .68);
        }

        .settings-stat-v {
            margin-top: 6px;
            font-size: 22px;
            line-height: 1;
            font-weight: 800;
            color: #fff;
        }

        .settings-stat-sub {
            margin-top: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, .74);
        }

        .settings-layout {
            display: grid;
            gap: 14px;
            grid-template-columns: 280px minmax(0, 1fr);
            align-items: start;
        }

        .settings-aside {
            position: sticky;
            top: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .settings-card,
        .settings-panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
        }

        .settings-card-body {
            padding: 16px;
        }

        .settings-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .settings-card-sub {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .settings-nav {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .settings-nav-link {
            border: 1px solid transparent;
            border-radius: 14px;
            padding: 12px;
            display: block;
            transition: .2s ease;
        }

        .settings-nav-link:hover {
            border-color: #dbe2ea;
            background: #f8fafc;
        }

        .settings-nav-link.active {
            border-color: #bfdbfe;
            background: #eff6ff;
            box-shadow: inset 0 0 0 1px rgba(29, 78, 216, .08);
        }

        .settings-nav-link.active .settings-nav-label,
        .settings-nav-link.active .settings-nav-desc {
            color: #1d4ed8;
        }

        .settings-nav-label {
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .settings-nav-title {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .settings-nav-desc {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .settings-kv {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        .settings-kv-item {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            padding: 12px;
        }

        .settings-kv-key {
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .settings-kv-value {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .settings-main {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .settings-panel {
            overflow: hidden;
            scroll-margin-top: 14px;
        }

        .settings-panel-head {
            padding: 18px 18px 16px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .settings-panel-label {
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .settings-panel-title {
            margin-top: 8px;
            font-size: 20px;
            line-height: 1.2;
            font-weight: 800;
            color: #0f172a;
        }

        .settings-panel-desc {
            margin-top: 6px;
            max-width: 620px;
            font-size: 13px;
            line-height: 1.7;
            color: #64748b;
        }

        .settings-chip {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            border-radius: 999px;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
        }

        .settings-chip.success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #15803d;
        }

        .settings-chip.warn {
            border-color: #fde68a;
            background: #fffbeb;
            color: #a16207;
        }

        .settings-panel-body {
            padding: 18px;
        }

        .settings-form-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .settings-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .settings-field-span-2 {
            grid-column: span 2;
        }

        .settings-label {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .settings-help {
            font-size: 12px;
            line-height: 1.6;
            color: #64748b;
        }

        .settings-note {
            margin-top: 14px;
            border-radius: 14px;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            padding: 12px 14px;
            font-size: 12px;
            line-height: 1.6;
            color: #475569;
        }

        .settings-actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .settings-toggle-grid,
        .settings-inline-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .settings-toggle-card,
        .settings-mini-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #f8fafc;
            padding: 14px;
        }

        .settings-toggle-card {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
        }

        .settings-toggle-title,
        .settings-mini-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .settings-toggle-copy,
        .settings-mini-sub {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.6;
            color: #64748b;
        }

        .settings-mini-value {
            margin-top: 10px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .settings-stack {
            display: grid;
            gap: 14px;
        }

        .settings-upgrade-board {
            border: 1px dashed #bfdbfe;
            border-radius: 18px;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            padding: 14px;
        }

        .settings-upgrade-state {
            min-height: 132px;
            border-radius: 14px;
            border: 1px solid #dbe2ea;
            background: rgba(255, 255, 255, .92);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }

        .settings-upgrade-state span {
            font-size: 13px;
            color: #475569;
        }

        .settings-upgrade-card {
            border: 1px solid #dbe2ea;
            border-radius: 16px;
            background: rgba(255, 255, 255, .92);
            padding: 16px;
        }

        .settings-upgrade-head {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .settings-upgrade-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            object-fit: cover;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .12);
        }

        .settings-upgrade-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .settings-upgrade-meta {
            font-size: 12px;
            line-height: 1.7;
            color: #64748b;
        }

        .settings-upgrade-actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
        }

        .settings-upgrade-actions a {
            text-decoration: none;
        }

        .settings-upgrade-actions a[disabled] {
            pointer-events: none;
        }

        @media (max-width: 1024px) {
            .settings-layout {
                grid-template-columns: 1fr;
            }

            .settings-aside {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .settings-hero {
                padding: 18px;
                border-radius: 18px;
            }

            .settings-hero-title {
                font-size: 24px;
            }

            .settings-panel-head {
                flex-direction: column;
            }

            .settings-form-grid {
                grid-template-columns: 1fr;
            }

            .settings-field-span-2 {
                grid-column: span 1;
            }

            .settings-toggle-card {
                flex-direction: column;
            }
        }
    </style>
@endpush

<x-app-layout>
    <div class="settings-shell">
        <section class="settings-hero">
            <div class="settings-hero-copy">
                <div class="settings-hero-eyebrow">Admin Control Center</div>
                <h1 class="settings-hero-title">系统设置工作台</h1>
                <p class="settings-hero-text">
                    把站点配置、访问开关、通知链路和升级入口集中到一个可导航的页面里。
                    本次重构只调整信息架构和界面壳层，保存接口、字段名和运维动作保持不变。
                </p>
            </div>

            <div class="settings-stat-grid">
                <div class="settings-stat-card">
                    <div class="settings-stat-k">配置分区</div>
                    <div class="settings-stat-v">{{ count($sections) }}</div>
                    <div class="settings-stat-sub">覆盖站点、通知、升级与成本配置</div>
                </div>
                <div class="settings-stat-card">
                    <div class="settings-stat-k">已启用开关</div>
                    <div class="settings-stat-v">{{ $enabledControlCount }}</div>
                    <div class="settings-stat-sub">共 5 项核心访问控制</div>
                </div>
                <div class="settings-stat-card">
                    <div class="settings-stat-k">SMTP 状态</div>
                    <div class="settings-stat-v">{{ $mailConfigured ? '已接入' : '待配置' }}</div>
                    <div class="settings-stat-sub">{{ $mailConfigured ? $mailHost : '建议先完成邮件测试后再启用账号验证' }}</div>
                </div>
                <div class="settings-stat-card">
                    <div class="settings-stat-k">当前版本</div>
                    <div class="settings-stat-v">{{ \App\Utils::config(\App\Enums\ConfigKey::AppVersion) }}</div>
                    <div class="settings-stat-sub">升级检查与安装操作仍由现有服务执行</div>
                </div>
            </div>
        </section>

        <div class="settings-layout">
            <aside class="settings-aside">
                <div class="settings-card">
                    <div class="settings-card-body">
                        <div class="settings-card-title">快速导航</div>
                        <div class="settings-card-sub">按主题切换到对应配置区，避免在单页里来回滚动查找。</div>

                        <nav class="settings-nav">
                            @foreach($sections as $section)
                                <a class="settings-nav-link" href="#{{ $section['id'] }}">
                                    <div class="settings-nav-label">{{ $section['eyebrow'] }}</div>
                                    <div class="settings-nav-title">{{ $section['title'] }}</div>
                                    <div class="settings-nav-desc">{{ $section['description'] }}</div>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="settings-card-body">
                        <div class="settings-card-title">当前摘要</div>
                        <div class="settings-kv">
                            <div class="settings-kv-item">
                                <div class="settings-kv-key">应用名称</div>
                                <div class="settings-kv-value">{{ $configs->get('app_name') ?: '未设置' }}</div>
                            </div>
                            <div class="settings-kv-item">
                                <div class="settings-kv-key">默认用户容量</div>
                                <div class="settings-kv-value">{{ $configs->get('user_initial_capacity') ?: '0' }} KB</div>
                            </div>
                            <div class="settings-kv-item">
                                <div class="settings-kv-key">存储估算币种</div>
                                <div class="settings-kv-value">{{ $configs->get('storage_cost_currency', 'CNY') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="settings-main">
                @foreach($sections as $section)
                    <section id="{{ $section['id'] }}" class="settings-panel">
                        <div class="settings-panel-head">
                            <div>
                                <div class="settings-panel-label">{{ $section['eyebrow'] }}</div>
                                <h2 class="settings-panel-title">{{ $section['title'] }}</h2>
                                <p class="settings-panel-desc">{{ $section['description'] }}</p>
                            </div>
                            <span class="settings-chip {{ $section['id'] === 'mail' && ! $mailConfigured ? 'warn' : ($section['id'] === 'control' ? 'success' : '') }}">
                                {{ $section['status'] }}
                            </span>
                        </div>
                        <div class="settings-panel-body">
                            @include('admin.setting.partials.'.$section['id'])
                        </div>
                    </section>
                @endforeach
            </main>
        </div>
    </div>

    <script type="text/html" id="update-tpl">
        <div class="settings-upgrade-card">
            <div class="settings-upgrade-head">
                <img id="icon" src="__icon__" alt="icon" class="settings-upgrade-icon" style="animation-duration: 5s">
                <div>
                    <p class="settings-upgrade-title">Lsky Pro __name__</p>
                    <p class="settings-upgrade-meta">__size__</p>
                    <p class="settings-upgrade-meta">发布于 __pushed_at__</p>
                </div>
            </div>
            <p id="upgrade-message" class="mt-4 text-sm text-gray-500"></p>
            <div class="mt-4 text-sm markdown-body">
                __changelog__
            </div>
            <div class="settings-upgrade-actions">
                <a href="javascript:void(0)" id="install" class="rounded-md px-4 py-2 bg-blue-500 text-white">立即安装</a>
            </div>
        </div>
    </script>

    @push('scripts')
        <script>
            let setSelected = function () {
                $('[data-select]').each(function () {
                    $(`[data-${$(this).data('select')}-driver=${$(this).val()}]`)[this.checked ? 'show' : 'hide']();
                });
            };

            setSelected();

            $('[data-select]').on('click change', function () {
                setSelected();
            });

            let setActiveNav = function (id) {
                $('.settings-nav-link').each(function () {
                    $(this).toggleClass('active', $(this).attr('href') === `#${id}`);
                });
            };

            let observeSections = function () {
                let sections = Array.from(document.querySelectorAll('.settings-panel[id]'));
                if (! sections.length) {
                    return;
                }

                setActiveNav(sections[0].id);

                if (! ('IntersectionObserver' in window)) {
                    return;
                }

                let observer = new IntersectionObserver((entries) => {
                    let current = entries
                        .filter(entry => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

                    if (current) {
                        setActiveNav(current.target.id);
                    }
                }, {
                    rootMargin: '-18% 0px -56% 0px',
                    threshold: [0.2, 0.45, 0.7]
                });

                sections.forEach(section => observer.observe(section));
            };

            $('.settings-nav-link').on('click', function () {
                let target = $(this).attr('href').replace('#', '');
                setActiveNav(target);
            });

            observeSections();

            $('form[data-settings-form="save"]').submit(function (e) {
                e.preventDefault();
                axios.put(this.action, $(this).serialize()).then(function (response) {
                    toastr[response.data.status ? 'success' : 'error'](response.data.message)
                });
            });

            $('#mail-test').click(function () {
                Swal.fire({
                    title: '请输入接收测试邮件的邮箱',
                    input: 'text',
                    inputValue: '',
                    inputAttributes: {
                        type: 'email',
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: '确认',
                    showLoaderOnConfirm: true,
                    preConfirm: (value) => {
                        return axios.post('{{ route('admin.settings.mail.test') }}', {
                            email: value,
                        }).then(response => {
                            if (! response.data.status) {
                                throw new Error(response.data.message)
                            }
                            return response.data;
                        }).catch(error => Swal.showValidationMessage(error));
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        toastr[result.value.status ? 'success' : 'warning'](result.value.message);
                    }
                })
            });

            let timer;
            let upgrade = function () {
                return {
                    start: function () {
                        $('#icon').addClass('animate-spin')
                        $('#install').attr('disabled', true).removeClass('bg-blue-500').addClass('cursor-not-allowed bg-gray-400').text('执行升级中...')
                        $('#upgrade-message').text('准备升级...').removeClass('text-red-500').addClass('text-gray-500');

                        timer = setInterval(getProgress, 1500);
                        axios.post('{{ route('admin.settings.upgrade') }}');
                    },
                    stop: function () {
                        $('#icon').removeClass('animate-spin')
                        $('#install').attr('disabled', false).removeClass('cursor-not-allowed bg-gray-400').addClass('bg-blue-500').text('立即安装')
                        clearInterval(timer);
                    }
                };
            };

            let getVersion = function (callback) {
                $('#check-update').show();
                axios.get('{{ route('admin.settings.check.update') }}').then(response => {
                    if (response.data.status && response.data.data.is_update) {
                        $('#check-update').hide();
                        let version = response.data.data.version;
                        let html = $('#update-tpl').html()
                            .replace(/__icon__/g, version.icon)
                            .replace(/__name__/g, version.name)
                            .replace(/__size__/g, version.size)
                            .replace(/__pushed_at__/g, version.pushed_at)
                            .replace(/__changelog__/g, version.changelog);
                        $('#have-update').html(html).show();
                        $('.markdown-body a').attr('target', '_blank');
                        callback && callback(version);
                    } else {
                        $('#not-update').show();
                        $('#check-update').hide();
                    }
                });
            }

            let getProgress = function () {
                axios.get('{{ route('admin.settings.upgrade.progress') }}').then(response => {
                    $('#upgrade-message').text(response.data.data.message);
                    if (response.data.data.status === 'success') {
                        $('#upgrade-message').removeClass('text-gray-500').addClass('text-green-500');
                        $('#install').hide();
                    }
                    if (response.data.data.status === 'fail') {
                        $('#upgrade-message').removeClass('text-gray-500').addClass('text-red-500');
                    }
                    if (response.data.data.status !== 'installing') {
                        upgrade().stop();
                    }
                });
            };

            $(document).on('click', '#install', function () {
                if ($(this).attr('disabled')) {
                    return;
                }
                upgrade().start();
            });

            @if(cache()->has('upgrade_progress'))
                getVersion(() => {
                    $('#icon').addClass('animate-spin')
                    $('#install').attr('disabled', true).removeClass('bg-blue-500').addClass('cursor-not-allowed bg-gray-400').text('正在升级...')
                    $('#upgrade-message').text('请稍等...').removeClass('text-red-500').addClass('text-gray-500');

                    timer = setInterval(getProgress, 1500);
                });
            @else
                getVersion();
            @endif
        </script>
    @endpush
</x-app-layout>
