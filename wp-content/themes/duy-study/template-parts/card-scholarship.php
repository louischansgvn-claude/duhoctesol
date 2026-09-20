<?php
/**
 * Scholarship card.
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
<article class="card glass" data-finder-card data-order="<?php echo esc_attr( (string) ( $args['index'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['n'] ?? '' ); ?>" data-deadline="<?php echo esc_attr( $item['d'] ?? '' ); ?>" data-country="<?php echo esc_attr( $country_slug ); ?>" data-value="<?php echo esc_attr( duy_slugify( (string) ( $item['value'] ?? '' ) ) ); ?>" data-level="<?php echo esc_attr( duy_slugify( (string) ( $item['level'] ?? '' ) ) ); ?>">
	<a class="thumb <?php echo esc_attr( $tag ); ?>" href="<?php echo esc_url( duy_scholarship_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem học bổng ' . $item['n'] ); ?>">
		<?php echo wp_kses_post( duy_img( duy_demo_photo( (string) ( $item['id'] ?? '' ), 'campus' ), 'Minh họa học bổng ' . $item['n'] ) ); ?>
		<span class="badge chip <?php echo esc_attr( 'pink' === $tag ? 'pink' : '' ); ?>"><?php echo esc_html( $item['c'] ?? '' ); ?></span>
		<?php echo wp_kses_post( duy_badge_stack( $item, 'scholarship' ) ); ?>
	</a>
	<div class="body">
		<h3><a href="<?php echo esc_url( duy_scholarship_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['n'] ); ?></a></h3>
		<div class="meta">
			<span><?php echo duy_icon( 'award' ); ?> <?php echo esc_html( $item['v'] ?? '' ); ?></span>
			<span><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $item['d'] ?? '' ); ?></span>
		</div>
		<p class="muted"><?php echo esc_html( $item['school'] ?? '' ); ?></p>
		<div class="foot">
			<span class="price"><?php echo esc_html( $item['level'] ?? '' ); ?></span>
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_scholarship_url( (string) $item['id'] ) ); ?>"><?php esc_html_e( 'Ứng tuyển', 'duy-study' ); ?></a>
		</div>
	</div>
</article>
