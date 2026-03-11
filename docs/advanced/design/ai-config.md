# AI 配置页设计（/advanced/ai-config）

## 页面信息架构
- 页面目标：统一管理 AI provider 与模型基线。
- 信息分区：
  - provider 卡片区：展示 label、Base URL、默认模型、ready 状态。
  - 配置面板区：维护 `active_provider/default_model/api_key/base_url/models`。
  - 状态区：显示加载、保存成功与错误反馈。

## 交互流程
1. 页面加载时调用 `GET /advanced-api/ai/config`。
2. 用户在左侧或下拉框切换当前编辑的 provider。
3. 修改 `api_key/base_url/models/default_model` 后点击保存。
4. 页面调用 `PUT /advanced-api/ai/config`，成功后回填标准化结果。
5. 非管理员访问保存接口时显示失败提示。

## 状态机
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示占位文案 | `loading` |
| `loading` | 拉取配置 | 表单禁用、状态栏提示“正在加载” | `ready` / `error` |
| `ready` | 配置已加载 | 可切换 provider、编辑表单、保存 | `saving` / `error` |
| `saving` | 提交保存 | 表单禁用、状态栏提示“正在保存” | `ready` / `error` |
| `error` | 读写失败 | 状态栏与 toast 展示 message | `loading` / `ready` |

## API 映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 读取配置 | `advanced.api.ai.config.show` | `GET /advanced-api/ai/config` | 无 | `active_provider/providers/provider_options` |
| 保存配置 | `advanced.api.ai.config.update` | `PUT /advanced-api/ai/config` | `active_provider/providers` | 标准化后的完整配置 |

## 数据模型
- `AiConfigPayload`
  - `active_provider: gpt|deepseek|qwen|gemini`
  - `providers: Record<string, AiProviderItem>`
  - `provider_options: Array<{provider,label,ready}>`
- `AiProviderItem`
  - `provider: string`
  - `label: string`
  - `transport: openai_compatible|gemini`
  - `base_url: string`
  - `api_key: string`
  - `default_model: string`
  - `models: string[]`
  - `ready: bool`

## 错误与空态
- 非管理员读取/保存：提示“仅管理员可查看/修改 AI 配置”。
- `api_key` 为空：provider 标记为 `ready=false`。
- 模型列表为空：回退 provider 默认模型集合。
- 保存校验失败：展示首条错误信息。

## 可观测性点
- 配置读写建议记录 `active_provider`、`provider_count`、`request_id`。
- QA 回归重点：provider 切换、默认模型回退、缓存刷新、权限拒绝。

## 安全设计
- 仅管理员可修改全局 AI 配置。
- API Key 不应出现在公开页面或非管理员错误信息中。
- 保存后清理 `configs` 缓存，避免旧配置继续参与 AI 请求。
