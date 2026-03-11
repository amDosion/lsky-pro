# Lsky Pro v3.0 全面升级计划

> 编写日期：2026-03-08
> 范围：现有功能补全 + AI 能力增强 + 基础设施升级

---

## 一、现有功能完整度评估与补全计划

### 1.1 上传管线 (ImageService) — 当前 92%，目标 98%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| U-01 | 扫描结果仅支持 block/标记不健康，无审核队列 | 接入 ReviewController 审核流程，扫描命中时设为 `review_pending` 而非直接删除 | P0 |
| U-02 | 上传时无策略可用性校验 | 在 `store()` 前增加 `Strategy::testConnection()` 健康检查 | P1 |
| U-03 | 水印模块使用 Intervention Image v2 API | 升级到 v3 API，统一 resize/watermark 调用方式 | P2 |
| U-04 | `@getimagesize()` 错误抑制 | 改用 try-catch 包裹，错误写入日志 | P2 |
| U-05 | 缩略图生成失败无事务回滚 | 将缩略图生成纳入数据库事务，失败时回滚整条 Image 记录 | P1 |
| U-06 | 外部扫描服务无熔断机制 | 引入 Circuit Breaker 模式，连续失败 N 次后自动跳过扫描并告警 | P1 |

**涉及文件：**
- `app/Services/ImageService.php`
- `app/Services/ImageProcessing/`
- `config/convention.php`

---

### 1.2 OCR 文字识别 — 当前 5%（仅占位），目标 90%

**现状：** `ProcessImageOcrPlaceholderJob` 仅生成元数据拼接的假 OCR 文本，无实际文字提取。

**补全方案：**

```
阶段一：接入 Tesseract OCR (本地引擎)
├── 安装 tesseract-ocr 及中英文语言包
├── 新建 App\Services\Ocr\TesseractOcrDriver
│   ├── 实现 OcrDriverInterface::extract(string $imagePath): OcrResult
│   ├── 支持语言自动检测 (chi_sim, eng, jpn 等)
│   └── 返回结构化结果: text, confidence, language, regions
├── 替换 ProcessImageOcrPlaceholderJob → ProcessImageOcrJob
│   ├── 调用 OcrDriver 提取文本
│   ├── 置信度 < 阈值时标记为低质量
│   └── 文本写入 images.ocr_text (截断 10000 字符)
└── 配置: config/ocr.php (driver, language, confidence_threshold)

阶段二：接入云端 OCR (可选，高精度)
├── 新建 App\Services\Ocr\TencentOcrDriver (腾讯云 OCR)
├── 新建 App\Services\Ocr\AliyunOcrDriver (阿里云 OCR)
├── OcrManager 统一管理驱动切换
└── 支持 fallback: 云端失败自动降级本地

阶段三：高级文档理解
├── 支持 PDF/Office 文档 OCR (多页提取)
├── 版面分析 (表格、段落、标题识别)
└── 手写体识别
```

**新增文件：**
```
app/Contracts/OcrDriverInterface.php
app/Services/Ocr/OcrManager.php
app/Services/Ocr/TesseractOcrDriver.php
app/Services/Ocr/TencentOcrDriver.php
app/Services/Ocr/AliyunOcrDriver.php
app/Jobs/ProcessImageOcrJob.php (替代 Placeholder)
config/ocr.php
```

**数据库变更：**
```sql
ALTER TABLE images ADD COLUMN ocr_confidence DECIMAL(5,2) NULLABLE;
ALTER TABLE images ADD COLUMN ocr_language VARCHAR(20) NULLABLE;
ALTER TABLE images ADD COLUMN ocr_status ENUM('pending','processing','success','failed','skipped') DEFAULT 'pending';
```

---

### 1.3 批量操作 (ImageBatchOperationService) — 当前 85%，目标 95%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| B-01 | 仅支持批量删除，无其他操作 | 拓展支持批量移动相册、批量修改权限、批量打标签 | P1 |
| B-02 | 大批量无分页，可能超时 | 引入分块处理 `chunkById(500)`，进度回调 | P0 |
| B-03 | 无审计日志 | 记录操作人、操作类型、影响数量到 `audit_logs` 表 | P1 |
| B-04 | 预览硬编码 20 条限制 | 改为配置项 `batch.preview_limit` | P2 |

---

### 1.4 Webhook 系统 — 当前 88%，目标 95%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| W-01 | 无投递历史记录 | 新增 `webhook_deliveries` 表，记录每次投递的状态/响应/耗时 | P1 |
| W-02 | 无端点健康检查 | 连续失败 5 次自动禁用，通知管理员 | P1 |
| W-03 | 无死信队列 | 超过重试次数的事件写入 `webhook_dead_letters` 表 | P2 |
| W-04 | 事件类型固定 4 种 | 拓展事件：`user.registered`、`album.created`、`image.reviewed`、`space.member_added` | P1 |
| W-05 | 无载荷过滤 | 支持 webhook 订阅时指定字段过滤器 (JSONPath) | P3 |

**新增数据库表：**
```sql
CREATE TABLE webhook_deliveries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_id BIGINT NOT NULL,
    event VARCHAR(50) NOT NULL,
    event_id VARCHAR(36) NOT NULL,
    status ENUM('success','failed','timeout') NOT NULL,
    http_status INT NULLABLE,
    response_body TEXT NULLABLE,
    duration_ms INT NOT NULL,
    attempt INT DEFAULT 1,
    error_message TEXT NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscription_event (subscription_id, event),
    INDEX idx_event_id (event_id)
);
```

---

### 1.5 团队空间 (TeamSpaceService) — 当前 78%，目标 92%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| T-01 | 角色无权限执行校验 | 新增 `SpacePolicy` 授权策略，在 Controller 层 `$this->authorize()` | P0 |
| T-02 | 无成员邀请/移除流程 | 新增 `POST /spaces/{id}/invitations`、`DELETE /spaces/{id}/members/{userId}` | P0 |
| T-03 | 无空间级配额 | `team_spaces` 表增加 `capacity`、`use_capacity` 字段 | P1 |
| T-04 | 无操作审计 | 团队操作写入 `audit_logs`，支持按空间筛选 | P1 |
| T-05 | 无成员权限细粒度控制 | 拓展 `permissions` JSON 字段：upload、delete、manage_album、invite_member 等 | P1 |

---

### 1.6 图片处理引擎 — 当前 82%，目标 92%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| P-01 | 无驱动自动选择 | 检测可用驱动，优先 libvips (低内存)，fallback imagick | P1 |
| P-02 | 无 ICC 色彩管理 | 处理时保留 ICC Profile，输出时支持 sRGB 转换 | P2 |
| P-03 | 批量处理无限流 | 增加并发限制 `processing.max_concurrent`，超出排队 | P1 |
| P-04 | 无处理结果缓存 | 相同参数+相同图片的处理结果缓存，避免重复计算 | P2 |
| P-05 | 模板定义无 Schema 校验 | 增加 JSON Schema 验证器，在模板保存时校验 | P1 |

---

### 1.7 插件钩子系统 (HookManager) — 当前 75%，目标 90%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| H-01 | 仅 4 个硬编码事件 | 拓展为动态事件注册：`HookManager::register($event, $handler, $priority)` | P1 |
| H-02 | 无优先级排序 | 插件支持 priority 属性，按优先级顺序执行 | P2 |
| H-03 | 无返回值处理 | 支持 pipeline 模式：前一个插件的输出作为下一个的输入 | P2 |
| H-04 | 无插件启用/禁用 | 增加 `plugins` 数据库表，支持后台开关 | P1 |

---

### 1.8 API 路由完整性 — 当前 85%，目标 98%

| 编号 | 问题 | 方案 | 优先级 |
|------|------|------|--------|
| A-01 | AI Prompt 异步接口未暴露在 API v1 路由 | 将 `AiPromptTaskController` 路由注册到 `routes/api.php` | P0 |
| A-02 | 缺少统一错误响应格式 | 新增 `ApiExceptionHandler`，统一返回 `{code, message, data}` | P1 |
| A-03 | 无 API 版本管理 | 规范 v1/v2 前缀，废弃标记，Sunset Header | P2 |
| A-04 | 缺少批量创建相册 API | 新增 `POST /api/v1/albums/batch` | P2 |

---

### 1.9 测试覆盖率 — 当前 2.5%，目标 60%

**测试计划：**

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── ImageServiceTest.php          # 上传验证、去重、扫描决策
│   │   ├── ImageBatchOperationTest.php    # 批量删除、回滚
│   │   ├── WebhookEventServiceTest.php   # 事件分发、签名生成
│   │   ├── TeamSpaceServiceTest.php      # 空间创建、成员管理
│   │   ├── SignedUrlServiceTest.php      # 签名生成、过期校验
│   │   ├── OcrManagerTest.php            # OCR 驱动调度
│   │   └── AiPromptServiceTest.php       # 提示词构建
│   ├── Models/
│   │   ├── ImageTest.php                 # 模型关系、Scope、生命周期
│   │   ├── UserTest.php                  # 容量计算、默认组
│   │   └── StrategyTest.php              # 驱动配置验证
│   └── Jobs/
│       ├── DeliverWebhookEventJobTest.php
│       ├── ProcessImageOcrJobTest.php
│       └── RunImageProcessTemplateJobTest.php
├── Feature/
│   ├── Api/
│   │   ├── UploadTest.php                # 完整上传流程
│   │   ├── ImageSearchTest.php           # 普通搜索+AI搜索
│   │   ├── BatchOperationTest.php        # 批量操作端到端
│   │   ├── WebhookTest.php               # 订阅CRUD+投递
│   │   ├── TeamSpaceTest.php             # 空间管理全流程
│   │   ├── AiPromptTest.php              # 同步+异步提示词
│   │   └── ImageProcessingTest.php       # 处理模板+作业
│   ├── Admin/
│   │   ├── ImageManagementTest.php       # 管理搜索+审核
│   │   └── UserManagementTest.php
│   └── Auth/
│       ├── TokenTest.php                 # Token 能力+IP 白名单+过期
│       └── SocialAuthTest.php
└── Integration/
    ├── StorageDriverTest.php             # 各存储驱动连通性
    └── QueueProcessingTest.php           # 队列任务端到端
```

---

## 二、AI 能力增强计划

### 2.1 AI 提示词系统 — 当前 95%，目标 100% + 增强

**现状评估：**
- 同步 API (`POST /api/v1/ai/prompt`)：✅ 完整可用
- 异步任务 (AiPromptTaskController)：⚠️ 功能完整但路由未暴露到 API v1
- 模板引擎：✅ 支持变量替换 (`{{intent}}`、`{{language}}`、`{{style}}`、`{{metadata_block}}`)
- 图片元数据提取：✅ 包含标签、OCR 文本、尺寸等
- 无外部 LLM 接入：提示词为本地模板拼接，非 AI 生成

**补全与增强方案：**

```
Phase 1: 路由与基础补全 (1 周)
├── [AI-P-01] 将异步 prompt-tasks 路由注册到 routes/api.php
│   ├── POST /api/v1/ai/prompt-tasks       (创建异步任务)
│   └── GET  /api/v1/ai/prompt-tasks/{id}  (查询任务状态)
├── [AI-P-02] 新增批量提示词生成
│   ├── POST /api/v1/ai/prompt/batch
│   ├── 接收 image_keys[] 数组 (最多 50 张)
│   ├── 返回 batch_task_id
│   └── 各图片并行生成，统一返回结果
├── [AI-P-03] 提示词历史缓存
│   ├── 相同 image_key + intent + template 组合缓存结果
│   ├── 缓存 TTL 可配置 (默认 24h)
│   └── 图片更新/标签变更时自动失效
└── [AI-P-04] 提示词质量评分
    ├── 根据元数据丰富度计算质量分 (0-100)
    │   ├── 有 OCR 文本 +20
    │   ├── 有标签 +30 (>5 个标签额外 +10)
    │   ├── 有尺寸信息 +10
    │   ├── 有 MIME 类型 +5
    │   └── 有 AI 描述 +25
    └── 低分时 API 返回 suggestions 字段建议补充标签/描述

Phase 2: LLM 接入 — 真正的 AI 提示词 (2 周)
├── [AI-P-05] 新增 LLM Provider 抽象层
│   ├── App\Contracts\LlmProviderInterface
│   │   ├── chat(messages[], options): string
│   │   ├── analyzeImage(imagePath, prompt): string
│   │   └── embed(text): float[]
│   ├── App\Services\Llm\LlmManager (驱动管理器)
│   │   ├── driver(name?): LlmProviderInterface
│   │   ├── 默认驱动从 config 读取
│   │   └── 支持运行时切换
│   ├── 驱动实现：
│   │   ├── ClaudeDriver   — Anthropic Claude API (推荐)
│   │   │   ├── Messages API + Vision 能力
│   │   │   ├── 支持 claude-sonnet-4-6 / claude-haiku-4-5
│   │   │   └── 图片 base64 编码传入
│   │   ├── OpenAiDriver   — OpenAI GPT-4o
│   │   │   ├── Chat Completions + Vision
│   │   │   └── 支持 gpt-4o / gpt-4o-mini
│   │   ├── QwenDriver     — 通义千问 VL
│   │   │   └── 适合国内用户，无需翻墙
│   │   └── OllamaDriver   — 本地模型 (Llava/Bakllava)
│   │       ├── 零成本，完全离线
│   │       └── 适合隐私敏感场景
│   └── config/llm.php
│       ├── default: env('LLM_DRIVER', 'claude')
│       ├── drivers.claude: { api_key, model, max_tokens, temperature }
│       ├── drivers.openai: { api_key, model, max_tokens }
│       ├── drivers.qwen: { api_key, model }
│       ├── drivers.ollama: { host, model }
│       ├── rate_limit: { requests_per_minute, daily_budget }
│       └── cache: { enabled, ttl }
├── [AI-P-06] LLM 增强提示词生成
│   ├── 将图片 + 元数据 + 用户意图发送给视觉模型
│   ├── System Prompt 指导模型输出结构化提示词：
│   │   ├── positive_prompt (正向提示词)
│   │   ├── negative_prompt (反向提示词)
│   │   ├── suggested_params { steps, cfg_scale, sampler, seed }
│   │   └── style_tags []
│   ├── 支持多风格预设：写实、动漫、油画、水彩、像素、3D
│   └── 输出同时包含中英双语提示词
└── [AI-P-07] 提示词模板管理
    ├── 内置专业模板 (人像、风景、产品、建筑、美食、抽象)
    ├── 用户自定义模板 CRUD
    │   ├── POST   /api/v1/ai/templates
    │   ├── GET    /api/v1/ai/templates
    │   ├── PUT    /api/v1/ai/templates/{id}
    │   └── DELETE /api/v1/ai/templates/{id}
    └── 团队空间内模板共享

Phase 3: 高级提示词功能 (2 周)
├── [AI-P-08] 图片风格迁移提示词
│   ├── 输入: 源图片 key + 目标风格参考图 key
│   ├── LLM 分析两张图片的风格差异
│   └── 输出: 将源图转换为目标风格的提示词
├── [AI-P-09] 提示词反向推理 (img2prompt)
│   ├── 输入: AI 生成的图片 key
│   ├── LLM 逆向分析推测生成参数
│   └── 输出: 推测的提示词 + Stable Diffusion / Midjourney 参数
└── [AI-P-10] 多模型提示词对比
    ├── POST /api/v1/ai/prompt/compare
    ├── 同一图片同时发送 2-3 个 LLM 模型
    ├── 并行生成不同风格提示词
    └── 返回对比结果供用户选择
```

**新增文件：**
```
app/Contracts/LlmProviderInterface.php
app/Services/Llm/LlmManager.php
app/Services/Llm/Drivers/ClaudeDriver.php
app/Services/Llm/Drivers/OpenAiDriver.php
app/Services/Llm/Drivers/QwenDriver.php
app/Services/Llm/Drivers/OllamaDriver.php
config/llm.php
database/migrations/xxxx_create_ai_prompt_templates_table.php
database/migrations/xxxx_add_llm_fields_to_ai_prompt_tasks.php
```

**ai_prompt_tasks 表新增字段：**
```sql
ALTER TABLE ai_prompt_tasks ADD COLUMN llm_driver VARCHAR(30) NULLABLE;
ALTER TABLE ai_prompt_tasks ADD COLUMN llm_model VARCHAR(50) NULLABLE;
ALTER TABLE ai_prompt_tasks ADD COLUMN llm_tokens_used INT NULLABLE;
ALTER TABLE ai_prompt_tasks ADD COLUMN quality_score INT NULLABLE;
ALTER TABLE ai_prompt_tasks ADD COLUMN cached BOOLEAN DEFAULT FALSE;
```

---

### 2.2 AI 智能检索 — 当前 SQL 评分，目标真正语义搜索

**现状评估：**
- 当前 "AI 搜索" 实际是 SQL `LIKE` + 硬编码权重评分，无真正 AI 参与
- 评分权重：文件名精确 120 / 文件名包含 60 / OCR 文本 35 / 标签 40
- 无语义理解、无向量检索、无图片相似度搜索
- 搜索仅支持精确文本匹配

**升级方案：**

```
Phase 1: 搜索基础增强 (1 周)
├── [AI-S-01] 搜索评分可配置化
│   ├── config/search.php 定义各字段权重
│   ├── 管理后台可调整
│   └── 不同场景 (图库/文档/设计稿) 使用不同权重预设
├── [AI-S-02] 全文搜索引擎接入
│   ├── 方案 A: MySQL FULLTEXT 索引 (零依赖)
│   │   ├── images.origin_name + images.ocr_text 建立 FULLTEXT 索引
│   │   └── 使用 MATCH...AGAINST 替代 LIKE
│   ├── 方案 B: Meilisearch (推荐，Laravel Scout 集成)
│   │   ├── composer require meilisearch/meilisearch-php
│   │   ├── Image 模型实现 Searchable Trait
│   │   ├── 索引字段: origin_name, alias_name, ocr_text, tags, ai_description
│   │   └── 自动同步：图片创建/更新/删除时自动索引
│   └── 支持中文分词
├── [AI-S-03] 模糊搜索与纠错
│   ├── Meilisearch 内置 typo-tolerance
│   ├── 拼音搜索 (中文场景): "fengjing" → "风景"
│   └── 同义词配置: { "照片": ["图片", "相片", "photo"] }
└── [AI-S-04] 搜索建议 (Auto-complete)
    ├── GET /api/v1/images/search/suggestions?q=xxx
    ├── 基于标签频率的热词推荐
    ├── 基于用户搜索历史的个性化建议
    └── 返回 top 10 建议词

Phase 2: 向量语义搜索 (3 周)
├── [AI-S-05] 图片 Embedding 生成
│   ├── 新增 App\Services\Embedding\EmbeddingManager
│   │   ├── driver(): EmbeddingDriverInterface
│   │   └── generateForImage(Image $image): void
│   ├── 驱动实现:
│   │   ├── ClipDriver — OpenAI CLIP 模型 (图文跨模态)
│   │   │   ├── 图片 → 512 维向量
│   │   │   ├── 文本 → 512 维向量 (同一空间)
│   │   │   └── 支持本地 ONNX 推理 / API 调用
│   │   ├── OpenAiDriver — text-embedding-3-small
│   │   │   ├── 文本 → 1536 维向量
│   │   │   └── 仅文本检索，不支持以图搜图
│   │   └── OllamaDriver — nomic-embed-text / llava
│   │       └── 完全本地化
│   ├── 新增 Job: GenerateImageEmbeddingJob
│   │   ├── 上传时自动触发
│   │   ├── 生成图片 Embedding + 文本 Embedding (OCR+标签)
│   │   └── 写入 image_embeddings 表
│   └── 新增 Command: lsky:generate-embeddings
│       ├── 批量为历史图片生成 Embedding
│       └── 支持 --chunk-size, --driver 参数
├── [AI-S-06] 向量数据库集成
│   ├── 方案 A (推荐): pgvector (PostgreSQL 扩展)
│   │   ├── 零额外部署，项目已支持 PostgreSQL
│   │   ├── CREATE EXTENSION vector;
│   │   ├── 支持 L2 / cosine / inner product 距离
│   │   └── IVFFlat 索引支持百万级数据
│   ├── 方案 B: Qdrant (独立部署)
│   │   ├── 千万级数据高性能
│   │   ├── Docker 一键部署
│   │   └── REST API + gRPC
│   ├── 方案 C: SQLite + sqlite-vss (轻量级)
│   │   └── 适合小规模部署
│   └── config/vector.php
│       ├── driver: env('VECTOR_DRIVER', 'pgvector')
│       ├── dimension: 512
│       └── distance_metric: 'cosine'
├── [AI-S-07] 语义搜索 API
│   ├── GET /api/v1/images/semantic-search?q=日落下的城市天际线
│   ├── 流程:
│   │   ├── 1. 用户文本 → Text Embedding
│   │   ├── 2. 向量数据库 cosine 相似度检索 Top-K
│   │   ├── 3. 合并关键词评分 (hybrid search)
│   │   └── 4. 按综合分数排序返回
│   ├── 参数: q, limit, threshold (最低相似度), hybrid_weight
│   └── 返回: images[] + similarity_score
└── [AI-S-08] 以图搜图
    ├── POST /api/v1/images/visual-search
    │   ├── 方式一: 上传图片文件搜索相似图
    │   └── 方式二: 传入已有图片 key 搜索相似图
    ├── 流程:
    │   ├── 1. 输入图片 → Image Embedding (CLIP)
    │   ├── 2. 向量数据库检索最近邻
    │   └── 3. 按相似度排序返回
    ├── 参数: image/image_key, limit, threshold, exclude_self
    └── 返回: images[] + similarity_score

Phase 3: 智能搜索增强 (2 周)
├── [AI-S-09] 自然语言查询理解 (NLQ)
│   ├── 用户输入: "上周上传的大于 5MB 的风景照片"
│   ├── LLM 解析为结构化查询:
│   │   {
│   │     time_range: { from: "2026-03-01", to: "2026-03-07" },
│   │     size: { min: 5242880 },
│   │     semantic_query: "风景",
│   │     filters: []
│   │   }
│   ├── 组合: 数据库条件过滤 + 语义搜索
│   └── GET /api/v1/images/smart-search?q=自然语言
├── [AI-S-10] 搜索结果聚类
│   ├── 搜索结果按 Embedding 相似度 K-means 聚类
│   ├── 每组显示代表图片 + 数量 + 自动命名
│   ├── GET /api/v1/images/search/clusters?q=xxx
│   └── 支持展开查看组内全部
└── [AI-S-11] 个性化推荐
    ├── 基于用户上传/浏览/搜索行为建模
    ├── 计算用户偏好向量 (标签频率 + Embedding 均值)
    ├── GET /api/v1/images/recommendations
    └── 推荐算法: 内容相似 + 协同过滤
```

**新增数据库表：**
```sql
-- 向量存储 (pgvector 方案)
CREATE TABLE image_embeddings (
    id BIGSERIAL PRIMARY KEY,
    image_id BIGINT NOT NULL REFERENCES images(id) ON DELETE CASCADE,
    model VARCHAR(50) NOT NULL,
    vector_type VARCHAR(10) NOT NULL, -- 'image' or 'text'
    embedding vector(512) NOT NULL,   -- pgvector 类型
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (image_id, model, vector_type)
);
CREATE INDEX idx_embedding_image ON image_embeddings USING ivfflat (embedding vector_cosine_ops);

-- 搜索历史
CREATE TABLE search_history (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    query VARCHAR(255) NOT NULL,
    query_type VARCHAR(20) NOT NULL, -- keyword/semantic/visual/nlq
    results_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    INDEX idx_user_time (user_id, created_at)
);
```

**新增文件：**
```
app/Contracts/EmbeddingDriverInterface.php
app/Services/Embedding/EmbeddingManager.php
app/Services/Embedding/Drivers/ClipDriver.php
app/Services/Embedding/Drivers/OpenAiDriver.php
app/Services/Embedding/Drivers/OllamaDriver.php
app/Jobs/GenerateImageEmbeddingJob.php
app/Console/Commands/GenerateEmbeddingsCommand.php
config/vector.php
config/search.php
```

---

### 2.3 AI 自动标签 & 图片理解

```
Phase 1: 自动标签生成 (2 周)
├── [AI-T-01] 上传时自动生成标签
│   ├── 新增 Job: AutoTagImageJob
│   │   ├── 调用 LlmManager::analyzeImage()
│   │   ├── System Prompt: "分析这张图片，返回 JSON 格式标签列表"
│   │   ├── 返回结构: { tags: [{name, confidence, category}] }
│   │   └── category: object/scene/color/emotion/style/action
│   ├── 在 ImageService::store() 中 afterCommit 触发
│   ├── 生成的标签自动写入 tags + image_tag 表
│   ├── 支持中英双语标签
│   └── config 控制: auto_tag.enabled, auto_tag.max_tags, auto_tag.min_confidence
├── [AI-T-02] 标签置信度与人工校正
│   ├── image_tag pivot 增加字段:
│   │   ├── confidence DECIMAL(5,2)
│   │   ├── source ENUM('manual','ai','ocr')
│   │   └── verified BOOLEAN DEFAULT FALSE
│   ├── 低置信度 (<0.6) 标签在 UI 上标记为 "待确认"
│   ├── 用户确认: PATCH /api/v1/images/{key}/tags/{tagId}/verify
│   └── 用户删除 AI 标签时记录负反馈
├── [AI-T-03] 图片描述生成 (AI Caption)
│   ├── images 表增加 ai_description TEXT 字段
│   ├── 新增 Job: GenerateImageDescriptionJob
│   ├── LLM 生成一段 50-200 字的自然语言描述
│   ├── 用途: SEO alt 文本 / 无障碍 / 搜索索引
│   ├── 支持多语言: 跟随系统语言或用户设置
│   └── API: GET /api/v1/images/{key} 返回中包含 ai_description
└── [AI-T-04] 智能分类 (Auto Category)
    ├── images 表增加 ai_category VARCHAR(50) 字段
    ├── 预设分类:
    │   ├── screenshot (截图)
    │   ├── photograph (照片)
    │   ├── illustration (插画/绘图)
    │   ├── document (文档/扫描件)
    │   ├── design (设计稿/UI)
    │   ├── meme (表情包/梗图)
    │   └── other (其他)
    ├── 上传时 LLM 自动分类
    └── 支持自定义分类规则 (管理后台配置)

Phase 2: 高级图片理解 (2 周)
├── [AI-T-05] 人脸检测 (可选模块)
│   ├── 检测图片中的人脸数量和位置坐标
│   ├── 支持人脸模糊/马赛克 (隐私保护功能)
│   ├── 智能裁剪时以人脸为焦点
│   ├── 使用 OpenCV / dlib / 云 API
│   └── images 表增加 face_count INT, face_regions JSON
├── [AI-T-06] 敏感内容细粒度分级
│   ├── 超越现有 block/safe 二分法
│   ├── 分级: safe / suggestive / explicit / violence / hate / self-harm
│   ├── 每级可配置处理策略:
│   │   ├── allow (放行)
│   │   ├── review (进入审核队列)
│   │   ├── blur (模糊显示 + 警告)
│   │   └── reject (拒绝上传)
│   ├── 结合现有腾讯/阿里云扫描 + LLM 二次确认
│   └── images 表增加 safety_level VARCHAR, safety_scores JSON
└── [AI-T-07] 文档智能解析
    ├── 识别图片中的表格 → 输出 CSV/JSON
    ├── 识别图片中的数学公式 → 输出 LaTeX
    ├── 识别图片中的代码块 → 输出格式化文本
    └── API: POST /api/v1/images/{key}/extract?type=table|formula|code
```

**数据库变更汇总：**
```sql
-- images 表新增字段
ALTER TABLE images ADD COLUMN ai_description TEXT NULLABLE;
ALTER TABLE images ADD COLUMN ai_category VARCHAR(50) NULLABLE;
ALTER TABLE images ADD COLUMN face_count INT NULLABLE;
ALTER TABLE images ADD COLUMN face_regions JSON NULLABLE;
ALTER TABLE images ADD COLUMN safety_level VARCHAR(20) NULLABLE;
ALTER TABLE images ADD COLUMN safety_scores JSON NULLABLE;

-- image_tag pivot 表增强
ALTER TABLE image_tag ADD COLUMN confidence DECIMAL(5,2) NULLABLE;
ALTER TABLE image_tag ADD COLUMN source ENUM('manual','ai','ocr') DEFAULT 'manual';
ALTER TABLE image_tag ADD COLUMN verified BOOLEAN DEFAULT FALSE;
```

---

## 三、基础设施升级计划

### 3.1 审计日志系统 (全新)

```
目标: 记录所有关键操作，满足企业合规需求

实现方案:
├── 新增 Model: AuditLog
│   ├── actor_id, actor_type (user/system/webhook)
│   ├── action (create/update/delete/login/export/batch_delete/...)
│   ├── target_type, target_id (多态关联)
│   ├── diff JSON (变更前后对比)
│   ├── ip_address, user_agent
│   ├── space_id (团队空间隔离)
│   └── created_at
├── 新增 Trait: HasAuditLog
│   ├── 自动监听 Model created/updated/deleted 事件
│   ├── 自动计算 diff (getDirty vs getOriginal)
│   ├── 异步写入 (队列) 不影响主流程性能
│   └── 应用到: Image, Album, User, Strategy, Group, TeamSpace
├── 新增 Service: AuditService
│   ├── log(actor, action, target, diff, context)
│   ├── query(filters) — 支持按时间/操作人/类型/目标筛选
│   ├── export(format, filters) — CSV/JSON 导出
│   └── cleanup(days) — 清理过期日志
├── API 端点:
│   ├── GET /api/v1/admin/audit-logs (管理员全局)
│   ├── GET /api/v1/spaces/{id}/audit-logs (空间管理员)
│   └── GET /api/v1/audit-logs/export (导出)
└── 管理后台: 审计日志查询/筛选/导出页面
```

**数据库表：**
```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    actor_id BIGINT NULLABLE,
    actor_type VARCHAR(20) NOT NULL DEFAULT 'user',
    action VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NULLABLE,
    target_id BIGINT NULLABLE,
    diff JSON NULLABLE,
    ip_address VARCHAR(45) NULLABLE,
    user_agent VARCHAR(500) NULLABLE,
    space_id BIGINT NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor (actor_id, actor_type),
    INDEX idx_target (target_type, target_id),
    INDEX idx_action_time (action, created_at),
    INDEX idx_space (space_id, created_at)
);
```

---

### 3.2 存储生命周期管理

```
├── [SL-01] 软删除自动清理
│   ├── 新增 Command: lsky:cleanup-soft-deletes
│   │   ├── 清理 N 天前的软删除 Image 记录 (默认 30 天)
│   │   ├── 同时删除物理文件 (检查 hash 引用计数)
│   │   └── 生成清理报告: 删除数量、释放空间
│   └── 注册到 Console Kernel: $schedule->command('lsky:cleanup-soft-deletes')->daily()
├── [SL-02] 孤立临时文件清理
│   ├── 新增 Command: lsky:cleanup-temp-files
│   │   ├── 清理 upload-tasks/ 中超过 24h 的临时文件
│   │   ├── 清理 failed 状态超过 7 天的 UploadTask 记录
│   │   └── 清理 processing 状态超过 1h 的卡住任务 (重置为 failed)
│   └── 注册到 Kernel: hourly
├── [SL-03] 过期图片自动处理
│   ├── 现有 expire_at 字段已有但无处理逻辑
│   ├── 新增 Command: lsky:expire-images
│   │   ├── 查询 expire_at < now() 的图片
│   │   ├── 执行软删除
│   │   └── 可选: 到期前 N 天邮件提醒用户
│   └── 注册到 Kernel: hourly
├── [SL-04] 冷热数据分层 (高级)
│   ├── Strategy 增加 storage_tier ENUM('hot','warm','cold')
│   ├── 新增 Command: lsky:migrate-cold-data
│   │   ├── 超过 N 天未访问的图片迁移到冷存储策略
│   │   ├── 原路径保留重定向记录
│   │   └── 访问时自动回迁 (lazy migration)
│   └── images 表增加 last_accessed_at, storage_migrated_at
└── [SL-05] 存储用量监控与告警
    ├── 新增 Command: lsky:storage-stats
    ├── 按策略/用户/空间统计用量趋势
    ├── 超额阈值告警 (80%/90%/95%)
    └── API: GET /api/v1/stats/storage (已有基础，增强)
```

---

### 3.3 API 网关与限流

```
├── [AG-01] 细粒度 API 限流
│   ├── 新增 Middleware: ApiRateLimiter
│   ├── 层级限流:
│   │   ├── 全局: 1000 req/min
│   │   ├── 按 Token: 60 req/min (可配置)
│   │   ├── 按端点: upload 10 req/min, read 120 req/min
│   │   └── 按 Group: 管理员无限制
│   ├── 超限返回 429 + X-RateLimit-* Headers
│   └── 基于 Redis 滑动窗口算法
├── [AG-02] API 用量统计
│   ├── 新增 Middleware: ApiUsageRecorder (异步记录)
│   ├── 记录: token_id, endpoint, method, status, bytes_in, bytes_out, duration_ms
│   ├── 批量写入 (每 100 条或每 10 秒)
│   └── API: GET /api/v1/stats/api-usage?period=day|week|month
└── [AG-03] 请求响应标准化
    ├── 新增 Trait: ApiResponseTrait
    ├── 统一成功: { status: "success", data: {...}, meta: {...} }
    ├── 统一错误: { status: "error", code: "VALIDATION_ERROR", message: "..." }
    ├── 统一分页: { data: [...], meta: { current_page, per_page, total, last_page } }
    └── 新增 ExceptionHandler 统一捕获 → 标准格式
```

---

## 四、实施路线图

### 第一阶段：基础补全与关键修复 (第 1-3 周)

```
Week 1: P0 关键修复
├── [A-01] AI Prompt 异步路由注册到 API v1         ── 半天
├── [T-01] 团队空间权限校验 SpacePolicy              ── 2 天
├── [T-02] 成员邀请/移除 API                         ── 1 天
├── [U-01] 扫描结果接入审核流程                       ── 1 天
└── [B-02] 批量操作分块处理                           ── 半天

Week 2: OCR 引擎 + 审计
├── OCR 驱动抽象层 + Tesseract 实现                  ── 3 天
├── ProcessImageOcrJob 替换占位逻辑                  ── 1 天
└── 审计日志 Model + Trait + Service                 ── 1 天

Week 3: 生命周期 + Webhook 增强
├── 软删除清理 + 临时文件清理 + 过期图片处理 Command  ── 2 天
├── Webhook 投递历史表 + 健康检查                    ── 1 天
├── 扫描服务熔断机制                                 ── 1 天
└── API 响应格式标准化                               ── 1 天
```

### 第二阶段：AI 能力建设 (第 4-7 周)

```
Week 4: LLM 抽象层
├── LlmProviderInterface + LlmManager               ── 1 天
├── Claude Driver 实现 + 测试                        ── 1 天
├── OpenAI Driver 实现                               ── 1 天
├── 通义千问 Driver 实现                              ── 1 天
└── Ollama Driver 实现                               ── 1 天

Week 5: AI 标签 & 描述 & 提示词增强
├── AutoTagImageJob + 上传流程集成                   ── 2 天
├── AI 描述生成 + ai_description 字段                ── 1 天
├── LLM 增强提示词生成 (替换模板拼接)                ── 1 天
└── 提示词质量评分 + 批量生成                        ── 1 天

Week 6: 向量搜索引擎
├── Embedding 驱动层 + CLIP 集成                     ── 2 天
├── pgvector 集成 + 迁移                             ── 1 天
├── GenerateImageEmbeddingJob + 上传集成             ── 1 天
└── 历史图片批量 Embedding Command                   ── 1 天

Week 7: 搜索 API 实现
├── 语义搜索 API                                     ── 1 天
├── 以图搜图 API                                     ── 2 天
├── 自然语言查询理解 (NLQ)                           ── 1 天
└── 搜索建议 + 搜索历史                              ── 1 天
```

### 第三阶段：体验优化与稳定性 (第 8-10 周)

```
Week 8: API 治理 + 高级 AI
├── 细粒度限流 Middleware + Redis                    ── 2 天
├── API 用量统计                                     ── 1 天
├── 敏感内容细粒度分级                               ── 1 天
└── 图片智能分类                                     ── 1 天

Week 9: 测试补全
├── Unit Tests — Services 层 (8 个 Service)          ── 3 天
├── Feature Tests — API 端到端 (7 个模块)            ── 2 天
└── 目标: 覆盖率 ≥ 60%

Week 10: 收尾
├── 图片处理引擎优化 (驱动自动选择、限流)              ── 1 天
├── 提示词模板管理 CRUD API                          ── 1 天
├── 插件系统增强 (动态事件、优先级)                    ── 1 天
├── OpenAPI/Swagger 文档生成                          ── 1 天
└── 性能压力测试                                     ── 1 天
```

---

## 五、技术选型建议

| 组件 | 推荐方案 | 备选方案 | 理由 |
|------|---------|---------|------|
| LLM 服务 | Claude API (claude-sonnet-4-6) | OpenAI GPT-4o | 视觉理解强，性价比高 |
| 图片 Embedding | CLIP ViT-B/32 (本地) | OpenAI Embeddings | 开源免费，图文跨模态 |
| 向量数据库 | pgvector (PostgreSQL 扩展) | Qdrant | 零额外部署，项目已支持 PG |
| 全文搜索 | Meilisearch | Elasticsearch | 轻量、中文友好、Scout 集成 |
| OCR 引擎 | Tesseract + chi_sim | 腾讯云 OCR | 本地运行零成本，精度可接受 |
| 本地 LLM | Ollama + Llava | vLLM | 离线场景，隐私敏感，零 API 费用 |
| 缓存/限流 | Redis | 文件缓存 | 已有支持，滑动窗口限流必需 |

---

## 六、新增配置文件规划

```
config/
├── llm.php              # LLM 服务 (driver, api_key, model, rate_limit, cache)
├── ocr.php              # OCR (driver, language, confidence_threshold, max_size)
├── vector.php           # 向量数据库 (driver, dimension, distance_metric, index_type)
├── search.php           # 搜索 (weights, fulltext_driver, suggestions, typo_tolerance)
├── audit.php            # 审计 (enabled, retention_days, async, excluded_actions)
├── auto_tag.php         # 自动标签 (enabled, max_tags, min_confidence, languages)
├── lifecycle.php        # 已有 → 扩充: soft_delete_retention, temp_cleanup, cold_migration
├── image_processing.php # 已有 → 扩充: driver_auto_select, max_concurrent, cache
└── queue.php            # 已有 → 新增队列组: ocr, embedding, auto_tag, audit
```

---

## 七、数据库迁移清单

| 迁移文件 | 操作 | 阶段 |
|----------|------|------|
| `add_ocr_status_fields_to_images` | images 增加 ocr_confidence, ocr_language, ocr_status | Phase 1 |
| `create_audit_logs_table` | 审计日志表 | Phase 1 |
| `create_webhook_deliveries_table` | Webhook 投递历史 | Phase 1 |
| `add_space_capacity_to_team_spaces` | team_spaces 增加 capacity, use_capacity | Phase 1 |
| `add_llm_fields_to_ai_prompt_tasks` | ai_prompt_tasks 增加 llm_driver, llm_model 等 | Phase 2 |
| `add_ai_fields_to_images` | images 增加 ai_description, ai_category, safety_level 等 | Phase 2 |
| `enhance_image_tag_pivot` | image_tag 增加 confidence, source, verified | Phase 2 |
| `create_image_embeddings_table` | 向量存储表 (pgvector) | Phase 2 |
| `create_search_history_table` | 搜索历史 | Phase 2 |
| `create_ai_prompt_templates_user_table` | 用户自定义提示词模板 | Phase 2 |
| `add_lifecycle_fields_to_images` | images 增加 last_accessed_at, storage_migrated_at | Phase 3 |

---

## 八、风险与应对

| 风险 | 影响 | 应对措施 |
|------|------|---------|
| LLM API 成本失控 | 大量自动标签/描述导致高额费用 | 日用量上限 (`llm.rate_limit.daily_budget`)；Ollama 本地模型兜底；缓存避免重复调用 |
| 向量检索性能瓶颈 | 百万图片向量检索延迟高 | pgvector IVFFlat 索引；超 500 万迁移 Qdrant；查询缓存 |
| OCR 队列积压 | 大量上传时 OCR 阻塞 | 独立 queue worker；优先级低于上传；支持跳过非图片类型 |
| LLM 服务不可用 | AI 标签/搜索功能不可用 | 所有 AI 功能 feature flag 可关闭；降级到传统搜索；熔断自动切换 |
| 数据库迁移冲突 | 多字段变更导致回滚困难 | 每阶段独立迁移文件；CI 测试 migrate + rollback；生产前备份 |
| 存储迁移中断 | 冷热分层迁移时文件丢失 | 迁移前复制而非移动；验证后再删源；失败自动重试 |
| Embedding 维度不一致 | 切换模型导致历史向量失效 | 按 model 字段隔离；支持多模型共存；提供重建 Command |
