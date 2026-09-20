# tests/

WordPress 本体を起動せずにテーマを検証する静的ハーネス。

`stubs.php` が WP のグローバル関数を最小限スタブし、各テンプレートを実際に
`include` して走らせる。**挙動の正しさを保証するものではなく**、
「致命的エラーが出ないこと」と「CLAUDE.md の各ルールが守られていること」を見る。
実機での確認の代わりにはならない。

## 実行

```sh
sh tests/run-all.sh
```

PHP 8.1+ と python3 が必要。node があれば JS の構文チェックも走る。

## 中身

| ファイル | 見ているもの |
|---|---|
| `stubs.php` | WP 関数のスタブ。`WP_Query::parse_query()` の判定ロジックを模している |
| `check_hierarchy.php` | `has_archive` とテンプレート階層（下記の注意点） |
| `run.php` | トップ・酒蔵・お知らせ・404 の描画 |
| `run2.php` | イベント・過去開催・ブログ・協賛の描画 |
| `run3.php` | イベント一覧の描画 |
| `run_fallback.php` | 開催予定が無いときの「次回準備中」 |
| `check_urls.php` | 方式A（CLAUDE.md #2）: リライト規則・`.html` パーマリンク・日本語スラッグ・301 |
| `check_price.php` | 酒蔵ページに価格が出ないこと（CLAUDE.md #1）。価格データを混入させても出ないか |
| `check_jsonld.php` | JSON-LD の `makesOffer` に価格が入らないこと |
| `check_css.py` | CSS が参照HTMLの逐語移植のままか（CLAUDE.md #3） |
| `check_acf.py` | ACF JSON の妥当性・キー重複・brewery の価格系フィールド |
| `check_new.php` / `check_new2.php` | 各テンプレートの要素が出ているか |
| `preview.php` | ブラウザで開けるプレビューHTMLを書き出す（下記） |

## `has_archive` の注意点

CPT の `rewrite` は全て `false`（URL の定義元は `inc/rewrite.php` だけ）だが、
**一覧を持つ CPT は `has_archive` を真値にしておく必要がある。**

`WP_Query::parse_query()` は `! empty( $post_type_obj->has_archive )` のときだけ
`is_post_type_archive` を立てる。`false` のままだと `/breweries/` などで
`is_archive` も立たず、最後のフォールバックで `is_home` になり
`home.php`（お知らせ一覧）が出てしまう。

`rewrite` が `false` なので `WP_Post_Type::add_rewrite_rules()` は何のルールも
足さない（追加処理は全て `false !== $this->rewrite` の内側）。つまり
`has_archive` を真にしても URL の定義元は `inc/rewrite.php` のまま。

`check_hierarchy.php` がこれを見張っている。

## プレビュー

```sh
php tests/preview.php
```

`tests/.preview/` にブラウザで開けるHTMLを書き出す（gitignore 済み）。
テンプレートを実際に実行し、CSSをインライン化して1枚にまとめたもの。
画像は `docs/design_handoff_wordpress/uploads/` の実ファイルを指す。

全テンプレート（13ページ）を書き出し、`index.html` から行き来できる。

**JSは読み込んでいない**ので、酒蔵一覧の検索・フィルタ、お知らせのカテゴリチップ、
トップのカウントダウンとスクロール演出、過去開催のライトボックスは動かない。
そこまで含めて確認したいときは本物のWordPressで動かすこと（README.md の「見る」）。
