@section('title', '图片管理')

<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/justified-gallery/justifiedGallery.min.css') }}">
    <style>
        @include('components.images-workspace-styles')
        @include('components.images-loading-styles')

        .admin-images-v4 .panel {
            overflow: hidden;
            min-height: 0;
        }

        .admin-images-v4 .images-tree-list {
            gap: 6px;
            border-bottom: 0;
        }

        .admin-images-v4 .images-aside-head {
            justify-content: flex-start;
        }

        .admin-images-v4 .images-aside-head-main {
            min-width: 0;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-images-v4 .aside-tree-search {
            flex: 1 1 auto;
            min-width: 0;
            height: 30px;
            padding: 0 10px;
            border: 0;
            outline: none;
            border-radius: 8px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
        }

        .admin-images-v4 .tree-empty-tip {
            display: none;
            padding: 6px 10px 2px;
            font-size: 12px;
            color: #94a3b8;
        }

        .admin-images-v4 .tree-empty-tip.show {
            display: block;
        }

        .admin-images-v4 .tree-label {
            font-size: 11px;
            color: #64748b;
            letter-spacing: .02em;
            margin-bottom: 6px;
        }

        .admin-images-v4 .tree-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            height: 34px;
            padding: 0 10px;
            font-size: 12px;
            color: #334155;
            background: #f8fafc;
            margin-bottom: 0;
        }

        .admin-images-v4 .tree-link:hover {
            border-color: #dbeafe;
        }

        .admin-images-v4 .tree-link.active {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .admin-images-v4 .tree-link-name {
            flex: 1 1 auto;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-images-v4 .tree-link-count {
            color: #64748b;
            font-size: 11px;
            flex: 0 0 auto;
        }

        .admin-images-v4 .toolbar-left,
        .admin-images-v4 .toolbar-right,
        .admin-images-v4 .toolbar-action-groups {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-images-v4 .toolbar-left {
            flex: 1 1 540px;
            min-width: 260px;
        }

        .admin-images-v4 .toolbar-right {
            flex: 0 0 auto;
            min-width: 0;
        }

        .admin-images-v4 .images-stage {
            padding: 10px;
        }

        .admin-images-v4 .grid-wrap {
            display: block;
            padding: 0;
            opacity: 1;
            visibility: visible;
            transition: opacity .14s ease, visibility .14s ease;
        }

        .admin-images-v4 .grid-wrap.is-layout-pending {
            opacity: 0;
            visibility: hidden;
        }

        .admin-images-v4 .grid-wrap.hidden,
        .admin-images-v4 .list-wrap.hidden {
            display: none !important;
        }

        .admin-image-item {
            position: relative;
            border: 1px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            cursor: pointer;
            transition: border-color .14s ease, box-shadow .14s ease;
        }

        .admin-image-item:hover {
            border-color: #dbeafe;
        }

        .admin-image-item.selected {
            border-color: #60a5fa;
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, .18);
        }

        .admin-image-item img {
            width: 100%;
            height: auto;
            object-fit: cover;
            display: block;
            background: #f1f5f9;
        }

        .admin-image-item .image-mask {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 84px;
            z-index: 1;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, .88) 100%);
        }

        .admin-image-item .item-meta {
            position: absolute;
            left: 8px;
            right: 8px;
            bottom: 8px;
            z-index: 2;
            display: grid;
            gap: 2px;
            color: #fff;
        }

        .admin-image-item .item-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .admin-image-item .item-sub {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            color: #dbeafe;
        }

        .admin-images-v4 .list-wrap {
            background: transparent;
            border: 0;
            border-radius: 0;
            overflow: visible;
        }

        .admin-images-v4 .list-head,
        .admin-images-v4 .list-row {
            display: grid;
            grid-template-columns: 100px 88px minmax(220px, 1fr) 120px 100px 150px 280px;
            gap: 10px;
            align-items: center;
        }

        .admin-images-v4 .list-head {
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            padding: 0 10px;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .admin-images-v4 .list-row {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            padding: 6px 10px;
            min-height: 72px;
            overflow: visible;
            margin-top: 8px;
            font-size: 12px;
            color: #334155;
        }

        .admin-images-v4 .list-row.selected {
            border-color: #60a5fa;
            background: #eff6ff;
        }

        .admin-images-v4 .list-row.selected:hover,
        .admin-images-v4 .list-row.selected:focus-within {
            border-color: #93c5fd;
            background: #eff6ff;
        }

        .admin-images-v4 .list-col {
            min-width: 0;
        }

        .admin-images-v4 .list-thumb-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 88px;
            height: 58px;
            border-radius: 6px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .admin-images-v4 .images-list-thumb {
            width: 88px;
            height: 58px;
            object-fit: cover;
            display: block;
        }

        .admin-images-v4 .list-type {
            font-size: 12px;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .admin-images-v4 .list-url {
            min-width: 0;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .admin-images-v4 .list-url-text {
            display: block;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            color: #0f172a;
        }

        .admin-images-v4 .list-resolution,
        .admin-images-v4 .list-size,
        .admin-images-v4 .list-date {
            font-size: 12px;
            font-variant-numeric: tabular-nums;
            color: #334155;
            white-space: nowrap;
        }

        .admin-images-v4 .list-ops {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            white-space: nowrap;
            min-width: max-content;
        }

        .admin-images-v4 .list-op-group {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, visibility .16s ease;
            flex: 0 0 auto;
        }

        .admin-images-v4 .list-row:hover .list-op-group,
        .admin-images-v4 .list-row:focus-within .list-op-group {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .admin-images-v4 .list-op-btn {
            height: 26px;
            border: 1px solid #dbe2ea;
            border-radius: 7px;
            background: #f8fafc;
            color: #334155;
            padding: 0 8px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex: 0 0 auto;
        }

        .admin-images-v4 .list-op-group .list-op-btn {
            border: 0;
            border-radius: 0;
            background: transparent;
            height: 28px;
            padding: 0 8px;
        }

        .admin-images-v4 .list-op-group .list-op-btn + .list-op-btn {
            border-left: 1px solid #dbe2ea;
        }

        .admin-images-v4 .toolbar-meta-btn:disabled,
        .admin-images-v4 .toolbar-action-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .admin-images-v4 .image-selector {
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        .admin-images-v4 .list-row .image-selector {
            position: static !important;
            width: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .admin-images-v4 .list-row .image-selector .text-xl {
            font-size: 17px;
            line-height: 1;
        }

        .admin-images-v4 .image-selector i {
            color: #ffffff;
            border-color: #94a3b8;
            background: #ffffff;
            transition: color .16s ease, border-color .16s ease, background-color .16s ease;
        }

        .admin-images-v4 .admin-image-item .image-selector {
            position: absolute;
            z-index: 3;
            top: 0;
            right: 0;
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease;
        }

        .admin-images-v4 .admin-image-item:hover .image-selector,
        .admin-images-v4 .admin-image-item.selected .image-selector {
            opacity: 1;
            pointer-events: auto;
        }

        .admin-images-v4 .admin-image-item.selected .image-selector i,
        .admin-images-v4 .list-row.selected .image-selector i {
            color: #2563eb;
            border-color: #2563eb;
            background: #ffffff;
        }

        @include('components.media-carousel-styles')

        .admin-carousel {
            --media-carousel-overlay: #ffffff;
        }

        @media (max-width: 1024px) {
            .admin-images-v4 .toolbar-left,
            .admin-images-v4 .toolbar-right {
                width: 100%;
            }

            .admin-images-v4 .toolbar-action-groups {
                flex-wrap: wrap;
            }
        }
    </style>

    @php
        $keywords = trim((string) request('keywords', ''));
        $activeExact = fn(string $token) => $keywords === $token;
        $activeUid = null;
        if (str_starts_with($keywords, 'uid:')) {
            $activeUid = (int) substr($keywords, 4);
        }
        $orderText = match ($keywords) {
            'order:earliest' => '最早',
            'order:utmost' => '最大',
            'order:least' => '最小',
            default => '最新',
        };
        $statusText = match ($keywords) {
            'is:public' => '公开',
            'is:private' => '私有',
            'is:unhealthy' => '违规',
            default => '全部',
        };
    @endphp

    <div class="admin-page-v2 admin-images-v4" id="admin-images-v4">
        <aside class="images-aside panel">
            <div class="images-aside-head">
                <div class="images-aside-head-main">
                    <div class="images-aside-title">用户筛选树</div>
                    <input type="text" id="user-tree-search" class="aside-tree-search" placeholder="筛选用户...">
                </div>
            </div>
            <div class="images-tree-list">
                <div class="tree-label">上传用户</div>
                <a class="tree-link {{ $keywords === '' ? 'active' : '' }}" href="{{ route('admin.images') }}"><span class="tree-link-name">全部图片</span></a>
                <a class="tree-link {{ $activeExact('is:guest') ? 'active' : '' }}" href="{{ route('admin.images', ['keywords' => 'is:guest']) }}"><span class="tree-link-name">游客上传</span></a>
                @foreach($users as $user)
                    <a class="tree-link js-user-tree-link {{ $activeUid === $user->id ? 'active' : '' }}" href="{{ route('admin.images', ['keywords' => 'uid:'.$user->id]) }}">
                        <span class="tree-link-name">{{ $user->name }}</span>
                        <span class="tree-link-count">{{ $user->images_count }}</span>
                    </a>
                @endforeach
                <div id="user-tree-empty" class="tree-empty-tip">没有匹配的用户</div>
            </div>
        </aside>

        <section class="images-main panel">
            <form class="images-toolbar" action="{{ route('admin.images') }}" method="get" id="admin-images-toolbar">
                <input type="hidden" id="per-page-input" name="per_page" value="{{ $perPage ?? 50 }}">
                <div class="toolbar-left">
                    <div class="hidden lg:block">
                        <div class="toolbar-action-groups">
                            <div class="toolbar-action-group">
                                <button type="submit" class="toolbar-action-btn"><i class="fas fa-search"></i>搜索</button>
                                <a href="{{ route('admin.images') }}" class="toolbar-action-btn"><i class="fas fa-undo"></i>重置</a>
                                <button type="button" id="grammar" class="toolbar-action-btn"><i class="fas fa-question-circle"></i>语法</button>
                            </div>
                            <div class="toolbar-action-group">
                                <button type="button" id="select-all" class="toolbar-action-btn"><i class="fas fa-check-square"></i>全选</button>
                                <button type="button" id="batch-delete" class="toolbar-action-btn" disabled><i class="fas fa-trash"></i>批量删除</button>
                            </div>
                        </div>
                    </div>
                    <div class="block lg:hidden">
                        <x-dropdown direction="right">
                            <x-slot name="trigger">
                                <button type="button" class="text-sm py-2 px-3 hover:bg-gray-100 rounded text-gray-800"><i class="fas fa-ellipsis-h text-blue-500"></i></button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link data-admin-action="submit" href="javascript:void(0)" @click="open = false">搜索</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.images') }}" @click="open = false">重置</x-dropdown-link>
                                <x-dropdown-link data-admin-action="grammar" href="javascript:void(0)" @click="open = false">语法</x-dropdown-link>
                                <x-dropdown-link data-admin-action="select-all" href="javascript:void(0)" @click="open = false">全选 / 反选</x-dropdown-link>
                                <x-dropdown-link data-admin-action="batch-delete" href="javascript:void(0)" @click="open = false">批量删除</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
                <div class="toolbar-right">
                    <input type="text" id="keywords-input" name="keywords" class="h-[30px] px-2.5 py-0 border-0 outline-none rounded bg-gray-100 text-sm transition-all duration-300 hidden md:block md:w-36 md:hover:w-52 md:focus:w-52" placeholder="输入关键字搜索..." value="{{ request('keywords') }}" />
                    <div class="toolbar-meta-group">
                        <button type="button" id="view-grid" class="view-switch-btn toolbar-meta-btn active" title="网格"><i class="fas fa-th"></i></button>
                        <button type="button" id="view-list" class="view-switch-btn toolbar-meta-btn" title="列表"><i class="fas fa-list"></i></button>
                    </div>
                    <div class="toolbar-meta-group">
                        <x-dropdown direction="left">
                            <x-slot name="trigger">
                                <button type="button" class="toolbar-meta-btn">
                                    <span>{{ $orderText }}</span>
                                    <i class="fas fa-sort-alpha-up text-blue-500"></i>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('')">最新</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('order:earliest')">最早</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('order:utmost')">最大</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('order:least')">最小</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                        <x-dropdown direction="left">
                            <x-slot name="trigger">
                                <button type="button" class="toolbar-meta-btn">
                                    <span>{{ $statusText }}</span>
                                    <i class="fas fa-eye text-blue-500"></i>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('')">全部</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('is:public')">公开</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('is:private')">私有</x-dropdown-link>
                                <x-dropdown-link href="javascript:void(0)" @click="open = false" onclick="setQuickKeyword('is:unhealthy')">违规</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </form>

            <div class="images-stage relative inset-0 h-full overflow-y-auto select-none" id="admin-images-stage">
                <x-images-loading-skeleton id="admin-loading" :show="true" />
                <div id="grid-view" class="grid-wrap hidden"></div>

                <div id="list-view" class="list-wrap hidden">
                    <div class="list-head">
                        <div>缩略图</div>
                        <div>类型</div>
                        <div>URL</div>
                        <div>分辨率</div>
                        <div>大小</div>
                        <div>上传时间</div>
                        <div class="text-right">操作</div>
                    </div>
                </div>

                <div id="admin-empty" class="hidden p-4">
                    <x-no-data message="这里还是空的～" />
                </div>
            </div>

            <div class="images-footer">
                <div class="images-pagination">
                    <button type="button" id="images-page-prev" class="pager-btn">上一页</button>
                    <span id="images-page-info" class="pager-info">第 {{ $images->currentPage() }} / {{ $images->lastPage() }} 页，共 {{ $images->total() }} 条</span>
                    <button type="button" id="images-page-next" class="pager-btn">下一页</button>
                    <span class="images-footer-label">每页</span>
                    <select id="images-page-size" class="pager-select">
                        @foreach([50, 100, 150, 200] as $size)
                            <option value="{{ $size }}" @selected(($perPage ?? 50) == $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="images-footer-label">前往</span>
                    <input id="images-page-jump" class="pager-jump" type="number" min="1" step="1" placeholder="页码">
                    <button type="button" id="images-page-go" class="pager-btn">确定</button>
                </div>
            </div>
        </section>
    </div>

    <x-media-carousel
        id-prefix="admin-carousel"
        root-class="admin-carousel"
        host-mode="panel"
        :show-status="false"
        :show-caption="false"
    >
        <x-slot name="actions">
            <div class="images-carousel-action-group">
                <button type="button" class="images-carousel-action" id="admin-carousel-rename"><i class="fas fa-pen"></i>重命名</button>
                <button type="button" class="images-carousel-action is-danger" id="admin-carousel-delete"><i class="fas fa-trash"></i>删除</button>
            </div>
        </x-slot>
    </x-media-carousel>
    <x-modal id="content-modal"><div id="modal-content"></div></x-modal>

    <script type="text/html" id="search-grammar-tpl">
        <p class="text-gray-600 mb-2"><b>name:张三 email:a@qq.com extension:jpg</b></p>
        <p class="text-gray-600">支持限定符：name/album/group/strategy/email/extension/md5/sha1/ip/is/order</p>
    </script>

    @push('scripts')
        <script src="{{ asset('js/justified-gallery/jquery.justifiedGallery.min.js') }}"></script>
        <script src="{{ asset('static/js/media-carousel-shared.js') }}?v={{ filemtime(public_path('static/js/media-carousel-shared.js')) }}"></script>
        <script>
            const modal = Alpine.store('modal');
            const {
                escapeHtml,
                copyText,
                renderThumbButtons,
                normalizeLoopIndex: normalizeCarouselIndex,
                setPanelScrollLocked: setCarouselScrollLocked,
            } = window.LskyMediaCarousel;
            const gridConfigs = {
                rowHeight: 180,
                margins: 16,
                captions: false,
                border: 10,
                waitThumbnailsLoad: false,
            };
            const selected = new Set();
            const toolbarForm = document.getElementById('admin-images-toolbar');
            const keywordsInput = document.getElementById('keywords-input');
            const userTreeSearchInput = document.getElementById('user-tree-search');
            const userTreeEmpty = document.getElementById('user-tree-empty');
            const perPageInput = document.getElementById('per-page-input');
            const $gridView = $('#grid-view');
            const $listView = $('#list-view');
            const $stage = $('#admin-images-stage');
            const $loading = $('#admin-loading');
            const $empty = $('#admin-empty');
            const $pagePrev = $('#images-page-prev');
            const $pageNext = $('#images-page-next');
            const $pageInfo = $('#images-page-info');
            const $pageSize = $('#images-page-size');
            const $pageJump = $('#images-page-jump');
            const $pageGo = $('#images-page-go');
            const VIEW_KEY = 'admin_images_view_mode';
            const $carousel = $('#admin-carousel');
            const $carouselImg = $('#admin-carousel-img');
            const $carouselMeta = $('#admin-carousel-detail');
            const $carouselThumbs = $('#admin-carousel-thumbs');
            const $carouselIndex = $('#admin-carousel-index');
            const $carouselTop = $('#admin-carousel-top');
            const displayNameOf = (image) => String(image?.filename || image?.alias_name || image?.origin_name || image?.name || '').trim() || '-';
            const state = {
                keywords: @json((string) request('keywords', '')),
                perPage: Number(@json((int) ($perPage ?? 50))),
                currentPage: Number(@json((int) $images->currentPage())),
                lastPage: Number(@json((int) $images->lastPage())),
                total: Number(@json((int) $images->total())),
                loading: false,
            };
            let viewMode = localStorage.getItem(VIEW_KEY) || 'grid';
            let carouselItems = [];
            let carouselIndex = 0;

            function syncUserTreeFilter() {
                if (!userTreeSearchInput) return;
                const keyword = String(userTreeSearchInput.value || '').trim().toLowerCase();
                let visibleUsers = 0;
                $('.js-user-tree-link').each(function () {
                    const text = String($(this).text() || '').trim().toLowerCase();
                    const matched = !keyword || text.includes(keyword);
                    $(this).toggleClass('hidden', !matched);
                    if (matched) visibleUsers++;
                });
                if (userTreeEmpty) {
                    userTreeEmpty.classList.toggle('show', !!keyword && visibleUsers === 0);
                }
            }

            function setQuickKeyword(token) {
                if (!toolbarForm || !keywordsInput) return false;
                keywordsInput.value = token || '';
                toolbarForm.submit();
                return false;
            }

            function formatSize(sizeKb) {
                return utils.formatSize((Number(sizeKb || 0)) * 1024);
            }

            function syncPagination() {
                const current = Math.max(1, Number(state.currentPage || 1));
                const last = Math.max(1, Number(state.lastPage || 1));
                const total = Math.max(0, Number(state.total || 0));
                $pageInfo.text(`第 ${current} / ${last} 页，共 ${total} 条`);
                $pagePrev.prop('disabled', current <= 1 || state.loading);
                $pageNext.prop('disabled', current >= last || state.loading);
                $pageSize.val(String(state.perPage));
                if (perPageInput) perPageInput.value = String(state.perPage);
            }

            function syncLoadingUi() {
                const shouldShow = state.loading && allItemIds().length === 0;
                $loading.toggleClass('is-list', viewMode === 'list');
                $loading.toggleClass('show', shouldShow);
                $loading.toggleClass('hidden', !shouldShow);
            }

            function syncToolbarState() {
                $('#view-grid, #view-list').prop('disabled', state.loading);
                $('#keywords-input').prop('disabled', state.loading);
                $('#grammar').prop('disabled', state.loading);
            }

            function syncEmptyState() {
                const count = allItemIds().length;
                const hasItems = count > 0;
                $empty.toggleClass('hidden', hasItems);
                if (!hasItems) {
                    $gridView.addClass('hidden');
                    $listView.addClass('hidden');
                    selected.clear();
                } else {
                    if (viewMode === 'list') {
                        $listView.removeClass('hidden');
                        $gridView.addClass('hidden');
                    } else {
                        $gridView.removeClass('hidden');
                        $listView.addClass('hidden');
                    }
                }
            }

            function allItemIds() {
                const ids = new Set();
                $('.item[data-id]').each(function () {
                    ids.add(String($(this).data('id')));
                });
                return Array.from(ids);
            }

            function syncSelectedUi() {
                const count = selected.size;
                $('#batch-delete').prop('disabled', state.loading || count === 0).text(count > 0 ? `批量删除(${count})` : '批量删除');
                const ids = allItemIds();
                const isAllSelected = ids.length > 0 && ids.every(id => selected.has(id));
                $('#select-all').text(isAllSelected ? '反选' : '全选').prop('disabled', state.loading || ids.length === 0);
                $('.item').each(function () {
                    const id = String($(this).data('id') || '');
                    const isOn = selected.has(id);
                    $(this).toggleClass('selected', isOn);
                });
            }

            function setViewMode(mode) {
                viewMode = mode === 'list' ? 'list' : 'grid';
                localStorage.setItem(VIEW_KEY, viewMode);
                $('#view-grid').toggleClass('active', viewMode === 'grid');
                $('#view-list').toggleClass('active', viewMode === 'list');
                syncLoadingUi();
                if ($('.item[data-id]').length === 0) return;
                if (viewMode === 'list') {
                    $gridView.justifiedGallery('destroy');
                    $listView.removeClass('hidden');
                    $gridView.addClass('hidden');
                } else {
                    $gridView.removeClass('hidden');
                    $listView.addClass('hidden');
                    syncGridGallery();
                }
            }

            function createGridItem(image) {
                const id = String(image.id);
                const type = escapeHtml(String(image.extension || '').toUpperCase());
                const rawName = displayNameOf(image);
                const name = escapeHtml(rawName);
                const thumb = escapeHtml(image.thumb_url || '');
                const preview = escapeHtml(image.preview_url || image.thumb_url || image.url || '');
                const date = escapeHtml(image.created_at || '');
                const unhealthyTag = image.is_unhealthy ? '<span class="bg-red-500 text-white rounded-md text-xs px-1">违规</span>' : '';
                const html = `
                    <a href="javascript:void(0)" data-id="${id}" class="admin-image-item images-item item group relative cursor-default rounded outline outline-2 outline-offset-2 outline-transparent">
                        <button type="button" class="image-selector" title="选择">
                            <div class="p-1 text-xl sm:text-2xl">
                                <i class="fas fa-check-circle block rounded-full bg-white text-white border border-gray-500"></i>
                            </div>
                        </button>
                        <div class="flex absolute top-1 left-1 z-[2] space-x-1">
                            ${unhealthyTag}
                            <span class="bg-white rounded-md text-xs px-1">${type}</span>
                            <span class="bg-slate-900/70 text-white rounded-md text-xs px-1">#${id}</span>
                        </div>
                        <div class="image-mask"></div>
                        <div class="item-meta">
                            <p class="item-name" title="${name}">${name}</p>
                            <p class="item-sub" title="${date}">${date}</p>
                        </div>
                        <img src="${thumb}" data-original="${preview}" alt="${name}" width="${Math.max(Number(image.width || 0), 1)}" height="${Math.max(Number(image.height || 0), 1)}">
                    </a>
                `;
                const $el = $(html);
                $el.data('json', image);
                return $el;
            }

            function createListRow(image) {
                const id = String(image.id);
                const type = escapeHtml(String(image.extension || '').toUpperCase());
                const url = escapeHtml(image.url || '');
                const thumb = escapeHtml(image.thumb_url || '');
                const date = escapeHtml(image.created_at || '');
                const resolution = `${Number(image.width || 0)} × ${Number(image.height || 0)}`;
                const name = escapeHtml(displayNameOf(image));
                const html = `
                    <div data-id="${id}" class="list-row item">
                        <div class="list-col list-thumb-wrap"><img src="${thumb}" class="images-list-thumb" alt="${name}" width="${Math.max(Number(image.width || 0), 1)}" height="${Math.max(Number(image.height || 0), 1)}"></div>
                        <div class="list-col"><span class="list-type">${type}</span></div>
                        <div class="list-col list-url" title="${url}"><span class="list-url-text">${url}</span></div>
                        <div class="list-col list-resolution">${resolution}</div>
                        <div class="list-col list-size">${formatSize(image.size)}</div>
                        <div class="list-col list-date">${date}</div>
                        <div class="list-col list-ops">
                            <span class="list-op-group">
                                <button type="button" class="list-op-btn js-copy" data-url="${url}"><i class="fas fa-link"></i>复制URL</button>
                                <button type="button" class="list-op-btn js-open" data-id="${id}"><i class="fas fa-eye"></i>预览</button>
                                <button type="button" class="list-op-btn delete" data-id="${id}"><i class="fas fa-trash"></i>删除</button>
                            </span>
                            <button type="button" class="image-selector overflow-hidden" title="选择">
                                <span class="text-xl"><i class="fas fa-check-circle block rounded-full bg-white text-white border border-gray-500"></i></span>
                            </button>
                        </div>
                    </div>
                `;
                const $el = $(html);
                $el.data('json', image);
                return $el;
            }

            function syncGridGallery() {
                const count = $gridView.find('.images-item[data-id]').length;
                if (viewMode !== 'grid' || count === 0) {
                    $gridView.justifiedGallery('destroy');
                    $gridView.removeClass('is-layout-pending');
                    return;
                }
                $gridView.off('.adminLayout');
                $gridView.on('jg.complete.adminLayout', function () {
                    $gridView.removeClass('is-layout-pending');
                });
                $gridView.justifiedGallery(gridConfigs).removeClass('reset');
                $gridView.justifiedGallery('norewind');
            }

            function renderImages(images, append = false) {
                if (!append) {
                    $gridView.justifiedGallery('destroy');
                    if (viewMode === 'grid' && images.length > 0) {
                        $gridView.addClass('is-layout-pending');
                    } else {
                        $gridView.removeClass('is-layout-pending');
                    }
                    $gridView.addClass('reset').empty();
                    $listView.find('.list-row.item').remove();
                    selected.clear();
                }
                images.forEach(image => {
                    $gridView.append(createGridItem(image));
                    $listView.append(createListRow(image));
                });
                syncGridGallery();
                syncEmptyState();
                syncSelectedUi();
                syncLoadingUi();
                syncToolbarState();
            }

            function loadPage(page, append = false) {
                if (state.loading) return;
                state.loading = true;
                syncLoadingUi();
                syncToolbarState();
                syncPagination();
                axios.get('{{ route('admin.images') }}', {
                    params: {
                        json: 1,
                        page: page,
                        per_page: state.perPage,
                        keywords: state.keywords || '',
                    },
                }).then(response => {
                    if (!response.data.status) {
                        toastr.error(response.data.message || '加载失败');
                        return;
                    }
                    const data = response.data.data || {};
                    const images = Array.isArray(data.images) ? data.images : [];
                    const pg = data.pagination || {};
                    state.currentPage = Number(pg.current_page || page || 1);
                    state.lastPage = Number(pg.last_page || 1);
                    state.total = Number(pg.total || 0);
                    state.perPage = Number(pg.per_page || state.perPage);
                    renderImages(images, append);
                }).catch(() => {
                    toastr.error('加载失败');
                }).finally(() => {
                    state.loading = false;
                    syncLoadingUi();
                    syncToolbarState();
                    syncPagination();
                });
            }

            function collectCarouselItems() {
                const map = new Map();
                $gridView.find('.item[data-id]').each(function () {
                    const image = $(this).data('json');
                    if (!image || !image.id) return;
                    map.set(String(image.id), image);
                });
                return Array.from(map.values());
            }

            function renderCarouselThumbs() {
                const html = renderThumbButtons(carouselItems, carouselIndex, (item) => ({
                    title: displayNameOf(item),
                    src: item.thumb_url || item.url || '',
                    alt: displayNameOf(item),
                }));
                $carouselThumbs.html(html);
            }

            function renderCarouselMeta(image) {
                const permissionText = Number(image.permission) === {{ \App\Enums\ImagePermission::Public }} ? '公开' : '私有';
                const reviewStatusMap = {
                    review_pending: '待审核',
                    review_approved: '已通过',
                    review_rejected: '已驳回',
                };
                const copyUrl = String(image.url || '').trim();
                const tagText = Array.isArray(image.tags)
                    ? image.tags.map((tag) => String(tag?.name || '').trim()).filter(Boolean).join(' / ')
                    : '';
                const ocrText = String(image.ocr_text || '').trim();
                const groups = [
                    {
                        title: '基础信息',
                        rows: [
                            {key: '名称', value: displayNameOf(image)},
                            {key: '原始名称', value: image.origin_name || '-'},
                            {key: 'URL', value: copyUrl || '-', isHtml: !!copyUrl},
                            {key: '类型', value: image.mimetype || '-'},
                            {key: '分辨率', value: `${Number(image.width || 0)} × ${Number(image.height || 0)}`},
                            {key: '大小', value: formatSize(image.size)},
                        ],
                    },
                    {
                        title: '归属信息',
                        rows: [
                            {key: '用户', value: image.user ? `${image.user.name || '-'} (${image.user.email || '-'})` : '游客'},
                            {key: '相册', value: image.album ? image.album.name : '-'},
                            {key: '策略', value: image.strategy ? image.strategy.name : '-'},
                            {key: '权限', value: permissionText},
                            {key: '时间', value: image.created_at || '-'},
                            {key: 'IP', value: image.uploaded_ip || '-'},
                        ],
                    },
                    {
                        title: 'AI与审核',
                        rows: [
                            {key: 'AI检测', value: typeof image.is_unhealthy === 'boolean' ? (image.is_unhealthy ? '疑似违规' : '正常') : '-'},
                            {key: '人工审核', value: reviewStatusMap[String(image.review_status || '')] || '-'},
                            {key: '审核原因', value: image.review_reason || '-'},
                            {key: '审核时间', value: image.reviewed_at || '-'},
                            {key: '审核人', value: image.reviewed_by ? `#${image.reviewed_by}` : '-'},
                            {key: '标签', value: tagText || '-'},
                            {key: 'OCR摘要', value: ocrText ? (ocrText.length > 140 ? `${ocrText.slice(0, 140)}...` : ocrText) : '-'},
                        ],
                    },
                ];
                let html = '';
                groups.forEach((group) => {
                    const rowsHtml = group.rows.map((row) => {
                        const valueHtml = row.isHtml
                            ? `<div class="images-carousel-detail-inline"><span class="images-carousel-detail-text">${escapeHtml(row.value)}</span><button type="button" class="images-carousel-detail-copy" data-url="${escapeHtml(row.value)}" title="复制链接"><i class="fas fa-link"></i></button></div>`
                            : escapeHtml(row.value);
                        return `<div class="images-carousel-detail-row"><dt class="images-carousel-detail-k">${escapeHtml(row.key)}</dt><dd class="images-carousel-detail-v">${valueHtml}</dd></div>`;
                    }).join('');
                    html += `<section class="images-carousel-detail-group"><div class="images-carousel-detail-group-title">${escapeHtml(group.title)}</div><div class="images-carousel-detail-group-body">${rowsHtml}</div></section>`;
                });
                $carouselMeta.html(html);
            }

            function renderCarousel() {
                if (!carouselItems.length) return;
                carouselIndex = normalizeCarouselIndex(carouselIndex, carouselItems.length);
                const image = carouselItems[carouselIndex];
                const displayUrl = image.preview_url || image.url || image.thumb_url || '';
                $carouselImg.attr('src', displayUrl).attr('alt', displayNameOf(image));
                if (image.thumb_url) {
                    $carouselImg.off('error').on('error', function () {
                        $(this).attr('src', image.thumb_url);
                    });
                }
                renderCarouselMeta(image);
                renderCarouselThumbs();
                $carouselIndex.text(`${carouselIndex + 1} / ${carouselItems.length}`);
                const sizeText = Number(image.size || 0) > 0 ? formatSize(image.size) : '-';
                const dimText = `${Number(image.width || 0)} × ${Number(image.height || 0)}`;
                $carouselTop.text(`${displayNameOf(image)} · ${dimText} · ${sizeText}`);
            }

            function openCarouselById(id) {
                const list = collectCarouselItems();
                if (!list.length) return;
                carouselItems = list;
                const target = String(id || '');
                const idx = list.findIndex(item => String(item.id) === target);
                carouselIndex = normalizeCarouselIndex(idx >= 0 ? idx : 0, carouselItems.length);
                renderCarousel();
                setCarouselScrollLocked($carousel.get(0), true);
                $carousel.addClass('show');
            }

            function closeCarousel() {
                $carousel.removeClass('show');
                setCarouselScrollLocked($carousel.get(0), false);
            }

            function carouselPrev() {
                if (!carouselItems.length) return;
                carouselIndex = normalizeCarouselIndex(carouselIndex - 1, carouselItems.length);
                renderCarousel();
            }

            function carouselNext() {
                if (!carouselItems.length) return;
                carouselIndex = normalizeCarouselIndex(carouselIndex + 1, carouselItems.length);
                renderCarousel();
            }

            function delOne(id) {
                Swal.fire({title: '确认删除该图片吗?', text: '记录与物理文件将会一起删除。', icon: 'warning', showCancelButton: true, confirmButtonText: '确认删除'})
                    .then((result) => {
                        if (!result.isConfirmed) return;
                        axios.delete(`/admin/images/${id}`).then(response => {
                            if (!response.data.status) return toastr.error(response.data.message);
                            modal.close('content-modal');
                            toastr.success(response.data.message);
                            selected.delete(String(id));
                            $(`.item[data-id="${id}"]`).remove();
                            state.total = Math.max(0, state.total - 1);
                            carouselItems = carouselItems.filter(item => String(item.id) !== String(id));
                            if (carouselItems.length === 0) {
                                closeCarousel();
                            } else if ($carousel.hasClass('show')) {
                                carouselIndex = Math.min(carouselIndex, carouselItems.length - 1);
                                renderCarousel();
                            }
                            syncEmptyState();
                            syncSelectedUi();
                            syncPagination();
                        });
                    });
            }

            function patchImageRecord(id, patch) {
                const targetId = String(id || '');
                if (!targetId) return;

                $('.item[data-id]').each(function () {
                    if (String($(this).data('id') || '') !== targetId) return;
                    const current = $(this).data('json') || {};
                    const next = {...current, ...patch};
                    $(this).data('json', next);
                    const nextName = displayNameOf(next);
                    $(this).find('.item-name').text(nextName).attr('title', nextName);
                    $(this).find('img').attr('alt', nextName);
                });

                carouselItems = carouselItems.map((item) => String(item.id) === targetId ? {...item, ...patch} : item);
                if ($carousel.hasClass('show')) {
                    renderCarousel();
                }
            }

            function renameOne(id, currentName) {
                const normalizedId = String(id || '');
                const initialValue = String(currentName || '').trim();
                if (!normalizedId) return;

                Swal.fire({
                    title: '重命名图片',
                    input: 'text',
                    inputValue: initialValue,
                    inputLabel: '显示名称',
                    inputPlaceholder: '请输入新的图片名称',
                    showCancelButton: true,
                    confirmButtonText: '保存',
                    cancelButtonText: '取消',
                    inputValidator: (value) => {
                        return String(value || '').trim() === '' ? '名称不能为空' : null;
                    },
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    axios.put(`/admin/images/${normalizedId}`, {
                        alias_name: String(result.value || '').trim(),
                    }).then((response) => {
                        if (!response.data.status) {
                            return toastr.error(response.data.message);
                        }
                        const payload = response.data.data?.image || {};
                        patchImageRecord(normalizedId, {
                            alias_name: payload.alias_name || String(result.value || '').trim(),
                            filename: payload.filename || String(result.value || '').trim(),
                        });
                        toastr.success(response.data.message || '重命名成功');
                    }).catch((error) => {
                        toastr.error(error?.response?.data?.message || error?.message || '重命名失败');
                    });
                });
            }

            function delBatch(ids) {
                if (!ids.length) return;
                Swal.fire({title: `确认删除选中的 ${ids.length} 项吗?`, text: '记录与物理文件将会一起删除。', icon: 'warning', showCancelButton: true, confirmButtonText: '确认删除'})
                    .then((result) => {
                        if (!result.isConfirmed) return;
                        axios.delete('{{ route('admin.images.batch.delete') }}', {data: {ids: ids}}).then(response => {
                            if (!response.data.status) return toastr.error(response.data.message);
                            toastr.success(response.data.message);
                            ids.forEach(id => {
                                selected.delete(String(id));
                                $(`.item[data-id="${id}"]`).remove();
                            });
                            state.total = Math.max(0, state.total - ids.length);
                            syncEmptyState();
                            syncSelectedUi();
                            syncPagination();
                        });
                    });
            }

            $('#grammar').on('click', function () {
                $('#modal-content').html($('#search-grammar-tpl').html());
                modal.open('content-modal');
            });

            $(document).on('click', '[data-admin-action]', function (e) {
                e.preventDefault();
                const action = String($(this).data('admin-action') || '');
                if (action === 'submit') {
                    if (toolbarForm) toolbarForm.requestSubmit ? toolbarForm.requestSubmit() : toolbarForm.submit();
                    return;
                }
                if (action === 'grammar') {
                    $('#grammar').trigger('click');
                    return;
                }
                if (action === 'select-all') {
                    $('#select-all').trigger('click');
                    return;
                }
                if (action === 'batch-delete') {
                    $('#batch-delete').trigger('click');
                }
            });

            if (userTreeSearchInput) {
                userTreeSearchInput.addEventListener('input', syncUserTreeFilter);
                syncUserTreeFilter();
            }

            $('#view-grid').on('click', function () {
                setViewMode('grid');
            });

            $('#view-list').on('click', function () {
                setViewMode('list');
            });

            $('#select-all').on('click', function () {
                const ids = allItemIds();
                if (!ids.length) return;
                const isAllSelected = ids.every(id => selected.has(id));
                if (isAllSelected) {
                    selected.clear();
                } else {
                    ids.forEach(id => selected.add(id));
                }
                syncSelectedUi();
            });

            $(document).on('pointerdown', '.image-selector', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const id = String($(this).closest('.item').data('id') || '');
                if (!id) return false;
                if (selected.has(id)) {
                    selected.delete(id);
                } else {
                    selected.add(id);
                }
                syncSelectedUi();
                return false;
            });

            $(document).on('click', '.image-selector', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            });

            $(document).on('click', '.item .delete', function (e) {
                e.stopPropagation();
                delOne($(this).data('id'));
            });

            $(document).on('click', '.item .js-open', function (e) {
                e.stopPropagation();
                const id = String($(this).data('id'));
                openCarouselById(id);
            });

            $(document).on('click', '.item .js-copy', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const url = String($(this).data('url') || '');
                if (!url) return;
                copyText(url).then(() => {
                    toastr.success('链接已复制');
                }).catch(() => {
                    toastr.warning('复制失败');
                });
            });

            $(document).on('click', '.item img', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const image = $(this).closest('.item').data('json');
                if (image && image.id) openCarouselById(image.id);
            });

            $(document).on('contextmenu', '.item', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const image = $(this).data('json') || {};
                const url = String(image.url || '').trim();
                if (!url) {
                    toastr.warning('未找到图片链接', null, {timeOut: 3000, extendedTimeOut: 0});
                    return false;
                }
                copyText(url).then(() => {
                    toastr.success('已复制图片 URL', null, {timeOut: 3000, extendedTimeOut: 0});
                }).catch(() => {
                    toastr.warning('复制失败', null, {timeOut: 3000, extendedTimeOut: 0});
                });
                return false;
            });

            $('#batch-delete').on('click', function () {
                delBatch(Array.from(selected));
            });

            $('#admin-carousel-close').on('click', function () {
                closeCarousel();
            });
            $('#admin-carousel-prev').on('click', function () {
                carouselPrev();
            });
            $('#admin-carousel-next').on('click', function () {
                carouselNext();
            });
            $('#admin-carousel-thumbs').on('click', '.images-carousel-thumb', function () {
                const idx = Number($(this).data('index'));
                if (Number.isNaN(idx)) return;
                carouselIndex = normalizeCarouselIndex(idx, carouselItems.length);
                renderCarousel();
            });
            $('#admin-carousel-delete').on('click', function () {
                if (!carouselItems.length) return;
                const item = carouselItems[carouselIndex] || {};
                if (item.id) delOne(item.id);
            });
            $('#admin-carousel-rename').on('click', function () {
                if (!carouselItems.length) return;
                const item = carouselItems[carouselIndex] || {};
                if (item.id) {
                    renameOne(item.id, displayNameOf(item));
                }
            });
            $carouselMeta.on('click', '.images-carousel-detail-copy', function () {
                const url = String($(this).data('url') || '').trim();
                if (!url) return;
                copyText(url).then(() => toastr.success('链接已复制')).catch(() => toastr.warning('复制失败'));
            });
            $('#admin-carousel').on('click', function (e) {
                if (e.target.id === 'admin-carousel') {
                    closeCarousel();
                }
            });
            $(document).on('keydown', function (e) {
                if (!$carousel.hasClass('show')) return;
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeCarousel();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    carouselPrev();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    carouselNext();
                }
            });

            $pagePrev.on('click', function () {
                if (state.currentPage <= 1 || state.loading) return;
                loadPage(state.currentPage - 1, false);
                $stage.scrollTop(0);
            });

            $pageNext.on('click', function () {
                if (state.currentPage >= state.lastPage || state.loading) return;
                loadPage(state.currentPage + 1, false);
                $stage.scrollTop(0);
            });

            $pageGo.on('click', function () {
                const target = Number($pageJump.val() || 0);
                if (!Number.isInteger(target) || target < 1) {
                    toastr.warning('请输入正确页码');
                    return;
                }
                const page = Math.min(target, Math.max(1, state.lastPage));
                loadPage(page, false);
                $stage.scrollTop(0);
            });

            $pageJump.on('keydown keypress', function (e) {
                if (e.key === 'Enter' || e.which === 13) {
                    e.preventDefault();
                    $pageGo.trigger('click');
                }
            });

            $pageSize.on('change', function () {
                state.perPage = Number($(this).val() || 50);
                if (perPageInput) perPageInput.value = String(state.perPage);
                loadPage(1, false);
                $stage.scrollTop(0);
            });

            $stage.on('scroll', function () {
                if (state.loading || state.currentPage >= state.lastPage) return;
                const remain = this.scrollHeight - this.scrollTop - this.clientHeight;
                if (remain <= 60) {
                    loadPage(state.currentPage + 1, true);
                }
            });

            setViewMode(viewMode);
            syncPagination();
            syncSelectedUi();
            loadPage(state.currentPage || 1, false);
        </script>
    @endpush
</x-app-layout>
