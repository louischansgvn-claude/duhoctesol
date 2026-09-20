<?php
/**
 * School finder archive.
 *
 * @package DUY_Study
 */

// Lọc + sắp xếp + phân trang phía server (trước đây render cả 572 thẻ rồi để JS lọc trong DOM).
$all_schools      = duy_public_value( duy_demo_schools() );
$finder_query     = duy_finder_query();
$matched_schools  = duy_finder_filter_schools( $all_schools, $finder_query );
$matched_schools  = duy_finder_sort_items( $matched_schools, (string) $finder_query['sort'], 'school' );
$page_data        = duy_paginate( $matched_schools, (int) $finder_query['page'] );
$schools          = $page_data['items'];
$featured_schools = duy_finder_has_filters( $finder_query ) || $page_data['page'] > 1 ? [] : duy_school_featured_cards( $all_schools, 6 );

duy_seo_pagination(
	[
		'base'      => 'truong',
		'query'     => $finder_query,
		'page_data' => $page_data,
	]
);

get_header();
duy_page_hero(
	'Tìm trường du học',
	'Lọc nhanh theo quốc gia, bậc học, ngành học và ngân sách dự kiến để tạo shortlist ban đầu.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Tìm trường' ],
	],
	'Finder'
);
?>
<?php if ( $featured_schools ) : ?>
	<section class="band school-feature-strip-band">
		<div class="wrap">
			<div class="section-head">
				<span class="eyebrow"><?php esc_html_e( 'Trường đối tác nổi bật', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Bắt đầu shortlist bằng các lựa chọn đáng ưu tiên', 'duy-study' ); ?></h2>
			</div>
			<div class="school-feature-rail" aria-label="<?php esc_attr_e( 'Trường đối tác nổi bật', 'duy-study' ); ?>">
				<?php foreach ( $featured_schools as $school ) : ?>
					<a class="school-rail-card glass glass-strong" href="<?php echo esc_url( duy_school_url( (string) $school['id'] ) ); ?>">
						<span class="rail-thumb"><?php echo wp_kses_post( duy_img( duy_country_image( (string) $school['c'] ), 'Minh họa campus ' . $school['n'] ) ); ?></span>
						<span class="rail-copy">
							<?php echo wp_kses_post( duy_badge_stack( $school, 'school' ) ); ?>
							<strong><?php echo esc_html( $school['n'] ); ?></strong>
							<small><?php echo esc_html( ( $school['city'] ?? $school['c'] ) . ' · ' . ( $school['major'] ?? '' ) ); ?></small>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
<section class="band">
	<div class="wrap finder" data-finder="schools" data-finder-mode="server">
		<div class="finder-aside-stack">
			<?php duy_part( 'finder-filter', [ 'type' => 'schools', 'heading' => 'Lọc trường', 'action' => duy_route_path( 'truong' ) ] ); ?>
			<?php duy_part( 'banner', [ 'key' => 'school', 'class' => 'banner-sidebar' ] ); ?>
		</div>
		<div>
			<div class="finder-toolbar">
				<div class="results-top" style="margin:0">
					<h2><?php esc_html_e( 'Kết quả trường', 'duy-study' ); ?></h2>
					<span class="chip" data-finder-count="schools"><?php echo esc_html( duy_result_range_text( $page_data ) ); ?></span>
				</div>
				<div class="finder-tools">
					<select form="schoolsFilterForm" name="sort" data-finder-sort aria-label="<?php esc_attr_e( 'Sắp xếp trường', 'duy-study' ); ?>">
						<option value="featured" <?php selected( $finder_query['sort'], 'featured' ); ?>><?php esc_html_e( 'Nổi bật trước', 'duy-study' ); ?></option>
						<option value="title" <?php selected( $finder_query['sort'], 'title' ); ?>><?php esc_html_e( 'A-Z', 'duy-study' ); ?></option>
					</select>
					<div class="view-toggle" aria-label="<?php esc_attr_e( 'Kiểu hiển thị', 'duy-study' ); ?>"><button class="active" type="button" data-view-toggle="grid" aria-pressed="true"><?php echo duy_icon( 'layers' ); ?></button><button type="button" data-view-toggle="list" aria-pressed="false"><?php echo duy_icon( 'book' ); ?></button></div>
				</div>
			</div>
			<div class="grid g3" data-finder-grid="schools">
				<?php foreach ( $schools as $index => $school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $school, 'index' => $index ] ); ?>
				<?php endforeach; ?>
			</div>
			<div class="empty-state glass" data-finder-empty="schools" <?php echo $schools ? 'hidden' : ''; ?>>
				<?php echo duy_icon_tile( 'search' ); ?>
				<h3><?php esc_html_e( 'Không tìm thấy trường phù hợp.', 'duy-study' ); ?></h3>
				<p class="muted"><?php esc_html_e( 'Thử đổi quốc gia hoặc xóa từ khóa để mở rộng kết quả.', 'duy-study' ); ?></p>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) ); ?>"><?php esc_html_e( 'Xóa bộ lọc', 'duy-study' ); ?></a>
			</div>
			<?php echo wp_kses_post( duy_pagination_markup( 'truong', $finder_query, $page_data ) ); ?>
		</div>
	</div>
</section>
<section class="band">
	<div class="wrap listing-guide">
		<div class="glass glass-strong rich-section">
			<span class="eyebrow"><?php esc_html_e( 'Cách chọn trường', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Đừng chỉ nhìn ranking, hãy nhìn mức phù hợp', 'duy-study' ); ?></h2>
			<ul class="check-list"><li>Đối chiếu ngành học, bậc học và yêu cầu đầu vào.</li><li>So sánh thành phố, chi phí sống, học bổng và cơ hội sau tốt nghiệp.</li><li>Giữ ít nhất 2-3 nhóm trường: an toàn, phù hợp và thử thách.</li></ul>
		</div>
		<?php duy_part( 'stat-callout', [ 'value' => count( $all_schools ), 'label' => 'trường trong danh sách' ] ); ?>
	</div>
</section>
<section class="band">
	<div class="wrap testimonial-strip glass glass-strong">
		<?php duy_part( 'pull-quote', [ 'text' => 'Shortlist tốt là shortlist giúp gia đình ra quyết định bình tĩnh hơn, không chạy theo tên trường một cách mơ hồ.' ] ); ?>
		<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Tư vấn chọn trường', 'duy-study' ); ?></a>
	</div>
</section>
<?php
get_footer();
