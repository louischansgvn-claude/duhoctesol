<?php
/**
 * Single event template.
 *
 * @package DUY_Study
 */

$id     = (string) duy_route_value( 'id', '' );
$events = duy_public_value( duy_items_newest_first( duy_demo_events(), 'event' ) );
$event  = duy_find_by_id( $events, $id );

if ( ! $event ) {
	include duy_template_path( '404.php' );
	return;
}

$post      = function_exists( 'get_page_by_path' ) ? get_page_by_path( $id, OBJECT, 'event' ) : null;
$post_id   = $post ? (int) $post->ID : 0;

// Sự kiện đã diễn ra được import (recap): post_content đã là bài hoàn chỉnh gồm
// các đoạn nội dung + thư viện ảnh. Render nguyên trạng, không dựng agenda/
// diễn giả/phí như sự kiện sắp diễn ra.
if ( $post_id && get_post_meta( $post_id, '_duy_ev_recap', true ) ) {
	$title     = get_the_title( $post_id );
	$date_text = (string) get_post_meta( $post_id, '_duy_ev_date_text', true );
	$type      = (string) get_post_meta( $post_id, '_duy_ev_type', true );

	get_header();
	duy_page_hero(
		$title,
		trim( $date_text . ( $type ? ' · ' . $type : '' ) ),
		[
			[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
			[ 'label' => 'Sự kiện', 'url' => duy_route_path( 'su-kien' ) ],
			[ 'label' => $title ],
		],
		'Event'
	);
	?>
	<section class="band" style="padding-top:1.25rem">
		<article class="wrap event-recap">
			<div class="sa-meta">
				<?php if ( $date_text ) : ?>
					<span class="sa-chip"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $date_text ); ?></span>
				<?php endif; ?>
				<?php if ( $type ) : ?>
					<span class="sa-badge"><?php echo esc_html( $type ); ?></span>
				<?php endif; ?>
			</div>
			<div class="seo-body"><?php echo wp_kses_post( get_post_field( 'post_content', $post_id ) ); ?></div>
			<div class="sa-consult" id="event-consult">
				<?php duy_part( 'consultation-form', [ 'source' => 'su-kien-' . $id ] ); ?>
			</div>
		</article>
	</section>
	<?php
	get_footer();
	return;
}

$profile   = duy_public_value( duy_event_profile_defaults( $event ) );
$get_field = static fn( string $field, $fallback = null ) => $post_id ? duy_field( $field, $post_id, $fallback ) : $fallback;
$get_rows  = static function ( string $field, array $fallback = [] ) use ( $post_id ): array {
	$rows = $post_id ? duy_rows( $field, $post_id ) : [];

	return $rows ?: $fallback;
};

$audience = duy_public_text( (string) $get_field( 'audience', $profile['audience'] ) );
$fee      = duy_public_text( (string) $get_field( 'fee', $profile['fee'] ) );
$benefits = duy_public_value( $get_rows( 'benefits', $profile['benefits'] ) );
$who      = duy_public_value( $get_rows( 'who', array_map( static fn( $item ) => [ 'item' => $item ], $profile['who'] ) ) );

$agenda_fallback = array_map(
	static fn( $item, $index ) => [
		'time' => sprintf( '%02d', (int) $index + 1 ),
		'item' => $item,
		'desc' => 'Có phần hỏi đáp để gia đình đặt câu hỏi theo hồ sơ.',
	],
	$event['agenda'] ?? [],
	array_keys( $event['agenda'] ?? [] )
);
$agenda_rows = duy_public_value( $get_rows( 'agenda', $agenda_fallback ) );
$agenda      = array_values(
	array_filter(
		array_map(
			static function ( $row, $index ) use ( $agenda_fallback ): array {
				if ( is_string( $row ) ) {
					$row = [ 'item' => $row ];
				}

				$fallback = $agenda_fallback[ $index ] ?? [];

				return [
					'time' => duy_public_text( (string) ( $row['time'] ?? $fallback['time'] ?? sprintf( '%02d', (int) $index + 1 ) ) ),
					'item' => duy_public_text( (string) ( $row['item'] ?? $row['title'] ?? $fallback['item'] ?? '' ) ),
					'desc' => duy_public_text( (string) ( $row['desc'] ?? $fallback['desc'] ?? '' ) ),
				];
			},
			$agenda_rows,
			array_keys( $agenda_rows )
		),
		static fn( array $row ): bool => '' !== $row['item']
	)
);

$speaker_fallback = $profile['speakers'] ?? [];
$speakers_raw     = duy_public_value( $get_rows( 'speakers', $speaker_fallback ) );
$speakers         = array_values(
	array_filter(
		array_map(
			static function ( array $speaker, int $index ) use ( $speaker_fallback ): array {
				$fallback = $speaker_fallback[ $index ] ?? [];

				return [
					'photo' => $speaker['photo'] ?? $fallback['photo'] ?? '',
					'name'  => duy_public_text( (string) ( $speaker['name'] ?? $fallback['name'] ?? '' ) ),
					'role'  => duy_public_text( (string) ( $speaker['role'] ?? $fallback['role'] ?? '' ) ),
					'org'   => duy_public_text( (string) ( $speaker['org'] ?? $fallback['org'] ?? '' ) ),
					'bio'   => duy_public_text( (string) ( $speaker['bio'] ?? $fallback['bio'] ?? '' ) ),
				];
			},
			$speakers_raw,
			array_keys( $speakers_raw )
		),
		static fn( array $speaker ): bool => '' !== $speaker['name']
	)
);

$videos_raw = duy_public_value( $get_rows( 'videos', $profile['videos'] ?? [] ) );
$videos     = array_values(
	array_filter(
		array_map(
			static function ( array $video ): array {
				$url = (string) ( $video['url'] ?? '' );
				$id  = duy_youtube_id( $url );

				return [
					'id'    => $id,
					'title' => duy_public_text( (string) ( $video['title'] ?? 'Video sự kiện' ) ),
					'desc'  => duy_public_text( (string) ( $video['desc'] ?? '' ) ),
				];
			},
			$videos_raw
		),
		static fn( array $video ): bool => '' !== $video['id']
	)
);

$gallery = $post_id ? (array) duy_field( 'gallery', $post_id, [] ) : [];
if ( ! $gallery ) {
	$gallery = $profile['gallery'] ?? [];
}

$normalize_school_ref = static function ( $ref ): string {
	if ( is_numeric( $ref ) ) {
		$post = get_post( (int) $ref );

		return $post instanceof WP_Post ? (string) $post->post_name : '';
	}

	if ( is_array( $ref ) ) {
		$ref = $ref['id'] ?? $ref['ID'] ?? $ref['value'] ?? '';
	}

	if ( is_string( $ref ) && preg_match( '/^post:school:(\d+)$/', $ref, $matches ) ) {
		$post = get_post( (int) $matches[1] );

		return $post instanceof WP_Post ? (string) $post->post_name : '';
	}

	return is_string( $ref ) ? sanitize_title( $ref ) : '';
};

$school_refs = $post_id ? (array) duy_field( 'participating_schools', $post_id, [] ) : [];
if ( ! $school_refs ) {
	$school_refs = $profile['schools'] ?? [];
}

$participating_schools = array_values(
	array_filter(
		array_map(
			static function ( $ref ) use ( $normalize_school_ref ): ?array {
				$slug = $normalize_school_ref( $ref );

				return $slug ? duy_find_by_id( duy_demo_schools(), $slug ) : null;
			},
			$school_refs
		)
	)
);

$related = array_values( array_filter( $events, static fn( $item ) => ( $item['id'] ?? '' ) !== $id ) );

get_header();
duy_page_hero(
	$event['n'],
	$event['time'] . ' · ' . $event['type'] . ' · ' . $event['place'],
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Sự kiện', 'url' => duy_route_path( 'su-kien' ) ],
		[ 'label' => $event['n'] ],
	],
	'Event'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap feature-block glass glass-strong">
		<div class="media-art"><?php echo wp_kses_post( duy_image( 'cover_image', $post_id, 'photo-event-workshop.webp', 'Ảnh bìa sự kiện ' . $event['n'] ) ); ?></div>
		<div class="feature-copy">
			<span class="chip"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $event['type'] ); ?></span>
			<h2><?php echo esc_html( $event['n'] ); ?></h2>
			<p><?php esc_html_e( 'Sự kiện được thiết kế để phụ huynh và học sinh nhận thông tin có thể hành động ngay, từ chọn trường đến học bổng và visa.', 'duy-study' ); ?></p>
			<div class="info-list">
				<p><?php echo duy_icon( 'clock' ); ?> <span><?php echo esc_html( $event['time'] ); ?></span></p>
				<p><?php echo duy_icon( 'pin' ); ?> <span><?php echo esc_html( $event['place'] ); ?></span></p>
				<p><?php echo duy_icon( 'user' ); ?> <span><?php echo esc_html( $audience ); ?></span></p>
				<p><?php echo duy_icon( 'award' ); ?> <span><?php echo esc_html( 'Phí tham dự: ' . $fee ); ?></span></p>
			</div>
			<a class="btn btn-primary" href="#event-register"><?php esc_html_e( 'Đăng ký tham dự', 'duy-study' ); ?></a>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap rich-layout">
		<article class="rich-main">
			<?php if ( $speakers ) : ?>
				<section class="rich-section glass glass-strong speakers-feature">
					<span class="eyebrow"><?php esc_html_e( 'Diễn giả', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Người trực tiếp chia sẻ trong sự kiện', 'duy-study' ); ?></h2>
					<div class="speaker-grid">
						<?php foreach ( $speakers as $speaker ) : ?>
							<article class="speaker-card glass">
								<div class="speaker-photo"><?php echo wp_kses_post( duy_media_image_markup( $speaker['photo'], $speaker['name'], '', 'photo-team-office.webp' ) ); ?></div>
								<div class="speaker-info">
									<h3><?php echo esc_html( $speaker['name'] ); ?></h3>
									<p class="muted"><?php echo esc_html( trim( $speaker['role'] . ' · ' . $speaker['org'], ' ·' ) ); ?></p>
									<?php if ( $speaker['bio'] ) : ?>
										<p><?php echo esc_html( $speaker['bio'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $benefits ) : ?>
				<section class="rich-section glass glass-strong">
					<span class="eyebrow"><?php esc_html_e( 'Bạn nhận được gì', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Thông tin đủ rõ để biết bước tiếp theo', 'duy-study' ); ?></h2>
					<div class="guide-cards cols-3">
						<?php foreach ( $benefits as $benefit ) : ?>
							<?php duy_part( 'guide-card', [ 'title' => $benefit['title'] ?? '', 'text' => $benefit['desc'] ?? '', 'icon' => 'sparkles' ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $agenda ) : ?>
				<section class="rich-section glass">
					<h2><?php esc_html_e( 'Agenda / chương trình', 'duy-study' ); ?></h2>
					<div class="guide-steps">
						<?php foreach ( $agenda as $index => $item ) : ?>
							<div class="guide-step glass">
								<span class="num"><?php echo esc_html( $item['time'] ?: sprintf( '%02d', $index + 1 ) ); ?></span>
								<div>
									<h3><?php echo esc_html( $item['item'] ); ?></h3>
									<?php if ( $item['desc'] ) : ?>
										<p><?php echo esc_html( $item['desc'] ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $videos ) : ?>
				<section class="rich-section glass">
					<span class="eyebrow"><?php esc_html_e( 'Video', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Xem thêm về trường và nội dung sự kiện', 'duy-study' ); ?></h2>
					<div class="event-video-grid">
						<?php foreach ( $videos as $index => $video ) : ?>
							<button class="youtube-facade glass <?php echo 0 === $index ? 'event-video-main' : ''; ?>" type="button" data-youtube-id="<?php echo esc_attr( $video['id'] ); ?>" aria-label="<?php echo esc_attr( 'Phát video ' . $video['title'] ); ?>">
								<span class="youtube-thumb">
									<?php echo duy_youtube_thumb_markup( (string) $video['id'], (string) $video['title'] ); ?>
									<span class="vp-play"><?php echo duy_icon( 'play', 'ic-fill' ); ?></span>
								</span>
								<span class="youtube-copy">
									<b><?php echo esc_html( $video['title'] ); ?></b>
									<?php if ( $video['desc'] ) : ?>
										<span><?php echo esc_html( $video['desc'] ); ?></span>
									<?php endif; ?>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $gallery ) : ?>
				<section class="rich-section glass glass-strong">
					<span class="eyebrow"><?php esc_html_e( 'Thư viện ảnh', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Không khí tư vấn và chuẩn bị hồ sơ', 'duy-study' ); ?></h2>
					<div class="event-gallery-grid">
						<?php foreach ( array_values( $gallery ) as $index => $image ) : ?>
							<?php
							$alt = sprintf( 'Ảnh sự kiện %s %d', $event['n'], $index + 1 );
							$src = duy_media_image_url( $image, 'photo-event-workshop.webp' );
							?>
							<button class="event-gallery-item glass" type="button" data-gallery-full="<?php echo esc_url( $src ); ?>" data-gallery-alt="<?php echo esc_attr( $alt ); ?>" aria-label="<?php echo esc_attr( 'Xem lớn ' . $alt ); ?>">
								<?php echo wp_kses_post( duy_media_image_markup( $image, $alt, '', 'photo-event-workshop.webp' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $participating_schools ) : ?>
				<section class="rich-section glass">
					<span class="eyebrow"><?php esc_html_e( 'Trường tham gia', 'duy-study' ); ?></span>
					<h2><?php esc_html_e( 'Trường và đối tác được nhắc trong buổi tư vấn', 'duy-study' ); ?></h2>
					<div class="event-school-grid">
						<?php foreach ( $participating_schools as $school ) : ?>
							<a class="event-school-card glass" href="<?php echo esc_url( duy_school_url( (string) $school['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem trường ' . $school['n'] ); ?>">
								<span class="event-school-logo"><?php echo esc_html( duy_school_acronym( (string) $school['n'] ) ); ?></span>
								<span>
									<b><?php echo esc_html( $school['n'] ); ?></b>
									<small><?php echo esc_html( $school['city'] . ' · ' . $school['c'] ); ?></small>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $who ) : ?>
				<section class="rich-section glass">
					<h2><?php esc_html_e( 'Dành cho ai', 'duy-study' ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $who as $item ) : ?>
							<li><?php echo esc_html( $item['item'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="rich-section" id="event-register">
				<?php duy_part( 'consultation-form', [ 'source' => 'event-' . $id ] ); ?>
			</section>

			<?php if ( $related ) : ?>
				<section class="rich-section">
					<div class="section-head">
						<span class="eyebrow"><?php esc_html_e( 'Sắp tới', 'duy-study' ); ?></span>
						<h2><?php esc_html_e( 'Sự kiện liên quan', 'duy-study' ); ?></h2>
					</div>
					<div class="grid g3">
						<?php foreach ( array_slice( $related, 0, 3 ) as $item ) : ?>
							<?php duy_part( 'card-event', [ 'item' => $item ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</article>
		<aside class="guide-side">
			<div class="side-card glass glass-strong"><h4><?php esc_html_e( 'Giữ chỗ', 'duy-study' ); ?></h4><p class="muted"><?php echo esc_html( $event['time'] . ' · ' . $event['place'] ); ?></p><a class="btn btn-primary" href="#event-register"><?php esc_html_e( 'Đăng ký ngay', 'duy-study' ); ?></a></div>
			<div class="side-card glass"><h4><?php esc_html_e( 'Chuẩn bị trước khi tham dự', 'duy-study' ); ?></h4><ul class="check-list"><li>Bảng điểm hoặc thông tin học lực hiện tại.</li><li>Quốc gia/ngành/trường đang quan tâm.</li><li>Ngân sách dự kiến và mốc nhập học.</li></ul></div>
		</aside>
	</div>
</section>
<?php
get_footer();
