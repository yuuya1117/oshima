<?php
/**
 * トップページのナビ（参照: reference/top-page.html）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$torasake_event  = torasake_current_event();
$torasake_ticket = $torasake_event ? torasake_field( 'ticket_url', $torasake_event->ID ) : '';
$torasake_links  = array(
	'#lead'      => $torasake_event ? sprintf( '%sとは', get_the_title( $torasake_event ) ) : 'TORASAKEとは',
	'#outline'   => '開催概要',
	'#breweries' => '参加酒蔵',
	'#tickets'   => 'チケット',
);
?>
<nav id="nav">
	<a class="nav-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img src="<?php echo esc_url( torasake_site_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>">
	</a>
	<ul class="nav-links">
		<?php foreach ( $torasake_links as $torasake_href => $torasake_label ) : ?>
			<li><a href="<?php echo esc_attr( $torasake_href ); ?>"><?php echo esc_html( $torasake_label ); ?></a></li>
		<?php endforeach; ?>
		<li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News</a></li>
		<li><a href="#archive">アーカイブ</a></li>
		<?php if ( '' !== $torasake_ticket ) : ?>
			<li><a href="<?php echo esc_url( $torasake_ticket ); ?>" target="_blank" rel="noopener" class="nav-cta">チケット購入</a></li>
		<?php endif; ?>
	</ul>
	<button class="hamburger" id="hamburger" aria-label="メニュー" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>

<div class="mobile-menu" id="mobile-menu">
	<?php foreach ( $torasake_links as $torasake_href => $torasake_label ) : ?>
		<a href="<?php echo esc_attr( $torasake_href ); ?>"><?php echo esc_html( $torasake_label ); ?></a>
	<?php endforeach; ?>
	<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News</a>
	<a href="#archive">アーカイブ</a>
	<?php if ( '' !== torasake_option_url( 'instagram_url' ) ) : ?>
		<a href="<?php echo esc_url( torasake_option_url( 'instagram_url' ) ); ?>" target="_blank" rel="noopener">Instagram</a>
	<?php endif; ?>
</div>
