# 审核中心页设计（/advanced/reviews）

## 页面信息架构
- 页面目标：管理员对图片审核状态进行查询、通过、驳回。
- 信息分区：
  - 筛选区：`status`、分页参数（可扩展 `per_page`）。
  - 审核动作区：`图片 key`、`驳回原因`、`通过/驳回`按钮。
  - 结果区：待审/已审列表与操作反馈。

## 交互流程
1. 管理员选择状态（默认 `review_pending`）并查询列表。
2. 对单条图片执行：
   - 通过：`POST /advanced-api/admin/reviews/{key}/approve`
   - 驳回：`POST /advanced-api/admin/reviews/{key}/reject`
3. 成功后局部刷新当前列表或直接更新行状态。
4. 非管理员访问时直接收到“无权限操作”。

## 状态机
### 页面状态
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示筛选项 | `loading_list` |
| `loading_list` | 查询列表 | 表格 loading | `list_ready` / `empty` / `error` |
| `list_ready` | 有数据 | 展示分页表格 | `loading_action` / `loading_list` |
| `loading_action` | 执行通过/驳回 | 行级按钮 loading | `list_ready` / `error` |
| `empty` | 返回空列表 | 显示空态说明 | `loading_list` |
| `error` | 请求失败 | 展示错误 message | `loading_list` |

### 审核领域状态（Image.review_status）
- `review_pending -> review_approved`（通过接口）
- `review_pending -> review_rejected`（驳回接口）
- 当前无“撤回到 pending”路由，二次审核需通过管理策略扩展。

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 查询审核列表 | `advanced.api.reviews.index` | `GET /advanced-api/admin/reviews` | `status`、`per_page(20/40/100)` | 分页列表：`review_status/review_reason/reviewed_at/reviewer` |
| 审核通过 | `advanced.api.reviews.approve` | `POST /advanced-api/admin/reviews/{key}/approve` | 无 | 返回该图片最新审核字段 |
| 审核驳回 | `advanced.api.reviews.reject` | `POST /advanced-api/admin/reviews/{key}/reject` | `review_reason(required,max=2000)` | 返回该图片最新审核字段 |

## 数据模型
- `ReviewImageItem`
  - `key: string`
  - `origin_name/alias_name: string`
  - `size/mimetype/extension: number|string`
  - `review_status: review_pending|review_approved|review_rejected`
  - `review_reason?: string|null`
  - `reviewed_at?: string|null`
  - `reviewed_by?: int|null`
  - `user: {id,name,email}`
  - `album: {id,name}|null`
- `ReviewActionResult`
  - `key`
  - `review_status`
  - `review_reason`
  - `reviewed_at`
  - `reviewed_by`

## 错误与空态
- 权限错误：`无权限操作`。
- 参数错误：`status 参数无效`、驳回缺少 `review_reason`。
- 资源错误：`图片不存在`。
- 空态：指定状态下无记录，提示可切换状态筛选。

## 可观测性点
- 已有审计：
  - `api.admin.review.access`（拒绝访问）
  - `api.admin.review.approve`（成功/图片不存在）
  - `api.admin.review.reject`（成功/图片不存在）
- 建议补充：查询接口 `index` 的读操作埋点（含状态筛选和返回数量）。
- 前端埋点建议：`advanced_reviews_query`、`advanced_reviews_approve`、`advanced_reviews_reject`。

## 安全设计
- 强权限门槛：控制器内 `ensureAdmin` 二次校验管理员身份。
- 输入校验：`status` 白名单，`review_reason` 长度限制。
- 数据最小化：审核列表只返回审核操作所需字段。
- 审计追踪：关键写操作记录操作者 `user_id`、`target(key)`、`result`。
