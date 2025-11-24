# PHPStan Practice - Plain PHP

フレームワークを使わない素のPHPに対してPHPStanを走らせる例です。

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
# PHP_VERSION=7.3
# COMPOSER_VERSION=1.10.27
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
- PHP: 7.3
- Composer: 1.10.27

## セットアップ

1. Dockerコンテナのビルドと起動

```bash
docker compose up -d --build
```

2. 依存関係のインストール

```bash
docker compose exec app composer install
```

## PHPStanの実行

```bash
# 基本的な実行
docker compose exec app vendor/bin/phpstan analyse

# より詳細な出力
docker compose exec app vendor/bin/phpstan analyse -v

# 特定のレベルで実行（0-9）
docker compose exec app vendor/bin/phpstan analyse --level 8
```

## ドキュメント

詳細な検証レポートや解析結果については、[docs/plain-verification-level1.md](../docs/plain-verification-level1.md) を参照してください。

このドキュメントには以下の情報が含まれています：
- PHPStan Level 1の詳細な検証結果
- ベースライン機能の検証
- 検出されたエラーの詳細分析
- プロジェクト構成とアーキテクチャの詳細

## TODOリストCLIツールの使い方

```bash
# TODOを追加
docker compose exec app php todo.php add "買い物に行く" "牛乳とパンを買う" "2025-01-15"

# TODOリストを表示
docker compose exec app php todo.php list

# TODOを完了にする
docker compose exec app php todo.php complete 1

# TODOを削除
docker compose exec app php todo.php delete 1

# TODOを検索
docker compose exec app php todo.php search "買い物"
```

**注意**: このCLIツールには意図的に24個のPHPStanエラーが含まれており、一部の機能は正常に動作しません。これはPHPStanのベースライン機能をデモンストレーションする目的です。

## コンテナ操作

### コンテナ内でのシェル実行

```bash
docker compose exec app bash
```

### クリーンアップ

```bash
# コンテナの停止と削除
docker compose down

# ボリュームも含めて削除
docker compose down -v
```
