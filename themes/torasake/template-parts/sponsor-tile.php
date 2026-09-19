<?php
/**
 * スポンサーのロゴタイル（参照: event-single-template.html / sponsors-page.html）。
 *
 * @package torasake
 *
 * @var array{id:int,tier:string} $args
 */

defined( 'ABSPATH' ) || exit;

$sponsor_id   = (int) ( $args['id'] ?? 0 );
$sponsor_tier = (string) ( $args['tier'] ?? 'supporter' );
if ( ! $sponsor_id ) {
	return;
}

$sponsor_name = get_the_title( $sponsor_id );
$sponsor_logo = torasake_field( 'logo', $sponsor_id, null );
$sponsor_link = (string) torasake_field( 'link_url', $sponsor_id );
$slot_class   = 'title' === $sponsor_tier ? 'title-slot' : 'supporter-slot';

$tile = is_array( $sponsor_logo ) && ! empty( $sponsor_logo['ID'] )
	? wp_get_attachment_image( (int) $sponsor_logo['ID'], 'medium', false, array( 'alt' => $sponsor_name, 'loading' => 'lazy' ) )
	: esc_html( $sponsor_name );
?>
<?php if ( '' !== $sponsor_link ) : ?>
	<a class="sponsor-tile <?php echo esc_attr( $slot_class ); ?>" href="<?php echo esc_url( $sponsor_link ); ?>" target="_blank" rel="noopener">
		<?php echo $tile; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php else : ?>
	<div class="sponsor-tile <?php echo esc_attr( $slot_class ); ?>">
		<?php echo $tile; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
<?php endif; ?>
