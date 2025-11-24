#!/bin/bash

# Laravel + PHPStan セットアップスクリプト

set -e

echo "================================"
echo "Laravel プロジェクトセットアップ"
echo "================================"

# Dockerコンテナのビルド
echo ""
echo "[1/7] Dockerコンテナをビルド中..."
docker compose build

# Laravelプロジェクトの作成（既に存在する場合はスキップ）
if [ ! -d "laravel" ]; then
    echo ""
    echo "[2/7] Laravelプロジェクトを作成中..."
    docker compose run --rm app composer create-project laravel/laravel laravel
else
    echo ""
    echo "[2/7] Laravelプロジェクトは既に存在します（スキップ）"
fi

# 依存パッケージのインストール
echo ""
echo "[3/7] Larastan（PHPStan Laravel拡張）をインストール中..."
docker compose run --rm app sh -c "cd laravel && composer require --dev phpstan/phpstan larastan/larastan"

# データベースファイルの作成
echo ""
echo "[4/7] SQLiteデータベースファイルを作成中..."
docker compose run --rm app touch laravel/database/database.sqlite

# PHPStan用ディレクトリの作成
echo ""
echo "[5/7] PHPStan用ディレクトリを作成中..."
docker compose run --rm app mkdir -p laravel/storage/phpstan

# 環境設定ファイルのコピー
echo ""
echo "[6/7] Laravel環境設定ファイルを確認中..."
if [ ! -f "laravel/.env" ]; then
    docker compose run --rm app sh -c "cd laravel && cp .env.example .env"
    docker compose run --rm app sh -c "cd laravel && php artisan key:generate"
fi

# データベースマイグレーション実行
echo ""
echo "[7/7] データベースマイグレーションを実行中..."
docker compose run --rm app sh -c "cd laravel && php artisan migrate"

echo ""
echo "================================"
echo "セットアップ完了！"
echo "================================"
echo ""
echo "次のステップ:"
echo "1. カスタムコード（Todo関連）を追加"
echo "2. Larastan実行: docker compose run --rm app sh -c 'cd laravel && ./vendor/bin/phpstan analyse --configuration=../phpstan.neon --memory-limit=512M'"
echo "3. 開発サーバー起動: docker compose run --rm -p 8000:8000 app sh -c 'cd laravel && php artisan serve --host=0.0.0.0'"
echo ""
