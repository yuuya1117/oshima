<?php
/**
 * イベント詳細（参照: reference/event-single-template.html）。
 *
 * URL: /events/{slug}.html  （参照HTMLの canonical と同形）
 *
 * 参照HTMLは中身が ◯◯ のプレースホルダ版。構造だけを受け継ぎ、値は全て ACF から入れる。
 * 参照にある `.tpl-flag`（「テンプレート — 内容を差し替えてください」の付箋）は
 * 差し替え前提の目印なので本番では出力しない。CSS側は逐語移植のため定義だけ残っている。
 *
 * ★ 価格を表示してよいのは event 配下とトップのチケットセクションだけ（CLAUDE.md）。
 *   このページはその「event 配下」にあたるので、チケット金額を出してよい。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$event_id   = get_the_ID();
	$ticket_url = (string) torasake_field( 'ticket_url', $event_id );
	$finished   = torasake_event_finished( $event_id );
	$breweries  = torasake_rows( 'breweries', $event_id );
	$edition    = torasake_event_edition_label( $event_id );
	?>

	<section id="hero" data-screen-label="01 Hero">
		<div class="hero-bg"></div>
		<span class="hero-eyebrow">Sake Tasting Series</span>

		<?php if ( '' !== $edition ) : ?>
			<span class="pill pill-mini"><?php echo esc_html( $edition ); ?></span>
		<?php endif; ?>

		<h1><?php the_title(); ?></h1>

		<?php if ( '' !== get_the_excerpt() ) : ?>
			<p class="lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<div class="hero-meta-row">
			<?php foreach ( torasake_event_meta_row( $event_id ) as $meta ) : ?>
				<span><?php echo esc_html( $meta['key'] ); ?> <b><?php echo esc_html( $meta['value'] ); ?></b></span>
			<?php endforeach; ?>
		</div>

		<div class="hero-cta-group">
			<?php if ( '' !== $ticket_url && ! $finished ) : ?>
				<?php // Peatix の ?utm_source= を落とさない（URL移行マップ 6章-7）。 ?>
				<a href="<?php echo esc_url( $ticket_url ); ?>" target="_blank" rel="noopener" class="btn-primary">チケット購入</a>
			<?php endif; ?>
			<?php if ( $breweries ) : ?>
				<a href="#breweries" class="btn-outline">参加酒蔵を見る</a>
			<?php endif; ?>
		</div>
	</section>

	<?php // ─── テーマ ─── ?>
	<?php if ( '' !== torasake_field( 'theme_body', $event_id ) ) : ?>
		<section id="theme" data-screen-label="02 テーマ">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">Theme</span>
					<h2 class="section-title">今回のテーマ</h2>
					<div class="divider"></div>
				</div>
				<?php echo wp_kses_post( torasake_field( 'theme_body', $event_id ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php // ─── 会場 ─── ?>
	<?php $venue_name = (string) torasake_field( 'venue_name', $event_id ); ?>
	<?php if ( '' !== $venue_name ) : ?>
		<?php $venue_image = torasake_field( 'venue_image', $event_id, null ); ?>
		<section id="venue" data-screen-label="03 会場" style="background: var(--bg2);">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">Venue</span>
					<h2 class="section-title">会場情報</h2>
					<div class="divider"></div>
				</div>
				<div class="venue-card">
					<div class="img">
						<?php if ( is_array( $venue_image ) && ! empty( $venue_image['ID'] ) ) : ?>
							<?php
							echo wp_get_attachment_image(
								(int) $venue_image['ID'],
								'large',
								false,
								array( 'alt' => $venue_name, 'loading' => 'lazy' )
							);
							?>
						<?php else : ?>
							会場写真
						<?php endif; ?>
					</div>
					<div class="vb">
						<h3><?php echo esc_html( $venue_name ); ?></h3>
						<div class="mono-list">
							<?php foreach ( torasake_event_venue_lines( $event_id ) as $line ) : ?>
								<?php echo esc_html( $line ); ?><br>
							<?php endforeach; ?>
						</div>
						<?php if ( '' !== torasake_field( 'venue_map_url', $event_id ) ) : ?>
							<a href="<?php echo esc_url( torasake_field( 'venue_map_url', $event_id ) ); ?>" target="_blank" rel="noopener">地図を見る ↗</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php // ─── 参加酒蔵 ─── ?>
	<?php if ( $breweries ) : ?>
		<section id="breweries" data-screen-label="04 参加酒蔵">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">Breweries</span>
					<h2 class="section-title">参加酒蔵</h2>
					<div class="divider"></div>
					<?php if ( '' !== torasake_field( 'brewery_section_sub', $event_id ) ) : ?>
						<p class="section-subtitle"><?php echo esc_html( torasake_field( 'brewery_section_sub', $event_id ) ); ?></p>
					<?php endif; ?>
				</div>
				<div class="brew-grid">
					<?php foreach ( $breweries as $brewery ) : ?>
						<?php
						$brewery_id = $brewery instanceof WP_Post ? (int) $brewery->ID : (int) $brewery;
						$has_page   = 'publish' === get_post_status( $brewery_id );
						$tag        = $has_page ? 'a' : 'div';
						?>
						<<?php echo esc_attr( $tag ); ?> class="brew-card"<?php echo $has_page ? ' href="' . esc_url( (string) get_permalink( $brewery_id ) ) . '"' : ''; ?>>
							<div class="img"><?php echo torasake_brewery_logo( $brewery_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<div class="nm"><?php echo esc_html( get_the_title( $brewery_id ) ); ?></div>
						</<?php echo esc_attr( $tag ); ?>>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php // ─── 参加方法 ─── ?>
	<?php $steps = torasake_rows( 'howto_steps', $event_id ); ?>
	<?php if ( $steps ) : ?>
		<section id="howto" data-screen-label="05 参加方法" style="background: var(--bg2);">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">How to Enjoy</span>
					<h2 class="section-title">参加方法</h2>
					<div class="divider"></div>
				</div>
				<div class="howto-steps">
					<?php foreach ( $steps as $i => $step ) : ?>
						<div class="howto-step">
							<div class="howto-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></div>
							<div>
								<h4><?php echo esc_html( $step['title'] ?? '' ); ?></h4>
								<p><?php echo esc_html( $step['body'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php // ─── スポンサー（ティア別）─── ?>
	<?php $tiers = torasake_event_sponsors_by_tier( $event_id ); ?>
	<?php if ( $tiers ) : ?>
		<section id="sponsors" data-screen-label="06 スポンサー">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">Sponsors</span>
					<h2 class="section-title">協賛企業</h2>
					<div class="divider"></div>
					<?php if ( '' !== torasake_field( 'sponsor_section_sub', $event_id ) ) : ?>
						<p class="section-subtitle"><?php echo esc_html( torasake_field( 'sponsor_section_sub', $event_id ) ); ?></p>
					<?php endif; ?>
				</div>
				<?php foreach ( $tiers as $tier => $sponsors ) : ?>
					<div class="sponsor-tier">
						<span class="sponsor-tier-label"><?php echo esc_html( 'title' === $tier ? 'Title Sponsor' : 'Supporters' ); ?></span>
						<div class="<?php echo 'title' === $tier ? 'sponsor-grid-title' : 'sponsor-grid-supporters'; ?>">
							<?php foreach ( $sponsors as $sponsor_id ) : ?>
								<?php get_template_part( 'template-parts/sponsor', 'tile', array( 'id' => $sponsor_id, 'tier' => $tier ) ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php // ─── チケット ─── ?>
	<?php // ★ event 配下なので価格を出してよい（CLAUDE.md）。 ?>
	<?php $ticket_price = torasake_field( 'ticket_price', $event_id ); ?>
	<?php if ( ( '' !== $ticket_price || '' !== $ticket_url ) && ! $finished ) : ?>
		<section id="ticket" data-screen-label="07 チケット">
			<div class="section-inner">
				<div class="section-header">
					<span class="section-label">Ticket</span>
					<h2 class="section-title">チケット</h2>
					<div class="divider"></div>
				</div>
				<div class="ticket-box">
					<?php if ( '' !== $ticket_price ) : ?>
						<div class="ticket-amount">¥<?php echo esc_html( number_format_i18n( (float) $ticket_price ) ); ?></div>
					<?php endif; ?>
					<?php if ( '' !== torasake_field( 'ticket_note', $event_id ) ) : ?>
						<p class="ticket-note"><?php echo wp_kses( nl2br( torasake_field( 'ticket_note', $event_id ) ), torasake_inline_html() ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $ticket_url ) : ?>
						<div style="margin-top:1.4rem;">
							<a href="<?php echo esc_url( $ticket_url ); ?>" target="_blank" rel="noopener" class="btn-primary">チケットを購入する</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
