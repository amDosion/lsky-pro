@props([
    'idPrefix',
    'rootClass' => '',
    'hostMode' => 'viewport',
    'showTop' => true,
    'showCopy' => true,
    'showCaption' => true,
    'showIndex' => true,
    'showStatus' => true,
    'showDetail' => true,
    'showThumbs' => true,
    'showLoading' => true,
    'showCropLayer' => false,
])

@php
    $id = static fn (string $suffix): string => $idPrefix.'-'.$suffix;
    $modeClass = $hostMode === 'panel' ? 'is-panel' : 'is-viewport';
    $rootClasses = trim('images-carousel '.$modeClass.' '.$rootClass);
@endphp

<div id="{{ $idPrefix }}" class="{{ $rootClasses }}">
    <div class="images-carousel-stage" id="{{ $id('stage') }}">
        <div class="images-carousel-shell">
            <div class="images-carousel-main" id="{{ $id('main') }}">
                <div class="images-carousel-viewport">
                    <div class="images-carousel-nav prev">
                        <button type="button" class="images-carousel-btn prev" id="{{ $id('prev') }}"><i class="fas fa-chevron-left"></i></button>
                    </div>
                    <div id="{{ $id('image-frame') }}" class="images-carousel-image-frame">
                        @if ($showLoading)
                            <div id="{{ $id('loading') }}" class="images-carousel-loading"></div>
                        @endif
                        <img id="{{ $id('img') }}" class="images-carousel-img" src="" alt="">
                        @if ($showCropLayer)
                            <div id="{{ $id('crop-layer') }}" class="images-carousel-crop-layer">
                                <div id="{{ $id('crop-box') }}" class="images-carousel-crop-box">
                                    <span class="crop-handle nw" data-handle="nw"></span>
                                    <span class="crop-handle n" data-handle="n"></span>
                                    <span class="crop-handle ne" data-handle="ne"></span>
                                    <span class="crop-handle e" data-handle="e"></span>
                                    <span class="crop-handle s" data-handle="s"></span>
                                    <span class="crop-handle w" data-handle="w"></span>
                                    <span class="crop-handle sw" data-handle="sw"></span>
                                    <span class="crop-handle se" data-handle="se"></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="images-carousel-nav next">
                        <button type="button" class="images-carousel-btn next" id="{{ $id('next') }}"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                @isset($actions)
                    <div class="images-carousel-main-actions">
                        {{ $actions }}
                    </div>
                @endisset
                @if ($showCaption)
                    <div class="images-carousel-main-footer">
                        <div id="{{ $id('caption') }}" class="images-carousel-caption"></div>
                    </div>
                @endif
            </div>
            <aside class="images-carousel-side">
                <div class="images-carousel-side-head">
                    <div class="images-carousel-side-meta">
                        @if ($showIndex || $showStatus)
                            <div class="images-carousel-side-badges">
                                @if ($showIndex)
                                    <div id="{{ $id('index') }}" class="images-carousel-index"></div>
                                @endif
                                @if ($showStatus)
                                    <div id="{{ $id('status') }}" class="images-carousel-status"></div>
                                @endif
                            </div>
                        @endif
                        @if ($showTop)
                            <div id="{{ $id('top') }}" class="images-carousel-top"></div>
                        @else
                            <div class="images-carousel-top hidden"></div>
                        @endif
                    </div>
                    <button type="button" class="images-carousel-side-close" id="{{ $id('close') }}"><i class="fas fa-times"></i></button>
                </div>
                @if ($showDetail)
                    <dl id="{{ $id('detail') }}" class="images-carousel-detail"></dl>
                @endif
            </aside>
        </div>
        @if ($showThumbs)
            <div class="images-carousel-bottom-stack">
                <div id="{{ $id('thumbs') }}" class="images-carousel-thumbs"></div>
            </div>
        @endif
    </div>
</div>
