# 高级功能页面设计文档索引

## 范围
- 文档目录：`docs/advanced/design`
- 页面数量：8（每页 1 份设计文档）
- 路由基线：`routes/web.php` 下 `advanced-api` 路由组
- 统一返回结构：`{ status: bool, message: string, data: object|array }`
- 补充：`templates.md` 保留 `process-template` 后台能力设计说明，不计入当前主菜单页索引。

## 页面文档清单
| 页面 | 前端路由 | 设计文档 |
|---|---|---|
| 图片编辑 | `/advanced/image-process` | [image-process.md](./image-process.md) |
| AI 检索 | `/advanced/ai-search` | [ai-search.md](./ai-search.md) |
| AI 提示词 | `/advanced/ai-prompt` | [ai-prompt.md](./ai-prompt.md) |
| AI 配置 | `/advanced/ai-config` | [ai-config.md](./ai-config.md) |
| 处理驱动 | `/advanced/drivers` | [drivers.md](./drivers.md) |
| 审核中心 | `/advanced/reviews` | [reviews.md](./reviews.md) |
| 作业中心 | `/advanced/jobs` | [jobs.md](./jobs.md) |
| 团队权限 | `/advanced/team-permissions` | [team-permissions.md](./team-permissions.md) |

## 跨页依赖关系
- AI 配置页 -> AI 检索 / AI 提示词页：统一 provider、API Key、Base URL 与默认模型基线。
- process-template 后台能力 -> 作业页：`/advanced-api/process-templates/{id}/dispatch` 创建异步任务后，转到作业页查询 `process-jobs/{jobId}`。
- 团队权限页 -> 其余页面：空间上下文由 `space_id`（`X-Space-Id` 或 token `current_space_id`）决定，影响图片检索、处理、提示词等数据隔离。
- 审核中心与图片域：审核状态写入 `images.review_status`，会影响后续检索与治理流程。

## 路由分组速览（advanced-api）
- 图片域：`images/search`、`images/ai-search`、`images/{key}/process`
- AI 域：`ai/prompt`、`ai/prompt-tasks`、`ai/config`
- 处理能力域：`processing/drivers/status`
- 模板域：`process-templates`、`process-templates/{id}/run`、`process-templates/{id}/dispatch`
- 作业域：`process-jobs`、`process-jobs/{jobId}`、`retry`、`cancel`
- 空间与权限域：`spaces`、`spaces/switch`、`spaces/{id}/members`、`spaces/{id}/members/{userId}/role`
- 审核域（管理员）：`admin/reviews`、`approve`、`reject`

## 公共设计约束
- 会话鉴权：所有 `advanced-api` 接口处于 `auth` 保护下。
- 空间隔离：读取/写入型接口都按 `space_id` 解析当前空间（中间件 `ResolveTeamSpaceContext`）。
- 审计日志：关键写操作使用 `operation.audit`（`audit` channel）记录 `action/resource/target/result/user_id/request_id/trace_id/ip`。
- 幂等与可重试：异步作业支持 `retry/cancel`，状态机详见 [jobs.md](./jobs.md)。
