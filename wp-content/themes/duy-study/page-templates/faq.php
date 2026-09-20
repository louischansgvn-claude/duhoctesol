<?php
/**
 * FAQ page.
 *
 * @package DUY_Study
 */

$groups = duy_faq_groups();

get_header();
duy_page_hero(
	'FAQ',
	'Những câu hỏi thường gặp trước khi học sinh và phụ huynh bắt đầu hồ sơ du học.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'FAQ' ],
	],
	'FAQ'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Hỏi nhanh', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Câu trả lời theo nhóm quyết định', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'FAQ giúp gia đình định hình câu hỏi trước khi tư vấn. Các nội dung về phí, visa, học bổng và chính sách trường cần xác nhận theo thời điểm thực tế.', 'duy-study' ); ?></p>
				<div class="info-list">
					<?php foreach ( array_keys( $groups ) as $group_name ) : ?>
						<p><?php echo duy_icon( 'check' ); ?> <?php echo esc_html( $group_name ); ?></p>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="media-art">
				<?php echo wp_kses_post( duy_img( 'hero-students.svg', 'Học sinh và phụ huynh trao đổi câu hỏi du học' ) ); ?>
			</div>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap faq-groups">
		<?php foreach ( $groups as $group_name => $items ) : ?>
			<section class="faq-group glass">
				<h2><?php echo esc_html( $group_name ); ?></h2>
				<?php duy_part( 'faq-accordion', [ 'items' => $items ] ); ?>
			</section>
		<?php endforeach; ?>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Chưa thấy câu trả lời?', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Hỏi chuyên viên Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></h2>
			<p><?php esc_html_e( 'Mỗi hồ sơ có bối cảnh riêng. Nếu câu hỏi liên quan đến ngân sách, visa, học bổng hoặc deadline, hãy để Ban Du học Hội TESOL TP.HCM kiểm tra theo quốc gia và trường cụ thể.', 'duy-study' ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
		</div>
		<div class="glass single-hero-card">
			<h2><?php esc_html_e( 'Thông tin cần chuẩn bị khi hỏi', 'duy-study' ); ?></h2>
			<ul class="check-list">
				<li><?php esc_html_e( 'Bậc học, ngành và quốc gia đang cân nhắc.', 'duy-study' ); ?></li>
				<li><?php esc_html_e( 'Học lực, tiếng Anh và thời điểm muốn nhập học.', 'duy-study' ); ?></li>
				<li><?php esc_html_e( 'Ngân sách dự kiến và câu hỏi phụ huynh đang lo nhất.', 'duy-study' ); ?></li>
			</ul>
		</div>
	</div>
</section>
<?php
get_footer();
