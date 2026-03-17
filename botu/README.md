# Login Starter (PHP + JS + CSS)

有料コンテンツ向けに、指定URLをログイン必須化できるシンプルな実装です。

## 機能
- ログイン / ログアウト
- SQLiteでユーザ管理
  - `mail`（ログインID）
  - `password`
  - `line_name`
  - `status`（`active` / `inactive`）
- 管理画面
  - ログイン後の共通遷移先設定
  - ユーザ管理（追加・編集・削除）
    - 追加時はパスワード自動発行
  - 保護URL管理（追加・有効化/無効化・削除）
- 認証ミドルウェア
  - 保護URLに一致し未ログインなら `login.php` へリダイレクト

## 前提
- 管理画面 (`admin.php`) へのアクセス制限はWebサーバ側で実施する想定
  - 例: Basic認証 / IP制限 / `.htaccess`

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

## DBファイル
- `data/app.sqlite`
- 初回アクセス時に自動生成されます。