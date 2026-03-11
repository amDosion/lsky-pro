.images-carousel {
    --media-carousel-overlay: #ffffff;
    --media-carousel-stage-width: min(98vw, 1520px);
    --media-carousel-stage-height: min(95vh, 980px);
    --media-carousel-side-width: clamp(280px, 22vw, 320px);
    background: var(--media-carousel-overlay);
    display: none;
}

.images-carousel.is-viewport {
    position: fixed;
    inset: 0;
    z-index: 90;
}

.images-carousel.is-panel {
    position: absolute;
    inset: 0;
    z-index: 120;
    border-radius: inherit;
    overflow: hidden;
    min-width: 0;
    min-height: 0;
}

.images-carousel.show {
    display: grid;
}

.images-carousel.is-viewport.show {
    place-items: center;
}

.images-carousel-stage {
    position: relative;
    width: var(--media-carousel-stage-width);
    height: var(--media-carousel-stage-height);
    display: grid;
    grid-template-rows: minmax(0, 1fr) auto;
    gap: 8px;
    padding: 6px;
    min-width: 0;
    min-height: 0;
    max-width: 100%;
    max-height: 100%;
    overflow: hidden;
}

.images-carousel.is-panel .images-carousel-stage {
    width: 100%;
    height: 100%;
    grid-template-rows: minmax(0, 1fr) auto;
    padding: 8px;
    overflow: hidden;
}

.images-carousel-top {
    min-width: 0;
    color: #334155;
    font-size: 12px;
    line-height: 1.45;
    white-space: normal;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.images-carousel-shell {
    min-height: 0;
    min-width: 0;
    height: 100%;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, var(--media-carousel-side-width));
    gap: 10px;
    overflow: hidden;
    align-items: stretch;
}

.images-carousel-main {
    min-height: 0;
    min-width: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
}

.images-carousel-viewport {
    min-height: 0;
    flex: 1 1 auto;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    display: grid;
    grid-template-columns: 52px minmax(0, 1fr) 52px;
    align-items: center;
    gap: 12px;
    padding: 12px;
    overflow: hidden;
}

.images-carousel-nav {
    min-height: 0;
    min-width: 0;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.images-carousel-image-frame {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    min-height: 0;
    padding: 12px;
    border: 1px solid #dbe2ea;
    border-radius: 12px;
    background:
        linear-gradient(45deg, #f8fafc 25%, transparent 25%),
        linear-gradient(-45deg, #f8fafc 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #f8fafc 75%),
        linear-gradient(-45deg, transparent 75%, #f8fafc 75%),
        #eef2f7;
    background-size: 24px 24px;
    background-position: 0 0, 0 12px, 12px -12px, -12px 0;
    overflow: hidden;
}

.images-carousel-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    background: #fff;
    opacity: 1;
    transform: scale(1);
    transition: opacity .2s ease, transform .22s ease;
}

.images-carousel-img.is-loading {
    opacity: .25;
    transform: scale(.985);
}

.images-carousel-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 3px solid #dbe2ea;
    border-top-color: #3b82f6;
    animation: carousel-spin .8s linear infinite;
    display: none;
}

.images-carousel-loading.show {
    display: block;
}

@keyframes carousel-spin {
    to {
        transform: rotate(360deg);
    }
}

.images-carousel-btn {
    position: relative;
    width: 42px;
    height: 42px;
    border-radius: 999px;
    border: 1px solid #dbe2ea;
    background: #ffffff;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .1);
    transition: all .16s ease;
}

.images-carousel-btn:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.images-carousel-main-actions {
    flex: 0 0 auto;
    min-height: 0;
    border-top: 1px solid #e2e8f0;
    background: #fff;
    padding: 8px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    overflow-y: hidden;
}

.images-carousel-main-footer {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    min-width: 0;
    padding: 0 16px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}

.images-carousel-caption {
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1 1 auto;
}

.images-carousel-side {
    min-height: 0;
    min-width: 0;
    height: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr);
    overflow: hidden;
}

.images-carousel-side-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.images-carousel-side-meta {
    min-width: 0;
    display: grid;
    gap: 6px;
    flex: 1 1 auto;
}

.images-carousel-side-badges {
    min-width: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.images-carousel-index,
.images-carousel-status {
    color: #475569;
    font-size: 12px;
}

.images-carousel-side-close {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid #dbe2ea;
    background: #ffffff;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex: 0 0 auto;
}

.images-carousel-side-close:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.images-carousel-detail {
    min-height: 0;
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
    padding: 8px 14px 12px;
}

.images-carousel-detail::-webkit-scrollbar {
    width: 10px;
}

.images-carousel-detail::-webkit-scrollbar-track {
    background: #f8fafc;
}

.images-carousel-detail::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
    border: 2px solid #f8fafc;
}

.images-carousel-detail-row {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px dashed #e2e8f0;
}

.images-carousel-detail-k {
    color: #64748b;
    font-size: 12px;
}

.images-carousel-detail-v {
    min-width: 0;
    color: #0f172a;
    font-size: 12px;
    line-height: 1.55;
    word-break: break-word;
    margin: 0;
}

.images-carousel-detail-inline {
    min-width: 0;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.images-carousel-detail-text {
    min-width: 0;
    flex: 1 1 auto;
    word-break: break-word;
}

.images-carousel-detail-copy {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    border: 1px solid #dbe2ea;
    background: #ffffff;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    flex: 0 0 auto;
}

.images-carousel-detail-copy:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.images-carousel-detail-group + .images-carousel-detail-group {
    margin-top: 16px;
}

.images-carousel-detail-group-title {
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.images-carousel-detail-group-body {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
}

.images-carousel-detail-group-body .images-carousel-detail-row {
    padding: 8px 12px;
}

.images-carousel-detail-group-body .images-carousel-detail-row:last-child {
    border-bottom: 0;
}

.images-carousel-detail-state {
    display: grid;
    gap: 8px;
    padding: 12px;
    margin-bottom: 14px;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    background: #eff6ff;
}

.images-carousel-detail-state.is-error {
    border-color: #fecaca;
    background: #fff1f2;
}

.images-carousel-detail-state-title {
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
}

.images-carousel-detail-state-meta {
    color: #475569;
    font-size: 12px;
    line-height: 1.5;
}

.images-carousel-detail-state-btn {
    width: fit-content;
    height: 30px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid #bfdbfe;
    background: #ffffff;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 600;
}

.images-carousel-detail-state.is-error .images-carousel-detail-state-btn {
    border-color: #fecaca;
    color: #b91c1c;
}

.images-carousel-bottom-stack {
    min-height: 0;
    min-width: 0;
    display: grid;
    gap: 8px;
    overflow: hidden;
}

.images-carousel-action-group {
    flex: 0 0 auto;
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 8px;
}

.images-carousel-action {
    height: 32px;
    border-radius: 8px;
    width: auto;
    min-width: 0;
    padding: 0 10px;
    border: 1px solid #dbe2ea;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.images-carousel-action.is-primary {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.images-carousel-action.is-danger {
    color: #b91c1c;
    background: #fff5f5;
    border-color: #fecaca;
}

.images-carousel-action.hidden {
    display: none;
}

.images-carousel-thumbs {
    height: 92px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    padding: 6px;
    display: flex;
    gap: 6px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x proximity;
}

.images-carousel-thumb {
    width: 108px;
    min-width: 108px;
    height: 66px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
    opacity: .72;
    scroll-snap-align: center;
    transition: all .16s ease;
}

.images-carousel-thumb:hover {
    opacity: .92;
    transform: translateY(-1px);
}

.images-carousel-thumb.active {
    border-color: #3b82f6;
    box-shadow: inset 0 0 0 1px #3b82f6;
    opacity: 1;
}

.images-carousel-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.images-carousel-crop-layer {
    position: absolute;
    display: none;
    inset: 0;
    pointer-events: auto;
    cursor: crosshair;
}

.images-carousel-crop-layer.active {
    display: block;
}

.images-carousel-crop-box {
    position: absolute;
    border: 2px solid #2563eb;
    background: rgba(147, 197, 253, .14);
    cursor: move;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, .52), 0 0 0 1px rgba(255, 255, 255, .62);
}

.images-carousel-crop-box::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(255,255,255,.72) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255,255,255,.72) 1px, transparent 1px);
    background-size: 33.333% 100%, 100% 33.333%;
    pointer-events: none;
}

.images-carousel-crop-box .crop-handle {
    position: absolute;
    width: 14px;
    height: 14px;
    border-radius: 999px;
    background: #fff;
    border: 2px solid #2563eb;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .18);
}

.images-carousel-crop-box .crop-handle.nw {
    left: -8px;
    top: -8px;
    cursor: nwse-resize;
}

.images-carousel-crop-box .crop-handle.n {
    left: calc(50% - 7px);
    top: -8px;
    cursor: ns-resize;
}

.images-carousel-crop-box .crop-handle.ne {
    right: -8px;
    top: -8px;
    cursor: nesw-resize;
}

.images-carousel-crop-box .crop-handle.e {
    right: -8px;
    top: calc(50% - 7px);
    cursor: ew-resize;
}

.images-carousel-crop-box .crop-handle.s {
    left: calc(50% - 7px);
    bottom: -8px;
    cursor: ns-resize;
}

.images-carousel-crop-box .crop-handle.w {
    left: -8px;
    top: calc(50% - 7px);
    cursor: ew-resize;
}

.images-carousel-crop-box .crop-handle.sw {
    left: -8px;
    bottom: -8px;
    cursor: nesw-resize;
}

.images-carousel-crop-box .crop-handle.se {
    right: -8px;
    bottom: -8px;
    cursor: nwse-resize;
}

@media (max-width: 1024px) {
    .images-carousel-stage {
        width: min(100vw, 100%);
        height: 100vh;
        grid-template-rows: minmax(0, 1fr) auto;
        padding: 8px;
    }

    .images-carousel-shell {
        grid-template-columns: 1fr;
        height: 100%;
    }

    .images-carousel-main { min-height: 280px; }

    .images-carousel-viewport {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        gap: 8px;
        padding: 8px;
    }

    .images-carousel-side {
        grid-template-rows: auto minmax(180px, 32vh);
    }
}
