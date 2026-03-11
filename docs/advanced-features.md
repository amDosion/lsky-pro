# Advanced Feature And Module Research

## Input Baseline

- Source docs reviewed:
  - `docs/documentation.md`
  - `docs/self-research.md`
  - `docs/context-compact.md`
  - `docs/design.md`
  - cycle-4 control plane / projection / auth foundation 代码结果

## Candidate Upgrades

| Candidate | Type | Combined Existing Capabilities | Research Evidence | User Value | Engineering Cost | Risk |
| --- | --- | --- | --- | --- | --- | --- |
| Provider-backed intelligence execution | module | AI config + analyze job + control plane + term projection | foundations 已齐，但结果仍是 `metadata_placeholder` | 很高 | 高 | 高 |
| Intelligence operator audit plane | module | control plane + scheduler + backfill service | 已能派发，但没有 run history / retry / failure samples | 很高 | 中高 | 中 |
| Passkey/WebAuthn product completion | module | auth identities + passkey foundation + settings status UI | foundation 已齐，但完整登录闭环仍缺失 | 高 | 高 | 高 |
| Browser automation and polish | workflow | advanced/settings 新壳层 + feature tests | 页面 contract 已稳定，但缺 JS/browser 证据 | 中 | 中 | 中 |

## Selected Expansion

- Expansion name: provider-backed intelligence execution + operator audit plane
- Expansion type: platform execution batch
- Included capabilities for next batch:
  - provider/model 驱动的真实 image intelligence analysis
  - source_version / provider / model / fallback reason 持久化
  - intelligence run ledger / recent runs / failure samples / retry contract
  - search / prompt / detail 对真实 intelligence payload 的 read-side adoption
  - advanced intelligence operator console 和单图 explanation UI
- Why this direction wins:
  - 它把当前产品最核心的 AI 价值从占位级别推进到真实结果。
  - 它最大化复用第四轮刚建立好的 control plane、projection 和 provider 配置能力。
  - 它能让后续的 tag sync、prompt quality、搜索质量优化都有真实数据底座。

## Delivery Plan

1. Project-owner planning tasks:
   - 明确 provider-backed intelligence execution 的验收边界、fallback contract 和 run ledger 范围
2. Implementation tasks:
   - provider-backed analyze pipeline
   - run ledger / retry contract
   - read-side adoption
   - operator console / image detail explanation UI
3. Validation tasks:
   - provider mocking、fallback、operator console、browser smoke
4. Security tasks:
   - provider secrets、prompt/data exposure、operator abuse 和 identity carryover 风险审查

## Acceptance Criteria For Next Batch

1. `NAF-501` image intelligence 可以使用已配置 provider/model 生成真实分析结果，而不是只返回 metadata placeholder。
2. `NAF-502` control plane 能看到 recent runs、失败样本和 retry/audit 所需基础信息。
3. `NAF-503` AI 搜索、Prompt 和图片详情优先消费真实 intelligence payload，并保留明确 fallback。
4. `NAF-504` `/advanced` 有 operator console，单图页面有 intelligence explanation UI。
5. `NAF-505` 第五轮 reviewer gate 对 provider 调用真实性、fallback honesty 和 operator abuse 给出明确结论。

## Post Cycle-5 Update

- cycle-5 completion summary:
  - provider-backed intelligence execution 已落地，`image_intelligence_runs` ledger 与 retry contract 已落地，`/advanced` recent-run / retry console 与单图 `intelligence` explanation payload 已可用。
- upgraded baseline:
  - intelligence 主链路不再是“候选模块”，而是现有平台能力；下一轮不该回到 placeholder 时代。

## Selected Expansion After Cycle-5

- Expansion name: auth identity/passkey completion + browser verification hardening
- Expansion type: auth/product hardening batch
- Included capabilities for next batch:
  - Passkey attestation/assertion + credential persistence
  - `auth_identities` 主路径化与 social/password/passkey 统一登录语义
  - `/settings` 账户安全 / 设备管理真实完成态
  - 登录/注册页 Passkey 浏览器交互
  - advanced/settings/login/intelligence 浏览器 smoke / E2E
- Why this direction wins:
  - intelligence 第五轮已经达到最小可运营闭环，继续深挖的边际收益暂时低于补齐认证闭环。
  - 用户原始需求里最显著未完成项已经转移到 Passkey 和浏览器级体验稳定性。

## Post Cycle-6 Update

- cycle-6 completion summary:
  - Passkey register/login/device management contract 已落地，`/login`、`/register`、`/settings` 已接入真实 endpoint。
  - social login 已切断 raw email auto-link，未绑定邮箱不会再被静默并账。
  - 当前剩余缺口不再是 Passkey foundation，而是 social identity control plane 与浏览器级证据。

## Selected Expansion After Cycle-6

- Expansion name: social identity binding + browser verification automation
- Expansion type: auth/control-plane completion batch
- Included capabilities for next batch:
  - authenticated Google/GitHub link/unlink backend contract
  - `/settings` 账户安全页的社交身份绑定/解绑控制台
  - OAuth callback/link intent/session safety hardening
  - auth/settings/advanced 浏览器 smoke / E2E / 手工验收矩阵
- Why this direction wins:
  - Passkey 已经进入最小可用闭环，继续只做 Passkey backend 的边际收益已经下降。
  - 用户对 Google/GitHub 登录的剩余需求已经从“guest 可登录”转为“已登录账户如何绑定/解绑”。
  - 缺少浏览器证据会让第六轮新增的 JS 交互长期停留在未验证状态。

## Post Cycle-7 Update

- cycle-7 completion summary:
  - authenticated Google/GitHub link/unlink contract、`identity_matrix` 驱动的设置页控制台和 cold-start smoke harness 已落地。
  - 账户安全页已不再依赖“密码字段非空”这种弱语义，而是改为显式 `auth_password_login_ready` 判断，避免社交自动建号的随机密码导致误解绑。
- upgraded baseline:
  - 认证主链路不再是“缺入口”，而是“缺治理与真实浏览器级验证”；下一轮不该继续重复造 bind/unbind。

## Selected Expansion After Cycle-7

- Expansion name: identity governance + browser-e2e operationalization
- Expansion type: auth/operator completion batch
- Included capabilities for next batch:
  - identity revoke / audit / timeline read model
  - `/settings` identity governance console，展示最近使用、风险提示和撤销限制
  - 真实浏览器自动化 runner，覆盖 `/login`、`/register`、`/settings`、`/advanced` 的关键 JS 交互与 WebAuthn capability 降级
  - browser-e2e container / rollout runbook / CI integration
- Why this direction wins:
  - 它直指第七轮后剩下的真实产品缺口，而不是重复补已打通的 endpoint。
  - 它能把当前的 curl smoke 提升为真实浏览器证据，减少“服务端绿了但客户端交互未证实”的盲区。
  - 它能让 legacy snapshot 和 revoke 风险在界面与运维层都被看见。

## Post Cycle-8 Update

- cycle-8 completion summary:
  - identity governance payload、事件时间线和 `/settings` governance console 已落地，并在重建镜像后通过 focused 回归。
  - 按用户要求，本轮没有把 browser-e2e 继续作为交付主线，真实页面验收转为手工 UI 流程。
- upgraded baseline:
  - 认证控制面已经足够稳定，下一轮不该继续围绕 browser runner 或绑定入口做增量，而应切回图片产品主线。

## Selected Expansion After Cycle-8

- Expansion name: intelligence retrieval v2 + prompt/tag quality
- Expansion type: intelligence/product quality batch
- Included capabilities for next batch:
  - intelligence tag sync / corpus enrichment
  - explainable AI retrieval ranking v2
  - Prompt V2 context and richer image direction inference
  - `/advanced` AI search 与单图详情 explainability UI
  - scheduler / runbook / QA evidence for the new intelligence flow
- Why this direction wins:
  - 它直接承接了用户原始需求里最核心的 AI 检索、tag 和 Prompt 改造诉求。
  - 它最大化复用了第五轮以来已经完成的 provider-backed intelligence 和 run ledger 基础层。
  - 它比继续扩 auth/testing infra 更接近图片管理工具的主业务价值。
