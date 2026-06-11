# 🛒 Laravel ECサイト（Portfolio Version）

Laravel 12 を使用して構築した **生活雑貨 EC サイト** のポートフォリオ公開用リポジトリです。  
認証、認可、権限制御、商品管理、在庫管理、決済、画像配信最適化、API連携、テストコードなど、実務で利用されるLaravel機能を幅広く実装しています。  
  
>追加実装①：2026/01 商品レビュー機能・ランキング機能（閲覧数/販売数/高評価）  
>追加実装②：2026/04 機能拡張および構成改善（React部分導入 / API連携 / S3 + CloudFront + WebP対応）  
>追加実装③：2026/06 認証・イベント・権限制御機能の拡張  
  （Google OAuth認証 / Event・Listenerによるイベント駆動処理 / Spatie Permissionによるロール管理）
  

公開URL &nbsp;&nbsp;: <a href="https://portfolio-sh0212.com/laravel_ecsitev2/" target="_blank">https://portfolio-sh0212.com/laravel_ecsitev2/</a> &nbsp;&nbsp;&nbsp; ( PC表示を前提として制作しています ) <br>
🎥 デモ動画（約4分） &nbsp;: <a href="https://youtu.be/xuvf-eTow_I" target="_blank">https://youtu.be/xuvf-eTow_I</a> 
 
<!-- --- -->

## 🚀 特徴 / 実装機能

### 🔐 認証機能
- ユーザー / オーナー / 管理者（複数ガード）
- Fortify をベースにしたログイン
- メール認証 (production では外しています)
- Google OAuth認証（Laravel Socialite）
- 各機能のご確認には以下をご利用ください（ユーザーは他99人分のダミーを登録）
---
  | ユーザー | メールアドレス | password | ログインURL |
  |----------|---------|---------|---------|
  | ユーザー | user@mail.com | user123 | <a href="https://portfolio-sh0212.com/laravel_ecsite/login" target="_blank">ユーザーログイン画面</a>
  | オーナー | owner1@mail.com ～ owner5@mail.com | owner123 (共通) | <a href="https://portfolio-sh0212.com/laravel_ecsite/owner/login" target="_blank">オーナーログイン画面</a>
  | 管理者 | admin@mail.com | admin123 | <a href="https://portfolio-sh0212.com/laravel_ecsite/admin/login" target="_blank">管理者ログイン画面</a>
---

### 🏪 出展者（オーナー）機能
- 店舗プロフィール編集
- 商品登録（画像アップロード対応・Intervention Image）
- 商品編集 / 削除
- 商品画像の複数枚管理（並び順、更新、削除）
- 在庫管理（入庫・在庫数管理）
- 入出庫履歴の一覧表示
- 在庫データのCSVエクスポート / インポート

---

### 🛍 ユーザー機能
- 商品一覧（絞り込み・検索・ページネーション・並び替え）
- お気に入り機能（Ajax）
- カート機能（Ajax）
- 注文確認
- Stripe Checkout による決済処理
- 決済ステータスに応じたメール通知　※Mailtrapへ送信
- マイページ機能
  - 注文履歴一覧（配送ステータスの表示）
  - 投稿レビュー一覧
  - アカウント情報登録（住所・電話番号など）※必須項目未登録時は購入制限
- レビュー投稿・評価
- ランキング表示（閲覧数・販売数・高評価）※Cache::remember()の活用によるパフォーマンス向上
- 一部画面に React を導入し、API連携による動的UIを実装（商品一覧、詳細、カート、お気に入り、レビュー投稿・一覧）

---

### 🛠 管理者機能
- 管理者アカウント管理（作成・編集・論理削除・復元）
- Spatie Permission を利用したロール・権限管理
- Gate を利用した管理画面アクセス制御

---

### 💳 決済（Stripe）
- Stripe Checkout の実装
- Stripe Webhook による決済状態の反映（成功 / 期限切れ / 支払い失敗）
- スナップショット（checkout_request / checkout_items）を用いた注文データの保持
- Webhook イベントの DB 保存（ログ）
- 「決済成功・キャンセル」画面
- Event / Listener を利用した通知処理の分離

---

### 🖼 画像アップロード
- Intervention Image を利用した画像加工 / WebP 形式へ統一し軽量化
- 一時保存（tmp） → 最終保存のフロー
- 複数画像管理（並び順・削除・更新）
- S3 を利用した画像ストレージ
- CloudFront（CDN）による高速配信

---

### 🧪 テストコード
- PHPUnit（Laravel Test）
- Feature テスト（コントローラ / サービス / webhook / ポリシー）
- Unit テスト（バリデーション / サービスロジック）
- テスト専用ファクトリ
- SQLite を使った高速テスト

---

### 📄 ダミーデータ
- 購入回数・購入商品数にばらつきを持たせた注文データ
- レビュー数・評価・閲覧数に相関を持たせた商品データなど

---

### 🗓️ スケジューラー

- データベース情報をリセット（1回/日）※ポートフォリオ用途のため常に初期状態を維持　

---

## 🏗 技術スタック

- **フレームワーク:** Laravel 12
- **インフラ / ストレージ:** AWS S3 / CloudFront
- **データベース:** MySQL
- **フロントエンド:** Blade / React（部分導入） / Vite / Tailwind少し
- **認証:** Laravel Fortify / Laravel Socialite
- **認可:** Gate / Policy / Spatie Permission
- **画像関連:** Intervention Image / SortableJS / Swiper
- **決済:** Stripe sandbox（Checkout + Webhook）
- **テスト:** PHPUnit
- **開発環境:** Docker (nginx / PHP-FPM / MySQL)
- **実行環境:** Apache（Xserver）
- **メール:** Mailtrap / Laravel Mail / Queue

---

## ➡️ 今後の予定

### 1. 機能拡張（バックエンド）
- ユーザー機能
  - 注文キャンセル処理（在庫・決済状態との整合性を考慮）
  - 閲覧履歴 / 最近見た商品

- オーナー機能
  - 売上・注文数などの簡易分析ページ

---

### 2. リファクタリング / 設計改善
- DTO / Resource クラスの適用範囲をオーナー機能側へ拡張

---

### 3. インフラ / 運用
- AWS環境へのデプロイ（本番構成の検証）
- CI/CD（GitHub Actions）による自動テスト・デプロイ