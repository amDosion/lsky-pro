# Project Change Log

## Cycle-8 Update

- Task status:
  - `cycle-8-identity-governance-manual-ui-handoff`: completed
- What changed:
  - 新增 `AuthIdentityGovernanceService`、`AuthIdentityEvent` 和 `auth_identity_events`，把 recovery level、method inventory、legacy snapshot、recent events 收敛成统一 governance payload。
  - `AuthIdentityService`、`PasskeyChallengeService`、用户改密、重置密码和注册链路现在都会写入治理事件，`/settings` 可直接展示 timeline 与风险提示。
  - `PasskeyAuthController::status` 已返回 `governance` 结构；`/settings` 已升级为 identity governance console，展示恢复等级、方法库存、legacy 一致性和最近事件。
  - 按用户要求，本轮不再以浏览器自动化作为验收方式；实验性的 browser-e2e 脚本未保留，后续由手工 UI 验收承接。
- Validation evidence:
  - 先重建 `lsky-pro-custom:php83`
  - `AuthenticationTest`、`PasskeyFoundationTest`、`User/SettingsPageTest`、`Admin/SettingsPageTest`、`RegistrationTest`: `OK (29 tests, 199 assertions)`
  - `php artisan route:list --path=auth/passkeys`: `Showing [7] routes`
  - `php artisan route:list --path=auth`: `Showing [11] routes`
- Residual risks:
  - 手工 UI 验收仍待执行；当前仓库不再把 browser-e2e runner 作为本轮交付件。
  - `users.provider/provider_id` 仍是 legacy 单槽位快照。
  - AI 检索、tag sync 和 Prompt V2 仍是下一轮最大的产品缺口。

## Cycle-7 Update

- Task status:
  - `cycle-7-social-identity-binding-browser-automation`: completed
- What changed:
  - `SocialAuthController` 已补齐 authenticated Google/GitHub link redirect、callback intent 和 unlink contract，`/settings` 不再只是只读状态卡片。
  - `PasskeyAuthController::status` 现在返回 `identity_matrix`，账户安全页可直接根据真实 backend payload 渲染 Google、GitHub、Passkey 与 legacy snapshot 状态。
  - 新增 `auth_password_login_ready` 语义；社交自动建号写入的随机占位密码不再被视为可用的本地登录方式，最后一个第三方身份的解绑判断与设置页密码状态文案已经统一。
  - `/settings` 已升级为真实 Google/GitHub 绑定解绑控制台；`/login`、`/register` 的 Passkey 文案也已对齐到真实能力边界。
  - 新增 `scripts/qa/e2e-auth-settings-advanced.sh`，对 `/login`、`/register`、`/settings`、`/advanced`、Passkey status、unlink 和 link redirect 做冷启动 smoke。
- Validation evidence:
  - 先按要求重建 `lsky-pro-custom:php83`，再执行 focused suite：`AuthenticationTest`、`User/SettingsPageTest`、`Admin/SettingsPageTest`、`RegistrationTest`，结果 `OK (21 tests, 119 assertions)`。
  - `php artisan route:list --path=auth/passkeys`: `Showing [7] routes`
  - `php artisan route:list --path=auth`: `Showing [11] routes`
  - `scripts/qa/e2e-auth-settings-advanced.sh`: `[PASS] auth/settings/advanced e2e passed`
- Residual risks:
  - 当前的 `e2e-auth-settings-advanced.sh` 仍是 HTTP/curl cold-start smoke，不是执行真实 JS 与 `navigator.credentials` 的浏览器自动化。
  - `users.provider/provider_id` 仍只是 legacy 单槽位快照，不是完整的 identity governance / audit model。
  - 身份撤销历史、会话治理和更细粒度的账户恢复/审计能力仍未落地。

## Cycle-6 Update

- Task status:
  - `cycle-6-auth-passkey-completion-browser-hardening`: completed
- What changed:
  - `PasskeyAuthController`、`PasskeyChallengeService`、`PasskeyWebauthnAdapter` 现在已经形成真实的 Passkey register/login/device management contract，`auth/passkeys` 共 `7` 条路由。
  - `AuthIdentityService` 不再按 raw email 自动并账；social login 现在优先走 `auth_identities` / legacy snapshot，遇到已存在但未绑定的邮箱会显式拒绝。
  - `/login`、`/register` 已接入 guest Passkey challenge/assertion；`/settings` 已接入已登录用户的 Passkey 登记、重命名、删除和状态刷新。
  - `PasskeyFoundationTest`、`AuthenticationTest`、`SettingsPageTest`、`RegistrationTest` 已覆盖新的认证 contract 和主要模板回归。
- Validation evidence:
  - `PasskeyFoundationTest`: `OK (8 tests, 71 assertions)`
  - `AuthenticationTest`: `OK (10 tests, 48 assertions)`
  - `SettingsPageTest`: `OK (2 tests, 24 assertions)`
  - `RegistrationTest`: `OK (2 tests, 4 assertions)`
  - `php artisan route:list --path=auth/passkeys`: `Showing [7] routes`
- Residual risks:
  - `/settings` 的 Google/GitHub 仍然是只读状态卡片，没有 authenticated bind/unbind 控制台。
  - 新的 Passkey JS 流程还没有真实浏览器手工或 E2E 证据。
  - 账户中心仍缺完整 identity revoke / callback intent / unlink 产品化。

## Cycle-5 Update

- Task status:
  - `cycle-5-provider-backed-intelligence-operator-plane`: completed
- What changed:
  - `AiMultimodalContentService` 与 `ProviderBackedImageIntelligenceAnalyzer` 已把 image intelligence 写侧升级到 active provider/default model 驱动的真实分析链路。
  - `image_intelligence_runs`、`ImageIntelligenceRunLedgerService`、`retry_run_id`、scheduler trigger source 和 recent-run UI 已把 operator plane 从一次性 dispatch 提升到可追踪、可重试的 run ledger。
  - `ImagePromptContextBuilder`、`AiController`、`User/ImageController` 已显式区分 provider-backed 与 placeholder/fallback，并在单图详情返回独立 `intelligence` 对象。
- Validation evidence:
  - `ImageIntelligenceWriteSideTest`、`IntelligenceControlPlaneTest`、`ImageIntelligenceBackfillCommandTest`、`ImageIntelligenceRunLedgerTest`: `OK (12 tests, 110 assertions)`
  - `AiPromptContextBuilderTest`、`ImageDetailIntelligenceTest`、`AdvancedFeaturePagesTest`、`AiSearchReadSideTest`、`UploadMainlineTest`: `OK (20 tests, 237 assertions)`
- Residual risks:
  - 浏览器级验证仍未执行，`/advanced` retry console、`/settings`、登录页 Passkey 交互仍缺真实浏览器证据。
  - Passkey/WebAuthn 仍只有 foundation，没有 attestation/assertion 校验与完整登录闭环。
  - `auth_identities` 已建模，但登录/解绑/账户中心的完整产品化仍未完成。

## Task Status

- Task ID: cycle-4-control-plane-auth-foundation
- Owner role: project_owner
- Status: completed

## What Changed

- Code paths changed:
  - `app/Http/Controllers/Api/V1/IntelligenceController.php`
  - `app/Http/Controllers/Auth/SocialAuthController.php`
  - `app/Http/Controllers/User/UserController.php`
  - `app/Jobs/AnalyzeImageIntelligenceJob.php`
  - `app/Models/AuthIdentity.php`
  - `app/Models/Image.php`
  - `app/Models/ImageIntelligenceTerm.php`
  - `app/Models/User.php`
  - `app/Models/WebauthnCredential.php`
  - `app/Services/AiSearchService.php`
  - `app/Services/Auth/AuthIdentityService.php`
  - `app/Services/Auth/LegacyAuthIdentityBackfillService.php`
  - `app/Services/Auth/PasskeyChallengeService.php`
  - `app/Services/ImageIntelligence/ImageIntelligenceControlPlaneService.php`
  - `app/Services/ImageIntelligence/ImageIntelligenceService.php`
  - `app/Services/ImageIntelligence/ImageIntelligenceTermProjectionService.php`
  - `app/Services/ImageIntelligence/ImageTagVisibilityBridgeService.php`
  - `database/migrations/2026_03_09_020000_create_image_intelligence_terms_table.php`
  - `database/migrations/2026_03_09_020000_create_auth_identities_table.php`
  - `database/migrations/2026_03_09_021000_create_webauthn_credentials_table.php`
  - `resources/views/user/advanced_overview.blade.php`
  - `resources/views/user/settings.blade.php`
  - `routes/auth.php`
  - `routes/web.php`
  - `tests/Feature/Advanced/AdvancedFeaturePagesTest.php`
  - `tests/Feature/Auth/AuthenticationTest.php`
  - `tests/Feature/Auth/LegacyAuthIdentityBackfillServiceTest.php`
  - `tests/Feature/Auth/PasskeyFoundationTest.php`
  - `tests/Feature/Intelligence/AiSearchReadSideTest.php`
  - `tests/Feature/Intelligence/ImageIntelligenceBackfillCommandTest.php`
  - `tests/Feature/Intelligence/ImageIntelligenceWriteSideTest.php`
  - `tests/Feature/Intelligence/ImageTagVisibilityBridgeTest.php`
  - `tests/Feature/Intelligence/IntelligenceControlPlaneTest.php`
  - `tests/Feature/User/SettingsPageTest.php`
- Functional outcomes:
  - `/advanced-api/intelligence/status`、`/backfill-preview`、`/backfill-dispatch` 已落地，管理员可通过 control plane 读取状态、预览候选并派发小批量回填。
  - `/advanced` 总览页已升级为真实 intelligence control panel，普通用户只看到状态，管理员才可见操作位。
  - intelligence dispatch 现在带有按 `image_id` 的去重锁，避免 control plane / scheduler 在锁有效期内重复为同一图片排队。
  - `image_intelligence_terms` 已成为 first-class projection contract，AI 搜索、详情展示和 visible tags 不再依赖临时桥接拼装。
  - 社交登录不再只依赖 `users.provider/provider_id` 单槽位；`auth_identities` 已接管多身份基础，并继续镜像 legacy snapshot 以保持兼容。
  - legacy `users.provider/provider_id` 回填现在改为服务化 `upsert`，可安全重跑，并会显式拦截重复 `provider + provider_id` 冲突，而不是静默 `insertOrIgnore`。
  - Passkey/WebAuthn foundation 已具备 `status` 与 `register/options` 后端基础，但仍明确停留在 foundation 级别，没有伪装成完整登录产品。
  - `/settings` 已改为从真实 backend status 读取 Google / GitHub / legacy snapshot / passkey foundation 状态，而不是静态“后端待接入”占位。

## Why It Changed

- Requirement or issue:
  - 第三轮已经把 intelligence 运行化最小骨架立起来，但 operator 还只能依赖 CLI/scheduler，标签治理仍然只是读侧兼容，账户安全页也仍缺真实 backend foundation。
- Decision summary:
  - 第四轮优先完成 `intelligence control plane + term projection + auth identity normalization + passkey foundation`，而不是过早接入真实视觉模型或完整 Passkey 登录。
- Tradeoffs:
  - intelligence 结果仍然是 `metadata_placeholder`，不是 provider-backed 真实推理结果。
  - backfill 仍然没有 run ledger、失败重试面板和 operator 审计视图。
  - Passkey 仍未进入 attestation/assertion 校验、凭证写入完成态和登录入口接入。

## Validation Evidence

- Commands run:
  - `docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php -l app/Http/Controllers/User/UserController.php && ./vendor/bin/phpunit tests/Feature/User/SettingsPageTest.php && ./vendor/bin/phpunit --filter=AdvancedFeaturePagesTest'`
  - `docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php -l app/Http/Controllers/User/UserController.php && php -l resources/views/user/settings.blade.php && ./vendor/bin/phpunit tests/Feature/User/SettingsPageTest.php && ./vendor/bin/phpunit --filter=PasskeyFoundationTest && ./vendor/bin/phpunit --filter=AuthenticationTest'`
  - `docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc './vendor/bin/phpunit tests/Feature/Intelligence/IntelligenceControlPlaneTest.php && ./vendor/bin/phpunit --filter=AiSearchReadSideTest && ./vendor/bin/phpunit --filter=ImageIntelligenceWriteSideTest && ./vendor/bin/phpunit --filter=ImageTagVisibilityBridgeTest'`
  - `docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php -l app/Services/ImageIntelligence/ImageIntelligenceService.php && php -l app/Jobs/AnalyzeImageIntelligenceJob.php && php -l app/Services/Auth/LegacyAuthIdentityBackfillService.php && php -l database/migrations/2026_03_09_020000_create_auth_identities_table.php && ./vendor/bin/phpunit tests/Feature/Intelligence/ImageIntelligenceWriteSideTest.php && ./vendor/bin/phpunit tests/Feature/Intelligence/IntelligenceControlPlaneTest.php && ./vendor/bin/phpunit tests/Feature/Intelligence/ImageIntelligenceBackfillCommandTest.php && ./vendor/bin/phpunit tests/Feature/Auth/LegacyAuthIdentityBackfillServiceTest.php && ./vendor/bin/phpunit --filter=AuthenticationTest'`
  - `docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php artisan route:list --path=advanced-api/intelligence && php artisan route:list --path=auth/passkeys && php artisan schedule:list'`
- Results:
  - `SettingsPageTest`: `OK (2 tests, 21 assertions)`
  - `PasskeyFoundationTest`: `OK (3 tests, 20 assertions)`
  - `AuthenticationTest`: `OK (9 tests, 41 assertions)`
  - `AdvancedFeaturePagesTest`: `OK (6 tests, 127 assertions)`
  - `IntelligenceControlPlaneTest`: `OK (2 tests, 28 assertions)`
  - `ImageIntelligenceBackfillCommandTest`: `OK (2 tests, 16 assertions)`
  - `AiSearchReadSideTest`: `OK (4 tests, 15 assertions)`
  - `ImageIntelligenceWriteSideTest`: `OK (3 tests, 18 assertions)`
  - `ImageTagVisibilityBridgeTest`: `OK (3 tests, 40 assertions)`
  - `LegacyAuthIdentityBackfillServiceTest`: `OK (2 tests, 5 assertions)`
  - `advanced-api/intelligence` 路由共 `3` 条。
  - `auth/passkeys` 路由共 `2` 条。
  - `schedule:list` 已显示 hourly intelligence backfill 和 stale task reclaim 计划。

## Ops Runbook

- Rollout order:
  - 先执行数据库迁移，再上线 control plane / projection / auth foundation 代码。
  - 先确认 `QUEUE_CONNECTION` 不是 `sync`，否则 intelligence dispatch 会在请求或 scheduler 内联执行，失去异步保护。
  - 上线后先用 preview 或 CLI dry-run 看候选规模，再做小批量 dispatch。
  - 再观察 backlog、失败和最近分析时间，最后再扩大生产使用。
- Runtime entrypoints:
  - control plane status:
    - `GET /advanced-api/intelligence/status`
  - control plane preview:
    - `POST /advanced-api/intelligence/backfill-preview`
  - control plane dispatch:
    - `POST /advanced-api/intelligence/backfill-dispatch`
  - passkey foundation:
    - `GET /auth/passkeys/status`
    - `POST /auth/passkeys/register/options`
  - CLI:
    - `php artisan images:backfill-intelligence --dispatch --limit=25 --chunk=25 --older-than-minutes=30`
- Monitoring and operator cautions:
  - intelligence backlog 主要看 `missing_count`、`pending_count`、`coverage_rate`、`latest_analyzed_at`。
  - intelligence jobs 仍走 upload pipeline queue，需要额外关注 `jobs_pending`、`jobs_failed` 与 worker 健康。
  - intelligence dispatch 现在有 per-image lock；如果 worker 故障导致锁长时间不释放，需要结合 pending 状态和锁 TTL 一起排查。
  - `auth_identities` legacy 回填会在重复 `provider + provider_id` 冲突时直接报错，中断迁移；上线前应先排查冲突用户，而不是期待迁移静默吞掉异常数据。
  - 不要在生产环境使用命令默认参数直接全量回填；control plane 和 scheduler 的 `25/25/30` 比 CLI 默认值更安全。
  - preview/dispatch 已加 throttle，但它仍然是管理员级全局操作，应结合 runbook 控制频率和批量规模。
  - Passkey challenge 当前完全依赖 session，TTL 为 300 秒；多节点环境必须使用 shared/sticky session。

## QA Gate

- Automated coverage now present:
  - intelligence control plane API 与 payload contract
  - advanced intelligence control panel 页面 contract
  - intelligence term projection 与 visible tags / search read side
  - social auth compatibility / frozen user / non-remember session
  - passkey foundation status/options endpoints
  - settings 页面真实 backend status 读取壳层
- Mandatory manual checks:
  - `/advanced` 上 preview / dispatch / refresh 的浏览器交互、错误态和非管理员态
  - `/settings` 页面 load 后的 identity/passkey status 文案更新、接口失败兜底和浏览器 capability 提示
  - `passkeys.register.options` 触发后页面对 pending challenge 的展示
  - AI 搜索结果和图片详情中的 visible tags 语义是否仍与 manual tags 区分清楚
- Missing automation:
  - control plane 参数矩阵：`image_id / from_id / force / missing_only / sample_limit`
  - backfill scheduler duplicate dispatch / stale job 场景
  - settings 页 JS 读取失败分支
  - 多 provider 并存和 legacy snapshot 冲突场景

## Review And Security Gate

- Reviewer conclusion:
  - 第四轮经过 remediation 后没有剩余阻塞级回归，control plane / term projection / auth identity foundation / passkey foundation 的边界当前可接受，且验证保持绿色。
- Main findings:
  - 中: 社交登录仍会按邮箱自动并账，且没有消费 provider email verified 证据；这是旧风险延续，不是第四轮新引入，但它仍然是多身份体系的主要安全 carryover。
  - 中: `QUEUE_CONNECTION=sync` 时，intelligence dispatch 会在当前进程内联执行，operator 会误以为是受控异步派发。
  - 中: Passkey foundation 仍然只是 challenge/session plumbing 和状态读取，不代表完整注册/登录验证已经完成。
  - 低: intelligence projection 当前更偏读写一致性 contract，而不是完整治理/审计模型；失败或回退场景仍需后续 run ledger 支撑。
  - 低: unauthorized preview/dispatch 仍走 `status=false` JSON 约定，而不是 HTTP 403 语义；它不会造成越权，但不利于 API 侧清晰判错。
- No high-severity findings:
  - 没有发现跨用户 intelligence 泄漏。
  - 没有发现 manual tags 被直接污染写入。
  - remediation 已补上 intelligence dispatch 去重锁，关闭了 reviewer 提出的重复排队阻塞项。
  - remediation 已把 legacy auth 回填改为显式冲突拦截 + rerun-safe upsert，关闭了 reviewer 提出的静默迁移冲突阻塞项。
  - Passkey foundation UI 没有把未完成能力伪装成可直接登录的产品。

## Environment Notes

- Host constraints:
  - 宿主机 `php` 仍是 `7.4.3`，第四轮全部继续使用 `lsky-pro-custom:php83` 容器验证。
- Frontend build:
  - 当前目录仍缺 `node_modules`，本轮仍未执行 Mix 构建。

## Residual Risks

- intelligence 仍是 `metadata_placeholder`，不是基于 provider/model 的真实视觉推理。
- control plane 还没有 run ledger、失败样本、重试审计和 operator history。
- `auth_identities` 已立住，但完整 identity management / unlink / revoke 仍未开始。
- Passkey/WebAuthn 仍停留在 foundation，不支持完整注册校验、登录验证和设备管理。
