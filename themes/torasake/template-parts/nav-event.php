<?php
/**
 * イベント告知ページのナビ（参照: event-single-template.html）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$torasake_ticket = (string) torasake_field( 'ticket_url', get_the_ID() );
?>
<nav>
	<a class="nav-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img src="<?php echo esc_url( torasake_site_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>">
	</a>
	<a class="nav-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← トップへ戻る</a>
	<?php if ( '' !== $torasake_ticket ) : ?>
		<a href="#ticket" class="nav-cta">チケット</a>
	<?php endif; ?>
</nav>
