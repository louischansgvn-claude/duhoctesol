<?php
/**
 * Country landing template.
 *
 * @package DUY_Study
 */

$slug    = (string) duy_route_value( 'slug', '' );
$country = duy_country_by_slug( $slug );

if ( ! $country ) {
	include duy_template_path( '404.php' );
	return;
}

$country_term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', $slug, 'country' ) : null;
$country_ref  = $country_term && ! is_wp_error( $country_term ) ? 'term:' . (int) $country_term->term_id : null;

if ( $country_ref ) {
	$country['lead']     = (string) duy_field( 'lead', $country_ref, $country['lead'] );
	$country['overview'] = (string) duy_field( 'overview', $country_ref, $country['overview'] ?? $country['lead'] );

	$term_stats = duy_rows( 'stats', $country_ref );
	if ( $term_stats ) {
		$country['stats'] = array_map(
			static fn( $row ) => [ $row['value'] ?? $row['value_text'] ?? '', $row['label'] ?? '' ],
			$term_stats
		);
	}

	$term_why = duy_rows( 'why', $country_ref );
	if ( $term_why ) {
		$country['why'] = array_map(
			static fn( $row ) => [
				'icon' => $row['icon'] ?? 'sparkles',
				't'    => $row['t'] ?? $row['title'] ?? 'Lý do',
				'd'    => $row['d'] ?? $row['desc'] ?? $row['text'] ?? '',
			],
			$term_why
		);
	}

	$term_system = duy_rows( 'system', $country_ref );
	if ( $term_system ) {
		$country['system'] = array_map(
			static function ( array $row ): string {
				$title = trim( (string) ( $row['title'] ?? '' ) );
				$desc  = trim( (string) ( $row['desc'] ?? '' ) );

				return $title && $desc ? $title . ': ' . $desc : $title . $desc;
			},
			$term_system
		);
	}

	$term_visa = duy_rows( 'visa', $country_ref );
	if ( $term_visa ) {
		$country['visa'] = duy_mockup_v2_string_items( $term_visa );
	}

	$term_cost = duy_rows( 'cost', $country_ref );
	if ( $term_cost ) {
		$country['cost'] = array_map(
			static function ( array $row ): string {
				$label = trim( (string) ( $row['label'] ?? '' ) );
				$value = trim( (string) ( $row['value'] ?? $row['value_text'] ?? '' ) );

				return $label && $value ? $label . ': ' . $value : $label . $value;
			},
			$term_cost
		);
	}
}

$country['overview'] = $country['overview'] ?? duy_country_overview_default( $slug, $country );
$country             = duy_public_value( $country );
$country_name        = (string) ( $country['name'] ?? '' );
$why_items           = array_values( (array) ( $country['why'] ?? [] ) );
$schools             = $country_ref ? duy_associated_content_items( 'schools', $country_ref, duy_demo_schools(), 'school' ) : [];
$scholarships        = $country_ref ? duy_associated_content_items( 'scholarships', $country_ref, duy_demo_scholarships(), 'scholarship' ) : [];
$news                = $country_ref ? duy_associated_content_items( 'news', $country_ref, duy_demo_news(), 'post' ) : [];
$schools             = duy_public_value( $schools ?: duy_country_school_cards( $slug, (array) ( $country['schools'] ?? [] ) ) );
$scholarships        = duy_public_value( $scholarships ?: duy_items_by_ids( duy_demo_scholarships(), $country['schols'] ?? [] ) );
$news                = duy_public_value( $news ?: duy_items_by_ids( duy_demo_news(), $country['news'] ?? [] ) );
$schools_az          = $schools;
usort( $schools_az, static fn( $a, $b ) => strnatcasecmp( (string) ( $a['n'] ?? '' ), (string) ( $b['n'] ?? '' ) ) );
$levels              = duy_mockup_v2_levels();
$level_copy          = [
	'thpt'         => 'Chương trình trung học, làm quen môi trường quốc tế sớm; thường cần giám hộ nếu dưới 18 tuổi.',
	'cao-dang'    => 'Lộ trình thực tiễn, tối ưu chi phí và dễ chuyển tiếp lên đại học.',
	'dai-hoc'     => 'Chương trình cử nhân theo ngành, yêu cầu học thuật và tiếng Anh rõ ràng.',
	'sau-dai-hoc' => 'Thạc sĩ/tiến sĩ cho hồ sơ đã có nền tảng chuyên ngành liên quan.',
	'anh-ngu'     => 'Khóa tiếng Anh ngắn hạn, linh hoạt thời gian, dùng làm bước đệm trước khóa chính.',
];
$majors              = duy_public_value( duy_mockup_v2_majors() );

get_header();
?>
<section class="page-hero wrap screen active">
	<?php echo wp_kses_post( duy_breadcrumb( [ [ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ], [ 'label' => 'Quốc gia', 'url' => duy_route_path( 'quoc-gia' ) ], [ 'label' => $country_name ] ] ) ); ?>
	<h1><?php echo duy_icon_tile( 'globe' ); ?> <?php echo esc_html( (string) ( $country['title'] ?? '' ) ); ?></h1>
	<p><?php echo esc_html( (string) ( $country['lead'] ?? '' ) ); ?></p>
	<div class="hero-cta">
		<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php echo esc_html( 'Tư vấn ' . (string) ( $country['title'] ?? '' ) ); ?></a>
		<a class="btn btn-cyan" href="<?php echo esc_url( duy_country_video_url( $slug ) ); ?>"><?php echo esc_html( 'Video du học ' . $country_name ); ?></a>
		<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php echo esc_html( 'Xem trường tại ' . $country_name ); ?></a>
	</div>
</section>

<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong" style="margin-bottom:2.5rem">
			<div class="media-art">
				<?php echo wp_kses_post( $country_ref ? duy_image( 'hero_image', $country_ref, duy_country_image( $country_name ), 'Minh họa campus và điểm đến du học ' . $country_name ) : duy_img( duy_country_image( $country_name ), 'Minh họa campus và điểm đến du học ' . $country_name ) ); ?>
				<?php /* — slot ảnh thương hiệu, không hiển thị ra UI. */ ?>
			</div>
			<div class="feature-copy">
				<span class="eyebrow"><?php echo esc_html( 'Tổng quan ' . $country_name ); ?></span>
				<h2><?php echo esc_html( (string) ( $country['lead'] ?? '' ) ); ?></h2>
				<p><?php esc_html_e( 'Mockup dùng dữ liệu tham khảo để gia đình so sánh nhanh. Các số liệu học phí, visa và chính sách mới cần Ban Du học Hội TESOL TP.HCM xác nhận trước khi public.', 'duy-study' ); ?></p>
				<div class="hero-cta">
					<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php echo esc_html( 'Nhận shortlist ' . $country_name ); ?></a>
					<a class="btn btn-cyan" href="<?php echo esc_url( duy_country_video_url( $slug ) ); ?>"><?php esc_html_e( 'Xem video trường', 'duy-study' ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'hoc-bong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php echo esc_html( 'Học bổng ' . $country_name ); ?></a>
				</div>
			</div>
		</div>

		<div class="stats glass glass-strong" style="margin-bottom:2.5rem">
			<?php foreach ( (array) ( $country['stats'] ?? [] ) as $stat ) : ?>
				<div class="stat"><b><?php echo esc_html( (string) ( $stat[0] ?? '' ) ); ?></b><span><?php echo esc_html( (string) ( $stat[1] ?? '' ) ); ?></span></div>
			<?php endforeach; ?>
		</div>

		<div class="results-top">
			<h2><?php echo esc_html( 'Các trường tại ' . $country_name ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php esc_html_e( 'Xem tất cả', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<p class="qg-desc" style="max-width:70ch;margin:.2rem 0 1.2rem"><?php echo esc_html( 'Các trường tiêu biểu tại ' . $country_name . ', sắp xếp theo bảng chữ cái để bạn dễ tra cứu.' ); ?></p>
		<div class="grid g3" style="margin-bottom:2.6rem">
			<?php if ( $schools_az ) : ?>
				<?php foreach ( $schools_az as $school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $school ] ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty-state glass"><h3><?php esc_html_e( 'Chưa có trường mẫu cho quốc gia này.', 'duy-study' ); ?></h3></div>
			<?php endif; ?>
		</div>

		<div class="section-head" style="text-align:left;max-width:none;margin:0 0 .6rem">
			<span class="eyebrow"><?php echo esc_html( 'Vì sao chọn ' . $country_name . '?' ); ?></span>
			<h2><?php echo esc_html( 'Lý do thuyết phục để chọn ' . $country_name ); ?></h2>
		</div>
		<p class="qg-desc" style="max-width:62ch;margin-bottom:1.4rem"><?php echo esc_html( (string) ( $country['overview'] ?? '' ) ); ?></p>
		<div class="bento reasons" style="margin-bottom:2.6rem">
			<?php if ( ! empty( $why_items[0] ) && is_array( $why_items[0] ) ) : ?>
				<div class="glass large reason reason-feature">
					<span class="chip pink" style="align-self:flex-start;margin-bottom:.7rem"><?php echo duy_icon( 'star', 'ic-fill' ); ?> <?php esc_html_e( 'Lý do hàng đầu', 'duy-study' ); ?></span>
					<?php echo duy_icon_tile( (string) ( $why_items[0]['icon'] ?? 'sparkles' ) ); ?>
					<h3><?php echo esc_html( (string) ( $why_items[0]['t'] ?? '' ) ); ?></h3>
					<p><?php echo esc_html( (string) ( $why_items[0]['d'] ?? '' ) ); ?></p>
					<?php duy_part( 'stat-callout', [ 'value' => $country['stats'][0][0] ?? $country['code'], 'label' => $country['stats'][0][1] ?? '' ] ); ?>
				</div>
			<?php endif; ?>
			<?php foreach ( array_slice( $why_items, 1 ) as $reason ) : ?>
				<?php if ( ! is_array( $reason ) ) { continue; } ?>
				<div class="glass reason">
					<?php echo duy_icon_tile( (string) ( $reason['icon'] ?? 'sparkles' ) ); ?>
					<h3><?php echo esc_html( (string) ( $reason['t'] ?? '' ) ); ?></h3>
					<p><?php echo esc_html( (string) ( $reason['d'] ?? '' ) ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="feature-block flip glass" style="margin-bottom:2.6rem">
			<?php duy_part( 'image-cluster', [ 'alt' => 'Đời sống và học tập tại ' . $country_name, 'images' => [ duy_country_image( $country_name ), 'photo-library-study.webp', 'photo-campus-library.webp' ], 'stat' => $country['stats'][0] ?? [ (string) ( $country['code'] ?? '' ), 'điểm đến' ] ] ); ?>
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Đồng hành cùng Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( 'Lộ trình ' . $country_name . ' thiết kế riêng cho bạn' ); ?></h2>
				<p><?php esc_html_e( 'Chuyên viên giúp bạn so sánh trường, ước tính chi phí thực tế và lập timeline hồ sơ - visa phù hợp mục tiêu của gia đình.', 'duy-study' ); ?></p>
				<?php duy_part( 'pull-quote', [ 'text' => 'Chọn đúng quốc gia là bước đầu; đi đúng lộ trình mới tạo ra kết quả.' ] ); ?>
				<div class="hero-cta" style="margin-top:1.1rem">
					<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php echo esc_html( 'Tư vấn ' . $country_name ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php esc_html_e( 'Xem trường', 'duy-study' ); ?></a>
				</div>
			</div>
		</div>

		<div class="section-head" style="text-align:left;max-width:none;margin:0 0 1rem">
			<span class="eyebrow"><?php esc_html_e( 'Bậc học', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Du học ' . $country_name . ' theo từng bậc học' ); ?></h2>
		</div>
		<div class="guide-cards cols-4" style="margin-bottom:2.6rem">
			<?php foreach ( $levels as $level_slug => $level ) : ?>
				<?php
				duy_part(
					'guide-card',
					[
						'title' => (string) $level['name'],
						'text'  => (string) ( $level_copy[ $level_slug ] ?? '' ),
						'icon'  => (string) $level['icon'],
						'url'   => duy_country_level_url( $slug, (string) $level_slug ),
					]
				);
				?>
			<?php endforeach; ?>
		</div>

		<div class="section-head" style="text-align:left;max-width:none;margin:0 0 1.2rem">
			<span class="eyebrow"><?php esc_html_e( 'Hệ thống giáo dục', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Các lộ trình học tại ' . $country_name ); ?></h2>
		</div>
		<div class="guide-cards cols-3" style="margin-bottom:2.6rem">
			<?php foreach ( (array) ( $country['system'] ?? [] ) as $index => $line ) : ?>
				<?php
				$parts = explode( ':', (string) $line, 2 );
				duy_part(
					'guide-card',
					[
						'title' => trim( $parts[0] ?? $line ),
						'text'  => trim( $parts[1] ?? $line ),
						'icon'  => [ 'cap', 'layers', 'route', 'book' ][ $index % 4 ] ?? 'book',
					]
				);
				?>
			<?php endforeach; ?>
		</div>

		<div class="bento" style="margin-bottom:2.6rem">
			<div class="glass large" style="padding:1.4rem">
				<h3 style="font-size:1.3rem"><?php esc_html_e( 'Chi phí tham khảo', 'duy-study' ); ?></h3>
				<table class="cost-table" style="margin-top:.7rem">
					<tr><th><?php esc_html_e( 'Khoản mục', 'duy-study' ); ?></th><th><?php esc_html_e( 'Mức tham khảo', 'duy-study' ); ?></th></tr>
					<?php foreach ( (array) ( $country['cost'] ?? [] ) as $cost ) : ?>
						<?php $parts = explode( ':', (string) $cost, 2 ); ?>
						<tr><td><b><?php echo esc_html( trim( $parts[0] ?? $cost ) ); ?></b></td><td><?php echo esc_html( trim( $parts[1] ?? '' ) ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>
			<div class="glass" style="padding:1.4rem">
				<h3 style="font-size:1.2rem"><?php esc_html_e( 'Checklist visa/hồ sơ', 'duy-study' ); ?></h3>
				<div class="info-list" style="margin-top:.6rem">
					<?php foreach ( (array) ( $country['visa'] ?? [] ) as $item ) : ?>
						<p><?php echo duy_icon( 'check' ); ?> <span><?php echo esc_html( (string) $item ); ?></span></p>
					<?php endforeach; ?>
				</div>
				<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>" style="margin-top:1rem"><?php echo esc_html( 'Check hồ sơ ' . $country_name ); ?></a>
			</div>
		</div>

		<div class="section-head" style="text-align:left;max-width:none;margin:0 0 1.2rem">
			<span class="eyebrow"><?php esc_html_e( 'Ngành học', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Ngành học tại ' . $country_name ); ?></h2>
		</div>
		<div class="country-major-grid" style="margin-bottom:2.6rem">
			<?php foreach ( $majors as $major ) : ?>
				<?php
				$major_slug    = (string) ( $major['slug'] ?? '' );
				$major_schools = duy_public_value( duy_mockup_v2_schools_for_major( $major_slug, $slug, 4 ) );
				?>
				<article class="country-major-card glass">
					<div class="major-card-head">
						<?php echo duy_icon_tile( (string) ( $major['icon'] ?? 'sparkles' ) ); ?>
						<div>
							<h3><?php echo esc_html( (string) ( $major['title'] ?? '' ) ); ?></h3>
							<p><?php echo esc_html( (string) ( $major['lead'] ?? '' ) ); ?></p>
						</div>
					</div>
					<?php if ( $major_schools ) : ?>
						<div class="qg-mini-grid">
							<?php foreach ( $major_schools as $school ) : ?>
								<a class="school-mini" href="<?php echo esc_url( duy_school_url( (string) $school['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem trường ' . $school['n'] ); ?>">
									<?php echo duy_icon_tile( 'cap' ); ?>
									<span><b><?php echo esc_html( (string) $school['n'] ); ?></b><span><?php echo esc_html( (string) ( $school['city'] ?? $school['c'] ?? '' ) ); ?></span></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="country-major-empty">
							<p class="muted"><?php esc_html_e( 'Đăng ký tư vấn để Ban Du học Hội TESOL TP.HCM gợi ý trường phù hợp với hồ sơ và mục tiêu của bạn.', 'duy-study' ); ?></p>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Nhận gợi ý trường', 'duy-study' ); ?></a>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="results-top">
			<h2><?php esc_html_e( 'Học bổng liên quan', 'duy-study' ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'hoc-bong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php esc_html_e( 'Xem học bổng', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<div class="grid g3" style="margin-bottom:2.4rem">
			<?php if ( $scholarships ) : ?>
				<?php foreach ( $scholarships as $scholarship ) : ?>
					<?php duy_part( 'card-scholarship', [ 'item' => $scholarship ] ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty-state glass"><h3><?php esc_html_e( 'Học bổng cho quốc gia này đang được cập nhật.', 'duy-study' ); ?></h3></div>
			<?php endif; ?>
		</div>

		<div class="results-top">
			<h2><?php echo esc_html( 'Cẩm nang ' . $country_name ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'tin-tuc' ) ); ?>"><?php esc_html_e( 'Xem tin khác', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<div class="grid g3">
			<?php if ( $news ) : ?>
				<?php foreach ( $news as $article ) : ?>
					<?php duy_part( 'card-news', [ 'item' => $article ] ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty-state glass"><h3><?php esc_html_e( 'Chưa có bài viết mẫu.', 'duy-study' ); ?></h3></div>
			<?php endif; ?>
		</div>

		<?php
		$country_videos = duy_public_value( duy_country_video_items( $slug, $country, $country_ref ) );
		$video_preview  = array_slice( $country_videos, 0, 3 );
		?>
		<?php if ( $video_preview ) : ?>
			<div class="results-top" style="margin-top:2.6rem">
				<h2><?php echo esc_html( 'Video du học ' . $country_name ); ?></h2>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_country_video_url( $slug ) ); ?>"><?php echo esc_html( 'Xem tất cả ' . count( $country_videos ) . ' video' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
			</div>
			<div class="event-video-grid country-video-grid">
				<?php foreach ( $video_preview as $index => $video ) : ?>
					<button class="youtube-facade glass <?php echo 0 === $index ? 'event-video-main' : ''; ?>" type="button" data-youtube-id="<?php echo esc_attr( (string) $video['id'] ); ?>" aria-label="<?php echo esc_attr( 'Phát video ' . (string) $video['title'] ); ?>">
						<span class="youtube-thumb">
							<?php echo duy_youtube_thumb_markup( (string) $video['id'], (string) $video['title'] ); ?>
							<span class="vp-play"><?php echo duy_icon( 'play', 'ic-fill' ); ?></span>
						</span>
						<span class="youtube-copy">
							<b><?php echo esc_html( (string) $video['title'] ); ?></b>
							<?php if ( ! empty( $video['school'] ) ) : ?>
								<span class="chip"><?php echo esc_html( (string) $video['school'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $video['desc'] ) ) : ?>
								<span><?php echo esc_html( (string) $video['desc'] ); ?></span>
							<?php endif; ?>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
			<div style="margin-top:1.4rem">
				<a class="btn btn-cyan" href="<?php echo esc_url( duy_country_video_url( $slug ) ); ?>"><?php echo esc_html( 'Xem thêm video du học ' . $country_name ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
			</div>
		<?php endif; ?>

		<?php $country_faqs = function_exists( 'duy_faq_items' ) ? duy_faq_items( $slug ) : []; ?>
		<?php if ( $country_faqs ) : ?>
			<div class="section-head" style="text-align:left;max-width:none;margin:2.6rem 0 1rem">
				<span class="eyebrow"><?php esc_html_e( 'Câu hỏi thường gặp', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( 'Câu hỏi thường gặp về du học ' . $country_name ); ?></h2>
			</div>
			<?php duy_part( 'faq-accordion', [ 'items' => $country_faqs ] ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
