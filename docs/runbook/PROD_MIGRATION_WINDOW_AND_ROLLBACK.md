# 生产迁移执行窗口与回滚步骤（Runbook）

## 1. 目标
- 在可控低峰窗口内完成数据库迁移，确保可回滚、可观测、可审计。
- 适用于涉及结构变更、索引变更、数据修复脚本的生产发布。

## 2. 执行窗口定义
- 推荐窗口：业务低峰（例如 UTC 01:00-04:00，按实际业务峰谷调整）。
- 冻结要求：窗口开始前 30 分钟冻结非紧急发布。
- 角色分工：
  - 指挥（Release Owner）：统一决策继续/暂停/回滚。
  - 执行（DB/应用）：执行命令并反馈结果。
  - 观察（Observer）：监控错误率、延迟、队列积压。

## 3. 触发迁移前检查（Go/No-Go）
1. 代码版本已固定（tag/commit 已确认）。
2. 已完成预演：`--pretend` SQL 审核通过。
3. 备份可用：数据库快照 + `storage` + `.env`。
4. 应用健康：核心接口冒烟通过。
5. 回滚资源就绪：上一稳定版本镜像/包可立即部署。

## 4. 标准执行步骤（生产）
1. 切换维护窗口公告（若有）。
2. 备份：
   - 数据库快照（RDS snapshot 或逻辑备份）
   - 文件存储与配置备份
3. 预检查：
   - `php artisan migrate:status`
   - `php artisan migrate --pretend`
4. 执行迁移：
   - `php artisan migrate --force`
5. 迁移后检查：
   - `php artisan migrate:status`
   - 运行核心冒烟测试（认证/上传/资源查询）
6. 解除维护窗口，持续观察 30-60 分钟。

## 5. 回滚策略

### 5.1 回滚触发条件（任一满足即触发）
- 发布后 10 分钟内核心接口错误率持续高于基线 2 倍。
- 出现阻塞级数据库错误（锁等待/超时/关键表不可用）。
- 上传主链路不可用且 5 分钟内无法恢复。

### 5.2 分级回滚动作
- L1（应用回滚）：仅回滚应用版本，保留已完成且兼容的迁移。
- L2（迁移回滚）：执行 `php artisan migrate:rollback --step=<N> --force`。
- L3（灾备恢复）：从快照恢复数据库，并回滚应用与存储到一致时间点。

### 5.3 标准回滚步骤
1. 指挥宣布进入回滚，冻结新请求变更。
2. 应用回切至上一稳定版本。
3. 若需 DB 回滚：执行 `migrate:rollback`（按预案 step）。
4. 若 DB 不可逆变更：启用快照恢复流程。
5. 执行冒烟验证并记录事件时间线。

## 6. 验证命令（示例）
```bash
php artisan migrate:status
php artisan migrate --pretend
php artisan migrate --force
php artisan migrate:rollback --step=1 --force
php artisan test --testsuite=Feature
```

## 7. 完成定义（Done Criteria）
- 迁移执行与验证记录完整（命令、结果、时间、执行人）。
- 未触发回滚，且观察窗口内关键指标恢复/稳定。
- 如触发回滚：回滚后服务恢复，事故报告与改进项已登记。

## 8. 记录模板
```md
- 发布批次: YYYYMMDD-XX
- 窗口: YYYY-MM-DD HH:mm ~ HH:mm (UTC)
- 版本: <tag/commit>
- 迁移命令与结果:
  - <cmd>
  - <result>
- 指标观察:
  - 错误率:
  - 延迟:
  - 队列积压:
- 是否回滚: 是/否
- 回滚级别: L1/L2/L3
- 结论: DONE/PARTIAL/BLOCKED
- 后续动作:
```
