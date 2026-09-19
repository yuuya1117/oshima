# URL移行マップ（SEO維持）

**2026.09 時点の本番サイト https://torasake.com/ を実地確認して作成。** 現行サイトは静的HTMLでインデックス済み。WordPress化にあたり **既存URLを1本も失わないこと**を最優先要件とする。

## 1. 現行の公開URL（実在確認済み・すべて維持対象）

| 現行URL | 内容 | WordPressでの受け口 |
|---|---|---|
| `/`（`/index.html`） | **2026.06.27 開催のイベントLP**（1ページ完結・アンカー構成） | front-page.php |
| `/news/index.html`（canonical `/news/`） | お知らせ一覧（5本） | home.php / archive |
| `/news/2026-07-03-event-report.html` | 開催報告 | single.php |
| `/news/2026-06-27-当日券.html` | 本日開幕・当日券 | single.php |
| `/news/2026-05-28-matching.html` | マッチング31組発表 | single.php |
| `/news/2026-05-25-event-system.html` | イベントガイド | single.php |
| `/news/2026-05-21-開催決定.html` | 開催決定 | single.php |
| `/past/index.html`（canonical `/past/`） | 前身イベント「日本酒虎の巻 Vol.2」レポート | single-past_event.php または固定ページ |
| `/uploads/*` | 画像（OGP画像 `keyvisual.jpg` 等を含む） | 下記4章参照 |

- チケット導線は外部の **Peatix**（`https://peatix.com/event/5186533?utm_source=hp`（TORASAKE mini）。旧6/27回は `https://torasake.peatix.com/?utm_source=website`）。WordPress側でもこのUTM付きURLを踏襲する
- **本プロジェクトで新規に設計した `/breweries/`・`/blog/`・`/sponsors/` は本番未公開**。新規URLなので移行制約はない（= 好きな構成にしてよい）

## 2. トップページの扱い（最重要・要判断）

現行 `/` は **6/27イベント単体のLP**で、以下のアンカーが本文・SNS・外部記事から参照されている。

`#hero` `#breweries` `#how-to-enjoy` `#pairing` `#howto` `#floormap` `#access` `#faq`

新トップ（v6 = TORASAKE mini 告知）に差し替えると、これらのアンカー先が消える。アンカーはサーバ側でリダイレクトできないため、対処は次のいずれか。

- **推奨**: 現行の6/27 LPを **`/past/torasake-2026/`** などに丸ごと保存し（アンカーIDもそのまま）、`/` は開催回ごとに中身が変わる告知トップにする。旧アンカー付きURL（`/#pairing` 等）はトップに着地するだけなので、`/` 側に「過去の開催回はこちら」の導線を必ず置く
- 代替: 新トップでも `#breweries` `#access` `#faq` など**同じidを再利用**して主要アンカーを生かす（v6は `#breweries` を既に踏襲済み）

`/` 自体のURLは変わらないため、ドメイン評価・被リンクは維持される。

## 3. 実装方針（推奨: 方式A ─ 既存URLをそのまま生かす）

`.html` 付きURLを正規URLとして維持する。URLが変わらないので順位変動リスクが最小。

```php
// functions.php
add_action('init', function () {
  add_rewrite_rule('^news/([^/]+)\.html$', 'index.php?post_type=post&name=$matches[1]', 'top');
  add_rewrite_rule('^past/?$',              'index.php?pagename=past', 'top');
});

// 投稿パーマリンクも .html 付きで出力する
add_filter('post_link', function ($link, $post) {
  if ($post->post_type === 'post') {
    return trailingslashit(home_url('news')) . $post->post_name . '.html';
  }
  return $link;
}, 10, 2);
```

- 投稿スラッグは**現行ファイル名と完全一致**させる: `2026-07-03-event-report` / `2026-06-27-当日券` / `2026-05-28-matching` / `2026-05-25-event-system` / `2026-05-21-開催決定`
- **日本語スラッグ2本**（`当日券`・`開催決定`）は管理画面のスラッグ欄に生の日本語で入力する。パーセントエンコードして貼らないこと
- `/news/index.html` → `/news/`、`/past/index.html` → `/past/` は301（現行canonicalが既にスラッシュ形なので、canonicalに合わせる）

### 方式B ─ クリーンURL化して301
`/news/xxx.html` → `/news/xxx/` に整理して旧URLから301。評価はほぼ引き継がれるが一時的な変動があるため、**理由がなければ方式Aを採る**。

## 4. 画像URLの維持（見落としやすい）

現行の画像は `/uploads/...` 直下。メディアライブラリに取り込むと `/wp-content/uploads/YYYY/MM/...` に変わり、**画像検索の流入とOGPキャッシュが切れる**。

- `.htaccess` で `/uploads/` を実ディレクトリのまま残す（最も安全）、または旧パス→新パスの301を個別に張る
- 特に `/uploads/keyvisual.jpg`（現行トップのOGP画像）と `/uploads/staff.jpg`（/past/ のOGP画像）は外部にキャッシュされているので必ず維持

## 5. 新規ページのURL設計（新規なので自由・下記を推奨）

| ページ | 推奨URL |
|---|---|
| 酒蔵一覧 | `/breweries/` |
| 酒蔵詳細（33件） | `/breweries/{slug}.html` ※本パッケージの参照HTMLと同形 |
| 過去開催アーカイブ | `/past/`（一覧化）＋ `/past/{slug}/` |
| ブログ | `/blog/` |
| 協賛 | `/sponsors/` |

酒蔵スラッグ33件: `afuri, azuma-rikishi, daishinshu, eight, emishiki, fujii, fukucho, gakki-masamune, gassan, hakkaisan, haneya, harada, ippoki, iyokagiya, kaiun, kakurei, kariho, kunpeki, mutsu-hassen, niida, nito, noguchi-naohiko, okunokami, raifuku, retsu, sake-hitosuji, shichida, sogen, taka, takachiyo, tsuchida, yoakemae, zaku`

## 6. 移行チェックリスト
1. 移行前に現行サイトをクロールして全URLを一覧化し、本マップと突き合わせる
2. Search Console から過去3〜6ヶ月の流入上位URL・被リンクを書き出し、**1本も欠けていないこと**を確認
3. ステージングで全URLに200が返ることをテスト（301の場合は最終到達先が200か）
4. 旧 `/uploads/` 画像パスの疎通を確認
5. 各ページの `<title>` / `meta description` / OGP / JSON-LD は現行HTMLの記述を**そのまま移植**する（書き換えると順位が動く）
6. 公開直後にサイトマップを再送信し、カバレッジを1週間監視
7. Peatixリンクの `?utm_source=` を落とさない（流入計測が切れる）。現行の告知は `?utm_source=hp`
