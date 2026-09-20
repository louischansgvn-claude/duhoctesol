<?php
/**
 * News archive.
 *
 * @package DUY_Study
 */

$news           = duy_public_value( duy_items_newest_first( duy_demo_news(), 'post' ) );
$featured_news  = duy_featured_items( $news, 'post', 4 );
$lead_story     = $featured_news[0] ?? $news[0] ?? null;
$latest_news    = array_values(
	array_filter(
		$news,
		static fn( $item ) => ( $item['id'] ?? '' ) !== ( $lead_story['id'] ?? '' )
	)
);
$secondary_news = array_slice( $latest_news, 0, 3 );
$panel_news     = array_slice( $latest_news, 3, 5 ) ?: array_slice( $latest_news, 0, 5 );
$news_by_cat    = [];
foreach ( $news as $item ) {
	$news_by_cat[ (string) ( $item['cat'] ?? 'Tin tức' ) ][] = $item;
}

get_header();
duy_page_hero(
	'Tin tức du học',
	'Cập nhật chính sách, kinh nghiệm và học bổng mới để học sinh chủ động hơn trước mỗi deadline.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Tin tức' ],
	],
	'News'
);
?>
<section class="band news-editorial-band" style="padding-top:1rem">
	<div class="wrap">
		<?php if ( $lead_story ) : ?>
			<div class="news-magazine">
				<a class="lead-story glass glass-strong" href="<?php echo esc_url( duy_news_url( (string) $lead_story['id'] ) ); ?>">
					<span class="lead-cover"><?php echo wp_kses_post( duy_news_cover_markup( $lead_story, 'Ảnh bìa bài viết ' . $lead_story['n'], 'photo-article-laptop.webp' ) ); ?></span>
					<span class="lead-body">
						<span class="eyebrow"><?php esc_html_e( 'Lead story', 'duy-study' ); ?></span>
						<?php echo wp_kses_post( duy_badge_stack( $lead_story, 'post' ) ); ?>
						<h2><?php echo esc_html( $lead_story['n'] ); ?></h2>
						<p><?php echo esc_html( $lead_story['lead'] ); ?></p>
						<small><?php echo esc_html( $lead_story['c'] ); ?></small>
					</span>
				</a>
				<div class="secondary-stories">
					<?php foreach ( $secondary_news as $item ) : ?>
						<a class="secondary-story glass" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>">
							<?php echo wp_kses_post( duy_badge_stack( $item, 'post' ) ); ?>
							<strong><?php echo esc_html( $item['n'] ); ?></strong>
							<small><?php echo esc_html( $item['cat'] . ' · ' . $item['c'] ); ?></small>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<section class="band news-banner-band">
	<div class="wrap"><?php duy_part( 'banner', [ 'key' => 'news', 'class' => 'banner-wide' ] ); ?></div>
</section>
<section class="band news-categories-band">
	<div class="wrap news-category-layout">
		<div class="category-columns">
			<?php foreach ( array_slice( $news_by_cat, 0, 3, true ) as $category => $items ) : ?>
				<div class="category-block glass">
					<span class="eyebrow"><?php echo esc_html( $category ); ?></span>
					<?php foreach ( array_slice( $items, 0, 3 ) as $item ) : ?>
						<a class="news-row" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>">
							<?php echo duy_icon( 'news' ); ?>
							<div><h4><?php echo esc_html( $item['n'] ); ?></h4><span class="rmeta"><?php echo esc_html( $item['c'] ); ?></span></div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<aside class="latest-panel glass glass-strong">
			<span class="eyebrow"><?php esc_html_e( 'Mới nhất', 'duy-study' ); ?></span>
			<?php foreach ( $panel_news as $item ) : ?>
				<a class="news-row" href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>">
					<?php echo duy_icon( 'arrow' ); ?>
					<div><h4><?php echo esc_html( $item['n'] ); ?></h4><span class="rmeta"><?php echo esc_html( $item['c'] ); ?></span></div>
				</a>
			<?php endforeach; ?>
		</aside>
	</div>
</section>
<section class="band">
	<div class="wrap finder" data-finder="news" data-page-size="6">
		<?php duy_part( 'finder-filter', [ 'type' => 'news', 'heading' => 'Lọc tin tức' ] ); ?>
		<div>
			<div class="finder-toolbar">
				<div class="results-top" style="margin:0"><h2><?php esc_html_e( 'Bài viết mới', 'duy-study' ); ?></h2><span class="chip" data-finder-count="news"><?php echo esc_html( count( $news ) . ' kết quả' ); ?></span></div>
				<div class="finder-tools"><select data-finder-sort aria-label="<?php esc_attr_e( 'Sắp xếp tin tức', 'duy-study' ); ?>"><option value="featured"><?php esc_html_e( 'Mới trước', 'duy-study' ); ?></option><option value="title"><?php esc_html_e( 'A-Z', 'duy-study' ); ?></option></select><div class="view-toggle"><button class="active" type="button" data-view-toggle="grid" aria-pressed="true"><?php echo duy_icon( 'layers' ); ?></button><button type="button" data-view-toggle="list" aria-pressed="false"><?php echo duy_icon( 'book' ); ?></button></div></div>
			</div>
			<div class="grid g3" data-finder-grid="news">
				<?php foreach ( $news as $index => $article ) : ?>
					<?php duy_part( 'card-news', [ 'item' => $article, 'index' => $index ] ); ?>
				<?php endforeach; ?>
			</div>
			<div class="empty-state glass" data-finder-empty="news" hidden>
				<?php echo duy_icon_tile( 'news' ); ?>
				<h3><?php esc_html_e( 'Không tìm thấy bài viết phù hợp.', 'duy-study' ); ?></h3>
				<p class="muted"><?php esc_html_e( 'Thử chọn chủ đề khác hoặc xóa từ khóa.', 'duy-study' ); ?></p>
				<button class="btn btn-ghost btn-sm" type="button" data-filter-reset><?php esc_html_e( 'Xóa bộ lọc', 'duy-study' ); ?></button>
			</div>
			<div class="finder-actions"><button class="btn btn-ghost" type="button" data-load-more="news"><?php esc_html_e( 'Tải thêm', 'duy-study' ); ?></button></div>
		</div>
	</div>
</section>
<section class="band"><div class="wrap listing-guide"><div class="glass glass-strong rich-section"><span class="eyebrow"><?php esc_html_e( 'Cách đọc tin', 'duy-study' ); ?></span><h2><?php esc_html_e( 'Biến bài viết thành checklist hành động', 'duy-study' ); ?></h2><ul class="check-list"><li>Lưu lại thông tin cần xác nhận với trường hoặc cơ quan visa.</li><li>Đối chiếu thông tin với hồ sơ cá nhân và mốc nhập học.</li><li>Hỏi chuyên viên nếu bài viết ảnh hưởng đến lựa chọn trường, học bổng hoặc visa.</li></ul></div><?php duy_part( 'pull-quote', [ 'text' => 'Tin tức hữu ích nhất khi nó giúp gia đình biết bước tiếp theo cần làm gì.' ] ); ?></div></section>
<section class="band"><div class="wrap testimonial-strip glass glass-strong"><p><?php esc_html_e( 'Có bài viết liên quan đến hồ sơ của bạn? Gửi câu hỏi để Ban Du học Hội TESOL TP.HCM giúp đọc cùng bối cảnh.', 'duy-study' ); ?></p><a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Hỏi chuyên viên', 'duy-study' ); ?></a></div></section>
<?php
get_footer();
