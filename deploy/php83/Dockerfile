FROM php:8.3-apache-bookworm

ARG DEBIAN_FRONTEND=noninteractive

RUN set -eux; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        git \
        python3 \
        python3-pip \
        python3-pil \
        python3-torch \
        tesseract-ocr \
        tesseract-ocr-eng \
        tesseract-ocr-chi-sim \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        zlib1g-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libmagickwand-dev \
        libvips-dev \
        libpq-dev \
        ghostscript \
        poppler-utils \
        dcraw \
        libraw-bin \
        libimage-exiftool-perl \
        libheif-examples \
        fonts-noto-cjk \
        libreoffice-core \
        libreoffice-writer \
        libreoffice-calc \
        libreoffice-impress \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        ftp \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && docker-php-ext-install -j"$(nproc)" \
        pgsql \
        pdo_pgsql \
    && pecl install redis \
    && pecl install vips \
    && docker-php-ext-enable vips \
    && docker-php-ext-enable redis \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && tesseract --version >/dev/null \
    && php -m | grep -qx 'pdo_pgsql' \
    && php -m | grep -qx 'pgsql' \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && a2enmod rewrite headers expires remoteip \
    && sed -i "s|rights=\"none\" pattern=\"PDF\"|rights=\"read\" pattern=\"PDF\"|" /etc/ImageMagick-6/policy.xml \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

RUN set -eux; \
    pip3 install --no-cache-dir --break-system-packages "transformers==4.26.1"; \
    python3 - <<'PY'
import torch
from PIL import Image
from transformers import BlipForConditionalGeneration, BlipProcessor
print(torch.__version__)
print(Image.__name__)
print(BlipProcessor.__name__)
print(BlipForConditionalGeneration.__name__)
PY

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY deploy/php83/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/php83/php.ini /usr/local/etc/php/conf.d/zz-lsky.ini
COPY deploy/php83/entrypoint.sh /usr/local/bin/lsky-entrypoint
COPY scripts/image_intelligence/requirements.txt /tmp/image-intelligence-requirements.txt

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

RUN python3 -m pip install --no-cache-dir --break-system-packages -r /tmp/image-intelligence-requirements.txt \
    && rm -f /tmp/image-intelligence-requirements.txt

COPY . .

RUN rm -f .env installed.lock \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        public/uploads \
    && rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi \
    && rm -f .env installed.lock \
    && chown -R www-data:www-data /var/www/html \
    && chmod +x /usr/local/bin/lsky-entrypoint

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    PHP_MEMORY_LIMIT=512M \
    PHP_UPLOAD_MAX_FILESIZE=128M \
    PHP_POST_MAX_SIZE=128M \
    PHP_MAX_EXECUTION_TIME=120 \
    INIT_AUTO_BOOTSTRAP=false

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
  CMD bash /var/www/html/scripts/health/startup.sh || exit 1

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/lsky-entrypoint"]
CMD ["apache2-foreground"]
