# Main Agent Self Research

## Research Baseline

- Reviewed docs:
  - `docs/documentation.md`
  - `docs/context-compact.md`
  - `docs/iteration-log.md`
  - `docs/advanced-features.md`
- Reviewed signals:
  - cycle-4 control plane / term projection / auth identity / passkey foundation 代码结果
  - 2026-03-09 本轮 PHPUnit、`route:list`、`schedule:list` 结果
  - ops runbook 只读结论

## Self Questions and Answers

| Question | Evidence | Answer | Confidence | Follow-up Gap |
| --- | --- | --- | --- | --- |
| 第四轮后 intelligence 最大缺口是什么？ | `ImageIntelligenceService` 仍生成 `metadata_placeholder`，control plane 只能操作 placeholder 回填 | 最大缺口已经从“没有控制面”转成“没有真实 provider-backed intelligence execution” | high | 需要真正的 provider/model 调用、source_version 和 fallback contract |
| term projection 还需要继续做吗？ | `image_intelligence_terms` 已落地，搜索和 visible tags 已消费 | term projection 的基础边界已够用，下一步重点不再是表结构，而是让真实 intelligence payload 驱动它 | medium-high | 需要 run ledger 和真实分析来源 |
| Passkey 现在是否该继续作为主线？ | status/options foundation 已有，设置页已读到真实状态，但没有 attestation/assertion | 还不该压过 intelligence 主线。Passkey 价值高，但当前图片产品最核心的 AI 能力仍停留在 placeholder | high | 将完整 Passkey 登录保留为 deferred stream |
| 第五轮最值得投入的新模块边界是什么？ | AI config 已有 provider/model discovery；control plane 与 projection 已 ready | `provider-backed intelligence execution + operator audit plane` 是最强下一步 | high | 需要分析 pipeline、run ledger、operator console、read-side adoption |
| 当前最需要继续跟踪的 carryover 风险是什么？ | social login 仍按 email 自动并账；dispatch 仍受 queue mode 影响 | auth email auto-link 与 queue sync 误配置仍需保留在 security/ops 视野中 | medium-high | 在第五轮 security gate 中继续跟踪，不把它们伪装成已解决 |

## Research Findings

- Strongest finding:
  - control plane、projection、多身份和 passkey foundation 已经把“基础设施缺口”补得差不多了，但核心 AI 价值仍被 `metadata_placeholder` 卡住。
- Contradictory or weak evidence:
  - Passkey completion 仍然有明显用户价值，但相对图片管理主业务，真实 intelligence execution 的收益更直接、更大。
- Missing observability or benchmark evidence:
  - 还没有 intelligence run ledger、provider/model 命中率、失败样本统计、fallback 占比、provider quota 消耗视图。
- Risks if the next cycle chooses the wrong direction:
  - 如果第五轮继续做 UI 外壳或继续补 foundation，系统会长期停留在“control plane 已有，但分析结果不是真智能”的状态。

## Candidate Next Iterations

| Theme | Type | Why Now | Dependencies | Expected User/Engineering Value | Risk |
| --- | --- | --- | --- | --- | --- |
| Provider-backed intelligence execution | module | provider/model 配置、control plane、projection 已具备前置条件 | cycle-1 AI config + cycle-4 control plane | very high | high |
| Passkey/WebAuthn product completion | module | foundation 已有，继续推进可完成登录闭环 | cycle-4 auth identity + passkey foundation | high | high |
| Intelligence operator audit plane | module | control plane 已能派发，但缺 run history / retry / failure samples | cycle-4 control plane | high | medium |
| Frontend browser automation and polish | workflow | advanced/settings 壳层已升级，但缺浏览器级稳定性证据 | cycle-3/cycle-4 UI shells | medium | medium |

## Selected Direction

- Selected iteration theme: provider-backed intelligence execution + operator audit plane
- Direction type: platform execution batch
- Why this direction wins over the other candidates:
  - 它直接把图片产品最核心的 AI 能力从 placeholder 推进到真实可用结果。
  - 它复用了第四轮刚刚建立好的 control plane、projection 和 AI provider/model 配置，收益最大。
  - 它比继续推进 Passkey 更贴近用户当前最核心的图片管理与 AI 搜索价值。
- What must be true before implementation starts:
  - analyze job 需要能读取 active provider/default model 并显式记录 fallback。
  - control plane 需要能看到 recent runs、失败样本和 retry contract。
  - search/prompt/detail 需要优先消费真实 intelligence payload，同时保留 placeholder fallback。
- Which new feature, workflow, or module boundary this iteration should create:
  - `provider-backed intelligence execution`
  - `intelligence run ledger`
  - `operator audit console`

## Queue Implications

- New `project_owner` planning tasks needed:
  - 第五轮范围与验收拆解
  - 第五轮队列固化
- New implementation tasks needed:
  - provider-backed analyze pipeline
  - run ledger / retry contract
  - read-side adoption for search / prompt / detail
  - operator console and single-image intelligence explanation UI
- New validation / benchmark tasks needed:
  - provider mocking / fallback tests
  - operator console feature tests
  - browser smoke for advanced intelligence and image detail explanation
- New review / remediation tasks needed:
  - reviewer 关注真实 provider 调用、fallback honesty、operator abuse 和 prompt/data exposure
- Should the main agent continue immediately with the rewritten queue in the current run?:
  - yes

## Post Cycle-5 Update

- New evidence gathered:
  - `ImageIntelligenceWriteSideTest`、`IntelligenceControlPlaneTest`、`ImageIntelligenceBackfillCommandTest`、`ImageIntelligenceRunLedgerTest` 已证明 provider-backed write side、run ledger、retry contract 和 job completion path 都已落地。
  - `AiPromptContextBuilderTest`、`ImageDetailIntelligenceTest`、`AdvancedFeaturePagesTest`、`AiSearchReadSideTest`、`UploadMainlineTest` 已证明 prompt/detail/search 的读侧 adoption 与 advanced operator console 没有回归。
- Updated strongest finding:
  - intelligence 主链路已经不再停留在 `metadata_placeholder`；当前最大的真实产品缺口变成了 Passkey 仍只有 foundation，以及关键认证/设置/advanced 页面仍缺浏览器级验证。
- Updated missing evidence:
  - 仍没有浏览器自动化或手工烟测来确认 `/settings`、登录页 Passkey 交互、`/advanced` operator console 在真实浏览器里的行为。
- Updated candidate ranking:
  - `auth identity/passkey completion + browser verification hardening` 应该优先于继续深挖 intelligence 新模块，因为 intelligence 主线的最小可用闭环已经成立。

## Updated Selected Direction

- Selected iteration theme: auth identity/passkey completion + browser verification hardening
- Direction type: auth/product hardening batch
- Why this direction now wins:
  - 它直接消化了用户原始需求中仍未完成的 Google/GitHub/Passkey 登录闭环问题。
  - 它承接了第五轮之后最明显的剩余风险：`auth_identities` 还未完全接管登录语义，Passkey 还不是真实可用能力。
  - 它顺带把 advanced/settings/login/intelligence 的浏览器验证补齐，减少“服务端断言都过了，但真实页面行为未确认”的盲区。

## Updated Queue Implications

- New implementation tasks needed:
  - Passkey attestation/assertion backend completion
  - multi-identity login/linking migration
  - settings account security / devices console
  - login/register passkey interactive flow
- New validation / review tasks needed:
  - browser smoke / E2E for login, settings, advanced intelligence
  - security review for email auto-link, credential abuse, session/origin constraints

## Post Cycle-6 Update

- New evidence gathered:
  - `PasskeyFoundationTest`: `OK (8 tests, 71 assertions)`，说明 register/login/credential management backend contract 已闭环。
  - `AuthenticationTest`: `OK (10 tests, 48 assertions)`，说明 social login 已切断 raw email auto-link，并保持冻结账号与 non-remember 策略。
  - `SettingsPageTest`: `OK (2 tests, 24 assertions)`，说明 `/settings` 已读取真实 Passkey status payload。
  - `RegistrationTest`: `OK (2 tests, 4 assertions)`，说明注册页壳层改动没有回归本地注册。
  - `php artisan route:list --path=auth/passkeys` 显示 `7` 条路由，Passkey contract 已从 foundation 升级到真实 register/login/device management surface。
- Updated strongest finding:
  - Passkey 最大缺口已经不再是服务端 challenge/assertion，而是 Google/GitHub 仍没有 authenticated bind/unbind，以及所有新交互仍缺真实浏览器证据。
- Updated missing evidence:
  - 没有 Playwright/Cypress/Dusk 或手工 smoke 结果来证明 `/login`、`/register`、`/settings`、`/advanced` 的新 JS 流程在真实浏览器中可用。
  - `/settings` 的 Google/GitHub 仍是只读状态卡片，缺 authenticated callback/link intent / unlink contract。
- Updated candidate ranking:
  - `social identity binding + browser verification automation` 现在优先于继续扩展 Passkey backend，因为 Passkey 主链路已经最小闭环，而身份控制台和浏览器证据仍是明显硬缺口。

## Updated Selected Direction After Cycle-6

- Selected iteration theme: social identity binding + browser verification automation
- Direction type: auth/control-plane hardening batch
- Why this direction now wins:
  - 用户原始需求里仍未完成的认证项已经收敛成 Google/GitHub 账户绑定与解绑，而不再是 guest login。
  - cycle-6 已经把 Passkey code path 接通，但没有浏览器级证据会让这部分价值停留在“代码通过，体验未证实”。
  - `auth_identities` 已是主解析路径，最自然的下一步就是把 settings 账户安全页做成真实 identity control console。

## Updated Queue Implications After Cycle-6

- New implementation tasks needed:
  - authenticated social link redirect/callback/unlink backend contract
  - settings social identity bind/unbind console
  - auth/settings/advanced browser smoke harness or checklist automation
- New validation / review tasks needed:
  - OAuth link intent、session、callback safety review
  - WebAuthn 浏览器 smoke、降级路径和取消路径验证

## Post Cycle-7 Update

- New evidence gathered:
  - 在重建 `lsky-pro-custom:php83` 后，`AuthenticationTest`、`User/SettingsPageTest`、`Admin/SettingsPageTest`、`RegistrationTest` 结果为 `OK (21 tests, 119 assertions)`。
  - `php artisan route:list --path=auth/passkeys` 显示 `7` 条路由，`php artisan route:list --path=auth` 显示 `11` 条路由。
  - `scripts/qa/e2e-auth-settings-advanced.sh` 已在冷启动环境通过，覆盖登录页、注册页、设置页、advanced 页、Passkey status、解绑和重新进入 Google link redirect。
  - 本轮补修了一个真实风险：社交自动建号的随机占位密码曾被误判为可用密码登录方式，现已改为显式 `auth_password_login_ready` 语义。
- Updated strongest finding:
  - 认证功能闭环本身已经基本成立，最大的剩余缺口不再是“能不能绑定/解绑”，而是“是否具备 identity governance / revoke / audit 控制面”和“是否有真实浏览器自动化证据”。
- Updated missing evidence:
  - 当前的 browser evidence 仍是 HTTP/curl cold-start smoke，不执行页面 JS，也不触发真实 `navigator.credentials`。
  - 还没有 identity timeline、revoke history、session governance 或 operator-facing 审计读模型。
- Updated candidate ranking:
  - `identity governance + browser-e2e operationalization` 现在优先于继续补新的 OAuth/Passkey endpoint，也优先于回头扩展 intelligence 新模块。

## Updated Selected Direction After Cycle-7

- Selected iteration theme: identity governance + browser-e2e operationalization
- Direction type: auth/operator hardening batch
- Why this direction now wins:
  - 第七轮已经完成 bind/unbind 控制台；继续追加同类 endpoint 的边际收益已经下降。
  - 真实产品风险现在集中在“用户是否会被误锁死/误撤销”和“客户端 JS/WebAuthn 流程是否真的可用”。
  - legacy snapshot 仍存在，只有把治理和审计做成 first-class model，账户安全页才算进入可运营阶段。

## Updated Queue Implications After Cycle-7

- New implementation tasks needed:
  - identity governance read model / revoke backend
  - identity audit / notice / recent-event payload
  - settings identity governance console
  - real browser automation runner for `/login`、`/register`、`/settings`、`/advanced`
- New validation / review tasks needed:
  - browser-e2e container/CI execution path
  - revoke abuse、legacy snapshot carryover、session/origin 约束安全审查

## Post Cycle-8 Update

- New evidence gathered:
  - `AuthIdentityGovernanceService`、`AuthIdentityEvent` 和 `auth_identity_events` 已把 recovery level、method inventory、legacy snapshot、recent events 收敛成统一治理读模型。
  - `AuthenticationTest`、`PasskeyFoundationTest`、`User/SettingsPageTest`、`Admin/SettingsPageTest`、`RegistrationTest` 在重建 `lsky-pro-custom:php83` 后结果为 `OK (29 tests, 199 assertions)`。
  - `php artisan route:list --path=auth/passkeys` 仍为 `7` 条，`php artisan route:list --path=auth` 仍为 `11` 条，说明第七轮认证主链路没有被第八轮治理改动破坏。
  - 用户已明确本阶段不采用 browser-e2e 作为验收方式，改由手工 UI 验收承接；因此实验性浏览器脚本不应继续占据下一轮主线。
- Updated strongest finding:
  - 认证治理已经进入“可运营”阶段，当前最大产品缺口重新回到图片产品主线，也就是 AI 检索质量、intelligence tag sync 和 Prompt V2。
- Updated missing evidence:
  - 手工 UI 验收尚未完成，但这已不是当前代码推进的主阻塞。
  - `users.provider/provider_id` legacy snapshot 仍存在，不过它更适合作为后续 hardening stream，而不是继续压住主线。
  - AI 检索目前仍更接近 explainable keyword/term ranking，而不是更完整的 retrieval v2。
- Updated candidate ranking:
  - `intelligence retrieval v2 + prompt/tag quality` 现在优先于继续深挖 auth 或 browser automation，因为它最直接承接了用户原始 AI 需求且复用现有 provider-backed intelligence。

## Updated Selected Direction After Cycle-8

- Selected iteration theme: intelligence retrieval v2 + prompt/tag quality
- Direction type: intelligence/product quality batch
- Why this direction now wins:
  - provider-backed intelligence、run ledger 和 detail payload 已到位，下一步最该解决的是“这些结果如何真正改善检索、标签和 Prompt”。
  - 第八轮已经把 auth/settings 收口到可手测状态，继续追加认证测试基建的边际收益已经下降。
  - 用户原始需求中“AI 检索完全重构、定时打 tag、提示词重构”仍然是最大的未完成价值块。

## Updated Queue Implications After Cycle-8

- New implementation tasks needed:
  - intelligence tag sync / corpus enrichment
  - AI retrieval ranking v2
  - Prompt V2 context builder
  - `/advanced` 与单图 explainability UI
- New validation / review tasks needed:
  - rebuilt-image focused suite for retrieval / prompt / detail
  - 手工 UI 验收矩阵，明确与自动化验证的边界
