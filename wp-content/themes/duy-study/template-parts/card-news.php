<?php
/**
 * News card.
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
<article class="card glass" data-finder-card data-order="<?php echo esc_attr( (string) ( $args['index'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['n'] ?? '' ); ?>" data-deadline="<?php echo esc_attr( $item['c'] ?? '' ); ?>" data-news-cat="<?php echo esc_attr( duy_slugify( (string) ( $item['cat'] ?? '' ) ) ); ?>">
	<a class="thumb cyan" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Đọc bài ' . $item['n'] ); ?>">
		<?php echo wp_kses_post( duy_news_cover_markup( $item, 'Ảnh bìa bài viết ' . $item['n'] ) ); ?>
		<span class="badge chip"><?php echo esc_html( $item['cat'] ?? '' ); ?></span>
		<?php echo wp_kses_post( duy_badge_stack( $item, 'post' ) ); ?>
	</a>
	<div class="body">
		<h3><a href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['n'] ); ?></a></h3>
		<p class="muted"><?php echo esc_html( $item['lead'] ?? '' ); ?></p>
		<div class="foot">
			<span class="meta"><?php echo esc_html( $item['c'] ?? '' ); ?></span>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>"><?php esc_html_e( 'Xem', 'duy-study' ); ?></a>
		</div>
	</div>
</article>
