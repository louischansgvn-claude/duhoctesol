<?php
/**
 * Student story card.
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

// Chấp nhận cả shape "pairs" (name/quote/school) lẫn shape story (n/q/s).
$item['n'] = (string) ( $item['n'] ?? $item['name'] ?? '' );
$item['q'] = (string) ( $item['q'] ?? $item['quote'] ?? '' );
$item['s'] = (string) ( $item['s'] ?? $item['school'] ?? '' );
$item['i'] = (string) ( $item['i'] ?? mb_substr( $item['n'], 0, 1 ) ?: 'D' );
?>
<article class="card glass">
	<a class="thumb pink" href="<?php echo esc_url( duy_story_url( (string) $item['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem câu chuyện ' . $item['n'] ); ?>">
		<?php echo wp_kses_post( duy_img( duy_demo_photo( (string) ( $item['id'] ?? '' ), 'student' ), 'Minh họa học sinh Ban Du học Hội TESOL TP.HCM ' . $item['n'] ) ); ?>
		<span class="badge chip pink"><?php echo esc_html( $item['i'] ?? 'D' ); ?></span>
	</a>
	<div class="body">
		<p class="pull-quote" style="font-size:1rem"><?php echo esc_html( $item['q'] ?? '' ); ?></p>
		<h3><?php echo esc_html( $item['n'] ?? '' ); ?></h3>
		<div class="meta"><span><?php echo duy_icon( 'cap' ); ?> <?php echo esc_html( $item['s'] ?? '' ); ?></span></div>
		<div class="foot">
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_story_url( (string) $item['id'] ) ); ?>"><?php esc_html_e( 'Xem', 'duy-study' ); ?></a>
		</div>
	</div>
</article>
