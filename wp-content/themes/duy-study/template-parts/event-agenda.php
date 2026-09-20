<?php
/**
 * Event agenda list.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events = duy_get_arg( $args, 'items', [] );
?>
<div class="events-agenda glass">
	<?php foreach ( $events as $event ) : ?>
		<?php
		$date = duy_event_date_parts( $event );
		// Sự kiện đã import có ảnh cover: dùng ảnh thật thay cho ô ngày.
		$thumb = (string) ( $event['thumb_url'] ?? '' );
		// `place` có thể là chuỗi rỗng nên không dùng `??` (chỉ bắt null).
		$place = trim( (string) ( $event['place'] ?? '' ) );
		$meta  = '' !== $place ? $place : trim( (string) ( $event['c'] ?? '' ) );
		$icon  = '' !== $place ? 'pin' : 'calendar';
		?>
		<a class="event-row" href="<?php echo esc_url( duy_event_url( (string) $event['id'] ) ); ?>">
			<?php if ( $thumb ) : ?>
				<?php $duy_thumb = duy_optimized_media( (string) $thumb ); ?>
				<span class="ev-thumb"><img src="<?php echo esc_url( $duy_thumb['url'] ); ?>" alt="<?php echo esc_attr( $event['thumb_alt'] ?: ( 'Hình ảnh sự kiện ' . ( $event['n'] ?? '' ) ) ); ?>"<?php echo duy_size_attrs( $duy_thumb['w'], $duy_thumb['h'] ); ?> loading="lazy" decoding="async"></span>
			<?php else : ?>
				<span class="date-badge"><span class="d"><?php echo esc_html( $date['day'] ); ?></span><span class="m"><?php echo esc_html( $date['month'] ); ?></span></span>
			<?php endif; ?>
			<span>
				<span class="ev-title"><?php echo esc_html( $event['n'] ?? '' ); ?></span>
				<?php if ( '' !== $meta ) : ?>
					<span class="ev-meta"><?php echo duy_icon( $icon ); ?> <?php echo esc_html( $meta ); ?></span>
				<?php endif; ?>
			</span>
		</a>
	<?php endforeach; ?>
</div>
