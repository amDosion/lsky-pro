# LSky Pro 执行清单（Long-Running Final）

## 统一执行入口
- `bash scripts/run-all.sh`（P0 全流程）
- 最新验证日志：`storage/logs/run-all-20260304T164331Z.log`

## P0（已完成）
- [x] `P0-T001` 基线健康检查与启动闭环
- 状态: DONE
- 验证: `bash scripts/health/startup.sh`

- [x] `P0-T002` 配置基线固化与自检
- 状态: DONE
- 验证: `bash scripts/checks/config-baseline.sh`

- [x] `P0-T003` 迁移/Seed 幂等验证
- 状态: DONE
- 验证: `bash scripts/checks/migrate-seed-idempotency.sh`

- [x] `P0-T004` 上传主链路验收
- 状态: DONE
- 验证: `bash scripts/acceptance/upload-mainline.sh`

- [x] `P0-T005` 接口冒烟测试集
- 状态: DONE
- 验证: `bash scripts/acceptance/api-smoke.sh`

- [x] `P0-T006` CI 最小闭环
- 状态: DONE
- 验证: `bash scripts/ci/smoke.sh`
- CI: `.github/workflows/smoke-ci.yml`

## P1（已完成）
- [x] `P1-T101` 安全基线修复（冻结账号登录/token、XSS、升级链路安全、CORS 收紧）
- 状态: DONE

- [x] `P1-T102` 队列化与重试/死信（图片删除异步）
- 状态: DONE
- 验证: `php artisan queue:work redis --queue=image-delete --once`

- [x] `P1-T103` 结构化日志与审计告警基础
- 状态: DONE
- 交付: `request_id/trace_id` 中间件、`audit` channel、限流告警反馈

- [x] `P1-T104` 性能优化（索引/查询）
- 状态: DONE
- 验证: 索引 migration 已执行为 `Ran`；上传频控与列表查询已优化

- [x] `P1-T105` 运维与应急 Runbook
- 状态: DONE
- 文档: `docs/runbook/OPERATIONS_RUNBOOK.md`

## P2（已完成）
- [x] `P2-T201` 统一脚本入口
- 状态: DONE
- 交付: `scripts/run-all.sh`

- [x] `P2-T202` 文档体系整理
- 状态: DONE
- 交付: `docs/README.md` + runbook 全集

- [x] `P2-T203` 覆盖率基线能力
- 状态: DONE
- 交付: `scripts/ci/coverage.sh` + `.github/workflows/coverage-ci.yml`

- [x] `P2-T204` 限流与审计增强
- 状态: DONE
- 交付: 管理敏感路由限流 + 审计日志

## 完成定义
- [x] `TASKS` 全项 DONE
- [x] `run-all.sh` 在容器环境全绿
- [x] 关键代码语法检查通过
- [x] 状态与证据写入 `docs/runbook/STATUS.md`

## 角色矩阵模板（全局规范）
后续新增任务请使用以下字段，便于跨项目统一校验：
```md
- Task: <id>
- owner: project-lead | frontend | backend | ops | qa | security | reviewer
- security-review: pending | pass | fail
- reviewer: pending | pass | fail
- verification:
  - <cmd>
```
