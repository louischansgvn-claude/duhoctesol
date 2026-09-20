<?php
/**
 * Featured news block.
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
<a class="news-featured glass" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>">
	<div class="cover"><?php echo wp_kses_post( duy_news_cover_markup( $item, 'Ảnh bìa bài viết ' . $item['n'], 'photo-article-laptop.webp' ) ); ?></div>
	<div class="fbody">
		<span class="chip"><?php echo esc_html( $item['cat'] ?? '' ); ?></span>
		<h3><?php echo esc_html( $item['n'] ?? '' ); ?></h3>
		<p><?php echo esc_html( $item['lead'] ?? '' ); ?></p>
	</div>
</a>
