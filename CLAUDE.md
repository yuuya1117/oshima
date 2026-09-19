# TORASAKE WordPress テーマ

torasake.com（静的HTML）をWordPressフルカスタムテーマへ移行したもの。

## 絶対に守るルール

### 1. 酒蔵ページに価格を出さない
- **酒蔵一覧（`/breweries/`）と酒蔵詳細（`/breweries/{slug}.html`）には一切価格を表示しない。**
- `brewery` の ACF に価格系フィールドを追加しない（`acf-json/group_brewery.json`）。
- `meta_table`（基本情報の可変dl）にも価格に類する行を入れない。
- JSON-LD の `makesOffer` も銘柄名とカテゴリのみ。`price` / `priceCurrency` を入れない。
- 価格を出してよいのは **トップのチケットセクション** と **`event` 配下** だけ。

### 2. 既存URLを1本も変えない（方式A）
`docs/design_handoff_wordpress/URL移行マップ.md` が唯一の正。
- `.html` 付きURLが正規URL。`/news/{slug}.html` を `/news/{slug}/` に変えない。
- `redirect_canonical` は `post` / `brewery` の単体ページで無効化してある（`inc/rewrite.php`）。外さない。
- 投稿スラッグは現行ファイル名と完全一致させる。日本語スラッグ2本
  （`2026-06-27-当日券` / `2026-05-21-開催決定`）は管理画面に **生の日本語** で入力する。
  パーセントエンコードして貼らない。
- Peatixリンクの `?utm_source=` を落とさない（流入計測が切れる）。
- `/uploads/` は実ディレクトリのまま残す。メディアライブラリに移して
  `/wp-content/uploads/YYYY/MM/` にしない（画像検索流入とOGPキャッシュが切れる）。

### 3. デザインは参照HTMLが確定版
`docs/design_handoff_wordpress/reference/` の HTML / CSS が確定版。色・余白・タイポは確定値。
- `assets/css/*.css` は参照の `<style>` を **逐語移植** したもの。値を「整理」しない。
- 参照ごとに `:root` の値が違う（`--border` が `.22` と `.25`、`--text` が `#16202e` と `#1a1a2e` 等）。
  意図的にそのままにしてある。統一しない。
- WordPress 実装で必要になった追加分は各CSSの末尾に
  「WordPress 実装で足した分」の見出しを付けて分離してある。確定値の側を書き換えない。
- なお `design_handoff_wordpress/README.md` の「トップページ＝紺基調 `--bg:#050c18`」は
  v5 の記述で、確定版の v6（`reference/top-page.html`）は水色基調 `--bg:#eaf6fc`。参照HTMLを優先する。
- **`archive-blog_post.php` だけは参照HTMLが存在しない**（ハンドオフはブログ記事単体のみ）。
  暫定デザインなので、確定版が出たら差し替える。
- 参照HTMLの `.tpl-flag`（「テンプレート — 内容を差し替えてください」の付箋）は
  差し替え目印なのでテンプレートから出力しない。CSS定義だけ逐語移植で残してある。

### 4. 数値をハードコードしない
蔵数・来場者数・開催日などは `event` / `past_event` の ACF から算出する。
テンプレートに直書きしない。

## 構成
```
themes/torasake/     テーマ本体
  inc/               functions.php から読む実処理
  acf-json/          ACFフィールド定義（ローカルJSON同期）
  assets/css|js/     テンプレート別アセット
  template-parts/    ナビ・フッター・トップの各セクション
docs/                ハンドオフ資料一式と移行手順
```

## 開発メモ
- PHP 8.1+ / WordPress 6.4+ / ACF PRO 必須（repeater・relationship・options page を使う）。
- CPT の `rewrite` は全て `false`。URL は `inc/rewrite.php` が唯一の定義元。
- テーマ切り替え時に `after_switch_theme` で初期セットアップが走る（冪等）。
