<?php
/**
 * About page.
 *
 * @package DUY_Study
 */

$about     = duy_about_defaults();
$strengths = duy_rows( 'about_strengths' ) ?: $about['strengths'];
$services  = duy_rows( 'about_services' ) ?: $about['services'];
$process   = duy_rows( 'about_process' ) ?: $about['process'];
$team      = duy_rows( 'about_team' ) ?: $about['team'];
$partners  = duy_rows( 'about_partners' ) ?: array_map(
	static fn( $item ) => [ 'item' => $item ],
	$about['partners']
);
$stats     = duy_rows( 'home_stats' ) ?: [
	[ 'value' => '5', 'label' => 'quốc gia trọng tâm' ],
	[ 'value' => '3', 'label' => 'văn phòng hỗ trợ' ],
	[ 'value' => '1-1', 'label' => 'lộ trình tư vấn' ],
];

get_header();
duy_page_hero(
	'Về chúng tôi',
	'Ban Du học Hội TESOL TP.HCM đồng hành cùng học sinh và phụ huynh từ định hướng ban đầu đến ngày lên đường.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Về chúng tôi' ],
	],
	'About'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Tư vấn bằng lộ trình, không chỉ bằng danh sách trường', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Mỗi hồ sơ được nhìn như một hành trình riêng: mục tiêu học tập, năng lực hiện tại, ngân sách gia đình, deadline và mức sẵn sàng của học sinh.', 'duy-study' ); ?></p>
				<?php duy_part( 'pull-quote', [ 'text' => 'Tư vấn tốt không phải là nói trường nào nổi tiếng nhất, mà là giúp gia đình thấy rõ lựa chọn nào phù hợp nhất.' ] ); ?>
			</div>
			<?php duy_part( 'image-cluster', [ 'alt' => 'Đội ngũ và văn phòng Ban Du học Hội TESOL TP.HCM', 'images' => [ 'photo-team-office.webp', 'photo-library-study.webp', 'photo-campus-library.webp' ], 'stat' => [ '3', 'văn phòng hỗ trợ gia đình' ] ] ); ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap guide-cards cols-3">
		<?php foreach ( $strengths as $strength ) : ?>
			<?php duy_part( 'guide-card', [ 'title' => $strength['title'] ?? '', 'text' => $strength['desc'] ?? '', 'icon' => $strength['icon'] ?? 'target' ] ); ?>
		<?php endforeach; ?>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:stretch">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Sứ mệnh', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Giúp gia đình ra quyết định du học rõ ràng hơn', 'duy-study' ); ?></h2>
			<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM biến thông tin rời rạc về trường, học bổng, chi phí và visa thành một lộ trình có thứ tự ưu tiên, mốc thời gian và trách nhiệm cụ thể.', 'duy-study' ); ?></p>
		</div>
		<div class="glass single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Tầm nhìn', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Mỗi học sinh có một bản đồ học tập phù hợp', 'duy-study' ); ?></h2>
			<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM hướng đến trải nghiệm tư vấn gần gũi, minh bạch và đủ dữ kiện để học sinh tự tin bước vào môi trường quốc tế.', 'duy-study' ); ?></p>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap stats glass glass-strong">
		<?php foreach ( array_slice( $stats, 0, 4 ) as $stat ) : ?>
			<div class="stat"><b><?php echo esc_html( $stat['value'] ?? '' ); ?></b><span><?php echo esc_html( $stat['label'] ?? '' ); ?></span></div>
		<?php endforeach; ?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Dịch vụ', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Hai nhóm đồng hành chính', 'duy-study' ); ?></h2>
		</div>
		<div class="grid g2">
			<?php foreach ( $services as $service ) : ?>
				<div class="glass glass-strong single-hero-card">
					<?php echo duy_icon_tile( $service['icon'] ?? 'route' ); ?>
					<h3><?php echo esc_html( $service['title'] ?? '' ); ?></h3>
					<p class="muted"><?php echo esc_html( $service['desc'] ?? '' ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap guide-cards cols-3">
		<?php
		foreach (
			[
				[ 'title' => 'Rõ ràng', 'desc' => 'Mỗi bước tư vấn đều có lý do, dữ liệu và điều kiện cần xác nhận.', 'icon' => 'target' ],
				[ 'title' => 'Tận tâm', 'desc' => 'Học sinh và phụ huynh được giải thích kỹ để không bị cuốn theo deadline.', 'icon' => 'heart' ],
				[ 'title' => 'Thực tế', 'desc' => 'Ưu tiên lựa chọn phù hợp hồ sơ, ngân sách và khả năng duy trì sau khi nhập học.', 'icon' => 'shield' ],
			] as $value
		) :
			duy_part( 'guide-card', [ 'title' => $value['title'], 'text' => $value['desc'], 'icon' => $value['icon'] ] );
		endforeach;
		?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Quy trình', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM đồng hành như thế nào', 'duy-study' ); ?></h2>
		</div>
		<div class="guide-steps">
			<?php foreach ( $process as $index => $step ) : ?>
				<div class="guide-step glass">
					<span class="num"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div>
						<h3><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $step['desc'] ?? '' ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Đội ngũ', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Những người theo sát hồ sơ của gia đình', 'duy-study' ); ?></h2>
		</div>
		<div class="team-grid">
			<?php foreach ( $team as $member ) : ?>
				<div class="team-card glass">
					<?php
					$initial = function_exists( 'mb_substr' )
						? mb_substr( (string) ( $member['name'] ?? 'D' ), 0, 1 )
						: substr( (string) ( $member['name'] ?? 'D' ), 0, 1 );
					?>
					<div class="avatar"><?php echo esc_html( $initial ); ?></div>
					<h3><?php echo esc_html( $member['name'] ?? '' ); ?></h3>
					<p class="muted"><?php echo esc_html( ( $member['role'] ?? '' ) . ' · ' . ( $member['office'] ?? '' ) ); ?></p>
					<p><?php echo esc_html( $member['exp'] ?? '' ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Đối tác & thành tựu', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Công bố có kiểm chứng', 'duy-study' ); ?></h2>
			<ul class="check-list">
				<?php foreach ( $partners as $item ) : ?>
					<li><?php echo esc_html( $item['item'] ?? $item['title'] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="glass single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Phụ huynh & học sinh', 'duy-study' ); ?></span>
			<?php
			foreach ( array_slice( duy_demo_pairs(), 0, 2 ) as $pair ) :
				duy_part( 'pull-quote', [ 'text' => $pair['quote'] ] );
			endforeach;
			?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<?php duy_part( 'consultation-form', [ 'source' => 'about' ] ); ?>
		<?php
		$about_meet = array_values( (array) duy_option( 'about_meet_gallery', [] ) );
		if ( ! $about_meet ) {
			$about_meet = [ 'photo-event-workshop.webp', 'photo-student-group.webp', 'photo-team-office.webp', 'photo-classroom.webp' ];
		}
		?>
		<div class="glass single-hero-card meet-gallery">
			<span class="eyebrow"><?php esc_html_e( 'Gặp gỡ & sự kiện', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Khoảnh khắc đồng hành cùng Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Hình ảnh gặp gỡ đối tác và các sự kiện tư vấn du học của Ban Du học Hội TESOL TP.HCM.', 'duy-study' ); ?></p>
			<div class="meet-slider" tabindex="0" aria-label="<?php esc_attr_e( 'Slideshow hình ảnh sự kiện Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?>">
				<?php foreach ( $about_meet as $index => $meet_image ) : ?>
					<figure class="meet-slide">
						<?php echo wp_kses_post( duy_media_image_markup( $meet_image, sprintf( 'Hình ảnh sự kiện Ban Du học Hội TESOL TP.HCM %d', $index + 1 ), '', 'photo-event-workshop.webp' ) ); ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
