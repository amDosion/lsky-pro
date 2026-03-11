# Project Overview

## Product and Business Context

- Product goal: 这是一个以图片上传、存储、管理、检索和扩展处理为核心的图片管理工具，已经从原始图床能力扩展到高级处理、AI 配置、提示词、审核、团队空间和异步任务。
- Target users:
  - 小团队或个人站长，需要稳定上传和管理图片资源。
  - 内容运营与设计人员，需要编辑、检索、生成提示词和批量操作。
  - 管理员，需要配置存储、AI 提供商、邮件、升级和系统运行。
- Core business flows:
  - 上传图片 -> 管理图片 -> 编辑/重命名/删除 -> 获取链接
  - 管理员配置系统和 AI -> 用户使用 AI 提示词/检索/处理
  - 后台作业异步执行 -> 页面查看状态与结果

## Architecture Summary

- Frontend stack and entry points:
  - Blade 视图为主，`resources/views/user/*.blade.php` 和 `resources/views/admin/*.blade.php` 承载主要 UI。
  - 高级功能采用 `x-advanced-shell` 统一容器。
  - 图片管理页 `resources/views/user/images.blade.php` 内嵌大量交互脚本，是最耦合的前端文件之一。
- Backend/services and APIs:
  - `routes/web.php` 提供页面与 `advanced-api` 接口。
  - `routes/api.php` 提供 token/API 上传与部分高级接口。
  - `app/Services` 中已有 AI 配置、AI 提示词、批量删除、图片处理、上传任务、Webhook 等服务。
- Data storage and external dependencies:
  - MariaDB/MySQL 存业务表。
  - Laravel Queue 承载上传任务、提示词任务、图片处理任务、删除任务、Webhook。
  - 对外依赖 AI provider、对象存储和邮件服务。
- CI/CD and runtime environments:
  - 当前运行在 Docker 容器 `lsky-pro`（PHP 8.3）。
  - 宿主机 PHP 版本不满足项目运行要求，只能用于非项目级参考。

## Critical Modules

| Module | Responsibility | Key Dependencies | Risk Notes |
| --- | --- | --- | --- |
| `user/images.blade.php` | 图片管理主工作台、列表/轮播/裁剪/重命名/删除 | `/user/images*`、`advanced-api/images/*` | 单文件前端逻辑过重，后续功能继续叠加会失控 |
| `AiProviderConfigService` | AI provider 元信息、保存配置、选择默认模型 | `configs` 表、AI 页面 | 当前模型列表静态写死，不是真正 provider registry |
| `AiPromptService` | 图片 + metadata -> AI 提示词 | provider config、远端 AI API | 与图像分析/检索边界混杂 |
| `ImageController::aiSearch` | 当前 AI 检索入口 | `images`、`tags`、`ocr_text` | 实际是关键词检索，不是真 AI retrieval |
| `Console\Kernel` | scheduler 任务调度 | queue、upload tasks、ai prompt tasks | 缺少标签/索引/性能监测链路，且有超时状态缺陷 |
| `admin/setting/index.blade.php` | 管理端系统设置 | `Config` 表、升级/邮件 | UI 结构老旧，难以继续扩展 |
| `SocialAuthController` | Google/GitHub OAuth 登录 | Socialite、`services.php` | 社交登录可用但 Passkey 空缺 |

## Current Capability Baseline

- Existing feature set:
  - 图片上传、相册管理、批量删除、签名链接、团队空间、处理模板、AI 提示词、审核中心、作业中心。
- Known limitations:
  - AI 检索本质仍是名称/OCR/tag 的加权模糊搜索。
  - OCR 是 placeholder，不是真正识别。
  - 模型列表依赖静态数组和手工维护。
  - 系统没有专门的性能检测页。
  - 登录只覆盖密码和社交登录，Passkey 缺失。
- Quality/security debt:
  - 大型 Blade 文件内嵌脚本可维护性差。
  - AI 领域边界未拆分，模型管理、提示词、标注、检索混在一起。
  - scheduler/队列运行状态对用户和管理员都不透明。

## Upgrade Opportunities

1. 先把 AI provider registry 和模型发现能力立住，避免后续所有 AI 功能继续依赖静态数组。
2. 增加系统性能观测页和统一作业观测，支撑后续 AI 标注和检索流水线落地。
3. 以“图像智能分析”独立领域重构 AI 检索、AI 标签和 AI 提示词，替代现在的关键词/OCR 占位方案。
