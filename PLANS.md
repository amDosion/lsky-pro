# PLANS.md

## Purpose
Use this file as the execution contract for non-interactive Codex runs (`codex exec`) and long-running multi-agent loops.

## Global Rules
1. Prefer non-destructive checks by default.
2. Any destructive operation requires explicit gate:
   - `ALLOW_DESTRUCTIVE=1`
   - environment must not be production unless explicitly approved.
3. Every iteration must emit evidence:
   - changed files
   - commands executed
   - pass/fail results
   - residual risks

## Plan Template
```md
## Iteration <N> - <YYYY-MM-DD>
- Goal:
- Scope:
- Owner agents:
- Constraints:
- Gate checks:
  - [ ] Syntax
  - [ ] Runtime checks
  - [ ] Smoke checks
  - [ ] Docs/status updated
- Commands:
  - <cmd1>
  - <cmd2>
- Expected outputs:
- Rollback:
```

## Default Execution Order
1. `bash scripts/run-all.sh`
2. `docker exec lsky-pro php artisan route:list`
3. `docker exec lsky-pro php artisan migrate:status`
4. `docker exec lsky-pro php artisan queue:failed`
5. update `docs/runbook/STATUS.md`

## Definition of Done
- `docs/tasks/TASKS.md` target items marked `DONE`
- latest `run-all` log exists and passed
- no untracked high-severity risks
