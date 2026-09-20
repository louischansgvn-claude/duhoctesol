<?php
/**
 * School card.
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

$country_slug = duy_country_slug( (string) ( $item['c'] ?? '' ) );
$tag          = (string) ( $item['tag'] ?? '' );
?>
<article class="card glass" data-finder-card data-order="<?php echo esc_attr( (string) ( $args['index'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['n'] ?? '' ); ?>" data-country="<?php echo esc_attr( $country_slug ); ?>" data-level="<?php echo esc_attr( duy_slugify( (string) ( $item['level'] ?? '' ) ) ); ?>" data-major="<?php echo esc_attr( duy_slugify( (string) ( $item['major'] ?? '' ) ) ); ?>" data-fee-band="<?php echo esc_attr( (string) ( $item['feeBand'] ?? $item['fee_band'] ?? '' ) ); ?>">
	<a class="thumb <?php echo esc_attr( $tag ); ?>" href="<?php echo esc_url( duy_school_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem trường ' . $item['n'] ); ?>">
		<?php if ( ! empty( $item['thumb_url'] ) ) : ?>
			<?php $duy_thumb = duy_optimized_media( (string) $item['thumb_url'] ); ?>
			<img class="school-logo-thumb<?php echo 'dark' === ( $item['thumb_bg'] ?? '' ) ? ' school-logo-thumb--dark' : ''; ?>" src="<?php echo esc_url( $duy_thumb['url'] ); ?>" alt="<?php echo esc_attr( $item['thumb_alt'] ?: ( 'Logo ' . $item['n'] ) ); ?>"<?php echo duy_size_attrs( $duy_thumb['w'], $duy_thumb['h'] ); ?> loading="lazy" decoding="async">
		<?php else : ?>
			<?php echo wp_kses_post( duy_img( duy_demo_photo( (string) ( $item['id'] ?? '' ), 'campus' ), 'Minh họa campus ' . $item['n'] ) ); ?>
		<?php endif; ?>
		<span class="badge chip <?php echo esc_attr( 'pink' === $tag ? 'pink' : '' ); ?>"><?php echo esc_html( $item['code'] ?? duy_country_code( (string) $item['c'] ) ); ?></span>
		<?php echo wp_kses_post( duy_badge_stack( $item, 'school' ) ); ?>
	</a>
	<div class="body">
		<h3><a href="<?php echo esc_url( duy_school_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['n'] ); ?></a></h3>
		<div class="meta">
			<span><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $item['city'] ?? $item['c'] ); ?></span>
			<span><?php echo duy_icon( 'cap' ); ?> <?php echo esc_html( $item['level'] ?? 'Đại học' ); ?></span>
		</div>
		<p class="muted"><?php echo esc_html( $item['r'] ?? '' ); ?></p>
		<div class="foot">
			<span class="price"><?php echo esc_html( $item['fee'] ?? '' ); ?></span>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_school_url( (string) $item['id'] ) ); ?>"><?php esc_html_e( 'Chi tiết', 'duy-study' ); ?></a>
		</div>
	</div>
</article>
