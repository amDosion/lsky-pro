# 处理驱动页设计（/advanced/drivers）

## 页面信息架构
- 页面目标：展示当前图片处理驱动配置与可用性，支撑故障定位与环境巡检。
- 信息分区：
  - 操作区：`刷新驱动状态`。
  - 结果区：`configured/strict/drivers[*].available/reason`。

## 交互流程
1. 用户进入页面或点击“刷新驱动状态”。
2. 调用 `GET /advanced-api/processing/drivers/status`。
3. 渲染当前驱动与候选驱动可用性原因。
4. 若不可用，前端提示引导至运维排查（安装扩展、配置切换）。

## 状态机
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 首次进入 | 显示“等待刷新” | `loading` |
| `loading` | 点击刷新 | 按钮 loading | `success` / `error` |
| `success` | 请求成功 | 渲染驱动状态卡片 | `loading` |
| `error` | 请求失败 | 展示错误提示 | `loading` |

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 查询驱动状态 | `advanced.api.processing.status` | `GET /advanced-api/processing/drivers/status` | 无 | `data.configured`、`data.strict`、`data.drivers` |

## 数据模型
- `ProcessingStatus`
  - `configured: string`（当前配置，如 `imagick`/`libvips`）
  - `strict: bool`（当前实现固定为 `true`）
  - `drivers: Record<string, DriverState>`
- `DriverState`
  - `available: bool`
  - `reason: string|null`

## 错误与空态
- 网络失败/会话失效导致请求失败。
- 空态：首次进入尚未查询时显示占位文案。
- 异常态：`configured` 指向不可用驱动时，结果区应高亮告警并提示影响范围（图片处理接口会失败）。

## 可观测性点
- 现状：该接口未写入 `auditOperation`。
- 建议新增后端审计：`api.processing.status.read`，记录 `configured` 和每个 driver 可用性。
- 建议前端埋点：
  - `advanced_drivers_refresh`
  - `advanced_drivers_unavailable_detected`

## 安全设计
- 访问控制：仅登录用户可访问。
- 信息边界：只返回驱动可用性摘要，不暴露系统命令、路径等敏感细节。
- 风险控制：前端禁止将 `reason` 作为 HTML 渲染，防止注入链路。
