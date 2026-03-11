# team-permissions 任务拆解

- 页面路由: `/advanced/team-permissions`
- API 基线: `/advanced-api/spaces/*`
- 主负责人: `backend`
- 优先级: P0

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| team-permissions-T01 | 团队空间权限模型与升级规则冻结 | project-lead | 权限矩阵 |
| team-permissions-T02 | 空间切换、成员列表、角色变更交互实现 | frontend | 页面实现 |
| team-permissions-T03 | 空间/成员/角色接口与权限校验实现 | backend | `SpaceController` + 中间件 |
| team-permissions-T04 | 会话刷新、生效时延、审计与告警策略 | ops | 运维与审计策略 |
| team-permissions-T05 | 越权、并发修改、owner 保护回归 | qa | 测试报告 |
| team-permissions-T06 | 高危授权与跨空间越权专项评审 | security | 安全评审结论 |
| team-permissions-T07 | 独立评审（权限最小化与可追踪性） | reviewer | 评审报告 |

## 完成定义（DoD）

- 支持空间列表、空间切换、成员查看、角色更新。
- owner 不可通过接口降级或非法提升。
- 权限变更具备审计记录，且在约定时间内生效。
- 安全与 reviewer 双门禁通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/spaces --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/SpaceController.php
docker exec lsky-pro php -l app/Http/Middleware/ResolveTeamSpaceContext.php
rg -n "spaces/switch|members|updateMemberRole" routes/web.php app/Http/Controllers/Api/V1/SpaceController.php
```

