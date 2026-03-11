# COMBINED_ADVANCED_SCENARIOS

## 1. 场景目标

将高级功能从单点工具升级为可执行的组合流程，覆盖图片处理、审核、检索、团队协作、批处理、AI 配置与作业运维。

## 2. 组合流程清单

### 场景 A：素材标准化生产线（设计团队）

1. `process-template` 通过后台能力创建标准处理模板（尺寸、滤镜、水印）
2. `jobs` 派发异步批处理并轮询状态
3. `drivers` 校验处理驱动可用性
4. `ai-search` 检索处理结果并复核标签/OCR

交付结果：同规格素材批量产出，任务可追踪可重试。

### 场景 B：合规审核闭环（运营 + 安全）

1. `reviews` 对待审图片执行通过/驳回
2. `team-permissions` 校验审核空间成员角色
3. `jobs` 对驳回素材触发重处理任务
4. `ai-prompt` 生成整改提示词给内容团队

交付结果：审核与整改形成闭环，角色边界清晰。

### 场景 C：多团队协作发布（企业空间）

1. `team-permissions` 配置 owner/admin/member
2. `process-template` 维护共享模板（`is_shared=true`）
3. `jobs` 各团队按模板批量执行
4. `ai-search` 跨标签检索资产，完成发布前复核

交付结果：模板复用、权限隔离、协作一致。

### 场景 D：问题排障与恢复（运维）

1. `drivers` 检查当前驱动与可用驱动列表
2. `jobs` 定位失败/部分成功任务并重试
3. `image-process` 对单图进行参数验证
4. `reviews` 确认恢复后的图片审核状态

交付结果：故障可定位、可修复、可验证。

### 场景 E：AI 能力基线配置（管理员）

1. `ai-config` 选择当前启用 provider
2. `ai-config` 维护 API Key、Base URL、模型列表与默认模型
3. `ai-prompt` 提交生成任务并验证返回结果中的 `provider/model`
4. `ai-search` 或后续 AI 功能复用同一 provider 基线

交付结果：AI 能力的供应商配置统一、可验证、可回归。

## 3. 页面与接口映射

| 页面/能力 | 关键接口 | 组合职责 |
|---|---|---|
| `advanced/image-process` | `POST /advanced-api/images/{key}/process` | 单图参数验证、处理兜底 |
| `advanced/ai-search` | `GET /advanced-api/images/ai-search` | 检索与回查 |
| `advanced/ai-prompt` | `POST /advanced-api/ai/prompt` / `POST /advanced-api/ai/prompt-tasks` | 生成可执行提示词 |
| `advanced/ai-config` | `GET/PUT /advanced-api/ai/config` | 统一配置 provider、密钥与默认模型 |
| `advanced/drivers` | `GET /advanced-api/processing/drivers/status` | 驱动与能力探测 |
| `advanced/reviews` | `GET/POST /advanced-api/admin/reviews*` | 审核动作中心 |
| `process-template`（后台能力） | `GET/POST /advanced-api/process-templates*` | 模板沉淀与分发（无独立主菜单） |
| `advanced/jobs` | `GET/POST /advanced-api/process-jobs*` | 作业调度与恢复 |
| `advanced/team-permissions` | `GET/PUT /advanced-api/spaces*` | 空间协作与权限治理 |

## 4. 上线检查项

1. `php artisan view:cache` 通过
2. `php artisan route:list | grep advanced-api` 路由完整
3. `php artisan test --testsuite=Feature` 通过
4. 管理员账号验证 `ai-config` 与 `reviews` 页面动作
5. 非管理员账号验证 AI 配置写入与审核操作的权限拒绝分支
