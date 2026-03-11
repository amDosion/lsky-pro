# ai-config 任务拆解

- 页面路由: `/advanced/ai-config`
- API 基线: `/advanced-api/ai/config`
- 主负责人: `backend`
- 优先级: P1

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| ai-config-T01 | provider 元数据、默认模型与校验规则冻结 | project-lead | 配置基线文档 |
| ai-config-T02 | provider 卡片、表单与状态反馈交互实现 | frontend | 页面实现 |
| ai-config-T03 | 配置读取/保存接口与标准化逻辑实现 | backend | `AiConfigController` / `AiProviderConfigService` |
| ai-config-T04 | 默认环境与缓存刷新策略整理 | ops | 运行策略说明 |
| ai-config-T05 | provider 切换、权限与默认值回退回归 | qa | 回归报告 |
| ai-config-T06 | API Key 暴露面与越权写入评审 | security | 安全评审 |
| ai-config-T07 | 独立评审（可维护性与扩展性） | reviewer | 风险清单 |

## 完成定义（DoD）

- 页面可读写 `active_provider/providers` 并返回标准化结果。
- provider ready 状态与默认模型回退逻辑稳定。
- 非管理员保存被拒绝。
- AI 提示词任务可读取配置结果中的 provider/model。
- 安全与 reviewer 双门禁通过。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/ai/config --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/AiConfigController.php
rg -n "AiProviderConfigService|ai/config|active_provider|default_model" app routes/web.php
bash scripts/acceptance/api-smoke.sh
```
