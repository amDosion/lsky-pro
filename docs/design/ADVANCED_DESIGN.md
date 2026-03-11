# LSky Advanced Design (Main-Agent Continuous Loop)

## 1. 架构原则
- 主Agent持续活跃：负责读任务、分发、验收、回写状态。
- 团队Agent分工：frontend/backend/ops/qa/security/reviewer。
- Non-interactive：统一走 `scripts/codex/noninteractive-exec.sh`。

## 2. 连续循环模型
1. 读取任务源：`docs/tasks/ADVANCED_TASKS.md`
2. 领取下一条 `TODO` 任务，标记 `IN_PROGRESS`
3. 生成本轮 prompt（含约束、验证、交付）
4. 调用 non-interactive 执行
5. 成功则标记 `DONE`，失败回滚为 `TODO`
6. 记录到 `docs/runbook/STATUS.md`
7. 检测是否还有下一条任务；若有继续，无则退出

## 3. 数据与状态文件
- 需求：`docs/requirements/ADVANCED_REQUIREMENTS.md`
- 设计：`docs/design/ADVANCED_DESIGN.md`
- 任务：`docs/tasks/ADVANCED_TASKS.md`
- 状态：`docs/runbook/STATUS.md`

## 4. 安全门禁
- 默认不执行破坏式操作。
- 若任务需要迁移/删除等动作，需 `ALLOW_DESTRUCTIVE=1`。

## 5. 失败恢复
- 任务执行失败自动回退为 `TODO`。
- 状态文档追加失败原因与下一轮策略。

## 6. 可扩展性
- 通过新增任务条目实现“无限扩展”。
- 支持引入 feasibility 子代理先评估后落地。
