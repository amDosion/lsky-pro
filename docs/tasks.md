# Task Breakdown

## Planning Rules

- Every task must have one owner role.
- Non-`project_owner` tasks must depend on `project_owner` planning outputs.
- `reviewer` acts as the phase gate before the next queue is accepted.
- After a clean reviewer pass, the main agent must rewrite `queue/tasks.csv` again instead of stopping.

## Team Assignment Matrix

| Role | Responsibility | Typical Deliverables |
| --- | --- | --- |
| project_owner | scope, requirements, queue ownership | docs, queue, acceptance criteria |
| frontend | page IA, UI behavior, client logic | Blade/UI code, browser flows |
| backend | APIs, jobs, domain logic, data contracts | services, controllers, tests |
| devops | runtime, scheduler, rollout, observability | runbooks, operational notes |
| qa | regression evidence and acceptance gates | tests, evidence, reports |
| security | authz, secret exposure, dependency risk | review notes, remediations |
| reviewer | final go/no-go review | findings, risk signoff |

## Current Queue Summary

| id | title | owner_role | depends_on | required_files | validate_cmds |
| --- | --- | --- | --- | --- | --- |
| PO-901 | 第九轮范围与验收拆解 | project_owner |  | docs/requirements.md;docs/design.md;docs/self-research.md |  |
| PO-902 | 第九轮队列固化 | project_owner | PO-901 | queue/tasks.csv;docs/context-compact.md;docs/advanced-features.md |  |
| BE-901 | Intelligence tag sync and corpus enrichment | backend | PO-902 | docs/documentation.md | docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc './vendor/bin/phpunit tests/Feature/Intelligence/AiSearchReadSideTest.php' |
| BE-902 | AI retrieval ranking and Prompt V2 backend | backend | PO-902;BE-901 | docs/documentation.md | docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc './vendor/bin/phpunit tests/Feature/Intelligence/AiSearchReadSideTest.php tests/Feature/Intelligence/AiPromptContextBuilderTest.php' |
| FE-901 | AI search and detail explainability UI | frontend | PO-902;BE-901;BE-902 | docs/documentation.md | manual-ui-check |
| OP-901 | Intelligence sync scheduler and runbook | devops | BE-901;BE-902 | docs/documentation.md | docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php artisan schedule:list && php artisan route:list --path=advanced-api' |
| QA-901 | 第九轮 intelligence 检索与 Prompt 回归证据 | qa | BE-901;BE-902;FE-901;OP-901 | docs/documentation.md | docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc './vendor/bin/phpunit tests/Feature/Intelligence/AiSearchReadSideTest.php tests/Feature/Intelligence/AiPromptContextBuilderTest.php tests/Feature/User/ImageDetailIntelligenceTest.php' |
| SE-901 | Intelligence retrieval and prompt data exposure audit | security | BE-901;BE-902 | docs/documentation.md | docker run --rm --entrypoint sh -v /opt/1panel/apps/lsky-pro:/app -w /app lsky-pro-custom:php83 -lc 'php artisan route:list --path=advanced-api' |
| RV-901 | 第九轮评审门禁 | reviewer | OP-901;QA-901;SE-901 | docs/documentation.md |  |
| PO-903 | 下一轮再规划 | project_owner | RV-901 | docs/self-research.md;docs/advanced-features.md;docs/iteration-log.md |  |

## Deferred Streams That Remain Explicit

- `STREAM-001` legacy identity snapshot 退场、session governance 与更深层账户恢复策略。
- `STREAM-002` Playwright/WebAuthn browser automation；当前按用户要求由手工 UI 验收承接。
- `STREAM-003` 向量检索、embedding 和召回排序。
- `STREAM-004` 图片工作台高级操作中心与编辑持久化。
- `STREAM-005` provider quota / cost telemetry 与更细粒度 operator 审计面板。
