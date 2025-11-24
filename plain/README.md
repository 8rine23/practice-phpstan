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

## プロジェクト構成

```
plain/
├── Dockerfile              # PHP環境の定義
├── compose.yaml            # Docker Compose設定
├── .env.example            # 環境変数のサンプル
├── composer.json           # PHPの依存関係定義
├── phpstan.neon            # PHPStan設定ファイル
├── phpstan-baseline.neon   # PHPStanベースラインファイル（既存エラーを無視）
├── todo.php                # TODOリストCLIツールのエントリーポイント
├── src/                    # PHPソースコード
│   ├── Calculator.php      # サンプル: 型エラーを含む計算機クラス
│   ├── User.php            # サンプル: 型エラーを含むユーザークラス
│   ├── Todo.php            # TODOエンティティクラス
│   ├── TodoRepository.php  # TODOデータ永続化層
│   ├── TodoService.php     # TODOビジネスロジック層
│   ├── TodoCommand.php     # TODOコマンドハンドラー
│   └── MagicTodo.php       # マジックメソッド・プロパティデモクラス（レベル1用）
└── README.md               # このファイル
```

## PHPStan設定

[phpstan.neon](phpstan.neon) で以下の設定を行っています:

- **解析レベル**: 1 (0-9の範囲で設定可能)
- **解析対象**: `src` ディレクトリ
- **除外パス**: `src/temp/*`

### レベルについて

- Level 0: 基本的なチェック（未定義変数など）
- Level 1: 可能性のある未定義変数、マジックメソッド・プロパティ、引数の超過
- Level 5: より厳密な型チェック
- Level 9: 最も厳格なチェック

## PHPStanベースライン機能

PHPStanのベースライン機能を使用すると、既存のエラーを無視しつつ、新しく追加されるエラーのみを検出できます。これにより、既存の大規模なコードベースに段階的にPHPStanを導入することが可能になります。

### ベースラインファイルの作成

```bash
# 現在のエラーをベースラインとして保存
docker compose exec app vendor/bin/phpstan analyse --generate-baseline
```

これにより `phpstan-baseline.neon` ファイルが生成され、現在検出されているすべてのエラーが記録されます。

### ベースラインの動作確認

このプロジェクトでは、ベースライン機能のデモンストレーションとして以下のワークフローを実装しています:

1. **ベースライン作成前**: 24個のエラーが検出される（Calculator.php、User.php、Todo.php、TodoRepository.php、TodoService.php、TodoCommand.php、MagicTodo.phpの既存エラー）

2. **ベースライン作成後**: `phpstan-baseline.neon` に24個のエラーが記録され、PHPStan実行時にはエラーが0個と表示される

3. **新しいエラー追加後**: 新しいエラーを追加すると、ベースラインに記録されていない新しいエラーのみが検出される

### ベースラインに含まれるエラー（24個）

既存のコードベースに含まれる以下のエラーはベースラインで無視されます:

**レベル0で検出されるエラー:**
- **Todo.php**: 未定義プロパティ `$priority`、未定義メソッド `formatDisplay()`
- **User.php**: 未定義プロパティ `$address`、未定義メソッド `formatInfo()`
- **Calculator.php**: 未定義変数 `$result`、未定義メソッド `calculatePower()`、未定義プロパティ `$lastResult`
- **TodoRepository.php**: 未定義変数 `$undefinedId`、未定義プロパティ `$completedTotal`
- **TodoService.php**: 未定義クラス `TodoExporter`、未定義静的プロパティ `$version`、未定義関数 `formatStatistics()`
- **TodoCommand.php**: 未定義変数 `$count`、`$totalTasks`、未定義メソッド `showVersion()`、未定義関数 `convertToJson()`、未定義クラス `TodoReporter`

**レベル1で新たに検出されるエラー:**
- **TodoService.php**: 可能性のある未定義変数 `$title`、`$found`、引数超過エラー
- **TodoCommand.php**: 可能性のある未定義変数 `$selected`、`$message`
- **MagicTodo.php**: 未知のマジックプロパティ `$unknownProperty`、未知のマジックメソッド `unknownMethod()`

### ベースラインの削除

```bash
# phpstan.neonからincludesを削除するか、ベースラインファイルを削除
rm phpstan-baseline.neon
```

ベースラインを削除すると、すべてのエラー（24個）が再び検出されるようになります。

## サンプルコードの説明

このプロジェクトには、PHPStanレベル1で検出できるエラーのデモンストレーションとして、現実世界でよく見られるバグパターンを含む複数のコードが含まれています。

### 基本サンプル

#### [Calculator.php](src/Calculator.php)

意図的に以下の型エラーを含んでいます:

- `subtract()`: int型を返すべきなのにstring型を返している（レベル0では検出されない）
- `multiply()`: null許容型の引数を直接使用している（レベル0では検出されない）

**ベースライン後に追加された新しいエラー**（これらはベースラインに含まれず、PHPStanで検出されます）:

- `modulo()`: 未定義変数 `$result` の使用（**レベル0で検出**）
- `power()`: 未定義メソッド `calculatePower()` の呼び出し（**レベル0で検出**）
- `getLastResult()`: 未定義プロパティ `$lastResult` へのアクセス（**レベル0で検出**）

#### [User.php](src/User.php)

意図的に以下のエラーを含んでいます:

- `getEmail()`: null許容型をnon-null型として返している（レベル0では検出されない）
- `getAddress()`: 未定義のプロパティにアクセスしている（**レベル0で検出**）
- `printInfo()`: 存在しないメソッドを呼び出している（**レベル0で検出**）

### TODOリスト管理CLIツール

現実的な小規模CLIアプリケーションの例として、TODOリスト管理ツールを実装しています。

#### [Todo.php](src/Todo.php)

TODOアイテムのエンティティクラス。以下のエラーを含みます:

- `getPriority()`: 未定義プロパティ `$priority` へのアクセス（**レベル0で検出**）
- `display()`: 未定義メソッド `formatDisplay()` の呼び出し（**レベル0で検出**）

#### [TodoRepository.php](src/TodoRepository.php)

データ永続化層。JSON形式でTODOを保存します。以下のエラーを含みます:

- `add()`: 未定義変数 `$undefinedId` の使用（**レベル0で検出**）
- `remove()`: 未定義メソッド `logDeletion()` の呼び出し（**レベル0で検出**）
- `getCompletedCount()`: 未定義プロパティ `$completedTotal` へのアクセス（**レベル0で検出**）

#### [TodoService.php](src/TodoService.php)

ビジネスロジック層。以下のエラーを含みます:

- `exportTodos()`: 存在しないクラス `TodoExporter` のインスタンス化（**レベル0で検出**）
- `getVersion()`: 未定義静的プロパティ `$version` へのアクセス（**レベル0で検出**）
- `printStatistics()`: 未定義関数 `formatStatistics()` の呼び出し（**レベル0で検出**）

#### [TodoCommand.php](src/TodoCommand.php)

CLIコマンドハンドラー。以下のエラーを含みます:

- `handleSearch()`: 未定義変数 `$count` の使用（**レベル0で検出**）
- `showHelp()`: 未定義メソッド `showVersion()` の呼び出し（**レベル0で検出**）
- `displayStatistics()`: 存在しないメソッド `getID()` の呼び出し（**レベル0で検出**）

**ベースライン後に追加された新しいエラー**（これらはベースラインに含まれず、PHPStanで検出されます）:

- `exportToJson()`: 未定義関数 `convertToJson()` の呼び出し（**レベル0で検出**）
- `printSummary()`: 未定義変数 `$totalTasks` の使用（**レベル0で検出**）
- `generateReport()`: 未定義クラス `TodoReporter` のインスタンス化（**レベル0で検出**）

### PHPStanレベル0で検出できるエラーパターン

レベル0では以下のような基本的だが重大なエラーを検出できます:

1. **未定義プロパティへのアクセス** - 存在しないプロパティの参照
2. **未定義メソッドの呼び出し** - 存在しないメソッドの呼び出し
3. **未定義変数の使用** - 初期化されていない変数の使用
4. **未定義クラスの使用** - 存在しないクラスのインスタンス化
5. **未定義関数の呼び出し** - 存在しない関数の呼び出し
6. **未定義静的プロパティへのアクセス** - 存在しない静的プロパティの参照

これらは全てタイポや実装忘れなど、現実世界のコードで頻繁に発生するエラーです。

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

注意: このCLIツールには意図的にエラーが含まれているため、一部の機能は正常に動作しません。これはPHPStanのデモンストレーション目的です。

## コンテナ内でのシェル実行

```bash
docker compose exec app bash
```

## クリーンアップ

```bash
# コンテナの停止と削除
docker compose down

# ボリュームも含めて削除
docker compose down -v
```
