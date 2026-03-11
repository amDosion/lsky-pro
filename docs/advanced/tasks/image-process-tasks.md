# image-process 任务拆解

- 页面路由: `/advanced/image-process`
- API 基线: `POST /advanced-api/images/{key}/process`
- 主负责人: `frontend`
- 优先级: P0

## 子任务

| 子任务ID | 子任务 | owner | 交付物 |
|---|---|---|---|
| image-process-T01 | 页面交互稿与状态机确认（idle/validating/submitting/success/error） | project-lead | 交互验收清单 |
| image-process-T02 | 页面表单、参数校验与结果区渲染 | frontend | 页面实现与交互说明 |
| image-process-T03 | 图片处理接口参数规则与返回结构稳定化 | backend | 控制器/服务实现 |
| image-process-T04 | 处理引擎与依赖检查（Imagick/libvips）及环境基线 | ops | 环境检查清单 |
| image-process-T05 | 单图与异常分支测试（不存在、非图片、非法参数） | qa | 测试报告 |
| image-process-T06 | 参数注入与越权处理评审（space/user 边界） | security | 安全评审记录 |
| image-process-T07 | 独立风险审查与发布建议 | reviewer | 评审结论 |

## 完成定义（DoD）

- 页面可提交合法参数并展示处理结果。
- 失败场景返回可读错误，且保留输入以便重试。
- 接口遵循当前空间与用户隔离，不可越权处理。
- 安全门禁和独立评审均为 `pass`。

## 验证命令

```bash
docker exec lsky-pro php artisan route:list --path=advanced-api/images --no-ansi | grep process
docker exec lsky-pro php -l app/Http/Controllers/Api/V1/ImageController.php
docker exec lsky-pro php -l app/Services/ImageProcessing/ImageProcessExecutor.php
bash scripts/acceptance/api-smoke.sh
```
