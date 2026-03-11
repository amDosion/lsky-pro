# 运维闭环 Runbook（部署/回滚/告警/应急）

## 1. 目标与适用范围
- 目标：建立可执行、可回退、可告警、可应急的生产运维闭环。
- 范围：LSky Pro 生产与预生产环境。
- 关联文档：
  - 迁移窗口与回滚：`docs/runbook/PROD_MIGRATION_WINDOW_AND_ROLLBACK.md`
  - 队列运行示例：`docs/runbook/QUEUE_WORKER_RUNTIME_EXAMPLES.md`
  - 长周期执行：`docs/runbook/LONG_RUNNING_AUTOPILOT.md`

## 2. 部署流程（标准）
1. 发布前准备
- 固定发布版本（tag/commit）。
- 执行 P0 验证：`bash scripts/run-all.sh`。
- 确认备份可用（DB + storage + `.env`）。

2. 发布执行
- 拉起新版本容器/进程。
- 执行迁移（低峰窗口）：`php artisan migrate --force`。
- 平滑重启队列：`php artisan queue:restart`。

3. 发布后验证
- 冒烟验证（认证/上传/资源查询）。
- 观察 30-60 分钟：错误率、延迟、队列积压。

## 3. 回滚流程（标准）
1. 触发条件（任一满足）
- 核心接口错误率持续高于基线。
- 发布后出现阻塞级故障且短时不可恢复。

2. 回滚动作
- L1：应用版本回退。
- L2：迁移回滚（可逆）`php artisan migrate:rollback --step=<N> --force`。
- L3：快照恢复（不可逆结构变更时）。

3. 回滚后验收
- 核心接口恢复可用。
- 队列消费恢复。
- 事故记录与改进项登记。

## 4. 告警规则（最小可用）
- A1：HTTP 5xx 错误率 > 2% 持续 5 分钟。
- A2：P95 延迟 > 基线 2 倍持续 10 分钟。
- A3：队列积压持续增长 10 分钟。
- A4：失败任务数在 10 分钟内持续上升。

告警分级：
- P1：核心链路不可用（需立即处理）。
- P2：性能显著退化（30 分钟内处理）。
- P3：非关键异常（纳入迭代修复）。

## 5. 应急处置流程（Incident）
1. 发现与分级
- 值班确认告警真实性并定级。

2. 止血
- 选择最小影响策略：限流、降级、回滚。

3. 诊断
- 查看日志：`tail -n 200 storage/logs/laravel.log`
- 查看队列：`php artisan queue:failed`
- 查看路由/配置：`php artisan route:list`、`php artisan config:show app`

4. 恢复
- 执行回滚或热修。
- 完成冒烟验证与指标复核。

5. 复盘
- 24 小时内提交事故复盘（时间线、根因、改进项、责任人）。

## 6. 验收清单
- [ ] 发布前已执行 `scripts/run-all.sh`，并留存日志。
- [ ] 部署后迁移/队列/冒烟验证通过。
- [ ] 告警规则已在监控平台配置并触发测试通过。
- [ ] 应急流程完成一次演练并记录。

## 7. 完成定义（DoD）
- 部署、回滚、告警、应急四项均有可执行步骤和命令。
- 新值班同学可按文档独立完成一次发布与一次故障演练。
- 文档与 `docs/tasks/TASKS.md`、`scripts/run-all.sh` 保持一致。
