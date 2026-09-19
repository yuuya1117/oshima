<?php
/**
 * URL 設計 ─ 方式A（既存URLをそのまま生かす）。
 *
 * docs/design_handoff_wordpress/URL移行マップ.md の 3章に従う。
 * 現行サイトは静的HTMLでインデックス済みのため、.html 付きURLを
 * 「正規URL」として維持する。既存URLを1本も失わないことが最優先要件。
 *
 * 維持対象（すべて 200 を返すこと）:
 *   /                                 → front-page.php
 *   /news/                            → home.php        （/news/index.html は 301）
 *   /news/{slug}.html                 → single.php
 *   /past/                            → 固定ページ       （/past/index.html は 301）
 *   /uploads/*                        → 実ディレクトリのまま（4章・docs/移行手順.md 参照）
 *
 * 新規（移行制約なし）:
 *   /breweries/                       → archive-brewery.php
 *   /breweries/{slug}.html            → single-brewery.php
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * 方式A のリライトルール。
 *
 * CPT 側で rewrite => false にしているので、ここが唯一の定義元になる。
 * 'top' 指定で WordPress 既定のルールより先に評価させる。
 */
function torasake_rewrite_rules(): void {
	// ── お知らせ（標準 post）─ 現行URLをそのまま維持 ──
	// 日本語スラッグ2本（2026-06-27-当日券 / 2026-05-21-開催決定）もここを通る。
	// post_name は DB 上パーセントエンコードで保持されるが、WP_Query の
	// sanitize_title_for_query() が再エンコードして突き合わせるため追加処理は不要。
	add_rewrite_rule( '^news/([^/]+)\.html$', 'index.php?post_type=post&name=$matches[1]', 'top' );

	// ── 酒蔵 ──
	add_rewrite_rule( '^breweries/?$', 'index.php?post_type=brewery', 'top' );
	add_rewrite_rule( '^breweries/([^/]+)\.html$', 'index.php?post_type=brewery&brewery=$matches[1]&name=$matches[1]', 'top' );

	// ── 過去開催 ──
	// /past/ は固定ページで受ける（テーマ有効化時に作成）。個別レポートは /past/{slug}/。
	add_rewrite_rule( '^past/([^/]+)/?$', 'index.php?post_type=past_event&past_event=$matches[1]&name=$matches[1]', 'top' );

	// ── ブログ・協賛（新規URL）──
	add_rewrite_rule( '^blog/?$', 'index.php?post_type=blog_post', 'top' );
	add_rewrite_rule( '^blog/([^/]+)/?$', 'index.php?post_type=blog_post&blog_post=$matches[1]&name=$matches[1]', 'top' );
}
add_action( 'init', 'torasake_rewrite_rules', 10 );

/**
 * 投稿（お知らせ）のパーマリンクを .html 付きで出力する。
 *
 * これをやらないと本文中のリンクや一覧のリンクが /news/{slug}/ になり、
 * 「既存URLを1本も変えない」が崩れる。
 *
 * @param string  $link 既定のパーマリンク。
 * @param WP_Post $post 投稿。
 * @return string
 */
function torasake_post_link( string $link, WP_Post $post ): string {
	if ( 'post' !== $post->post_type || empty( $post->post_name ) ) {
		return $link;
	}
	// 下書き等でスラッグが未確定のうちは既定のまま返す。
	if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ), true ) ) {
		return $link;
	}
	// post_name は非ASCIIの場合すでにパーセントエンコード済み。二重エンコードしない。
	return home_url( '/news/' . $post->post_name . '.html' );
}
add_filter( 'post_link', 'torasake_post_link', 10, 2 );

/**
 * CPT のパーマリンク。
 *
 * @param string  $link 既定のパーマリンク。
 * @param WP_Post $post 投稿。
 * @return string
 */
function torasake_post_type_link( string $link, WP_Post $post ): string {
	if ( empty( $post->post_name ) ) {
		return $link;
	}
	if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ), true ) ) {
		return $link;
	}

	return match ( $post->post_type ) {
		// 参照HTMLと同形（/breweries/taka.html）。
		'brewery'    => home_url( '/breweries/' . $post->post_name . '.html' ),
		'past_event' => home_url( '/past/' . $post->post_name . '/' ),
		'blog_post'  => home_url( '/blog/' . $post->post_name . '/' ),
		default      => $link,
	};
}
add_filter( 'post_type_link', 'torasake_post_type_link', 10, 2 );

/**
 * 酒蔵アーカイブの URL。
 *
 * has_archive を false にしているので get_post_type_archive_link() が
 * 空を返す。テンプレートから安全に呼べるよう補う。
 *
 * @param string|false $link      既定のリンク。
 * @param string       $post_type 投稿タイプ。
 * @return string|false
 */
function torasake_post_type_archive_link( $link, string $post_type ) {
	return match ( $post_type ) {
		'brewery'   => home_url( '/breweries/' ),
		'blog_post' => home_url( '/blog/' ),
		default     => $link,
	};
}
add_filter( 'post_type_archive_link', 'torasake_post_type_archive_link', 10, 2 );

/**
 * 旧 index.html を canonical のスラッシュ形へ 301。
 *
 * 現行サイトの canonical はすでにスラッシュ形なので、そこへ寄せる
 * （URL移行マップ 3章）。
 */
function torasake_redirect_legacy_index(): void {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( '' === $uri ) {
		return;
	}

	$path  = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );

	$map = array(
		'/index.html'           => '/',
		'/news/index.html'      => '/news/',
		'/past/index.html'      => '/past/',
		'/breweries/index.html' => '/breweries/',
		'/blog/index.html'      => '/blog/',
	);

	if ( ! isset( $map[ $path ] ) ) {
		return;
	}

	$target = home_url( $map[ $path ] );
	if ( '' !== $query ) {
		$target .= '?' . $query;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'torasake_redirect_legacy_index', 1 );

/**
 * .html 付きURLに対して WordPress の正規化リダイレクトが働かないようにする。
 *
 * redirect_canonical() は /news/foo.html を /news/foo/ に飛ばそうとする。
 * それをやられると方式Aが成立しないので、対象の単体ページでは無効化する。
 *
 * @param string $redirect_url  リダイレクト先。
 * @param string $requested_url 要求されたURL。
 * @return string|false
 */
function torasake_disable_canonical_redirect( $redirect_url, $requested_url ) {
	if ( is_singular( array( 'post', 'brewery' ) ) ) {
		return false;
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'torasake_disable_canonical_redirect', 10, 2 );

/**
 * 指定スラッグの固定ページを用意する（既にあればその ID を返す）。
 *
 * @param string $slug  スラッグ。
 * @param string $title タイトル。
 * @return int 固定ページ ID。失敗時 0。
 */
function torasake_ensure_page( string $slug, string $title ): int {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post ) {
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_type'   => 'page',
			'post_status' => 'publish',
		)
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * テーマ有効化時のセットアップ。
 *
 * - パーマリンク構造を /%postname%/ にする（方式Aの前提）
 * - /news/ を投稿ページ、/past/ を固定ページとして用意する
 * - 初期タームを投入する
 * - リライトルールを書き出す
 *
 * いずれも冪等。既存の設定は上書きしない。
 */
function torasake_activate(): void {
	torasake_seed_terms();

	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	// 表示設定 ── / は front-page.php、/news/ は投稿ページ。
	// page_for_posts は show_on_front = 'page' のときだけ効くので、
	// フロント用の固定ページもあわせて用意する（front-page.php が優先されるため中身は空でよい）。
	if ( ! (int) get_option( 'page_for_posts' ) ) {
		$front_id = torasake_ensure_page( 'front', 'トップページ' );
		$news_id  = torasake_ensure_page( 'news', 'お知らせ' );

		if ( $front_id && $news_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_id );
			update_option( 'page_for_posts', $news_id );
		}
	}

	// /past/ ── 過去開催アーカイブの受け口。
	torasake_ensure_page( 'past', '過去のイベント' );

	torasake_register_post_types();
	torasake_register_taxonomies();
	torasake_rewrite_rules();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'torasake_activate' );
