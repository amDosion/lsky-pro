# drivers 任务拆解

- 页面路由: `/advanced/drivers`
- API 基线: `GET /advanced-api/processing/drivers/status`
- 主负责人: `backend`
- 优先级: P0

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| drivers-T01 | 驱动能力矩阵与可用性定义冻结 | project-lead | 驱动能力矩阵 |
| drivers-T02 | 驱动状态页展示与刷新交互 | frontend | 页面实现 |
| drivers-T03 | 驱动状态接口与可用性探针实现 | backend | `ProcessingController@status` |
| drivers-T04 | 环境依赖检查脚本与运维告警 | ops | 驱动巡检流程 |
| drivers-T05 | 不同驱动健康状态与异常分支测试 | qa | 测试记录 |
| drivers-T06 | 配置泄露与敏感信息暴露评审 | security | 安全结论 |
| drivers-T07 | 独立评审（可靠性与可运维性） | reviewer | 评审结论 |

## 完成定义（DoD）

- 页面可稳定展示当前驱动与候选驱动可用性。
- 驱动不可用时返回明确 `reason`，且不暴露敏感细节。
- 运维有可执行巡检与告警阈值。
- 安全与 reviewer 门禁通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/processing/drivers/status --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ProcessingController.php
rg -n "processing/drivers/status|configured|available" routes/web.php app/Http/Controllers/Api/V1/ProcessingController.php
bash scripts/checks/config-baseline.sh
```

