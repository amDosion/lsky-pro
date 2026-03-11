# 作业中心页设计（/advanced/jobs）

## 页面信息架构
- 页面目标：追踪异步批处理作业，提供详情、重试、取消能力。
- 信息分区：
  - 筛选区：`status`。
  - 列表区：最近作业（最多 50 条）。
  - 操作区：`job_id`、`详情`、`重试`、`取消`。
  - 结果区：作业进度与结果明细（successes/failures）。

## 交互流程
1. 用户按状态查询作业列表（或全部）。
2. 选择某作业查看详情。
3. 对失败类作业执行重试；对进行中作业执行取消。
4. 轮询详情（前端可扩展）直至终态。

## 状态机
### 页面状态
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示筛选和占位 | `loading_list` |
| `loading_list` | 查询列表 | 列表 loading | `list_ready` / `empty` / `error` |
| `list_ready` | 返回作业列表 | 展示作业摘要 | `loading_detail` / `operating` / `loading_list` |
| `loading_detail` | 查看详情 | 详情 loading | `detail_ready` / `error` |
| `detail_ready` | 详情成功 | 展示进度与结果 | `operating` / `loading_detail` |
| `operating` | 重试/取消 | 行级操作 loading | `detail_ready` / `error` |
| `empty` | 无作业 | 展示空态引导到模板页派发 | `loading_list` |
| `error` | 接口失败 | 显示 message | `loading_list` |

### 作业领域状态（`image_process_jobs.status`）
- 初始：`pending`
- Worker 领取：`pending/retrying -> processing`
- 终态：
  - `processing -> success`
  - `processing -> failed`
  - `processing -> partial_success`
- 用户取消：`pending/retrying/processing -> cancelled`
- 用户重试：`failed/partial_success/cancelled -> retrying -> processing`
- 终态 `success` 不允许重试或取消。

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 列表查询 | `advanced.api.jobs.index` | `GET /advanced-api/process-jobs` | `status?` | `items[]`（最多 50 条） |
| 作业详情 | `advanced.api.jobs.show` | `GET /advanced-api/process-jobs/{jobId}` | `jobId` | 单作业完整快照 |
| 重试作业 | `advanced.api.jobs.retry` | `POST /advanced-api/process-jobs/{jobId}/retry` | `jobId` | 新状态（`retrying`） |
| 取消作业 | `advanced.api.jobs.cancel` | `POST /advanced-api/process-jobs/{jobId}/cancel` | `jobId` | 新状态（`cancelled`） |

## 数据模型
- `ImageProcessJob`
  - `job_id: string`
  - `status: pending|retrying|processing|success|failed|partial_success|cancelled`
  - `template: {id,name}`
  - `total/processed/success/failed: int`
  - `progress: int(0~100)`
  - `result: {keys[], space_id?, successes[], failures[]}`
  - `error_message?: string|null`
  - `started_at/finished_at/created_at/updated_at`
- `JobFailureItem`
  - `key: string`
  - `message: string`

## 错误与空态
- 资源错误：`任务不存在`。
- 状态错误：
  - `当前任务状态不可重试`
  - `当前任务状态不可取消`
- 空态：无作业时提示“去模板页派发任务”。
- 一致性说明：取消后任务可能已处理部分 key，前端需展示 `processed/success/failed` 实时值。

## 可观测性点
- 已有审计：
  - `api.process_job.retry`
  - `api.process_job.cancel`
- 建议补充：`jobs.index/show` 读接口埋点，用于看板统计查询压力。
- 前端埋点建议：`advanced_jobs_list`、`advanced_jobs_detail`、`advanced_jobs_retry`、`advanced_jobs_cancel`。

## 安全设计
- 访问控制：仅登录用户；并以 `user_id` 过滤作业，禁止跨用户读取。
- 状态约束：重试/取消均做状态白名单校验，防止非法跃迁。
- 幂等防护：Worker 通过原子 update 抢占 `pending/retrying`，避免重复执行。
- 故障收敛：`failed()` 回调统一落 `status=failed` 与错误信息。
