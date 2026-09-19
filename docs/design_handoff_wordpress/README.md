# Handoff: TORASAKE WordPress化（フルカスタムテーマ）

## 概要
torasake.com は現在、静的HTMLの複数ページ群（トップ／酒蔵一覧＋詳細／イベント告知／お知らせ／ブログ／過去開催／協賛）で構成されている。これをWordPress（フルカスタムテーマ、PHPでゼロから実装）に移行し、運営者自身が酒蔵・イベント・ニュース・スポンサー・過去開催情報を管理画面から更新できるようにする。

## 最重要: 既存URLを変えないこと
現行サイトは静的HTMLのまま検索インデックスされている。WordPress化で **URLを1本も失わない**ことを最優先要件とする。CPTのリライトスラッグを現行ディレクトリ名に合わせ、`.html` 付きURLを正規URLとして維持する方式を推奨。詳細な方針・現行URL全リスト・移行チェックリストは同梱の `URL移行マップ.md` を参照。

## デザインファイルについて
`reference/` 配下のHTMLは**デザイン参照として作られたプロトタイプ**であり、そのまま本番コードとして流用するものではない。実装時はこれらのHTML/CSSが示すレイアウト・配色・タイポグラフィ・インタラクションを、WordPressのテンプレート階層＋ACF＋WP_Queryを使って再現すること。JSによるクライアントサイド検索・フィルタ（酒蔵一覧の都道府県/キーワード絞り込みなど）はロジックは踏襲しつつ、データソースをJS内配列からWP_Query/REST APIに置き換える。

## フィデリティ
**Hi-fi。** 色・タイポグラフィ・余白・インタラクションは確定値として扱ってよい（本番相当の配色・フォント）。ただし実データ（酒蔵数・銘柄・写真等）はダミーが混在するため、コンテンツは移行時に差し替える。

## サイト構成とCPT対応

### 1. `brewery`（酒蔵） — 参照: brewery-archive.html / brewery-single.html / brewery-style.css
- 一覧ページ（archive-brewery.php）: 検索ボックス＋都道府県セレクト＋開催回セレクト＋結果件数＋グリッド（220px幅カード、ロゴ画像＋蔵名＋出展銘柄）
- 詳細ページ（single-brewery.php）: ヒーロー（地域名＋蔵名(和英)＋タグライン＋ロゴ）／出展銘柄カード（アクセントカラーはoklchで銘柄ごとに変える、`--accent`変数をACFのカラーピッカーかプリセットから注入）／蔵の物語（リッチテキスト）／基本情報dlテーブル／参加イベント履歴
- ACFフィールド:
  - `region_ja` (text), `founded_year` (text), `tagline` (textarea)
  - `logo` (image), `official_url` (url), `instagram_url` (url)
  - `sake_lineup` (repeater): `number_label`, `bottle_image`, `type_label`, `name_ja`, `name_en`, `description`, `tags` (repeater of text), `accent_hue` (number, oklchのhue角度に使用)
  - `story_heading` (text), `story_body` (wysiwyg)
  - `meta_table` (repeater): `label`, `value` — 所在地/創業/代表銘柄/出展銘柄/特徴/公式サイト/Instagram等、可変長のdlに対応
  - `event_history` (relationship → event / past_event)
- タクソノミー: `prefecture`（都道府県フィルタ用）, `event_edition`（開催回。現在は `torasake-2026`＝2026.06.27／32蔵 と `torasake-mini-2026`＝2026.10.16／10蔵 の2ターム。1蔵が複数回に属する）
- 一覧の開催回セレクトは**直近の開催回をデフォルト選択**にする（現在は TORASAKE mini）。URLパラメータ `?event=` `?pref=` `?q=` でのプリフィルに対応済み
- **重要な制約: 酒蔵一覧・詳細ページには一切価格を表示しない。** ACFフィールドに価格系項目を追加しないこと（CLAUDE.mdルール）。

### 2. `event`（イベント告知） — 参照: event-single-template.html
- ヒーロー（開催回バッジ／タイトル／リード文／日時・会場・チケットCTA）
- セクション: テーマ説明／会場情報／参加酒蔵（breweryリレーション、カードグリッド）／参加方法ステップ／スポンサー（sponsor リレーション、ティア別）／チケット（価格・注意事項・購入CTA）
- ACFフィールド: `event_date`, `event_time_range`, `venue_name`, `venue_address`, `venue_access` (最寄駅等), `theme_body` (wysiwyg), `breweries` (relationship), `howto_steps` (repeater: title/body), `sponsors` (relationship), `ticket_price` (number), `ticket_note` (textarea), `event_status` (select: 開催予定/開催中/終了)
- タクソノミー: `event_edition`

### 3. `news_post`（お知らせ） — 参照: news-archive.html / news-single-example.html
- 一覧: フィルタチップ（すべて/開催報告/お知らせ/イベントガイド）＋カードリスト（日付・カテゴリタグ・タイトル・抜粋）
- 標準のWP投稿＋カテゴリータクソノミーで十分（無理にCPT化しなくてよい）。カテゴリー: `report`（開催報告）, `info`（お知らせ）, `guide`（イベントガイド）
- 抜粋は `excerpt`、本文はブロックエディタでよい

### 4. `blog_post`（蔵元インタビュー等） — 参照: blog-single-template.html
- ヒーロー（カテゴリバッジ／タイトル／日付・県＋蔵名）／動画埋め込み（YouTube iframe）／記事本文（プルクオート対応）／関連酒蔵カード
- ACFフィールド: `youtube_video_id`, `related_brewery` (relationship → brewery), `pull_quote` (text), `pull_quote_who` (text)

### 5. `past_event`（過去開催レポート） — 参照: past-event-single.html
- リード写真／統計カード（来場者数・出展蔵数などをrepeaterで可変に）／レポート本文
- ACFフィールド: `event_date`, `venue_name`, `stats` (repeater: `number`, `unit_suffix`, `label`), `report_body` (wysiwyg), `gallery` (gallery field)

### 6. `sponsor`（協賛企業）
- `logo` (image), `tier` (select: title/supporter), `link_url` (url)
- `event`・トップページから参照して表示

### 7. トップページ — 参照: top-page.html（**v6・TORASAKE mini 告知版**）
トップページは「常設のブランドサイト」ではなく **直近イベントのランディングページ**として設計されている。開催が決まった `event`（status=開催予定）が1件あれば、トップ全体がその回の告知になる。開催予定が無い期間は「次回準備中」フォールバックを出す（v5レイアウトを予備として残してよい）。

- セクション構成: Hero（告知バー＋KV＋情報バー）→ About（コンセプト＋3指標）→ 開催概要 → 参加酒蔵 → 料理/会場 → **チケット** → アーカイブ → News
- **Hero**: 上部に赤帯のティッカー（`.ticker` ＝販売状況の一文。ACF `ticker_text`、空なら非表示）。その下にKV画像を**全幅・トリミングなし**で配置（ACF `key_visual`、16:9想定。文言はKVに焼き込み済みのためHTML側で文字を重ねない）。直下に紺色の `.hero-bar` ＝ DATE / VENUE / SAKE（蔵数）＋チケットCTA。SEOのためKVの `alt` に日時・会場を入れる
- **About**: リード2行＋補足。その下に3枚の指標カード（`.pt` ＝ SCALE / BREWERIES / PAIRING）。数値は `event` のACFから流し込み、ハードコードしない
- **開催概要**: `.ol-grid` ＝ DATE&TIME / VENUE / STYLE / TICKET の4カード（紺地）。ACF `outline`（repeater: `key_en`, `value`, `note`）で可変にする
- **参加酒蔵**: `event` の `breweries` リレーションから5列グリッド（ロゴ＋蔵名＋産地）。個別ページ未作成の蔵はリンクなしのタイル（`.brew-static`）。ロゴ未登録時は蔵名を大きく組む `.wordmark` にフォールバック
- **チケット**: `.tk-grid` ＝ 券種カード3枚。ACF `tickets`（repeater: `label_en`, `name`, `price`, `entry_time`, `limit_label`, `note`, `purchase_url`, `is_featured`）。`is_featured` のカードのみ赤枠＋上部リボン。上部に開催日時までのカウントダウン（`event_datetime` をdata属性で渡しJSで計算）。下に全券共通特典（`ticket_includes` repeater）と注意書き（`ticket_notes`）
  - **価格を表示してよいのはこのチケットセクションと `event` 配下のみ。酒蔵一覧・酒蔵詳細には一切価格を出さない（CLAUDE.mdルール）**
- **スティッキーCTA**: スクロールがヒーロー80%を超えたら画面下部に固定バー（`.sticky-cta`）をスライドイン。`event_status` が終了なら非表示
- **アーカイブ / News**: 直近の `past_event` 1件を紺地フィーチャーカードで、Newsは `news_post` 最新3件
- **全体**: IntersectionObserverによるスクロールフェードイン（`.rv`）、`.section-label` の左右に短い罫線
- 実績の数値（蔵数・来場者数など）はハードコードせず `event` / `past_event` から算出すること

## デザイントークン
サイト内で2つの配色系統を使い分けている。踏襲すること。

**トップページ（重厚・紺基調）**
- `--bg:#050c18` `--bg2:#08111f` `--bg3:#0d1929`
- `--gold:#c8a84b` `--gold-dim:#8a7132`
- `--red:#9b2a1a` `--red-bright:#d03a20`
- `--text:#f0e8d5` `--text-dim:#9a9080`
- フォント: 見出し=Noto Serif JP(700-900) / UI・本文補助=Noto Sans JP

**一覧・記事系ページ（明るい水色/生成り基調）**
- 酒蔵一覧: `--bg:#fbfaf7` `--bg2:#f4f9fd`
- news/past/sponsors/event/blog: `--bg:#e8f5fb` `--bg2:#f4f9fd` `--bg3:#ffffff`
- 共通アクセント: `--gold:#c8a060` `--gold-dim:#a07840` `--red:#e63329` `--navy:#0d1929`
- `--text:#1a1a2e` `--text-dim:#5a6a78` `--border:rgba(90,140,180,0.25)`
- フォント: 本文=Noto Serif JP(400-700) / ラベル・UI=Noto Sans JP(300-700)、letter-spacingを広めに取る和欧混植

## 現在のイベントデータ（2026.09時点）
- **TORASAKE mini** / 2026年10月16日（金）17:00-23:00（L.O. 22:30、先行入場16:30）
- 会場: 虎ノ門ヒルズ ステーションタワー B2F T-MARKET
- 規模: 300〜500名 / 参加10蔵（賀儀屋・大信州・一歩己・高千代・二兎・農口尚彦研究所・屋守・羽根屋・夜明け前・笑四季）
- 券種: 早割2,500円［限定100枚/17:00］・30分先行入場3,000円［限定50枚/16:30］・前売3,000円［17:00］・当日3,500円
- 全券共通特典: イベント専用500円コイン×3枚（1,500円分）＋TORASAKEオリジナルグラス
- KV: `uploads/OOSHIMA-KV-0903_16-9.jpg`（16:9）
- **\u672a\u78ba\u5b9a**: mini\u51fa\u5c55\u9298\u67c4\uff08\u5404\u8535\u30da\u30fc\u30b8\u306f\u524d\u56de6/27\u306e\u9298\u67c4\u306e\u307e\u307e\uff09\n- \u30c1\u30b1\u30c3\u30c8\u8ca9\u58f2: Peatix `https://peatix.com/event/5186533?utm_source=hp`\uff08UTM\u3092\u843d\u3068\u3055\u306a\u3044\u3053\u3068\uff09

## アセット
- \u672c\u30d1\u30c3\u30b1\u30fc\u30b8\u306e `uploads/` \u306b\u3001\u53c2\u7167HTML\u304c\u4f7f\u3046\u753b\u50cf\uff08\u8535\u30ed\u30b435\u70b9\u30fbKV\u30fb\u4f1a\u5834\u5199\u771f\u7b49\uff09\u3092\u540c\u68b1\u6e08\u307f\u3002`reference/*.html` \u306f `../uploads/` \u3092\u53c2\u7167\u3059\u308b\u306e\u3067\u3001\u30d5\u30a9\u30eb\u30c0\u69cb\u9020\u3092\u5d29\u3055\u305a\u306b\u5c55\u958b\u3059\u308c\u3070\u305d\u306e\u307e\u307e\u30d6\u30e9\u30a6\u30b6\u3067\u958b\u3051\u308b
- \u672c\u756a\u30b5\u30a4\u30c8\u306e\u5168\u753b\u50cf\u306f\u73fe\u884c `/uploads/` \u914d\u4e0b\u306b\u3042\u308b\uff08\u672c\u30d1\u30c3\u30b1\u30fc\u30b8\u306f\u5bb9\u91cf\u524a\u6e1b\u306e\u305f\u3081\u5fc5\u8981\u5206\u306e\u307f\u62b1\u5408\uff09\u3002WordPress\u79fb\u884c\u6642\u306e\u6271\u3044\u306f `URL\u79fb\u884c\u30de\u30c3\u30d7.md` \u306e4\u7ae0\u3092\u53c2\u7167
- 蔵ロゴ・ボトル画像は `/uploads/` 配下（現行サイト）にある。WordPress移行時はメディアライブラリへ一括インポート

## ファイル一覧（本パッケージ同梱）
- `reference/top-page.html` — トップページ
- `reference/brewery-archive.html` — 酒蔵一覧（開催回フィルタ対応版）
- `reference/brewery-single.html` / `brewery-single-mini.html` — 酒蔵詳細（後者は出展銘柄未定・mini出展蔵の例）
- `reference/brewery-style.css` — 酒蔵ページ共通CSS
- `reference/event-single-template.html` — イベント告知テンプレート
- `reference/news-archive.html` / `news-single-example.html` — お知らせ一覧・記事例
- `reference/blog-single-template.html` — 蔵元インタビュー等テンプレート
- `reference/past-event-single.html` — 過去開催レポート
- `reference/sponsors-page.html` — 協賛ページ

- `URL移行マップ.md` — 既存URL維持の方針と全URLリスト（**必読**）

移行計画・スケジュールの詳細は同梱の `TORASAKE WordPress移行計画書.html` を参照。
