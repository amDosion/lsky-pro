# Requirements Document

## Product Goal

- Problem statement:
  - provider-backed intelligence 和 identity governance 已经立住，但当前 AI 检索仍偏向 term/keyword 拼装，标签同步与 Prompt 质量没有形成完整闭环。
  - `image_intelligence_records` 已经能写入真实 provider/model 结果，但这些结果还没有被稳定地规范成更强的搜索语料、标签材料和 Prompt V2 上下文。
  - 用户原始需求里“AI 检索完全重构、对图片方向推理生成更详细提示词、后台定时补标签/tag”仍是当前最大未完成块。
- Target users:
  - 图片运营/设计用户：需要更准确的 AI 检索、更稳定的标签和更详细的 Prompt。
  - 平台管理员：需要可观察的 intelligence sync、批量回填和失败排查手段。
  - 图片消费用户：需要图片详情里能看到可解释的 AI 结果来源，而不是模糊占位字段。
- Success criteria:
  - intelligence labels/keywords/summary 能形成稳定的 tag/corpus 同步链路。
  - AI 搜索结果的排序和解释明显优于当前单纯关键词/term 加权。
  - Prompt V2 能基于 intelligence payload 生成更详细的方向、构图、风格和用途提示。

## In Scope

- Must-have features:
  - intelligence tag sync / corpus enrichment
  - AI retrieval ranking v2
  - Prompt V2 context generation
  - advanced AI search / image detail explainability UI
  - scheduler / runbook / regression evidence for the new intelligence flow
- Frontend scope:
  - `/advanced` AI 搜索结果解释
  - 单图 intelligence / prompt explainability 区块
- Backend scope:
  - intelligence corpus 规范化、同步、可见性与去重
  - retrieval scoring / ranking v2
  - Prompt V2 context builder
  - scheduler / backfill / operator payload
- Documentation and orchestration scope:
  - 第九轮 requirements/design/tasks/context/queue 对齐
  - 明确“手工 UI 验收”与“镜像内 focused 验证”的边界

## Out of Scope

- 本轮不直接完成以下完整能力：
  - 向量检索 / embedding / pgvector
  - 更深层的浏览器自动化体系
  - 认证 legacy snapshot 彻底退场
  - 图片工作台整页重做

## Functional Requirements

1. `FR-901` 系统必须能把 intelligence labels/keywords/summary 规范成可持续同步的搜索语料或标签材料，而不是只停留在单次分析结果里。
2. `FR-902` AI 检索必须引入 ranking v2，综合 intelligence payload、OCR、manual tags、origin_name 等信号，而不是仅靠简单关键词命中。
3. `FR-903` Prompt V2 必须基于 intelligence payload 输出更详细的主题、构图、风格、用途或方向提示，并对降级来源保持诚实。
4. `FR-904` `/advanced` AI 搜索和单图详情必须展示 explainability 信息，让用户能看见命中依据、同步状态和 intelligence 来源。
5. `FR-905` scheduler / runbook 必须支持 intelligence tag sync 的定时执行、回填和失败排查。
6. `FR-906` 本轮不得回退第八轮的 identity governance、Passkey 或 social identity contract。

## Non-Functional Requirements

- Performance:
  - retrieval v2 不能显著拖慢当前 AI 搜索接口。
  - intelligence tag sync 必须支持分批、限流和重跑。
- Reliability:
  - 同步失败、provider 结果缺失或字段不完整时必须有清晰 fallback。
  - manual tags 与 intelligence-derived tags 的边界必须稳定，不得互相污染。
- Security:
  - OCR/intelligence/prompt 解释不得无意泄漏敏感内容或 provider 原始返回中的不安全字段。
  - 本轮仍需保留对 legacy identity carryover 的显式注意，但不以此作为主线实现。
- Observability:
  - 需要能看见 sync 状态、最近更新时间、失败样本或至少可解释的 operator summary。

## Acceptance Criteria

1. `AC-901` intelligence tag sync / corpus enrichment 有 focused tests 覆盖去重、可见性和读侧消费。
2. `AC-902` retrieval ranking v2 有 focused tests，能证明 intelligence payload 被真正纳入排序或解释。
3. `AC-903` Prompt V2 有 focused tests，能证明输出比现有上下文更详细且来源诚实。
4. `AC-904` `/advanced` 或单图 explainability UI 已能展示 intelligence 来源、命中依据或同步状态。
5. `AC-905` 第九轮验证继续统一使用重建后的 `lsky-pro-custom:php83`。
6. `AC-906` `queue/tasks.csv`、`docs/design.md`、`docs/tasks.md`、`docs/context-compact.md` 与第九轮目标一致。

## Risks and Open Questions

- Risk:
  - intelligence term/tag sync 如果直接覆盖现有 manual tag 语义，会破坏用户手工管理结果。
- Risk:
  - retrieval v2 如果只叠加更多字段而不控制权重，可能会让结果变得更噪。
- Risk:
  - Prompt V2 如果直接透出 provider 原始文本，可能引入不一致或敏感暴露。
- Question:
  - intelligence-derived tags 是继续走 projection/read-side 语义，还是需要更显式的 sync state 持久化。
- Question:
  - retrieval explainability 放在搜索结果卡片、详情页还是两者都需要。
