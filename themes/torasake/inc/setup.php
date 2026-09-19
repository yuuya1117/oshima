<?php
/**
 * テーマの基本サポート宣言とメニュー・画像サイズ。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * テーマサポート。
 */
function torasake_setup(): void {
	load_theme_textdomain( 'torasake', TORASAKE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	// 参照HTMLのカードは 16:9 のサムネイルと蔵ロゴ（contain）を使う。
	add_image_size( 'torasake_card', 720, 405, true );   // news カード / アーカイブカード
	add_image_size( 'torasake_kv', 1920, 1080, true );   // ヒーローKV
	add_image_size( 'torasake_logo', 480, 480, false );  // 蔵ロゴ（トリミングしない）

	register_nav_menus(
		array(
			'primary' => __( 'グローバルナビ', 'torasake' ),
			'footer'  => __( 'フッターナビ', 'torasake' ),
		)
	);
}
add_action( 'after_setup_theme', 'torasake_setup' );

/**
 * 一覧系のクエリ件数。
 *
 * 酒蔵一覧は全件をサーバレンダリングしてから JS で絞り込む（参照HTMLの挙動を踏襲）ため、
 * ページングせず全件を出す。
 *
 * @param WP_Query $query メインクエリ。
 */
function torasake_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'brewery' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}

	// お知らせ一覧のカテゴリチップもクライアント側で絞り込むので、1ページに多めに出す。
	if ( $query->is_home() ) {
		$query->set( 'posts_per_page', 20 );
	}
}
add_action( 'pre_get_posts', 'torasake_pre_get_posts' );
