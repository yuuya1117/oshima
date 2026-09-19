<?php
/**
 * テンプレートから使うヘルパー。
 *
 * ★ 価格に関する規約（CLAUDE.md）
 *   価格を出してよいのは トップの Tickets セクションと event 配下だけ。
 *   酒蔵一覧・酒蔵詳細には一切出さない。brewery の ACF にも価格項目を作らない。
 *   このファイルにも brewery から価格を取り出すヘルパーは置かないこと。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

/**
 * 告知中のイベントを1件返す。
 *
 * トップページは「常設のブランドサイト」ではなく直近イベントのLPなので、
 * ここが空なら front-page.php は「次回準備中」フォールバックを出す。
 *
 * @return WP_Post|null
 */
function torasake_current_event(): ?WP_Post {
	static $cached = false;
	if ( false !== $cached ) {
		return $cached;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'event',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => 'event_status',
					'value'   => array( '開催予定', '開催中' ),
					'compare' => 'IN',
				),
			),
			'meta_key'               => 'event_date',
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
		)
	);

	$cached = $query->have_posts() ? $query->posts[0] : null;
	return $cached;
}

/**
 * 直近の開催回ターム（酒蔵一覧の既定選択に使う）。
 *
 * edition_date（ターム項目・Ymd）が最大のものを「直近」とみなす。
 *
 * @return WP_Term|null
 */
function torasake_latest_edition(): ?WP_Term {
	static $cached = false;
	if ( false !== $cached ) {
		return $cached;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'event_edition',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		$cached = null;
		return $cached;
	}

	usort(
		$terms,
		static function ( WP_Term $a, WP_Term $b ): int {
			return strcmp(
				(string) get_term_meta( $b->term_id, 'edition_date', true ),
				(string) get_term_meta( $a->term_id, 'edition_date', true )
			);
		}
	);

	$cached = $terms[0];
	return $cached;
}

/**
 * 蔵ロゴの img タグ。未登録なら蔵名を大きく組む wordmark にフォールバックする。
 *
 * @param int    $brewery_id 酒蔵の投稿ID。
 * @param string $size       画像サイズ。
 * @param string $wordmark_class wordmark 時のクラス。
 * @return string エスケープ済みHTML。
 */
function torasake_brewery_logo( int $brewery_id, string $size = 'torasake_logo', string $wordmark_class = 'wordmark' ): string {
	$logo = torasake_field( 'logo', $brewery_id, null );
	$name = get_the_title( $brewery_id );

	if ( is_array( $logo ) && ! empty( $logo['ID'] ) ) {
		return wp_get_attachment_image(
			(int) $logo['ID'],
			$size,
			false,
			array(
				'alt'     => $name,
				'loading' => 'lazy',
			)
		);
	}

	if ( has_post_thumbnail( $brewery_id ) ) {
		return get_the_post_thumbnail( $brewery_id, $size, array( 'alt' => $name, 'loading' => 'lazy' ) );
	}

	return sprintf(
		'<span class="%s">%s</span>',
		esc_attr( $wordmark_class ),
		esc_html( torasake_field( 'brand_name', $brewery_id, $name ) )
	);
}

/**
 * 酒蔵の都道府県名（フル）。
 *
 * @param int $brewery_id 酒蔵の投稿ID。
 * @return string 例: 山口県
 */
function torasake_brewery_pref( int $brewery_id ): string {
	$terms = get_the_terms( $brewery_id, 'prefecture' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	return $terms[0]->name;
}

/**
 * 都道府県の短縮形（トップの蔵グリッド「愛媛・成龍酒造」用）。
 *
 * @param string $pref 都道府県名。
 * @return string 例: 山口
 */
function torasake_pref_short( string $pref ): string {
	return (string) preg_replace( '/(都|道|府|県)$/u', '', $pref );
}

/**
 * 蔵の「産地」表示（トップの蔵グリッド）。
 *
 * @param int $brewery_id 酒蔵の投稿ID。
 * @return string 例: 愛媛・成龍酒造
 */
function torasake_brewery_origin( int $brewery_id ): string {
	$pref = torasake_pref_short( torasake_brewery_pref( $brewery_id ) );
	$name = get_the_title( $brewery_id );

	return '' === $pref ? $name : $pref . '・' . $name;
}

/**
 * event_status が「終了」かどうか。
 *
 * スティッキーCTAの出し分けに使う。
 *
 * @param int $event_id イベントID。
 * @return bool
 */
function torasake_event_finished( int $event_id ): bool {
	return '終了' === torasake_field( 'event_status', $event_id );
}

/**
 * ACF の日付（Ymd）を ISO8601 に直す。
 *
 * カウントダウン用に data 属性へ渡す。
 *
 * @param string $ymd   ACF の date_picker 生値（Ymd）。
 * @param string $time  開始時刻（H:i）。
 * @return string 例: 2026-10-16T17:00:00+09:00
 */
function torasake_iso_datetime( string $ymd, string $time = '00:00' ): string {
	if ( ! preg_match( '/^\d{8}$/', $ymd ) ) {
		return '';
	}
	if ( ! preg_match( '/^\d{1,2}:\d{2}$/', $time ) ) {
		$time = '00:00';
	}

	$date = DateTimeImmutable::createFromFormat(
		'Ymd H:i',
		$ymd . ' ' . $time,
		new DateTimeZone( wp_timezone_string() )
	);

	return $date ? $date->format( 'c' ) : '';
}

/**
 * お知らせのカテゴリスラッグ（先頭1件）。フィルタチップの data-cat に使う。
 *
 * @param int $post_id 投稿ID。
 * @return string report | info | guide | ''
 */
function torasake_news_cat_slug( int $post_id ): string {
	$cats = get_the_category( $post_id );
	return empty( $cats ) ? '' : $cats[0]->slug;
}

/**
 * 参照HTMLのタグ配色クラス（cat-report / cat-info / cat-guide）。
 *
 * @param string $slug カテゴリスラッグ。
 * @return string
 */
function torasake_news_tag_class( string $slug ): string {
	return in_array( $slug, array( 'report', 'info', 'guide' ), true ) ? 'cat-' . $slug : 'cat-info';
}

/**
 * ナビの種類を返す（header.php から呼ぶ）。
 *
 * @return string front | back | article
 */
function torasake_nav_variant(): string {
	if ( is_front_page() ) {
		return 'front';
	}
	// 記事系はハンバーガー付きのフルナビ。
	if ( is_singular( array( 'post', 'past_event' ) ) ) {
		return 'article';
	}
	// イベント告知はチケットCTA付き。
	if ( is_singular( 'event' ) ) {
		return 'event';
	}
	return 'back';
}

/**
 * フッターの種類を返す（footer.php から呼ぶ）。
 *
 * @return string front | brand | copyright | article
 */
function torasake_footer_variant(): string {
	if ( is_front_page() ) {
		return 'front';
	}
	if ( is_singular( 'brewery' ) ) {
		return 'copyright';
	}
	if ( is_singular( array( 'post', 'past_event' ) ) ) {
		return 'article';
	}
	return 'brand';
}

/**
 * ナビの「戻る」先。
 *
 * @return array{0:string,1:string} URL とラベル。
 */
function torasake_back_link(): array {
	if ( is_singular( 'brewery' ) ) {
		return array( home_url( '/breweries/' ), '参加酒蔵 一覧へ' );
	}
	if ( is_singular( 'blog_post' ) ) {
		return array( home_url( '/blog/' ), 'ブログ一覧へ' );
	}
	return array( home_url( '/' ), 'トップへ戻る' );
}

/**
 * サイトロゴ URL。
 *
 * カスタムロゴが未設定なら現行サイトと同じ /uploads/torasake_logo.png を指す
 * （URL移行マップ 4章: 画像は /uploads/ 直下のまま維持する）。
 *
 * @return string
 */
function torasake_site_logo_url(): string {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $src ) {
			return (string) $src[0];
		}
	}
	return home_url( '/uploads/torasake_logo.png' );
}

/**
 * ファビコン URL。
 *
 * @return string
 */
function torasake_favicon_url(): string {
	$icon = get_site_icon_url( 180 );
	return $icon ? $icon : home_url( '/uploads/torasake_logo.png' );
}

/**
 * コピーライト行。
 *
 * @param string $suffix 「実行委員会」等の後置き。
 * @return string
 */
function torasake_copyright( string $suffix = '' ): string {
	$name = get_bloginfo( 'name' );
	$year = wp_date( 'Y' );

	return '' === $suffix
		? sprintf( '© %s %s All Rights Reserved.', $year, $name )
		: sprintf( '© %s %s %s', $year, $name, $suffix );
}

/**
 * サイト共通のリンク（ACF オプションページ）。
 *
 * オプションページが無い環境でも落ちないように get_field の 'option' 呼び出しを包む。
 *
 * @param string $name フィールド名。
 * @return string
 */
function torasake_option_url( string $name ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$value = get_field( $name, 'option' );
	return is_string( $value ) ? $value : '';
}

/**
 * 本文中に許可するインラインHTML。
 *
 * 参照HTMLは <br>・<strong>・<span class="em"> を見出しやリード文で使っている。
 *
 * @return array<string,array<string,array<string,bool>>>
 */
function torasake_inline_html(): array {
	return array(
		'br'     => array( 'class' => array() ),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'small'  => array(),
		'span'   => array( 'class' => array() ),
		'a'      => array(
			'href'   => array(),
			'target' => array(),
			'rel'    => array(),
			'class'  => array(),
		),
	);
}

/**
 * KV の alt テキスト。
 *
 * README: SEOのため alt に日時・会場を入れる。
 * ACF kv_alt が入っていればそれを優先する。
 *
 * @param int $event_id イベントID。
 * @return string
 */
function torasake_event_kv_alt( int $event_id ): string {
	$custom = (string) torasake_field( 'kv_alt', $event_id );
	if ( '' !== $custom ) {
		return $custom;
	}

	$parts = array_filter(
		array(
			get_the_title( $event_id ),
			torasake_event_date_label( $event_id ),
			(string) torasake_field( 'venue_name', $event_id ),
		)
	);

	return implode( ' ', $parts );
}

/**
 * イベント日の表示（例: 2026年10月16日(金)）。
 *
 * @param int $event_id イベントID。
 * @return string
 */
function torasake_event_date_label( int $event_id ): string {
	$ymd = (string) torasake_field( 'event_date', $event_id );
	if ( ! preg_match( '/^\d{8}$/', $ymd ) ) {
		return '';
	}

	$date = DateTimeImmutable::createFromFormat( 'Ymd', $ymd, wp_timezone() );
	if ( ! $date ) {
		return '';
	}

	$weekdays = array( '日', '月', '火', '水', '木', '金', '土' );

	return sprintf(
		'%s年%s月%s日(%s)',
		$date->format( 'Y' ),
		$date->format( 'n' ),
		$date->format( 'j' ),
		$weekdays[ (int) $date->format( 'w' ) ]
	);
}

/**
 * ヒーローバーの DATE / VENUE / SAKE。
 *
 * 蔵数はハードコードせず、リレーションの件数から出す。
 *
 * @param int $event_id       イベントID。
 * @param int $brewery_count  参加酒蔵の件数。
 * @return array<int,array{key:string,value:string}>
 */
function torasake_event_hero_items( int $event_id, int $brewery_count ): array {
	$items = array();

	$date_value = trim( torasake_event_date_label( $event_id ) . ' ' . (string) torasake_field( 'event_time_range', $event_id ) );
	if ( '' !== $date_value ) {
		$items[] = array( 'key' => 'DATE', 'value' => $date_value );
	}

	$venue = (string) torasake_field( 'venue_name', $event_id );
	if ( '' !== $venue ) {
		$items[] = array( 'key' => 'VENUE', 'value' => $venue );
	}

	$sake = (string) torasake_field( 'hero_sake_label', $event_id );
	if ( '' === $sake && $brewery_count > 0 ) {
		$sake = sprintf( '全国%d蔵', $brewery_count );
	}
	if ( '' !== $sake ) {
		$items[] = array( 'key' => 'SAKE', 'value' => $sake );
	}

	return $items;
}

/**
 * 過去開催カードの日付行（例: 2026.06.27 SAT ── 虎ノ門ヒルズ）。
 *
 * @param int $past_id past_event の投稿ID。
 * @return string
 */
function torasake_past_event_dateline( int $past_id ): string {
	$ymd   = (string) torasake_field( 'event_date', $past_id );
	$venue = (string) torasake_field( 'venue_name', $past_id );
	$label = '';

	if ( preg_match( '/^\d{8}$/', $ymd ) ) {
		$date = DateTimeImmutable::createFromFormat( 'Ymd', $ymd, wp_timezone() );
		if ( $date ) {
			$label = $date->format( 'Y.m.d' ) . ' ' . strtoupper( $date->format( 'D' ) );
		}
	}

	if ( '' !== $label && '' !== $venue ) {
		return $label . ' ── ' . $venue;
	}

	return '' !== $label ? $label : $venue;
}

/**
 * 開催回タームの表示名。
 *
 * edition_label_short があればそれを使う（セレクトが長くなりすぎるため）。
 *
 * @param WP_Term $term 開催回ターム。
 * @return string
 */
function torasake_edition_label( WP_Term $term ): string {
	$short = (string) get_term_meta( $term->term_id, 'edition_label_short', true );
	return '' !== $short ? $short : $term->name;
}

/**
 * 酒蔵ヒーローの地域行（例: Yamaguchi ・ 山口県 宇部市）。
 *
 * @param int $brewery_id 酒蔵ID。
 * @return string
 */
function torasake_brewery_region_line( int $brewery_id ): string {
	// 全文を手で入れたい場合は region_ja が優先。
	$override = (string) torasake_field( 'region_ja', $brewery_id );
	if ( '' !== $override ) {
		return $override;
	}

	$en   = (string) torasake_field( 'region_en', $brewery_id );
	$pref = torasake_brewery_pref( $brewery_id );
	$city = (string) torasake_field( 'city', $brewery_id );

	$jp = trim( $pref . ' ' . $city );

	if ( '' !== $en && '' !== $jp ) {
		return $en . ' ・ ' . $jp;
	}

	return '' !== $jp ? $jp : $en;
}

/**
 * 蔵名の英字・別名行（例: 貴 ・ TAKA ・ Domaine Taka）。
 *
 * @param int $brewery_id 酒蔵ID。
 * @return string
 */
function torasake_brewery_name_en( int $brewery_id ): string {
	$parts = array_filter(
		array(
			(string) torasake_field( 'brand_name', $brewery_id ),
			(string) torasake_field( 'brand_name_en', $brewery_id ),
			(string) torasake_field( 'brand_alias', $brewery_id ),
		)
	);

	return implode( ' ・ ', array_unique( $parts ) );
}

/**
 * 銘柄カードのアクセントカラー。
 *
 * 参照HTMLは oklch で銘柄ごとに色相だけを変えている。
 * 明度・彩度は参照の値をそのまま固定し、ACF からは hue（角度）だけを受ける。
 *
 * @param float $hue 色相角（0-360）。
 * @return string style 属性の中身。
 */
function torasake_accent_style( float $hue ): string {
	$hue = fmod( max( 0.0, $hue ), 360.0 );

	return sprintf(
		'--accent:oklch(0.58 0.11 %1$s); --accent-soft:oklch(0.96 0.025 %1$s); --accent-line:oklch(0.86 0.05 %1$s);',
		rtrim( rtrim( number_format( $hue, 2, '.', '' ), '0' ), '.' )
	);
}

/**
 * URL からホスト名を取り出す（www. は落とす）。
 *
 * @param string $url URL。
 * @return string
 */
function torasake_pretty_host( string $url ): string {
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	return (string) preg_replace( '/^www\./', '', $host );
}

/**
 * Instagram URL から @ハンドルを取り出す。
 *
 * @param string $url Instagram URL。
 * @return string
 */
function torasake_instagram_handle( string $url ): string {
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$path = explode( '/', $path )[0] ?? '';
	return '' === $path ? $url : '@' . $path;
}

/**
 * 酒蔵の参加イベント履歴。
 *
 * event（告知中）と past_event（開催済み）を開催日の新しい順にまとめる。
 * event_history リレーションが手で設定されていればそれを優先する。
 *
 * @param int $brewery_id 酒蔵ID。
 * @return array<int,array{url:string,status:string,state:string,name:string,meta:string}>
 */
function torasake_brewery_event_history( int $brewery_id ): array {
	$related = torasake_rows( 'event_history', $brewery_id );

	if ( ! $related ) {
		// 未設定なら開催回タクソノミー経由で拾う。
		$editions = wp_get_post_terms( $brewery_id, 'event_edition', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $editions ) || empty( $editions ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'      => array( 'event', 'past_event' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'event_edition',
						'field'    => 'term_id',
						'terms'    => $editions,
					),
				),
			)
		);
		$related = $query->posts;
	}

	$entries = array();
	foreach ( $related as $item ) {
		$post = $item instanceof WP_Post ? $item : get_post( (int) $item );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$id       = (int) $post->ID;
		$finished = 'past_event' === $post->post_type || torasake_event_finished( $id );
		$venue    = (string) torasake_field( 'venue_name', $id );
		$date     = torasake_event_date_label( $id );

		$entries[] = array(
			'url'    => (string) get_permalink( $id ),
			'status' => $finished ? '開催報告' : '開催予定',
			'state'  => $finished ? 'done' : 'upcoming',
			'name'   => get_the_title( $id ),
			'meta'   => trim( $date . ( '' !== $venue ? '／ ' . $venue : '' ) ),
			'sort'   => (string) torasake_field( 'event_date', $id ),
		);
	}

	usort( $entries, static fn( array $a, array $b ): int => strcmp( $b['sort'], $a['sort'] ) );

	return $entries;
}

/**
 * 投稿ページ（/news/）の見出し。
 *
 * @return string
 */
function torasake_posts_page_title(): string {
	$page_id = (int) get_option( 'page_for_posts' );
	return $page_id ? get_the_title( $page_id ) : 'お知らせ';
}

/**
 * イベントの開催回ラベル（ヒーローの pill）。
 *
 * @param int $event_id イベントID。
 * @return string
 */
function torasake_event_edition_label( int $event_id ): string {
	$override = (string) torasake_field( 'edition_pill', $event_id );
	if ( '' !== $override ) {
		return $override;
	}

	$terms = get_the_terms( $event_id, 'event_edition' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	return torasake_edition_label( $terms[0] );
}

/**
 * イベント詳細ヒーローの日程・時間・会場。
 *
 * @param int $event_id イベントID。
 * @return array<int,array{key:string,value:string}>
 */
function torasake_event_meta_row( int $event_id ): array {
	$row = array();

	$date = torasake_event_date_label( $event_id );
	if ( '' !== $date ) {
		$row[] = array( 'key' => '日程', 'value' => $date );
	}

	$time = (string) torasake_field( 'event_time_range', $event_id );
	if ( '' !== $time ) {
		$row[] = array( 'key' => '時間', 'value' => $time );
	}

	$venue = (string) torasake_field( 'venue_name', $event_id );
	if ( '' !== $venue ) {
		$row[] = array( 'key' => '会場', 'value' => $venue );
	}

	return $row;
}

/**
 * 会場カードの「住所 / 最寄駅 / フロア」行。
 *
 * @param int $event_id イベントID。
 * @return array<int,string>
 */
function torasake_event_venue_lines( int $event_id ): array {
	$lines = array();

	foreach ( array( '住所' => 'venue_address', '最寄駅' => 'venue_access', 'フロア' => 'venue_floor' ) as $label => $field ) {
		$value = (string) torasake_field( $field, $event_id );
		if ( '' !== $value ) {
			$lines[] = $label . ':' . $value;
		}
	}

	return $lines;
}

/**
 * イベントに紐づくスポンサーをティア別にまとめる。
 *
 * @param int $event_id イベントID。
 * @return array<string,array<int,int>> title / supporter の順。空なら空配列。
 */
function torasake_event_sponsors_by_tier( int $event_id ): array {
	return torasake_group_sponsors( torasake_rows( 'sponsors', $event_id ) );
}

/**
 * 公開中のスポンサーを全件ティア別にまとめる（協賛ページ用）。
 *
 * @return array<string,array<int,int>>
 */
function torasake_sponsors_by_tier(): array {
	$query = new WP_Query(
		array(
			'post_type'      => 'sponsor',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	return torasake_group_sponsors( $query->posts );
}

/**
 * スポンサー投稿の集合を tier で振り分ける。
 *
 * @param array<int,mixed> $items WP_Post または投稿ID の配列。
 * @return array<string,array<int,int>>
 */
function torasake_group_sponsors( array $items ): array {
	$grouped = array( 'title' => array(), 'supporter' => array() );

	foreach ( $items as $item ) {
		$id = $item instanceof WP_Post ? (int) $item->ID : (int) $item;
		if ( ! $id || 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		$tier = 'title' === torasake_field( 'tier', $id, 'supporter' ) ? 'title' : 'supporter';
		$grouped[ $tier ][] = $id;
	}

	// 中身のあるティアだけ返す。
	return array_filter( $grouped );
}

/**
 * ブログ記事の「◯◯県 ◯◯酒造」行。
 *
 * @param int $blog_id ブログ記事ID。
 * @return string
 */
function torasake_blog_brewery_line( int $blog_id ): string {
	$brewery_id = torasake_first_post_id( torasake_field( 'related_brewery', $blog_id, null ) );
	if ( ! $brewery_id ) {
		return '';
	}

	$pref = torasake_brewery_pref( $brewery_id );
	$name = get_the_title( $brewery_id );

	return '' === $pref ? $name : $pref . ' ' . $name;
}

/**
 * relationship / post_object の戻り値から最初の投稿IDを取り出す。
 *
 * relationship は配列、post_object は単体を返すので両方受ける。
 *
 * @param mixed $value ACF の戻り値。
 * @return int 見つからなければ 0。
 */
function torasake_first_post_id( $value ): int {
	if ( $value instanceof WP_Post ) {
		return (int) $value->ID;
	}
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	if ( is_array( $value ) && ! empty( $value ) ) {
		return torasake_first_post_id( reset( $value ) );
	}
	return 0;
}
