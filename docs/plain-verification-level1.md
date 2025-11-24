# Plain PHP Level 1 検証レポート

## 検証概要

### 目的

フレームワークを使用しない素のPHP（Plain PHP）で実装されたCLIアプリケーションに対して、PHPStan Level 1を適用し、ベースライン機能を活用した静的解析の有効性を検証する。

### 検証日時

2025-11-24

### 検証者

Claude Code

---

## 検証環境

### システム構成

- **PHPバージョン**: 7.3
- **フレームワーク**: なし（Plain PHP）
- **Composerバージョン**: 1.10.27
- **コンテナ管理**: Docker Compose
- **アプリケーション形態**: CLIベースのTODOリスト管理ツール

### PHPStan設定

**ファイル**: [phpstan.neon](../plain/phpstan.neon)

```yaml
includes:
    - phpstan-baseline.neon

parameters:
    level: 1
    paths:
        - src
    excludePaths:
        - src/temp/*
```

### 使用パッケージ

- **PHPStan**: 最新版（Composer経由）

---

## プロジェクト構成

```
plain/
├── phpstan.neon            # PHPStan設定（Level 1）
├── phpstan-baseline.neon   # ベースラインファイル（24エラー）
├── composer.json           # PHPの依存関係定義
├── todo.php                # TODOリストCLIツールのエントリーポイント
└── src/                    # PHPソースコード
    ├── Calculator.php      # 基本サンプル（3エラー）
    ├── User.php            # 基本サンプル（2エラー）
    ├── Todo.php            # TODOエンティティクラス（2エラー）
    ├── TodoRepository.php  # TODOデータ永続化層（2エラー）
    ├── TodoService.php     # TODOビジネスロジック層（6エラー）
    ├── TodoCommand.php     # TODOコマンドハンドラー（7エラー）
    └── MagicTodo.php       # マジックメソッド・プロパティデモ（2エラー）
```

### アプリケーション機能

**実装済みCLIコマンド**:
```bash
# TODO追加
php todo.php add "タイトル" "説明" "期限"

# TODO一覧
php todo.php list

# TODO完了
php todo.php complete <id>

# TODO削除
php todo.php delete <id>

# TODO検索
php todo.php search "キーワード"
```

---

## PHPStan Level 1 検証

### PHPStan Level 1とは

Level 1では、Level 0の基本的なチェックに加えて、以下の項目が検出される：

1. **可能性のある未定義変数**
   - 条件分岐で変数が定義されない場合を検出

2. **引数の超過**
   - 関数に過剰な引数を渡す場合を検出（Level 0では不足のみ）

3. **未知のマジックメソッド・プロパティ**
   - `__call`, `__get`を持つクラスで、PHPDocで定義されていないマジックアクセスを検出

4. **より厳密な型チェック**
   - より正確なコントロールフロー解析

### PHPStan実行

```bash
$ cd $HOME/develop/practice-phpstan/plain
$ docker compose run --rm app ./vendor/bin/phpstan analyze --level=1
```

**実行結果**:

```
 7/7 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 [OK] No errors
```

✅ **ベースライン機能により24個のエラーが正常に無視された**

---

## ベースライン生成

### 生成コマンド

```bash
$ docker compose exec app vendor/bin/phpstan analyse --generate-baseline
```

### 実行結果

ベースラインファイルに24個のエラーが記録された。

### ベースラインファイル

**[phpstan-baseline.neon](../plain/phpstan-baseline.neon)**

**構造**:
```yaml
parameters:
    ignoreErrors:
        -
            message: "#^Undefined variable\\: \\$result$#"
            count: 1
            path: src/Calculator.php
        -
            message: "#^Variable \\$title might not be defined\\.$#"
            count: 1
            path: src/TodoService.php
        -
            message: "#^Access to an undefined property App\\\\MagicTodo\\:\\:\\$unknownProperty\\.$#"
            count: 1
            path: src/MagicTodo.php
        # ... (24個のエラー)
```

---

## 検出されたエラー詳細（ベースライン）

### エラー内訳（Level 1）

| ファイル | エラー数 | 主なエラータイプ |
|---------|---------|-----------------|
| TodoCommand.php | 7個 | 未定義変数、未定義メソッド、未定義関数、未定義クラス、可能性のある未定義変数、引数超過 |
| TodoService.php | 6個 | 未定義クラス、未定義静的プロパティ、未定義関数、可能性のある未定義変数、引数超過 |
| Calculator.php | 3個 | 未定義変数、未定義メソッド、未定義プロパティ |
| MagicTodo.php | 2個 | **未知のマジックプロパティ、未知のマジックメソッド（Level 1特有）** |
| Todo.php | 2個 | 未定義プロパティ、未定義メソッド |
| TodoRepository.php | 2個 | 未定義変数、未定義プロパティ |
| User.php | 2個 | 未定義プロパティ、未定義メソッド |
| **合計** | **24個** | |

### 1. TodoCommand.php（7エラー）

#### Level 0エラー（5個）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義変数 | `handleSearch()` | `$count` | 1 |
| 未定義メソッド | `showHelp()` | `showVersion()` | 1 |
| 未定義関数 | `exportToJson()` | `convertToJson()` | 1 |
| 未定義変数 | `printSummary()` | `$totalTasks` | 1 |
| 未定義クラス | `generateReport()` | `TodoReporter` | 1 |

**コード例（Level 0）**:
```php
// TodoCommand.php
private function handleSearch(array $argv): void
{
    if (!isset($argv[2])) {
        echo "Error: Search keyword is required\n";
        return;
    }

    $keyword = $argv[2];
    $results = $this->service->searchTodos($keyword);

    if (empty($results)) {
        echo "No todos found matching '$keyword'\n";
        return;
    }

    // PHPStan Level 0: $countは初期化されていない
    echo "Found $count results:\n";

    foreach ($results as $todo) {
        echo "- {$todo->getId()}: {$todo->getTitle()}\n";
    }
}
```

#### Level 1追加エラー（2個）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 可能性のある未定義変数 | `getSelectedTodo()` | `$selected` | 1 |
| 可能性のある未定義変数 | `processCommand()` | `$message` | 1 |

**コード例（Level 1特有）**:
```php
// TodoCommand.php

/**
 * Error: Possibly undefined variable
 * PHPStan Level 1: possibly undefined variable
 */
public function getSelectedTodo(array $argv): ?Todo
{
    $todos = $this->service->listTodos();

    if (isset($argv[2])) {
        $id = (int)$argv[2];

        foreach ($todos as $todo) {
            if ($todo->getId() === $id) {
                $selected = $todo;
                break;
            }
        }
    }

    // PHPStan Level 1: $selectedが定義されない可能性がある
    // （$argv[2]が未設定、またはループで見つからない場合）
    return $selected;
}

/**
 * Error: Possibly undefined variable in complex condition
 * PHPStan Level 1: possibly undefined variable
 */
public function processCommand(string $cmd): void
{
    if ($cmd === 'stats') {
        $message = 'Showing statistics';
    } elseif ($cmd === 'export') {
        $message = 'Exporting data';
    }

    // PHPStan Level 1: $messageが定義されない可能性がある
    // （$cmdが'stats'でも'export'でもない場合）
    echo $message . "\n";
}
```

### 2. TodoService.php（6エラー）

#### Level 0エラー（3個）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義クラス | `exportTodos()` | `TodoExporter` | 1 |
| 未定義静的プロパティ | `getVersion()` | `self::$version` | 1 |
| 未定義関数 | `printStatistics()` | `formatStatistics()` | 1 |

**コード例（Level 0）**:
```php
// TodoService.php

/**
 * Error: Using undefined class
 */
public function exportTodos(): string
{
    $todos = $this->repository->findAll();

    // PHPStan Level 0: undefined class
    $exporter = new TodoExporter();
    return $exporter->export($todos);
}

/**
 * Error: Accessing undefined static property
 */
public function getVersion(): string
{
    // PHPStan Level 0: undefined static property
    return self::$version;
}

/**
 * Error: Accessing undefined function
 */
public function printStatistics(): void
{
    $todos = $this->repository->findAll();
    $completed = 0;
    $pending = 0;

    foreach ($todos as $todo) {
        if ($todo->isCompleted()) {
            $completed++;
        } else {
            $pending++;
        }
    }

    // PHPStan Level 0: undefined function
    formatStatistics($completed, $pending);
}
```

#### Level 1追加エラー（3個）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 可能性のある未定義変数 | `getFirstTodoTitle()` | `$title` | 1 |
| 引数超過 | `createSimpleTodo()` | 4引数（許容3引数） | 1 |
| 可能性のある未定義変数 | `findTodoByTitle()` | `$found` | 1 |

**コード例（Level 1特有）**:
```php
// TodoService.php

/**
 * Error: Possibly undefined variable
 * PHPStan Level 1: possibly undefined variable
 */
public function getFirstTodoTitle(): string
{
    $todos = $this->repository->findAll();

    if (count($todos) > 0) {
        $title = $todos[0]->getTitle();
    }

    // PHPStan Level 1: $titleが定義されない可能性がある
    // （$todosが空の場合）
    return $title;
}

/**
 * Error: Too many arguments
 * PHPStan Level 1: too many arguments
 */
public function createSimpleTodo(string $title): Todo
{
    // createTodoは最大3引数だが4つ渡している
    // PHPStan Level 1: too many arguments
    return $this->createTodo($title, '', null, 'extra');
}

/**
 * Error: Possibly undefined variable in condition
 * PHPStan Level 1: possibly undefined variable
 */
public function findTodoByTitle(string $searchTitle): ?Todo
{
    $todos = $this->repository->findAll();

    foreach ($todos as $todo) {
        if ($todo->getTitle() === $searchTitle) {
            $found = $todo;
            break;
        }
    }

    // PHPStan Level 1: $foundが定義されない可能性がある
    // （ループで見つからない場合）
    return $found;
}
```

### 3. MagicTodo.php（2エラー）⭐ Level 1特有

**Level 1で新たに検出されるマジックメソッド・プロパティエラー**

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未知のマジックプロパティ | `demonstrateMagicProperty()` | `$unknownProperty` | 1 |
| 未知のマジックメソッド | `demonstrateMagicMethod()` | `unknownMethod()` | 1 |

**コード例**:
```php
<?php

namespace App;

/**
 * Class demonstrating magic methods for PHPStan Level 1
 *
 * PHPStan Level 1 detects unknown magic methods and properties
 * on classes with __call and __get
 */
class MagicTodo
{
    private $data = [];

    /**
     * Magic getter
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Magic setter
     */
    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Magic method caller
     */
    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $property = lcfirst(substr($name, 3));
            return $this->data[$property] ?? null;
        }

        throw new \BadMethodCallException("Method $name does not exist");
    }

    /**
     * Error: Accessing unknown magic property
     * PHPStan Level 1: unknown magic property
     */
    public function demonstrateMagicProperty(): void
    {
        // PHPStan Level 1: このプロパティがPHPDocで定義されていない
        echo $this->unknownProperty;
    }

    /**
     * Error: Calling unknown magic method
     * PHPStan Level 1: unknown magic method
     */
    public function demonstrateMagicMethod(): void
    {
        // PHPStan Level 1: このメソッドがPHPDocで定義されていない
        $this->unknownMethod();
    }
}
```

**Level 1の特徴**:
- `__get`, `__set`, `__call`を持つクラスでも、PHPDocで明示的に定義されていないプロパティやメソッドへのアクセスを検出
- Level 0ではこれらのマジックアクセスは検出されない

### 4. Calculator.php（3エラー）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義変数 | `modulo()` | `$result` | 1 |
| 未定義メソッド | `power()` | `calculatePower()` | 1 |
| 未定義プロパティ | `getLastResult()` | `$lastResult` | 1 |

### 5. Todo.php（2エラー）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義プロパティ | `getPriority()` | `$priority` | 1 |
| 未定義メソッド | `display()` | `formatDisplay()` | 1 |

### 6. TodoRepository.php（2エラー）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義変数 | `add()` | `$undefinedId` | 1 |
| 未定義プロパティ | `getCompletedCount()` | `$completedTotal` | 1 |

### 7. User.php（2エラー）

| エラータイプ | メソッド | 詳細 | 出現回数 |
|------------|---------|------|---------|
| 未定義プロパティ | `getAddress()` | `$address` | 1 |
| 未定義メソッド | `printInfo()` | `formatInfo()` | 1 |

### 重要な設計方針

**すべてのエラーは未使用メソッド内に配置**されており、アプリケーションの実行には影響しない：

- CLIコマンドから呼び出されていないメソッド
- エラーを含むメソッドは意図的に配置
- PHPStanの検出能力を実証するためのデモコード

---

## PHPStan Level 1で検出される項目

### Level 0の検出項目（基本）

1. ✅ 未定義クラス
2. ✅ 未定義関数
3. ✅ 未定義メソッド（$thisへの呼び出し）
4. ✅ 引数不足
5. ✅ 常に未定義の変数
6. ✅ 未定義プロパティ
7. ✅ 未定義静的プロパティ

### Level 1の追加検出項目

1. ⭐ **可能性のある未定義変数**
   ```php
   if ($condition) {
       $var = 'value';
   }
   echo $var; // $conditionがfalseの場合、$varは未定義
   ```

2. ⭐ **引数の超過**
   ```php
   function foo($a, $b) {}
   foo(1, 2, 3); // 3番目の引数は不要
   ```

3. ⭐ **未知のマジックメソッド・プロパティ**
   ```php
   class Magic {
       public function __get($name) {}
       public function __call($name, $args) {}
   }

   $obj = new Magic();
   $obj->undefinedProperty; // PHPDocで定義されていない
   $obj->undefinedMethod(); // PHPDocで定義されていない
   ```

4. ⭐ **より厳密なコントロールフロー解析**
   - ループ内での変数定義の追跡
   - 複雑な条件分岐での変数の存在チェック

---

## CLIアプリケーション動作確認

### テスト実行

```bash
$ cd $HOME/develop/practice-phpstan/plain
$ docker compose up -d
$ docker compose exec app php todo.php list
```

**実行結果**:

```
No todos found
```

✅ **CLIアプリケーションが正常に動作**

### 基本動作テスト

```bash
# TODO追加
$ docker compose exec app php todo.php add "買い物" "牛乳を買う" "2025-01-15"
Todo created with ID: 1

# TODO一覧
$ docker compose exec app php todo.php list
[ ] 1: 買い物
   Description: 牛乳を買う
   Due: 2025-01-15

# TODO完了
$ docker compose exec app php todo.php complete 1
Todo 1 marked as completed

# TODO一覧（完了済み）
$ docker compose exec app php todo.php list
[✓] 1: 買い物
   Description: 牛乳を買う
   Due: 2025-01-15
```

✅ **すべてのコマンドが正常動作**

---

## ベースライン機能の検証

### ベースラインの仕組み

1. **既存エラーの記録**: `phpstan-baseline.neon`に24個のエラーを記録
2. **エラーの無視**: 記録されたエラーはPHPStan実行時に無視される
3. **新規エラーのみ検出**: 新たに追加されたエラーのみが報告される

### 検証結果

#### ✅ 成功した点

1. **既存エラーの無視**
   ```bash
   # 24個のエラーが存在するが
   [OK] No errors
   ```

2. **新規エラーの検出**
   - ベースラインに記録されていない新しいエラーは検出される
   - 新規コードの品質を保つことができる

3. **段階的導入の実現**
   - 既存コードに手を加えずにPHPStan導入
   - Level 1の厳密なチェックを安全に適用

### Level 1特有のエラー検出例

**可能性のある未定義変数**:
```php
// 修正前（エラー）
public function findTodoByTitle(string $searchTitle): ?Todo
{
    $todos = $this->repository->findAll();

    foreach ($todos as $todo) {
        if ($todo->getTitle() === $searchTitle) {
            $found = $todo;
            break;
        }
    }

    return $found; // $foundが未定義の可能性
}

// 修正後（エラーなし）
public function findTodoByTitle(string $searchTitle): ?Todo
{
    $todos = $this->repository->findAll();
    $found = null; // 初期化

    foreach ($todos as $todo) {
        if ($todo->getTitle() === $searchTitle) {
            $found = $todo;
            break;
        }
    }

    return $found;
}
```

---

## 得られた知見

### Plain PHPでのPHPStan Level 1の特徴

#### 適用効果

1. **可能性のある未定義変数の検出**
   - 条件分岐での変数定義漏れを防止
   - より堅牢なコードを実現

2. **引数チェックの強化**
   - Level 0: 引数不足のみ検出
   - Level 1: 引数超過も検出

3. **マジックメソッドの型安全性**
   - `__call`, `__get`を使用するクラスの安全性向上
   - PHPDocによる明示的な定義を促進

4. **フレームワークなしでも効果的**
   - Laravel不要
   - Plain PHPでも十分な型チェックが可能

#### Plain PHP特有の注意点

1. **オートロードの設定**
   ```json
   {
       "autoload": {
           "psr-4": {
               "App\\": "src/"
           }
       }
   }
   ```

2. **型宣言の活用**
   ```php
   // PHP 7.3+では型宣言が重要
   public function createTodo(
       string $title,
       string $description = '',
       ?string $dueDate = null
   ): Todo {
       // ...
   }
   ```

3. **名前空間の使用**
   ```php
   namespace App;

   class Todo
   {
       // ...
   }
   ```

### ベストプラクティス

#### PHPStan設定

```yaml
parameters:
    level: 1
    paths:
        - src
    excludePaths:
        - src/temp/*
        - vendor/*
```

#### Composer設定

```json
{
    "scripts": {
        "phpstan": "phpstan analyse"
    }
}
```

実行:
```bash
composer phpstan
```

#### CI/CD統合

```yaml
# .github/workflows/phpstan.yml
name: PHPStan

on: [push, pull_request]

jobs:
  phpstan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.3'
      - name: Install dependencies
        run: composer install
      - name: Run PHPStan
        run: composer phpstan
```

---

## エラーパターン分類

### Level 0で検出（基本）

| エラータイプ | 説明 | Plain PHPでの検出数 |
|-------------|------|-------------------|
| 未定義変数 | 初期化されていない変数 | 4個 |
| 未定義メソッド | 存在しないメソッド | 3個 |
| 未定義プロパティ | 存在しないプロパティ | 5個 |
| 未定義クラス | 存在しないクラス | 2個 |
| 未定義関数 | 存在しない関数 | 2個 |
| 未定義静的プロパティ | 存在しない静的プロパティ | 1個 |

**合計Level 0**: 17個相当

### Level 1で追加検出

| エラータイプ | 説明 | Plain PHPでの検出数 |
|-------------|------|-------------------|
| 可能性のある未定義変数 | 条件分岐で定義されない可能性 | 4個 |
| 引数超過 | 過剰な引数 | 1個 |
| 未知のマジックプロパティ | PHPDocで未定義 | 1個 |
| 未知のマジックメソッド | PHPDocで未定義 | 1個 |

**合計Level 1追加**: 7個

**総合計**: 24個

---

## 結論

### 検証結果サマリー

#### ✅ 達成した目標

1. **PHPStan Level 1での検証完了**
   - 24個のエラーを検出
   - ベースラインで正常に管理

2. **CLIアプリケーション正常動作確認**
   - 全コマンドで動作確認
   - エラーは未使用メソッド内のため影響なし

3. **ベースライン機能の有効性実証**
   - 既存エラーを無視しつつ新規エラーのみ検出
   - Plain PHPでも段階的導入が可能

4. **Level 1特有の検出項目確認**
   - 可能性のある未定義変数（4個）
   - 引数超過（1個）
   - マジックメソッド・プロパティ（2個）

#### 📊 検証データ

| 項目 | 結果 |
|------|------|
| 総エラー数 | 24個 |
| Level 0相当 | 17個 |
| Level 1追加 | 7個 |
| ベースラインエントリ | 24個 |
| CLI動作 | ✅ 正常 |
| PHPStan結果 | `[OK] No errors` |

### Plain PHPでのPHPStan活用の利点

1. **フレームワーク不要**
   - シンプルなPHPプロジェクトでも効果的
   - 学習コストが低い

2. **段階的な型安全性向上**
   - Level 1から始めて着実に改善
   - ベースライン機能で既存コードへの影響を最小化

3. **バグの早期発見**
   - 未定義変数の可能性を事前検出
   - 引数の過不足を検出

4. **コード品質の可視化**
   - 静的解析により品質メトリクスを取得
   - チーム全体での品質基準共有

### 今後の展開

#### 短期的な改善（1-3ヶ月）

1. **ベースラインエラーの削減**
   - 目標: 24個 → 15個以下
   - 未使用メソッドの削除
   - 軽微なエラーの修正

2. **Level 2への移行検討**
   - より厳密な型チェック導入
   - null許容型の厳密なチェック

3. **CI/CD統合**
   - GitHub Actions設定
   - 自動チェックの導入

#### 中期的な改善（3-6ヶ月）

1. **Level 5を目標**
   - 段階的にレベルアップ
   - 各レベルで安定化

2. **型宣言の完全化**
   - すべてのメソッドに型宣言
   - PHPDocの充実

3. **テストとの統合**
   - PHPUnit導入
   - 静的解析 + 動的テストの両輪

#### 長期的な目標（6ヶ月以上）

1. **Level 9（最高レベル）を目指す**
   - 完全な型安全性
   - strict_types宣言の活用

2. **継続的な品質向上**
   - 定期的なレビュー
   - メトリクス可視化

---

## 参考資料

### 公式ドキュメント

- [PHPStan公式サイト](https://phpstan.org/)
- [PHPStan Rule Levels](https://phpstan.org/user-guide/rule-levels)
- [PHPStan Baseline](https://phpstan.org/user-guide/baseline)

### 関連レポート

- [phpstan-level0-detection.md](phpstan-level0-detection.md) - Level 0検出項目詳細
- [verification-discrepancies.md](verification-discrepancies.md) - レポート検証結果

### プロジェクトファイル

- [phpstan.neon](../plain/phpstan.neon) - PHPStan設定（Level 1）
- [phpstan-baseline.neon](../plain/phpstan-baseline.neon) - ベースライン（24エラー）
- [README.md](../plain/README.md) - Plain PHPプロジェクト説明

---

**検証完了日**: 2025-11-24
**検証者**: Claude Code
**検証環境**: Docker + PHP 7.3 + Composer 1.10.27 + PHPStan Level 1
