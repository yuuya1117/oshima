<?php
/**
 * イベント一覧。
 *
 * URL: /events/
 *
 * ★ 一覧の参照HTMLはハンドオフに含まれていない（イベント単体の
 *   event-single-template.html だけ）。ゼロから作らず、確定済みの組み方を
 *   組み合わせてある（詳細は assets/css/event-archive.css の冒頭）。
 *     開催予定 … top-page.html の .arch（紺のフィーチャーカード）
 *     終了分   … brewery-style.css の .eh-row（イベント履歴の行）
 *
 * ★ 価格は出さない。金額はイベント詳細（event 配下）で見せる。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

// 開催予定・開催中を上に、終了を下に分ける。
$upcoming = array();
$finished = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$id = get_the_ID();
		if ( torasake_event_finished( $id ) ) {
			$finished[] = $id;
		} else {
			$upcoming[] = $id;
		}
	}
	rewind_posts();
}
?>

<header class="page-hero">
	<div class="page-hero-eyebrow">Events</div>
	<h1><?php post_type_archive_title(); ?></h1>
</header>

<main class="event-main">
	<?php if ( ! $upcoming && ! $finished ) : ?>
		<div class="empty-state show">イベントはまだありません。</div>
	<?php endif; ?>

	<?php // ── 開催予定・開催中 ── ?>
	<?php foreach ( $upcoming as $event_id ) : ?>
		<a class="ev-feature" href="<?php echo esc_url( (string) get_permalink( $event_id ) ); ?>">
			<div class="im">
				<?php if ( has_post_thumbnail( $event_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $event_id, 'large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<?php $kv = torasake_field( 'key_visual', $event_id, null ); ?>
					<?php if ( is_array( $kv ) && ! empty( $kv['ID'] ) ) : ?>
						<?php echo wp_get_attachment_image( (int) $kv['ID'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<div class="bd">
				<?php if ( '' !== torasake_field( 'event_status', $event_id ) ) : ?>
					<span class="status"><?php echo esc_html( torasake_field( 'event_status', $event_id ) ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== torasake_event_dateline( $event_id ) ) : ?>
					<span class="date"><?php echo esc_html( torasake_event_dateline( $event_id ) ); ?></span>
				<?php endif; ?>
				<h2><?php echo esc_html( get_the_title( $event_id ) ); ?></h2>
				<?php if ( '' !== torasake_event_venue_line( $event_id ) ) : ?>
					<p class="venue"><?php echo esc_html( torasake_event_venue_line( $event_id ) ); ?></p>
				<?php endif; ?>
				<span class="lnk">詳細を見る ›</span>
			</div>
		</a>
	<?php endforeach; ?>

	<?php // ── 終了した回 ── ?>
	<?php if ( $finished ) : ?>
		<?php if ( $upcoming ) : ?>
			<span class="ev-label">Archive</span>
		<?php endif; ?>
		<ul class="ev-list">
			<?php foreach ( $finished as $event_id ) : ?>
				<li>
					<a class="ev-row" href="<?php echo esc_url( (string) get_permalink( $event_id ) ); ?>">
						<span class="status">終了</span>
						<div class="bd">
							<div class="nm"><?php echo esc_html( get_the_title( $event_id ) ); ?></div>
							<div class="meta"><?php echo esc_html( torasake_event_rowline( $event_id ) ); ?></div>
						</div>
						<span class="arrow">→</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

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
</main>

<?php
get_footer();
