<?php
/**
 * Ask Duy video pair.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$item = duy_get_arg( $args, 'item', [] );
if ( ! $item ) {
	return;
}
?>
<a class="video-pair glass" href="<?php echo esc_url( duy_story_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem video ' . $item['name'] ); ?>">
	<div class="vp-frames">
		<div class="vp-frame student">
			<span class="vp-tag"><?php esc_html_e( 'Học viên', 'duy-study' ); ?></span>
			<span class="vp-play"><?php echo duy_icon( 'play', 'ic-fill' ); ?></span>
			<span class="vp-cap"><?php echo esc_html( $item['name'] ?? '' ); ?></span>
		</div>
		<div class="vp-frame school">
			<span class="vp-tag"><?php esc_html_e( 'Trường', 'duy-study' ); ?></span>
			<span class="vp-play"><?php echo duy_icon( 'play', 'ic-fill' ); ?></span>
			<span class="vp-cap"><?php echo esc_html( $item['school'] ?? '' ); ?></span>
		</div>
		<span class="vp-amp">&amp;</span>
	</div>
	<div class="vp-meta">
		<div class="avatar"><?php echo esc_html( $item['i'] ?? 'D' ); ?></div>
		<div>
			<b><?php echo esc_html( $item['name'] ?? '' ); ?></b>
			<span><?php echo esc_html( $item['award'] ?? $item['school'] ?? '' ); ?></span>
		</div>
	</div>
</a>
