<?php
/**
 * Scholarship finder archive.
 *
 * @package DUY_Study
 */

$scholarships = duy_items_newest_first( duy_demo_scholarships(), 'scholarship' );
$featured     = duy_featured_items( $scholarships, 'scholarship', 1 )[0] ?? $scholarships[0] ?? null;
$urgent       = array_slice( duy_scholarships_by_deadline( $scholarships ), 0, 4 );

get_header();
duy_page_hero(
	'Tìm học bổng',
	'Xem nhanh giá trị học bổng, deadline và trường liên quan theo từng quốc gia.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Học bổng' ],
	],
	'Scholarships'
);
?>
<?php if ( $featured ) : ?>
	<section class="band scholarship-featured-band">
		<div class="wrap finder-featured">
			<div class="glass glass-strong feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Nổi bật', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( $featured['n'] ); ?></h2>
				<p><?php esc_html_e( 'Một học bổng đáng đưa vào checklist khi hồ sơ có thành tích, câu chuyện học tập và timeline chuẩn bị đủ sớm.', 'duy-study' ); ?></p>
				<div class="info-list">
					<p><?php echo duy_icon( 'award' ); ?> <span><?php echo esc_html( $featured['v'] ); ?></span></p>
					<p><?php echo duy_icon( 'calendar' ); ?> <span><?php echo esc_html( 'Deadline ' . $featured['d'] ); ?></span></p>
					<p><?php echo duy_icon( 'cap' ); ?> <span><?php echo esc_html( $featured['school'] . ' · ' . $featured['level'] ); ?></span></p>
				</div>
				<a class="btn btn-primary" href="<?php echo esc_url( duy_scholarship_url( (string) $featured['id'] ) ); ?>"><?php esc_html_e( 'Xem điều kiện', 'duy-study' ); ?></a>
			</div>
			<aside class="scholarship-feature-panel glass glass-strong" aria-label="<?php esc_attr_e( 'Thông tin nhanh học bổng nổi bật', 'duy-study' ); ?>">
				<a class="sf-image" href="<?php echo esc_url( duy_scholarship_url( (string) $featured['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem học bổng ' . $featured['n'] ); ?>">
					<?php echo wp_kses_post( duy_image( 'scholarship_feature_image', null, duy_country_image( (string) $featured['c'] ), 'Minh họa học bổng ' . $featured['n'] ) ); ?>
				</a>
				<div class="sf-facts">
					<span class="chip pink"><?php esc_html_e( 'Giá trị học bổng', 'duy-study' ); ?></span>
					<strong><?php echo esc_html( $featured['v'] ); ?></strong>
					<div class="sf-chip-grid">
						<span class="chip"><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $featured['c'] ); ?></span>
						<span class="chip"><?php echo duy_icon( 'cap' ); ?> <?php echo esc_html( $featured['level'] ); ?></span>
						<span class="chip"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $featured['d'] ); ?></span>
						<span class="chip"><?php echo duy_icon( 'award' ); ?> <?php esc_html_e( 'Ưu tiên hồ sơ sớm', 'duy-study' ); ?></span>
					</div>
				</div>
			</aside>
		</div>
	</section>
<?php endif; ?>
<?php if ( $urgent ) : ?>
	<section class="band scholarship-deadline-band">
		<div class="wrap">
			<div class="section-head">
				<span class="eyebrow"><?php esc_html_e( 'Sắp hết hạn', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Ưu tiên học bổng theo deadline gần nhất', 'duy-study' ); ?></h2>
			</div>
			<div class="deadline-strip">
				<?php foreach ( $urgent as $item ) : ?>
					<?php $days_left = duy_deadline_days_left( $item ); ?>
					<a class="deadline-card glass" href="<?php echo esc_url( duy_scholarship_url( (string) $item['id'] ) ); ?>">
						<span class="deadline-days"><?php echo esc_html( null === $days_left ? '--' : (string) max( 0, $days_left ) ); ?><small><?php esc_html_e( 'ngày', 'duy-study' ); ?></small></span>
						<span>
							<?php echo wp_kses_post( duy_badge_stack( $item, 'scholarship' ) ); ?>
							<strong><?php echo esc_html( $item['n'] ); ?></strong>
							<small><?php echo esc_html( $item['v'] . ' · Deadline ' . $item['d'] ); ?></small>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
<section class="band scholarship-results-band">
	<div class="wrap finder" data-finder="scholarships" data-page-size="6">
		<?php duy_part( 'finder-filter', [ 'type' => 'scholarships', 'heading' => 'Lọc học bổng' ] ); ?>
		<div>
			<div class="finder-toolbar">
				<div class="results-top" style="margin:0">
					<h2><?php esc_html_e( 'Kết quả học bổng', 'duy-study' ); ?></h2>
					<span class="chip" data-finder-count="scholarships"><?php echo esc_html( count( $scholarships ) . ' kết quả' ); ?></span>
				</div>
				<div class="finder-tools">
					<select data-finder-sort aria-label="<?php esc_attr_e( 'Sắp xếp học bổng', 'duy-study' ); ?>"><option value="featured"><?php esc_html_e( 'Nổi bật trước', 'duy-study' ); ?></option><option value="deadline"><?php esc_html_e( 'Deadline gần', 'duy-study' ); ?></option><option value="title"><?php esc_html_e( 'A-Z', 'duy-study' ); ?></option></select>
					<div class="view-toggle"><button class="active" type="button" data-view-toggle="grid" aria-pressed="true"><?php echo duy_icon( 'layers' ); ?></button><button type="button" data-view-toggle="list" aria-pressed="false"><?php echo duy_icon( 'book' ); ?></button></div>
				</div>
			</div>
			<?php duy_part( 'banner', [ 'key' => 'scholarship', 'class' => 'banner-wide' ] ); ?>
			<div class="grid g3" data-finder-grid="scholarships">
				<?php foreach ( $scholarships as $index => $scholarship ) : ?>
					<?php duy_part( 'card-scholarship', [ 'item' => $scholarship, 'index' => $index ] ); ?>
				<?php endforeach; ?>
			</div>
			<div class="empty-state glass" data-finder-empty="scholarships" hidden>
				<?php echo duy_icon_tile( 'award' ); ?>
				<h3><?php esc_html_e( 'Không tìm thấy học bổng phù hợp.', 'duy-study' ); ?></h3>
				<p class="muted"><?php esc_html_e( 'Thử đổi quốc gia hoặc xóa từ khóa để xem thêm lựa chọn.', 'duy-study' ); ?></p>
				<button class="btn btn-ghost btn-sm" type="button" data-filter-reset><?php esc_html_e( 'Xóa bộ lọc', 'duy-study' ); ?></button>
			</div>
			<div class="finder-actions"><button class="btn btn-ghost" type="button" data-load-more="scholarships"><?php esc_html_e( 'Tải thêm', 'duy-study' ); ?></button></div>
		</div>
	</div>
</section>
<section class="band">
	<div class="wrap listing-guide">
		<div class="glass glass-strong rich-section">
			<span class="eyebrow"><?php esc_html_e( 'Cách chọn học bổng', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Ưu tiên học bổng phù hợp, không chỉ học bổng lớn', 'duy-study' ); ?></h2>
			<ul class="check-list"><li>Đọc điều kiện GPA, tiếng Anh, ngành và deadline trước tiên.</li><li>Chuẩn bị câu chuyện hồ sơ, bài luận và minh chứng thành tích.</li><li>Tính phần chi phí còn lại sau học bổng để tránh thiếu ngân sách.</li></ul>
		</div>
		<?php duy_part( 'pull-quote', [ 'text' => 'Học bổng tốt nhất là học bổng đúng với hồ sơ và đủ thời gian để chuẩn bị thật kỹ.' ] ); ?>
	</div>
</section>
<section class="band"><div class="wrap testimonial-strip glass glass-strong"><p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM giúp rà soát điều kiện, timeline, bài luận và giấy tờ trước khi nộp học bổng.', 'duy-study' ); ?></p><a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Tư vấn học bổng', 'duy-study' ); ?></a></div></section>
<?php
get_footer();
