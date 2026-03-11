# Design Document

## System Overview

- Architecture summary:
  - 第九轮不再把认证测试基建作为主线，而是回到图片产品核心能力：`intelligence retrieval v2 + prompt/tag quality`。
  - 现有 `provider-backed intelligence`、`image_intelligence_terms`、`run ledger` 和单图 `intelligence` payload 会成为本轮设计的输入层。
- Key modules in this batch:
  - `IntelligenceTagSyncService`
  - `RetrievalRankingV2`
  - `PromptContextV2Builder`
  - `Search/Detail Explainability UI`
  - `Intelligence Sync Runbook`

## Backend Design

### Intelligence Tag Sync And Corpus Enrichment

- Problem with current design:
  - intelligence payload 已经存在，但标签、关键词和摘要还没有形成稳定的同步/规范化链路，AI 搜索消费的语料仍偏轻量。
- Proposed abstraction:
  - 新增或扩展 intelligence sync service，把 `labels / keywords / summary / prompt_hint` 规范成检索可用的 corpus。
  - 明确输出：
    - normalized terms
    - sync status / updated_at
    - visible intelligence-derived tags
    - 与 manual tags 的边界
- Design constraints:
  - 不直接污染用户手工标签。
  - 重跑和回填必须幂等。

### Retrieval Ranking V2

- Problem with current design:
  - 现有 AI 搜索更接近“关键词 + OCR + tags”的加权检索，不是真正基于 intelligence payload 的新排序。
- Proposed abstraction:
  - 在现有 `AiSearchService` 基础上引入 ranking v2，综合：
    - intelligence caption / summary
    - normalized labels / keywords
    - OCR
    - origin name
    - manual tags
  - 同时生成 explainability 片段，说明主要命中来源。
- Design constraints:
  - 先做 explainable scoring，不在本轮引入 embedding/vector。
  - 结果要能优先利用 intelligence，而不是简单把新字段拼进去。

### Prompt V2 Context Builder

- Problem with current design:
  - 现有 prompt context 仍偏向基础标签和简单方向提示，未充分利用 provider-backed intelligence 的 richer fields。
- Proposed abstraction:
  - 升级 prompt builder，输出更详细的：
    - subject / scene
    - composition
    - style / mood
    - possible use cases
    - fallback source
- Design constraints:
  - 降级时必须明确标明来自 placeholder、legacy 或缺失上下文。
  - 不能把 provider 原始字段不加约束地透传到前端。

## Frontend Design

### Advanced AI Search Explainability

- Goal:
  - 让 `/advanced` AI 搜索不只是返回结果列表，而是能解释“为什么命中”“这条结果用了哪些 intelligence 信号”。
- Proposed placement:
  - 搜索结果卡片或结果摘要区块展示：
    - top matched signals
    - intelligence sync status
    - ranking hints
- UX rule:
  - 优先提供高信号解释，不堆太多底层字段。

### Image Detail Prompt And Intelligence Explanation

- Goal:
  - 让单图详情可以解释 Prompt V2 的生成基础和 intelligence 来源。
- Proposed placement:
  - 单图 detail / preview 邻近区域展示：
    - latest intelligence source
    - prompt summary
    - tag sync 状态
    - fallback / degraded reason
- UX rule:
  - 明确区分 manual tags、intelligence-derived terms 和 prompt summary。

## Integration Design

- Execution:
  - `AnalyzeImageIntelligenceJob -> image_intelligence_records -> tag sync / corpus enrichment`
- Search:
  - `AiSearchService -> ranking v2 -> explainability payload -> /advanced`
- Prompt:
  - `ImagePromptContextBuilder -> Prompt V2 summary -> image detail / prompt consumer`
- Ops:
  - `scheduler / backfill command -> sync status -> operator-visible summary`

## Persistence Model

- Existing tables still used:
  - `image_intelligence_records`
  - `image_intelligence_terms`
  - `image_intelligence_runs`
  - `images`
  - `tags` / `image_tag`
- New schema preference in this batch:
  - 尽量优先复用现有 intelligence tables；仅在 sync state 无法承载时再考虑新增持久化字段或表。
- Deferred tables:
  - embedding / vector / semantic search tables 继续后延

## QA Strategy

- Automated:
  - retrieval ranking focused tests
  - Prompt V2 focused tests
  - intelligence tag sync / corpus enrichment tests
  - advanced/detail feature regression tests
- Manual:
  - rebuilt image 上的 AI 搜索与单图详情 UI 手测
  - settings/auth 的 UI 验收由用户承接，不作为本轮自动化阻塞
- Verification environment:
  - 继续使用重建后的 `lsky-pro-custom:php83`

## Security Design

- Intelligence retrieval:
  - explainability 不能泄漏不应暴露的 OCR/intelligence 原文片段
  - 排序和同步不得让 intelligence-derived tags 覆盖 manual tags 的产品语义
- Prompt V2:
  - 需要过滤或约束 provider 原始文本输出
- Auth carryover:
  - 本轮不继续扩 auth surface，但第八轮 governance contract 不能被破坏

## Intentional Non-Goals In This Batch

- 不在本轮完成 embedding / vector retrieval。
- 不在本轮恢复 browser-e2e 实验方案。
- 不在本轮重做整页图片工作台 UI。
- 不在本轮彻底移除 legacy identity snapshot。
