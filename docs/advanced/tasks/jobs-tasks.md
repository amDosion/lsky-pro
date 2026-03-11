# jobs 任务拆解

- 页面路由: `/advanced/jobs`
- API 基线: `/advanced-api/process-jobs/*`
- 主负责人: `ops`
- 优先级: P1

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| jobs-T01 | 作业状态机与运维 SLA 冻结 | project-lead | 状态机与SLA文档 |
| jobs-T02 | 作业列表、详情、重试、取消交互实现 | frontend | 页面实现 |
| jobs-T03 | 作业查询/重试/取消接口与状态约束实现 | backend | `ProcessTemplateController` 作业接口 |
| jobs-T04 | 队列 worker、并发、重试与监控告警配置 | ops | 队列运维手册 |
| jobs-T05 | 状态跃迁、幂等与批量故障回归测试 | qa | 回归报告 |
| jobs-T06 | 作业 payload 脱敏与跨用户访问评审 | security | 安全评审结论 |
| jobs-T07 | 独立评审（可恢复性与风险） | reviewer | 评审报告 |

## 完成定义（DoD）

- 页面可完成作业查询、详情查看、重试、取消。
- 状态流转符合定义，非法状态操作被拒绝。
- 队列积压与失败率可观测并具备告警。
- 安全与 reviewer 门禁通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/process-jobs --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ProcessTemplateController.php
docker exec lsky-pro php artisan list --no-ansi | grep queue:work
bash scripts/ci/smoke.sh
```
