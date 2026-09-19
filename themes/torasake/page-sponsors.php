<?php
/**
 * Template Name: 協賛ページ
 *
 * 協賛ページ（参照: reference/sponsors-page.html）。
 *
 * URL: /sponsors/  （固定ページ。テーマ有効化時に作成し、このテンプレートを割り当てる）
 *
 * スラッグ sponsors ならテンプレート階層が自動でこのファイルを選ぶが、それだと
 * _wp_page_template が default のままで ACF の page_template ロケーションに当たらない。
 * そのため有効化時にテンプレートを明示的に割り当てている。
 *
 * 参照HTMLの本文はプラン説明の固定コピーだが、運営が管理画面から
 * 書き換えられるよう全て ACF から流し込む。初期値は参照HTMLの文言。
 * 実際の協賛企業ロゴが登録されていれば、プラン紹介の下に掲出する。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();
$tiers   = torasake_rows( 'sponsor_tiers', $page_id );
$flow    = torasake_rows( 'sponsor_flow', $page_id );
$current = torasake_sponsors_by_tier();
$email   = (string) torasake_field( 'contact_email', $page_id );
?>

<header class="page-hero">
	<div class="page-hero-eyebrow">Sponsorship Program</div>
	<h1><?php echo esc_html( torasake_field( 'hero_heading', $page_id, get_the_title() ) ); ?></h1>
	<?php if ( '' !== torasake_field( 'hero_body', $page_id ) ) : ?>
		<p><?php echo esc_html( torasake_field( 'hero_body', $page_id ) ); ?></p>
	<?php endif; ?>
</header>

<?php // ─── 協賛プラン ─── ?>
<?php if ( $tiers ) : ?>
	<section id="tiers">
		<div class="section-inner">
			<div class="section-header">
				<span class="section-label">Plans</span>
				<h2 class="section-title">協賛プラン</h2>
				<div class="divider"></div>
			</div>
			<div class="tier-grid">
				<?php foreach ( $tiers as $tier ) : ?>
					<?php $is_title = 'title' === ( $tier['tier'] ?? 'supporter' ); ?>
					<div class="tier-card<?php echo $is_title ? ' title-tier' : ''; ?>">
						<span class="tier-badge <?php echo $is_title ? 'title-badge' : 'supporter-badge'; ?>">
							<?php echo esc_html( $tier['badge'] ?? '' ); ?>
						</span>
						<h3><?php echo esc_html( $tier['name'] ?? '' ); ?></h3>
						<?php $benefits = isset( $tier['benefits'] ) && is_array( $tier['benefits'] ) ? $tier['benefits'] : array(); ?>
						<?php if ( $benefits ) : ?>
							<ul>
								<?php foreach ( $benefits as $benefit ) : ?>
									<li><?php echo esc_html( is_array( $benefit ) ? ( $benefit['text'] ?? '' ) : $benefit ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php // ─── 現在の協賛企業 ─── ?>
<?php if ( $current ) : ?>
	<section id="current-sponsors" style="background: var(--bg2);">
		<div class="section-inner">
			<div class="section-header">
				<span class="section-label">Our Sponsors</span>
				<h2 class="section-title">ご協賛いただいている皆さま</h2>
				<div class="divider"></div>
			</div>
			<?php foreach ( $current as $tier => $sponsors ) : ?>
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

<?php // ─── お問い合わせから協賛開始まで ─── ?>
<?php if ( $flow ) : ?>
	<section id="flow">
		<div class="section-inner">
			<div class="section-header">
				<span class="section-label">How it Works</span>
				<h2 class="section-title">お問い合わせから協賛開始まで</h2>
				<div class="divider"></div>
			</div>
			<div class="flow-steps">
				<?php foreach ( $flow as $i => $step ) : ?>
					<div class="flow-step">
						<div class="flow-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></div>
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

<?php // ─── お問い合わせ ─── ?>
<?php if ( '' !== $email ) : ?>
	<section id="contact">
		<div class="section-inner">
			<span class="section-label">Contact</span>
			<h2 class="section-title">協賛に関するお問い合わせ</h2>
			<?php if ( '' !== torasake_field( 'contact_body', $page_id ) ) : ?>
				<p><?php echo esc_html( torasake_field( 'contact_body', $page_id ) ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( 'mailto:' . $email ); ?>" class="btn-primary">メールで問い合わせる</a>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
