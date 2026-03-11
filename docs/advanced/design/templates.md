# process-template 后台能力设计（原 `/advanced/templates`）

> 独立主菜单页已调整为 `/advanced/ai-config`。本文件保留 `process-template` 后台能力的设计说明，供 `image-process`、`jobs` 等调用点复用。

## 能力信息架构
- 能力目标：管理图片处理模板，并支持同步批处理与异步派发。
- 核心分区：
  - 模板管理：模板列表、模板创建（`name/definition/is_shared`）。
  - 执行能力：`template_id` + `keys[]`。
  - 结果输出：同步执行结果或派发后的作业信息。

## 交互流程
1. 调用点进入时先查询模板列表（含本人模板 + 共享模板）。
2. 用户可创建新模板（定义经服务端校验）。
3. 执行方式：
   - 立即执行：`run`，接口返回逐 key 成功/失败明细。
   - 异步派发：`dispatch`，返回 `job_id` 供作业页跟踪。
4. 失败时保留 `keys` 和 `template_id`，允许调整后重试。

## 消费端状态机
| 状态 | 触发条件 | 调用点行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示空表单/空列表 | `loading_templates` |
| `loading_templates` | 拉取模板 | 列表 loading | `ready` / `error` |
| `ready` | 模板可用 | 可创建/执行/派发 | `creating` / `running` / `dispatching` |
| `creating` | 创建模板 | 提交中 | `ready` / `error` |
| `running` | 同步批处理 | 执行中 | `ready` / `error` |
| `dispatching` | 异步派发 | 提交中 | `ready` / `error` |
| `error` | 任一操作失败 | 显示 message | `ready` |

## API 映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 模板列表 | `advanced.api.templates.index` | `GET /advanced-api/process-templates` | 无 | `items[]`（含 `is_owner/is_shared`） |
| 创建模板 | `advanced.api.templates.store` | `POST /advanced-api/process-templates` | `name`、`definition`、`is_shared?` | 新模板对象 |
| 同步执行模板 | `advanced.api.templates.run` | `POST /advanced-api/process-templates/{id}/run` | `keys[] (1~500)` | `successes[]/failures[]/counts` |
| 异步派发模板 | `advanced.api.templates.dispatch` | `POST /advanced-api/process-templates/{id}/dispatch` | `keys[] (1~500)` | 作业快照（`job_id/status/progress`） |

## 数据模型
- `ImageProcessTemplate`
  - `id: int`
  - `name: string<=128`
  - `definition: object`（同 `ImageProcessRequest` 结构）
  - `is_shared: bool`
  - `is_owner: bool`
  - `created_at/updated_at`
- `TemplateRunResult`
  - `template: {id,name}`
  - `requested_count/success_count/failure_count`
  - `successes[]`
  - `failures[]: {key,message}`
- `TemplateDispatchResult`
  - `job_id`
  - `status`（初始 `pending`）
  - `total/processed/success/failed/progress`

## 错误与空态
- 参数错误：`name/definition` 非法、`keys` 为空或超过 500。
- 资源错误：`模板不存在` 或无权限。
- 执行错误：单 key 失败会进入 `failures[]`，不阻断其他 key。
- 空态：模板列表为空时提示“先创建模板再执行”。

## 可观测性点
- 已有审计：
  - `api.process_template.create`
  - `api.process_template.run`
  - `api.process_template.dispatch`
- 关键观测字段：`requested_count/success_count/failure_count/template_id/job_id`。
- 调用点埋点建议：`advanced_templates_create`、`advanced_templates_run`、`advanced_templates_dispatch`。

## 安全设计
- 权限范围：仅可创建自己的模板；执行时可读取“自己模板 + 共享模板”。
- 输入校验：模板定义通过 `ImageProcessExecutor` 同步校验，阻断非法操作参数。
- 数据隔离：执行目标 key 仍按用户与空间权限判定，不因共享模板放宽图片访问边界。
- 审计闭环：所有写入/执行动作均记录审计日志，支撑追责。
