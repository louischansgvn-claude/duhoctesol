<?php
/**
 * Archive banner.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$key    = (string) duy_get_arg( $args, 'key', 'school' );
$class  = (string) duy_get_arg( $args, 'class', '' );
$config = duy_archive_banner_config( $key );

if ( ! $config ) {
	return;
}

$title = (string) ( $config['title'] ?? '' );
$cta   = (string) ( $config['cta'] ?? '' );
$url   = (string) ( $config['url'] ?? '#' );
?>
<a class="archive-banner promo-banner glass glass-strong <?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $title ?: $cta ); ?>">
	<span class="pb-img">
		<?php echo wp_kses_post( duy_image( (string) $config['image_field'], null, (string) $config['image'], $title ?: $cta ) ); ?>
	</span>
	<span class="pb-body">
		<?php if ( $title ) : ?>
			<h4><?php echo esc_html( $title ); ?></h4>
		<?php endif; ?>
		<?php if ( $cta ) : ?>
			<p><?php echo esc_html( $cta ); ?></p>
		<?php endif; ?>
	</span>
</a>
