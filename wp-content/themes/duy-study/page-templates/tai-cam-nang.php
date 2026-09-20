<?php
/**
 * Download guide page.
 *
 * @package DUY_Study
 */

$outline = duy_download_guide_outline();

get_header();
duy_page_hero(
	'Tải cẩm nang du học',
	'Cẩm nang giúp gia đình hình dung checklist 12 tháng trước kỳ nhập học.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Tải cẩm nang du học' ],
	],
	'Download'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Cẩm nang Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Một file để gia đình nhìn thấy toàn bộ hành trình', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Cẩm nang gom các câu hỏi quan trọng về chọn quốc gia, ngân sách, hồ sơ, visa và chuẩn bị lên đường. Nội dung chi phí, visa và học bổng cần xác nhận theo thời điểm thực tế.', 'duy-study' ); ?></p>
				<div class="info-list">
					<p><?php echo duy_icon( 'calendar' ); ?> <?php esc_html_e( 'Timeline 12 tháng trước kỳ nhập học', 'duy-study' ); ?></p>
					<p><?php echo duy_icon( 'check' ); ?> <?php esc_html_e( 'Checklist hồ sơ theo từng giai đoạn', 'duy-study' ); ?></p>
					<p><?php echo duy_icon( 'award' ); ?> <?php esc_html_e( 'Khung ngân sách và học bổng tham khảo', 'duy-study' ); ?></p>
				</div>
			</div>
			<?php duy_part( 'image-cluster', [ 'alt' => 'Cẩm nang du học Ban Du học Hội TESOL TP.HCM', 'images' => [ 'photo-article-laptop.webp', 'photo-library-study.webp', 'photo-campus-library.webp' ], 'stat' => [ '4', 'phần nội dung chính' ] ] ); ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Mục lục', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Trong cẩm nang có gì', 'duy-study' ); ?></h2>
			<div class="guide-steps">
				<?php foreach ( $outline as $index => $item ) : ?>
					<div class="guide-step glass">
						<span class="num"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div>
							<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<p><?php echo esc_html( $item['desc'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php duy_part( 'consultation-form', [ 'source' => 'download-guide' ] ); ?>
	</div>
</section>

<section class="band">
	<div class="wrap guide-cards cols-3">
		<?php
		foreach (
			[
				[ 'Timeline 12 tháng', 'Các mốc cần chuẩn bị trước kỳ nhập học, từ chọn hướng đi đến ngày lên đường.', 'calendar' ],
				[ 'Checklist hồ sơ', 'Giấy tờ học thuật, tiếng Anh, tài chính, visa và checklist trước bay.', 'check' ],
				[ 'Câu hỏi cho phụ huynh', 'Các điểm nên hỏi chuyên viên trước khi chọn trường, học bổng hoặc quốc gia.', 'chat' ],
			] as $item
		) :
			duy_part( 'guide-card', [ 'title' => $item[0], 'text' => $item[1], 'icon' => $item[2] ] );
		endforeach;
		?>
	</div>
</section>
<?php
get_footer();
