<?php
/**
 * 酒蔵詳細（参照: reference/brewery-single.html / brewery-single-mini.html）。
 *
 * URL: /breweries/{slug}.html  （参照HTMLと同形。inc/rewrite.php で定義）
 *
 * 出展銘柄が決まっている蔵は3カラムの sake-lineup、
 * 未定の蔵（mini 出展蔵など）は代表銘柄だけの sake-card にフォールバックする。
 *
 * ★ 価格は一切表示しない（CLAUDE.md）。銘柄カードにも基本情報テーブルにも出さない。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$brewery_id = get_the_ID();
	$pref       = torasake_brewery_pref( $brewery_id );
	$logo       = torasake_field( 'logo', $brewery_id, null );
	$lineup     = torasake_rows( 'sake_lineup', $brewery_id );
	?>

	<header class="hero">
		<div class="hero-inner">
			<div class="hero-meta">
				<?php if ( '' !== torasake_brewery_region_line( $brewery_id ) ) : ?>
					<span class="brewery-region"><?php echo esc_html( torasake_brewery_region_line( $brewery_id ) ); ?></span>
				<?php endif; ?>

				<h1 class="brewery-name">
					<?php the_title(); ?>
					<?php if ( '' !== torasake_brewery_name_en( $brewery_id ) ) : ?>
						<span class="brewery-name-en"><?php echo esc_html( torasake_brewery_name_en( $brewery_id ) ); ?></span>
					<?php endif; ?>
				</h1>

				<?php if ( '' !== torasake_field( 'tagline', $brewery_id ) ) : ?>
					<p class="brewery-tagline"><?php echo wp_kses( torasake_field( 'tagline', $brewery_id ), torasake_inline_html() ); ?></p>
				<?php endif; ?>
			</div>

			<div class="brewery-logo-wrap">
				<?php echo torasake_brewery_logo( $brewery_id, 'torasake_logo', 'maruisi-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</header>

	<?php if ( '' !== $pref ) : ?>
		<div class="pref-link-wrap">
			<a class="pref-link" href="<?php echo esc_url( add_query_arg( 'pref', rawurlencode( $pref ), home_url( '/breweries/' ) ) ); ?>">
				<?php echo esc_html( $pref ); ?>の他の酒蔵を見る →
			</a>
		</div>
	<?php endif; ?>

	<section class="content">
		<div class="content-inner">
			<?php if ( '' !== torasake_field( 'lineup_section_label', $brewery_id ) ) : ?>
				<span class="section-label"><?php echo esc_html( torasake_field( 'lineup_section_label', $brewery_id ) ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== torasake_field( 'lineup_section_title', $brewery_id ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( torasake_field( 'lineup_section_title', $brewery_id ) ); ?></h2>
			<?php endif; ?>

			<?php if ( $lineup ) : ?>
				<?php // 出展銘柄が確定している蔵。 ?>
				<div class="sake-lineup">
					<?php foreach ( $lineup as $sake ) : ?>
						<?php $hue = isset( $sake['accent_hue'] ) && '' !== $sake['accent_hue'] ? (float) $sake['accent_hue'] : null; ?>
						<div class="sake-lineup-card"<?php echo null === $hue ? '' : ' style="' . esc_attr( torasake_accent_style( $hue ) ) . '"'; ?>>
							<?php if ( ! empty( $sake['number_label'] ) ) : ?>
								<p class="sl-num"><?php echo esc_html( $sake['number_label'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $sake['bottle_image']['ID'] ) ) : ?>
								<div class="sl-bottle">
									<?php
									echo wp_get_attachment_image(
										(int) $sake['bottle_image']['ID'],
										'large',
										false,
										array(
											'alt'     => ( $sake['name_ja'] ?? '' ) . ' ボトル',
											'loading' => 'lazy',
										)
									);
									?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $sake['type_label'] ) ) : ?>
								<span class="sl-type"><?php echo esc_html( $sake['type_label'] ); ?></span>
							<?php endif; ?>

							<p class="sl-name"><?php echo esc_html( $sake['name_ja'] ?? '' ); ?></p>

							<?php if ( ! empty( $sake['name_en'] ) ) : ?>
								<p class="sl-name-en"><?php echo esc_html( $sake['name_en'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $sake['description'] ) ) : ?>
								<p class="sl-desc"><?php echo esc_html( $sake['description'] ); ?></p>
							<?php endif; ?>

							<?php $tags = isset( $sake['tags'] ) && is_array( $sake['tags'] ) ? $sake['tags'] : array(); ?>
							<?php if ( $tags ) : ?>
								<ul class="sl-notes">
									<?php foreach ( $tags as $tag ) : ?>
										<li><?php echo esc_html( is_array( $tag ) ? ( $tag['tag'] ?? '' ) : $tag ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( '' !== torasake_field( 'lineup_badge', $brewery_id ) ) : ?>
					<div style="text-align:center;">
						<span class="sl-badge"><?php echo esc_html( torasake_field( 'lineup_badge', $brewery_id ) ); ?></span>
					</div>
				<?php endif; ?>

			<?php elseif ( '' !== torasake_field( 'flagship_name', $brewery_id ) ) : ?>
				<?php // 出展銘柄が未定の蔵。代表銘柄だけを1枚のカードで見せる。 ?>
				<div class="sake-card">
					<div>
						<p class="sake-info-label">代表銘柄</p>
						<p class="sake-name"><?php echo esc_html( torasake_field( 'flagship_name', $brewery_id ) ); ?></p>
						<?php if ( '' !== torasake_field( 'flagship_description', $brewery_id ) ) : ?>
							<p class="sake-desc"><?php echo esc_html( torasake_field( 'flagship_description', $brewery_id ) ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php // 蔵の物語 ?>
			<?php if ( '' !== torasake_field( 'story_body', $brewery_id ) ) : ?>
				<div class="brewery-story">
					<?php if ( '' !== torasake_field( 'story_heading', $brewery_id ) ) : ?>
						<h3><?php echo esc_html( torasake_field( 'story_heading', $brewery_id ) ); ?></h3>
					<?php endif; ?>
					<?php echo wp_kses_post( torasake_field( 'story_body', $brewery_id ) ); ?>
				</div>
			<?php endif; ?>

			<?php // 基本情報。可変長の dl。★価格に類する項目は入力しないこと（CLAUDE.md）。 ?>
			<?php $meta_rows = torasake_rows( 'meta_table', $brewery_id ); ?>
			<?php if ( $meta_rows ) : ?>
				<div class="meta-table">
					<?php foreach ( $meta_rows as $row ) : ?>
						<?php if ( empty( $row['label'] ) ) { continue; } ?>
						<dl>
							<dt><?php echo esc_html( $row['label'] ); ?></dt>
							<dd><?php echo wp_kses( $row['value'] ?? '', torasake_inline_html() ); ?></dd>
						</dl>
					<?php endforeach; ?>

					<?php if ( '' !== torasake_field( 'official_url', $brewery_id ) ) : ?>
						<dl>
							<dt>公式サイト</dt>
							<dd><a href="<?php echo esc_url( torasake_field( 'official_url', $brewery_id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( torasake_pretty_host( torasake_field( 'official_url', $brewery_id ) ) ); ?> ↗</a></dd>
						</dl>
					<?php endif; ?>

					<?php if ( '' !== torasake_field( 'instagram_url', $brewery_id ) ) : ?>
						<dl>
							<dt>Instagram</dt>
							<dd><a href="<?php echo esc_url( torasake_field( 'instagram_url', $brewery_id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( torasake_instagram_handle( torasake_field( 'instagram_url', $brewery_id ) ) ); ?> ↗</a></dd>
						</dl>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php // 参加イベント履歴 ?>
			<?php $history = torasake_brewery_event_history( $brewery_id ); ?>
			<?php if ( $history ) : ?>
				<div class="event-history">
					<div class="eh-header">
						<h3 class="eh-title">参加イベント履歴</h3>
						<a class="eh-back" href="<?php echo esc_url( home_url( '/breweries/' ) ); ?>">← 参加酒蔵一覧へ</a>
					</div>
					<div class="eh-list">
						<?php foreach ( $history as $entry ) : ?>
							<a class="eh-row" href="<?php echo esc_url( $entry['url'] ); ?>">
								<span class="eh-status <?php echo esc_attr( $entry['state'] ); ?>"><?php echo esc_html( $entry['status'] ); ?></span>
								<div class="eh-body">
									<div class="eh-name"><?php echo esc_html( $entry['name'] ); ?></div>
									<div class="eh-meta"><?php echo esc_html( $entry['meta'] ); ?></div>
								</div>
								<span class="eh-arrow">→</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$prev = get_previous_post();
	$next = get_next_post();
	?>
	<div class="brew-pagenav">
		<?php if ( $prev instanceof WP_Post ) : ?>
			<a class="bp-side bp-prev" href="<?php echo esc_url( (string) get_permalink( $prev ) ); ?>">
				<span class="bp-label">← 前の酒蔵</span>
				<span class="bp-name"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
			</a>
		<?php else : ?>
			<span class="bp-side"></span>
		<?php endif; ?>

		<a class="bp-all" href="<?php echo esc_url( home_url( '/breweries/' ) ); ?>">酒蔵一覧へ</a>

		<?php if ( $next instanceof WP_Post ) : ?>
			<a class="bp-side bp-next" href="<?php echo esc_url( (string) get_permalink( $next ) ); ?>">
				<span class="bp-label">次の酒蔵 →</span>
				<span class="bp-name"><?php echo esc_html( get_the_title( $next ) ); ?></span>
			</a>
		<?php else : ?>
			<span class="bp-side"></span>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
