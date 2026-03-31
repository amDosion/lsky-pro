#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

ROLE="${CONTAINER_ROLE:-web}"
QUEUE_CONNECTION_NAME="${LARAVEL_QUEUE_CONNECTION:-${QUEUE_CONNECTION:-redis}}"
WORKER_QUEUES="${LARAVEL_WORKER_QUEUES:-upload-critical,default,ai-prompt,image-process,webhook-events,image-delete}"
WORKER_SLEEP="${LARAVEL_WORKER_SLEEP:-3}"
WORKER_TRIES="${LARAVEL_WORKER_TRIES:-3}"
WORKER_MAX_TIME="${LARAVEL_WORKER_MAX_TIME:-3600}"
WORKER_TIMEOUT="${LARAVEL_WORKER_TIMEOUT:-600}"
SCHEDULE_INTERVAL="${LARAVEL_SCHEDULE_INTERVAL:-60}"

git config --global --add safe.directory /var/www/html >/dev/null 2>&1 || true

# ============================================================
# 自动安装/更新依赖（容器重建后自动恢复）
# ============================================================

# Composer 依赖：如果 vendor 不存在或 composer.lock 有更新则安装
if [ -f composer.json ]; then
  if [ ! -d vendor ] || [ composer.lock -nt vendor/autoload.php ] 2>/dev/null; then
    echo "[entrypoint] Installing composer dependencies..."
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts 2>&1
    composer dump-autoload --no-dev --optimize --no-scripts 2>&1
    echo "[entrypoint] Composer dependencies installed."
  fi
fi

# npm 依赖：如果 node_modules 不存在且有 package.json 则安装（可选，仅开发环境）
# if [ -f package.json ] && [ ! -d node_modules ] && command -v npm >/dev/null 2>&1; then
#   echo "[entrypoint] Installing npm dependencies..."
#   npm install --production 2>&1 || true
# fi

cat > /usr/local/etc/php/conf.d/zz-runtime-overrides.ini <<EOF
memory_limit=${PHP_MEMORY_LIMIT:-512M}
post_max_size=${PHP_POST_MAX_SIZE:-128M}
upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE:-128M}
max_execution_time=${PHP_MAX_EXECUTION_TIME:-120}
EOF

mkdir -p \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  public/uploads
chown -R www-data:www-data storage bootstrap/cache public/uploads || true

# Deployment switches or host bind changes can leave stale Laravel manifests
# behind. Clear them before bootstrapping package discovery.
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php || true

if [ -f artisan ]; then
  if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
  fi
  if [ -f .env ] && ! grep -Eq '^APP_KEY=base64:[A-Za-z0-9+/=]+' .env; then
    php artisan key:generate --force --no-interaction >/dev/null 2>&1
  fi
  php artisan package:discover --ansi >/dev/null 2>&1
  php artisan storage:link >/dev/null 2>&1
  php artisan migrate --force --no-interaction >/dev/null 2>&1
  php artisan config:cache >/dev/null 2>&1
  php artisan view:cache >/dev/null 2>&1
  php artisan route:cache >/dev/null 2>&1

  if [ "${INIT_AUTO_BOOTSTRAP:-false}" = "true" ]; then
    php artisan lsky:bootstrap
  fi
fi

if [ -f .env ] && [ -w .env ]; then
  chown www-data:www-data .env || true
  chmod 664 .env || true
fi

if [ -f installed.lock ] && [ -w installed.lock ]; then
  chown www-data:www-data installed.lock || true
  chmod 664 installed.lock || true
fi

chown -R www-data:www-data storage bootstrap/cache public/uploads || true

if [ -f artisan ]; then
  case "$ROLE" in
    worker)
      echo "[entrypoint] Starting queue worker..."
      exec php artisan queue:work "$QUEUE_CONNECTION_NAME" \
        --queue="$WORKER_QUEUES" \
        --sleep="$WORKER_SLEEP" \
        --tries="$WORKER_TRIES" \
        --max-time="$WORKER_MAX_TIME" \
        --timeout="$WORKER_TIMEOUT"
      ;;
    scheduler)
      echo "[entrypoint] Starting scheduler loop..."
      while true; do
        if ! php artisan schedule:run --no-interaction; then
          echo "[entrypoint] schedule:run failed, retrying after ${SCHEDULE_INTERVAL}s"
        fi
        sleep "$SCHEDULE_INTERVAL"
      done
      ;;
    web)
      ;;
    *)
      echo "[entrypoint] Unknown CONTAINER_ROLE=$ROLE, falling back to provided command."
      ;;
  esac
fi

exec "$@"
