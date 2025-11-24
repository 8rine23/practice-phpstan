# 大規模コードベースにおけるPHPStan導入ガイド

## 目次

1. [概要](#概要)
2. [主要な懸念点と対処法](#主要な懸念点と対処法)
   - [1. パフォーマンスとメモリ消費](#1-パフォーマンスとメモリ消費)
   - [2. 既存エラーの大量発生](#2-既存エラーの大量発生)
   - [3. レガシーコードとの互換性](#3-レガシーコードとの互換性)
   - [4. チーム導入とワークフロー](#4-チーム導入とワークフロー)
   - [5. CI/CD統合](#5-cicd統合)
   - [6. 段階的レベルアップ戦略](#6-段階的レベルアップ戦略)
3. [推奨導入ロードマップ](#推奨導入ロードマップ)
4. [まとめ](#まとめ)
5. [参考資料](#参考資料)

---

## 概要

大規模なPHPコードベースにPHPStanを導入する際、多くの組織が直面する課題があります。本レポートでは、実際のプロジェクトで発生する主要な懸念点を特定し、それぞれに対する複数の対処法を提示します。

### 大規模コードベースの定義

本レポートにおける「大規模コードベース」とは、以下のような特徴を持つプロジェクトを指します：

- **コード行数**: 10万行以上
- **ファイル数**: 数百〜数千ファイル
- **開発期間**: 5年以上の運用履歴
- **開発者数**: 複数チーム、10名以上の開発者
- **レガシーコード**: PHP 5.x〜7.x時代に書かれたコード含む

---

## 主要な懸念点と対処法

### 1. パフォーマンスとメモリ消費

#### 懸念点の詳細

大規模なコードベースでPHPStanを実行すると、以下の問題が発生する可能性があります：

- **メモリ不足**: 数GB単位のメモリ消費により、ローカル環境やCI環境でのメモリ枯渇
- **実行時間の長期化**: 解析に数分〜数十分かかり、開発者の生産性低下
- **低スペックマシンでの実行不可**: コントリビューターの環境によっては実行できない

**実例**: WordPressのようなプロジェクトでは、全体を解析すると通常のCI環境でメモリ不足が発生することが報告されています。

#### 対処法の選択肢

##### 選択肢 1: メモリ制限の緩和

**概要**: PHPのメモリ制限を引き上げる

**実装方法**:
```bash
# コマンドラインで実行
php -d memory_limit=-1 ./vendor/bin/phpstan analyse src

# または phpstan.neon で設定
# phpstan.neon
parameters:
    memory_limit: 2G
```

**メリット**:
- 実装が簡単
- 即座に効果が出る

**デメリット**:
- 根本的な解決にならない
- CI環境のリソース消費が増加
- コスト増につながる可能性

**推奨度**: ⭐⭐ (一時的な対処として)

##### 選択肢 2: Bleeding Edge機能の有効化

**概要**: PHPStanの最新最適化機能を利用

**実装方法**:
```yaml
# phpstan.neon
includes:
    - phar://phpstan.phar/conf/bleedingEdge.neon

parameters:
    level: 6
    paths:
        - src
```

**メリット**:
- 巨大なコードベースでの解析が大幅に高速化
- メモリ消費の削減
- 追加コストなし

**デメリット**:
- 最新機能のため、予期しない動作の可能性
- 安定版より頻繁な更新が必要

**推奨度**: ⭐⭐⭐⭐ (積極的に推奨)

##### 選択肢 3: 並列処理の最適化

**概要**: CPUコア数に応じた並列実行

**実装方法**:
```bash
# デフォルトでは自動的にCPUコア数分並列実行される
vendor/bin/phpstan analyse

# 手動で並列数を指定
vendor/bin/phpstan analyse --parallel 4
```

**メリット**:
- 実行時間の大幅短縮
- PHPStan 2.0ではデフォルトで有効

**デメリット**:
- メモリ消費は増加する可能性
- シングルコアマシンでは効果なし

**推奨度**: ⭐⭐⭐⭐⭐ (必須)

##### 選択肢 4: 解析対象の分割

**概要**: コードベースを複数のグループに分けて段階的に解析

**実装方法**:
```yaml
# phpstan-core.neon (コア機能のみ)
parameters:
    level: 6
    paths:
        - src/Core
        - src/Models

# phpstan-features.neon (追加機能)
parameters:
    level: 5
    paths:
        - src/Features
```

**メリット**:
- 重要な部分を優先的に厳格化
- 段階的な導入が可能
- CI/CDでの並列実行に最適

**デメリット**:
- 管理する設定ファイルが増加
- 全体像の把握が困難

**推奨度**: ⭐⭐⭐⭐ (大規模プロジェクトに推奨)

##### 選択肢 5: 自動生成ファイルの除外

**概要**: 解析不要なファイルを明示的に除外

**実装方法**:
```yaml
parameters:
    level: 6
    paths:
        - src
    excludePaths:
        - src/*/Generated/*
        - src/Cache/*
        - vendor/*
        - storage/*
```

**メリット**:
- 解析時間とメモリの大幅削減
- ノイズの除去

**デメリット**:
- 除外パターンの保守が必要

**推奨度**: ⭐⭐⭐⭐⭐ (必須)

##### 選択肢 6: PHPStan 2.0へのアップグレード

**概要**: 最新版の大幅な最適化を活用

**主な改善点**:
- **メモリ消費70%削減**
- 大規模プロジェクトでのCI/CDメモリ問題解決
- 解析速度の向上

**実装方法**:
```bash
composer require --dev phpstan/phpstan:^2.0
```

**メリット**:
- 劇的なパフォーマンス改善
- 長期的なメンテナンス性向上

**デメリット**:
- 既存の設定やベースラインの見直しが必要
- 新しいエラーが検出される可能性

**推奨度**: ⭐⭐⭐⭐⭐ (強く推奨)

---

### 2. 既存エラーの大量発生

#### 懸念点の詳細

レガシーコードを含む大規模プロジェクトでは、初回実行時に数千〜数万のエラーが報告されることがあります：

- **10,000件以上のエラー**: 手動修正が現実的でない
- **開発者のモチベーション低下**: 膨大なエラー数に圧倒される
- **プロジェクト停滞**: 全て修正するまで先に進めない

**実例**: 8年運用されたレガシーコードベースでは、Level 0でも数千のエラーが報告されることがあります。

#### 対処法の選択肢

##### 選択肢 1: ベースライン機能の活用

**概要**: 既存エラーを記録し、新規エラーのみ検出

**実装方法**:
```bash
# ベースラインファイルを生成
vendor/bin/phpstan analyse --level 6 \
    --configuration phpstan.neon \
    src/ tests/ \
    --generate-baseline

# phpstan.neon に追加
includes:
    - phpstan-baseline.neon
```

**メリット**:
- 既存エラーを無視し、新規コードに厳格なルール適用
- 段階的な改善が可能
- 開発フローを止めない

**デメリット**:
- ベースラインファイルが巨大になる可能性
- 既存の問題が放置される

**推奨度**: ⭐⭐⭐⭐⭐ (最も推奨)

**適用シーン**:
- エラー数: 数十〜数百件
- プロジェクトフェーズ: 運用中で即座の修正が困難
- チーム状況: 新規機能開発と並行して品質向上

##### 選択肢 2: 低レベルからの段階的導入

**概要**: Level 0から開始し、徐々にレベルを上げる

**実装方法**:
```yaml
# フェーズ1: Level 0
parameters:
    level: 0
    paths:
        - src

# フェーズ2: Level 0のエラーゼロ達成後、Level 1へ
# フェーズ3: Level 1のエラーゼロ達成後、Level 2へ
# ... 最終目標: Level 6
```

**メリット**:
- 着実な品質向上
- 開発者が段階的に学習できる

**デメリット**:
- 同じファイルを複数回編集することになる
- 完了までに時間がかかる
- 低レベルの期間中、準最適なコードが書かれる

**推奨度**: ⭐⭐⭐ (小〜中規模プロジェクト向け)

##### 選択肢 3: ファイル単位での修正

**概要**: 触るファイルから順次修正

**実装戦略**:
```markdown
1. ベースラインを生成 (全エラーを一旦無視)
2. 開発中にファイルを編集する際、そのファイルのPHPStanエラーを修正
3. スプリントごとに数ファイルずつ修正
4. 徐々にベースラインのエラー数を減らす
```

**メリット**:
- 作業の分散化
- 通常開発フローに組み込める
- 心理的負担が少ない

**デメリット**:
- 完了までの期間が不明確
- 触られないファイルはいつまでも修正されない

**推奨度**: ⭐⭐⭐⭐ (継続的改善に最適)

##### 選択肢 4: クリティカルパス優先戦略

**概要**: ビジネスクリティカルな部分から修正

**実装方法**:
```yaml
# 高優先度エリア: 厳格なルール
# phpstan-critical.neon
parameters:
    level: 6
    paths:
        - src/Payment
        - src/Authentication
        - src/Security

# 低優先度エリア: 緩いルールとベースライン
# phpstan-others.neon
parameters:
    level: 2
    paths:
        - src/Admin
        - src/Reports
includes:
    - phpstan-baseline-others.neon
```

**メリット**:
- リスクの高い領域を優先的に保護
- ROIが高い
- ビジネス価値との整合性

**デメリット**:
- エリア分けの判断が必要
- 管理が複雑化

**推奨度**: ⭐⭐⭐⭐⭐ (エンタープライズ推奨)

##### 選択肢 5: 新規コードのみ厳格化

**概要**: 既存コードはベースラインで無視、新規コードに厳格なルール適用

**実装方法**:
```bash
# 1. 現在のLevel 6でベースライン生成
vendor/bin/phpstan analyse --level 6 --generate-baseline

# 2. strictルールを追加
composer require --dev phpstan/phpstan-strict-rules

# phpstan.neon
includes:
    - phpstan-baseline.neon
    - vendor/phpstan/phpstan-strict-rules/rules.neon

parameters:
    level: 6
```

**メリット**:
- 技術的負債の増加を防止
- 新規コードの品質保証
- 既存コードへの影響なし

**デメリット**:
- 既存コードの品質は改善されない
- コードベース内に品質のばらつき

**推奨度**: ⭐⭐⭐⭐ (ハイブリッド戦略)

##### 選択肢 6: ベースライン解析ツールの活用

**概要**: ベースラインを分析し、優先度を判断

**利用ツール**:
- **phpstan-baseline-analysis**: エラーの種類と頻度を可視化
- **phpstan-baseline-filter**: 特定のエラーパターンを抽出
- **phpstan-baseline-guard**: ベースラインへの新規エラー追加を防止

**実装例**:
```bash
# ベースライン解析
composer require --dev staabm/phpstan-baseline-analysis

# 最も頻出するエラーパターンを特定
vendor/bin/phpstan-baseline-analyze phpstan-baseline.neon

# 優先度の高いエラーから修正開始
```

**メリット**:
- データドリブンな意思決定
- 効率的なエラー修正順序
- 進捗の可視化

**デメリット**:
- 追加ツールの学習コスト
- 初期セットアップの手間

**推奨度**: ⭐⭐⭐⭐ (大規模ベースライン向け)

---

### 3. レガシーコードとの互換性

#### 懸念点の詳細

古いPHPコードには以下の問題があります：

- **グローバル変数の多用**: `$GLOBALS`や`include`を使った変数共有
- **型情報の欠如**: PHPDocやタイプヒントがない
- **動的な変数アクセス**: `$$variable`や`__get()`の多用
- **フレームワーク非依存**: 独自のアーキテクチャ

**実例**: 10,000件以上のエラーが発生し、手動修正が非現実的なケース。

#### 対処法の選択肢

##### 選択肢 1: ignoreErrorsを活用

**概要**: 特定パターンのエラーを無視

**実装方法**:
```yaml
parameters:
    level: 6
    paths:
        - src
    ignoreErrors:
        # レガシーなグローバル変数アクセスを無視
        - '#Access to an undefined variable \$GLOBALS#'

        # 特定ディレクトリの特定エラーを無視
        -
            message: '#Variable \$\w+ might not be defined#'
            path: src/Legacy/*

        # 特定クラスのマジックメソッドを無視
        -
            message: '#Call to an undefined method .+::__get\(\)#'
            path: src/Models/LegacyModel.php
```

**メリット**:
- レガシーコードを触らずに導入可能
- 細かな制御が可能

**デメリット**:
- 本来検出すべきエラーも無視する可能性
- 正規表現の保守コスト

**推奨度**: ⭐⭐⭐ (限定的な使用を推奨)

##### 選択肢 2: 段階的なPHPDocの追加

**概要**: 型情報を徐々に追加

**実装方法**:
```php
// Before: 型情報なし
function calculate($a, $b) {
    return $a + $b;
}

// After: PHPDocで型情報追加
/**
 * @param int|float $a
 * @param int|float $b
 * @return int|float
 */
function calculate($a, $b) {
    return $a + $b;
}
```

**メリット**:
- コードの理解が向上
- PHPStanの解析精度向上
- リファクタリングの基盤

**デメリット**:
- 作業量が多い
- 既存の動作に影響する可能性

**推奨度**: ⭐⭐⭐⭐ (長期的には必須)

##### 選択肢 3: レガシーコードの隔離

**概要**: レガシー部分を別設定で管理

**実装方法**:
```yaml
# phpstan-modern.neon (新しいコード)
parameters:
    level: 6
    paths:
        - src/Modern

# phpstan-legacy.neon (古いコード)
parameters:
    level: 2
    paths:
        - src/Legacy
    ignoreErrors:
        - '#.*#'  # 必要に応じて広範囲に無視
```

**メリット**:
- 新旧コードを区別して管理
- 段階的な移行が可能

**デメリット**:
- 境界の定義が難しい
- 2つの設定の保守

**推奨度**: ⭐⭐⭐⭐ (移行期に有効)

##### 選択肢 4: 自動型推論の活用

**概要**: PHPStanの型推論機能を最大限活用

**実装方法**:
```yaml
parameters:
    level: 6
    inferPrivatePropertyTypeFromConstructor: true
    checkMissingIterableValueType: false  # 段階的に有効化
    checkGenericClassInNonGenericObjectType: false
```

**メリット**:
- PHPDocなしでも一定の型チェック
- 段階的な厳格化

**デメリット**:
- 推論の限界がある
- 明示的な型情報には劣る

**推奨度**: ⭐⭐⭐⭐ (初期段階で有効)

##### 選択肢 5: リファクタリングツールの併用

**概要**: Rectorなどの自動修正ツールを活用

**実装方法**:
```bash
# Rectorのインストール
composer require --dev rector/rector

# PHPDocの自動追加
vendor/bin/rector process src --set type-declaration

# PHPStanで検証
vendor/bin/phpstan analyse
```

**メリット**:
- 大規模な自動修正が可能
- 人的ミスの削減

**デメリット**:
- 自動修正の検証が必要
- 予期しない変更のリスク

**推奨度**: ⭐⭐⭐⭐ (大規模リファクタリング時)

---

### 4. チーム導入とワークフロー

#### 懸念点の詳細

チーム全体での導入には以下の課題があります：

- **学習曲線**: PHPStanのエラーメッセージ理解に時間がかかる
- **抵抗感**: 追加作業と感じる開発者の反発
- **レビュー負荷**: PRレビュー時の新たなチェックポイント
- **優先度の不一致**: ビジネス機能開発 vs 品質改善

#### 対処法の選択肢

##### 選択肢 1: 段階的なチーム教育

**概要**: 勉強会とペアプログラミングで知識共有

**実装計画**:
```markdown
**Week 1-2**: PHPStan基礎セミナー
- PHPStanとは何か
- エラーメッセージの読み方
- 基本的な修正方法

**Week 3-4**: ハンズオン
- サンプルコードで修正体験
- 実際のプロジェクトで小規模導入

**Week 5-**: 継続的サポート
- Slackチャンネルでのサポート
- 週次の知見共有会
```

**メリット**:
- スキルの底上げ
- チーム内のサポート体制構築

**デメリット**:
- 教育時間の確保が必要
- 短期的な生産性低下

**推奨度**: ⭐⭐⭐⭐⭐ (必須)

##### 選択肢 2: チャンピオン制度

**概要**: 各チームにPHPStanエキスパートを配置

**実装方法**:
```markdown
1. 早期アダプターを「チャンピオン」に任命
2. チャンピオンが自チーム内でサポート
3. 定期的なチャンピオンミーティングで知見共有
4. チャンピオンがベストプラクティスをドキュメント化
```

**メリット**:
- 分散型サポート体制
- 各チームに適した導入方法
- モチベーション向上

**デメリット**:
- チャンピオンの負荷増
- チーム間の導入スピード差

**推奨度**: ⭐⭐⭐⭐ (中〜大規模チーム)

##### 選択肢 3: 段階的なエラーレベル導入

**概要**: チームの習熟度に応じてレベルを上げる

**実装計画**:
```yaml
# Month 1-2: Level 0 (基礎的なエラーのみ)
parameters:
    level: 0

# Month 3-4: Level 2 (型チェック追加)
parameters:
    level: 2

# Month 5-6: Level 4
parameters:
    level: 4

# Month 7+: Level 6 (目標レベル)
parameters:
    level: 6
```

**メリット**:
- 無理のないペース
- 各段階で学習と適応が可能

**デメリット**:
- 完了までに時間がかかる
- モチベーション維持が課題

**推奨度**: ⭐⭐⭐⭐ (チーム規模が大きい場合)

##### 選択肢 4: スプリント計画への組み込み

**概要**: 各スプリントでPHPStanエラー修正タスクを割り当て

**実装方法**:
```markdown
**スプリント計画テンプレート**:
- 新規機能開発: 70%
- バグ修正: 20%
- PHPStanエラー修正: 10%

**具体的なタスク例**:
- US-001: ユーザー登録機能 (8pt)
- BUG-042: ログイン不具合修正 (3pt)
- TECH-123: UserController.phpのPHPStanエラー修正 (2pt)
```

**メリット**:
- 継続的な品質改善
- 見える化された進捗

**デメリット**:
- 機能開発速度の若干の低下
- 優先度調整の複雑化

**推奨度**: ⭐⭐⭐⭐⭐ (アジャイル開発)

##### 選択肢 5: プレコミットフック導入

**概要**: コミット前に自動チェック

**実装方法**:
```bash
# .git/hooks/pre-commit
#!/bin/bash

# 変更されたPHPファイルのみチェック
files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$')

if [ -n "$files" ]; then
    vendor/bin/phpstan analyse $files --level 6

    if [ $? -ne 0 ]; then
        echo "PHPStan errors found. Commit aborted."
        exit 1
    fi
fi
```

**メリット**:
- 自動化された品質ゲート
- 早期のエラー検出

**デメリット**:
- コミットプロセスが遅くなる
- 開発者の不満の可能性

**推奨度**: ⭐⭐⭐ (チーム文化による)

---

### 5. CI/CD統合

#### 懸念点の詳細

CI/CDパイプラインでの課題：

- **ビルド時間の増加**: PHPStan実行で数分追加
- **パイプライン失敗の増加**: 新しいエラーでビルドが赤くなる
- **メモリ不足**: CI環境のリソース制約
- **複数ブランチの管理**: ブランチごとのベースライン管理

#### 対処法の選択肢

##### 選択肢 1: 並列パイプライン実行

**概要**: PHPStanを他のタスクと並列実行

**実装方法 (GitHub Actions)**:
```yaml
name: CI

on: [push, pull_request]

jobs:
  phpstan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: php-actions/phpstan@v3
        with:
          level: 6
          memory_limit: 1G

  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run tests
        run: vendor/bin/phpunit

  code-style:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Check code style
        run: vendor/bin/php-cs-fixer fix --dry-run
```

**メリット**:
- ビルド時間の短縮
- 独立した失敗原因の特定

**デメリット**:
- CI並列実行数の制限に注意
- 設定が複雑化

**推奨度**: ⭐⭐⭐⭐⭐ (必須)

##### 選択肢 2: 差分解析の実装

**概要**: 変更されたファイルのみ解析

**実装方法**:
```bash
#!/bin/bash
# ci-phpstan.sh

# マージベースとの差分を取得
BASE_BRANCH=${BASE_BRANCH:-main}
CHANGED_FILES=$(git diff --name-only origin/$BASE_BRANCH...HEAD | grep '\.php$')

if [ -z "$CHANGED_FILES" ]; then
    echo "No PHP files changed."
    exit 0
fi

# 変更されたファイルのみ解析
vendor/bin/phpstan analyse $CHANGED_FILES --level 6 --no-progress
```

**メリット**:
- 実行時間の劇的短縮
- PRレビューに最適

**デメリット**:
- 全体の整合性チェックは別途必要
- 依存関係の変更を見逃す可能性

**推奨度**: ⭐⭐⭐⭐ (PR用パイプライン)

##### 選択肢 3: 結果キャッシュの活用

**概要**: PHPStanの解析結果をキャッシュ

**実装方法 (GitHub Actions)**:
```yaml
- name: Cache PHPStan results
  uses: actions/cache@v3
  with:
    path: |
      vendor
      .phpstan-cache
    key: ${{ runner.os }}-phpstan-${{ hashFiles('composer.lock') }}

- name: Run PHPStan
  run: |
    mkdir -p .phpstan-cache
    vendor/bin/phpstan analyse --configuration phpstan.neon
```

**PHPStan設定**:
```yaml
parameters:
    tmpDir: .phpstan-cache
```

**メリット**:
- 2回目以降の実行が高速化
- CI時間の削減

**デメリット**:
- キャッシュ管理の複雑さ
- 初回実行は遅い

**推奨度**: ⭐⭐⭐⭐⭐ (必須)

##### 選択肢 4: ベースライン変更の検出

**概要**: ベースラインへの新規エラー追加を防止

**実装方法**:
```bash
# CI script
#!/bin/bash

# 現在のベースラインをバックアップ
cp phpstan-baseline.neon phpstan-baseline.neon.backup

# PHPStanを実行 (ベースラインなし)
vendor/bin/phpstan analyse --level 6 --error-format=json > current-errors.json

# ベースラインと比較
if ! diff -q phpstan-baseline.neon phpstan-baseline.neon.backup; then
    echo "ERROR: Baseline has changed. New errors should be fixed, not added to baseline."
    exit 1
fi
```

**または専用ツール**:
```bash
composer require --dev leovie/phpstan-baseline-guard

# CI で実行
vendor/bin/phpstan-baseline-guard
```

**メリット**:
- 技術的負債の増加を防止
- 品質基準の維持

**デメリット**:
- 厳格すぎると開発が止まる可能性

**推奨度**: ⭐⭐⭐⭐ (品質重視プロジェクト)

##### 選択肢 5: 段階的なCI導入

**概要**: 最初は警告のみ、徐々に必須化

**実装フェーズ**:
```yaml
# Phase 1: 実行するが失敗しても続行
- name: Run PHPStan (informational)
  run: vendor/bin/phpstan analyse || true

# Phase 2: 警告を出すが失敗させない
- name: Run PHPStan (warning)
  run: |
    vendor/bin/phpstan analyse || echo "::warning::PHPStan found errors"

# Phase 3: 完全に必須化
- name: Run PHPStan (required)
  run: vendor/bin/phpstan analyse
```

**メリット**:
- チームへの心理的圧力を軽減
- 段階的な適応

**デメリット**:
- Phase 1-2では強制力がない
- 移行タイミングの判断が難しい

**推奨度**: ⭐⭐⭐⭐ (大規模チーム向け)

##### 選択肢 6: 環境別の設定

**概要**: ローカル、CI、本番で異なる設定

**実装方法**:
```yaml
# phpstan-local.neon (開発環境: 緩い設定)
parameters:
    level: 4
    paths:
        - src

# phpstan-ci.neon (CI環境: 厳格)
includes:
    - phpstan-baseline.neon

parameters:
    level: 6
    paths:
        - src
        - tests

# phpstan-production.neon (デプロイ前: 最も厳格)
parameters:
    level: max
    paths:
        - src
```

**CI設定**:
```yaml
- name: Run PHPStan (CI)
  run: vendor/bin/phpstan analyse --configuration phpstan-ci.neon
```

**メリット**:
- 環境に応じた最適化
- ローカルでの快適な開発

**デメリット**:
- 環境差異による混乱の可能性
- 設定ファイルの増加

**推奨度**: ⭐⭐⭐ (高度な使い方)

---

### 6. 段階的レベルアップ戦略

#### 懸念点の詳細

PHPStanのレベルを上げる際の課題：

- **同じファイルを何度も編集**: Level 0→1→2と上げるたびに修正
- **モチベーション低下**: 終わりが見えない修正作業
- **優先度の判断**: どのレベルまで上げるべきか
- **バージョンアップの影響**: PHPStan更新で新しいエラー検出

#### 対処法の選択肢

##### 選択肢 1: ベースライン併用の一気アップ

**概要**: 目標レベルで一度にベースライン生成

**実装方法**:
```bash
# いきなりLevel 6でベースライン生成
vendor/bin/phpstan analyse --level 6 --generate-baseline
```

**その後の戦略**:
```markdown
1. Level 6でベースライン生成 (既存エラーを全て無視)
2. 新規コードはLevel 6を遵守
3. 既存エラーは機会があるたびに修正
4. ベースラインのエラー数を徐々に削減
```

**メリット**:
- 同じファイルを何度も編集しない
- 最初から高い品質基準

**デメリット**:
- 初期のベースラインファイルが巨大
- チームの学習曲線が急

**推奨度**: ⭐⭐⭐⭐⭐ (最も効率的)

##### 選択肢 2: 2段階アプローチ

**概要**: Level 0→Level 6の2段階のみ

**実装計画**:
```markdown
**Phase 1** (1-2ヶ月):
- Level 0でエラーゼロを達成
- 基本的な型チェックの習慣化

**Phase 2** (3-6ヶ月):
- Level 6へ一気にアップ
- ベースライン活用で既存エラー管理
```

**メリット**:
- 中間レベルをスキップして効率化
- 学習曲線の緩和

**デメリット**:
- Level 0→6のギャップが大きい

**推奨度**: ⭐⭐⭐⭐ (バランス型)

##### 選択肢 3: ディレクトリ別レベル戦略

**概要**: 重要度に応じてディレクトリごとに異なるレベル

**実装方法**:
```yaml
# phpstan.neon
parameters:
    level: 2  # デフォルトレベル
    paths:
        - src

# 重要なコア機能は高レベル
# phpstan-core.neon
parameters:
    level: 6
    paths:
        - src/Core
        - src/Security
        - src/Payment

# テストコードは中レベル
# phpstan-tests.neon
parameters:
    level: 4
    paths:
        - tests
```

**メリット**:
- リスクベースの優先順位付け
- 効率的なリソース配分

**デメリット**:
- 複数の設定ファイル管理
- 一貫性の欠如

**推奨度**: ⭐⭐⭐⭐ (エンタープライズ向け)

##### 選択肢 4: 時限目標設定

**概要**: 明確な期限とマイルストーンを設定

**実装計画例**:
```markdown
**Q1 2025**:
- Level 0達成
- ベースライン: 0件

**Q2 2025**:
- Level 3達成
- コア機能をLevel 6へ

**Q3 2025**:
- 全体をLevel 6へ
- ベースライン: 500件以下

**Q4 2025**:
- ベースラインゼロを目指す
```

**メリット**:
- 明確な目標と進捗管理
- チームのモチベーション維持

**デメリット**:
- 期限プレッシャー
- 品質より期限達成が優先されるリスク

**推奨度**: ⭐⭐⭐ (プロジェクト管理重視)

##### 選択肢 5: 自動アップグレード戦略

**概要**: CI/CDで自動的にレベルアップを試行

**実装方法**:
```bash
#!/bin/bash
# auto-level-up.sh

CURRENT_LEVEL=$(grep -oP 'level: \K\d+' phpstan.neon)
NEXT_LEVEL=$((CURRENT_LEVEL + 1))

# 次のレベルで実行してみる
vendor/bin/phpstan analyse --level $NEXT_LEVEL

if [ $? -eq 0 ]; then
    # エラーがなければレベルアップ
    sed -i "s/level: $CURRENT_LEVEL/level: $NEXT_LEVEL/" phpstan.neon
    git commit -am "chore: PHPStan level up to $NEXT_LEVEL"
    echo "✅ Successfully upgraded to level $NEXT_LEVEL"
else
    echo "⚠️  Level $NEXT_LEVEL has errors. Keep working on current level."
fi
```

**メリット**:
- 自動化された進捗
- 手動作業の削減

**デメリット**:
- 自動コミットのリスク
- 複雑な設定

**推奨度**: ⭐⭐ (実験的)

---

## 推奨導入ロードマップ

大規模コードベースへの段階的導入計画の例：

### Phase 1: 準備期間 (1-2週間)

```markdown
**目標**: 環境構築と初期評価

**アクション**:
1. PHPStan 2.0のインストール
   - `composer require --dev phpstan/phpstan:^2.0`

2. 初期実行とエラー数の把握
   - Level 0で実行: エラー数を記録
   - Level 6で実行: 最終目標のエラー数を把握

3. チーム説明会
   - PHPStanの価値提案
   - 導入ロードマップの共有

**成功指標**:
- 全開発者がPHPStanの目的を理解
- エラー数のベースライン記録完了
```

### Phase 2: 基礎導入 (1ヶ月)

```markdown
**目標**: Level 6でベースライン生成、CI/CD統合

**アクション**:
1. ベースライン生成
   - `vendor/bin/phpstan analyse --level 6 --generate-baseline`

2. CI/CD統合
   - GitHub Actions / GitLab CI でPHPStan実行
   - 並列実行と結果キャッシュの設定

3. プレコミットフック (オプション)
   - 変更ファイルのみチェック

**成功指標**:
- CI/CDでPHPStanが自動実行される
- 新規コードでPHPStanエラーが出ない
- ベースラインファイルが増加しない
```

### Phase 3: 継続的改善 (3-6ヶ月)

```markdown
**目標**: ベースラインのエラー数削減

**アクション**:
1. スプリント計画への組み込み
   - 各スプリントで10-20件のエラー修正をタスク化

2. ファイル編集時の修正ルール
   - 既存ファイルを編集する際、PHPStanエラーも修正

3. 月次レビュー
   - ベースラインエラー数の推移確認
   - ボトルネック分析

**成功指標**:
- 月あたり50-100件のエラー削減
- 新規追加エラー: 0件
- チームの習熟度向上
```

### Phase 4: 最適化 (6ヶ月以降)

```markdown
**目標**: ベースラインゼロ、strictルール導入

**アクション**:
1. 残存エラーの集中修正
   - 特定期間をリファクタリングに充てる

2. strictルールの導入
   - `phpstan/phpstan-strict-rules`追加
   - より厳格な型チェック

3. ベストプラクティスのドキュメント化
   - 社内ガイドライン作成

**成功指標**:
- ベースラインエラー: 0件
- Level max達成
- 新規開発者のオンボーディング効率化
```

---

## まとめ

### 重要ポイント

1. **ベースラインは必須**: 大規模コードベースでは、既存エラーを一度ベースラインに記録し、新規コードのみ厳格化
2. **段階的導入**: 一度にLevel maxを目指すのではなく、チームの成熟度に合わせた段階的導入
3. **CI/CD統合**: 自動化によって品質を持続的に担保
4. **チーム教育**: ツール導入だけでなく、チーム全体のスキルアップが鍵
5. **パフォーマンス最適化**: PHPStan 2.0、Bleeding Edge、並列実行、キャッシュの活用

### 推奨される最小構成

どの大規模プロジェクトでも最低限実施すべき対策：

```yaml
✅ PHPStan 2.0以上を使用
✅ Level 6でベースライン生成
✅ CI/CDへの統合 (並列実行・キャッシュ活用)
✅ 新規コードへの厳格なルール適用
✅ ベースラインへの新規エラー追加禁止
```

### 成功事例から学ぶ教訓

- **WordPress**: 巨大なレガシーコードベースでも、ベースライン戦略で段階的導入成功
- **エンタープライズ企業**: Level 6をゴールとし、クリティカルパスから優先的に適用
- **8年運用プロジェクト**: Level 0から開始し、1年かけてLevel 6達成

### 避けるべき失敗パターン

❌ **いきなりLevel maxを目指す**: チームが圧倒され、プロジェクト停滞
❌ **ベースラインなしで全エラー修正**: 現実的でなく、モチベーション低下
❌ **CI統合なしで運用**: 手動実行では継続性が保てない
❌ **チーム教育の不足**: ツールだけ導入してもエラー修正スキルがない

---

## 参考資料

### 公式ドキュメント

- [PHPStan公式サイト](https://phpstan.org/)
- [The Baseline | PHPStan](https://phpstan.org/user-guide/baseline)
- [Command Line Usage | PHPStan](https://phpstan.org/user-guide/command-line-usage)
- [PHPStan's Baseline Feature Lets You Hold New Code to a Higher Standard](https://phpstan.org/blog/phpstans-baseline-feature-lets-you-hold-new-code-to-a-higher-standard)

### パフォーマンス最適化

- [From Minutes to Seconds: Massive Performance Gains in PHPStan](https://phpstan.org/blog/from-minutes-to-seconds-massive-performance-gains-in-phpstan)
- [Debugging PHPStan Performance: Identify Slow Files](https://phpstan.org/blog/debugging-performance-identify-slow-files)
- [Static Analyser PHPStan Releases Version 2.0 - InfoQ](https://www.infoq.com/news/2024/12/phpstan-v2-release/)
- [Large memory consumption · Issue #4072 · phpstan/phpstan](https://github.com/phpstan/phpstan/issues/4072)

### ベースライン戦略

- [Fixing a Legacy PHP Codebase: PHPStan's Baseline Generator - Shawn Hooper](https://www.shawnhooper.ca/2022/08/23/phpstan-baseline-generator/)
- [Adding PHPStan to a legacy project | BackEndTea](https://backendtea.com/post/phpstan-legacy-project/)
- [Analyze your PHPStan baseline | My developer experience](https://staabm.github.io/2022/07/04/phpstan-baseline-analysis.html)
- [PHPStan baseline filter | My developer experience](https://staabm.github.io/2023/10/30/phpstan-filter-baseline.html)

### レガシーコード対応

- [Seeking Guidance on PHPStan Configuration for Legacy Code Migration - Stack Overflow](https://stackoverflow.com/questions/77926307/seeking-guidance-on-phpstan-configuration-for-legacy-code-migration-variable)
- [Level 0: Implementing PHP Static Analysis on an 8-year-old codebase](https://marvinquezon.com/posts/level-0-implementing-php-static-analysis-on-an-8-year-old-codebase)
- [Scrub Up! Cleaning Your PHP Application With PHPStan](https://developer.vonage.com/en/blog/scrub-up-cleaning-your-php-application-with-phpstan)

### チーム導入とCI/CD

- [Proposal: PHPStan in the WordPress core development workflow – Make WordPress Core](https://make.wordpress.org/core/2025/07/11/proposal-phpstan-in-the-wordpress-core-development-workflow/)
- [GitHub - php-actions/phpstan: PHP Static Analysis in Github Actions](https://github.com/php-actions/phpstan)
- [How to Create A GitLab CI Pipeline to Statically Analyse PHP Projects](https://www.howtogeek.com/devops/how-to-create-a-gitlab-ci-pipeline-to-statically-analyse-php-projects/)
- [Use PHPStan to Ensure Code Quality in Your Project - K5D::Blog](https://kienngd.github.io/use-phpstan-to-ensure-code-quality-in-your-project/)

### 総合ガイド

- [What's the correct way to add PHPStan to an existing codebase? | Oliver Davies](https://www.oliverdavies.uk/daily/2025/03/16/what-s-the-correct-way-to-add-phpstan-to-an-existing-codebase)
- [Supercharge Your Laravel Codebase with PHPStan: A Guide to Static Analysis](https://sandeeppant.medium.com/supercharge-your-laravel-codebase-with-phpstan-a-guide-to-static-analysis-90b7021ae2e6)
- [PHPStan: Elevate Your PHP Code Quality with Static Analysis - DEV Community](https://dev.to/alphaolomi/phpstan-elevate-your-php-code-quality-with-static-analysis-519f)

---

**ドキュメント作成日**: 2025-11-24
**対象PHPStanバージョン**: 2.0以上
**想定読者**: 大規模PHPプロジェクトの技術リード、アーキテクト、開発マネージャー
