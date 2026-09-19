<?php
/**
 * トップ ─ アーカイブ（直近の past_event を紺地フィーチャーカードで1件）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$past_query = new WP_Query(
	array(
		'post_type'           => 'past_event',
		'posts_per_page'      => 1,
		'post_status'         => 'publish',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'meta_key'            => 'event_date',
		'orderby'             => 'meta_value',
		'order'               => 'DESC',
	)
);

if ( ! $past_query->have_posts() ) {
	return;
}

$past    = $past_query->posts[0];
$past_id = (int) $past->ID;

// 開催レポート記事があればそちらへ、無ければ過去開催ページ自身へ。
$report = torasake_field( 'report_link', $past_id, null );
$link   = ( $report instanceof WP_Post ) ? get_permalink( $report ) : get_permalink( $past_id );
?>
<section id="archive" data-screen-label="07 アーカイブ">
	<div class="inner">
		<div class="s-head rv">
			<span class="s-label">Archive</span>
			<h2 class="s-title">前回の開催</h2>
			<div class="divider"></div>
		</div>
		<div class="arch rv rv-d1">
			<div class="im">
				<?php if ( has_post_thumbnail( $past_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $past_id, 'large', array( 'loading' => 'lazy' ) ); ?>
				<?php endif; ?>
			</div>
			<div class="bd">
				<span class="date"><?php echo esc_html( torasake_past_event_dateline( $past_id ) ); ?></span>
				<h3><?php echo wp_kses( get_the_title( $past_id ), torasake_inline_html() ); ?></h3>
				<p><?php echo esc_html( get_the_excerpt( $past_id ) ); ?></p>
				<?php if ( $link ) : ?>
					<a class="lnk" href="<?php echo esc_url( (string) $link ); ?>">開催レポートを見る ›</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
