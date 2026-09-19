<?php
/**
 * お知らせ一覧（参照: reference/news-archive.html）。
 *
 * URL: /news/   ← 現行URLを維持（/news/index.html は 301）
 *
 * /news/ は「投稿ページ」として設定される（inc/rewrite.php の有効化処理）。
 * カテゴリチップは参照HTMLどおりクライアント側で絞り込む。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

// フィルタチップ。参照HTMLの すべて/開催報告/お知らせ/イベントガイド に対応。
$chips = array_filter(
	array_map(
		static function ( string $slug ): ?WP_Term {
			$term = get_term_by( 'slug', $slug, 'category' );
			return $term instanceof WP_Term ? $term : null;
		},
		array( 'report', 'info', 'guide' )
	)
);
?>

<header class="page-hero">
	<div class="page-hero-eyebrow">News &amp; Updates</div>
	<h1><?php echo esc_html( torasake_posts_page_title() ); ?></h1>
</header>

<?php if ( $chips ) : ?>
	<div class="filter-bar" id="filterBar">
		<button class="filter-chip active" type="button" data-cat="">すべて</button>
		<?php foreach ( $chips as $chip ) : ?>
			<button class="filter-chip" type="button" data-cat="<?php echo esc_attr( $chip->slug ); ?>"><?php echo esc_html( $chip->name ); ?></button>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<main>
	<?php if ( have_posts() ) : ?>
		<ul class="news-list" id="newsList">
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				$slug = torasake_news_cat_slug( get_the_ID() );
				$cats = get_the_category();
				?>
				<li data-cat="<?php echo esc_attr( $slug ); ?>">
					<a href="<?php the_permalink(); ?>" class="news-item">
						<div class="news-meta">
							<span class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
							<?php if ( $cats ) : ?>
								<span class="news-tag <?php echo esc_attr( torasake_news_tag_class( $slug ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></span>
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
		<div class="empty-state" id="emptyState">このカテゴリの記事はまだありません。</div>

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
		<div class="empty-state show">お知らせはまだありません。</div>
	<?php endif; ?>
</main>

<?php
get_footer();
