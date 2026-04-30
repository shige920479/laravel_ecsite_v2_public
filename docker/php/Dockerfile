FROM php:8.3-fpm

# ------------------------------------------------------------
# 1) 基本パッケージと PHP 拡張
# ------------------------------------------------------------
RUN apt-get update -qq && \
    apt-get install -y \
        git \
        unzip \
        zip \
        curl \
        vim \
        less \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libfreetype6-dev \
        libonig-dev \
        libicu-dev \
        libsqlite3-dev \
    && docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ \
        --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip \
        gd \
        intl \
        bcmath

RUN pecl install redis \
    && docker-php-ext-enable redis
# OPCache（Laravel で必須級の高速化）
RUN docker-php-ext-install opcache
# PCOV
RUN pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo "pcov.enabled=1" > /usr/local/etc/php/conf.d/pcov.ini \
    && echo "pcov.directory=/var/www/html" >> /usr/local/etc/php/conf.d/pcov.ini

# ------------------------------------------------------------
# 2) Composer
# ------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------
# 3) 開発用ユーザー（developer）を作成（UID/GID=1000 に揃える）
# ------------------------------------------------------------
ARG UID=1000
ARG GID=1000

RUN groupadd -g ${GID} developer \
    && useradd -u ${UID} -g developer -m developer \
    && usermod -aG www-data developer

# ------------------------------------------------------------
# 4) entrypoint を配置　（既にホスト側で　chmod +x docker/php/entrypoint.sh 実行済み）
# ------------------------------------------------------------
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# ------------------------------------------------------------
# 5) 作業ディレクトリ
# ------------------------------------------------------------
WORKDIR /var/www/html

# ------------------------------------------------------------
# 6) PHP-FPM を developer ユーザーで動かす
# ------------------------------------------------------------
USER developer

# ------------------------------------------------------------
# 7) php.ini をコピー
# ------------------------------------------------------------
COPY php.ini /usr/local/etc/php/php.ini

# ------------------------------------------------------------
# 8) 環境準備・アプリ起動
# ------------------------------------------------------------
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]