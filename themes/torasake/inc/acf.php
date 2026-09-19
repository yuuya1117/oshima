<?php
/**
 * ACF ローカルJSON の同期。
 *
 * フィールド定義は acf-json/ に置き Git 管理する。
 * 管理画面で編集すると同じ場所に書き戻るので、差分がレビューできる。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * 保存先を acf-json/ に固定する。
 *
 * @param string $path 既定の保存先。
 * @return string
 */
function torasake_acf_json_save_point( string $path ): string {
	return TORASAKE_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'torasake_acf_json_save_point' );

/**
 * 読み込み元に acf-json/ を加える。
 *
 * @param array<int,string> $paths 既定の読み込み元。
 * @return array<int,string>
 */
function torasake_acf_json_load_point( array $paths ): array {
	unset( $paths[0] );
	$paths[] = TORASAKE_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'torasake_acf_json_load_point' );

/**
 * ACF 未導入でもテーマが致命的に壊れないようにする。
 *
 * 表示は落ちるが白画面にはならない。本番では ACF PRO を必須とする。
 *
 * @param string   $selector フィールド名。
 * @param int|string $post_id 対象。
 * @param mixed    $default 既定値。
 * @return mixed
 */
function torasake_field( string $selector, $post_id = false, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $selector, $post_id );
	return ( null === $value || '' === $value || false === $value ) ? $default : $value;
}

/**
 * repeater / relationship 用。必ず配列を返す。
 *
 * @param string     $selector フィールド名。
 * @param int|string $post_id  対象。
 * @return array<int,mixed>
 */
function torasake_rows( string $selector, $post_id = false ): array {
	$value = torasake_field( $selector, $post_id, array() );
	return is_array( $value ) ? $value : array();
}

/**
 * サイト共通設定のオプションページ。
 *
 * acf-json/group_site.json の location が指す 'torasake-settings' を作る。
 */
function torasake_acf_options_page(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title' => 'サイト共通設定',
			'menu_title' => 'サイト設定',
			'menu_slug'  => 'torasake-settings',
			'capability' => 'edit_theme_options',
			'redirect'   => false,
			'icon_url'   => 'dashicons-admin-settings',
			'position'   => 59,
		)
	);
}
add_action( 'acf/init', 'torasake_acf_options_page' );
