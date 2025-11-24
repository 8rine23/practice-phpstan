# PHPStan Practice - Laravel Framework

LaravelフレームワークをベースにしたWebアプリケーションに対してPHPStanを走らせる例です。

本格的な**Nginx + PHP-FPM**構成を採用し、プロダクション環境に近い形でLaravelアプリケーションを実行できます。

## 環境要件

- Docker
- Docker Compose

## アーキテクチャ

このプロジェクトは以下の構成で動作します：

- **Nginx**: Webサーバー（ポート8080）
  - 静的ファイルの配信
  - PHP-FPMへのリバースプロキシ
- **PHP-FPM**: PHPアプリケーションサーバー（ポート9000）
  - Laravel 11.x
  - PHP 8.3
  - Composer 2.7.9
- **SQLite**: データベース（ファイルベース）

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

## プロジェクト構成

```
framework/
├── Dockerfile              # PHP-FPM + Laravel環境の定義
├── compose.yaml            # Docker Compose設定（Nginx + PHP-FPM）
├── nginx.conf              # Nginx設定ファイル
├── .env.example            # 環境変数のサンプル
├── .gitignore             # Git無視ファイル
├── .dockerignore          # Docker無視ファイル
├── phpstan.neon           # PHPStan設定ファイル
├── phpstan-baseline.neon  # PHPStanベースラインファイル（既存エラーを無視）
├── setup.sh               # 自動セットアップスクリプト
├── README.md              # このファイル
└── laravel/               # Laravelプロジェクトルート
    ├── app/
    │   ├── Http/
    │   │   └── Controllers/
    │   │       ├── TodoController.php      # TODOのCRUD処理
    │   │       └── DashboardController.php # ダッシュボード
    │   ├── Models/
    │   │   └── Todo.php                    # TODOモデル
    │   ├── Services/
    │   │   ├── TodoService.php             # TODOビジネスロジック
    │   │   ├── StatisticsService.php       # 統計処理
    │   │   └── ExportService.php           # エクスポート処理
    │   └── Repositories/
    │       └── TodoRepository.php          # TODOデータアクセス層
    ├── database/
    │   ├── migrations/
    │   │   └── xxxx_create_todos_table.php
    │   └── seeders/
    │       └── TodoSeeder.php
    ├── routes/
    │   └── web.php
    ├── resources/
    │   └── views/
    │       ├── todos/
    │       │   ├── index.blade.php
    │       │   ├── create.blade.php
    │       │   ├── edit.blade.php
    │       │   └── stats.blade.php
    │       └── dashboard.blade.php
    ├── composer.json
    └── artisan
```

## PHPStan設定

[phpstan.neon](phpstan.neon) で以下の設定を行っています:

- **解析レベル**: 0 (0-9の範囲で設定可能)
- **解析対象**: `laravel/app`, `laravel/routes`
- **除外パス**: Laravelのシステムファイル（Kernel.php、Providersなど）
- **拡張機能**: Larastan（Laravel専用PHPStan拡張）

### Larastanについて

[Larastan](https://github.com/larastan/larastan)は、Laravel専用のPHPStan拡張機能です。

**主な機能:**
- **Eloquent ORM（モデル）の型推論** - モデルの動的プロパティやリレーションを理解
- **Facadeの型チェック** - Laravel Facadeの静的呼び出しを正しく解析
- **コレクションメソッドの型サポート** - Collectionクラスのメソッドチェーンを追跡
- **ルーティングの検証** - ルート定義の妥当性チェック
- **ビューの存在チェック** - `view()`関数で指定されたビューファイルの存在確認

**使用方法:**
Larastanは`phpstan`コマンドを使用しますが、`phpstan.neon`に`larastan/larastan/extension.neon`をインクルードすることで、Laravel特有の機能が自動的に有効になります。

```yaml
includes:
    - ./laravel/vendor/larastan/larastan/extension.neon
```

### レベルについて

- Level 0: 基本的なチェック（未定義変数など）
- Level 5: より厳密な型チェック
- Level 9: 最も厳格なチェック

## PHPStanベースライン機能

PHPStanのベースライン機能を使用すると、既存のエラーを無視しつつ、新しく追加されるエラーのみを検出できます。これにより、既存の大規模なコードベースに段階的にPHPStanを導入することが可能になります。

### ベースラインファイルの作成

```bash
# 現在のエラーをベースラインとして保存
docker compose run --rm app sh -c "cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M --generate-baseline"

# ベースラインファイルを framework/ ディレクトリに移動
mv laravel/phpstan-baseline.neon ./phpstan-baseline.neon
```

これにより `phpstan-baseline.neon` ファイルが生成され、現在検出されているすべてのエラーが記録されます。

### ベースラインの動作確認

このプロジェクトでは、ベースライン機能のデモンストレーションとして以下のワークフローを実装しています:

1. **ベースライン作成前**: 18個のエラーが検出される（TodoController.php、DashboardController.php、TodoService.php、StatisticsService.php、ExportService.phpの既存エラー）

2. **ベースライン作成後**: `phpstan-baseline.neon` に18個のエラーが記録され、PHPStan実行時にはエラーが0個と表示される

3. **新しいエラー追加後**: 新しいエラーを追加すると、ベースラインに記録されていない新しいエラーのみが検出される

### ベースラインに含まれるエラー（18個）

既存のコードベースに含まれる以下のエラーはベースラインで無視されます:

- **TodoController.php** (7個): 未定義変数 `$sortOrder`、`$deletedCount`、未定義メソッド `validateTitle()`、`archiveOldTodos()`、未定義プロパティ `$totalCompleted`、`$restoredItems`（2箇所）
- **DashboardController.php** (2個): 未定義メソッド `calculateProgress()`、未定義クラス `ReportService`
- **TodoService.php** (5個): 未定義変数 `$userId`、未定義関数 `calculatePercentage()`、`notifyUser()`、未定義静的プロパティ `Todo::$exportFormat`、未定義クラス `ReportGenerator`
- **StatisticsService.php** (3個): 未定義プロパティ `$cache`、未定義メソッド `clearCache()`、未定義変数 `$weekData`
- **ExportService.php** (1個): 未定義プロパティ `$format`

### ベースラインの削除

```bash
# ベースラインファイルを削除
rm phpstan-baseline.neon
```

ベースラインを削除すると、すべてのエラー（18個）が再び検出されるようになります。

## サンプルコードの説明

このプロジェクトには、PHPStanレベル0で検出できるエラーのデモンストレーションとして、現実世界でよく見られるバグパターンを含むTODOリスト管理Webアプリケーションが含まれています。

**重要**: すべてのエラーは**未使用のメソッド内**に配置されており、アプリケーションの動作には影響しません。これにより、実際に動作するWebアプリケーションでPHPStanのベースライン機能を体験できます。

### アプリケーション構成

#### [TodoController.php](laravel/app/Http/Controllers/TodoController.php)

TODO項目のCRUD操作を処理するコントローラー。主要なメソッド（`index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, `stats()`）は正常に動作します。

以下のエラーは**ルートに登録されていない未使用メソッド**に含まれます:

- `sortTodosByCustomOrder()`: 未定義変数 `$sortOrder` の使用（**レベル0で検出**）
- `validateTodoTitle()`: 未定義メソッド `validateTitle()` の呼び出し（**レベル0で検出**）
- `getTotalCompleted()`: 未定義プロパティ `$totalCompleted` へのアクセス（**レベル0で検出**）
- `bulkDelete()`: 未定義変数 `$deletedCount` の使用（**レベル0で検出**）
- `archive()`: 未定義メソッド `archiveOldTodos()` の呼び出し（**レベル0で検出**）
- `restore()`: 未定義プロパティ `$restoredItems` へのアクセス（**レベル0で検出**）

#### [DashboardController.php](laravel/app/Http/Controllers/DashboardController.php)

ダッシュボード表示とエクスポート機能を処理するコントローラー。主要なメソッド（`index()`, `export()`）は正常に動作します。

以下のエラーは**ルートに登録されていない未使用メソッド**に含まれます:

- `progress()`: 未定義メソッド `calculateProgress()` の呼び出し（**レベル0で検出**）
- `generateReport()`: 未定義クラス `ReportService` のインスタンス化（**レベル0で検出**）

#### [TodoService.php](laravel/app/Services/TodoService.php)

TODOのビジネスロジック層。主要なメソッド（`createTodo()`, `updateTodo()`, `deleteTodo()`, `getAllTodos()`, `getStatistics()`など）は正常に動作します。

以下のエラーは**使用されていないpublicメソッド**に含まれます:

- `addUserIdToData()`: 未定義変数 `$userId` の使用（**レベル0で検出**）
- `calculateCompletionPercentage()`: 未定義関数 `calculatePercentage()` の呼び出し（**レベル0で検出**）
- `getExportFormat()`: 未定義静的プロパティ `Todo::$exportFormat` へのアクセス（**レベル0で検出**）
- `notifyUserAboutTodo()`: 未定義関数 `notifyUser()` の呼び出し（**レベル0で検出**）
- `createReport()`: 未定義クラス `ReportGenerator` のインスタンス化（**レベル0で検出**）

#### [StatisticsService.php](laravel/app/Services/StatisticsService.php)

統計情報処理サービス。主要な`generate()`メソッドは正常に動作します。

以下のエラーは**使用されていないpublicメソッド**に含まれます:

- `getCachedData()`: 未定義プロパティ `$cache` へのアクセス（**レベル0で検出**）
- `refresh()`: 未定義メソッド `clearCache()` の呼び出し（**レベル0で検出**）
- `getWeeklyStats()`: 未定義変数 `$weekData` の使用（**レベル0で検出**）

#### [ExportService.php](laravel/app/Services/ExportService.php)

TODO項目のエクスポート機能を提供するサービス。主要な`export()`メソッドは正常に動作します。

以下のエラーは**使用されていないpublicメソッド**に含まれます:

- `getFormat()`: 未定義プロパティ `$format` へのアクセス（**レベル0で検出**）

### PHPStanレベル0で検出できるエラーパターン

レベル0では以下のような基本的だが重大なエラーを検出できます:

1. **未定義プロパティへのアクセス** - 存在しないプロパティの参照
2. **未定義メソッドの呼び出し** - 存在しないメソッドの呼び出し
3. **未定義変数の使用** - 初期化されていない変数の使用
4. **未定義クラスの使用** - 存在しないクラスのインスタンス化
5. **未定義関数の呼び出し** - 存在しない関数の呼び出し
6. **未定義静的プロパティへのアクセス** - 存在しない静的プロパティの参照

これらは全てタイポや実装忘れなど、現実世界のコードで頻繁に発生するエラーです。

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

**注意**: このWebアプリケーションには意図的に18個のPHPStanエラーが含まれていますが、すべてのエラーは未使用のメソッド内に配置されているため、**アプリケーションは完全に正常動作します**。これにより、実際に動作するWebアプリケーションでPHPStanのベースライン機能を安全に体験できます。

### コンテナログの確認

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

## コンテナ内でのシェル実行

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
