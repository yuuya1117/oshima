<?php
/**
 * トップページのフッター＋スティッキーCTA（参照: reference/top-page.html）。
 *
 * @package torasake
 */

defined( 'ABSPATH' ) || exit;

$torasake_event    = torasake_current_event();
$torasake_ticket   = $torasake_event ? torasake_field( 'ticket_url', $torasake_event->ID ) : '';
$torasake_sticky   = $torasake_event ? torasake_field( 'sticky_cta_text', $torasake_event->ID ) : '';
$torasake_finished = $torasake_event && torasake_event_finished( (int) $torasake_event->ID );
?>
<footer>
	<span class="fl"><?php bloginfo( 'name' ); ?></span>
	<div class="fsub"><?php bloginfo( 'description' ); ?></div>
	<div class="flinks">
		<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News</a>
		<a href="<?php echo esc_url( home_url( '/past/' ) ); ?>">過去のイベント</a>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
		<a href="<?php echo esc_url( home_url( '/breweries/' ) ); ?>">参加酒蔵</a>
		<?php if ( '' !== torasake_option_url( 'instagram_url' ) ) : ?>
			<a href="<?php echo esc_url( torasake_option_url( 'instagram_url' ) ); ?>" target="_blank" rel="noopener">Instagram</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>">協賛について</a>
	</div>
	<div class="fcopy"><?php bloginfo( 'name' ); ?></div>
</footer>

<?php // event_status が「終了」ならスティッキーCTAは出さない。 ?>
<?php if ( $torasake_event && ! $torasake_finished && '' !== $torasake_ticket ) : ?>
	<div class="sticky-cta" id="sticky">
		<?php if ( '' !== $torasake_sticky ) : ?>
			<span class="txt"><?php echo wp_kses( $torasake_sticky, array( 'b' => array(), 'strong' => array() ) ); ?></span>
		<?php endif; ?>
		<a href="#tickets" class="btn btn-primary">チケットを見る</a>
	</div>
<?php endif; ?>
