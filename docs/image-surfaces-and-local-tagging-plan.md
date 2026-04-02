# 图片三视图统一与本地打标替换执行文档

日期：2026-03-31  
状态：待执行  
负责人：Codex 主代理

## 1. 背景与目标

当前项目存在四类问题，需要在 **不丢功能、不改当前样式语言** 的前提下完成修复和重构：

1. 用户图片管理、画廊、后台图片管理三处视图布局高度相似，但卡片和列表行渲染逻辑分叉，维护成本高。
2. 审核中心缩略图不显示。
3. `adv-aside` 与主侧栏中的“系统性能”图标不显示。
4. 本地 OCR/tag 当前依赖 `BLIP caption + Tesseract OCR`，CPU 成本高；需要改成 **不依赖外部 AI token**、更轻量的本地方案。

## 2. 硬约束

本次执行必须同时满足：

- 不能丢功能。
- 不能改变当前页面样式、布局节奏、交互入口位置。
- 不能把三页硬合成一个超级页面组件。
- 不能引入外部多模态/LLM token 消耗。
- OCR/tag 必须保留：
  - 上传自动触发
  - 手动重识别
  - 定时/批量回填
  - 标签落库后可用于 AI 检索

## 3. 已确认的根因

### 3.1 图片三视图不是同一个组件体系

已共享：

- `resources/views/components/images-workspace.blade.php`
- `resources/views/components/images-workspace-styles.blade.php`
- `public/static/js/media-carousel-shared.js`

未共享：

- 用户图片管理：`resources/views/user/images.blade.php`
- 画廊：`resources/views/common/gallery.blade.php`
- 后台图片管理：`resources/views/admin/image/index.blade.php`

结论：

- 目前是三套独立 renderer，不是一个卡片组件。
- 能共享的是“卡片骨架 / 列表行骨架 / badge / 缩略图与状态选择逻辑”。
- 不应该强行共享的是 toolbar、批量操作、详情侧栏、权限动作。

### 3.2 审核中心缩略图不显示

前端问题：

- `resources/views/user/advanced_pages/reviews.blade.php` 当前手工拼图片 URL：
  - `"/" + key + "." + ext`

这个地址在以下情况不可靠：

- 原图保护
- 非本地策略
- 自定义策略 URL
- 文档预览链路
- 缩略图/预览分流

后端问题：

- `app/Http/Controllers/Api/V1/Admin/ReviewController.php` 的列表接口没有返回：
  - `url`
  - `thumb_url`
  - `preview_url`
  - `filename`

### 3.3 系统性能图标不显示

根因是 Font Awesome 版本不匹配。

- 当前项目使用：`@fortawesome/fontawesome-free ^5.15.4`
- 当前写法用了：`fa-gauge-high`

`fa-gauge-high` 属于 Font Awesome 6 命名，不在当前项目加载的 FA5 中。

受影响位置：

- `resources/views/components/advanced-shell.blade.php`
- `resources/views/layouts/sidebar.blade.php`

### 3.4 当前本地打标链路 CPU 成本高

当前链路：

1. 上传后由 `app/Services/ImageService.php` 派发 intelligence job
2. `app/Services/ImageIntelligence/ImageIntelligenceService.php` 调用本地 analyzer
3. `app/Services/ImageIntelligence/LocalImageIntelligenceAnalyzer.php` 调用
   `scripts/image_intelligence/classify_ocr.py`
4. Python 脚本逻辑：
   - 先用 Tesseract OCR 提取文本
   - 如果 OCR / 文件名没有足够种子信号，再调用 `BLIP`
   - 用 caption + OCR + 文件名匹配词表生成标签

问题：

- `BLIP` 是生成式 caption 模型，不适合“只要打标签”的场景。
- 当前默认模型 `Salesforce/blip-image-captioning-base` 的权重文件约 `990MB`，对 CPU 不友好。
- 项目真正需要的是：
  - 视觉标签
  - OCR 文本
  - 标签落库
  - 检索可用

并不需要生成式 caption 本身。

## 4. 新方案

### 4.1 图片三视图共享策略

本次不做“整页组件统一”，只做 **卡片层共享**。

执行策略：

1. 在 `public/static/js/media-carousel-shared.js` 抽共享 renderer：
   - 网格卡片骨架
   - 列表行骨架
   - badge 渲染
   - 缩略图/预览 URL 选择
   - intelligence/review 状态映射
2. 用户页、画廊、后台页改为调用共享 renderer。
3. 各页面继续保留自己的：
   - toolbar
   - 批量操作
   - 权限动作
   - 详情侧栏

这样能统一渲染，但不会把功能耦死。

### 4.2 审核中心缩略图修复

修复策略：

1. 后端 review 列表返回真实的 `url / thumb_url / preview_url / filename`
2. 前端审核中心按优先级使用：
   - `preview_url`
   - `thumb_url`
   - `url`
3. 删除手工拼接 `"/key.ext"` 的逻辑

### 4.3 系统性能图标修复

修复策略：

- 统一把 `fa-gauge-high` 改成当前 FA5 可用的 `fa-tachometer-alt`

这属于兼容性修复，不会影响样式结构。

### 4.4 本地 OCR/tag 替换策略

#### 目标

- 保留 Tesseract 做文本 OCR
- 去掉 BLIP caption 作为默认打标核心
- 改为更轻量的 **本地图像标签模型**
- 不调用外部 AI，不消耗 token

#### 推荐方案

推荐把默认视觉打标模型换成：

- `SmilingWolf/wd-vit-tagger-v3`

原因：

- 这是直接面向标签预测的模型，不是生成式 caption
- 有 ONNX 版本，适合 CPU 推理
- 模型文件体积显著低于当前 BLIP base
- 对“从图中给出可检索 tag”更贴合当前业务

对比参考：

- 当前：`Salesforce/blip-image-captioning-base`
  - 权重约 `990MB`
  - 任务类型：caption generation
- 候选：`SmilingWolf/wd-vit-tagger-v3`
  - ONNX 权重约 `379MB`
  - 任务类型：image tagging
- 更高精度但更重：`SmilingWolf/wd-swinv2-tagger-v3`
  - ONNX 权重约 `414MB`

本次建议默认选 `wd-vit-tagger-v3`，原因是它在体积和精度之间更平衡。

#### 业务落地方式

1. 保留 OCR：
   - 文件内文字仍由 Tesseract 提取
2. 视觉标签改为：
   - `wd-vit-tagger-v3` 直接给出 tag 候选
3. 继续走现有中文标签映射和关键词落库
4. 输出结构保持兼容：
   - `labels`
   - `keywords`
   - `summary`
   - `ocr_text`
   - `metadata`

#### 兼容与回滚

增加后端配置开关：

- `LSKY_LOCAL_TAGGER_BACKEND=wd_tagger|blip_legacy`

默认切到 `wd_tagger`，但保留 `blip_legacy` 作为回滚开关。  
这样能保证功能不丢，同时允许快速回退。

## 5. 实施任务

### 任务 A：先修两个明确 bug

目标：

- 审核中心缩略图恢复
- 系统性能图标恢复

涉及文件：

- `app/Http/Controllers/Api/V1/Admin/ReviewController.php`
- `resources/views/user/advanced_pages/reviews.blade.php`
- `resources/views/components/advanced-shell.blade.php`
- `resources/views/layouts/sidebar.blade.php`

验收：

- 审核中心的每一条图片行都能显示可点击缩略图
- `adv-aside` 和主侧栏里的“系统性能”图标恢复显示

### 任务 B：做图片三视图卡片层共享

目标：

- 用户页 / 画廊 / 后台图片页共用一套卡片与列表行 renderer
- 样式不变、DOM class 语义不变、功能入口不减少

涉及文件：

- `public/static/js/media-carousel-shared.js`
- `resources/views/user/images.blade.php`
- `resources/views/common/gallery.blade.php`
- `resources/views/admin/image/index.blade.php`

验收：

- 三页布局视觉不变
- 用户页、画廊、后台的页面功能不丢
- 新增 badge、缩略图、状态展示逻辑统一

### 任务 C：切换本地打标模型

目标：

- 不再默认依赖 BLIP caption
- 改成 `Tesseract OCR + wd-vit-tagger-v3`
- 继续支持上传自动触发、手动重识别、定时回填、AI 检索

涉及文件：

- `scripts/image_intelligence/classify_ocr.py`
- `scripts/image_intelligence/requirements.txt`
- `app/Services/ImageIntelligence/LocalImageIntelligenceAnalyzer.php`
- `app/Services/ImageIntelligence/LocalImageIntelligenceProcessRunner.php`
- `app/Services/ImageIntelligence/ImageIntelligenceService.php`
- `deploy/php83/Dockerfile`
- `scripts/health/startup.sh`

必要时补充：

- 模型下载脚本
- 运行时 env 配置
- 容器健康检查项

验收：

- 上传图片后自动落 tag
- 手动重识别能成功
- 定时/批量回填能成功
- 搜索“袜子”等标签能命中对应图片
- 不走外部 provider/token

## 6. 测试与验收

### 6.1 功能回归

必须验证：

1. 用户图片管理可正常浏览、重命名、删除、查看详情
2. 画廊可正常浏览、复制 URL、下载
3. 后台图片管理可正常浏览、预览、批量删除
4. 审核中心缩略图恢复
5. 系统性能图标恢复
6. 上传自动 OCR/tag 正常
7. 手动立即重识别正常
8. 定时/批量回填正常
9. 标签落库后可搜索命中

### 6.2 非功能约束

必须满足：

- 页面样式不变
- 不新增外部 AI token 依赖
- 容器健康检查通过
- worker / scheduler 不被破坏

## 7. 实施顺序

严格按这个顺序执行：

1. 任务 A：修图标与审核中心缩略图
2. 任务 B：做三视图卡片层共享
3. 任务 C：替换本地打标模型
4. 跑回归与容器验证

## 8. 风险控制

### 风险 1：共享重构导致样式漂移

控制：

- 共享 renderer 必须复用现有 class 和 DOM 结构
- 不改 CSS token，不改布局层级

### 风险 2：tag 模型替换后识别结果分布变化

控制：

- 保留 `blip_legacy` 回滚开关
- 先兼容旧输出结构
- 用样例图片验证标签命中再切默认值

### 风险 3：容器中依赖缺失

控制：

- Dockerfile 增加 ONNX 相关依赖
- 健康检查增加模型与 runtime 检查

## 9. 外部参考

- BLIP base 模型页：https://huggingface.co/Salesforce/blip-image-captioning-base
- WD ViT Tagger v3 模型页：https://huggingface.co/SmilingWolf/wd-vit-tagger-v3
- WD SwinV2 Tagger v3 模型页：https://huggingface.co/SmilingWolf/wd-swinv2-tagger-v3

