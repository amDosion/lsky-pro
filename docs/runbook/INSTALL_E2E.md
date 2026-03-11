# Install E2E

## Purpose

This runbook validates fresh-environment startup without touching the live DB.
It covers two isolated scenarios:

1. `Web Install -> Login`
2. `Bootstrap -> Login`

Do not merge these into one container stack. `INIT_AUTO_BOOTSTRAP=true` will
create `installed.lock`, so the `/install` flow no longer exists in that stack.

## Safety Rules

- Never reuse the live `.env` for install E2E.
- Never point E2E compose to the live DB.
- Keep `ALLOW_DESTRUCTIVE=0`.
- Use disposable Docker stacks only.

## Files

- Compose: [docker-compose.install.yml](/root/code-server/config/workspace/lsky-pro/lsky/deploy/e2e/docker-compose.install.yml)
- Compose: [docker-compose.bootstrap.yml](/root/code-server/config/workspace/lsky-pro/lsky/deploy/e2e/docker-compose.bootstrap.yml)
- Script: [e2e-install-login.sh](/root/code-server/config/workspace/lsky-pro/lsky/scripts/qa/e2e-install-login.sh)
- Script: [e2e-bootstrap-login.sh](/root/code-server/config/workspace/lsky-pro/lsky/scripts/qa/e2e-bootstrap-login.sh)

## Run

```bash
bash scripts/qa/e2e-install-login.sh
bash scripts/qa/e2e-bootstrap-login.sh
```

## Expected Results

- Install stack:
  - `/install` is reachable
  - install POST returns `status=true`
  - `/` redirects to `/login`
  - admin login redirects to `/dashboard`
- Bootstrap stack:
  - `installed.lock` exists after startup
  - `/install` returns `404`
  - `/login` is reachable
  - admin login redirects to `/dashboard`

## Notes

- The runtime image is kept separate from 1Panel production compose.
- `.dockerignore` excludes `.env`, `installed.lock`, `storage`, `bootstrap/cache`,
  `public/uploads`, and SQLite test files so fresh-install validation is not
  polluted by host state.
