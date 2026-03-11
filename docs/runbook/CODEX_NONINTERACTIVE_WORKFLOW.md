# Codex Non-Interactive Workflow

## Goal
Use `codex exec` + `PLANS.md` for deterministic, repeatable, non-interactive execution.

## Inputs
- Plan contract: `PLANS.md`
- Prompt file: `prompts/<task>.md`

## Run
```bash
bash scripts/codex/noninteractive-exec.sh prompts/task.md
```

## Audit Output
- `noninteractive-exec.sh` 会将 Codex 最后一条输出写入：
- `storage/logs/codex/codex-<UTC_TIMESTAMP>.txt`

## Conventions
1. Prompt must include:
- objective
- scope/files
- validation commands
- done criteria
2. Use non-destructive mode first.
3. Write outcomes to `docs/runbook/STATUS.md`.

## Validation
```bash
bash scripts/codex/validate.sh
```

- CI smoke: `.github/workflows/codex-workflow-smoke.yml`

## Suggested prompt skeleton
```md
Read PLANS.md and execute Iteration N.
Scope: <files>
Constraints: no destructive ops unless ALLOW_DESTRUCTIVE=1.
Validation:
- bash scripts/run-all.sh
- docker exec lsky-pro php artisan migrate:status
Output:
- changed files
- command results
- residual risks
```

## Advanced Continuous Loop
```bash
bash scripts/codex/validate-advanced-loop.sh
MAX_ROUNDS=0 bash scripts/codex/continuous-main-agent.sh
```

- `MAX_ROUNDS=0` 表示持续执行，直到 `docs/tasks/ADVANCED_TASKS.md` 中没有 `TODO`。
- 每轮自动写入 `docs/runbook/STATUS.md`。
