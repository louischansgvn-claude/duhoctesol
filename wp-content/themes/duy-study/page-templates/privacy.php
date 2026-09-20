<?php
/**
 * Privacy page.
 *
 * @package DUY_Study
 */

get_header();
duy_page_hero(
	'Chính sách bảo mật',
	'Cách Ban Du học Hội TESOL TP.HCM xử lý thông tin liên hệ trong quá trình tư vấn du học.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Chính sách bảo mật' ],
	],
	'Privacy'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="media-art"><?php echo wp_kses_post( duy_img( 'photo-article-laptop.webp', 'Minh họa chính sách bảo mật' ) ); ?></div>
			<div class="feature-copy">
				<div class="guide-steps">
					<div class="guide-step glass"><span class="num">01</span><div><h3><?php esc_html_e( 'Thông tin thu thập', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Họ tên, email, số điện thoại, quốc gia quan tâm, thời gian dự định học và văn phòng gần nhất.', 'duy-study' ); ?></p></div></div>
					<div class="guide-step glass"><span class="num">02</span><div><h3><?php esc_html_e( 'Mục đích sử dụng', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Tư vấn lộ trình, gửi tài liệu, nhắc lịch sự kiện và hỗ trợ hồ sơ nếu học sinh/phụ huynh đồng ý.', 'duy-study' ); ?></p></div></div>
					<div class="guide-step glass"><span class="num">03</span><div><h3><?php esc_html_e( 'Quyền của người dùng', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Người dùng có thể yêu cầu cập nhật hoặc xóa thông tin liên hệ theo quy định bảo mật hiện hành.', 'duy-study' ); ?></p></div></div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
