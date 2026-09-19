<?php
/**
 * タクソノミー。
 *
 * prefecture    : 酒蔵一覧の都道府県フィルタ用。
 * event_edition : 開催回。brewery / event / past_event をまたいで結びつける。
 *                 1蔵が複数の開催回に属する（例: 二兎は 2026 と mini の両方）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * タクソノミー登録。
 */
function torasake_register_taxonomies(): void {
	register_taxonomy(
		'prefecture',
		array( 'brewery' ),
		array(
			'labels'            => array(
				'name'          => '都道府県',
				'singular_name' => '都道府県',
				'menu_name'     => '都道府県',
				'all_items'     => '都道府県一覧',
				'add_new_item'  => '都道府県を追加',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			// 個別アーカイブは持たない。絞り込みは /breweries/?pref= で行う。
			'publicly_queryable' => false,
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		'event_edition',
		array( 'brewery', 'event', 'past_event' ),
		array(
			'labels'            => array(
				'name'          => '開催回',
				'singular_name' => '開催回',
				'menu_name'     => '開催回',
				'all_items'     => '開催回一覧',
				'add_new_item'  => '開催回を追加',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'publicly_queryable' => false,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'torasake_register_taxonomies', 5 );

/**
 * 初期タームを用意する（テーマ有効化時に1度だけ）。
 *
 * 開催回の並び順は ACF のターム項目 edition_date（日付）で決まる。
 * 「直近の開催回」＝ edition_date が最大のターム。
 */
function torasake_seed_terms(): void {
	$editions = array(
		'torasake-2026'      => array( 'TORASAKE -虎ノ酒- 2026', '20260627' ),
		'torasake-mini-2026' => array( 'TORASAKE mini', '20261016' ),
	);

	foreach ( $editions as $slug => $data ) {
		$term = term_exists( $slug, 'event_edition' );
		if ( ! $term ) {
			$term = wp_insert_term( $data[0], 'event_edition', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) && empty( get_term_meta( (int) $term['term_id'], 'edition_date', true ) ) ) {
			update_term_meta( (int) $term['term_id'], 'edition_date', $data[1] );
		}
	}

	// お知らせのカテゴリー（参照HTMLのフィルタチップと一致させる）。
	$categories = array(
		'report' => '開催報告',
		'info'   => 'お知らせ',
		'guide'  => 'イベントガイド',
	);
	foreach ( $categories as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
}
