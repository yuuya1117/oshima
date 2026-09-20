<?php
/**
 * イベント一覧。
 *
 * URL: /events/
 *
 * ★ 一覧の参照HTMLはハンドオフに含まれていない（イベント単体の
 *   event-single-template.html だけ）。お知らせ一覧（news-archive.css）の
 *   ページヒーローとカードを流用して組んである。**確定版ではない。**
 *   デザインが出たら差し替えること。
 *
 * ★ 価格は出さない。一覧は日程・会場・状態までに留め、金額は
 *   イベント詳細（event 配下）で見せる。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<header class="page-hero">
	<div class="page-hero-eyebrow">Events</div>
	<h1><?php post_type_archive_title(); ?></h1>
</header>

<main>
	<?php if ( have_posts() ) : ?>
		<ul class="news-list">
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				$event_id = get_the_ID();
				$status   = (string) torasake_field( 'event_status', $event_id );
				$venue    = (string) torasake_field( 'venue_name', $event_id );
				$date     = torasake_event_date_label( $event_id );
				// 終了は金、開催予定・開催中は赤。news-archive.css の確定済みチップ配色を使う。
				$chip     = '終了' === $status ? 'cat-report' : 'cat-info';
				?>
				<li>
					<a href="<?php the_permalink(); ?>" class="news-item">
						<div class="news-meta">
							<?php if ( '' !== $date ) : ?>
								<span class="news-date"><?php echo esc_html( $date ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $status ) : ?>
								<span class="news-tag <?php echo esc_attr( $chip ); ?>"><?php echo esc_html( $status ); ?></span>
							<?php endif; ?>
						</div>
						<div class="news-title"><?php the_title(); ?></div>
						<?php if ( '' !== $venue ) : ?>
							<div class="news-excerpt"><?php echo esc_html( $venue ); ?></div>
						<?php endif; ?>
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
		<div class="empty-state show">開催予定のイベントはまだありません。</div>
	<?php endif; ?>
</main>

<?php
get_footer();
