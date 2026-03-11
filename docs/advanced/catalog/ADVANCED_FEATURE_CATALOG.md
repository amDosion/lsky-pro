# ADVANCED_FEATURE_CATALOG

## 1. 功能分级

| 功能标识 | 页面 | 分级 | 主负责人 | 核心价值 |
|---|---|---|---|---|
| image-process | `/advanced/image-process` | P0 | frontend | 单图/批量处理核心入口，直接影响素材生产效率 |
| drivers | `/advanced/drivers` | P0 | backend | 处理引擎可用性基线，决定处理链路稳定性 |
| team-permissions | `/advanced/team-permissions` | P0 | backend | 空间隔离与权限安全基础能力 |
| ai-config | `/advanced/ai-config` | P1 | backend | 统一 AI provider、API Key、Base URL 与默认模型基线 |
| jobs | `/advanced/jobs` | P1 | ops | 异步任务可观测与可恢复 |
| reviews | `/advanced/reviews` | P1 | backend | 内容合规审核闭环 |
| ai-search | `/advanced/ai-search` | P2 | frontend | 智能检索效率提升 |
| ai-prompt | `/advanced/ai-prompt` | P2 | frontend | Prompt 资产化与内容生产效率 |

> `process-template` 仍通过 `/advanced-api/process-templates*` 作为后台处理能力保留，不再作为独立主菜单页。

## 2. 依赖关系

### 2.1 关键依赖

- `drivers -> image-process`
- `team-permissions -> image-process`
- `team-permissions -> jobs`
- `team-permissions -> reviews`
- `team-permissions -> ai-search`
- `team-permissions -> ai-prompt`
- `ai-config -> ai-search`
- `ai-config -> ai-prompt`
- `image-process -> process-template`
- `process-template -> jobs`
- `reviews -> ai-search`

### 2.2 依赖矩阵

| From \ To | image-process | ai-config | jobs | reviews | ai-search | ai-prompt |
|---|---|---|---|---|---|---|
| drivers | X |  |  |  |  |  |
| team-permissions | X |  | X | X | X | X |
| ai-config |  |  |  |  | X | X |
| reviews |  |  |  |  | X |  |

> 后台能力链路：`image-process -> process-template -> jobs`。

## 3. 里程碑

| 里程碑 | 功能范围 | 目标 | 退出条件 | 门禁 |
|---|---|---|---|---|
| M1 基础能力 | drivers, team-permissions, image-process | 建立处理可用性 + 权限隔离 + 核心处理页 | 基础接口与页面可用，权限隔离回归通过 | security=pass, reviewer=pass |
| M2 运营闭环 | process-template, jobs, reviews | 建立模板复用、作业调度、审核合规闭环 | 异步作业可追踪可恢复，审核链路可追责 | qa=pass, security=pass, reviewer=pass |
| M3 智能增强 | ai-config, ai-search, ai-prompt | 提升 provider 治理、检索与内容生成效率 | 配置保存稳定，检索与 prompt 结果稳定，异常降级可用 | qa=pass, reviewer=pass |

## 4. 回归清单

| 检查域 | 回归要点 | owner | 验证命令 |
|---|---|---|---|
| 路由可用性 | `advanced-api` 全部关键路由存在 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api --no-ansi` |
| 图片处理 | `images/{key}/process` 参数/异常分支 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/images --no-ansi` |
| 驱动状态 | `processing/drivers/status` 返回结构稳定 | ops | `docker exec lsky-pro php artisan route:list --path=advanced-api/processing/drivers/status --no-ansi` |
| AI 配置 | `ai/config` 读写结构与权限稳定 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/ai/config --no-ansi` |
| 模板执行 | `process-template` run/dispatch 成功与失败隔离 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/process-templates --no-ansi` |
| 作业调度 | jobs 查询/重试/取消状态机正确 | ops | `docker exec lsky-pro php artisan route:list --path=advanced-api/process-jobs --no-ansi` |
| 审核链路 | reviews 查询与 approve/reject 权限有效 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/admin/reviews --no-ansi` |
| 团队权限 | spaces/members/role 更新防越权 | security | `docker exec lsky-pro php artisan route:list --path=advanced-api/spaces --no-ansi` |
| 智能检索 | ai-search 结果与空态稳定 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/images/ai-search --no-ansi` |
| 提示词生成 | ai-prompt 参数校验、任务结果与 provider/model 回传稳定 | qa | `docker exec lsky-pro php artisan route:list --path=advanced-api/ai/prompt --no-ansi` |
| 全链路 | 基础脚本与冒烟检查通过 | ops | `bash scripts/codex/validate.sh && bash scripts/acceptance/api-smoke.sh` |

## 5. 风险与缓解

| 风险ID | 风险描述 | 影响 | 触发信号 | 缓解策略 | owner |
|---|---|---|---|---|---|
| R-01 | 处理驱动不可用导致处理页整体失效 | 高 | `drivers.available=false` | 增加启动检查与降级提示 | ops |
| R-02 | 空间权限缺陷引发跨团队数据泄露 | 严重 | 非法跨空间读取/写入 | 强制 space 校验 + 安全专项测试 | security |
| R-03 | process-template 定义失控导致批处理错误扩散 | 高 | 大批量失败率升高 | 模板校验白名单 + 小流量试跑 | backend |
| R-04 | 作业积压导致延迟扩大 | 高 | 队列 backlog 持续增长 | 扩容 worker + 重试退避 + 告警 | ops |
| R-05 | 审核误判或权限绕过导致合规风险 | 高 | 审核异常投诉/越权日志 | 审核操作审计 + 双人复核策略 | security |
| R-06 | AI 检索慢查询影响用户体验 | 中高 | P95 响应上升 | 建索引、分页限制、降级策略 | backend |
| R-07 | Prompt 内容泄露敏感字段 | 高 | 输出含隐私元数据 | 元数据最小化输出 + 脱敏策略 | security |
| R-08 | 页面与 API 状态机不一致引发误操作 | 中 | 前端显示与后端状态不一致 | 明确状态机契约 + QA 回归 | frontend |

## 6. 发布建议

1. 按 `M1 -> M2 -> M3` 顺序推进，不跳阶段发布。
2. 每阶段结束必须完成 `security` 和 `reviewer` 双门禁。
3. 发布前执行统一验证命令并将证据写入 runbook。

## 7. 组合场景

- 组合式落地场景文档：`docs/advanced/catalog/COMBINED_ADVANCED_SCENARIOS.md`
