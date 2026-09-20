<?php
/**
 * Guide card.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = (string) duy_get_arg( $args, 'title', '' );
$text  = (string) duy_get_arg( $args, 'text', '' );
$icon  = (string) duy_get_arg( $args, 'icon', 'check' );
$step  = (string) duy_get_arg( $args, 'step', '' );
$url   = (string) duy_get_arg( $args, 'url', '' );
$tag   = $url ? 'a' : 'div';
?>
<<?php echo esc_html( $tag ); ?> class="guide-card glass" <?php echo $url ? 'href="' . esc_url( $url ) . '"' : ''; ?>>
	<div class="gc-top">
		<?php echo duy_icon_tile( $icon ); ?>
		<?php if ( $step ) : ?>
			<span class="step-no"><?php echo esc_html( $step ); ?></span>
		<?php endif; ?>
	</div>
	<h3><?php echo esc_html( $title ); ?></h3>
	<p><?php echo esc_html( $text ); ?></p>
</<?php echo esc_html( $tag ); ?>>
