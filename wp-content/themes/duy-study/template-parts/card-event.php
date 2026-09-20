<?php
/**
 * Event card.
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

$tag = (string) ( $item['tag'] ?? '' );
?>
<article class="card glass" data-finder-card data-order="<?php echo esc_attr( (string) ( $args['index'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['n'] ?? '' ); ?>" data-deadline="<?php echo esc_attr( $item['time'] ?? '' ); ?>" data-event-type="<?php echo esc_attr( duy_slugify( (string) ( $item['type'] ?? '' ) ) ); ?>">
	<a class="thumb <?php echo esc_attr( $tag ); ?>" href="<?php echo esc_url( duy_event_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem sự kiện ' . $item['n'] ); ?>">
		<?php if ( ! empty( $item['thumb_url'] ) ) : ?>
			<?php $duy_cover = duy_optimized_media( (string) $item['thumb_url'] ); ?>
			<img class="ev-cover-img" src="<?php echo esc_url( $duy_cover['url'] ); ?>" alt="<?php echo esc_attr( $item['thumb_alt'] ?: ( 'Hình ảnh sự kiện ' . $item['n'] ) ); ?>"<?php echo duy_size_attrs( $duy_cover['w'], $duy_cover['h'] ); ?> loading="lazy" decoding="async">
		<?php else : ?>
			<?php echo wp_kses_post( duy_img( duy_demo_photo( (string) ( $item['id'] ?? '' ), 'event' ), 'Minh họa sự kiện ' . $item['n'] ) ); ?>
		<?php endif; ?>
		<span class="badge chip <?php echo esc_attr( 'pink' === $tag ? 'pink' : '' ); ?>"><?php echo esc_html( $item['type'] ?? '' ); ?></span>
		<?php echo wp_kses_post( duy_badge_stack( $item, 'event' ) ); ?>
	</a>
	<div class="body">
		<h3><a href="<?php echo esc_url( duy_event_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['n'] ); ?></a></h3>
		<div class="meta">
			<span><?php echo duy_icon( 'clock' ); ?> <?php echo esc_html( $item['time'] ?? $item['c'] ); ?></span>
			<?php if ( ! empty( $item['place'] ) ) : ?>
				<span><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $item['place'] ); ?></span>
			<?php endif; ?>
			<span><?php echo duy_icon( 'user' ); ?> <?php esc_html_e( 'Học sinh & phụ huynh', 'duy-study' ); ?></span>
		</div>
		<div class="foot">
			<?php
			// `c` thường là nhãn ngày; nếu đã hiện ở dòng meta phía trên thì bỏ để
			// không lặp lại (và để tránh trông như một mức giá).
			$foot_label = (string) ( $item['c'] ?? '' );
			$meta_time  = (string) ( $item['time'] ?? $item['c'] ?? '' );
			?>
			<span class="price"><?php echo esc_html( $foot_label === $meta_time ? '' : $foot_label ); ?></span>
			<a class="btn btn-cyan btn-sm" href="<?php echo esc_url( duy_event_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['cta'] ?? __( 'Đăng ký', 'duy-study' ) ); ?></a>
		</div>
	</div>
</article>
