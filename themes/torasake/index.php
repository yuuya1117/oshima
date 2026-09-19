<?php
/**
 * フォールバック。
 *
 * テンプレート階層でどれにも当たらなかったときに使われる。
 * 実運用では front-page / home / single / single-brewery / archive-brewery が
 * すべてのURLを受けるので、ここに来るのは検索結果と 404 くらい。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="page-hero">
	<h1>
		<?php if ( is_search() ) : ?>
			<?php echo esc_html( sprintf( '「%s」の検索結果', get_search_query() ) ); ?>
		<?php elseif ( is_404() ) : ?>
			ページが見つかりません
		<?php else : ?>
			<?php the_archive_title(); ?>
		<?php endif; ?>
	</h1>
</header>

<main>
	<?php if ( have_posts() ) : ?>
		<ul class="news-list">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<li>
					<a href="<?php the_permalink(); ?>" class="news-item">
						<div class="news-meta">
							<span class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
						</div>
						<div class="news-title"><?php the_title(); ?></div>
						<div class="news-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></div>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<div class="empty-state show">
			<p>お探しのページは見つかりませんでした。</p>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">トップへ戻る</a></p>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
