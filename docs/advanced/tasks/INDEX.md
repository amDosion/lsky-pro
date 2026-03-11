# 高级功能任务索引（docs/advanced/tasks）

> 范围基于 `docs/advanced/requirements` 与 `docs/advanced/design` 的 8 个高级功能主菜单页面。
>
> `templates-tasks.md` 保留 `process-template` 后台能力任务说明，不计入当前主菜单页索引。

## 任务文件总览

| 功能标识 | 任务文档 | 优先级 | 里程碑 | 主负责人 | 状态 |
|---|---|---|---|---|---|
| image-process | [image-process-tasks.md](./image-process-tasks.md) | P0 | M1 | frontend | TODO |
| drivers | [drivers-tasks.md](./drivers-tasks.md) | P0 | M1 | backend | TODO |
| team-permissions | [team-permissions-tasks.md](./team-permissions-tasks.md) | P0 | M1 | backend | TODO |
| ai-config | [ai-config-tasks.md](./ai-config-tasks.md) | P1 | M3 | backend | TODO |
| jobs | [jobs-tasks.md](./jobs-tasks.md) | P1 | M2 | ops | TODO |
| reviews | [reviews-tasks.md](./reviews-tasks.md) | P1 | M2 | backend | TODO |
| ai-search | [ai-search-tasks.md](./ai-search-tasks.md) | P2 | M3 | frontend | TODO |
| ai-prompt | [ai-prompt-tasks.md](./ai-prompt-tasks.md) | P2 | M3 | frontend | TODO |

## 跨页依赖速览

- `drivers -> image-process`
- `team-permissions -> image-process/ai-search/ai-prompt/jobs/reviews`
- `ai-config -> ai-search/ai-prompt`
- `image-process -> process-template`
- `process-template -> jobs`
- `reviews -> ai-search`

## 全局完成定义

- 8 个主菜单功能任务文档子任务均完成并具备证据。
- `process-template` 后台能力任务说明与 API 回归同步维护。
- 每个功能均完成 `security` 与 `reviewer` 门禁。
- 回归清单执行并记录结果。
- 运行脚本验证通过。

## 统一验证命令

```bash
bash scripts/codex/validate.sh
bash scripts/codex/validate-advanced-loop.sh
bash scripts/acceptance/api-smoke.sh
```

```bash
# 建议发布前执行全链路校验
bash scripts/run-all.sh
```
