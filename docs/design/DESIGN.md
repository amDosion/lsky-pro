# Lsky Pro 技术设计文档（架构升级）

## 目标架构
将当前同步重路径改造为四层：
1. Upload Gateway（同步最小闭环）
2. Processing Orchestrator（异步编排）
3. Delivery Adapter（统一输出策略）
4. Admin Control Plane（配置/升级/观测/补偿）

## AS-IS 关键问题
- `ImageService::store` 过载，聚合鉴权、处理、存储、审核、缩略图。
- `QUEUE_CONNECTION=sync`，后处理无法削峰。
- 缺少关键复合索引，查询与限流成本随数据增大退化。
- 配置缓存全局 flush，变更影响面大。
- 升级机制缺签名与路径白名单。

## TO-BE 设计
### 1. 上传流水线
- 同步阶段仅做：鉴权、基础校验、摘要计算、原图写入、最小元数据落库、任务投递。
- 异步阶段处理：审核、水印、缩略图、回填状态。
- 新增状态：`pending_processing | ready | blocked | failed`。

### 2. 队列设计
- 连接：生产使用 Redis。
- 队列：`upload-critical`, `upload-scan`, `upload-thumb`, `upload-maintenance`。
- 幂等键：`image_id + task_type + process_version`。
- 失败策略：指数退避 + DLQ + 后台重放。

### 3. 缓存/会话
- cache/session 迁移到 Redis。
- 定向失效（不再全局 `Cache::flush()`）。
- 限流改用原子计数（INCR + EXPIRE）。

### 4. 数据库索引计划
- images: `(strategy_id, md5, sha1)`, `(user_id, created_at)`, `(uploaded_ip, created_at)`, `(album_id, created_at)`。
- group_strategy: 唯一索引 `(group_id, strategy_id)`。
- 采用在线 DDL 逐步发布。

### 5. 安全设计
- 升级：补丁签名校验 + 路径白名单 + dry-run + 原子切换。
- XSS：前端禁用不可信 `.html()`；服务端输出编码；CSP。
- AuthZ：引入 Policy/Gate；token scopes；管理员能力细分。

### 6. 可观测
- 统一 `trace_id/request_id`。
- 指标：上传成功率、时延、队列堆积、失败重试、存储错误率。
- 告警：上传成功率下降、队列 lag、failed_jobs 激增。

### 7. 灰度与回滚
- Feature Flags：
  - `upload_pipeline_async_enabled`
  - `upload_scan_async_enabled`
  - `thumbnail_async_enabled`
  - `upgrade_secure_mode_enabled`
- 分阶段发布（观测 -> 索引/redis -> 队列 -> 灰度 -> 全量）。
- 回滚：应用回滚 + flag 关闭 + 任务继续保留可恢复。

### 8. 变更记录（P1-T104：查询路径优化第一批）
- 变更日期：2026-03-04
- 变更范围：
  - 上传频控查询：`ImageService::rateLimiter()`
  - 用户图片列表：`User\\ImageController::images()`
  - API 图片列表：`Api\\V1\\ImageController::images()`
- 具体优化：
  - 频控阈值判断从 `count(*) >= limit` 改为 `offset(limit-1)->exists()`，只判断是否触达阈值，避免大窗口聚合。
  - 列表查询显式 `select` 必需字段，减少不必要列读取。
  - API/Web 列表补充按列关系预加载，避免 `links/url` 计算触发 N+1。
- 语义说明：
  - 不改变业务规则、过滤/排序逻辑、分页大小与响应语义。
