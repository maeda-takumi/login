# Login Starter (PHP + JS + CSS)

汎用ログイン機能を `login` フォルダごと配置して使う想定のシンプル実装です。

## 機能
- ログイン / ログアウト
- SQLiteでユーザ管理
  - `id`
  - `mail`
  - `password`
  - `status`
  - `line_name`
- 管理画面
  - ログイン後遷移先の設定
  - ユーザ管理（追加・編集・削除）
  - 保護URLパターン管理（追加・有効化/無効化・削除）
- 認証ミドルウェア
  - 保護URLに一致し、未ログインの場合 `login.php` へリダイレクト

## 共通ファイル
- `header.php`
- `footer.php`
- `css/style.css`
- `js/app.js`

※ CSS/JSは `time()` で毎回読み込みます（キャッシュ無効化）。

## 起動
```bash
php -S 0.0.0.0:8000 -t .
```

## 初期管理者
- mail: `admin@example.com`
- password: `password123`

## DBファイル
- `data/app.sqlite`
- 初回アクセス時に自動生成されます。
