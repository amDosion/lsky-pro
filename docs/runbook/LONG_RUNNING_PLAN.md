# Long-Running Execution Plan

## Mode
- 模式: 持续迭代（continuous iteration）
- 主记录: `docs/runbook/STATUS.md`
- 规则: 本文件只定义迭代机制，不再固定写死某一轮任务明细。

## Source of Truth
- Requirements: `docs/requirements/REQUIREMENTS.md`
- Design: `docs/design/DESIGN.md`
- Tasks: `docs/tasks/TASKS.md`
- Latest iteration status: `docs/runbook/STATUS.md` 的最后一个 Iteration 段落

## Iteration Contract
1. 每轮开始前，从 `docs/runbook/STATUS.md` 最新迭代读取 `下一步/风险/阻塞` 作为输入。
2. 执行范围严格受 `PLANS.md` 与当轮 prompt 约束，默认非破坏式执行。
3. 每轮结束必须写回 `docs/runbook/STATUS.md`：
   - 状态（DONE/PARTIAL/IN_PROGRESS）
   - 已完成项与变更文件
   - 验证命令与结果
   - 残留风险与下一轮计划

## Exit Criteria (Per Iteration)
- 代码与文档改动完成并可追溯。
- 至少完成约定的验证命令（如 `bash -n`、`bash scripts/codex/validate.sh`、目标脚本冒烟）。
- `docs/runbook/STATUS.md` 已追加最新迭代记录。

## Next
- 始终以 `docs/runbook/STATUS.md` 最新迭代中的 `下一步` 为准，进入下一轮闭环。
