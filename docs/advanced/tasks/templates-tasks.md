# process-template 后台能力任务拆解（原 templates）

- 主菜单入口: 无独立主菜单，当前主菜单页已调整为 `/advanced/ai-config`
- 能力暴露: `/advanced-api/process-templates/*`
- 消费入口: `image-process`、`jobs` 与其他批处理调用点
- 主负责人: `backend`
- 优先级: P1

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| templates-T01 | 模板生命周期与作用域策略冻结 | project-lead | 模板策略文档 |
| templates-T02 | 模板列表、创建、运行、派发调用点实现 | frontend | 调用点实现 |
| templates-T03 | 模板 CRUD、同步执行、异步派发接口实现 | backend | `ProcessTemplateController` |
| templates-T04 | 模板运行队列、容量与失败重试基线 | ops | 运维配置清单 |
| templates-T05 | 模板定义校验与 1~500 keys 边界回归 | qa | 回归报告 |
| templates-T06 | 跨空间模板误用与越权执行评审 | security | 安全评审 |
| templates-T07 | 独立评审（失败隔离与一致性） | reviewer | 风险清单 |

## 完成定义（DoD）

- 模板支持列表、创建、同步执行、异步派发全链路。
- 执行失败可按 key 级别反馈，不影响成功项。
- 模板与图片访问都遵循空间与权限隔离。
- 调用点文档明确说明其为后台能力而非独立主菜单页。
- 安全与 reviewer 双门禁通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/process-templates --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ProcessTemplateController.php
rg -n "process-templates|dispatch|run|keys" routes/web.php app/Http/Controllers/Api/V1/ProcessTemplateController.php
bash scripts/acceptance/api-smoke.sh
```
