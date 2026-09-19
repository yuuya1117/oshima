<?php
/**
 * 記事ページのナビ（参照: news-single-example.html）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$torasake_event  = torasake_current_event();
$torasake_ticket = $torasake_event ? torasake_field( 'ticket_url', $torasake_event->ID ) : '';
?>
<nav>
	<a class="nav-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img src="<?php echo esc_url( torasake_site_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>">
	</a>
	<a class="nav-back" href="<?php echo esc_url( home_url( '/news/' ) ); ?>">← お知らせ一覧へ</a>
	<button class="hamburger" id="hamburger" aria-label="メニューを開く" aria-expanded="false">
		<span></span><span></span><span></span>
	</button>
	<ul class="nav-links" id="nav-links">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">トップ</a></li>
		<li><a href="<?php echo esc_url( home_url( '/#breweries' ) ); ?>">参加酒蔵</a></li>
		<li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">お知らせ</a></li>
		<li><a href="<?php echo esc_url( home_url( '/past/' ) ); ?>">過去のイベント</a></li>
		<?php if ( '' !== $torasake_ticket ) : ?>
			<li><a href="<?php echo esc_url( $torasake_ticket ); ?>" target="_blank" rel="noopener" class="nav-cta">チケット購入</a></li>
		<?php endif; ?>
	</ul>
</nav>
