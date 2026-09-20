<?php
/**
 * CSS / JS の読み込み。
 *
 * 参照HTML（docs/design_handoff_wordpress/reference/）は :root の値が
 * ページごとに微妙に異なる（--border が .22 と .25、--text が #16202e と #1a1a2e 等）。
 * 確定値として扱う指示なので統一せず、テンプレート別に逐語移植したCSSを出し分ける。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Google Fonts の preconnect。
 */
function torasake_resource_hints( array $hints, string $relation ): array {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'torasake_resource_hints', 10, 2 );

/**
 * フロント側のアセット。
 */
function torasake_enqueue_assets(): void {
	// 参照HTMLと同じウェイトを読む。
	wp_enqueue_style(
		'torasake-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;500;600;700;900&family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap',
		array(),
		null
	);

	// テーマ識別用（実スタイルは持たない）。
	wp_enqueue_style( 'torasake-style', get_stylesheet_uri(), array( 'torasake-fonts' ), TORASAKE_VERSION );

	if ( is_front_page() ) {
		torasake_style( 'front' );
		torasake_script( 'front' );

		$event = torasake_current_event();
		if ( $event ) {
			wp_localize_script(
				'torasake-front',
				'TORASAKE_FRONT',
				array(
					'eventDatetime' => torasake_iso_datetime(
						(string) torasake_field( 'event_date', $event->ID ),
						(string) torasake_field( 'event_start_time', $event->ID, '00:00' )
					),
					'finished'      => torasake_event_finished( $event->ID ),
				)
			);
		}
		return;
	}

	if ( is_post_type_archive( 'brewery' ) ) {
		torasake_style( 'brewery-archive' );
		torasake_script( 'brewery-archive' );
		return;
	}

	if ( is_singular( 'brewery' ) ) {
		torasake_style( 'brewery-single' );
		return;
	}

	if ( is_singular( 'event' ) ) {
		torasake_style( 'event-single' );
		return;
	}

	if ( is_singular( 'past_event' ) ) {
		torasake_style( 'past-event' );
		torasake_script( 'past-event' );
		torasake_script( 'nav-toggle' );
		return;
	}

	if ( is_singular( 'blog_post' ) ) {
		torasake_style( 'blog-single' );
		return;
	}

	if ( is_page_template( 'page-sponsors.php' ) || is_page( 'sponsors' ) ) {
		torasake_style( 'sponsors' );
		return;
	}

	// ブログ・イベントの一覧は参照HTMLが無い。確定済みの news-archive.css を
	// ベース（nav / page-hero / footer）にして、追加分だけ別ファイルで重ねる。
	if ( is_post_type_archive( 'blog_post' ) ) {
		torasake_style( 'news-archive' );
		torasake_style( 'blog-archive' );
		return;
	}

	if ( is_post_type_archive( 'event' ) ) {
		torasake_style( 'news-archive' );
		torasake_style( 'event-archive' );
		return;
	}

	if ( is_home() || is_category() || is_archive() ) {
		torasake_style( 'news-archive' );
		torasake_script( 'news-archive' );
		return;
	}

	if ( is_singular( 'post' ) ) {
		torasake_style( 'news-single' );
		torasake_script( 'nav-toggle' );
		return;
	}

	// 固定ページ等の既定。
	torasake_style( 'news-archive' );
}
add_action( 'wp_enqueue_scripts', 'torasake_enqueue_assets' );

/**
 * assets/css/{handle}.css を登録する。
 *
 * @param string $handle ファイル名（拡張子なし）。
 */
function torasake_style( string $handle ): void {
	$rel  = '/assets/css/' . $handle . '.css';
	$path = TORASAKE_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'torasake-' . $handle,
		TORASAKE_URI . $rel,
		array( 'torasake-fonts' ),
		(string) filemtime( $path )
	);
}

/**
 * assets/js/{handle}.js を登録する。
 *
 * @param string $handle ファイル名（拡張子なし）。
 */
function torasake_script( string $handle ): void {
	$rel  = '/assets/js/' . $handle . '.js';
	$path = TORASAKE_DIR . $rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'torasake-' . $handle,
		TORASAKE_URI . $rel,
		array(),
		(string) filemtime( $path ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
}
