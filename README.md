# TORASAKE WordPress テーマ

torasake.com のWordPressフルカスタムテーマ。静的HTMLからの移行にあたり
**既存URLを1本も変えない**ことを最優先要件としている（方式A）。

## 構成

```
themes/torasake/          テーマ本体
  functions.php           読み込みハブ
  inc/
    setup.php             テーマサポート・クエリ調整
    post-types.php        CPT（brewery / event / past_event / blog_post / sponsor）
    taxonomies.php        prefecture / event_edition・初期ターム
    rewrite.php           ★方式A のURL定義（唯一の定義元）
    acf.php               ACFローカルJSON同期・オプションページ
    template-tags.php     テンプレート用ヘルパー
    enqueue.php           テンプレート別アセット読み込み
    seo.php               meta / OGP / JSON-LD
  acf-json/               ACFフィールド定義（Git管理）
  assets/css|js/          参照HTMLから逐語移植したCSS・JS
  template-parts/         ナビ・フッター・トップの各セクション
  front-page.php          トップ（直近イベントのLP）
  archive-brewery.php     酒蔵一覧
  single-brewery.php      酒蔵詳細
  home.php                お知らせ一覧
  single.php              お知らせ詳細
  single-event.php        イベント告知
  single-past_event.php   過去開催レポート
  archive-event.php       イベント一覧 ※参照HTML無し・確定パターンの組み合わせ
  archive-blog_post.php   ブログ一覧 ※参照HTML無し・確定パターンの組み合わせ
  single-blog_post.php    ブログ記事 / 蔵元インタビュー
  page-sponsors.php       協賛ページ
  index.php               フォールバック

docs/
  design_handoff_wordpress/   ハンドオフ資料一式（参照HTML・URL移行マップ）
  移行手順.md                 URL対応表・公開前チェックリスト

tests/                        WordPress を起動しない静的検証ハーネス
```

## 見る

### 1. プレビュー（WordPress不要・いちばん手軽）

```sh
php tests/preview.php
```

`tests/.preview/index.html` をブラウザで開くと、全テンプレートのプレビューを
目次から行き来できる。テンプレートを実際に実行してCSSを埋め込んだ静的HTML。

見た目とマークアップの確認用。**JSは動かない**（検索・フィルタ・カウントダウン・
ライトボックス等）。中身はダミーデータ。

### 2. ローカルのWordPressで動かす（クリックして回れる）

JSの挙動・管理画面・URLの疎通まで見たいとき。

**Local（いちばん簡単。GUIだけで完結）**

1. https://localwp.com/ から Local をインストール
2. 「Create a new site」でサイトを作る（PHP 8.1以上を選ぶ）
3. 作ったサイトの `app/public/wp-content/themes/` を開く
4. このリポジトリの `themes/torasake` をそこにコピー（またはシンボリックリンク）
5. 管理画面 → プラグイン → **ACF PRO** を入れて有効化（必須）
6. 外観 → テーマ → 「TORASAKE」を有効化
   → 有効化時に固定ページ・初期ターム・リライトルールが自動で入る
7. 設定 → パーマリンク を開いて「変更を保存」（リライトの再書き出し）
8. イベントを1件作り `event_status` を「開催予定」にする → トップがその回の告知になる

**wp-env（Docker と Node がある人向け）**

```sh
npm -g i @wordpress/env
cd /path/to/this/repo
cat > .wp-env.json <<'JSON'
{ "themes": [ "./themes/torasake" ], "phpVersion": "8.2" }
JSON
wp-env start          # http://localhost:8888  (admin / password)
```

ACF PRO は有償プラグインなので管理画面から手動で入れる。

**中身が空だと寂しいので**、`docs/design_handoff_wordpress/uploads/` の画像を
メディアにアップロードし、`tests/preview.php` のダミーデータを参考に
酒蔵・イベントを数件登録すると本番に近い状態になる。

### 3. ステージングに上げる

公開前のチェックリストは [`docs/移行手順.md`](docs/移行手順.md) の5章。
全URLに200が返るかの確認コマンドも載せてある。

## 検証

```sh
sh tests/run-all.sh
```

WordPress 本体を起動せずに全テンプレートを実行し、CLAUDE.md の各ルール
（価格非表示・方式A・CSS逐語移植・テンプレート階層）を機械的に検査する。
実機での確認の代わりにはならない。詳しくは [`tests/README.md`](tests/README.md)。

## 必要環境

- WordPress 6.4+
- PHP 8.1+
- **ACF PRO**（repeater / relationship / gallery / options page を使う）

## セットアップ

1. `themes/torasake/` を `wp-content/themes/` に配置
2. ACF PRO を有効化
3. テーマを有効化 → `after_switch_theme` で初期セットアップが走る
   （パーマリンク構造・固定ページ・初期ターム・リライトルール。すべて冪等）
4. 「設定 > 表示設定」で投稿ページが「お知らせ」になっているか確認
5. `event` を1件作り `event_status` を「開催予定」にする → トップがその回の告知になる
6. 「協賛について」固定ページのテンプレートが「協賛ページ」になっているか確認

詳しくは [`docs/移行手順.md`](docs/移行手順.md)。

## 開発上の約束

[`CLAUDE.md`](CLAUDE.md) に集約してある。要点だけ:

- **酒蔵一覧・詳細に価格を出さない。** ACFにも価格フィールドを作らない
- **`.html` 付きURLが正規URL。** 変えない・301しない
- **参照HTMLがデザインの確定版。** CSSの値を「整理」しない
- 蔵数などの数値はハードコードせずACFから算出する
