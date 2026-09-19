<?php
/**
 * ブログ記事 / 蔵元インタビュー（参照: reference/blog-single-template.html）。
 *
 * URL: /blog/{slug}/
 *
 * 参照HTMLの `.tpl-flag`（差し替え目印の付箋）は本番では出力しない。
 * 動画枠は YouTube ID が入っていれば iframe、未入力ならプレースホルダを出す
 * （参照HTMLと同じ挙動）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$blog_id  = get_the_ID();
	$video_id = (string) torasake_field( 'youtube_video_id', $blog_id );
	$related  = torasake_field( 'related_brewery', $blog_id, null );
	$cat_name = (string) torasake_field( 'category_label', $blog_id, '蔵元インタビュー' );
	?>

	<header class="article-hero">
		<?php if ( '' !== $cat_name ) : ?>
			<span class="article-cat"><?php echo esc_html( $cat_name ); ?></span>
		<?php endif; ?>
		<h1><?php the_title(); ?></h1>
		<div class="article-meta">
			<span><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
			<?php if ( '' !== torasake_blog_brewery_line( $blog_id ) ) : ?>
				<span><?php echo esc_html( torasake_blog_brewery_line( $blog_id ) ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<main>
		<?php if ( '' !== $video_id ) : ?>
			<div class="video-embed">
				<?php // youtube-nocookie を使う。参照HTMLの埋め込み枠と比率は同じ。 ?>
				<iframe
					src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $video_id ); ?>"
					title="<?php echo esc_attr( get_the_title() ); ?>"
					loading="lazy"
					allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
					allowfullscreen></iframe>
			</div>
			<?php if ( '' !== torasake_field( 'video_caption', $blog_id ) ) : ?>
				<p class="video-caption"><?php echo esc_html( torasake_field( 'video_caption', $blog_id ) ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<div class="article-body">
			<?php the_content(); ?>

			<?php // プルクオート。本文中に置きたい場合はカスタムHTMLブロックで .pull-quote を使う。 ?>
			<?php if ( '' !== torasake_field( 'pull_quote', $blog_id ) ) : ?>
				<div class="pull-quote">
					<?php echo esc_html( torasake_field( 'pull_quote', $blog_id ) ); ?>
					<?php if ( '' !== torasake_field( 'pull_quote_who', $blog_id ) ) : ?>
						<span class="who"><?php echo esc_html( torasake_field( 'pull_quote_who', $blog_id ) ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php $related_id = torasake_first_post_id( $related ); ?>
		<?php if ( $related_id && 'publish' === get_post_status( $related_id ) ) : ?>
			<a class="related-brewery" href="<?php echo esc_url( (string) get_permalink( $related_id ) ); ?>">
				<div class="rb-logo"><?php echo torasake_brewery_logo( $related_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div>
					<div class="rb-label">この蔵の紹介ページ</div>
					<div class="rb-name"><?php echo esc_html( get_the_title( $related_id ) ); ?> →</div>
				</div>
			</a>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();
