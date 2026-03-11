# Lsky Pro 安全修复计划

> 生成日期：2026-03-08
> 优先级：P0 = 立即修复 | P1 = 本周修复 | P2 = 下周修复

---

## 修复清单总览

| 编号 | 优先级 | 类别 | 问题描述 | 涉及文件 | 状态 |
|------|--------|------|---------|---------|------|
| FIX-01 | P0 | 凭据泄露 | Strategy/Group configs 暴露在 API 响应中 | Strategy.php, Group.php | ✅ |
| FIX-02 | P0 | 文件类型绕过 | 仅校验扩展名,未校验 MIME magic bytes | ImageService.php:110 | ✅ |
| FIX-03 | P0 | 路径遍历 | {filename} 未过滤 `..` 和路径分隔符 | ImageService.php:746 | ✅ |
| FIX-04 | P0 | IDOR | UploadTask 端点无认证无鉴权 | routes/api.php:38 | ✅ |
| FIX-05 | P0 | SSRF | Webhook URL 无限制 | WebhookController, DeliverWebhookEventJob | ✅ |
| FIX-06 | P0 | 时序漏洞 | 内容扫描在图片已公开后才执行 | ImageService.php:261-296 | ✅ |
| FIX-07 | P0 | 密钥安全 | 签名URL空密钥可伪造 + Webhook空secret | SignedUrlService.php, DeliverWebhookEventJob.php | ✅ |
| FIX-08 | P0 | 凭据明文 | WebhookSubscription secret 无 $hidden | WebhookSubscription.php | ✅ |
| FIX-09 | P1 | 竞态条件 | 上传去重无事务锁 (TOCTOU) | ImageService.php:227-271 | ⬜ 需更大重构 |
| FIX-10 | P1 | 数据泄露 | Album orWhere 跨用户泄露 | Album.php:62 | ✅ |
| FIX-11 | P1 | 数据完整性 | 物理文件删除未用 withTrashed() | Image.php:198 | ✅ |
| FIX-12 | P1 | 权限越权 | TeamMembership 自定义 permissions 覆盖角色 | TeamMembership.php:55-63 | ✅ |
| FIX-13 | P1 | 能力映射缺失 | EnforceTokenRestrictions 缺 webhook/review 路由 | EnforceTokenRestrictions.php | ✅ |
| FIX-14 | P1 | 资源泄漏 | Imagick 对象不 destroy + 无尺寸限制 | ImagickImageProcessorDriver.php | ✅ |
| FIX-15 | P1 | 资源泄漏 | fopen 句柄异常路径泄漏 | ImageService.php:235-242 | ✅ |
| FIX-16 | P1 | 空调度器 | 无任何定时清理任务 | Console/Kernel.php | ✅ |
| FIX-17 | P1 | 队列配置 | retry_after < job timeout 导致重复执行 | config/queue.php | ✅ |
| FIX-18 | P1 | 路由安全 | image.php 用 Route::any 接受所有方法 | routes/image.php | ✅ |
| FIX-19 | P2 | 错误处理 | @getimagesize 默认 [400,400] | ImageService.php:209 | ✅ |
| FIX-20 | P2 | 错误处理 | mkdir 无 recursive 且 @ 抑制 | ImageService.php:660-661 | ✅ |
| FIX-21 | P2 | 数据完整性 | 批量回滚无状态检查可重复回滚 | ImageBatchOperationService.php:122 | ✅ |
| FIX-22 | P2 | XSS | links() 中 origin_name 未转义 | Image.php:363 | ✅ |
| FIX-23 | P2 | 代码注入 | HookManager app() 无类名校验 | HookManager.php | ✅ |
| FIX-24 | P2 | Webhook | created 事件未用 afterCommit | Image.php:132-135 | ✅ 已有 afterCommit |
| FIX-25 | P2 | 空间隔离 | space_id 为 null 时跨空间查询 | ImageProcessExecutor.php | ✅ |
| FIX-26 | P1 | XSS | renderImages 模板替换无 HTML 转义 | user/images.blade.php | ✅ |
| FIX-27 | P1 | XSS | 相册树/抽屉模板替换无转义 | user/images.blade.php | ✅ |
| FIX-28 | P1 | XSS | 详情抽屉模板替换无转义 | user/images.blade.php | ✅ |
| FIX-29 | P2 | XSS | 管理端 renderCarouselThumbs src 未转义 | admin/image/index.blade.php | ✅ |
| FIX-30 | P2 | XSS | 用户端 renderCarouselThumbs src 未转义 | user/images.blade.php | ✅ |
| FIX-31 | P2 | XSS | drawer.open title 未转义 | user/images.blade.php | ✅ |
| FIX-32 | P2 | 健壮性 | ensureCarouselOriginalImage 无超时保护 | user/images.blade.php | ✅ |
| FIX-33 | P2 | XSS | 上传占位符 guid/objectUrl 未转义 | user/images.blade.php | ✅ |
| FIX-34 | P1 | XSS | 相册编辑模板 .html()/.attr('title') 未转义 | user/images.blade.php | ✅ |
| FIX-35 | P1 | XSS | 相册创建/编辑错误消息 response.data.message 未转义 | user/images.blade.php | ✅ |

---

## P0 — 立即修复 (8项)

### FIX-01: Strategy/Group configs 凭据泄露

**问题:** Strategy.configs 包含 AWS 密钥、OSS AccessKey 等存储凭据，API 响应中直接输出。

**修复:**
```
文件: app/Models/Strategy.php
操作: 添加 protected $hidden = ['configs'];

文件: app/Models/Group.php
操作: 添加 protected $hidden = ['configs'];
```

### FIX-02: 文件类型验证绕过

**问题:** 仅检查 getClientOriginalExtension()（客户端可伪造），未校验文件真实内容。

**修复:**
```
文件: app/Services/ImageService.php
位置: 行 110-113 附近
操作: 在扩展名检查之后，增加 MIME magic bytes 校验
```

### FIX-03: 路径遍历

**问题:** replacePathname() 中 {filename} 直接使用客户端文件名，含 `../` 时写入任意路径。

**修复:**
```
文件: app/Services/ImageService.php
位置: 行 746
操作: 过滤 ..、/、\ 等路径分隔符
```

### FIX-04: UploadTask 端点无认证

**问题:** GET upload-tasks/{taskId} 在 auth:sanctum 之外，任何人可查看上传结果。

**修复:**
```
文件: routes/api.php
位置: 行 38
操作: 移入 auth:sanctum 中间件组内
```

### FIX-05: Webhook SSRF

**问题:** Webhook URL 无校验，可指向内网/云元数据服务。

**修复:**
```
文件: app/Http/Controllers/Api/V1/WebhookController.php
操作: store() 增加 URL 校验（禁止私网IP、仅允许 http/https）

文件: app/Jobs/DeliverWebhookEventJob.php
操作: 投递前二次校验 URL，阻止 DNS 重绑定
```

### FIX-06: 扫描时序问题

**问题:** 图片先 save() 再 scan()，存在违规内容公开访问窗口。

**修复:**
```
文件: app/Services/ImageService.php
位置: 行 261-296
操作: 将 scan() 移到 save() 之前执行
```

### FIX-07: 空密钥签名

**问题:** SignedUrlService 和 DeliverWebhookEventJob 均接受空密钥生成有效签名。

**修复:**
```
文件: app/Services/SignedUrlService.php
位置: secret() 方法 (行 115-127)
操作: 空密钥时抛出异常

文件: app/Jobs/DeliverWebhookEventJob.php
位置: 行 61
操作: secret 为空时跳过签名或拒绝投递
```

### FIX-08: Webhook secret 明文暴露

**问题:** WebhookSubscription 无 $hidden，secret 会序列化到 API 响应。

**修复:**
```
文件: app/Models/WebhookSubscription.php
操作: 添加 protected $hidden = ['secret'];
```

---

## P1 — 本周修复 (9项)

### FIX-09: 上传去重竞态条件

**修复:**
```
文件: app/Services/ImageService.php
位置: 行 227-271
操作: 将去重检查+文件写入+DB保存包裹在 DB::transaction 中，
     去重查询加 lockForUpdate()
```

### FIX-10: Album orWhere 跨用户泄露

**修复:**
```
文件: app/Models/Album.php
位置: 行 61-62
操作: 将 orWhere 包裹在嵌套 where(function($q) { ... })
```

### FIX-11: deletePhysicalFileSync 未用 withTrashed

**修复:**
```
文件: app/Models/Image.php
位置: 行 198
操作: static::query() 改为 static::withTrashed()
```

### FIX-12: TeamMembership 权限越权

**修复:**
```
文件: app/Models/TeamMembership.php
位置: 行 55-63 resolvedPermissions()
操作: 自定义权限与角色允许的权限取交集
```

### FIX-13: EnforceTokenRestrictions 能力映射缺失

**修复:**
```
文件: app/Http/Middleware/EnforceTokenRestrictions.php
位置: ABILITY_MAP
操作: 添加 webhook、admin/reviews 路由的能力映射
```

### FIX-14: Imagick 资源泄漏 + 无尺寸限制

**修复:**
```
文件: app/Services/ImageProcessing/Drivers/ImagickImageProcessorDriver.php
位置: process() 方法 (行 32-59)
操作: 1) try/finally 中 $image->clear(); $image->destroy()
      2) readImageBlob 后检查尺寸，超限抛异常
```

### FIX-15: fopen 句柄异常泄漏

**修复:**
```
文件: app/Services/ImageService.php
位置: 行 235-242
操作: 加 finally 块确保关闭句柄
```

### FIX-16: 空调度器

**修复:**
```
文件: app/Console/Kernel.php
操作: 注册定时清理任务
  - lsky:cleanup-expired-images (每小时)
  - 清理 upload-tasks 临时文件 (每日)
  - 清理 failed_jobs (每周)
```

### FIX-17: retry_after 配置不匹配

**修复:**
```
文件: config/queue.php
操作: retry_after 设为 2000 (大于最大 job timeout 1800)
```

### FIX-18: image.php Route::any

**修复:**
```
文件: routes/image.php
位置: 行 11
操作: Route::any → Route::match(['GET', 'HEAD'], ...)
```

---

## P2 — 下周修复 (7项)

### FIX-19: @getimagesize 默认值
将 [400, 400] 改为 [0, 0]，getimagesize 失败时记录日志。

### FIX-20: mkdir 无 recursive
@mkdir(dirname($pathname)) → mkdir(dirname($pathname), 0755, true)，去掉 @。

### FIX-21: 批量回滚无状态检查
rollbackBatchDelete 增加 whereNotIn('status', ['rolled_back']) 条件。

### FIX-22: links() XSS
origin_name 用 htmlspecialchars() 转义。

### FIX-23: HookManager 类名校验
验证类名实现 HookPluginInterface 后再实例化。

### FIX-24: created 事件 afterCommit
Webhook/Hook dispatch 移到事务提交后。

### FIX-25: 空间隔离
space_id 为 null 时默认限制为用户个人空间。
