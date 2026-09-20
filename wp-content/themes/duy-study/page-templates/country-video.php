<?php
/**
 * Country video archive template.
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
}

$country      = duy_public_value( $country );
$country_name = (string) ( $country['name'] ?? '' );
$country_title = (string) ( $country['title'] ?? 'Du học ' . $country_name );
$videos       = duy_public_value( duy_country_video_items( $slug, $country, $country_ref ) );
$featured     = $videos[0] ?? null;
$other_videos = array_slice( $videos, 1 );
$schools      = $country_ref ? duy_associated_content_items( 'schools', $country_ref, duy_demo_schools(), 'school' ) : [];
$schools      = duy_public_value( $schools ?: duy_country_school_cards( $slug, (array) ( $country['schools'] ?? [] ) ) );

usort( $schools, static fn( $a, $b ) => strnatcasecmp( (string) ( $a['n'] ?? '' ), (string) ( $b['n'] ?? '' ) ) );

get_header();
?>
<section class="page-hero wrap screen active">
	<?php echo wp_kses_post( duy_breadcrumb( [ [ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ], [ 'label' => 'Quốc gia', 'url' => duy_route_path( 'quoc-gia' ) ], [ 'label' => $country_title, 'url' => duy_country_url_from_slug( $slug ) ], [ 'label' => 'Video' ] ] ) ); ?>
	<span class="eyebrow"><?php esc_html_e( 'Video theo quốc gia', 'duy-study' ); ?></span>
	<h1><?php echo duy_icon_tile( 'play' ); ?> <?php echo esc_html( 'Video du học ' . $country_name ); ?></h1>
	<p><?php echo esc_html( 'Tổng hợp video giới thiệu trường, ngành học và điểm cần kiểm tra khi chuẩn bị hồ sơ du học ' . $country_name . '.' ); ?></p>
	<div class="hero-cta">
		<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php echo esc_html( 'Tư vấn ' . $country_name ); ?></a>
		<a class="btn btn-cyan" href="<?php echo esc_url( duy_official_youtube_channel_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Kênh YouTube Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></a>
		<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php echo esc_html( 'Xem trường tại ' . $country_name ); ?></a>
	</div>
</section>

<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<?php if ( $featured ) : ?>
			<div class="country-video-feature glass glass-strong">
				<button class="youtube-facade country-video-main" type="button" data-youtube-id="<?php echo esc_attr( (string) $featured['id'] ); ?>" aria-label="<?php echo esc_attr( 'Phát video ' . (string) $featured['title'] ); ?>">
					<span class="youtube-thumb">
						<?php echo duy_youtube_thumb_markup( (string) $featured['id'], (string) $featured['title'] ); ?>
						<span class="vp-play"><?php echo duy_icon( 'play', 'ic-fill' ); ?></span>
					</span>
					<span class="youtube-copy">
						<b><?php echo esc_html( (string) $featured['title'] ); ?></b>
						<?php if ( ! empty( $featured['desc'] ) ) : ?>
							<span><?php echo esc_html( (string) $featured['desc'] ); ?></span>
						<?php endif; ?>
					</span>
				</button>
				<div class="country-video-copy">
					<span class="eyebrow"><?php echo esc_html( 'Playlist ' . $country_name ); ?></span>
					<h2><?php echo esc_html( 'Xem nhanh trước khi chọn trường tại ' . $country_name ); ?></h2>
					<?php echo duy_video_links( $featured, $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
					<p><?php esc_html_e( 'Mỗi video có thể dùng như bước mở đầu: học sinh xem trường, phụ huynh ghi lại câu hỏi về học phí, học bổng, yêu cầu đầu vào và lộ trình visa để trao đổi cùng chuyên viên.', 'duy-study' ); ?></p>
					<div class="stats compact-stats">
						<div class="stat"><b><?php echo esc_html( (string) count( $videos ) ); ?></b><span><?php esc_html_e( 'video hiện có', 'duy-study' ); ?></span></div>
						<div class="stat"><b><?php echo esc_html( (string) count( $schools ) ); ?></b><span><?php esc_html_e( 'trường liên quan', 'duy-study' ); ?></span></div>
					</div>
					<?php duy_part( 'pull-quote', [ 'text' => 'Xem video giúp gia đình có ngôn ngữ chung trước buổi tư vấn, nhất là khi đang so sánh nhiều trường cùng lúc.' ] ); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="results-top" style="margin-top:2.6rem">
			<h2><?php echo esc_html( 'Khám phá các trường tại ' . $country_name . ' qua video' ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_country_url_from_slug( $slug ) ); ?>"><?php echo esc_html( 'Quay lại ' . $country_title ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<p class="qg-desc" style="max-width:70ch;margin:.2rem 0 1.2rem"><?php echo esc_html( 'Cùng Ban Du học Hội TESOL TP.HCM tìm hiểu thông tin các trường tại ' . $country_name . ' qua các video nhé!' ); ?></p>
		<?php if ( $other_videos ) : ?>
			<div class="event-video-grid country-video-grid">
				<?php foreach ( $other_videos as $index => $video ) : ?>
					<div class="cv-item">
						<button class="youtube-facade glass" type="button" data-youtube-id="<?php echo esc_attr( (string) $video['id'] ); ?>" aria-label="<?php echo esc_attr( 'Phát video ' . (string) $video['title'] ); ?>">
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
						<?php echo duy_video_links( $video, $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php elseif ( $featured ) : ?>
			<div class="empty-state glass">
				<h3><?php echo esc_html( 'Hiện Ban Du học Hội TESOL TP.HCM có 1 video giới thiệu cho ' . $country_name . '.' ); ?></h3>
				<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM sẽ liên tục bổ sung thêm video mới trong thời gian tới.', 'duy-study' ); ?></p>
			</div>
		<?php else : ?>
			<div class="empty-state glass">
				<h3><?php echo esc_html( 'Ban Du học Hội TESOL TP.HCM đang cập nhật video giới thiệu cho ' . $country_name . '.' ); ?></h3>
				<p><?php esc_html_e( 'Bạn có thể xem toàn bộ video du học mới nhất trên kênh YouTube của Ban Du học Hội TESOL TP.HCM.', 'duy-study' ); ?></p>
				<a class="btn btn-cyan btn-sm" href="<?php echo esc_url( duy_official_youtube_channel_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Mở kênh YouTube', 'duy-study' ); ?></a>
			</div>
		<?php endif; ?>

		<div class="results-top" style="margin-top:2.6rem">
			<h2><?php echo esc_html( 'Trường liên quan tại ' . $country_name ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>"><?php esc_html_e( 'Xem tất cả trường', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<div class="grid g3">
			<?php if ( $schools ) : ?>
				<?php foreach ( array_slice( $schools, 0, 6 ) as $school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $school ] ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="empty-state glass"><h3><?php esc_html_e( 'Chưa có trường mẫu cho quốc gia này.', 'duy-study' ); ?></h3></div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
