# AI 提示词页设计（/advanced/ai-prompt）

## 页面信息架构
- 页面目标：基于图片元数据生成可复用提示词（Prompt），用于创作、营销、描述生成等场景。
- 信息分区：
  - 输入区：`图片 Key`、`意图(intent)`、`模板(template)`、`语言(language)`。
  - 操作区：`生成提示词`。
  - 结果区：返回 `prompt + metadata + template_used`。

## 交互流程
1. 用户填写 `key` 和 `intent`，可选模板与语言。
2. 调用 `POST /advanced-api/ai/prompt`。
3. 服务端校验图片归属与参数，拼装 metadata 并套用模板。
4. 成功后展示 prompt 文本，支持复制与二次编辑。
5. 失败则保留输入并提示错误。

## 状态机
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始态 | 展示输入项与占位文案 | `validating` |
| `validating` | 点击生成 | 检查 `key/intent` | `submitting` / `error` |
| `submitting` | 调用 API | 显示请求中 | `success` / `error` |
| `success` | 成功返回 | 渲染 prompt 与 metadata | `validating` |
| `error` | 参数/权限/网络错误 | 显示失败 message | `validating` |

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 生成提示词 | `advanced.api.ai.prompt` | `POST /advanced-api/ai/prompt` | `key`、`intent`、`template?`、`language?`、`style?` | `data.prompt`、`data.metadata`、`data.template_used` |
| 选图辅助（可选） | `advanced.api.images.search` | `GET /advanced-api/images/search` | `q` 等 | 用于定位可用图片 key |

## 数据模型
- `AiPromptRequest`
  - `key: string(required)`
  - `intent: string(required, <=2000)`
  - `template?: string(<=5000)`
  - `language?: string(<=32, default=zh-CN)`
  - `style?: string(<=200, default=专业、简洁、可执行)`
- `AiPromptResponse.data`
  - `prompt: string`
  - `template_used: string`
  - `metadata: object`
    - `key/filename/origin_name/mimetype/size_bytes/dimensions/tags/ocr_text/url`

## 错误与空态
- 参数错误：缺少 `key`、`intent`，或字段超长。
- 业务错误：`图片不存在`（常见于 key 不存在或空间不匹配）。
- 空态：初始无结果时显示“等待生成”；生成失败后保留上次成功结果（建议）。

## 可观测性点
- 服务端已接入审计：`action=api.ai.prompt`，记录 `target(key)`、`intent_length`、`user_id`。
- 建议前端埋点：
  - `advanced_ai_prompt_submit`
  - `advanced_ai_prompt_success`
  - `advanced_ai_prompt_error`
  - 指标：生成成功率、平均 prompt 长度、平均响应时延。

## 安全设计
- 访问控制：`auth` 登录态必需。
- 数据归属：按用户+空间查询图片，防止越权读取他人元数据。
- 输入防护：长度限制避免超大输入压垮服务。
- 内容安全：`template` 属于自由文本，前端仅按文本展示，不作为 HTML 执行。
- 隐私控制：metadata 中可能包含 OCR 文本，默认不写前端持久日志。
