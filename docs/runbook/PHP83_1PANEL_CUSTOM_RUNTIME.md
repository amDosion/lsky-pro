# PHP 8.3 Custom Runtime For 1Panel

## Purpose

This runbook upgrades the runtime layer for Lsky Pro to PHP 8.3 without mixing
application changes into the upstream `halcyonazure/lsky-pro-docker:latest`
image.

## Why This Is Needed

The current online container was confirmed to use:

- image: `halcyonazure/lsky-pro-docker:latest`
- runtime: `PHP 8.1.28`

That means runtime PHP upgrades are not controlled by repository code alone.
They require switching 1Panel from the upstream image to a custom image or a
Compose build that uses this repository.

## Files Added

- `deploy/php83/Dockerfile`
- `deploy/php83/apache-vhost.conf`
- `deploy/php83/php.ini`
- `deploy/php83/entrypoint.sh`
- `deploy/1panel/docker-compose.php83.yml`
- `deploy/1panel/README.md`

## Runtime Capabilities Included

The custom PHP 8.3 image includes:

- `pdo_mysql`
- `gd`
- `imagick`
- `mbstring`
- `intl`
- `zip`
- `bcmath`
- `exif`
- `pcntl`
- Apache with `DocumentRoot=/var/www/html/public`

## 1Panel Migration Path

1. Copy this repository to the target 1Panel app workspace.
2. Import or adapt `deploy/1panel/docker-compose.php83.yml`.
3. Ensure `.env` points to the correct production database and storage paths.
4. Build the custom image in 1Panel.
5. Start the new service on a temporary port first.
6. Run health checks and application smoke checks.
7. Switch traffic only after login, upload, image preview, and admin pages all pass.

## Rollback

If the custom runtime fails, switch 1Panel back to the previous image:

- `halcyonazure/lsky-pro-docker:latest`

Do not run destructive database commands during runtime cutover.
Rollback should only change the container image/runtime layer.

## Notes

- `INIT_AUTO_BOOTSTRAP=false` is the safe default.
- Enable bootstrap only for a brand-new environment that has not been initialized.
- Health checks and entrypoints must never call bootstrap with `--force`.
- Destructive artisan commands are gated by `ALLOW_DESTRUCTIVE=1`.
- The Compose example keeps `storage`, `bootstrap/cache`, `public/uploads`, and
  `.env` outside the image so upgrades do not wipe mutable runtime data.
