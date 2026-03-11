# LSky Advanced Requirements (Continuous Expansion)

## 1. 目标
在不破坏现有功能与 API v1 兼容性的前提下，持续迭代扩展 LSky 到“可企业化落地”的能力：安全、可靠、效率、协作、可观测、自动化。

## 2. 范围
- In Scope
  - Token 精细权限与生命周期治理
  - 审计日志与操作追踪
  - 安全下载与防盗链
  - 异步上传流水线（审核/缩略图/衍生图）
  - 生命周期策略（TTL/归档/回收）
  - 可观测与成本分析
  - Webhook 事件中心
  - 团队协作与空间隔离（分阶段）
- Out of Scope
  - 一次性重写全部模块
  - 破坏式替换现有上传协议

## 3. 约束
- 必须支持 Multi-agent + Non-interactive 连续执行。
- 每轮必须记录证据到 `docs/runbook/STATUS.md`。
- 默认非破坏；破坏性操作需显式门禁。

## 4. 优先级（P0/P1/P2）
- P0
  - AR-001 Token scope + 过期 + IP 白名单
  - AR-002 审计日志（管理端/API 关键写操作）
  - AR-003 安全下载链接（签名/时效）
  - AR-004 异步上传流水线第一期
  - AR-005 生命周期策略第一期
- P1
  - AR-006 批量操作 2.0（预览/回滚）
  - AR-007 Webhook 事件中心
  - AR-008 可观测看板与成本分析
  - AR-009 团队空间与协作权限
- P2
  - AR-010 智能检索（OCR/标签/语义）
  - AR-011 插件化扩展点

## 5. 验收标准
- 功能：对应任务全部 `DONE`。
- 质量：语法检查、核心冒烟、回归通过。
- 运营：有 runbook、有回滚、有监控指标。
- 安全：高危风险无新增，敏感链路审计可追溯。

## 6. AR-008 最小可用接口说明
- Endpoint: `GET /api/v1/stats/overview`
- Auth: `auth:sanctum` + token ability `analytics:read`
- 返回字段（MVP）：
  - 近 7/30 天上传量（含逐日序列）
  - 当前存储占用（KB/Bytes/人类可读）
  - 按策略分布（上传数、占用）
  - 按 mimetype 分布（上传数、占用）
  - 存储成本估算（基于配置单价）
- 成本配置项：
  - `storage_cost_per_gb_month`：每 GB 每月单价（默认 `0.12`）
  - `storage_cost_currency`：币种代码（默认 `CNY`）

## 7. AR-010 最小可用接口说明
- 数据结构：
  - `tags` 表（标签主表）
  - `image_tag` 表（图片-标签关联）
  - `images.ocr_text`（OCR 预留文本）
- 搜索入口：
  - `GET /api/v1/images`
  - `GET /api/v1/images/search`
- 搜索参数（MVP）：
  - `tag_keyword`：按标签关键字检索
  - `ocr_keyword`：按 OCR 文本关键字检索
  - `q`：跨 `origin_name/alias_name/tags/ocr_text` 统一关键字检索
- 上传占位 OCR：
  - 上传成功后异步派发占位任务，写入 `ocr_text`（当前不接入真实 OCR 引擎）
