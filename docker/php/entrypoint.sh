#!/bin/sh
set -e

echo "🔧 Preparing Laravel directories..."

# Laravel が必要とするディレクトリ
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 権限調整（volume 前提なので chmod のみ）
chmod -R 775 storage bootstrap/cache

echo "✅ Laravel directories are ready."

# command で渡されたものを実行（php-fpm）
exec "$@"