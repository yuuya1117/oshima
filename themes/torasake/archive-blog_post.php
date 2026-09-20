<?php
/**
 * ブログ一覧 / 蔵元インタビュー。
 *
 * URL: /blog/
 *
 * ★ 一覧の参照HTMLはハンドオフに含まれていない（記事単体の
 *   blog-single-template.html だけ）。ゼロから作らず、確定済みの組み方を
 *   組み合わせてある（詳細は assets/css/blog-archive.css の冒頭）。
 *     カード構造・hoverズーム … top-page.html の .news-card
 *     金のカテゴリバッジ       … blog-single-template.html の .article-cat
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

<main class="blog-main">
	<?php if ( have_posts() ) : ?>
		<div class="blog-grid">
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				$blog_id  = get_the_ID();
				$video_id = (string) torasake_field( 'youtube_video_id', $blog_id );
				$brewery  = torasake_blog_brewery_line( $blog_id );
				?>
				<a class="blog-card" href="<?php the_permalink(); ?>">
					<div class="im">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'torasake_card', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						<?php elseif ( '' !== $video_id ) : ?>
							<?php // アイキャッチ未設定でも動画があればYouTubeのサムネイルを使う。 ?>
							<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg' ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<?php if ( '' !== $video_id ) : ?>
							<span class="play" aria-hidden="true"></span>
						<?php endif; ?>
					</div>

					<?php if ( '' !== torasake_field( 'category_label', $blog_id ) ) : ?>
						<span class="cat"><?php echo esc_html( torasake_field( 'category_label', $blog_id ) ); ?></span>
					<?php endif; ?>

					<h3><?php the_title(); ?></h3>

					<?php if ( '' !== get_the_excerpt() ) : ?>
						<p class="excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 60, '…' ) ); ?></p>
					<?php endif; ?>

					<div class="meta">
						<span><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
						<?php if ( '' !== $brewery ) : ?>
							<span class="brewery"><?php echo esc_html( $brewery ); ?></span>
						<?php endif; ?>
					</div>
				</a>
			<?php endwhile; ?>
		</div>

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
