FROM nginx:1.25

# 権限を調整（ログフォルダなど）
RUN mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www

# Laravel本体（/var/www/html）をマウントで受け取る

# nginx設定をコピー
COPY default.conf /etc/nginx/conf.d/default.conf
