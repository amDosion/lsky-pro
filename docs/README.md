# Docs Index（执行索引）

## 1. 先读顺序（建议）
1. `docs/tasks/TASKS.md`：任务分解与执行清单（P0/P1/P2）。
2. `docs/runbook/OPERATIONS_RUNBOOK.md`：部署、回滚、告警、应急总流程。
3. `docs/runbook/PROD_MIGRATION_WINDOW_AND_ROLLBACK.md`：生产迁移窗口与分级回滚。
4. `docs/runbook/QUEUE_WORKER_RUNTIME_EXAMPLES.md`：worker 运行与守护样例。
5. `docs/runbook/LONG_RUNNING_AUTOPILOT.md`：多轮长跑执行模板。
6. `docs/runbook/STATUS.md`：每轮执行状态与证据。
7. `docs/runbook/CODEX_NONINTERACTIVE_WORKFLOW.md`：非交互 `codex exec` 工作流。
8. `PLANS.md`：执行计划契约模板（配合 `codex exec`）。
9. `docs/runbook/LOCAL_RUNTIME_AND_PREVIEW_DEPS.md`：本地直跑与预览依赖补全。
10. `docs/requirements/ADVANCED_REQUIREMENTS.md`：高级拓展持续迭代需求。
11. `docs/design/ADVANCED_DESIGN.md`：主Agent持续调度设计。
12. `docs/tasks/ADVANCED_TASKS.md`：高级拓展连续任务池。

## 2. 执行顺序（落地）
1. 运行统一入口：`bash scripts/run-all.sh`。
2. 若 `P0` 全部通过，按 `TASKS.md` 推进 `P1`。
3. 发布前按 `OPERATIONS_RUNBOOK.md` 走完整发布与回滚校验。
4. 执行结果记录到 `docs/runbook/STATUS.md`。
5. 非交互执行可用：`bash scripts/codex/noninteractive-exec.sh <prompt-file>`。
6. 高级拓展主循环：
   - `bash scripts/codex/validate-advanced-loop.sh`
   - `MAX_ROUNDS=0 bash scripts/codex/continuous-main-agent.sh`

## 3. 文档分层
- `docs/tasks/`：任务管理与优先级。
- `docs/runbook/`：运维流程、值班、应急、长周期执行。
  - 含本地运行与预览依赖补全说明：`LOCAL_RUNTIME_AND_PREVIEW_DEPS.md`
- `docs/design/`：设计方案与关键决策。
- `docs/requirements/`：需求范围与约束。

## 4. 统一验证入口
- 本地/容器统一命令：`bash scripts/run-all.sh`
- 日志位置：`storage/logs/run-all-<UTC_TIMESTAMP>.log`
