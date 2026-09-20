<?php
/**
 * 過去開催レポート（参照: reference/past-event-single.html）。
 *
 * URL: /past/{slug}/
 *
 * 現行の /past/ は前身イベント「日本酒虎の巻 Vol.2」のレポート1本。
 * 移行マップ 2章の推奨に従い、6/27 の LP もここへ保存していく想定。
 *
 * 統計カード（来場者数・出展蔵数）はハードコードせず stats リピーターから出す（CLAUDE.md #4）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$past_id = get_the_ID();
	$stats   = torasake_rows( 'stats', $past_id );
	$info    = torasake_rows( 'info_list', $past_id );
	$gallery = torasake_rows( 'gallery', $past_id );
	?>

	<header class="page-hero">
		<div class="page-hero-eyebrow">PAST EVENT REPORT</div>
		<h1><?php the_title(); ?></h1>
		<?php if ( '' !== torasake_event_date_label( $past_id ) ) : ?>
			<p class="page-hero-sub"><?php echo esc_html( torasake_event_date_label( $past_id ) ); ?> 開催</p>
		<?php endif; ?>
	</header>

	<main>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="lead-photo">
				<?php the_post_thumbnail( 'full' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stats ) : ?>
			<div class="stats">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="stat">
						<div class="stat-num">
							<?php echo esc_html( $stat['number'] ?? '' ); ?>
							<?php if ( ! empty( $stat['unit_suffix'] ) ) : ?>
								<small><?php echo esc_html( $stat['unit_suffix'] ); ?></small>
							<?php endif; ?>
						</div>
						<div class="stat-label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $info ) : ?>
			<h2 class="section-h">開催概要</h2>
			<dl class="info-list">
				<?php foreach ( $info as $row ) : ?>
					<?php if ( empty( $row['key_en'] ) ) { continue; } ?>
					<dt><?php echo esc_html( $row['key_en'] ); ?></dt>
					<dd>
						<?php if ( ! empty( $row['url'] ) ) : ?>
							<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $row['value'] ?? '' ); ?></a>
						<?php else : ?>
							<?php echo wp_kses( $row['value'] ?? '', torasake_inline_html() ); ?>
						<?php endif; ?>
					</dd>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>

		<?php if ( '' !== torasake_field( 'report_body', $past_id ) ) : ?>
			<h2 class="section-h"><?php echo esc_html( torasake_field( 'report_heading', $past_id, '当日の様子' ) ); ?></h2>
			<?php echo wp_kses_post( torasake_field( 'report_body', $past_id ) ); ?>
		<?php endif; ?>

		<?php if ( $gallery ) : ?>
			<?php // 1枚目だけ featured（2×2）にする。参照HTMLと同じ組み方。 ?>
			<div class="gallery" id="gallery">
				<?php foreach ( $gallery as $i => $photo ) : ?>
					<?php if ( empty( $photo['ID'] ) ) { continue; } ?>
					<div class="gallery-item<?php echo 0 === $i ? ' featured' : ''; ?>">
						<?php
						echo wp_get_attachment_image(
							(int) $photo['ID'],
							0 === $i ? 'large' : 'medium_large',
							false,
							array(
								'alt'       => ( $photo['alt'] ?? '' ) ?: get_the_title( $past_id ),
								'loading'   => 'lazy',
								'data-full' => wp_get_attachment_image_url( (int) $photo['ID'], 'full' ),
							)
						);
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( '' !== torasake_field( 'closing_body', $past_id ) ) : ?>
			<h2 class="section-h"><?php echo esc_html( torasake_field( 'closing_heading', $past_id ) ); ?></h2>
			<?php echo wp_kses_post( torasake_field( 'closing_body', $past_id ) ); ?>
		<?php endif; ?>

		<?php // 次回開催のCTA。告知中のイベントがあるときだけ出す。 ?>
		<?php $next_event = torasake_current_event(); ?>
		<?php if ( $next_event ) : ?>
			<?php
			$next_id     = (int) $next_event->ID;
			$next_ticket = (string) torasake_field( 'ticket_url', $next_id );
			$next_line   = array_filter(
				array(
					torasake_event_date_label( $next_id ),
					(string) torasake_field( 'venue_name', $next_id ),
					(string) torasake_field( 'hero_sake_label', $next_id ),
				)
			);
			?>
			<div class="cta-wrap">
				<h3><?php echo esc_html( get_the_title( $next_id ) ); ?> 開催決定</h3>
				<?php if ( $next_line ) : ?>
					<p><?php echo esc_html( implode( '｜', $next_line ) ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $next_ticket ) : ?>
					<a href="<?php echo esc_url( $next_ticket ); ?>" target="_blank" rel="noopener" class="cta-btn">チケットを購入</a>
				<?php else : ?>
					<a href="<?php echo esc_url( (string) get_permalink( $next_id ) ); ?>" class="cta-btn">詳細を見る</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</main>

	<?php if ( $gallery ) : ?>
		<div class="lightbox" id="lightbox">
			<button class="lightbox-close" aria-label="閉じる">×</button>
			<img id="lightbox-img" alt="">
		</div>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
