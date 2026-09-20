<?php
/**
 * Single guide template.
 *
 * @package DUY_Study
 */

$slug   = (string) duy_route_value( 'slug', '' );
$guide  = duy_guide_by_slug( $slug );
$guides = array_values( duy_demo_guides() );

if ( ! $guide ) {
	include duy_template_path( '404.php' );
	return;
}

$current_index = 0;
foreach ( $guides as $index => $item ) {
	if ( ( $item['slug'] ?? '' ) === $slug ) {
		$current_index = $index;
		break;
	}
}

$prev = $guides[ $current_index - 1 ] ?? null;
$next = $guides[ $current_index + 1 ] ?? null;

get_header();
duy_page_hero(
	$guide['title'],
	$guide['lead'],
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Lộ trình du học', 'url' => duy_route_path( 'lo-trinh-du-hoc' ) ],
		[ 'label' => $guide['title'] ],
	],
	'Lộ trình'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap guide-layout">
		<div>
			<?php
			duy_part(
				'feature-block',
				[
					'eyebrow' => 'Guide',
					'title'   => $guide['st'],
					'text'    => $guide['lead'],
					'image'   => 'photo-campus-library.webp',
					'alt'     => 'Minh họa ' . $guide['title'],
				]
			);
			?>
			<?php
			$is_value_guide = 'Bốn giá trị lớn nhất' === ( $guide['st'] ?? '' );
			?>
			<div class="guide-cards" style="margin-top:var(--gap)">
				<?php foreach ( $guide['steps'] as $index => $step ) : ?>
					<?php
					$card_label = ( $is_value_guide ? 'GIÁ TRỊ ' : 'Bước ' ) . ( $index + 1 );
					duy_part(
						'guide-card',
						[
							'title' => $step['h'],
							'text'  => $step['p'],
							'icon'  => [ 'target', 'route', 'book', 'check', 'shield' ][ $index ] ?? 'check',
							'step'  => $card_label,
						]
					);
					?>
				<?php endforeach; ?>
			</div>

			<div class="feature-block flip glass" style="margin-top:1.6rem">
				<?php duy_part( 'image-cluster', [ 'alt' => 'Minh họa lộ trình ' . $guide['title'], 'images' => [ 'photo-campus-library.webp', 'photo-library-study.webp', 'photo-team-office.webp' ], 'stat' => [ (string) ( $current_index + 1 ), 'chặng cần hoàn thiện trong lộ trình mẫu' ] ] ); ?>
				<div class="feature-copy">
					<span class="eyebrow"><?php esc_html_e( 'Điểm cần nhớ', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Làm ít việc hơn nhưng đúng thứ tự hơn', 'duy-study' ); ?></h2>
					<p><?php esc_html_e( 'Mỗi guide được thiết kế như một chặng riêng để học sinh và phụ huynh không bỏ sót giấy tờ, deadline hoặc câu hỏi tài chính quan trọng.', 'duy-study' ); ?></p>
					<?php duy_part( 'pull-quote', [ 'text' => 'Một lộ trình tốt giúp gia đình ra quyết định theo thứ tự, không theo cảm xúc của deadline.' ] ); ?>
				</div>
			</div>

			<div class="glass glass-strong" style="padding:1.3rem;border-radius:var(--r-lg);margin-top:var(--gap)">
				<?php if ( 'cost_table' === $guide['extra_type'] ) : ?>
					<h2><?php esc_html_e( 'Bảng chi phí tham khảo', 'duy-study' ); ?></h2>
					<table class="cost-table">
						<thead><tr><th><?php esc_html_e( 'Quốc gia', 'duy-study' ); ?></th><th><?php esc_html_e( 'Chi phí mẫu', 'duy-study' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( duy_demo_countries() as $country ) : ?>
								<tr><td><?php echo esc_html( $country['title'] ); ?></td><td><?php echo esc_html( $country['cost'][0] ?? '' ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php elseif ( 'phases' === $guide['extra_type'] ) : ?>
					<h2><?php esc_html_e( 'Gợi ý chia giai đoạn hồ sơ', 'duy-study' ); ?></h2>
					<div class="guide-steps">
						<div class="guide-step glass"><span class="num">01</span><div><h3><?php esc_html_e( 'Shortlist', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Chốt nhóm quốc gia, ngành, bậc học và ngân sách dự kiến.', 'duy-study' ); ?></p></div></div>
						<div class="guide-step glass"><span class="num">02</span><div><h3><?php esc_html_e( 'Hồ sơ học thuật', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Hoàn thiện bảng điểm, chứng chỉ, bài luận và thư giới thiệu.', 'duy-study' ); ?></p></div></div>
						<div class="guide-step glass"><span class="num">03</span><div><h3><?php esc_html_e( 'Tài chính và visa', 'duy-study' ); ?></h3><p><?php esc_html_e( 'Chuẩn bị chứng minh tài chính, thư mục đích và checklist visa.', 'duy-study' ); ?></p></div></div>
					</div>
				<?php elseif ( 'faq' === $guide['extra_type'] ) : ?>
					<h2><?php esc_html_e( 'Câu hỏi thường gặp sau khi có offer', 'duy-study' ); ?></h2>
					<?php duy_part( 'faq-accordion', [ 'items' => array_slice( duy_demo_faqs(), 0, 4 ) ] ); ?>
				<?php elseif ( 'checklist' === $guide['extra_type'] ) : ?>
					<h2><?php esc_html_e( 'Checklist trước khi lên đường', 'duy-study' ); ?></h2>
					<div class="guide-cards cols-3">
						<?php foreach ( [ 'Hộ chiếu, visa, thư mời, bảo hiểm', 'Chỗ ở, vé máy bay, đưa đón sân bay', 'Tiền mặt ban đầu, SIM, app ngân hàng', 'Thông tin liên hệ khẩn cấp và văn phòng trường' ] as $index => $item ) : ?>
							<?php duy_part( 'guide-card', [ 'title' => 'Checklist ' . ( $index + 1 ), 'text' => $item, 'icon' => 'check' ] ); ?>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<?php duy_part( 'pull-quote', [ 'text' => 'Du học không chỉ là tấm bằng; đó là bước ngoặt về tư duy, nghề nghiệp và con người bạn.' ] ); ?>
				<?php endif; ?>
			</div>

			<div class="hero-cta" style="justify-content:space-between">
				<?php if ( $prev ) : ?>
					<a class="btn btn-ghost" href="<?php echo esc_url( duy_guide_url( (string) $prev['slug'] ) ); ?>"><?php echo duy_icon( 'arrow' ); ?> <?php echo esc_html( $prev['title'] ); ?></a>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( duy_guide_url( (string) $next['slug'] ) ); ?>"><?php echo esc_html( $next['title'] ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<aside class="guide-side">
			<div class="side-card glass glass-strong">
				<h4><?php esc_html_e( '5 chặng lộ trình', 'duy-study' ); ?></h4>
				<nav class="gnav" aria-label="<?php esc_attr_e( 'Điều hướng lộ trình', 'duy-study' ); ?>">
					<?php foreach ( $guides as $index => $item ) : ?>
						<a class="<?php echo ( $item['slug'] ?? '' ) === $slug ? 'active' : ''; ?>" href="<?php echo esc_url( duy_guide_url( (string) $item['slug'] ) ); ?>"><span class="n"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span><?php echo esc_html( $item['title'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
			<div class="side-card glass">
				<h4><?php esc_html_e( 'Cần rà soát hồ sơ?', 'duy-study' ); ?></h4>
				<p class="muted"><?php esc_html_e( 'Gửi thông tin để Ban Du học Hội TESOL TP.HCM gợi ý bước tiếp theo.', 'duy-study' ); ?></p>
				<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
			</div>
		</aside>
	</div>
</section>
<?php
get_footer();
