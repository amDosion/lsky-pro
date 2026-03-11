# reviews 任务拆解

- 页面路由: `/advanced/reviews`
- API 基线: `/advanced-api/admin/reviews`
- 主负责人: `backend`
- 优先级: P1

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| reviews-T01 | 审核流程与状态流转规则冻结 | project-lead | 审核流程图 |
| reviews-T02 | 审核列表、通过/驳回交互与错误反馈 | frontend | 页面实现 |
| reviews-T03 | 审核查询与审核动作接口实现 | backend | `Admin\ReviewController` |
| reviews-T04 | 审核 SLA 与告警规则配置 | ops | 告警规则清单 |
| reviews-T05 | 审核并发冲突、重提与分页回归 | qa | 回归报告 |
| reviews-T06 | 管理员权限绕过与审计完整性评审 | security | 安全评审记录 |
| reviews-T07 | 独立评审（合规性与可追溯） | reviewer | 评审结论 |

## 完成定义（DoD）

- 审核列表查询与通过/驳回动作可用。
- 非管理员不可执行审核动作。
- 审核动作具备审计记录与可追踪原因。
- 安全门禁、reviewer 门禁均通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/admin/reviews --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/Admin/ReviewController.php
rg -n "review_status|approve|reject|ensureAdmin" app/Http/Controllers/Api/V1/Admin/ReviewController.php
bash scripts/acceptance/api-smoke.sh
```

