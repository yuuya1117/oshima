<?php
/**
 * トップページ（参照: reference/top-page.html ─ v6 / TORASAKE mini 告知版）。
 *
 * トップは常設のブランドサイトではなく「直近イベントのランディングページ」。
 * event_status が 開催予定 / 開催中 の event が1件あれば、その回の告知で全体を組む。
 * 無い期間は「次回準備中」フォールバックを出す。
 *
 * 実績の数値（蔵数・来場者数など）はハードコードせず event / past_event から出す。
 *
 * URL: / （現行 /index.html を維持。/index.html は inc/rewrite.php で 301）
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$event = torasake_current_event();

if ( ! $event ) {
	get_template_part( 'template-parts/front', 'fallback' );
	get_footer();
	return;
}

$event_id   = (int) $event->ID;
$ticket_url = (string) torasake_field( 'ticket_url', $event_id );
$breweries  = torasake_rows( 'breweries', $event_id );
$kv         = torasake_field( 'key_visual', $event_id, null );
$ticker     = (string) torasake_field( 'ticker_text', $event_id );
$finished   = torasake_event_finished( $event_id );
?>

<section id="hero" data-screen-label="01 Hero">
	<?php // 赤帯のティッカー。空なら出さない。 ?>
	<?php if ( '' !== $ticker ) : ?>
		<div class="ticker sans"><?php echo esc_html( $ticker ); ?></div>
	<?php endif; ?>

	<?php if ( is_array( $kv ) && ! empty( $kv['ID'] ) ) : ?>
		<?php
		// KVは全幅・トリミングなし。文言は画像に焼き込み済みなので文字は重ねない。
		// SEOのため alt に日時・会場を入れる。
		?>
		<div class="kv">
			<?php
			echo wp_get_attachment_image(
				(int) $kv['ID'],
				'full',
				false,
				array(
					'alt'             => torasake_event_kv_alt( $event_id ),
					'fetchpriority'   => 'high',
					'decoding'        => 'async',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="hero-bar">
		<?php foreach ( torasake_event_hero_items( $event_id, count( $breweries ) ) as $item ) : ?>
			<div class="hb-item">
				<span class="hb-k"><?php echo esc_html( $item['key'] ); ?></span>
				<span class="hb-v"><?php echo esc_html( $item['value'] ); ?></span>
			</div>
		<?php endforeach; ?>
		<?php if ( '' !== $ticket_url && ! $finished ) : ?>
			<a href="#tickets" class="hb-cta">チケットを見る</a>
		<?php endif; ?>
	</div>
</section>

<?php // ─── About ─── ?>
<section id="lead" data-screen-label="02 コンセプト">
	<div class="inner">
		<div class="s-head rv">
			<span class="s-label">About</span>
			<h2 class="s-title"><?php echo wp_kses( torasake_field( 'lead_heading', $event_id ), array( 'br' => array( 'class' => array() ) ) ); ?></h2>
			<div class="divider"></div>
		</div>
		<div class="narrow">
			<p class="lead-copy rv rv-d1"><?php echo wp_kses( torasake_field( 'lead_copy', $event_id ), torasake_inline_html() ); ?></p>
			<?php if ( '' !== torasake_field( 'lead_body', $event_id ) ) : ?>
				<p class="lead-body rv rv-d2"><?php echo wp_kses( torasake_field( 'lead_body', $event_id ), torasake_inline_html() ); ?></p>
			<?php endif; ?>
		</div>

		<?php $points = torasake_rows( 'lead_points', $event_id ); ?>
		<?php if ( $points ) : ?>
			<div class="lead-pts">
				<?php foreach ( array_slice( $points, 0, 3 ) as $i => $point ) : ?>
					<div class="pt rv rv-d<?php echo (int) ( $i + 1 ); ?>">
						<span class="n"><?php echo esc_html( $point['label_en'] ?? '' ); ?></span>
						<div class="big"><?php echo esc_html( $point['value'] ?? '' ); ?></div>
						<p><?php echo wp_kses( $point['body'] ?? '', torasake_inline_html() ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php // ─── 開催概要 ─── ?>
<?php $outline = torasake_rows( 'outline', $event_id ); ?>
<?php if ( $outline ) : ?>
	<section id="outline" data-screen-label="03 開催概要">
		<div class="inner">
			<div class="s-head rv">
				<span class="s-label">Event Outline</span>
				<h2 class="s-title">開催概要</h2>
				<div class="divider"></div>
			</div>
			<div class="ol-grid">
				<?php foreach ( $outline as $i => $row ) : ?>
					<div class="ol rv rv-d<?php echo (int) min( 3, intdiv( $i, 2 ) + 1 ); ?>">
						<div class="k"><?php echo esc_html( $row['key_en'] ?? '' ); ?></div>
						<div class="v">
							<?php echo wp_kses( $row['value'] ?? '', torasake_inline_html() ); ?>
							<?php if ( ! empty( $row['note'] ) ) : ?>
								<small><?php echo wp_kses( $row['note'], torasake_inline_html() ); ?></small>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( '' !== torasake_field( 'venue_map_url', $event_id ) ) : ?>
				<div class="ol-map">
					<a href="<?php echo esc_url( torasake_field( 'venue_map_url', $event_id ) ); ?>" target="_blank" rel="noopener">会場の地図を見る ›</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php // ─── 参加酒蔵 ─── ?>
<?php if ( $breweries ) : ?>
	<section id="breweries" data-screen-label="04 参加酒蔵">
		<div class="inner">
			<div class="s-head rv">
				<span class="s-label">Breweries</span>
				<h2 class="s-title">参加酒蔵 <?php echo esc_html( (string) count( $breweries ) ); ?>蔵</h2>
				<div class="divider"></div>
				<?php if ( '' !== torasake_field( 'brewery_section_sub', $event_id ) ) : ?>
					<p class="s-sub"><?php echo esc_html( torasake_field( 'brewery_section_sub', $event_id ) ); ?></p>
				<?php endif; ?>
			</div>
			<div class="brew-grid rv rv-d1" id="brew-grid">
				<?php foreach ( $breweries as $brewery ) : ?>
					<?php
					$brewery_id = $brewery instanceof WP_Post ? (int) $brewery->ID : (int) $brewery;
					// 個別ページが公開されていない蔵はリンクなしのタイルにする。
					$has_page   = 'publish' === get_post_status( $brewery_id );
					$tag        = $has_page ? 'a' : 'div';
					?>
					<<?php echo esc_attr( $tag ); ?> class="brew<?php echo $has_page ? '' : ' brew-static'; ?>"<?php echo $has_page ? ' href="' . esc_url( (string) get_permalink( $brewery_id ) ) . '"' : ''; ?>>
						<div class="im"><?php echo torasake_brewery_logo( $brewery_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="nm">
							<?php echo esc_html( torasake_field( 'brand_name', $brewery_id, get_the_title( $brewery_id ) ) ); ?>
							<span class="pref"><?php echo esc_html( torasake_brewery_origin( $brewery_id ) ); ?></span>
						</div>
					</<?php echo esc_attr( $tag ); ?>>
				<?php endforeach; ?>
			</div>
			<div class="center rv rv-d2" style="margin-top:2.6rem">
				<a href="<?php echo esc_url( home_url( '/breweries/' ) ); ?>" class="btn btn-outline">これまでの参加酒蔵を見る</a>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php // ─── 料理 / 会場 ─── ?>
<?php
$food_image = torasake_field( 'food_image', $event_id, null );
$food_head  = (string) torasake_field( 'food_heading', $event_id );
?>
<?php if ( '' !== $food_head ) : ?>
	<section id="food" data-screen-label="05 料理">
		<div class="inner">
			<div class="food rv">
				<div class="im">
					<?php if ( is_array( $food_image ) && ! empty( $food_image['ID'] ) ) : ?>
						<?php
						echo wp_get_attachment_image(
							(int) $food_image['ID'],
							'large',
							false,
							array(
								'alt'     => ( $food_image['alt'] ?? '' ) ?: (string) torasake_field( 'venue_name', $event_id ),
								'loading' => 'lazy',
								'style'   => 'object-position:' . esc_attr( torasake_field( 'food_image_position', $event_id, '50% 50%' ) ),
							)
						);
						?>
					<?php endif; ?>
				</div>
				<div class="bd">
					<span class="s-label" style="margin-bottom:0">Food</span>
					<h3><?php echo wp_kses( $food_head, torasake_inline_html() ); ?></h3>
					<p><?php echo wp_kses( torasake_field( 'food_body', $event_id ), torasake_inline_html() ); ?></p>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php // ─── チケット ─── ?>
<?php // ★ 価格を表示してよいのはこのセクションと event 配下だけ（CLAUDE.md）。 ?>
<?php $tickets = torasake_rows( 'tickets', $event_id ); ?>
<?php if ( $tickets && ! $finished ) : ?>
	<section id="tickets" data-screen-label="06 チケット">
		<div class="inner">
			<div class="s-head rv">
				<span class="s-label">Tickets</span>
				<h2 class="s-title"><?php echo esc_html( torasake_field( 'ticket_section_title', $event_id, 'チケット販売中' ) ); ?></h2>
				<div class="divider"></div>
				<?php if ( '' !== torasake_field( 'ticket_section_sub', $event_id ) ) : ?>
					<p class="s-sub"><?php echo esc_html( torasake_field( 'ticket_section_sub', $event_id ) ); ?></p>
				<?php endif; ?>
			</div>

			<div class="count rv rv-d1">
				<span class="lb">開催まで</span>
				<span class="nums" id="countdown">--</span>
			</div>

			<div class="tk-grid">
				<?php foreach ( $tickets as $i => $ticket ) : ?>
					<?php $featured = ! empty( $ticket['is_featured'] ); ?>
					<div class="tk<?php echo $featured ? ' feat' : ''; ?> rv rv-d<?php echo (int) min( 3, $i + 1 ); ?>">
						<?php if ( ! empty( $ticket['limit_label'] ) ) : ?>
							<span class="flag"<?php echo $featured ? '' : ' style="background:var(--gold)"'; ?>><?php echo esc_html( $ticket['limit_label'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $ticket['label_en'] ) ) : ?>
							<span class="lim"><?php echo esc_html( $ticket['label_en'] ); ?></span>
						<?php endif; ?>
						<h3><?php echo esc_html( $ticket['name'] ?? '' ); ?></h3>
						<div class="price"><?php echo esc_html( number_format_i18n( (float) ( $ticket['price'] ?? 0 ) ) ); ?><span>円</span></div>
						<?php if ( ! empty( $ticket['entry_time'] ) ) : ?>
							<div class="enter"><?php echo esc_html( $ticket['entry_time'] ); ?> 入場</div>
						<?php endif; ?>
						<?php if ( ! empty( $ticket['note'] ) ) : ?>
							<p class="note"><?php echo esc_html( $ticket['note'] ); ?></p>
						<?php endif; ?>
						<?php $purchase = $ticket['purchase_url'] ?? $ticket_url; ?>
						<?php if ( '' !== $purchase ) : ?>
							<?php // Peatix の ?utm_source= を落とさない（URL移行マップ 6章-7）。 ?>
							<a href="<?php echo esc_url( $purchase ); ?>" target="_blank" rel="noopener" class="btn <?php echo $featured ? 'btn-primary' : 'btn-outline'; ?>">Peatixで購入</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php $includes = torasake_rows( 'ticket_includes', $event_id ); ?>
			<?php if ( $includes ) : ?>
				<div class="incl rv rv-d2">
					<div class="ttl">全券共通</div>
					<ul>
						<?php foreach ( $includes as $include ) : ?>
							<li>
								<?php echo esc_html( $include['text'] ?? '' ); ?>
								<?php if ( ! empty( $include['note'] ) ) : ?>
									<small><?php echo esc_html( $include['note'] ); ?></small>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( '' !== torasake_field( 'ticket_notes', $event_id ) ) : ?>
				<p class="fine rv"><?php echo wp_kses( nl2br( torasake_field( 'ticket_notes', $event_id ) ), torasake_inline_html() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php // ─── アーカイブ（直近の past_event 1件）─── ?>
<?php get_template_part( 'template-parts/front', 'archive' ); ?>

<?php // ─── News（最新3件）─── ?>
<?php get_template_part( 'template-parts/front', 'news' ); ?>

<?php
get_footer();
