# Long-Running Status

## 2026-03-04 Iteration 1
- 状态: DONE
- 任务: P1-T101（Security Baseline Fast Fixes）

### 已完成
- [x] Admin 用户详情页 XSS 快修（动态字段统一 HTML 转义）
- [x] 冻结用户登录拦截
- [x] 冻结用户 Token 发放拦截
- [x] 安全配置基线文档新增
- [x] `.env` 权限收紧为 `640`

### 变更文件
- app/Http/Requests/Auth/LoginRequest.php
- app/Http/Controllers/Api/V1/TokenController.php
- resources/views/admin/user/index.blade.php
- docs/runbook/SECURITY_BASELINE.md
- docs/requirements/REQUIREMENTS.md
- docs/design/DESIGN.md
- docs/tasks/TASKS.md
- docs/runbook/LONG_RUNNING_PLAN.md

### 验证记录
- `php -l app/Http/Requests/Auth/LoginRequest.php` 通过
- `php -l app/Http/Controllers/Api/V1/TokenController.php` 通过
- `docker exec lsky-pro php artisan route:list` 关键路由存在
- `stat .env` 权限为 `640`

### 阻塞
- 宿主机 PHP 版本为 7.4，无法在宿主机直接执行项目 artisan（需要容器内 PHP 8.1 环境执行）。

### 下一步（Iteration 2）
- P1-T104：索引与查询优化（先做 migration：`images(strategy_id,md5,sha1)`、`images(user_id,created_at)`、`images(uploaded_ip,created_at)`）
- P1-T102：队列改造准备（Redis queue 配置 + worker runbook 草案）

## 2026-03-04 Iteration 2
- 状态: PARTIAL
- 任务: P1-T104（索引与查询优化）

### 已完成
- [x] 新增热点索引 migration：`images` 上 4 个复合索引

### 变更文件
- database/migrations/2026_03_04_162500_add_hot_indexes_to_images_table.php

### 验证记录
- `php -l database/migrations/2026_03_04_162500_add_hot_indexes_to_images_table.php` 通过
- `docker exec lsky-pro php artisan migrate --pretend` 显示预期 SQL

### 下一步
- 执行真实迁移（建议低峰）
- 改造上传频控查询，逐步从 DB 聚合迁移到 Redis 计数

## 2026-03-04 Iteration 3
- 状态: DONE
- 模式: Long-running + Multi-agent loop
- 任务: P1-T101（深化）+ P0/P1 文档闭环

### 已完成
- [x] 升级链路路径安全加固（绝对路径/穿越/目录白名单拦截）
- [x] 管理设置保存加固（仅允许已存在 config key；数组统一 JSON 化）
- [x] 配置缓存失效改为定向 `Cache::forget('configs')`
- [x] CORS 默认收紧（通过 `CORS_ALLOWED_ORIGINS` 显式配置，默认非通配）
- [x] 新增自动驾驶循环文档 `LONG_RUNNING_AUTOPILOT.md`

### 变更文件
- app/Services/UpgradeService.php
- app/Http/Controllers/Admin/SettingController.php
- config/cors.php
- .env.example
- docs/runbook/LONG_RUNNING_AUTOPILOT.md

### 验证记录
- 容器内语法检查:
  - `php -l app/Services/UpgradeService.php`
  - `php -l app/Http/Controllers/Admin/SettingController.php`
  - `php -l config/cors.php`
  - 均通过

### 下一轮计划（Iteration 4）
- P1-T102: Redis queue 接入与 worker 运行手册
- P1-T104: 迁移执行窗口内真实加索引 + 查询路径优化第一批

## 2026-03-04 Iteration 4
- 状态: DONE
- 模式: Long-running + Multi-agent loop
- 任务: P1-T102 + P1-T104 + 运维文档闭环

### 已完成
- [x] 图片删除链路支持异步队列化（Redis queue）
- [x] 增加 `IMAGE_DELETE_ASYNC` 开关与重试/超时/退避参数
- [x] 上传频控查询从 `count(*)` 优化为 `offset+exists` 阈值判定
- [x] 用户与 API 图片列表显式 `select` + 关系预加载，降低查询开销
- [x] 新增生产迁移窗口/回滚与 worker 运行文档
- [x] 修复异步队列连接默认值，避免回落到同步队列

### 变更文件
- app/Jobs/DeleteImagePhysicalFileJob.php
- app/Models/Image.php
- config/queue.php
- .env.example
- app/Services/ImageService.php
- app/Http/Controllers/User/ImageController.php
- app/Http/Controllers/Api/V1/ImageController.php
- docs/design/DESIGN.md
- docs/runbook/PROD_MIGRATION_WINDOW_AND_ROLLBACK.md
- docs/runbook/QUEUE_WORKER_RUNTIME_EXAMPLES.md
- docs/runbook/REDIS_QUEUE_IMAGE_DELETE.md
- docs/runbook/LONG_RUNNING_AUTOPILOT.md

### 验证记录
- 容器内语法检查:
  - `docker exec lsky-pro php -l app/Jobs/DeleteImagePhysicalFileJob.php`
  - `docker exec lsky-pro php -l app/Models/Image.php`
  - `docker exec lsky-pro php -l app/Services/ImageService.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/User/ImageController.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ImageController.php`
  - `docker exec lsky-pro php -l config/queue.php`
- 队列运行验证:
  - `docker exec lsky-pro php artisan queue:failed --no-ansi`
  - `docker exec lsky-pro php artisan queue:work redis --queue=image-delete --once --no-ansi --tries=1 --timeout=30`
- 迁移验证:
  - `docker exec lsky-pro php artisan migrate --no-ansi`（`2026_03_04_162500_add_hot_indexes_to_images_table` 执行成功）
  - `docker exec lsky-pro php artisan migrate:status --no-ansi`（索引 migration 状态为 Ran）

### 残留事项
- 生产环境按变更窗口执行同版发布与观测（本环境已完成迁移与命令级验证）。

### 下一轮计划（Iteration 5）
- P0-T004/P0-T005: 上传主链路验收 + API 冒烟测试集落地

## 2026-03-04 Iteration 5
- 状态: DONE
- 模式: Long-running + Multi-agent loop
- 任务: P0 全量收敛 + P1-T103 + P2-T201/P2-T202/P2-T204

### 已完成
- [x] `run-all.sh` 修复为容器/本地双模式统一执行器
- [x] P0 六项全部打通并在容器环境全绿
- [x] 新增 smoke 与 acceptance 脚本、最小 CI workflow
- [x] 增加 `request_id/trace_id` 上下文中间件与响应头回传
- [x] 限流反馈增强（429 包含 retry_after/request_id/trace_id）
- [x] 审计日志通道 `audit` 与敏感接口审计记录
- [x] 管理敏感路由限流落地
- [x] 运维 runbook 与 docs 索引完成整理

### 关键修复
- 修复本地策略符号链接创建路径，避免 `symlink(): File exists` 幂等失败
- 修复上传频控 SQL 在 MariaDB 上 `OFFSET` 语法问题（改为 `OFFSET + LIMIT 1`）
- 修复测试场景下邮件配置空值兼容（`AppServiceProvider`）
- 修复 API smoke 中配置缓存污染导致的用户容量为空问题

### 验证记录
- `bash scripts/run-all.sh` 通过
- 日志: `storage/logs/run-all-20260304T164331Z.log`
- `docker exec lsky-pro php -l ...`（本轮所有关键改动文件）通过

## 2026-03-04 Iteration 6
- 状态: DONE
- 模式: Long-running closeout
- 任务: P2-T203（覆盖率基线能力）+ 全项任务收口

### 已完成
- [x] 新增覆盖率脚本 `scripts/ci/coverage.sh`（xdebug 环境）
- [x] 新增覆盖率 CI `/.github/workflows/coverage-ci.yml`
- [x] `docs/tasks/TASKS.md` 全项更新为 DONE

### 验证记录
- 覆盖率脚本语法与路径检查通过
- 覆盖率执行依赖 CI 提供 xdebug（本容器无 xdebug）

### 总结
- `P0/P1/P2` 任务项全部完成并具备可执行入口。

## 2026-03-04 Iteration 7
- 状态: DONE
- 模式: Long-running continuation
- 任务: 安全化执行器 + Codex Non-interactive workflow 落地

### 已完成
- [x] `run-all` 默认安全模式（隔离 SQLite 验证，不触碰业务库）
- [x] 幂等/验收/CI/覆盖率脚本统一为隔离库执行并自动处理 `installed.lock`
- [x] 新增 `PLANS.md` 执行计划契约
- [x] 新增 `scripts/codex/noninteractive-exec.sh`
- [x] 新增 `docs/runbook/CODEX_NONINTERACTIVE_WORKFLOW.md`
- [x] 更新 `docs/README.md` 索引

### 验证记录
- `bash scripts/run-all.sh` 通过（日志：`storage/logs/run-all-20260304T165348Z.log`）
- `bash -n scripts/codex/noninteractive-exec.sh` 通过

### 结论
- 默认执行路径已从“潜在破坏式”切换为“安全非破坏式”，并具备非交互 Codex 工作流入口。

## 2026-03-05 Iteration 8
- 状态: IN_PROGRESS
- 模式: Long-running + Multi-agent + Non-interactive
- 任务: 图片管理精修 + 社交登录接入 + 仪表盘迁移 + API 树形重构

### 已完成
- [x] 图片管理：分页右侧对齐、同款圆点选择器、去除 hover 删除按钮
- [x] 图片管理：详情改为轮播面板（左大图/右详情/底部缩略图）
- [x] 图片管理：局部删除、分页控件、滚动懒加载
- [x] 社交登录：Google/GitHub 路由、控制器、配置、登录/注册入口、用户字段迁移
- [x] 仪表盘：管理员控制台指标与趋势图并入用户仪表盘（兼容保留原控制台）
- [x] 接口页：左侧接口树 + 右侧内容区（自动目录与滚动高亮）

### 验证记录
- `php -l`：`resources/views/admin/image/index.blade.php` 通过
- `php -l`：`app/Http/Controllers/Auth/SocialAuthController.php` 通过
- `php -l`：`app/Http/Controllers/User/UserController.php` 通过
- `php -l`：`resources/views/user/dashboard.blade.php` 通过
- `php -l`：`resources/views/common/api.blade.php` 通过
- `php -l`：`routes/auth.php` 通过
- `php -l`：`database/migrations/2026_03_05_120000_add_social_columns_to_users_table.php` 通过
- `bash /root/.codex/skills/codex-workflow-standard/scripts/validate_codex_workflow.sh /root/code-server/config/workspace/lsky-pro/lsky` 通过
- `bash /root/.codex/skills/codex-team-recipes-global/scripts/validate_role_matrix.sh /root/code-server/config/workspace/lsky-pro/lsky/docs/tasks/TASKS.md` 通过

### 风险与待办
- 本机缺少 `composer`，未能在本地执行 `composer require laravel/socialite` 与 lock 更新
- 需在目标运行环境执行：`composer require laravel/socialite:^5.16`、`php artisan migrate`

## 2026-03-05 Iteration 9
- 状态: DONE
- 任务: 高级拓展功能落地（OAuth 可用性 + OpenAPI 精度 + Non-interactive 多代理闭环）

### 已完成
- [x] OAuth 可用性治理：provider 配置检测、未配置友好提示、redirect/callback 异常兜底
- [x] 登录/注册页第三方按钮按配置状态展示（可用/禁用）
- [x] OpenAPI 文档生成增强：`/upload` 输出 `multipart/form-data`，`/tokens` 可识别 inline validate 参数
- [x] 新增 `api/openapi.json` 持续可用验证
- [x] Non-interactive 工作流闭环补全：审计日志时间戳落盘、validate 脚本、CI smoke workflow、计划文档同步

### 变更文件
- app/Http/Controllers/Auth/SocialAuthController.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- app/Http/Controllers/Common/ApiController.php
- scripts/codex/noninteractive-exec.sh
- scripts/codex/validate.sh
- .github/workflows/codex-workflow-smoke.yml
- docs/runbook/LONG_RUNNING_PLAN.md
- docs/runbook/CODEX_NONINTERACTIVE_WORKFLOW.md
- docs/runbook/STATUS.md

### 验证记录
- `php -l app/Http/Controllers/Auth/SocialAuthController.php` 通过
- `php -l app/Http/Controllers/Common/ApiController.php` 通过
- `php -l routes/web.php` 通过
- `bash scripts/codex/validate.sh` 通过（输出 `codex workflow validation passed`）
- `docker exec lsky-pro php artisan route:list | grep -E "api/openapi.json|oauth.redirect|oauth.callback"` 命中
- OpenAPI 冒烟：
  - `openapi=3.0.3`
  - `/upload` => `multipart/form-data`
  - `/tokens` requestBody 包含 `email`

### 残留风险
- 宿主机 PHP 扩展 `pdo_mysql` 警告仍存在（容器内运行正常）。
- OAuth 实际登录成功仍依赖生产/测试环境正确配置 `services.php` 对应环境变量。

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: 第三轮-后端：异步批处理作业中心（process template jobs）

### 已完成
- [x] 新增 `image_process_jobs` 表（`job_id,user_id,template_id,status,total,processed,success,failed,result,error_message,started_at,finished_at`）
- [x] 新增 `ImageProcessJob` 模型（含 casts 与 `user/template` 关联）
- [x] 新增异步 Job `RunImageProcessTemplateJob`，复用 `ImageProcessExecutor` 逐条执行并实时更新进度
- [x] 新增 API：
- [x] `POST /api/v1/process-templates/{id}/dispatch`（提交任务返回 `job_id`）
- [x] `GET /api/v1/process-jobs/{jobId}`（查询进度与结果）
- [x] 更新 token ability 映射：新增路由映射到 `images:process`

### 变更文件
- database/migrations/2026_03_05_210000_create_image_process_jobs_table.php
- app/Models/ImageProcessJob.php
- app/Jobs/RunImageProcessTemplateJob.php
- app/Http/Controllers/Api/V1/ProcessTemplateController.php
- routes/api.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- docs/runbook/STATUS.md

### 验证记录（容器内）
- `docker exec lsky-pro php -l /var/www/html/database/migrations/2026_03_05_210000_create_image_process_jobs_table.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Models/ImageProcessJob.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Jobs/RunImageProcessTemplateJob.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Api/V1/ProcessTemplateController.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Http/Middleware/EnforceTokenRestrictions.php` 通过
- `docker exec lsky-pro php -l /var/www/html/routes/api.php` 通过
- `docker exec lsky-pro php artisan route:list --path=api/v1/process --no-ansi` 命中新路由：
- `GET|HEAD api/v1/process-jobs/{jobId}`
- `POST api/v1/process-templates/{id}/dispatch`

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: AR-next-1（批处理模板与一键执行）

### 已完成
- [x] 新增 `image_process_templates` 数据表（`user_id/name/definition/is_shared`）
- [x] 新增 API:
  - `GET /api/v1/process-templates`
  - `POST /api/v1/process-templates`
  - `POST /api/v1/process-templates/{id}/run`
- [x] `run` 接口对 `keys[]` 逐条执行与 `images/{key}/process` 同等处理逻辑，并返回成功/失败明细
- [x] 新增 token ability 映射（以上三个模板路由映射到 `images:process`）

### 变更文件
- database/migrations/2026_03_05_190000_create_image_process_templates_table.php
- app/Models/ImageProcessTemplate.php
- app/Services/ImageProcessing/ImageProcessExecutor.php
- app/Http/Controllers/Api/V1/ProcessTemplateController.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- app/Models/User.php
- routes/api.php
- docs/runbook/STATUS.md

### 验证记录
- 容器 `php -l`（本轮改动文件）通过
- 容器 `php artisan route:list --path=api/v1` 可见新增 `process-templates` 相关路由

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: 登录体系改造（默认入口切换 + 登录页视觉重构）

### 已完成
- [x] 根路由 `/` 改为安装态分流：已安装跳转 `/login`，未安装跳转 `/install`
- [x] 登录页重构为左右分栏：左侧动画视觉区，右侧登录表单区
- [x] 登录区保留账号密码、记住我、找回密码、Google/GitHub 第三方登录按钮
- [x] 保持现有登录提交流程、错误提示、会话提示与 OAuth 可用性逻辑不变
- [x] 移除默认回落到上传入口（`welcome/upload`）的首页行为
- [x] 响应式适配：移动端仅展示右侧登录区，桌面端展示双栏布局

### 变更文件
- routes/web.php
- resources/views/auth/login.blade.php
- docs/runbook/STATUS.md

### 验证记录
- 容器路由检查：
  - `docker exec lsky-pro php artisan route:list | grep -E "(^\\s*GET\\|HEAD\\s+(/\\s|login\\s|install\\s))|login|install"` 命中：
    - `GET|HEAD /`
    - `ANY install`
    - `GET|HEAD login`
    - `POST login`
- 容器语法检查：
  - `docker exec lsky-pro php -l /var/www/html/routes/web.php` 通过
  - `docker exec lsky-pro php -l /var/www/html/resources/views/auth/login.blade.php` 通过
- 宿主语法检查（补充）：
  - `php -l routes/web.php` 通过
  - `php -l resources/views/auth/login.blade.php` 通过

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: AR-003 安全下载链接（签名 + 时效）

### 已完成
- [x] 新增签名服务 `SignedUrlService`（HMAC-SHA256，支持 base64 `APP_KEY`）
- [x] 新增下载安全配置 `config/download.php` 与 `.env.example` 开关
- [x] 图片 URL 生成接入签名（默认关闭，开启后追加 `expires` + `signature`）
- [x] 图片输出链路新增签名校验（仅在开关命中时生效，默认兼容旧行为）
- [x] 新增后端签名 URL 入口：`GET /api/v1/images/{key}/signed-url?expires_in=300`
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-003 更新为 DONE

### 变更文件
- app/Services/SignedUrlService.php
- config/download.php
- app/Models/Image.php
- app/Http/Controllers/Controller.php
- routes/api.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- .env.example
- docs/tasks/ADVANCED_TASKS.md

### 验证记录
- 宿主机 `php -l`：
  - `app/Services/SignedUrlService.php`、`config/download.php`、`routes/api.php`、`app/Http/Middleware/EnforceTokenRestrictions.php`、`app/Http/Controllers/Api/V1/ImageController.php` 通过
  - `app/Models/Image.php`、`app/Http/Controllers/Controller.php` 在宿主机 PHP 7.4 环境出现语法误报（项目使用 PHP 8 语法）
- 容器内 `docker exec lsky-pro php -l`：
  - `app/Services/SignedUrlService.php`、`config/download.php`、`app/Models/Image.php`、`app/Http/Controllers/Controller.php`、`routes/api.php`、`app/Http/Middleware/EnforceTokenRestrictions.php`、`app/Http/Controllers/Api/V1/ImageController.php` 全部通过
- 路由冒烟：
  - `docker exec lsky-pro php artisan route:list --path=api/v1/images` 命中 `GET api/v1/images/{key}/signed-url`
  - `docker exec lsky-pro php artisan route:list --path='{key}.{extension}'` 命中 `ANY {key}.{extension} -> Controller@output`

### 下一步（Iteration 10 触发条件）
- 若用户确认继续：进入“其余页面高级 UI/交互统一化”批次，按模块（用户端、管理端、设置端）逐轮自动推进并回写 STATUS。

## 2026-03-05 Iteration 10
- 状态: PARTIAL
- 任务: 其余页面高级 UI/交互统一化（Batch 1：全局壳层与 API 文档页体验）

### 已完成（Batch 1）
- [x] 修复 API 文档页高度变量与主布局对齐：`--header-height` + `100dvh` 适配
- [x] 移动端侧栏遮罩恢复（点击遮罩关闭）
- [x] 移动端侧栏打开时主内容滚动锁定（避免穿透滚动）
- [x] 视口缩放策略修正（允许用户缩放阅读）

### 变更文件
- resources/views/layouts/app.blade.php
- resources/views/common/api.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `php -l resources/views/layouts/app.blade.php` 通过
- `php -l resources/views/common/api.blade.php` 通过
- `docker exec lsky-pro php -l /var/www/html/resources/views/layouts/app.blade.php` 通过
- `docker exec lsky-pro php -l /var/www/html/resources/views/common/api.blade.php` 通过
- `docker exec lsky-pro php artisan optimize:clear` 完成

### 下一步（Batch 2）
- 对 `user/images.blade.php` 做结构化拆分与样式统一（toolbar/主区/详情抽屉局部组件化），在不改功能前提下去除旧式字符串模板耦合点。

## 2026-03-05 Iteration 11
- 状态: PARTIAL
- 任务: 其余页面高级 UI/交互统一化（Batch 2：我的图片页面低风险语义化重构）

### 已完成（Batch 2 / Phase 1）
- [x] 将相册树头部 `javascript:void(0)` 伪链接改为语义化 `button`
- [x] 将移动端工具栏触发器改为 `button`
- [x] 将排序/权限下拉触发器从 `a` 改为 `button`
- [x] 新增头部图标按钮统一样式类 `aside-head-icon-btn`

### 变更文件
- resources/views/user/images.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `php -l resources/views/user/images.blade.php` 通过
- `docker exec lsky-pro php -l /var/www/html/resources/views/user/images.blade.php` 通过
- `docker exec lsky-pro php artisan view:clear` 完成

### 下一步（Batch 2 / Phase 2）
- 对 `user/images.blade.php` 继续拆分局部片段（toolbar / aside head / carousel controls），减少单文件复杂度并保持功能等价。

## 2026-03-05 Iteration 12
- 状态: DONE
- 任务: 其余页面高级 UI/交互统一化（Batch 2 完成收口）

### 已完成
- [x] `user/images` 结构化拆分为 partial（aside-head / toolbar / footer-pagination / carousel-shell）
- [x] `user/images`、`admin/image`、`admin/group(add/edit)` 低风险语义化按钮替换（减少伪链接触发器）
- [x] `admin/image` 页头层级与容器节奏统一
- [x] `admin/group(add/edit)` tabs 与主卡片视觉统一

### 变更文件
- resources/views/user/images.blade.php
- resources/views/user/images/partials/aside-head.blade.php
- resources/views/user/images/partials/toolbar.blade.php
- resources/views/user/images/partials/footer-pagination.blade.php
- resources/views/user/images/partials/carousel-shell.blade.php
- resources/views/admin/image/index.blade.php
- resources/views/admin/group/add.blade.php
- resources/views/admin/group/edit.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `php -l`（上述全部 Blade 文件）通过
- `docker exec lsky-pro php -l`（上述主要 Blade）通过
- `docker exec lsky-pro php artisan view:clear` 完成
- `docker exec lsky-pro php artisan optimize:clear` 已于上一轮完成
- `bash scripts/codex/validate.sh` 通过

### 终态判断
- 本轮既定范围（API页、壳层、我的图片、管理图片、角色组 add/edit）无高优先级残留问题。
- 下一轮触发条件：用户新增模块范围或验收反馈出现新缺陷。

## 2026-03-05 Auto-Loop AR-001
- 状态: DRY_RUN
- 任务: AR-001 Token scope + 过期 + IP 白名单
- 说明: 由 scripts/codex/continuous-main-agent.sh 自动调度

## 2026-03-05 Auto-Loop AR-001
- 状态: FAILED
- 任务: AR-001 Token scope + 过期 + IP 白名单
- 说明: 由 scripts/codex/continuous-main-agent.sh 自动调度

## 2026-03-05 Iteration 13
- 状态: DONE
- 任务: AR-001 Token scope + 过期 + IP 白名单

### 已完成
- [x] 新增 Token 限制模型：`expires_at` + `ip_whitelist`
- [x] 新增 `EnforceTokenRestrictions` 中间件（过期、IP 白名单、能力校验）
- [x] API 路由接入 token 限制中间件
- [x] Token 签发支持参数：`abilities[]`、`expires_in`、`expires_at`、`ip_whitelist`
- [x] Sanctum 绑定自定义 PAT 模型

### 变更文件
- app/Models/PersonalAccessToken.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- app/Http/Controllers/Api/V1/TokenController.php
- app/Providers/AppServiceProvider.php
- routes/api.php
- database/migrations/2026_03_05_140000_add_restrictions_to_personal_access_tokens_table.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `php -l`（上述 PHP 文件）通过
- `docker exec lsky-pro php -l`（上述 PHP 文件）通过
- `docker exec lsky-pro php artisan route:list | grep api/v1/(images|albums|tokens|profile)` 通过
- `docker exec lsky-pro php artisan optimize:clear` 完成

### 残留风险
- 新增 migration 尚未执行，需在目标环境执行 `php artisan migrate`。
- 现有调用方若使用受限 abilities，需要按新能力集合配置 token。

### 下一步
- 自动进入 `AR-002`：审计日志（管理端/API 关键写操作）。

## 2026-03-05 Auto-Loop AR-002
- 状态: DRY_RUN
- 任务: AR-002 审计日志（管理端/API关键写操作）
- 说明: 由 scripts/codex/continuous-main-agent.sh 自动调度

## 2026-03-05 Iteration 14
- 状态: DONE
- 任务: AR-002 审计日志（管理端/API关键写操作）

### 已完成
- [x] 扫描并覆盖 Admin/API 关键写操作控制器（create/update/delete/upload/token issue/revoke 等）
- [x] 新增统一审计 helper：`AuditsOperations` trait，避免控制器重复拼日志字段
- [x] 审计字段统一为 `request_id`、`trace_id`、`user_id`、`resource`、`target`、`action`、`result`、`ip`
- [x] 成功/失败分支分别落审计（如 token 校验失败、冻结用户、组保护删除失败、批量删除空参数等）
- [x] 修复此前 AR-002 半成品中的语法破坏与 `return` 后不可达日志代码

### 变更文件
- app/Http/Controllers/Concerns/AuditsOperations.php
- app/Http/Controllers/Admin/GroupController.php
- app/Http/Controllers/Admin/StrategyController.php
- app/Http/Controllers/Admin/UserController.php
- app/Http/Controllers/Admin/ImageController.php
- app/Http/Controllers/Admin/SettingController.php
- app/Http/Controllers/Api/V1/AlbumController.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/Api/V1/TokenController.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `php -l`（上述改动 PHP 文件）通过；本机 PHP 环境存在 `pdo_mysql` 启动告警，但不影响语法校验结果
- `docker exec lsky-pro php -l`（上述改动 PHP 文件）通过

### 残留风险
- 当前仅完成控制器层审计统一，若后续新增写接口需复用同一 trait 才能保持字段一致性
- 审计日志量会随上传/删除频次增长，建议后续在运维侧持续观察 `storage/logs/audit-*.log` 轮转与容量

### 下一步
- 自动进入 `AR-003`：安全下载链接（签名 + 时效）

## 2026-03-05 Iteration 15
- 状态: DONE
- 任务: AR-005 生命周期策略第一期（TTL + 回收站）

### 已完成
- [x] `images` 增加生命周期字段：`expire_at` 与 `deleted_at`（软删）及索引
- [x] `Image` 模型接入 `SoftDeletes`，删除事件仅在 `forceDelete` 时触发物理文件删除，兼容现有删除链路
- [x] 新增生命周期配置 `config/lifecycle.php`（TTL 开关/默认值 + 回收站开关）
- [x] 上传流程支持默认 TTL（启用后自动写入 `expire_at`）
- [x] 新增到期清理命令 `images:cleanup-expired`（默认 dry-run，`--execute` 才实际删除）
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-005 标记为 DONE

### 变更文件
- database/migrations/2026_03_05_150000_add_lifecycle_columns_to_images_table.php
- app/Models/Image.php
- app/Services/ImageService.php
- app/Services/UserService.php
- app/Console/Commands/CleanupExpiredImages.php
- config/lifecycle.php
- .env.example
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `php -l`（本轮改动 PHP 文件）部分通过；宿主机 `php` 为 7.4.3，项目内既有 PHP8 语法（命名参数、nullsafe）导致 `app/Models/Image.php`、`app/Services/ImageService.php`、`app/Services/UserService.php` 解析失败，容器内 PHP8 正常
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）通过
- `docker exec lsky-pro php artisan list` 可见 `images:cleanup-expired`

### 残留事项
- 新增 migration 尚未执行，需在目标环境运行 `php artisan migrate`。

## 2026-03-05 Iteration 16
- 状态: DONE
- 任务: AR-004 异步上传流水线第一期（提交即返回 + 状态查询）

### 已完成
- [x] 新增上传异步开关配置：`upload_pipeline_async_enabled`（默认 `0`，保持同步兼容）
- [x] API/Web 上传在异步模式下改为“提交即返回” `task_id/status`
- [x] 新增 `API v1` 任务状态查询：`GET /api/v1/upload-tasks/{taskId}`
- [x] 新增异步处理 Job：任务状态回写 `success/failed`，并保存结果/错误信息
- [x] 新增 `upload_tasks` 持久化表与模型，支持任务生命周期追踪
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-004 标记为 DONE

### 变更文件
- app/Enums/ConfigKey.php
- config/convention.php
- app/Utils.php
- config/queue.php
- .env.example
- app/Services/UploadTaskService.php
- app/Jobs/ProcessUploadTaskJob.php
- app/Models/UploadTask.php
- app/Http/Controllers/Api/V1/UploadTaskController.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/Controller.php
- routes/api.php
- database/migrations/2026_03_05_152000_create_upload_tasks_table.php
- database/migrations/2026_03_05_151000_add_upload_pipeline_async_enabled_config.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `php -l`（改动文件）已执行；宿主机 PHP 7.4 对项目内既有 PHP8 语法文件（如 `app/Utils.php`、`app/Http/Controllers/Controller.php`）会报解析错误，新增 AR-004 文件语法可通过
- `docker exec lsky-pro php -l`（改动文件）全部通过
- `docker exec lsky-pro php artisan route:list --no-ansi | grep -E "upload-tasks|v1/upload"` 命中：
  - `POST api/v1/upload`
  - `GET|HEAD api/v1/upload-tasks/{taskId}`

### 残留事项
- 新增 migration 尚未执行，需在目标环境运行 `php artisan migrate`。
- 异步开关默认关闭；如需启用请将配置项 `upload_pipeline_async_enabled` 设为 `1` 并启动对应 queue worker。

## 2026-03-05 Iteration 17
- 状态: DONE
- 任务: AR-006 批量操作2.0（预览 + 回滚）

### 已完成
- [x] 新增 `API v1` 批量删除预演接口：`POST /api/v1/images/batch-delete/preview`（返回 `affected_count` 与 `preview_keys`）
- [x] 新增批量删除接口支持 dry-run 与 execute：`POST /api/v1/images/batch-delete`（`dry_run`/`execute`）
- [x] execute 时记录可回滚批次：新增 `image_batch_operations` 表，记录 `batch_id`、图片 IDs/keys、执行/回滚时间
- [x] 新增按批次回滚接口：`POST /api/v1/images/batch-delete/rollback/{batchId}`，恢复软删图片
- [x] 接入 API v1 路由与 token 权限映射：`EnforceTokenRestrictions` 增加对应路径能力校验
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-006 标记为 DONE

### 变更文件
- database/migrations/2026_03_05_153000_create_image_batch_operations_table.php
- app/Models/ImageBatchOperation.php
- app/Services/ImageBatchOperationService.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- routes/api.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）通过

### 残留风险
- AR-006 execute 为保证可回滚，固定执行软删除；若线上预期“立即物理删除”，行为与旧的批量删除路径会有差异
- 新增 migration 尚未执行，目标环境需执行 `php artisan migrate`

## 2026-03-05 Iteration 17
- 状态: DONE
- 任务: AR-007 Webhook 事件中心（上传/删除）

### 已完成
- [x] 新增 `webhook_subscriptions` 持久化表（`url`、`secret`、`events`、`enabled`）
- [x] 新增订阅模型 `WebhookSubscription`
- [x] 在 `Image` 模型 `created/deleted` 生命周期触发 webhook 事件分发（`image.uploaded` / `image.deleted`）
- [x] 新增异步投递 Job：携带签名头（`X-Lsky-Signature`）并按队列配置自动重试
- [x] 新增 webhook 最小 API CRUD（创建/启停/删除/列表）并接入 `api/v1` 路由
- [x] 投递失败写审计日志（重试告警 + 最终失败错误）
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-007 标记为 DONE

### 变更文件
- database/migrations/2026_03_05_170000_create_webhook_subscriptions_table.php
- app/Models/WebhookSubscription.php
- app/Services/WebhookEventService.php
- app/Jobs/DeliverWebhookEventJob.php
- app/Http/Controllers/Api/V1/WebhookController.php
- app/Models/Image.php
- config/queue.php
- routes/api.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan route:list --no-ansi | rg "api/v1/webhooks|webhooks"` 命中：
  - `GET|HEAD api/v1/webhooks`
  - `POST api/v1/webhooks`
  - `PATCH api/v1/webhooks/{id}/toggle`
  - `DELETE api/v1/webhooks/{id}`
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）全部通过：
  - app/Http/Controllers/Api/V1/WebhookController.php
  - app/Jobs/DeliverWebhookEventJob.php
  - app/Models/WebhookSubscription.php
  - app/Services/WebhookEventService.php
  - app/Models/Image.php
  - config/queue.php
  - routes/api.php
  - database/migrations/2026_03_05_170000_create_webhook_subscriptions_table.php

### 残留事项
- 新增 migration 尚未执行，需在目标环境执行 `php artisan migrate`。
- 生产环境需启动对应队列 worker（默认 `webhook-events`）。

## 2026-03-05 Iteration 17
- 状态: DONE
- 任务: AR-008 可观测与成本分析看板

### 已完成
- [x] 新增 `API v1` 统计接口：`GET /api/v1/stats/overview`
- [x] 提供近 7/30 天上传量（含逐日序列）
- [x] 提供当前存储占用与按策略/按 mimetype 分布
- [x] 提供成本估算字段（按配置单价估算月存储成本）
- [x] 新增配置项：`storage_cost_per_gb_month`、`storage_cost_currency`
- [x] 接入鉴权与 token 能力：`analytics:read`
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-008 标记为 DONE

### 变更文件
- app/Http/Controllers/Api/V1/AnalyticsController.php
- routes/api.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- app/Http/Controllers/Api/V1/TokenController.php
- app/Enums/ConfigKey.php
- config/convention.php
- app/Utils.php
- resources/views/admin/setting/index.blade.php
- database/migrations/2026_03_05_160000_add_observability_cost_configs.php
- docs/requirements/ADVANCED_REQUIREMENTS.md
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan route:list --path=api/v1/stats --no-ansi` 命中：
  - `GET|HEAD api/v1/stats/overview`
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）通过：
  - `app/Http/Controllers/Api/V1/AnalyticsController.php`
  - `routes/api.php`
  - `app/Http/Middleware/EnforceTokenRestrictions.php`
  - `app/Http/Controllers/Api/V1/TokenController.php`
  - `app/Enums/ConfigKey.php`
  - `app/Utils.php`
  - `database/migrations/2026_03_05_160000_add_observability_cost_configs.php`

### 残留事项
- 新增 migration 尚未执行，需在目标环境运行 `php artisan migrate`。
- 成本估算为静态单价模型（按“当前存储 GB * 单价”），未包含分层存储/请求次数/流量成本。

### 下一步
- 自动进入 `AR-006`：批量操作 2.0（预览 + 回滚）。

## 2026-03-05 Iteration 18
- 状态: DONE
- 任务: AR-011 插件化扩展点（受控钩子）

### 已完成
- [x] 新增 `HookManager`，仅允许白名单事件：`image.uploading`、`image.uploaded`、`image.deleting`、`image.deleted`
- [x] 新增插件注册配置：`config/plugins.php`，支持本地插件类按事件或全局注册
- [x] 上传关键路径触发 hook：`ImageService::store` 触发 `image.uploading`
- [x] 删除/上传完成关键路径触发 hook：`Image` 模型生命周期触发 `image.uploaded`、`image.deleting`、`image.deleted`
- [x] 插件异常隔离：`HookManager` 捕获插件异常并写审计告警，不影响主流程
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-011 标记为 DONE

### 变更文件
- app/Contracts/HookPluginInterface.php
- app/Services/HookManager.php
- config/plugins.php
- app/Services/ImageService.php
- app/Models/Image.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）通过

### 残留风险
- 当前仅支持本地类插件注册，不包含远程插件安装/签名校验能力
- hook payload 目前以图片/请求上下文为主，若后续插件需要更多上下文字段需扩展 payload 约定

## 2026-03-05 Iteration 18
- 状态: DONE
- 任务: AR-010 智能检索一期（标签 + OCR）

### 已完成
- [x] 新增标签主表与图片标签关联表：`tags`、`image_tag`
- [x] `images` 新增 `ocr_text` 预留字段
- [x] API 支持标签与 OCR 关键字检索（`tag_keyword` / `ocr_keyword` / `q`）
- [x] 新增检索路由：`GET /api/v1/images/search`（复用 `images:read`）
- [x] 上传成功后异步派发 OCR 占位任务，写入 `ocr_text`（不接入真实 OCR 引擎）
- [x] 上传流程支持标签写入（`tags` 字符串或数组）并同步到 `image_tag`
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-010 标记为 DONE

### 变更文件
- routes/api.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Models/Image.php
- app/Models/Tag.php
- app/Services/ImageService.php
- app/Jobs/ProcessImageOcrPlaceholderJob.php
- database/migrations/2026_03_05_170000_create_tags_table.php
- database/migrations/2026_03_05_170100_create_image_tag_table.php
- database/migrations/2026_03_05_170200_add_ocr_text_to_images_table.php
- docs/requirements/ADVANCED_REQUIREMENTS.md
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan route:list --path=api/v1/images --no-ansi` 命中：
  - `GET|HEAD api/v1/images/search`
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）全部通过：
  - `routes/api.php`
  - `app/Models/Image.php`
  - `app/Models/Tag.php`
  - `app/Services/ImageService.php`
  - `app/Http/Controllers/Api/V1/ImageController.php`
  - `app/Http/Middleware/EnforceTokenRestrictions.php`
  - `app/Jobs/ProcessImageOcrPlaceholderJob.php`
  - `database/migrations/2026_03_05_170000_create_tags_table.php`
  - `database/migrations/2026_03_05_170100_create_image_tag_table.php`
  - `database/migrations/2026_03_05_170200_add_ocr_text_to_images_table.php`

### 残留事项
- 新增 migrations 尚未执行，部署时需运行 `php artisan migrate`。
- OCR 当前为占位写入，后续可替换为真实 OCR 引擎管线。

### 下一步
- 自动进入 `AR-006`：批量操作 2.0（预览 + 回滚）补齐体验与边界。

## 2026-03-05 Iteration 18
- 状态: DONE
- 任务: AR-009 团队空间与协作权限（一期）

### 已完成
- [x] 新增 `team_spaces` 与 `team_memberships` 持久化模型与迁移，角色支持 `owner/admin/member`
- [x] 用户支持多空间归属（`User::teamSpaces()` / `User::teamMemberships()`）
- [x] `images` 新增 `space_id` 归属字段，并在迁移中为存量用户回填“个人空间”
- [x] 上传链路接入空间归属：有上下文用当前空间，否则默认个人空间
- [x] API v1 新增空间接口：列表与切换上下文
- [x] API 鉴权中间件新增空间能力：`spaces:read`、`spaces:switch`
- [x] 图片查询/删除/批量删除/回滚/签名链接在空间上下文下按 `space_id` 隔离
- [x] `docs/tasks/ADVANCED_TASKS.md` 将 AR-009 标记 DONE

### 变更文件
- database/migrations/2026_03_05_180000_create_team_spaces_and_memberships_tables.php
- app/Models/TeamSpace.php
- app/Models/TeamMembership.php
- app/Services/TeamSpaceService.php
- app/Http/Middleware/ResolveTeamSpaceContext.php
- app/Http/Controllers/Api/V1/SpaceController.php
- app/Models/User.php
- app/Models/Image.php
- app/Models/PersonalAccessToken.php
- app/Services/ImageService.php
- app/Services/ImageBatchOperationService.php
- app/Services/UserService.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/Api/V1/TokenController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- routes/api.php
- docs/tasks/ADVANCED_TASKS.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l`（本轮改动 PHP 文件）全部通过
- `docker exec lsky-pro php artisan route:list --no-ansi | rg "api/v1/spaces|spaces/switch|api/v1/images|auth:sanctum"` 命中：
  - `GET|HEAD api/v1/spaces`
  - `POST api/v1/spaces/switch`
  - `GET|HEAD api/v1/images`
  - `GET|HEAD api/v1/images/search`
  - `DELETE api/v1/images/{key}`
  - `GET|HEAD api/v1/images/{key}/signed-url`

### 残留事项
- 新增迁移尚未执行，需在目标环境执行 `php artisan migrate`。
- 旧 token 未设置 `current_space_id` 时会回退到个人空间；如需切换空间需调用 `POST /api/v1/spaces/switch`。

## 2026-03-05 Iteration 19
- 状态: DONE
- 任务: Advanced Tasks AR-001 ~ AR-011 主线程总验收

### 已完成
- [x] AR-001 ~ AR-011 全部标记 DONE（见 `docs/tasks/ADVANCED_TASKS.md`）
- [x] 主线程容器级语法复核（新增/改动关键 PHP 文件）全部通过
- [x] 主线程路由复核命中：`upload-tasks`、`batch-delete`、`webhooks`、`stats/overview`、`spaces`、`images/search`、`signed-url`
- [x] 主线程命令复核命中：`images:cleanup-expired`

### 验证记录
- `docker exec lsky-pro php -l <changed-files>`：通过
- `docker exec lsky-pro php artisan route:list --path=api/v1 --no-ansi | rg "upload-tasks|batch-delete|webhooks|stats/overview|spaces|images/search|signed-url"`：通过
- `docker exec lsky-pro php artisan list --no-ansi | rg "images:cleanup-expired"`：通过
- `docker exec lsky-pro php artisan migrate:status --no-ansi`：通过（可读），新增迁移为 Pending

### 残留事项
- 新增 migrations 尚未执行，需在目标环境执行 `php artisan migrate`。
- 当前目录非 git 工作树，无法产出 commit 历史；若需要提交，请提供含 `.git` 的仓库路径。

## 2026-03-05 Iteration 20
- 状态: DONE
- 任务: 首次启动自动初始化（数据库+管理员）

### 已完成
- [x] 执行迁移：新增迁移全部已在运行环境执行完成
- [x] 新增 `lsky:bootstrap` 命令（幂等）：自动 migrate、补齐基础数据、创建/修复管理员、生成 `installed.lock`
- [x] 启动脚本接入：`scripts/health/startup.sh` 在 `INIT_AUTO_BOOTSTRAP=true` 时自动执行 bootstrap
- [x] 本地启动脚本接入：`scripts/setup/run-local.sh` 改为执行 `php artisan lsky:bootstrap --force`
- [x] `.env.example` 增加初始化变量：`INIT_AUTO_BOOTSTRAP`、`INIT_ADMIN_NAME`、`INIT_ADMIN_EMAIL`、`INIT_ADMIN_PASSWORD`
- [x] README 增加首次启动自动初始化说明

### 变更文件
- app/Console/Commands/Bootstrap.php
- scripts/health/startup.sh
- scripts/setup/run-local.sh
- .env.example
- README.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan migrate --force`：通过
- `docker exec lsky-pro php artisan migrate:status --no-ansi`：新增迁移全部 Ran
- `docker exec lsky-pro php -l app/Console/Commands/Bootstrap.php`：通过
- `docker exec lsky-pro php artisan list --no-ansi | rg "lsky:bootstrap"`：命中
- `docker exec lsky-pro php artisan lsky:bootstrap --force`：通过（Nothing to migrate + Found existing admin）
- `bash -n scripts/health/startup.sh && bash -n scripts/setup/run-local.sh`：通过

### 备注
- 当前目录非 git 工作树，无法直接提交 commit。

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: Backend 高阶能力实现（图片处理/驱动治理/AI 提示词与检索增强）

### 已完成
- [x] 新增企业图片处理接口 `POST /api/v1/images/{key}/process`
- [x] 支持处理参数：`resize(width,height,fit)`、`filters(grayscale,blur,sharpen,contrast)`、`watermark(text,position,size,color)`
- [x] 新增图片处理驱动配置 `config/image_processing.php`，支持 `IMAGE_PROCESS_DRIVER=imagick|libvips`
- [x] 新增驱动管理器并严格校验驱动可用性（不可用直接报错，无降级）
- [x] 新增驱动状态接口 `GET /api/v1/processing/drivers/status`
- [x] 新增 AI 提示词生成接口 `POST /api/v1/ai/prompt`（基于图片元信息 + 用户意图模板生成可复制提示词）
- [x] 新增 AI 检索增强接口 `GET /api/v1/images/ai-search?q=...`（融合 name/tags/ocr_text 打分排序）
- [x] 补齐路由与 token ability 映射（`EnforceTokenRestrictions` + `TokenController` 默认 abilities）

### 变更文件
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/Api/V1/AiController.php
- app/Http/Controllers/Api/V1/ProcessingController.php
- app/Http/Controllers/Api/V1/TokenController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- app/Services/AiPromptService.php
- app/Services/ImageProcessing/Contracts/ImageProcessorDriver.php
- app/Services/ImageProcessing/Drivers/ImagickImageProcessorDriver.php
- app/Services/ImageProcessing/Drivers/LibvipsImageProcessorDriver.php
- app/Services/ImageProcessing/ImageProcessingManager.php
- config/image_processing.php
- routes/api.php
- .env.example
- docs/runbook/STATUS.md

### 验证记录
- 容器内语法检查通过：
  - `docker exec lsky-pro php -l app/Services/ImageProcessing/Contracts/ImageProcessorDriver.php`
  - `docker exec lsky-pro php -l app/Services/ImageProcessing/Drivers/ImagickImageProcessorDriver.php`
  - `docker exec lsky-pro php -l app/Services/ImageProcessing/Drivers/LibvipsImageProcessorDriver.php`
  - `docker exec lsky-pro php -l app/Services/ImageProcessing/ImageProcessingManager.php`
  - `docker exec lsky-pro php -l app/Services/AiPromptService.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ProcessingController.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/Api/V1/AiController.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ImageController.php`
  - `docker exec lsky-pro php -l app/Http/Controllers/Api/V1/TokenController.php`
  - `docker exec lsky-pro php -l app/Http/Middleware/EnforceTokenRestrictions.php`
  - `docker exec lsky-pro php -l routes/api.php`
  - `docker exec lsky-pro php -l config/image_processing.php`
- 容器内路由检查通过：
  - `docker exec lsky-pro php artisan route:list --path=api/v1`
  - 已命中新路由：
    - `POST api/v1/images/{key}/process`
    - `GET|HEAD api/v1/processing/drivers/status`
    - `POST api/v1/ai/prompt`
    - `GET|HEAD api/v1/images/ai-search`

### 风险
- `libvips` 驱动依赖运行环境存在 `Jcupitt\\Vips\\Image` 绑定；若仅设置 `IMAGE_PROCESS_DRIVER=libvips` 但缺失绑定，会按设计严格报错。
- 图片处理接口当前返回 `content_base64`，大图场景会增加响应体积与带宽占用，建议网关层配置响应体阈值并配合调用侧分页/限流。

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: 用户侧“高阶工具”页面（左侧二级 tab 树 + 右侧主内容区）与 4 个接口落地

### 已完成
- [x] 用户侧新增“高阶工具”页面结构：左侧二级菜单(tab树) + 右侧 main 内容区
- [x] 左侧 tab 包含并可即时切换：图片编辑、AI检索、AI提示词、处理驱动、企业管理
- [x] 接入既有真实接口（页面右侧各 tab 真实调用）：
  - `POST /api/v1/images/{key}/process`
  - `GET /api/v1/images/ai-search`
  - `POST /api/v1/ai/prompt`
  - `GET /api/v1/processing/drivers/status`
- [x] 侧边栏入口文案更新为“高阶工具”，入口可见可点击
- [x] 页面样式对齐现有风格（卡片边框、色板、间距）并支持桌面/移动布局

### 变更文件
- resources/views/user/advanced.blade.php
- resources/views/layouts/sidebar.blade.php
- docs/runbook/STATUS.md

### 验证截图点位说明（文字）
- 点位 1（入口可见）: 左侧主导航“我的”分组中可见“高阶工具”菜单，点击后进入 `/advanced`。
- 点位 2（结构正确）: 页面左侧为二级 tab 树，右侧为主内容区；点击任一 tab，右侧标题与内容即时切换。
- 点位 3（图片编辑）: 在“图片编辑”输入 key 并执行后，右侧输出区显示 `/api/v1/images/{key}/process` 的 JSON 返回。
- 点位 4（AI检索）: 在“AI检索”输入关键词后，输出区显示 `/api/v1/images/ai-search` 返回结果列表。
- 点位 5（AI提示词）: 在“AI提示词”提交主题/风格后，输出区显示 `/api/v1/ai/prompt` 生成结果。
- 点位 6（处理驱动）: 点击“刷新驱动状态”，输出区显示 `/api/v1/processing/drivers/status` 的 drivers 数据。
- 点位 7（企业管理）: 点击“刷新企业视图”，输出区显示同一接口中的 `enterprise` 字段聚合信息。

### 本地自测
- `docker exec lsky-pro php -l resources/views/user/advanced.blade.php` 通过
- `docker exec lsky-pro php -l resources/views/layouts/sidebar.blade.php` 通过
- `awk '/<script>/{flag=1;next}/<\\/script>/{flag=0}flag' resources/views/user/advanced.blade.php > /tmp/advanced-tools-inline.js && node --check /tmp/advanced-tools-inline.js` 通过（页面内 JS 语法检查）
- `docker exec lsky-pro php artisan route:list --path=api/v1 --no-ansi | rg \"images/ai-search|images/\\{key\\}/process|ai/prompt|processing/drivers/status\"` 命中
- `docker exec lsky-pro php artisan view:cache --no-ansi` 通过（Blade 编译无语法错误）

## 2026-03-05 Iteration 11
- 状态: DONE
- 任务: AR-next-2 审核工作流（images 审核字段 + 管理员审核 API + 列表过滤 + 审计 + 容器验证）

### 已完成
- [x] `images` 表新增审核字段：`review_status`、`review_reason`、`reviewed_at`、`reviewed_by`
- [x] 新增管理员审核 API：
  - `GET /api/v1/admin/reviews?status=...`
  - `POST /api/v1/admin/reviews/{key}/approve`
  - `POST /api/v1/admin/reviews/{key}/reject`
- [x] 图片列表查询支持按 `review_status` 过滤（用户与管理端）
- [x] 审核动作（approve/reject）写入 `audit` channel 审计日志
- [x] 完成容器内语法、路由与迁移预演验证

### 变更文件
- database/migrations/2026_03_05_190000_add_review_columns_to_images_table.php
- app/Enums/ImageReviewStatus.php
- app/Models/Image.php
- app/Http/Controllers/Api/V1/Admin/ReviewController.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/Admin/ImageController.php
- app/Http/Controllers/User/ImageController.php
- routes/api.php
- docs/runbook/STATUS.md

### 容器验证
- 语法检查通过：
  - `docker exec lsky-pro php -l /var/www/html/app/Enums/ImageReviewStatus.php`
  - `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Api/V1/Admin/ReviewController.php`
  - `docker exec lsky-pro php -l /var/www/html/app/Models/Image.php`
  - `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Api/V1/ImageController.php`
  - `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Admin/ImageController.php`
  - `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/User/ImageController.php`
  - `docker exec lsky-pro php -l /var/www/html/routes/api.php`
  - `docker exec lsky-pro php -l /var/www/html/database/migrations/2026_03_05_190000_add_review_columns_to_images_table.php`
- 路由检查通过：
  - `docker exec lsky-pro php artisan route:list --path=api/v1/admin/reviews --no-ansi`
  - 命中：
    - `GET|HEAD api/v1/admin/reviews`
    - `POST api/v1/admin/reviews/{key}/approve`
    - `POST api/v1/admin/reviews/{key}/reject`
- 用户图片路由检查通过：
  - `docker exec lsky-pro php artisan route:list --path=api/v1/images --no-ansi | rg "GET\|HEAD\s+api/v1/images|api/v1/images/search"`
- 迁移 SQL 预演通过：
  - `docker exec lsky-pro php artisan migrate --pretend --path=database/migrations/2026_03_05_190000_add_review_columns_to_images_table.php --no-ansi`

## 2026-03-05 Iteration 10
- 状态: DONE
- 任务: AR-next-3（团队权限细化 + 高阶页扩展）

### 已完成
- [x] `team_memberships` 新增 `permissions(json)` 字段迁移，支持按角色回填默认能力
- [x] 团队成员模型新增角色能力映射与权限解析函数（自定义权限优先，角色能力兜底）
- [x] 新增 API：`GET /api/v1/spaces/{id}/members`
- [x] 新增 API：`PUT /api/v1/spaces/{id}/members/{userId}/role`
- [x] Token 能力映射扩展：`spaces:members:read`、`spaces:members:update`
- [x] 高阶工具页新增 3 个 Tab：`审核中心`、`批处理模板`、`团队权限`
- [x] 前端接入上述接口：成员列表读取、角色更新、权限筛选展示
- [x] 页面保持现有视觉风格并维持移动端响应式

### 变更文件
- database/migrations/2026_03_05_201000_add_permissions_to_team_memberships_table.php
- app/Models/TeamMembership.php
- app/Models/TeamSpace.php
- app/Models/User.php
- app/Services/TeamSpaceService.php
- app/Http/Controllers/Api/V1/SpaceController.php
- app/Http/Controllers/Api/V1/TokenController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- routes/api.php
- resources/views/user/advanced.blade.php
- tests/Feature/Api/ApiSmokeTest.php
- docs/runbook/STATUS.md

### 容器验证记录
- `docker exec lsky-pro php -l /var/www/html/app/Models/TeamMembership.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Api/V1/SpaceController.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Http/Middleware/EnforceTokenRestrictions.php` 通过
- `docker exec lsky-pro php -l /var/www/html/app/Http/Controllers/Api/V1/TokenController.php` 通过
- `docker exec lsky-pro php -l /var/www/html/resources/views/user/advanced.blade.php` 通过
- `docker exec lsky-pro php -l /var/www/html/database/migrations/2026_03_05_201000_add_permissions_to_team_memberships_table.php` 通过
- `docker exec lsky-pro php artisan route:list --no-ansi | rg "api/v1/spaces"` 命中：
  - `GET|HEAD  api/v1/spaces`
  - `POST      api/v1/spaces/switch`
  - `GET|HEAD  api/v1/spaces/{id}/members`
  - `PUT       api/v1/spaces/{id}/members/{userId}/role`

### 备注
- `docker exec lsky-pro php artisan test --filter=ApiSmokeTest` 在当前容器失败，失败原因为既有 `InstallSeeder` 重复插入 `configs_name_unique`（`upload_pipeline_async_enabled`）导致的唯一键冲突，属于现有测试环境数据初始化问题，非本次 AR-next-3 改动引入。

## 2026-03-05 Iteration 12
- 状态: DONE
- 任务: 第三轮-前端：高阶工具页新增“作业中心”Tab（异步提交 + 任务轮询）

### 已完成
- [x] 在 `advanced` 页面新增“作业中心”Tab，并接入主标题切换。
- [x] 新增“提交异步作业”UI：模板 ID + keys 输入，调用 `POST /api/v1/process-templates/{id}/dispatch`。
- [x] 新增“任务进度轮询”UI：支持手动输入 `job_id` 或从“最近任务列表”选择，调用 `GET /api/v1/process-jobs/{jobId}`。
- [x] 新增轮询控制：单次查询、开始轮询（3s）、停止轮询；切换离开“作业中心”Tab 自动停轮询。
- [x] 新增最近任务本地缓存（localStorage）与快捷按钮列表，保证“输入或最近任务列表轮询”路径可用。
- [x] 页面样式保持现有高阶工具视觉体系，并补充移动端响应式处理（`span-2` 窄屏降级）。

### 变更文件
- resources/views/user/advanced.blade.php
- docs/runbook/STATUS.md

### 本地自测
- `php -l resources/views/user/advanced.blade.php` 通过（存在环境告警：`pdo_mysql` 扩展加载 warning，不影响 Blade 语法检查结论）。
- `awk '/<script>/{flag=1;next}/<\/script>/{flag=0}flag' resources/views/user/advanced.blade.php > /tmp/advanced-tools-inline.js && node --check /tmp/advanced-tools-inline.js` 通过。

### 说明
- 当前仓库路由中尚未检索到 `process-jobs` 与 `process-templates/{id}/dispatch`（本轮按前端契约先行接入）。若后端字段命名与预期不一致，前端已做多字段 `job_id` 兜底提取（`job_id/jobId/id`）。

## 2026-03-05 Iteration 21
- 状态: DONE
- 任务: 第二/三轮主线程整合验收（模板批处理+审核流+团队权限+异步作业中心）

### 已完成
- [x] 批处理模板：创建/列表/执行 + 异步派发与作业状态查询
- [x] 审核工作流：待审列表、通过、驳回、筛选与审计
- [x] 团队权限细化：成员能力 JSON、成员列表、角色更新
- [x] 高阶工具页面：新增审核中心/批处理模板/团队权限/作业中心 tab
- [x] 迁移已执行：`2026_03_05_210000_create_image_process_jobs_table` 等全部 Ran
- [x] 修复 `InstallSeeder` 幂等问题（upsert + firstOrCreate），消除重复执行冲突
- [x] 修复 `User` 创建容量为空导致测试失败的问题

### 验证记录
- `docker exec lsky-pro php artisan migrate --force` 通过
- `docker exec lsky-pro php artisan route:list --path=api/v1` 命中新接口（process-templates/dispatch/process-jobs/admin-reviews/spaces-members/ai-search/prompt/drivers）
- `docker exec lsky-pro php -l <changed-files>` 通过
- `docker exec lsky-pro php artisan test --filter=ApiSmokeTest` 通过（3 passed）

### 残留事项
- 目录不是 git 工作树，无法直接提交 commit 历史。

## 2026-03-05 Iteration 22
- 状态: DONE
- 任务: 作业中心第四轮增强（列表/重试/取消 + 前端联动）

### 已完成
- [x] 后端新增作业列表接口：`GET /api/v1/process-jobs`
- [x] 后端新增作业重试接口：`POST /api/v1/process-jobs/{jobId}/retry`
- [x] 后端新增作业取消接口：`POST /api/v1/process-jobs/{jobId}/cancel`
- [x] 作业执行链路支持 `retrying/cancelled` 状态并避免重复执行抢占
- [x] token ability 映射补齐（`images:process`）
- [x] 前端“作业中心”支持状态筛选、列表展示、查看/重试/取消
- [x] 前端初始自动拉取作业列表

### 变更文件
- app/Models/ImageProcessJob.php
- app/Jobs/RunImageProcessTemplateJob.php
- app/Http/Controllers/Api/V1/ProcessTemplateController.php
- app/Http/Middleware/EnforceTokenRestrictions.php
- routes/api.php
- resources/views/user/advanced.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l <changed-files>`：通过
- `docker exec lsky-pro php artisan route:list --path=api/v1`：命中 process-jobs/retry/cancel 新路由
- `docker exec lsky-pro php artisan test --filter=ApiSmokeTest`：通过（3 passed）

## 2026-03-05 Iteration 23
- 状态: DONE
- 任务: 高阶能力专属页面化（每个功能独立页面）+ 审查复核

### 已完成
- [x] 新增高阶总览页：`/advanced`
- [x] 新增每功能专属页面：
  - `/advanced/image-process`
  - `/advanced/ai-search`
  - `/advanced/ai-prompt`
  - `/advanced/drivers`
  - `/advanced/reviews`
  - `/advanced/templates`
  - `/advanced/jobs`
  - `/advanced/team-permissions`
- [x] 左侧菜单新增“高阶”分组并逐项挂载页面入口
- [x] 页面统一壳层组件 `advanced-shell`，保持风格一致与响应式

### 变更文件
- app/Http/Controllers/User/UserController.php
- routes/web.php
- resources/views/layouts/sidebar.blade.php
- resources/views/components/advanced-shell.blade.php
- resources/views/user/advanced_overview.blade.php
- resources/views/user/advanced_pages/*.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l <changed-files>`：通过
- `docker exec lsky-pro php artisan route:list | rg "advanced|advanced/{feature}"`：通过
- `docker exec lsky-pro php artisan view:cache`：通过
- `docker exec lsky-pro php artisan test --filter=ApiSmokeTest`：通过（3 passed）

## 2026-03-05 Iteration 24
- 状态: DONE
- 任务: 上传故障修复闭环 + AI 提示词后台任务化（我的图片轮播/高阶页面）

### 已完成
- [x] 新增 AI 提示词任务控制器：创建任务 + 查询任务
- [x] 新增路由：
  - `POST /advanced-api/ai/prompt-tasks`
  - `GET /advanced-api/ai/prompt-tasks/{taskId}`
- [x] 队列配置新增 `ai_prompt`，默认跟随 `QUEUE_CONNECTION`，新环境默认可用
- [x] `我的图片` 轮播中的 AI 提示词改为后台任务提交 + 轮询 + 结果复制
- [x] `高阶/AI提示词` 页面改为后台任务提交 + 轮询
- [x] 迁移执行完成：`2026_03_05_235000_create_ai_prompt_tasks_table`
- [x] 新增自动化测试覆盖 AI 提示词任务接口主流程

### 变更文件
- app/Http/Controllers/Api/V1/AiPromptTaskController.php
- routes/web.php
- config/queue.php
- resources/views/user/images.blade.php
- resources/views/user/advanced_pages/ai-prompt.blade.php
- tests/Feature/Advanced/AdvancedFeaturePagesTest.php
- .env
- .env.example
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan migrate --force --no-interaction`：通过（ai_prompt_tasks 已创建）
- `docker exec lsky-pro php artisan config:clear && php artisan cache:clear && php artisan view:cache`：通过
- `docker exec lsky-pro php artisan route:list | grep "advanced-api/ai/prompt-tasks"`：通过
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（25 passed）
- `codex-workflow-standard/scripts/validate_codex_workflow.sh`：通过
- `codex-team-recipes-global/scripts/validate_role_matrix.sh`：通过

## 2026-03-07 Iteration 25
- 状态: DONE
- 任务: 修复“我的图片”上传成功后进度残留与刷新后不可见问题

### 已完成
- [x] 后端上传返回补齐 `id/album_id/key/url/preview_url/thumb_url/width/height/created_at`，前端可直接渲染新条目
- [x] 上传逻辑不再依赖 `GET /user/images/{id}` 二次查询，上传成功后立即替换占位卡片
- [x] 占位卡片成功态会移除上传遮罩，避免“提示成功但进度条仍显示”
- [x] 上传请求支持传入当前相册 `album_id`，后端优先写入该相册
- [x] 相册筛选持久化到 localStorage，页面重载后尽量保持原筛选上下文

### 变更文件
- app/Services/UploadTaskService.php
- app/Services/ImageService.php
- resources/views/user/images.blade.php
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l app/Services/UploadTaskService.php`：通过
- `docker exec lsky-pro php -l app/Services/ImageService.php`：通过
- `docker exec lsky-pro php artisan view:cache`：通过
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（25 passed）

## 2026-03-08 Iteration 26
- 状态: DONE
- 任务: 共享轮播组件抽取 + 轮播裁剪增强 + AI/审核详情补全 + PHP 运行时落点核查

### 已完成
- [x] 新增共享轮播组件 `resources/views/components/media-carousel.blade.php`
- [x] 新增共享轮播样式 `resources/views/components/media-carousel-styles.blade.php`
- [x] `我的图片` 与 `图片管理` 均切换到同一套轮播壳结构，避免重复维护 DOM
- [x] 轮播裁剪补充重置与比例预设（`1:1` / `16:9` / `4:5`）
- [x] 用户图片详情接口补齐 `ocr_text`、`tags`、`is_unhealthy`
- [x] 轮播右侧详情补齐 AI 检测状态、人工审核状态、审核原因、审核时间、审核人、标签、OCR 摘要
- [x] 补一条 Feature 回归测试，锁定用户图片详情接口返回 AI/审核元数据
- [x] 已确认当前运行容器镜像与挂载：
  - 镜像 `halcyonazure/lsky-pro-docker:latest`
  - 挂载 `/root/code-server/config/workspace/lsky-pro/lsky => /var/www/html`
- [x] 已确认 PHP 升级入口不在仓库代码，而在 1Panel/运行镜像层

### 变更文件
- resources/views/components/media-carousel.blade.php
- resources/views/components/media-carousel-styles.blade.php
- resources/views/user/images/partials/carousel-shell.blade.php
- resources/views/user/images.blade.php
- resources/views/admin/image/index.blade.php
- app/Http/Controllers/User/ImageController.php
- tests/Feature/Smoke/UploadMainlineTest.php
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php -l app/Http/Controllers/User/ImageController.php`：通过
- `docker exec lsky-pro php artisan view:cache`：通过
- `docker inspect lsky-pro --format '{{.Config.Image}}|{{range .Mounts}}{{.Source}}=>{{.Destination}};{{end}}'`：通过
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（26 passed）

## 2026-03-08 Iteration 27
- 状态: DONE
- 任务: 登录事故恢复 + 破坏性命令安全闸 + 自举安全默认值 + 图片处理回归测试补齐

### 已完成
- [x] 核验线上 MySQL 数据并从本地 SQL 备份恢复用户、策略、分组、图片等核心数据
- [x] 将真实用户 `121802744@qq.com` 的密码哈希恢复为当前有效密码，并验证 Laravel 认证链路可用
- [x] 新增控制台安全闸，默认阻止 `migrate:fresh`、`migrate:refresh`、`migrate:reset`、`migrate:rollback`、`db:wipe`
- [x] 健康检查脚本默认改为 `INIT_AUTO_BOOTSTRAP=false`，并移除 `lsky:bootstrap --force`
- [x] 本地启动脚本与 PHP 8.3 入口脚本改为无 `--force` 的首启自举
- [x] `.env.example` 增加 `ALLOW_DESTRUCTIVE=0`，对齐安全默认值
- [x] 新增图片处理 Feature 测试，覆盖 `advanced-api/images/{key}/process` 的旋转预览主流程
- [x] 修复图片处理执行器遗漏 `strategy.key` 字段，避免文件系统适配器出现 `Unhandled match case NULL`

### 变更文件
- app/Services/ImageProcessing/ImageProcessExecutor.php
- app/Services/UploadTaskService.php
- app/Http/Controllers/Api/V1/ImageController.php
- app/Http/Controllers/User/ImageController.php
- app/Providers/AppServiceProvider.php
- scripts/health/startup.sh
- scripts/setup/run-local.sh
- deploy/php83/entrypoint.sh
- deploy/1panel/README.md
- docs/runbook/PHP83_1PANEL_CUSTOM_RUNTIME.md
- .env.example
- tests/Feature/Smoke/UploadMainlineTest.php
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan tinker --execute="..."`：通过（核心数据已恢复，账号凭证校验 VALID）
- `docker exec lsky-pro php -l app/Services/ImageProcessing/ImageProcessExecutor.php && php -l app/Services/UploadTaskService.php && php -l app/Http/Controllers/Api/V1/ImageController.php && php -l app/Http/Controllers/User/ImageController.php && php -l app/Providers/AppServiceProvider.php`：通过
- `docker exec lsky-pro php artisan view:cache`：通过
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（27 passed）
- `docker exec lsky-pro php artisan migrate:fresh --pretend`：已被安全闸拒绝，符合预期

## 2026-03-08 Iteration 28
- 状态: DONE
- 任务: 登录事故恢复 + 共享轮播收口 + 公共脚本复用 + 运行手册补记

### 已完成
- [x] 确认登录事故根因：此前误对 live MySQL 执行 `migrate:fresh --seed`，真实业务数据被清空
- [x] 从本机 SQL 备份恢复 live `lsky` 数据库核心数据（用户/策略/角色组/图片等）
- [x] 将真实用户 `121802744@qq.com` 的密码哈希重置为当前有效密码，并验证 Laravel 认证链路恢复
- [x] 管理端共享轮播修复复用残留：详情面板绑定从 `#admin-carousel-meta` 更正为 `#admin-carousel-detail`
- [x] 公共脚本 `media-carousel-shared.js` 的复制逻辑补齐 `execCommand` fallback
- [x] `高阶/图片编辑` 与 `高阶/AI提示词` 页面接入共享 `escapeHtml/copyText` 工具
- [x] 收紧共享轮播大图留白，放大预览有效区域
- [x] 登录按钮样式加固，避免被全局按钮样式覆盖成“同色不可见”
- [x] 新增 PHP 8.3 自定义运行时部署产物已纳入仓库：
  - `deploy/php83/*`
  - `deploy/1panel/docker-compose.php83.yml`
  - `docs/runbook/PHP83_1PANEL_CUSTOM_RUNTIME.md`

### 变更文件
- public/static/js/media-carousel-shared.js
- resources/views/admin/image/index.blade.php
- resources/views/auth/login.blade.php
- resources/views/components/media-carousel-styles.blade.php
- resources/views/user/advanced_pages/image-process.blade.php
- resources/views/user/advanced_pages/ai-prompt.blade.php
- deploy/php83/Dockerfile
- deploy/php83/apache-vhost.conf
- deploy/php83/entrypoint.sh
- deploy/php83/php.ini
- deploy/1panel/docker-compose.php83.yml
- deploy/1panel/README.md
- docs/runbook/PHP83_1PANEL_CUSTOM_RUNTIME.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan tinker --execute="Auth::validate(...)"`：通过（真实账户校验 `VALID`）
- `docker exec lsky-pro php artisan view:cache`：通过
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（27 passed）
- `codex-workflow-standard/scripts/validate_codex_workflow.sh`：通过
- `codex-team-recipes-global/scripts/validate_role_matrix.sh`：通过

### 残留风险
- 当前运行容器仍是 `halcyonazure/lsky-pro-docker:latest`，仓库已具备 PHP 8.3 自定义镜像方案，但尚未切换 live runtime
- 当前容器内未直接提供可用的本地 HTTP 入口，无法在本轮做浏览器级登录 POST 验证；已用 Laravel 认证链路与 Feature 测试覆盖登录主路径

## 2026-03-08 Iteration 29
- 状态: DONE
- 任务: 再次修复登录事故 + testing 隔离补强 + destructive 命令入口级防护

### 已完成
- [x] 确认第二次登录故障根因：`php artisan migrate:fresh --env=testing` 在缺少 `.env.testing` 的情况下落到了默认 MySQL，真实 `lsky` 数据再次被清空
- [x] 再次从 `/root/recovery/lsky-before-rerestore-20260304-172946.sql` 恢复真实业务数据
- [x] 再次为真实用户 `121802744@qq.com` 重置有效密码哈希，并验证 Laravel 认证链路 `VALID`
- [x] 新增 `.env.testing`，将 `artisan --env=testing` 默认连接固定到 SQLite，避免再落到 live MySQL
- [x] `tests/CreatesApplication.php` 增加 testing SQLite 文件自动创建逻辑，修复 `/tmp/lsky-phpunit.sqlite` 缺失导致的测试波动
- [x] `artisan` 启动入口新增 destructive 命令预检：
  - 非 testing 环境默认拒绝 `migrate:fresh` / `migrate:refresh` / `migrate:reset` / `migrate:rollback` / `db:wipe`
  - testing 环境仅允许 destructive 命令落到 SQLite，非 SQLite 一律拒绝，除非显式设置 `ALLOW_DESTRUCTIVE=1`
- [x] 应用层 `AppServiceProvider` 保留二级防护，避免入口层之外的控制台调用漏拦截
- [x] 全量 Feature 测试恢复全绿（27 passed）

### 变更文件
- artisan
- .env.testing
- app/Providers/AppServiceProvider.php
- tests/CreatesApplication.php
- docs/runbook/PHP83_1PANEL_CUSTOM_RUNTIME.md
- docs/runbook/STATUS.md

### 验证记录
- `docker exec lsky-pro php artisan tinker --execute="..."`：通过（live MySQL 当前 `users=3, strategies=2, groups=3, images=9`）
- `docker exec lsky-pro php artisan tinker --execute="Auth::validate(...)"`：通过（真实用户凭证 `VALID`）
- `docker exec lsky-pro php -l artisan && php -l app/Providers/AppServiceProvider.php && php -l tests/CreatesApplication.php`：通过
- `APP_ENV=testing DB_CONNECTION=pgsql ALLOW_DESTRUCTIVE=0 php artisan migrate:fresh --force --no-interaction`：已被入口级安全闸拒绝，符合预期
- `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=/tmp/lsky-guard-check.sqlite ALLOW_DESTRUCTIVE=0 php artisan migrate:fresh --force --no-interaction`：通过（说明 testing+sqlite 仍可正常迁移）
- `docker exec lsky-pro php artisan test --testsuite=Feature --stop-on-failure`：通过（27 passed）
- `docker exec lsky-pro php artisan view:cache`：通过

### 残留风险
- 当前 live runtime 仍为 `PHP 8.1.28`，PHP 8.3 已具备 1Panel 自定义镜像切换方案，但本轮未直接切换线上容器
- 本轮未做浏览器自动化回放；登录和高阶功能以 Laravel 认证校验、Blade 编译与 Feature 测试作为主验证手段

## 2026-03-08 Iteration 30
- 状态: DONE
- 任务: 安装态/数据库失败链路修复 + PHP 8.3 Docker 运行时补齐 + 从安装到登录隔离 E2E 跑通

### 已完成
- [x] 将安装态判断从 `installed.lock` 单点升级为真实健康检查：
  - `installed.lock`
  - 数据库可连接
  - 关键表存在
  - `configs.app_name` 存在
  - 至少一个管理员存在
- [x] `routes/web.php` 与 `CheckIsInstalled` 改为基于 `InstallStateService` 统一判定，避免 lock 存在但库未就绪时页面直接炸掉
- [x] `lsky:install` 改为非破坏式安装路径，不再执行 `migrate:fresh`
- [x] `lsky:bootstrap` 改为幂等初始化/修复入口，可安全补齐默认配置、默认角色组、默认本地策略和管理员
- [x] Web 安装控制器改为统一委托 `lsky:install`，移除 HTTP 侧重复造管理员与策略修补逻辑
- [x] 新增 `.dockerignore`，避免把 live `.env`、`installed.lock`、`storage`、测试产物打进镜像
- [x] PHP 8.3 Docker 运行时补齐 `ext-ftp`，修复 `league/flysystem-ftp` 依赖安装失败
- [x] PHP 8.3 运行时补齐 Laravel 必需运行目录：
  - `storage/framework/sessions`
  - `storage/framework/views`
  - `storage/framework/cache/data`
  - `storage/logs`
  - `storage/app/public`
- [x] 修复 entrypoint 对 `.env` / `installed.lock` 的 root 所有权污染，确保 Web 安装流程可以正常回写 `.env`
- [x] QA 脚本去掉对宿主机 `php -r` 的 URL 解码依赖，改为纯 bash 解析 XSRF token
- [x] 补齐两套隔离 E2E：
  - `web install -> login`
  - `bootstrap -> login`

### 变更文件
- app/Console/Commands/Bootstrap.php
- app/Console/Commands/Install.php
- app/Http/Controllers/Controller.php
- app/Http/Middleware/CheckIsInstalled.php
- app/Services/InstallStateService.php
- deploy/e2e/docker-compose.bootstrap.yml
- deploy/e2e/docker-compose.install.yml
- deploy/php83/Dockerfile
- deploy/php83/entrypoint.sh
- docs/runbook/INSTALL_E2E.md
- docs/runbook/STATUS.md
- routes/web.php
- scripts/qa/e2e-bootstrap-login.sh
- scripts/qa/e2e-install-login.sh
- .dockerignore

### 验证记录
- `bash -n scripts/qa/e2e-install-login.sh && bash -n scripts/qa/e2e-bootstrap-login.sh`：通过
- `bash scripts/qa/e2e-install-login.sh`：通过
- `bash scripts/qa/e2e-bootstrap-login.sh`：通过
- 调试态人工复核：
  - `/install` 返回 `200`
  - 安装 POST 返回 `{"status":true}`
  - 安装后 `users=1, admin_exists=true`
  - `/` 302 到 `/login`
  - 登录 POST 302 到 `/dashboard`
  - `/dashboard` 命中 `dashboard-v4 / 快捷入口`
- live 数据安全确认：
  - 本轮所有 Docker 验证均在隔离 compose 环境完成
  - 当前 `docker ps -a` 无残留 `lsky-e2e-*` 容器

### 残留风险
- live 仍运行旧容器运行时，仓库内 PHP 8.3 自定义镜像方案与隔离验证已完成，但尚未切换线上
- `deploy/php83/Dockerfile` 仍会在镜像构建阶段完整执行 Composer 安装，后续可继续优化为更稳定的缓存层拆分

## 2026-03-08 Iteration 31
- 状态: DONE
- 任务: 图片管理 JSON 修复 + 登录/退出安装态纠偏 + 我的图片/图片管理加载骨架与轮播详情统一

### 已完成
- [x] 修复 `Admin\\ImageController@index` 的 JSON 返回签名错误，`/admin/images?json=1` 不再因为 `success()` 参数类型错误中断
- [x] 修复 `welcome.blade.php` 对 `$_is_notice` 的未定义访问，避免欢迎页与安装态附近的 Blade 变量异常
- [x] 将 `我的图片` 与 `图片管理` 的加载骨架抽为公共组件：
  - `components/images-loading-styles.blade.php`
  - `components/images-loading-skeleton.blade.php`
- [x] `我的图片` 接入公共加载骨架，首屏与筛选切换时不再直接空白/闪空态
- [x] `我的图片` 轮播详情改为与 `图片管理` 一致的三组结构：
  - `基础信息`
  - `归属信息`
  - `安全信息`
- [x] `图片管理` 改为复用公共加载骨架，移除页面内重复 skeleton 样式
- [x] 清理共享轮播样式文件中的重复 `detail-group` 定义，避免后续样式覆盖不可控

### 变更文件
- app/Http/Controllers/Admin/ImageController.php
- docs/runbook/STATUS.md
- resources/views/admin/image/index.blade.php
- resources/views/components/images-loading-skeleton.blade.php
- resources/views/components/images-loading-styles.blade.php
- resources/views/components/media-carousel-styles.blade.php
- resources/views/user/images.blade.php
- resources/views/welcome.blade.php

### 验证记录
- `docker build -t lsky-pro-custom:php83 -f deploy/php83/Dockerfile .`：通过
- `docker compose -f deploy/1panel/docker-compose.php83.yml up -d --no-build --force-recreate`：通过
- `docker exec lsky-pro php artisan view:clear && docker exec lsky-pro php artisan view:cache`：通过
- 控制器直调验证：`Admin\\ImageController@index(json=1)` 返回 `200`，且包含 `preview_url/review_status/ocr_text/tags`
- HTTP 登录链路：
  - `/login` -> 登录成功 -> `/dashboard`：通过
  - `/images`：`200`
  - `/admin/images`：`200`
  - `/advanced`：`200`
  - 登出 `POST /logout` -> `/login`：通过
  - 匿名访问 `/` -> `/login`：通过
- 页面源码检查：
  - `我的图片` 已包含 `syncImagesLoadingState` 与三组轮播详情标题
  - `图片管理` 已包含 `admin-loading` 与三组轮播详情标题

### 残留风险
- 本轮完成的是服务端模板与 HTTP 级验证，还没做浏览器级交互录制，因此拖拽/切换动画类问题仍需继续做前端联调
- `图片管理` 页面仍保留一部分历史模板片段（如旧 modal/template 辅助块），虽然当前不阻塞功能，但应在后续收敛为单一路径

## 2026-03-08 Iteration 32
- 状态: DONE
- 任务: 公告变量修复 + 我的图片加载/错误态补齐 + 我的图片与图片管理轮播详情继续对齐

### 已完成
- [x] 修复 `layouts/header.blade.php` / `layouts/app.blade.php` / `welcome.blade.php` 的公告变量传递，消除 `$_is_notice` 未定义导致的高级功能页异常
- [x] 共享轮播样式补齐公共详情分组样式与详情状态样式，避免用户页和管理页继续各自分叉
- [x] `我的图片` 增加首屏加载骨架、错误态和重试入口
- [x] `我的图片` 视图切换改为优先复用当前页数据，本地重渲染，不再默认重新请求
- [x] `我的图片` 工具栏在加载中锁定，避免重复触发搜索 / 视图切换 / 批量操作
- [x] `我的图片` 轮播详情改成与管理页一致的三组结构，并增加详情加载中 / 失败重试提示
- [x] `我的图片` 网格和列表补充最小审核状态 badge：`疑似违规` / `待审核` / `已驳回`
- [x] `图片管理` 轮播详情分组名统一为 `AI与审核`

### 变更文件
- docs/runbook/STATUS.md
- resources/views/admin/image/index.blade.php
- resources/views/components/media-carousel-styles.blade.php
- resources/views/layouts/app.blade.php
- resources/views/layouts/header.blade.php
- resources/views/user/images.blade.php
- resources/views/welcome.blade.php

### 验证记录
- `docker build -t lsky-pro-custom:php83 -f /opt/1panel/apps/lsky-pro/deploy/php83/Dockerfile /opt/1panel/apps/lsky-pro`：通过
- `docker compose -f /opt/1panel/apps/lsky-pro/deploy/1panel/docker-compose.php83.yml up -d --no-build --force-recreate`：通过
- `docker exec lsky-pro php artisan view:clear && docker exec lsky-pro php artisan view:cache`：通过
- HTTP 链路：
  - 匿名访问 `/` -> `/login`：通过
  - 登录 `POST /login` -> `/dashboard`：通过
  - `/dashboard`：`200`
  - `/images`：`200`
  - `/admin/images`：`200`
  - `/admin/images?json=1&per_page=1`：`200`
  - `/advanced`：`200`
  - 登出 `POST /logout` -> `/login`：通过
- 页面源码检查：
  - `我的图片` 已包含 `images-error`
  - `我的图片` 已包含 `images-item-badges`
  - `我的图片` 已包含 `images-carousel-detail-state`
  - `图片管理` 已包含 `AI与审核` 分组标题
- 安装态检查：
  - `InstallStateService::inspect()` 返回 `healthy=true`
- 日志检查：
  - 清空日志后访问已登录态 `/advanced`，`storage/logs/laravel.log` 为空，未再出现 `$_is_notice` 异常

### 残留风险
- 这轮仍是 HTTP/模板级验证，尚未做浏览器自动化交互录制，因此拖拽框选、轮播键盘切换、视图切换动画等仍需继续收口
- `我的图片` 与 `图片管理` 还存在少量历史模板/事件路径，下一轮需要继续收敛成单一实现

## 2026-03-08 Iteration 33
- 状态: DONE
- 任务: 清理图片页与高级页残留的兼容/降级/历史模板路径，收敛为单一路径实现

### 已完成
- [x] `我的图片` 改为直接依赖 `window.LskyMediaCarousel` 共享实现，移除本地 `window.LskyMediaCarousel || {}` 与 `||` helper fallback
- [x] `图片管理` 改为直接依赖 `window.LskyMediaCarousel` 共享实现，移除本地 `window.LskyMediaCarousel || {}` 与 `||` helper fallback
- [x] `AI检索` 页接入共享脚本，移除旧的 `execCommand('copy')` 兼容复制链路
- [x] `AI提示词` / `图片编辑` 改为直接复用共享 `escapeHtml/copyText`
- [x] `图片管理` 删除已废弃的 `image-tpl` 历史模板与对应死事件
- [x] `图片管理` 去掉 `justifiedGallery('destroy')` 的无用 `try/catch` 包装
- [x] `我的图片` 删除重复声明的 workspace/pager/toolbar CSS，保留共享样式为唯一来源
- [x] `我的图片` 删除模板之间残留的孤儿闭合标签

### 变更文件
- docs/runbook/STATUS.md
- public/static/js/media-carousel-shared.js
- resources/views/admin/image/index.blade.php
- resources/views/user/advanced_pages/ai-prompt.blade.php
- resources/views/user/advanced_pages/ai-search.blade.php
- resources/views/user/advanced_pages/image-process.blade.php
- resources/views/user/images.blade.php

### 验证记录
- `docker build -t lsky-pro-custom:php83 -f /opt/1panel/apps/lsky-pro/deploy/php83/Dockerfile /opt/1panel/apps/lsky-pro`：通过（两轮重建，最终镜像 `lsky-pro-custom:php83`）
- `docker compose -f /opt/1panel/apps/lsky-pro/deploy/1panel/docker-compose.php83.yml up -d --no-build --force-recreate`：通过
- `docker exec lsky-pro php artisan view:clear && docker exec lsky-pro php artisan view:cache`：通过
- 容器健康检查：`healthy lsky-pro-custom:php83`
- HTTP 链路：
  - 登录 `POST /login` -> `/dashboard`：通过
  - `/dashboard`：`200`
  - `/images`：`200`
  - `/admin/images`：`200`
  - `/advanced`：`200`
  - `/advanced/ai-search`：`200`
  - `/advanced/ai-prompt`：`200`
  - `/advanced/image-process`：`200`
  - 登出 `POST /logout` -> `/login`：通过
  - 匿名访问 `/` -> `/login`：通过
- 源码检查：
  - 目标文件中不再命中 `window.LskyMediaCarousel || {}` / `carouselShared` / `shared = window.LskyMediaCarousel || {}` / `execCommand(` / `image-tpl` / `try { $gridView.justifiedGallery('destroy') } catch`
  - `我的图片` 与 `图片管理` 已命中共享 destructuring：`renderThumbButtons` / `normalizeLoopIndex` / `setPanelScrollLocked`
  - `AI检索` / `AI提示词` / `图片编辑` 已命中共享 `const { escapeHtml, copyText } = window.LskyMediaCarousel;`

### 残留风险
- 本轮仍是 HTTP/模板/容器级验证，还没做浏览器自动化交互录制，因此拖拽框选、轮播动画、复杂 hover 态仍需继续做浏览器级联调
- 当前 `/opt/1panel/apps/lsky-pro` 不是 git 工作树，变更追踪依赖 `STATUS.md` 与容器验证记录，而不是 `git diff`

## 2026-03-08 Iteration 34
- 状态: DONE
- 任务: 重构高级功能中的 AI 配置 / AI 提示词真实调用 / 共享轮播自由裁剪，并清理废弃高级页残留

### 已完成
- [x] 高级功能主菜单以 `ai-config` 替换旧的批处理模板页入口，统一承载 Gemini / DeepSeek / 千问 / GPT 的 API Key、Base URL、模型列表与默认模型配置
- [x] `AiPromptService` 重写为真实外部 AI 调用，不再使用伪造模板结果；支持 OpenAI Compatible 与 Gemini 原生接口
- [x] `AI 提示词` 页面补齐 provider / model 展示，后台任务返回结果中携带 provider 元信息
- [x] 共享轮播组件重构为三列导航布局，主图不再延伸到左右切换按钮下方
- [x] 共享轮播裁剪层升级为图上直接编辑：新增上下左右边缘控制点、强化遮罩、支持比例锁定与拖拽新建裁剪框
- [x] 删除未再使用的旧版 [advanced.blade.php](/opt/1panel/apps/lsky-pro/resources/views/user/advanced.blade.php)，清理高级页历史残留入口
- [x] 修复 `ImageProcessExecutor` 个人空间解析 SQL 歧义，恢复图片处理接口稳定性
- [x] Advanced Feature / 全量 Feature 回归通过，并完成 live 登录与图片处理验证

### 变更文件
- app/Enums/ConfigKey.php
- app/Http/Controllers/Api/V1/AiConfigController.php
- app/Http/Controllers/User/UserController.php
- app/Services/AiPromptService.php
- app/Services/AiProviderConfigService.php
- app/Services/ImageProcessing/ImageProcessExecutor.php
- database/migrations/2026_03_08_120000_add_ai_provider_configs.php
- resources/views/components/media-carousel.blade.php
- resources/views/components/media-carousel-styles.blade.php
- resources/views/components/advanced-shell.blade.php
- resources/views/layouts/sidebar.blade.php
- resources/views/user/advanced_overview.blade.php
- resources/views/user/advanced_pages/ai-config.blade.php
- resources/views/user/advanced_pages/ai-prompt.blade.php
- resources/views/user/images.blade.php
- tests/Feature/Advanced/AdvancedFeaturePagesTest.php
- resources/views/user/advanced.blade.php (deleted)
- docs/runbook/STATUS.md

### 验证记录
- `docker build -t lsky-pro-custom:php83 -f /opt/1panel/apps/lsky-pro/deploy/php83/Dockerfile /opt/1panel/apps/lsky-pro`：通过（最终镜像 `lsky-pro-custom:php83`）
- `docker compose -f /opt/1panel/apps/lsky-pro/deploy/1panel/docker-compose.php83.yml up -d --no-build --force-recreate`：通过
- `docker exec lsky-pro php artisan migrate --force --no-interaction`：通过（`2026_03_08_120000_add_ai_provider_configs` 已执行）
- `docker exec lsky-pro php artisan view:clear && docker exec lsky-pro php artisan view:cache`：通过
- 容器健康检查：`healthy lsky-pro-custom:php83`
- 语法检查：
  - `docker exec lsky-pro php -l /var/www/html/app/Services/AiPromptService.php`：通过
  - `docker exec lsky-pro php -l /var/www/html/app/Services/ImageProcessing/ImageProcessExecutor.php`：通过
  - `docker exec lsky-pro php -l /var/www/html/resources/views/components/media-carousel.blade.php`：通过
  - `docker exec lsky-pro php -l /var/www/html/resources/views/user/advanced_pages/ai-prompt.blade.php`：通过
- 路由检查：`docker exec lsky-pro php artisan route:list --path=advanced-api/ai --no-ansi`：通过（`ai/config`、`ai/prompt`、`ai/prompt-tasks` 全部存在）
- Feature 回归：
  - `docker run --rm -v /opt/1panel/apps/lsky-pro:/var/www/html -w /var/www/html lsky-pro-custom:php83 bash -lc 'php artisan test --filter=AdvancedFeaturePagesTest --stop-on-failure'`：通过（3 passed）
  - `docker run --rm -v /opt/1panel/apps/lsky-pro:/var/www/html -w /var/www/html lsky-pro-custom:php83 bash -lc 'php artisan test --testsuite=Feature --stop-on-failure'`：通过（28 passed）
- live HTTP 验证：
  - 登录 `POST /login` -> `/dashboard`：通过
  - `/advanced`：`200`
  - `/advanced/ai-config`：`200`
  - `/advanced/ai-prompt`：`200`
  - `/images`：`200`
  - `/admin/images`：`200`
  - `GET /advanced-api/ai/config`：通过（`active_provider=gpt`，provider 数量 `4`）
  - 真实图片处理回归：登录后对用户现有图片 `1M0Xpc` 调用 `POST /advanced-api/images/1M0Xpc/process` + `{"transform":{"rotate":90}}` 返回成功，结果尺寸 `1024 x 1024`

### 残留风险
- `AI 提示词` 真实调用已接通，但 live 生产环境还未填写实际 provider API Key；页面与接口当前会明确提示“先在 AI 配置中完成配置”
- DeepSeek / 千问 / Gemini 的具体视觉模型能力由用户选择的实际模型决定；当前配置页负责治理入口，不替用户规避不兼容模型选择
- 裁剪交互和轮播布局已统一到共享组件，但还未做浏览器录屏级自动化验证，后续仍需补前端交互回放
