<?php
/**
 * Image cluster.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = duy_get_arg( $args, 'images', [ 'photo-campus-library.webp', 'photo-team-office.webp', 'photo-library-study.webp' ] );
$alt    = (string) duy_get_arg( $args, 'alt', 'Ban Du học Hội TESOL TP.HCM' );
$stat   = duy_get_arg( $args, 'stat', [ '12', 'tháng timeline mẫu' ] );
$show_note = (bool) duy_get_arg( $args, 'show_note', true );
?>
<div class="image-cluster" aria-label="<?php echo esc_attr( $alt ); ?>">
	<div class="cluster-card cluster-main glass glass-strong"><?php echo wp_kses_post( duy_img( (string) ( $images[0] ?? 'photo-campus-library.webp' ), $alt ) ); ?></div>
	<div class="cluster-card cluster-side glass glass-strong"><?php echo wp_kses_post( duy_img( (string) ( $images[1] ?? 'photo-team-office.webp' ), $alt . ' phụ' ) ); ?></div>
	<div class="cluster-card cluster-small glass glass-strong">
		<div class="stat-callout">
			<b><?php echo esc_html( $stat[0] ?? '12' ); ?></b>
			<span class="muted"><?php echo esc_html( $stat[1] ?? 'tháng timeline mẫu' ); ?></span>
		</div>
	</div>
	<?php /* — ghi chú nội bộ, không hiển thị ra UI. */ ?>
</div>
