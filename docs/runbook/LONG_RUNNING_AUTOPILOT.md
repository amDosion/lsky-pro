# Long-Running Multi-Agent Autopilot

## Objective
Use iterative multi-agent execution to continuously deliver production-safe changes until release gates are satisfied.

## Scope
- Source task list: `docs/tasks/TASKS.md`
- Status ledger: `docs/runbook/STATUS.md`
- Iteration plan: `docs/runbook/LONG_RUNNING_PLAN.md`

## Loop Cadence
Each iteration runs in this order:
1. `Plan`
- pick highest-priority unblocked tasks from `docs/tasks/TASKS.md`
- define file ownership per agent
2. `Parallel Execute`
- spawn focused agents (one concern per agent)
- avoid overlapping file ownership
3. `Integrate`
- consolidate outputs
- resolve conflicts centrally
4. `Verify`
- syntax checks
- route/config/migration checks in runtime container
- targeted behavior checks
5. `Record`
- append outcomes in `docs/runbook/STATUS.md`
- mark DONE/PARTIAL/BLOCKED
6. `Decide Next`
- continue next highest-priority task if gates pass

## Agent Roles
- `security-agent`: authn/authz, XSS, upgrade safety
- `performance-agent`: indexes, query tuning, cache/queue
- `ops-agent`: runbook, rollout, rollback, incidents
- `integration-agent`: final merge + validation

## Trigger Conditions (Start New Iteration)
Start a new iteration when any condition matches:
1. 有 `P0/P1` 任务处于 `TODO` 且依赖已满足。
2. 上一轮状态为 `PARTIAL`，且阻塞条件已解除。
3. 生产事件/告警要求紧急修复（需标记 `HOTFIX`）。
4. 例行节奏触发（每日或每半日固定窗口）。

## Checkpoint Gates (Per Iteration)
- `G1` Code compiles / syntax valid
- `G2` Runtime command checks pass (inside container)
- `G3` No new high-severity risk introduced
- `G4` Status + evidence written

If any gate fails:
- mark iteration `BLOCKED`
- record blocker + owner + fallback
- execute smallest safe rollback for that iteration

## Iteration Done Definition
An iteration is `DONE` only if all are true:
1. 该轮目标任务交付物已完成（代码/文档/配置）。
2. 验证命令全部通过，结果可复现。
3. `STATUS.md` 已记录变更文件、命令、风险与下一步。
4. 不存在未登记的高风险残留问题。

## Iteration Acceptance Checklist
- [ ] 任务选择符合优先级与依赖顺序。
- [ ] 文件所有权无冲突（并行 agent 未重叠改同文件）。
- [ ] 关键验证命令执行并有结果证据。
- [ ] 失败/阻塞有明确 owner 与处理时限。
- [ ] 下一轮输入清晰（下一任务、前置条件、风险）。

## Release Gate (One-Shot Landing)
Release allowed only when all are true:
1. P0 tasks are DONE
2. P1 security baseline tasks are DONE
3. required migrations are validated (`--pretend` + reviewed SQL)
4. runtime smoke checks pass
5. runbook includes deploy + rollback + incident procedures

## Blocking Rules
- Do not execute destructive DB ops without explicit low-traffic window.
- Do not broaden permissions or wildcard CORS for production defaults.
- Do not merge changes lacking file-level verification evidence.

## Evidence Template
For each task:
- changed files
- commands run
- key output
- residual risk
- next action

## Multi-Round Execution Template
```md
## Iteration <N> - <YYYY-MM-DD>
- 状态: TODO | DOING | PARTIAL | BLOCKED | DONE
- 触发条件: <定时/阻塞解除/HOTFIX/P0-P1 ready>
- 目标任务: <task-id list>
- 依赖校验: <已满足/未满足 + 说明>

### 执行摘要
- 负责人/Agent:
- 变更文件:
- 核心动作:

### 验证命令与结果
- `<cmd1>` -> `<result>`
- `<cmd2>` -> `<result>`

### 完成定义核对
- [ ] 交付物齐全
- [ ] 验证通过
- [ ] 状态已记录
- [ ] 残留风险已登记

### 验收清单核对
- [ ] 优先级/依赖正确
- [ ] 文件所有权无冲突
- [ ] 阻塞处理明确
- [ ] 下一轮输入明确

### 风险与阻塞
- 风险:
- 阻塞:
- 处理人:
- 截止时间:

### 下一轮计划
- 下一任务:
- 进入条件:
```
