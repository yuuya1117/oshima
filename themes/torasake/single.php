<?php
/**
 * お知らせ詳細（参照: reference/news-single-example.html）。
 *
 * URL: /news/{slug}.html  ← 現行URLをそのまま維持（方式A）
 *      スラッグは現行ファイル名と完全一致させること。
 *      日本語スラッグ2本（2026-06-27-当日券 / 2026-05-21-開催決定）は
 *      管理画面のスラッグ欄に生の日本語で入力する（パーセントエンコードして貼らない）。
 *
 * 本文はブロックエディタ。参照HTMLの thanks-box / ig-link-card / photo-gallery は
 * assets/css/news-single.css に残してあるので、カスタムHTMLブロックでそのまま使える。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$slug = torasake_news_cat_slug( get_the_ID() );
	$cats = get_the_category();
	?>

	<header class="page-hero">
		<div class="page-hero-eyebrow"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></div>
		<h1><?php the_title(); ?></h1>
		<div class="page-hero-meta">
			<span class="hero-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
			<?php if ( $cats ) : ?>
				<span class="hero-tag"><?php echo esc_html( $cats[0]->name ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<main>
		<article class="article-body">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article-lead">
					<?php the_post_thumbnail( 'full' ); ?>
					<?php if ( '' !== get_the_post_thumbnail_caption() ) : ?>
						<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<?php the_content(); ?>
		</article>

		<a class="back-link" href="<?php echo esc_url( home_url( '/news/' ) ); ?>">← お知らせ一覧へ戻る</a>
	</main>

	<?php
endwhile;

get_footer();
