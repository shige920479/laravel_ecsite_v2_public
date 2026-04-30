#!/bin/bash
set -e

echo "=========================================="
echo "  Laravel 公開用クリーンアップツール"
echo "=========================================="

# 削除対象一覧（dry-run 表示用）
TARGETS=(
    "src/--coverage-text"
    "src/app/Http/Controllers/TestController.php"
    "src/resources/views/dev"
    "src/routes/dev"
    "src/progress.txt"
    "src/public/images/mug*.jpg"
    "src/public/images/towel*.jpg"
    "src/public/images/shop*.jpg"
    "src/storage/app/public/uploads/item-images/*"
    "src/storage/app/public/uploads/shops/*"
    "src/.phpstorm.meta.php"
    "src/.phpunit.result.cache"
    "src/node_modules"
    "src/vendor"
)

echo ""
echo "🔍 以下のファイル・ディレクトリを削除します："
echo ""

for TARGET in "${TARGETS[@]}"; do
    echo " - $TARGET"
done

echo ""
read -p "本当に削除しますか？ (y/N): " CONFIRM

if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
    echo "キャンセルしました。"
    exit 1
fi

echo ""
echo "🧹 削除処理を開始します..."
echo ""

# 削除処理
for TARGET in "${TARGETS[@]}"; do
    if ls $TARGET >/dev/null 2>&1; then
        rm -rf $TARGET
        echo "✔ 削除: $TARGET"
    else
        echo "⚠ なし: $TARGET"
    fi
done

echo ""
echo "=========================================="
echo "  Clean up 完了しました！"
echo "=========================================="