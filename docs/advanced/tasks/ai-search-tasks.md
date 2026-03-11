# ai-search 任务拆解

- 页面路由: `/advanced/ai-search`
- API 基线: `GET /advanced-api/images/ai-search`
- 主负责人: `frontend`
- 优先级: P2

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| ai-search-T01 | 检索体验目标与评分口径冻结 | project-lead | 检索口径文档 |
| ai-search-T02 | 检索页输入、分页、命中高亮与空态设计落地 | frontend | 页面实现 |
| ai-search-T03 | 混合检索接口（名称/标签/OCR）与排序实现 | backend | 查询实现与接口文档 |
| ai-search-T04 | 索引、慢查询与容量监控配置 | ops | 观测项与阈值 |
| ai-search-T05 | 关键词/语义降级/空结果等场景回归 | qa | 回归报告 |
| ai-search-T06 | 越权检索与结果泄露评审 | security | 安全评审记录 |
| ai-search-T07 | 独立评审（准确性/性能权衡） | reviewer | 风险清单 |

## 完成定义（DoD）

- 支持 `q` 检索并返回分页结果与得分排序。
- 无结果与异常态反馈明确，页面可重试。
- 结果严格受空间与权限限制。
- 查询性能达到约定阈值并完成安全评审。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/images/ai-search --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ImageController.php
rg -n "aiSearch|ocr_text|tags" app/Http/Controllers/Api/V1/ImageController.php app/Models/Image.php
bash scripts/acceptance/api-smoke.sh
```

