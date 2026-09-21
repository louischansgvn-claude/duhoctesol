<?php
/**
 * Front page.
 *
 * @package DUY_Study
 */

$countries    = duy_demo_countries();
$schools      = duy_demo_schools();
$scholarships = duy_items_newest_first( duy_demo_scholarships(), 'scholarship' );
$events       = duy_items_newest_first( duy_demo_events(), 'event' );
$news         = duy_items_newest_first( duy_demo_news(), 'post' );
$pairs        = duy_demo_pairs();

$duy_home_clean_text = static function ( string $value ): string {
	$value = (string) preg_replace( '/\s*\{\{TODO[^}]*\}\}/u', '', $value );
	$value = (string) preg_replace( '/\s{2,}/u', ' ', $value );

	return trim( $value );
};

$duy_home_clean_value = null;
$duy_home_clean_value = static function ( $value ) use ( &$duy_home_clean_value, $duy_home_clean_text ) {
	if ( is_array( $value ) ) {
		$cleaned = [];
		foreach ( $value as $key => $child ) {
			$cleaned[ $key ] = $duy_home_clean_value( $child );
		}

		return $cleaned;
	}

	return is_string( $value ) ? $duy_home_clean_text( $value ) : $value;
};

$countries    = $duy_home_clean_value( $countries );
$schools      = $duy_home_clean_value( $schools );
$scholarships = $duy_home_clean_value( $scholarships );
$events       = $duy_home_clean_value( $events );
$news         = $duy_home_clean_value( $news );
$pairs        = $duy_home_clean_value( $pairs );
// Cân bằng độ dài với cột tin tức bên trái (1 bài nổi bật + 4 dòng = 5 mục):
// 3 sự kiện nổi bật + 2 dòng danh sách.
$featured_events = array_slice( $events, 0, 3 );
$agenda_events   = array_slice( $events, 3, 2 );

get_header();
?>
<section class="hero screen active">
	<div class="wrap hero-grid">
		<div>
			<div class="breadcrumb"><?php esc_html_e( 'Trang chủ', 'duy-study' ); ?></div>
			<span class="eyebrow"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM · Avenue to New World', 'duy-study' ); ?></span>
			<h1><span class="hero-line"><?php esc_html_e( 'Trở thành', 'duy-study' ); ?></span> <span class="grad hero-line"><?php esc_html_e( 'công dân toàn cầu', 'duy-study' ); ?></span> <span class="hero-line"><?php esc_html_e( 'cùng Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span></h1>
			<p class="lead"><?php esc_html_e( 'Từ chọn quốc gia, chọn trường, săn học bổng đến visa và chuẩn bị lên đường, Ban Du học Hội TESOL TP.HCM đồng hành với học sinh và phụ huynh bằng một lộ trình rõ ràng.', 'duy-study' ); ?></p>

			<form class="searchbox glass glass-strong" data-hero-search data-search-mode="schools">
				<div class="search-tabs" data-search-tabs role="tablist" aria-label="<?php esc_attr_e( 'Tìm kiếm nhanh', 'duy-study' ); ?>">
					<button class="active" type="button" data-search-tab="schools" role="tab" aria-selected="true"><?php echo duy_icon( 'cap' ); ?> <?php esc_html_e( 'Tìm trường', 'duy-study' ); ?></button>
					<button type="button" data-search-tab="scholarships" role="tab" aria-selected="false"><?php echo duy_icon( 'award' ); ?> <?php esc_html_e( 'Học bổng', 'duy-study' ); ?></button>
					<button type="button" data-search-tab="countries" role="tab" aria-selected="false"><?php echo duy_icon( 'globe' ); ?> <?php esc_html_e( 'Quốc gia', 'duy-study' ); ?></button>
				</div>
				<div class="search-fields">
					<select name="country" aria-label="<?php esc_attr_e( 'Chọn quốc gia', 'duy-study' ); ?>">
						<?php foreach ( duy_country_options() as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<input name="keyword" type="search" placeholder="<?php esc_attr_e( 'Ngành học, trường, học bổng', 'duy-study' ); ?>">
					<button class="btn btn-primary" type="submit"><?php echo duy_icon( 'search' ); ?> <?php esc_html_e( 'Tìm', 'duy-study' ); ?></button>
				</div>
			</form>

			<div class="hero-cta">
				<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'lo-trinh-du-hoc' ) ); ?>"><?php esc_html_e( 'Xem lộ trình du học', 'duy-study' ); ?></a>
			</div>
		</div>
		<div class="hero-visual glass">
			<?php echo duy_img( 'photo-student-group.webp', 'Học sinh chuẩn bị lộ trình du học', '', 'eager', [ 'width' => 1600, 'height' => 1100, 'fetchpriority' => 'high', 'decoding' => 'async' ] ); ?>
			<div class="floatcard fc1 glass glass-strong"><?php echo duy_icon( 'award' ); ?> <?php esc_html_e( 'Học bổng theo hồ sơ', 'duy-study' ); ?></div>
			<div class="floatcard fc2 glass glass-strong"><?php echo duy_icon( 'shield' ); ?> <?php esc_html_e( 'Checklist visa', 'duy-study' ); ?></div>
			<div class="floatcard fc3 glass glass-strong"><?php echo duy_icon( 'route' ); ?> <?php esc_html_e( 'Lộ trình 1-1', 'duy-study' ); ?></div>
		</div>
	</div>
</section>

<section class="band" style="padding-top:1rem">
	<div class="wrap stats glass glass-strong">
		<!-- Demo stats for homepage; confirm partner/speed numbers in admin before production. -->
		<div class="stat"><b>5</b><span><?php esc_html_e( 'quốc gia trọng tâm', 'duy-study' ); ?></span></div>
		<div class="stat"><b>3</b><span><?php esc_html_e( 'văn phòng HCM · ĐN · BMT', 'duy-study' ); ?></span></div>
		<div class="stat"><b>1.200+</b><span><?php esc_html_e( 'trường đối tác tham khảo', 'duy-study' ); ?></span></div>
		<div class="stat"><b>24h</b><span><?php esc_html_e( 'phản hồi tư vấn bước đầu', 'duy-study' ); ?></span></div>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Quốc gia du học', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Chọn điểm đến theo mục tiêu học tập và ngân sách', 'duy-study' ); ?></h2>
			<p><?php esc_html_e( 'Mỗi quốc gia có lộ trình, chi phí và yêu cầu visa khác nhau. Ban Du học Hội TESOL TP.HCM giúp gia đình so sánh trước khi ra quyết định.', 'duy-study' ); ?></p>
		</div>
		<div class="grid g5">
			<?php foreach ( $countries as $slug => $country ) : ?>
				<?php duy_part( 'country-card', [ 'item' => $country, 'slug' => $slug ] ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<?php
		duy_part(
			'feature-block',
			[
				'eyebrow'   => 'Lộ trình có nhịp',
				'title'     => 'Từ mơ hồ đến checklist rõ ràng cho từng gia đình',
				'text'      => 'Ban Du học Hội TESOL TP.HCM đặt các quyết định quan trọng vào đúng thứ tự: mục tiêu học tập, ngân sách, quốc gia, trường, học bổng, visa và chuẩn bị lên đường.',
				'image'     => 'photo-campus-library.webp',
				'alt'       => 'Minh họa buổi tư vấn lộ trình du học',
				'cta_label' => 'Xem lộ trình',
				'cta_url'   => duy_route_path( 'lo-trinh-du-hoc' ),
				'show_note' => false,
			]
		);
		?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Gợi ý nổi bật', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( $scholarships ? __( 'Trường và học bổng đang được quan tâm', 'duy-study' ) : __( 'Trường đang được quan tâm', 'duy-study' ) ); ?></h2>
		</div>
		<div class="grid g3">
			<?php foreach ( array_slice( $schools, 0, 3 ) as $school ) : ?>
				<?php duy_part( 'card-school', [ 'item' => $school ] ); ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $scholarships ) : ?>
			<div class="grid g3" style="margin-top:var(--gap)">
				<?php foreach ( array_slice( $scholarships, 0, 3 ) as $scholarship ) : ?>
					<?php duy_part( 'card-scholarship', [ 'item' => $scholarship ] ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Tin tức và sự kiện', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Theo dõi cập nhật mới trước khi nộp hồ sơ', 'duy-study' ); ?></h2>
		</div>
		<div class="en-grid">
			<div>
				<?php duy_part( 'news-featured', [ 'item' => $news[0] ] ); ?>
				<div class="news-list glass" style="padding:0 1.1rem;margin-top:var(--gap)">
					<?php foreach ( array_slice( $news, 1, 4 ) as $article ) : ?>
						<a class="news-row" href="<?php echo esc_url( duy_news_url( (string) $article['id'] ) ); ?>">
							<?php echo duy_icon( 'news' ); ?>
							<div><h4><?php echo esc_html( $article['n'] ); ?></h4><span class="rmeta"><?php echo esc_html( $article['c'] ); ?></span></div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<div>
				<div class="home-events">
					<div class="home-events-featured">
						<?php foreach ( $featured_events as $event ) : ?>
							<?php
							$event_post    = get_page_by_path( (string) $event['id'], OBJECT, 'event' );
							$event_post_id = $event_post ? (int) $event_post->ID : 0;
							$event_intro   = ! empty( $event['desc'] )
								? wp_trim_words( (string) $event['desc'], 22 )
								: ( ! empty( $event['agenda'][0] )
									? sprintf( __( 'Trọng tâm: %s.', 'duy-study' ), $event['agenda'][0] )
									: __( 'Cập nhật thông tin và trả lời câu hỏi theo hồ sơ.', 'duy-study' ) );
							?>
							<article class="home-event-card glass">
								<a class="event-thumb" href="<?php echo esc_url( duy_event_url( (string) $event['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem sự kiện ' . $event['n'] ); ?>">
									<?php if ( ! empty( $event['thumb_url'] ) ) : ?>
										<img src="<?php echo esc_url( (string) $event['thumb_url'] ); ?>" alt="<?php echo esc_attr( $event['thumb_alt'] ?: ( 'Ảnh sự kiện ' . $event['n'] ) ); ?>" loading="lazy" decoding="async">
									<?php else : ?>
										<?php echo wp_kses_post( duy_image( 'cover_image', $event_post_id, 'photo-event-workshop.webp', 'Ảnh sự kiện ' . $event['n'] ) ); ?>
									<?php endif; ?>
									<span class="chip badge <?php echo esc_attr( 'pink' === ( $event['tag'] ?? '' ) ? 'pink' : '' ); ?>"><?php echo esc_html( $event['type'] ?? '' ); ?></span>
								</a>
								<div class="event-body">
									<h3><a href="<?php echo esc_url( duy_event_url( (string) $event['id'] ) ); ?>"><?php echo esc_html( $event['n'] ); ?></a></h3>
									<div class="ev-meta">
										<span><?php echo duy_icon( 'clock' ); ?> <?php echo esc_html( $event['time'] ?? $event['c'] ); ?></span>
										<?php if ( ! empty( $event['place'] ) ) : ?>
											<span><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $event['place'] ); ?></span>
										<?php endif; ?>
									</div>
									<p><?php echo esc_html( $event_intro ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
					<?php if ( $agenda_events ) : ?>
						<div class="home-events-rest">
							<h3><?php esc_html_e( 'Sự kiện gần đây', 'duy-study' ); ?></h3>
							<?php duy_part( 'event-agenda', [ 'items' => $agenda_events ] ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="hero-cta"><a class="btn btn-cyan" href="<?php echo esc_url( duy_route_path( 'su-kien' ) ); ?>"><?php esc_html_e( 'Xem tất cả sự kiện', 'duy-study' ); ?></a></div>
			</div>
		</div>
	</div>
</section>

<?php if ( $pairs ) : ?>
<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Ask Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Lắng nghe chia sẻ từ du học sinh', 'duy-study' ); ?></h2>
		</div>
		<div class="vp-carousel" tabindex="0" aria-label="<?php esc_attr_e( 'Carousel video Ask Duy', 'duy-study' ); ?>">
			<button class="vp-nav vp-prev" type="button" data-vp-prev aria-label="<?php esc_attr_e( 'Video trước', 'duy-study' ); ?>"><?php echo duy_icon( 'arrow' ); ?></button>
			<div class="vp-track">
				<?php foreach ( $pairs as $pair ) : ?>
					<?php duy_part( 'video-pair', [ 'item' => $pair ] ); ?>
				<?php endforeach; ?>
			</div>
			<button class="vp-nav vp-next" type="button" data-vp-next aria-label="<?php esc_attr_e( 'Video tiếp theo', 'duy-study' ); ?>"><?php echo duy_icon( 'arrow' ); ?></button>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div>
				<?php duy_part( 'image-cluster', [ 'alt' => 'Đội ngũ tư vấn Ban Du học Hội TESOL TP.HCM', 'images' => [ 'photo-team-office.webp', 'photo-library-study.webp', 'photo-campus-library.webp' ], 'stat' => [ '3', 'văn phòng hỗ trợ gia đình' ], 'show_note' => false ] ); ?>
			</div>
			<div>
				<?php duy_part( 'consultation-form', [ 'source' => 'home' ] ); ?>
			</div>
		</div>
	</section>

<?php get_footer(); ?>
