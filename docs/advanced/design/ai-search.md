# AI 检索页设计（/advanced/ai-search）

## 页面信息架构
- 页面目标：通过自然检索词在名称、别名、OCR 文本、标签中进行综合检索，并返回相关度排序结果。
- 信息分区：
  - 查询区：关键词输入（`q`）。
  - 操作区：`检索`。
  - 结果区：分页列表（包含 `ai_score`）。

## 交互流程
1. 用户输入检索词 `q`。
2. 调用 `GET /advanced-api/images/ai-search?q=...`。
3. 服务端执行加权评分（精确命中/模糊命中/OCR/标签）。
4. 返回分页结果，前端按 `ai_score desc, created_at desc` 展示。
5. 用户可继续翻页、改词重检索。

## 状态机
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始态 | 展示输入框与占位提示 | `querying` |
| `querying` | 点击检索 | 显示 loading | `result` / `empty` / `error` |
| `result` | 命中记录 | 展示列表+分页 | `querying` |
| `empty` | 无命中 | 展示空态（建议带检索建议） | `querying` |
| `error` | 校验/网络/服务异常 | 展示错误 message | `querying` |

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| AI 检索主接口 | `advanced.api.images.ai-search` | `GET /advanced-api/images/ai-search` | `q`(required, <=255) | 分页列表，单项含 `key/origin_name/ocr_text/tags/ai_score` |
| 普通检索兜底（可选） | `advanced.api.images.search` | `GET /advanced-api/images/search` | `q` 等过滤参数 | 普通分页列表，不含加权得分 |

## 数据模型
- `AiSearchQuery`
  - `q: string(1~255)`
  - `page?: int`
- `AiSearchItem`
  - `key: string`
  - `origin_name: string`
  - `pathname: string`
  - `tags: {id:int,name:string}[]`
  - `ocr_text?: string`
  - `ai_score: number`
  - `review_status: review_pending|review_approved|review_rejected`
  - `date/human_date: string`
- `AiSearchPage`
  - Laravel 分页结构：`current_page/data/total/per_page/...`

## 错误与空态
- 参数错误：`q` 缺失或超过长度限制。
- 服务错误：数据库/网络异常。
- 空态：返回分页 `data=[]` 时提示“无匹配结果”，并建议缩短关键词或改用标签词。

## 可观测性点
- 现状：该接口未写入 `auditOperation`（无后端审计 action）。
- 建议新增后端审计：`api.image.ai_search`，记录 `q_length`、`result_count`、`space_id`。
- 建议前端埋点：
  - `advanced_ai_search_submit`
  - `advanced_ai_search_result`
  - `advanced_ai_search_empty`
  - `advanced_ai_search_error`

## 安全设计
- 会话鉴权：仅登录用户可访问。
- 空间隔离：查询自动绑定当前 `space_id`，防止跨空间数据泄露。
- 查询约束：`q` 长度限制 + 分页，降低滥用与慢查询风险。
- 输出最小化：仅返回页面需要字段，避免暴露不必要内部信息。
