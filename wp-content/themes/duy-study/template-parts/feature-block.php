<?php
/**
 * Editorial feature block.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$flip      = (bool) duy_get_arg( $args, 'flip', false );
$eyebrow   = (string) duy_get_arg( $args, 'eyebrow', '' );
$title     = (string) duy_get_arg( $args, 'title', '' );
$text      = (string) duy_get_arg( $args, 'text', '' );
$image     = (string) duy_get_arg( $args, 'image', 'photo-campus-library.webp' );
$alt       = (string) duy_get_arg( $args, 'alt', $title );
$cta_label = (string) duy_get_arg( $args, 'cta_label', '' );
$cta_url   = (string) duy_get_arg( $args, 'cta_url', '' );
$show_note = (bool) duy_get_arg( $args, 'show_note', true );
?>
<div class="feature-block <?php echo $flip ? 'flip ' : ''; ?>glass glass-strong">
	<div class="feature-copy">
		<?php if ( $eyebrow ) : ?>
			<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>
		<h2><?php echo esc_html( $title ); ?></h2>
		<p><?php echo esc_html( $text ); ?></p>
		<?php if ( $cta_label && $cta_url ) : ?>
			<div class="hero-cta">
				<a class="btn btn-primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<div class="media-art">
		<?php echo wp_kses_post( duy_img( $image, $alt ) ); ?>
		<?php if ( $show_note ) : ?>
			<?php /* — ghi chú nội bộ, không hiển thị ra UI. */ ?>
		<?php endif; ?>
	</div>
</div>
