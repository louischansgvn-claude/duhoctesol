<?php
/**
 * Country card.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$country = duy_get_arg( $args, 'item', [] );
$slug    = duy_get_arg( $args, 'slug', '' );
if ( ! $country || ! $slug ) {
	return;
}
?>
<a class="country-card <?php echo esc_attr( $country['cls'] ?? '' ); ?>" href="<?php echo esc_url( duy_country_url_from_slug( $slug ) ); ?>">
	<?php echo wp_kses_post( duy_img( duy_country_image( (string) $country['name'] ), 'Minh họa campus du học ' . $country['name'] ) ); ?>
	<div>
		<span class="cc-code"><?php echo esc_html( $country['code'] ?? '' ); ?></span>
		<h3><?php echo esc_html( $country['title'] ?? '' ); ?></h3>
		<p><?php echo esc_html( $country['lead'] ?? '' ); ?></p>
	</div>
</a>
