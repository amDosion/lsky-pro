# Security Baseline (Phase 1)

## Runtime
- APP_ENV=prod
- APP_DEBUG=false
- LOG_LEVEL=warning 或 error

## Session/Cookie
- SESSION_SECURE_COOKIE=true (HTTPS)
- SESSION_HTTP_ONLY=true
- SESSION_SAME_SITE=lax 或 strict

## API
- Token 应支持有效期与最小权限 scopes
- 冻结账户禁止登录与 token 发放

## Frontend
- 不可信数据禁止 `.html()` 注入
- 默认使用转义输出

## Upgrade
- 禁止在生产直接执行未签名在线补丁
- 升级前必须完成 DB + storage + .env 备份

## File Permissions
- `.env` 权限建议 `640`（所有者可读写，组可读）
