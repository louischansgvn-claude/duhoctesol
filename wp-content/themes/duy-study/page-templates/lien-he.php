<?php
/**
 * Contact page.
 *
 * @package DUY_Study
 */

get_header();
$duy_contact_hotline        = (string) duy_option( 'hotline', '0906.510.747' );
$duy_contact_hotline_digits = duy_phone_digits( $duy_contact_hotline );
duy_page_hero(
	'Liên hệ Ban Du học Hội TESOL TP.HCM',
	'Gửi thông tin để đội ngũ Ban Du học Hội TESOL TP.HCM liên hệ và gợi ý lộ trình du học phù hợp với hồ sơ.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Liên hệ' ],
	],
	'Contact'
);
?>
	<section class="band" style="padding-top:1rem">
		<div class="wrap grid g2" style="align-items:start">
			<?php duy_part( 'consultation-form', [ 'source' => 'contact' ] ); ?>
			<div class="glass glass-strong single-hero-card">
				<h2><?php esc_html_e( 'Thông tin liên hệ', 'duy-study' ); ?></h2>
				<div class="info-list">
					<p><?php echo duy_icon( 'phone' ); ?> <a href="tel:<?php echo esc_attr( $duy_contact_hotline_digits ); ?>"><?php echo esc_html( $duy_contact_hotline ); ?></a></p>
					<p><?php echo duy_icon( 'send' ); ?> <?php esc_html_e( 'Gửi form để đội ngũ tư vấn phản hồi qua kênh phù hợp.', 'duy-study' ); ?></p>
				</div>
			<div class="guide-steps" style="margin-top:1rem">
				<?php foreach ( duy_offices() as $index => $office ) : ?>
					<div class="guide-step glass">
						<span class="num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
						<div><h3><?php echo esc_html( $office['name'] ); ?></h3><p><?php echo esc_html( $office['address'] ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<section class="band">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Bản đồ', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Chọn văn phòng gần gia đình nhất', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM có văn phòng tại nhiều tỉnh thành; chọn địa điểm gần bạn nhất để được hỗ trợ trực tiếp.', 'duy-study' ); ?></p>
			</div>
			<div class="media-art" aria-label="<?php esc_attr_e( 'Map placeholder', 'duy-study' ); ?>">
				<div style="height:100%;display:flex;align-items:center;justify-content:center;color:var(--primary-2)"><?php echo duy_icon( 'map', 'ic-lg' ); ?></div>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
