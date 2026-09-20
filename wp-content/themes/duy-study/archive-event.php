<?php
/**
 * Event archive.
 *
 * Trang vận hành như một trang tin: sự kiện mới nhất lên đầu (bài chính), các
 * sự kiện còn lại đẩy dần xuống. Khi có sự kiện mới, nó tự động thành bài chính.
 * Thứ tự: sự kiện sắp diễn ra trước (gần nhất lên đầu), rồi tới sự kiện đã diễn
 * ra (mới nhất lên đầu).
 *
 * @package DUY_Study
 */

$events      = duy_public_value( duy_items_newest_first( duy_demo_events(), 'event' ) );
$event_split = duy_split_events_by_time( $events );
$stream      = array_merge( $event_split['upcoming'], $event_split['past'] );

// 2 bài phụ (không phải 3): ảnh bìa để tỉ lệ 16/9 cho rõ mặt người, nên cột phải
// cao lên — giữ 2 card để cân chiều cao với bài chính bên trái.
$lead      = $stream[0] ?? null;
$secondary = array_slice( $stream, 1, 2 );
$grid      = array_slice( $stream, 3, 6 );
$rest      = array_slice( $stream, 9 );

$today = function_exists( 'current_time' ) ? strtotime( date( 'Y-m-d', (int) current_time( 'timestamp' ) ) ) : strtotime( date( 'Y-m-d' ) );

/** Chip trạng thái: sắp diễn ra (nhấn mạnh) hay đã diễn ra. */
$duy_ev_status = static function ( array $event ) use ( $today ): array {
	$ts = duy_event_timestamp( $event );

	return ( $ts && $ts < $today )
		? [ 'label' => 'Đã diễn ra', 'class' => '' ]
		: [ 'label' => 'Sắp diễn ra', 'class' => 'pink' ];
};

/** Ảnh bìa thật của sự kiện, fallback ảnh minh hoạ khi chưa có. */
$duy_ev_cover = static function ( array $event, string $class = '' ): string {
	$url = (string) ( $event['thumb_url'] ?? '' );
	if ( $url ) {
		return sprintf(
			'<img src="%s" alt="%s" loading="lazy" decoding="async"%s>',
			esc_url( $url ),
			esc_attr( $event['thumb_alt'] ?: ( 'Hình ảnh sự kiện ' . ( $event['n'] ?? '' ) ) ),
			$class ? ' class="' . esc_attr( $class ) . '"' : ''
		);
	}

	return duy_img( 'photo-event-workshop.webp', 'Minh hoạ sự kiện ' . ( $event['n'] ?? '' ) );
};

get_header();
duy_page_hero(
	'Sự kiện du học',
	'Hội thảo, workshop và ngày hội tư vấn Ban Du học Hội TESOL TP.HCM đã và đang tổ chức — xem lại hình ảnh, nội dung từng buổi.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Sự kiện' ],
	],
	'Events'
);
?>
<?php if ( $lead ) : ?>
	<?php $lead_status = $duy_ev_status( $lead ); ?>
	<section class="band event-editorial-band" style="padding-top:1rem">
		<div class="wrap">
			<div class="news-magazine">
				<a class="lead-story glass glass-strong" href="<?php echo esc_url( duy_event_url( (string) $lead['id'] ) ); ?>">
					<span class="lead-cover"><?php echo wp_kses_post( $duy_ev_cover( $lead ) ); ?></span>
					<span class="lead-body">
						<span class="eyebrow"><?php esc_html_e( 'Sự kiện mới nhất', 'duy-study' ); ?></span>
						<span class="ev-status-row">
							<span class="chip <?php echo esc_attr( $lead_status['class'] ); ?>"><?php echo esc_html( $lead_status['label'] ); ?></span>
							<?php if ( ! empty( $lead['c'] ) ) : ?>
								<span class="ev-date"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $lead['c'] ); ?></span>
							<?php endif; ?>
						</span>
						<h2><?php echo esc_html( $lead['n'] ); ?></h2>
						<?php if ( ! empty( $lead['desc'] ) ) : ?>
							<p><?php echo esc_html( wp_trim_words( (string) $lead['desc'], 34 ) ); ?></p>
						<?php endif; ?>
						<small><?php echo esc_html( $lead['cta'] ?? 'Xem chi tiết' ); ?> →</small>
					</span>
				</a>
				<?php if ( $secondary ) : ?>
					<div class="secondary-stories">
						<?php foreach ( $secondary as $item ) : ?>
							<?php $st = $duy_ev_status( $item ); ?>
							<a class="secondary-story secondary-story--ev glass" href="<?php echo esc_url( duy_event_url( (string) $item['id'] ) ); ?>">
								<span class="ss-cover"><?php echo wp_kses_post( $duy_ev_cover( $item ) ); ?></span>
								<span class="ss-body">
									<span class="ev-status-row">
										<span class="chip <?php echo esc_attr( $st['class'] ); ?>"><?php echo esc_html( $st['label'] ); ?></span>
									</span>
									<strong><?php echo esc_html( $item['n'] ); ?></strong>
									<small><?php echo esc_html( $item['c'] ?? '' ); ?></small>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $grid ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="section-head" style="text-align:left;max-width:none;margin-bottom:1.2rem">
				<span class="eyebrow"><?php esc_html_e( 'Sự kiện trước đó', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Xem lại các buổi tư vấn Ban Du học Hội TESOL TP.HCM đã tổ chức', 'duy-study' ); ?></h2>
			</div>
			<div class="grid g3">
				<?php foreach ( $grid as $index => $event ) : ?>
					<?php duy_part( 'card-event', [ 'item' => $event, 'index' => $index ] ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $rest ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="section-head" style="text-align:left;max-width:none;margin-bottom:.9rem">
				<span class="eyebrow"><?php esc_html_e( 'Lưu trữ', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Các sự kiện cũ hơn', 'duy-study' ); ?></h2>
			</div>
			<?php duy_part( 'event-agenda', [ 'items' => $rest ] ); ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! $lead ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="empty-state glass">
				<h3><?php esc_html_e( 'Chưa có sự kiện nào được đăng.', 'duy-study' ); ?></h3>
				<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM sẽ cập nhật lịch sự kiện và hình ảnh các buổi tư vấn tại đây.', 'duy-study' ); ?></p>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="band">
	<div class="wrap listing-guide">
		<div class="glass glass-strong rich-section">
			<span class="eyebrow"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM tại sự kiện', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Chúng tôi mang gì tới mỗi buổi tư vấn', 'duy-study' ); ?></h2>
			<ul class="check-list">
				<li>Tư vấn 1-1 theo học lực, ngành quan tâm và ngân sách của từng gia đình.</li>
				<li>Thông tin học bổng, học phí tham khảo và yêu cầu đầu vào theo từng trường.</li>
				<li>Giải đáp lộ trình hồ sơ, visa và các mốc thời gian cần chuẩn bị.</li>
			</ul>
		</div>
		<?php duy_part( 'stat-callout', [ 'value' => count( $events ), 'label' => 'sự kiện đã tổ chức' ] ); ?>
	</div>
</section>
<section class="band">
	<div class="wrap testimonial-strip glass glass-strong">
		<p><?php esc_html_e( 'Muốn Ban Du học Hội TESOL TP.HCM thông báo khi có sự kiện tại khu vực của bạn? Để lại thông tin để được liên hệ sớm.', 'duy-study' ); ?></p>
		<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Hỏi chuyên viên', 'duy-study' ); ?></a>
	</div>
</section>
<?php
get_footer();
