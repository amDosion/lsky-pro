# Prompt Context

Use this file to capture project-specific coding constraints for all workers.

## Global Constraints

- Keep changes minimal and scoped to assigned task.
- Preserve existing behavior unless requirements explicitly allow breaking changes.
- Update docs and tests with code changes.
- For post-review innovation tasks, use `docs/project-overview.md`, `docs/repo-structure-map.md`, and `docs/self-research.md` as mandatory inputs.
- For every review/remediation pass, enforce: performance, security, edge cases, backend domain ownership, UI consistency, modular file split, clear naming, and documentation sync.

## Repository Conventions

- Language and framework conventions:
  - Laravel 10 + Blade + 少量 Alpine/jQuery，不引入新的前端框架。
  - 现有页面中文文案为主，新增 UI 与状态文案保持中文。
  - 优先保持现有 API 结构和响应壳 `status/message/data`。
- Test conventions:
  - 优先补 `tests/Feature/Advanced/AdvancedFeaturePagesTest.php`
  - 对第三方 AI 调用使用 `Http::fake()`。
- Lint/format conventions:
  - PHP 语法/测试通过优先于代码风格工具。
  - 前端通过 `npm run build` 验证打包。

## Delivery Constraints

- Branch/release rules:
  - 当前目录不是 git 仓库，需以文件级改动和验证证据作为交付依据。
- Rollback expectations:
  - 避免不可逆迁移，本轮优先无迁移方案。
