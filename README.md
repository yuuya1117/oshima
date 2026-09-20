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

## 検証

```sh
sh tests/run-all.sh
```

WordPress 本体を起動せずに全テンプレートを実行し、CLAUDE.md の各ルール
（価格非表示・方式A・CSS逐語移植・テンプレート階層）を機械的に検査する。
実機での確認の代わりにはならない。詳しくは [`tests/README.md`](tests/README.md)。

見た目を確認したいときは `php tests/preview.php`。`tests/.preview/` に
ブラウザで開けるHTMLが出る。

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
