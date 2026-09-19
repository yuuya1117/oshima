<?php
/**
 * ブログ一覧。
 *
 * URL: /blog/
 *
 * ★ 一覧の参照HTMLはハンドオフに含まれていない（記事単体の
 *   blog-single-template.html だけ）。そのため記事ヒーローの配色・
 *   タイポと、お知らせ一覧のカードの組み方を流用して構成した。
 *   確定版ではないので、デザインが出たら差し替えること。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<header class="page-hero">
	<div class="page-hero-eyebrow">Blog</div>
	<h1><?php post_type_archive_title(); ?></h1>
</header>

<main>
	<?php if ( have_posts() ) : ?>
		<ul class="news-list">
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				$blog_id = get_the_ID();
				?>
				<li>
					<a href="<?php the_permalink(); ?>" class="news-item">
						<div class="news-meta">
							<span class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
							<?php if ( '' !== torasake_blog_brewery_line( $blog_id ) ) : ?>
								<span class="news-tag cat-report"><?php echo esc_html( torasake_blog_brewery_line( $blog_id ) ); ?></span>
							<?php endif; ?>
						</div>
						<div class="news-title"><?php the_title(); ?></div>
						<?php if ( '' !== get_the_excerpt() ) : ?>
							<div class="news-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></div>
						<?php endif; ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '←',
				'next_text' => '→',
				'class'     => 'news-pagination',
			)
		);
		?>
	<?php else : ?>
		<div class="empty-state show">記事はまだありません。</div>
	<?php endif; ?>
</main>

<?php
get_footer();
