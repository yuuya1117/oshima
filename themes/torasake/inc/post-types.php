<?php
/**
 * カスタム投稿タイプ。
 *
 * rewrite は全 CPT で false にしてある。URL は inc/rewrite.php で手書きする
 * （方式A: 既存URLの .html を正規URLとして維持するため、自動生成では形が合わない）。
 *
 * お知らせ(news)は標準の post をそのまま使う。CPT 化しない。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * CPT 登録。
 */
function torasake_register_post_types(): void {
	$shared = array(
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => false,
		'has_archive'  => false,
		'menu_position'=> 20,
	);

	register_post_type(
		'brewery',
		array_merge(
			$shared,
			array(
				'labels'        => torasake_pt_labels( '酒蔵', 'Brewery' ),
				'menu_icon'     => 'dashicons-store',
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
				'taxonomies'    => array( 'prefecture', 'event_edition' ),
				// 一覧の前後ナビ（bp-prev / bp-next）を手で並べ替えられるようにする。
				'hierarchical'  => false,
			)
		)
	);

	register_post_type(
		'event',
		array_merge(
			$shared,
			array(
				'labels'     => torasake_pt_labels( 'イベント', 'Event' ),
				'menu_icon'  => 'dashicons-tickets-alt',
				'supports'   => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'taxonomies' => array( 'event_edition' ),
			)
		)
	);

	register_post_type(
		'past_event',
		array_merge(
			$shared,
			array(
				'labels'     => torasake_pt_labels( '過去開催', 'Past Event' ),
				'menu_icon'  => 'dashicons-archive',
				'supports'   => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'taxonomies' => array( 'event_edition' ),
			)
		)
	);

	register_post_type(
		'blog_post',
		array_merge(
			$shared,
			array(
				'labels'    => torasake_pt_labels( 'ブログ', 'Blog' ),
				'menu_icon' => 'dashicons-edit-large',
				'supports'  => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			)
		)
	);

	register_post_type(
		'sponsor',
		array_merge(
			$shared,
			array(
				'labels'      => torasake_pt_labels( '協賛企業', 'Sponsor' ),
				'menu_icon'   => 'dashicons-awards',
				'supports'    => array( 'title', 'thumbnail', 'page-attributes' ),
				// 協賛は単体ページを持たず、event / トップから参照して表示するだけ。
				'public'      => true,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
			)
		)
	);
}
add_action( 'init', 'torasake_register_post_types', 5 );

/**
 * CPT ラベルの組み立て。
 *
 * @param string $ja 和名。
 * @param string $en 英名（管理画面の「新規追加」等で使う）。
 * @return array<string,string>
 */
function torasake_pt_labels( string $ja, string $en ): array {
	return array(
		'name'               => $ja,
		'singular_name'      => $ja,
		'add_new'            => '新規追加',
		'add_new_item'       => $ja . 'を追加',
		'edit_item'          => $ja . 'を編集',
		'new_item'           => '新しい' . $ja,
		'view_item'          => $ja . 'を表示',
		'search_items'       => $ja . 'を検索',
		'not_found'          => $ja . 'が見つかりません',
		'not_found_in_trash' => 'ゴミ箱に' . $ja . 'はありません',
		'all_items'          => $ja . '一覧',
		'menu_name'          => $ja,
		'name_admin_bar'     => $en,
	);
}
