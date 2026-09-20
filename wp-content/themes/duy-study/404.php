<?php
/**
 * Branded 404.
 *
 * @package DUY_Study
 */

if ( ! is_404() ) {
	// Template được include từ route ảo (quốc gia/bậc học không tồn tại): trả 404 thật thay vì soft-404 200.
	global $wp_query;
	if ( $wp_query instanceof WP_Query ) {
		$wp_query->set_404();
	}
	status_header( 404 );
	nocache_headers();
}

get_header();
duy_page_hero(
	'Không tìm thấy trang',
	'Đường dẫn này không còn tồn tại hoặc đã được thay đổi.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => '404' ],
	],
	'404'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Quay lại lộ trình rõ ràng hơn', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Bạn có thể về trang chủ, tìm trường hoặc gửi thông tin để Ban Du học Hội TESOL TP.HCM hỗ trợ đúng điểm đến.', 'duy-study' ); ?></p>
				<div class="hero-cta">
					<a class="btn btn-primary" href="<?php echo esc_url( duy_url( '/' ) ); ?>"><?php esc_html_e( 'Về trang chủ', 'duy-study' ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'truong' ) ); ?>"><?php esc_html_e( 'Tìm trường', 'duy-study' ); ?></a>
				</div>
			</div>
			<div class="media-art"><?php echo wp_kses_post( duy_img( 'campus-global.svg', 'Minh họa 404 Ban Du học Hội TESOL TP.HCM' ) ); ?></div>
		</div>
	</div>
</section>
<?php
get_footer();
