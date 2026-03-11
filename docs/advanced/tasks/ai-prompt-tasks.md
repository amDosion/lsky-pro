# ai-prompt 任务拆解

- 页面路由: `/advanced/ai-prompt`
- API 基线: `POST /advanced-api/ai/prompt`
- 主负责人: `frontend`
- 优先级: P2

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| ai-prompt-T01 | 提示词生成场景与模板策略冻结 | project-lead | 需求边界说明 |
| ai-prompt-T02 | 提示词输入页与结果页交互实现（复制、重试） | frontend | 页面实现 |
| ai-prompt-T03 | Prompt 生成接口、元数据拼装与参数校验 | backend | `AiController@prompt` |
| ai-prompt-T04 | 模型调用配置、超时与降级策略配置 | ops | 运行策略说明 |
| ai-prompt-T05 | 参数边界、并发生成与错误路径回归 | qa | 测试报告 |
| ai-prompt-T06 | 模板注入/隐私字段暴露评审 | security | 安全审计结果 |
| ai-prompt-T07 | 独立评审（可解释性与稳定性） | reviewer | 评审报告 |

## 完成定义（DoD）

- 页面支持输入 `key/intent/template/language` 并成功生成 prompt。
- 错误场景可定位（参数、图片不存在、服务异常）。
- 元数据输出遵循最小暴露原则。
- 安全与 reviewer 审核结论为 `pass`。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/ai/prompt --no-ansi
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/AiController.php
rg -n "prompt|intent|template|metadata" app/Http/Controllers/Api/V1/AiController.php
bash scripts/acceptance/api-smoke.sh
```

