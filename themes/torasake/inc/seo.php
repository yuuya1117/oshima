<?php
/**
 * meta description / OGP / JSON-LD。
 *
 * URL移行マップ 6章-5 の「現行HTMLの記述をそのまま移植する」に対応するため、
 * ページ単位で上書きできるようにしている（ACF: seo_title / seo_description / seo_og_image）。
 * 未入力なら本文・抜粋から組み立てる。
 *
 * SEOプラグインを別途入れる場合は、このファイルの読み込みを functions.php から外すこと。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * <title> の上書き。
 *
 * @param array<string,string> $parts タイトル要素。
 * @return array<string,string>
 */
function torasake_document_title_parts( array $parts ): array {
	if ( is_singular() ) {
		$custom = torasake_field( 'seo_title', get_the_ID() );
		if ( '' !== $custom ) {
			$parts['title'] = $custom;
			unset( $parts['tagline'] );
		}
	}
	return $parts;
}
add_filter( 'document_title_parts', 'torasake_document_title_parts' );

/**
 * head に meta description / canonical / OGP を出す。
 */
function torasake_head_meta(): void {
	$description = torasake_meta_description();
	$canonical   = torasake_canonical_url();
	$image       = torasake_og_image();
	$title       = wp_get_document_title();

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( '' !== $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	}

	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() && ! is_front_page() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( '' !== $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( '' !== $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	echo '<meta property="og:locale" content="ja_JP">' . "\n";
}
add_action( 'wp_head', 'torasake_head_meta', 2 );

/**
 * meta description を決める。
 *
 * @return string
 */
function torasake_meta_description(): string {
	if ( is_singular() ) {
		$custom = torasake_field( 'seo_description', get_the_ID() );
		if ( '' !== $custom ) {
			return $custom;
		}
		$excerpt = get_the_excerpt( get_the_ID() );
		if ( '' !== $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 90, '' );
		}
	}

	if ( is_front_page() ) {
		$event = torasake_current_event();
		if ( $event ) {
			$custom = torasake_field( 'seo_description', $event->ID );
			if ( '' !== $custom ) {
				return $custom;
			}
		}
	}

	return (string) get_bloginfo( 'description' );
}

/**
 * canonical。
 *
 * 単体ページは .html 付きの正規URL（方式A）になる。
 *
 * @return string
 */
function torasake_canonical_url(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	if ( is_post_type_archive( 'brewery' ) ) {
		return home_url( '/breweries/' );
	}
	if ( is_home() ) {
		return home_url( '/news/' );
	}
	return '';
}

/**
 * OGP 画像。
 *
 * @return string
 */
function torasake_og_image(): string {
	if ( is_singular() ) {
		$custom = torasake_field( 'seo_og_image', get_the_ID(), null );
		if ( is_array( $custom ) && ! empty( $custom['url'] ) ) {
			return (string) $custom['url'];
		}
		if ( has_post_thumbnail() ) {
			return (string) get_the_post_thumbnail_url( get_the_ID(), 'full' );
		}
	}

	if ( is_front_page() ) {
		$event = torasake_current_event();
		if ( $event ) {
			$kv = torasake_field( 'key_visual', $event->ID, null );
			if ( is_array( $kv ) && ! empty( $kv['url'] ) ) {
				return (string) $kv['url'];
			}
		}
	}

	return '';
}

/**
 * 酒蔵詳細の JSON-LD（Organization + BreadcrumbList）。
 *
 * 参照HTML（brewery-single.html）の構造化データを ACF から組み立てる。
 * makesOffer は銘柄名とカテゴリのみ。★価格は入れない（CLAUDE.md）。
 */
function torasake_brewery_jsonld(): void {
	if ( ! is_singular( 'brewery' ) ) {
		return;
	}

	$id        = get_the_ID();
	$permalink = get_permalink( $id );
	$name      = get_the_title( $id );
	$brand     = torasake_field( 'brand_name', $id );
	$logo      = torasake_field( 'logo', $id, null );

	$organization = array(
		'@type' => 'Organization',
		'@id'   => $permalink . '#organization',
		'name'  => $name,
	);

	$alternate = array_values(
		array_filter(
			array(
				$brand,
				torasake_field( 'brand_name_kana', $id ),
				torasake_field( 'brand_name_en', $id ),
			)
		)
	);
	if ( $alternate ) {
		$organization['alternateName'] = $alternate;
	}

	$tagline = wp_strip_all_tags( (string) torasake_field( 'tagline', $id ) );
	if ( '' !== $tagline ) {
		$organization['description'] = $tagline;
	}
	if ( is_array( $logo ) && ! empty( $logo['url'] ) ) {
		$organization['logo'] = $logo['url'];
	}

	$official = torasake_field( 'official_url', $id );
	if ( '' !== $official ) {
		$organization['url'] = $official;
	}

	$instagram = torasake_field( 'instagram_url', $id );
	if ( '' !== $instagram ) {
		$organization['sameAs'] = array( $instagram );
	}

	$founded = torasake_field( 'founded_year', $id );
	if ( preg_match( '/\d{4}/', (string) $founded, $m ) ) {
		$organization['foundingDate'] = $m[0];
	}

	$pref = torasake_brewery_pref( $id );
	$city = torasake_field( 'city', $id );
	if ( '' !== $pref || '' !== $city ) {
		$organization['address'] = array_filter(
			array(
				'@type'           => 'PostalAddress',
				'addressRegion'   => $pref,
				'addressLocality' => $city,
				'addressCountry'  => 'JP',
			)
		);
	}

	$offers = array();
	foreach ( torasake_rows( 'sake_lineup', $id ) as $row ) {
		$sake_name = $row['name_ja'] ?? '';
		if ( '' === $sake_name ) {
			continue;
		}
		$offers[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array_filter(
				array(
					'@type'    => 'Product',
					'name'     => $sake_name,
					'category' => $row['type_label'] ?? '',
				)
			),
		);
	}
	if ( $offers ) {
		$organization['makesOffer'] = $offers;
	}

	$graph = array(
		$organization,
		array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => get_bloginfo( 'name' ),
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => '参加酒蔵',
					'item'     => home_url( '/breweries/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => '' !== $brand ? sprintf( '%s（%s）', $name, $brand ) : $name,
				),
			),
		),
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'torasake_brewery_jsonld', 3 );
