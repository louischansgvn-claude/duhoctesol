<?php
/**
 * Study roadmap hub.
 *
 * @package DUY_Study
 */

$guides = array_values( duy_demo_guides() );
$icons  = [ 'sparkles', 'route', 'book', 'check', 'shield' ];

$guide_hub_items = array_map(
	static function ( array $guide, int $index ) use ( $icons ): array {
		$slug    = (string) ( $guide['slug'] ?? '' );
		$post    = function_exists( 'get_page_by_path' ) ? get_page_by_path( $slug, OBJECT, 'guide' ) : null;
		$post_id = $post ? (int) $post->ID : 0;
		$tasks   = $post_id ? duy_rows( 'hub_tasks', $post_id ) : [];

		if ( $tasks ) {
			$tasks = array_values(
				array_filter(
					array_map(
						static fn( $row ) => (string) ( $row['item'] ?? $row['title'] ?? $row['desc'] ?? '' ),
						$tasks
					)
				)
			);
		} else {
			$tasks = array_values( array_filter( array_map( 'strval', $guide['hub_tasks'] ?? [] ) ) );
		}

		return [
			'number'  => $index + 1,
			'title'   => (string) ( $guide['title'] ?? '' ),
			'outcome' => (string) ( $post_id ? duy_field( 'hub_outcome', $post_id, $guide['outcome'] ?? $guide['lead'] ?? '' ) : ( $guide['outcome'] ?? $guide['lead'] ?? '' ) ),
			'tasks'   => $tasks,
			'icon'    => $icons[ $index ] ?? 'check',
			'url'     => duy_guide_url( $slug ),
		];
	},
	$guides,
	array_keys( $guides )
);

$roadmap_clean_value = null;
$roadmap_clean_value = static function ( $value ) use ( &$roadmap_clean_value ) {
	if ( is_array( $value ) ) {
		return array_map( $roadmap_clean_value, $value );
	}

	if ( ! is_string( $value ) ) {
		return $value;
	}

	$value = (string) preg_replace( '/\s*\{\{TODO[^}]*\}\}/u', '', $value );

	return trim( (string) preg_replace( '/\s{2,}/u', ' ', $value ) );
};
$roadmap_faqs = $roadmap_clean_value( array_slice( duy_demo_faqs(), 0, 4 ) );

get_header();
duy_page_hero(
	'Lộ trình du học',
	'Đi theo từng chặng giúp học sinh và phụ huynh ra quyết định theo dữ kiện, không chạy theo deadline phút cuối.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Lộ trình du học' ],
	],
	'Roadmap'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<?php
		duy_part(
			'feature-block',
			[
				'eyebrow'   => 'Bắt đầu đúng thứ tự',
				'title'     => 'Một bản đồ rõ cho hành trình nhiều quyết định',
				'text'      => 'Từ lý do đi du học đến chuẩn bị hành lý, mỗi chặng có checklist riêng để gia đình biết cần làm gì, khi nào làm và cần xác nhận điểm nào.',
				'image'     => 'photo-student-group.webp',
				'alt'       => 'Minh họa học sinh và phụ huynh xem lộ trình du học',
				'cta_label' => 'Đăng ký tư vấn',
				'cta_url'   => duy_route_path( 'lien-he' ),
				'show_note' => false,
			]
		);
		?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( '5 chặng chính', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Từ định hướng đến ngày lên đường', 'duy-study' ); ?></h2>
		</div>
		<div class="timeline">
			<?php foreach ( $guide_hub_items as $item ) : ?>
				<a class="tl-step glass" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( 'Xem chi tiết ' . $item['title'] ); ?>">
					<span class="dot"><?php echo esc_html( (string) $item['number'] ); ?></span>
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['outcome'] ); ?></p>
					<span class="go"><?php echo duy_icon( 'arrow' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Chi tiết từng chặng', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Mỗi chặng có những đầu việc cần làm rõ', 'duy-study' ); ?></h2>
		</div>
		<div class="guide-cards roadmap-card-grid">
			<?php foreach ( $guide_hub_items as $item ) : ?>
				<a id="<?php echo esc_attr( 'chang-' . $item['number'] ); ?>" class="guide-card roadmap-card glass" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( 'Xem chi tiết ' . $item['title'] ); ?>">
					<div class="gc-top">
						<?php echo duy_icon_tile( $item['icon'] ); ?>
						<span class="step-no"><?php echo esc_html( 'Chặng ' . $item['number'] ); ?></span>
					</div>
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<ul class="check-list">
						<?php foreach ( $item['tasks'] as $task ) : ?>
							<li><?php echo esc_html( $task ); ?></li>
						<?php endforeach; ?>
					</ul>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<?php duy_part( 'pull-quote', [ 'text' => 'Một lộ trình tốt giúp gia đình ra quyết định theo thứ tự, không theo cảm xúc của deadline.' ] ); ?>
		<?php duy_part( 'faq-accordion', [ 'items' => $roadmap_faqs ] ); ?>
	</div>
</section>
<?php
get_footer();
