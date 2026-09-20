<?php
/**
 * Single scholarship template.
 *
 * @package DUY_Study
 */

$id          = (string) duy_route_value( 'id', '' );
$scholarships = duy_items_newest_first( duy_demo_scholarships(), 'scholarship' );
$scholarship = duy_find_by_id( $scholarships, $id );

if ( ! $scholarship ) {
	include duy_template_path( '404.php' );
	return;
}

$post       = function_exists( 'get_page_by_path' ) ? get_page_by_path( $id, OBJECT, 'scholarship' ) : null;
$post_id    = $post ? (int) $post->ID : 0;
$profile    = duy_scholarship_profile_defaults( $scholarship );
$get_field  = static fn( string $field, $fallback = null ) => $post_id ? duy_field( $field, $post_id, $fallback ) : $fallback;
$get_rows   = static function ( string $field, array $fallback = [] ) use ( $post_id ): array {
	$rows = $post_id ? duy_rows( $field, $post_id ) : [];

	return $rows ?: $fallback;
};
$issuer     = (string) $get_field( 'issuer', $profile['issuer'] );
$quota      = (string) $get_field( 'quota', $profile['quota'] );
$intake     = (string) $get_field( 'intake', $profile['intake'] );
$intro      = (string) $get_field( 'intro', $profile['intro'] );
$benefits   = $get_rows( 'benefits', $profile['benefits'] );
$eligibility = $get_rows( 'eligibility', array_map( static fn( $item ) => [ 'item' => $item ], $profile['eligibility'] ) );
$documents  = $get_rows( 'documents', array_map( static fn( $item ) => [ 'item' => $item ], $profile['documents'] ) );
$steps      = $get_rows( 'how_to_steps', $profile['how_to_steps'] );
$programs   = $get_rows( 'applicable_programs', array_map( static fn( $item ) => [ 'item' => $item ], $profile['applicable_programs'] ) );
$related    = array_values(
	array_filter(
		$scholarships,
		static fn( $item ) => ( $item['id'] ?? '' ) !== $id && ( $item['c'] ?? '' ) === ( $scholarship['c'] ?? '' )
	)
);

get_header();
duy_page_hero(
	$scholarship['n'],
	$scholarship['v'] . ' · ' . $scholarship['school'] . ' · Deadline ' . $scholarship['d'],
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Học bổng', 'url' => duy_route_path( 'hoc-bong' ) ],
		[ 'label' => $scholarship['n'] ],
	],
	'Scholarship'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap feature-block glass glass-strong">
		<div class="feature-copy">
			<span class="chip pink"><?php echo duy_icon( 'award' ); ?> <?php echo esc_html( $scholarship['v'] ); ?></span>
			<h2><?php echo esc_html( $scholarship['n'] ); ?></h2>
			<p><?php echo esc_html( $intro ); ?></p>
			<div class="info-list">
				<p><?php echo duy_icon( 'cap' ); ?> <span><?php echo esc_html( $scholarship['school'] . ' · ' . $scholarship['level'] ); ?></span></p>
				<p><?php echo duy_icon( 'globe' ); ?> <span><?php echo esc_html( $scholarship['c'] ); ?></span></p>
				<p><?php echo duy_icon( 'calendar' ); ?> <span><?php echo esc_html( 'Deadline ' . $scholarship['d'] ); ?></span></p>
			</div>
			<div class="hero-cta"><a class="btn btn-primary" href="#scholarship-apply"><?php esc_html_e( 'Ứng tuyển học bổng', 'duy-study' ); ?></a><a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Tư vấn hồ sơ', 'duy-study' ); ?></a></div>
		</div>
		<div class="media-art"><?php echo wp_kses_post( duy_img( duy_country_image( (string) $scholarship['c'] ), 'Ảnh bìa học bổng ' . $scholarship['n'] ) ); ?></div>
	</div>
</section>
<section class="band">
	<div class="wrap quick-facts">
		<div class="stat glass"><b><?php echo esc_html( $scholarship['v'] ); ?></b><span><?php esc_html_e( 'giá trị', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $scholarship['level'] ); ?></b><span><?php esc_html_e( 'bậc học', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $scholarship['c'] ); ?></b><span><?php esc_html_e( 'quốc gia', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $scholarship['d'] ); ?></b><span><?php esc_html_e( 'hạn nộp', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $quota ); ?></b><span><?php esc_html_e( 'số suất', 'duy-study' ); ?></span></div>
	</div>
</section>
<section class="band">
	<div class="wrap rich-layout">
		<article class="rich-main">
			<section class="rich-section glass glass-strong">
				<span class="eyebrow"><?php esc_html_e( 'Giới thiệu', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Học bổng này phù hợp với hồ sơ nào?', 'duy-study' ); ?></h2>
				<p><?php echo esc_html( $intro ); ?></p>
				<p class="muted"><?php echo esc_html( 'Đơn vị cấp: ' . $issuer ); ?></p>
			</section>
			<section class="rich-section glass">
				<h2><?php esc_html_e( 'Giá trị và quyền lợi', 'duy-study' ); ?></h2>
				<div class="guide-cards cols-3">
					<?php foreach ( $benefits as $benefit ) : ?>
						<?php duy_part( 'guide-card', [ 'title' => $benefit['title'] ?? '', 'text' => $benefit['desc'] ?? '', 'icon' => 'award' ] ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="rich-section glass glass-strong">
				<h2><?php esc_html_e( 'Điều kiện ứng tuyển', 'duy-study' ); ?></h2>
				<ul class="check-list"><?php foreach ( $eligibility as $item ) : ?><li><?php echo esc_html( $item['item'] ?? '' ); ?></li><?php endforeach; ?></ul>
			</section>
			<section class="rich-section glass">
				<h2><?php esc_html_e( 'Hồ sơ cần chuẩn bị', 'duy-study' ); ?></h2>
				<ul class="check-list"><?php foreach ( $documents as $item ) : ?><li><?php echo esc_html( $item['item'] ?? '' ); ?></li><?php endforeach; ?></ul>
			</section>
			<section class="rich-section glass glass-strong">
				<h2><?php esc_html_e( 'Quy trình và cách nộp', 'duy-study' ); ?></h2>
				<div class="guide-steps">
					<?php foreach ( $steps as $index => $step ) : ?>
						<div class="guide-step glass"><span class="num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><div><h3><?php echo esc_html( $step['title'] ?? '' ); ?></h3><p><?php echo esc_html( $step['desc'] ?? '' ); ?></p></div></div>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="rich-section glass">
				<h2><?php esc_html_e( 'Trường/ngành áp dụng', 'duy-study' ); ?></h2>
				<ul class="check-list"><?php foreach ( $programs as $item ) : ?><li><?php echo esc_html( $item['item'] ?? '' ); ?></li><?php endforeach; ?></ul>
				<p class="muted"><?php echo esc_html( 'Kỳ nhập học: ' . $intake ); ?></p>
			</section>
			<?php if ( $related ) : ?>
				<section class="rich-section">
					<div class="section-head"><span class="eyebrow"><?php esc_html_e( 'Liên quan', 'duy-study' ); ?></span><h2><?php esc_html_e( 'Học bổng cùng quốc gia', 'duy-study' ); ?></h2></div>
					<div class="grid g3"><?php foreach ( array_slice( $related, 0, 3 ) as $item ) : ?><?php duy_part( 'card-scholarship', [ 'item' => $item ] ); ?><?php endforeach; ?></div>
				</section>
			<?php endif; ?>
			<section class="rich-section" id="scholarship-apply"><?php duy_part( 'consultation-form', [ 'source' => 'scholarship-' . $id ] ); ?></section>
		</article>
		<aside class="guide-side">
			<div class="side-card glass glass-strong"><h4><?php esc_html_e( 'Mốc quan trọng', 'duy-study' ); ?></h4><div class="info-list"><p><?php echo duy_icon( 'calendar' ); ?> <span><?php echo esc_html( $scholarship['d'] ); ?></span></p><p><?php echo duy_icon( 'award' ); ?> <span><?php echo esc_html( $scholarship['v'] ); ?></span></p><p><?php echo duy_icon( 'cap' ); ?> <span><?php echo esc_html( $scholarship['school'] ); ?></span></p></div><a class="btn btn-primary" href="#scholarship-apply"><?php esc_html_e( 'Bắt đầu hồ sơ', 'duy-study' ); ?></a></div>
			<div class="side-card glass"><h4><?php esc_html_e( '3 văn phòng', 'duy-study' ); ?></h4><div class="info-list"><?php foreach ( duy_offices() as $office ) : ?><p><?php echo duy_icon( 'pin' ); ?> <span><?php echo esc_html( $office['name'] . ' · ' . $office['address'] ); ?></span></p><?php endforeach; ?></div></div>
		</aside>
	</div>
</section>
<?php
get_footer();
