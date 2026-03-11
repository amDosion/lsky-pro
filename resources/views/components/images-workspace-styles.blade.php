.images-v2,
.admin-images-v4 {
    width: 100%;
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 10px;
    height: 100%;
    min-height: 0;
}

.images-v2 .images-aside,
.admin-images-v4 .images-aside {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.images-v2 .images-main,
.admin-images-v4 .images-main {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    overflow: hidden;
}

.images-v2 .images-aside-head,
.admin-images-v4 .images-aside-head {
    min-height: 46px;
    padding: 0 10px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.images-v2 .images-aside-title,
.admin-images-v4 .images-aside-title {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}

.images-v2 .images-tree-list,
.admin-images-v4 .images-tree-list {
    padding: 8px;
    overflow-y: auto;
    min-height: 0;
    display: grid;
    gap: 6px;
}

.images-v2 .images-toolbar,
.admin-images-v4 .images-toolbar {
    z-index: 4;
    min-height: 46px;
    padding: 0 10px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}

.images-v2 .images-stage,
.admin-images-v4 .images-stage {
    flex: 1 1 auto;
    min-height: 0;
    border: 0;
    background: #fff;
    overflow-y: auto;
}

.images-v2 .images-footer,
.admin-images-v4 .images-footer {
    border: 0;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    min-height: 38px;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.images-v2 .images-footer-label,
.admin-images-v4 .images-footer-label {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
}

.images-v2 .images-pagination,
.admin-images-v4 .images-pagination {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
}

.images-v2 .pager-btn,
.images-v2 .pager-select,
.images-v2 .pager-jump,
.admin-images-v4 .pager-btn,
.admin-images-v4 .pager-select,
.admin-images-v4 .pager-jump {
    height: 28px;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    background: #fff;
    color: #334155;
    font-size: 12px;
    padding: 0 8px;
    line-height: 1;
}

.images-v2 .pager-btn,
.admin-images-v4 .pager-btn {
    min-width: 66px;
    background: #f8fafc;
    padding: 0 10px;
}

.images-v2 .pager-btn:disabled,
.admin-images-v4 .pager-btn:disabled {
    color: #94a3b8;
    border-color: #e2e8f0;
    background: #f8fafc;
    cursor: not-allowed;
}

.images-v2 .pager-info,
.admin-images-v4 .pager-info {
    font-size: 12px;
    color: #475569;
    min-width: 168px;
    text-align: center;
}

.images-v2 .pager-select,
.admin-images-v4 .pager-select {
    min-width: 82px;
}

.images-v2 .pager-jump,
.admin-images-v4 .pager-jump {
    width: 72px;
}

.images-v2 .toolbar-search,
.admin-images-v4 .toolbar-search {
    flex: 1 1 300px;
    min-width: 220px;
    height: 30px;
    padding: 0 10px;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    font-size: 12px;
}

.images-v2 .toolbar-action-groups,
.admin-images-v4 .toolbar-action-groups {
    display: flex;
    align-items: center;
    gap: 8px;
}

.images-v2 .toolbar-action-group,
.admin-images-v4 .toolbar-action-group {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
}

.images-v2 .toolbar-action-btn,
.admin-images-v4 .toolbar-action-btn {
    height: 30px;
    border: 0;
    border-right: 1px solid #dbe2ea;
    border-radius: 0;
    background: transparent;
    color: #334155;
    font-size: 12px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 10px;
    white-space: nowrap;
    cursor: pointer;
}

.images-v2 .toolbar-action-group .toolbar-action-btn:last-child,
.admin-images-v4 .toolbar-action-group .toolbar-action-btn:last-child {
    border-right: 0;
}

.images-v2 .toolbar-action-btn:hover,
.admin-images-v4 .toolbar-action-btn:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.images-v2 .toolbar-action-btn.is-disabled,
.images-v2 .toolbar-action-btn:disabled,
.admin-images-v4 .toolbar-action-btn.is-disabled,
.admin-images-v4 .toolbar-action-btn:disabled {
    background: #f8fafc;
    border-right-color: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
    pointer-events: none;
}

.images-v2 .toolbar-meta-group,
.admin-images-v4 .toolbar-meta-group {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    overflow: visible;
    background: #fff;
}

.images-v2 .toolbar-meta-btn,
.admin-images-v4 .toolbar-meta-btn {
    height: 30px;
    border: 0;
    border-right: 1px solid #dbe2ea;
    background: transparent;
    color: #334155;
    font-size: 12px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 10px;
    white-space: nowrap;
}

.images-v2 .toolbar-meta-btn:hover,
.admin-images-v4 .toolbar-meta-btn:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

.images-v2 .toolbar-meta-group > :last-child .toolbar-meta-btn,
.images-v2 .toolbar-meta-group > .toolbar-meta-btn:last-child,
.admin-images-v4 .toolbar-meta-group > :last-child .toolbar-meta-btn,
.admin-images-v4 .toolbar-meta-group > .toolbar-meta-btn:last-child {
    border-right: 0;
}

.images-v2 .view-switch-btn,
.admin-images-v4 .view-switch-btn {
    min-width: 30px;
    padding: 0 8px;
}

.images-v2 .view-switch-btn.active,
.admin-images-v4 .view-switch-btn.active {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

@media (max-width: 1024px) {
    .images-v2,
    .admin-images-v4 {
        grid-template-columns: 1fr;
    }

    .images-v2 .images-aside,
    .admin-images-v4 .images-aside {
        min-height: 220px;
    }
}
