# 团队权限页设计（/advanced/team-permissions）

## 页面信息架构
- 页面目标：在团队空间内查看成员并调整角色（owner/admin/member）。
- 信息分区：
  - 空间区：空间列表、当前空间标记、空间切换（建议显式支持）。
  - 成员区：成员列表、操作者权限说明。
  - 变更区：目标 `user_id` 与目标 `role`。

## 交互流程
1. 加载空间列表，确定当前空间。
2. 选择空间后拉取成员列表。
3. 具备 `member.update_role` 权限的操作者可提交角色变更。
4. 成功后刷新目标成员行与权限集合；失败则给出原因。

## 状态机
### 页面状态
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示输入区 | `loading_spaces` |
| `loading_spaces` | 查询空间列表 | 列表 loading | `space_ready` / `error` |
| `space_ready` | 已选择空间 | 可拉取成员 | `loading_members` / `switching_space` |
| `switching_space` | 提交空间切换 | 显示切换中 | `space_ready` / `error` |
| `loading_members` | 查询成员 | 表格 loading | `members_ready` / `empty` / `error` |
| `members_ready` | 成员已展示 | 可发起角色更新 | `updating_role` / `loading_members` |
| `updating_role` | 提交角色更新 | 行级 loading | `members_ready` / `error` |
| `empty` | 无成员 | 显示空态 | `loading_members` |
| `error` | 任一请求失败 | 展示错误 | `space_ready` |

### 角色变更约束状态
- 允许：`admin <-> member`
- 禁止：
  - `owner -> *`（拥有者不可修改）
  - `* -> owner`（接口不支持提升为 owner）

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 空间列表 | `advanced.api.spaces.index` | `GET /advanced-api/spaces` | 无 | `current_space_id`、`spaces[]` |
| 切换空间 | `advanced.api.spaces.switch` | `POST /advanced-api/spaces/switch` | `space_id` | `current_space_id` |
| 成员列表 | `advanced.api.spaces.members` | `GET /advanced-api/spaces/{id}/members` | `id(space_id)` | `space/operator/members[]` |
| 更新角色 | `advanced.api.spaces.members.role.update` | `PUT /advanced-api/spaces/{id}/members/{userId}/role` | `role in owner|admin|member`（实际仅支持 admin/member 变更） | 更新后的 `member` |

## 数据模型
- `SpaceItem`
  - `id`
  - `name`
  - `is_personal`
  - `role`（当前用户在该空间的角色）
  - `is_current`
  - `owner_user_id`
- `Operator`
  - `user_id`
  - `role`
  - `permissions[]`
- `MemberItem`
  - `id`
  - `user_id`
  - `name/email`
  - `role`
  - `permissions[]`
  - `is_self`

## 错误与空态
- 权限错误：
  - `无权限访问该空间`
  - `无权限查看空间成员`
  - `无权限更新成员角色`
- 资源错误：`空间不存在`、`成员不存在`。
- 规则错误：`空间拥有者角色不可修改`、`不支持通过该接口提升为 owner`。
- 空态：空间成员为空时提示邀请成员入口（可扩展）。

## 可观测性点
- 已有审计：
  - `api.space.switch`（success/failed）
  - `api.space.member.update_role`（success）
- 建议补充：`members` 查询读埋点，记录 `space_id` 与成员数。
- 前端埋点建议：`advanced_space_switch`、`advanced_members_list`、`advanced_member_role_update`。

## 安全设计
- 空间访问控制：`switch` 需先校验 membership；`members/update_role` 要求操作者在目标空间中并具备权限。
- 细粒度权限：基于 `TeamMembership.permissions` + `can(ability)` 判定，角色仅是默认权限集。
- 越权防护：接口总是按 `{space_id, user_id}` 精确查询目标成员，避免跨空间修改。
- 审计闭环：角色变更记录 `space_id/target_user_id/operator_user_id/role`，满足追责需求。
