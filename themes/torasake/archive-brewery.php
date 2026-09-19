<?php
/**
 * 酒蔵一覧（参照: reference/brewery-archive.html）。
 *
 * URL: /breweries/   （新規URLなので移行制約なし。/breweries/index.html は 301）
 *
 * 参照HTMLは JS 内の配列を絞り込んでいたが、データソースを WP_Query に置き換えた。
 * 全件をサーバレンダリングしたうえで、検索・都道府県・開催回の絞り込みは
 * data-* 属性を見て JS が行う（参照HTMLと同じ即時フィルタのUXを保つ・SEOにも有利）。
 *
 * ★ 価格は一切表示しない（CLAUDE.md）。brewery の ACF にも価格項目は無い。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$latest_edition = torasake_latest_edition();
$default_event  = $latest_edition ? $latest_edition->slug : '';

// セレクトの選択肢。実際に蔵が紐づいているタームだけを出す。
$pref_terms    = get_terms( array( 'taxonomy' => 'prefecture', 'hide_empty' => true ) );
$edition_terms = get_terms( array( 'taxonomy' => 'event_edition', 'hide_empty' => true, 'object_ids' => null ) );
$pref_terms    = is_wp_error( $pref_terms ) ? array() : $pref_terms;
$edition_terms = is_wp_error( $edition_terms ) ? array() : $edition_terms;

// 開催回は新しい順に並べる。
usort(
	$edition_terms,
	static fn( WP_Term $a, WP_Term $b ): int => strcmp(
		(string) get_term_meta( $b->term_id, 'edition_date', true ),
		(string) get_term_meta( $a->term_id, 'edition_date', true )
	)
);

// 全件レンダリングしているので、公開済みの酒蔵数がそのまま総数になる。
$counts = wp_count_posts( 'brewery' );
$total  = $counts ? (int) $counts->publish : 0;
?>

<section id="hero">
	<span class="hero-eyebrow">Sake Brewery Directory</span>
	<h1><?php post_type_archive_title(); ?></h1>

	<div class="search-bar">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
		<label class="screen-reader-text" for="q">蔵名・銘柄名で検索</label>
		<input id="q" type="text" placeholder="蔵名・銘柄名で検索(例:二兎、純米大吟醸)" autocomplete="off">
		<button class="clear-btn" id="clearBtn" type="button">クリア</button>
	</div>

	<div class="filter-row">
		<label class="screen-reader-text" for="prefSelect">都道府県で絞り込む</label>
		<select class="filter-select" id="prefSelect">
			<option value="">都道府県:すべて</option>
			<?php foreach ( $pref_terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->name ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>

		<label class="screen-reader-text" for="eventSelect">開催回で絞り込む</label>
		<select class="filter-select" id="eventSelect" data-default="<?php echo esc_attr( $default_event ); ?>">
			<option value="">開催回:すべて</option>
			<?php foreach ( $edition_terms as $term ) : ?>
				<?php // 直近の開催回を既定選択にする。 ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $term->slug, $default_event ); ?>>
					<?php echo esc_html( torasake_edition_label( $term ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</section>

<div class="result-meta">
	<div><strong id="countNum"><?php echo esc_html( (string) $total ); ?></strong> 蔵 / 全<span id="totalNum"><?php echo esc_html( (string) $total ); ?></span>蔵</div>
	<div class="tags" id="activeTags"></div>
</div>

<section id="grid-section">
	<div class="brew-grid" id="grid">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				$brewery_id = get_the_ID();
				$pref       = torasake_brewery_pref( $brewery_id );
				$lineup     = (string) torasake_field( 'lineup_summary', $brewery_id );
				$editions   = wp_get_post_terms( $brewery_id, 'event_edition', array( 'fields' => 'slugs' ) );
				$editions   = is_wp_error( $editions ) ? array() : $editions;
				// 検索対象は蔵名・ブランド名・出展銘柄。
				$haystack   = implode(
					' ',
					array_filter(
						array(
							get_the_title(),
							(string) torasake_field( 'brand_name', $brewery_id ),
							$lineup,
						)
					)
				);
				?>
				<a class="brew-card"
					href="<?php the_permalink(); ?>"
					data-pref="<?php echo esc_attr( $pref ); ?>"
					data-events="<?php echo esc_attr( implode( ',', $editions ) ); ?>"
					data-search="<?php echo esc_attr( mb_strtolower( $haystack, 'UTF-8' ) ); ?>">
					<div class="img"><?php echo torasake_brewery_logo( $brewery_id, 'torasake_logo', 'wordmark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="body">
						<?php if ( '' !== $pref ) : ?>
							<div class="pref"><?php echo esc_html( $pref ); ?></div>
						<?php endif; ?>
						<h3><?php the_title(); ?></h3>
						<?php if ( '' !== $lineup ) : ?>
							<div class="sake"><?php echo esc_html( $lineup ); ?></div>
						<?php endif; ?>
					</div>
				</a>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>

	<div class="empty-state" id="emptyState">
		条件に一致する酒蔵が見つかりませんでした。検索条件を変えてお試しください。
	</div>
</section>

<?php
get_footer();
