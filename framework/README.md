# PHPStan Practice - Laravel Framework

LaravelフレームワークをベースにしたWebアプリケーションに対してPHPStanを走らせる例です。

本格的な**Nginx + PHP-FPM**構成を採用し、プロダクション環境に近い形でLaravelアプリケーションを実行できます。

## 環境要件

- Docker
- Docker Compose

## PHPバージョンの変更方法

PHPとComposerのバージョンは環境変数で指定できます。

### 方法1: .envファイルを使用

```bash
# .env.exampleをコピーして.envファイルを作成
cp .env.example .env

# .envファイルを編集してバージョンを指定
# PHP_VERSION=8.3
# COMPOSER_VERSION=2.7.9
```

### 方法2: docker compose実行時に直接指定

```bash
# PHP 8.2を使用する場合
PHP_VERSION=8.2 docker compose build

# PHP 8.1とComposer 2.6を使用する場合
PHP_VERSION=8.1 COMPOSER_VERSION=2.6 docker compose build
```

### デフォルトバージョン

環境変数を指定しない場合、以下のバージョンが使用されます:
- PHP: 8.3
- Composer: 2.7.9
- Laravel: 11.x（最新版）

## セットアップ

### 自動セットアップ（推奨）

提供されているセットアップスクリプトを使用すると、一括でセットアップできます。

```bash
./setup.sh
```

このスクリプトは以下を実行します:
1. Dockerコンテナのビルド
2. Laravelプロジェクトの作成
3. PHPStan関連パッケージのインストール
4. SQLiteデータベースファイルの作成
5. PHPStan用ディレクトリの作成
6. Laravel環境設定
7. データベースマイグレーション

### 手動セットアップ

1. Dockerコンテナのビルド

```bash
docker compose build
```

2. Laravelプロジェクトの作成

```bash
docker compose run --rm app composer create-project laravel/laravel laravel
```

3. PHPStan関連パッケージのインストール

```bash
docker compose run --rm app sh -c "cd laravel && composer require --dev phpstan/phpstan larastan/larastan"
```

4. データベースとディレクトリの準備

```bash
# SQLiteデータベースファイルの作成
docker compose run --rm app touch laravel/database/database.sqlite

# PHPStan用ディレクトリの作成
docker compose run --rm app mkdir -p laravel/storage/phpstan
```

5. Laravel環境設定

```bash
# .envファイルのコピー
docker compose run --rm app sh -c "cd laravel && cp .env.example .env"

# アプリケーションキーの生成
docker compose run --rm app sh -c "cd laravel && php artisan key:generate"
```

6. データベースマイグレーション

```bash
docker compose run --rm app sh -c "cd laravel && php artisan migrate"
```

## Larastan（PHPStan）の実行

このプロジェクトではLarastan（Laravel専用PHPStan拡張）を使用しています。

```bash
# 基本的な実行（設定ファイルは自動検出）
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M"

# より詳細な出力
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M -v"

# 特定のレベルで実行（0-9）
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M --level 8"

# エイリアスとして使いやすくする場合
cd $HOME/develop/practice-phpstan/framework
alias larastan="docker compose run --rm app sh -c 'cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M'"
```

**注意**: LarastanはPHPStanのLaravel専用拡張で、実行時には`phpstan`コマンドを使用しますが、Laravel固有の型推論機能が有効になっています。

## ドキュメント

詳細な検証レポートや解析結果については、[docs/framework-verification-level1.md](../docs/framework-verification-level1.md) を参照してください。

このドキュメントには以下の情報が含まれています：
- PHPStan Level 1の詳細な検証結果
- ベースライン機能の検証とベストプラクティス
- Larastanの効果検証
- 検出されたエラーの詳細分析
- プロジェクト構成とアーキテクチャの詳細

## TODOリストWebアプリケーションの使い方

### Webサーバーの起動

このプロジェクトは**Nginx + PHP-FPM**構成となっており、本格的なWebサーバー環境を提供します。

```bash
# Nginx + PHP-FPMサーバーを起動
docker compose up -d

# ログを確認
docker compose logs -f

# サーバーを停止
docker compose down
```

ブラウザで http://localhost:8080 にアクセスしてください。

**構成:**
- `app` コンテナ: PHP-FPM (ポート9000)でPHPコードを処理
- `nginx` コンテナ: Nginx (ポート8080)でHTTPリクエストを受け付け、PHP-FPMにプロキシ

### 主な機能

- **ダッシュボード** (`/`): TODO統計と最近のTODO一覧
- **TODO一覧** (`/todos`): 全TODO表示
- **TODO作成** (`/todos/create`): 新規TODO作成フォーム
- **TODO編集** (`/todos/{id}/edit`): 既存TODO更新・削除
- **統計表示** (`/stats`): TODO統計の詳細表示
- **エクスポート** (`/export`): TODO一覧のテキストエクスポート

### Artisanコマンド

```bash
# データベースマイグレーション
docker compose run --rm app sh -c "cd laravel && php artisan migrate"

# データベースのリセット
docker compose run --rm app sh -c "cd laravel && php artisan migrate:fresh"

# シーダーの実行
docker compose run --rm app sh -c "cd laravel && php artisan db:seed"

# ルーティング一覧の確認
docker compose run --rm app sh -c "cd laravel && php artisan route:list"

# キャッシュクリア
docker compose run --rm app sh -c "cd laravel && php artisan cache:clear"
```

**注意**: このWebアプリケーションには意図的に19個のPHPStanエラーが含まれていますが、すべてのエラーは未使用のメソッド内に配置されているため、**アプリケーションは完全に正常動作します**。これにより、実際に動作するWebアプリケーションでPHPStanのベースライン機能を安全に体験できます。

## コンテナ操作

### ログ確認

```bash
# 全コンテナのログを表示
docker compose logs

# Nginxのログのみ表示
docker compose logs nginx

# PHP-FPMのログのみ表示
docker compose logs app

# リアルタイムでログを監視
docker compose logs -f
```

### コンテナの状態確認

```bash
# 実行中のコンテナ一覧
docker compose ps

# コンテナの詳細情報
docker compose ps -a
```

### コンテナ内でのシェル実行

```bash
# PHP-FPMコンテナに入る
docker compose run --rm app bash

# Nginxコンテナに入る
docker compose exec nginx sh
```

## クリーンアップ

```bash
# コンテナの停止と削除
docker compose down

# ボリュームも含めて削除
docker compose down -v

# Laravelプロジェクトの削除（完全リセット）
rm -rf laravel/
```

## トラブルシューティング

### パーミッションエラーが発生する場合

```bash
# Laravelディレクトリの所有権を変更
docker compose run --rm app chown -R $(id -u):$(id -g) laravel/
```

### データベース接続エラーが発生する場合

```bash
# database.sqliteファイルが存在することを確認
ls -la laravel/database/database.sqlite

# 存在しない場合は作成
docker compose run --rm app touch laravel/database/database.sqlite
```

### Composerの依存関係エラー

```bash
# vendor/ディレクトリを削除して再インストール
docker compose run --rm app sh -c "cd laravel && rm -rf vendor/ composer.lock"
docker compose run --rm app sh -c "cd laravel && composer install"
```

### Larastanのメモリエラー

```bash
# メモリ制限を増やして実行
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=1G"
```

## クイックリファレンス

### よく使うコマンド

```bash
# Webサーバー起動（Nginx + PHP-FPM）
docker compose up -d

# Webサーバー停止
docker compose down

# ログ確認
docker compose logs -f

# Larastan実行（基本）
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M"

# ベースライン生成
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M --generate-baseline"

# レベル変更して実行
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M --level 5"

# マイグレーション実行
docker compose run --rm app sh -c "cd laravel && php artisan migrate"
```

### Nginx設定

Nginx設定ファイル: [nginx.conf](nginx.conf)

```nginx
server {
    listen 80;
    server_name localhost;
    root /app/laravel/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

- **ドキュメントルート**: `/app/laravel/public`
- **FastCGIパス**: `app:9000` (PHP-FPMコンテナ)
- **リッスンポート**: 80 (ホストの8080にマッピング)
