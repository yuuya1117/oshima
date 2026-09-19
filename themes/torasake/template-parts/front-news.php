<?php
/**
 * トップ ─ News（最新3件）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$news_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post_status'         => 'publish',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $news_query->have_posts() ) {
	return;
}
?>
<section id="news" data-screen-label="08 News" style="background:var(--bg2)">
	<div class="inner">
		<div class="s-head rv">
			<span class="s-label">News</span>
			<h2 class="s-title">お知らせ</h2>
			<div class="divider"></div>
		</div>
		<div class="news-grid">
			<?php $i = 0; ?>
			<?php while ( $news_query->have_posts() ) : ?>
				<?php
				$news_query->the_post();
				++$i;
				$cats = get_the_category();
				?>
				<a class="news-card rv rv-d<?php echo (int) $i; ?>" href="<?php the_permalink(); ?>">
					<div class="im">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'torasake_card', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
					</div>
					<?php if ( $cats ) : ?>
						<span class="cat"><?php echo esc_html( $cats[0]->name ); ?></span>
					<?php endif; ?>
					<h4><?php the_title(); ?></h4>
					<span class="dt"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
				</a>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<div class="center rv" style="margin-top:2.6rem">
			<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="btn btn-outline">お知らせ一覧を見る</a>
		</div>
	</div>
</section>
