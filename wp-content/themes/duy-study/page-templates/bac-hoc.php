<?php
/**
 * Country level landing template.
 *
 * @package DUY_Study
 */

$country_slug = (string) duy_route_value( 'slug', '' );
$level_slug   = (string) duy_route_value( 'level', '' );
$country      = duy_country_by_slug( $country_slug );
$level        = duy_mockup_v2_level_by_slug( $level_slug );
$bac_data     = duy_mockup_v2_bac_data();
$level_data   = $bac_data[ $level_slug ] ?? null;

if ( ! $country || ! $level || ! is_array( $level_data ) ) {
	include duy_template_path( '404.php' );
	return;
}

$country_name               = duy_public_text( (string) ( $country['name'] ?? '' ) );
$level_data['countryNote']  = str_replace( '{country}', $country_name, (string) ( $level_data['countryNote'] ?? '' ) );
$country                    = duy_public_value( $country );
$level_data                 = duy_public_value( $level_data );
$levels                     = duy_mockup_v2_levels();
$other_levels               = array_filter(
	$levels,
	static fn( $item, $slug ) => $slug !== $level_slug,
	ARRAY_FILTER_USE_BOTH
);
$page_title                 = 'Du học ' . $country_name . ' bậc ' . $level['name'];
$timeline                   = array_values( (array) ( $level_data['timeline'] ?? [] ) );

// Trường tương ứng với quốc gia + bậc học, sắp theo bảng chữ cái.
$accepted_levels = duy_level_school_labels()[ $level_slug ] ?? [];
$level_schools   = array_values(
	array_filter(
		duy_demo_schools(),
		static function ( $school ) use ( $country_name, $accepted_levels ) {
			if ( (string) ( $school['c'] ?? '' ) !== $country_name ) {
				return false;
			}
			if ( ! $accepted_levels ) {
				return true;
			}
			return in_array( (string) ( $school['level'] ?? '' ), $accepted_levels, true );
		}
	)
);
usort( $level_schools, static fn( $a, $b ) => strnatcasecmp( (string) ( $a['n'] ?? '' ), (string) ( $b['n'] ?? '' ) ) );
$level_schools = duy_public_value( $level_schools );

// Phân trang danh sách trường: trang bậc học của Mỹ có ~129 trường, render hết
// khiến HTML nặng 229 KB và DOM rất lớn trên điện thoại.
$level_page_query = duy_finder_query();
$level_base_path  = 'quoc-gia/' . $country_slug . '/' . $level_slug;
$level_page_data  = duy_paginate( $level_schools, (int) $level_page_query['page'] );
$level_schools    = $level_page_data['items'];

duy_seo_pagination(
	[
		'base'      => $level_base_path,
		'query'     => $level_page_query,
		'page_data' => $level_page_data,
	]
);

get_header();
duy_page_hero(
	$page_title,
	(string) ( $level_data['tagline'] ?? '' ),
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Quốc gia', 'url' => duy_route_path( 'quoc-gia' ) ],
		[ 'label' => (string) $country['title'], 'url' => duy_country_url_from_slug( $country_slug ) ],
		[ 'label' => 'Bậc ' . $level['name'] ],
	],
	'Bậc học'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<?php
		duy_part(
			'feature-block',
			[
				'eyebrow'   => 'Tổng quan bậc học',
				'title'     => (string) $level['name'] . ' tại ' . $country_name,
				'text'      => (string) ( $level_data['overview'] ?? '' ),
				'image'     => duy_country_image( $country_name ),
				'alt'       => 'Minh họa du học ' . $country_name . ' bậc ' . $level['name'],
				'cta_label' => 'Tư vấn bậc ' . $level['name'],
				'cta_url'   => duy_route_path( 'lien-he' ),
			]
		);
		?>
		<?php if ( ! empty( $level_data['countryNote'] ) ) : ?>
			<div style="margin-top:var(--gap)">
				<?php duy_part( 'pull-quote', [ 'text' => (string) $level_data['countryNote'] ] ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="results-top">
			<h2><?php echo esc_html( 'Trường tại ' . $country_name . ' — bậc ' . $level['name'] ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $country_slug ) ); ?>"><?php esc_html_e( 'Xem tất cả trường', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<p class="qg-desc" style="max-width:70ch;margin:.2rem 0 1.3rem">
			<?php echo esc_html( 'Trường tiêu biểu tại ' . $country_name . ' phù hợp bậc ' . $level['name'] . ', sắp xếp theo bảng chữ cái để bạn dễ tra cứu.' ); ?>
			<?php if ( $level_page_data['pages'] > 1 ) : ?>
				<strong><?php echo esc_html( 'Đang xem ' . duy_result_range_text( $level_page_data ) . '.' ); ?></strong>
			<?php endif; ?>
		</p>
		<?php if ( $level_schools ) : ?>
			<div class="grid g3">
				<?php foreach ( $level_schools as $school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $school ] ); ?>
				<?php endforeach; ?>
			</div>
			<?php echo wp_kses_post( duy_pagination_markup( $level_base_path, $level_page_query, $level_page_data ) ); ?>
		<?php else : ?>
			<div class="empty-state glass">
				<h3><?php echo esc_html( 'Danh sách trường bậc ' . $level['name'] . ' tại ' . $country_name . ' đang được cập nhật' ); ?></h3>
				<p class="muted"><?php esc_html_e( 'Đăng ký tư vấn để Ban Du học Hội TESOL TP.HCM gợi ý trường phù hợp hồ sơ, hoặc xem toàn bộ trường của quốc gia này.', 'duy-study' ); ?></p>
				<div class="hero-cta" style="margin-top:.8rem">
					<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Nhận gợi ý trường', 'duy-study' ); ?></a>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $country_slug ) ); ?>"><?php echo esc_html( 'Xem trường tại ' . $country_name ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Phù hợp với ai', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Ai nên chọn bậc ' . $level['name'] ); ?></h2>
			<ul class="check-list">
				<?php foreach ( (array) ( $level_data['whoFor'] ?? [] ) as $item ) : ?>
					<li><?php echo esc_html( (string) $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Điều kiện đầu vào', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Checklist hồ sơ cần chuẩn bị', 'duy-study' ); ?></h2>
			<ul class="check-list">
				<?php foreach ( (array) ( $level_data['requirements'] ?? [] ) as $item ) : ?>
					<li><?php echo esc_html( (string) $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<?php if ( $timeline ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="section-head">
				<span class="eyebrow"><?php esc_html_e( 'Lộ trình chuẩn bị', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( 'Các bước chuẩn bị bậc ' . $level['name'] ); ?></h2>
			</div>
			<div class="guide-steps level-timeline">
				<?php foreach ( $timeline as $index => $item ) : ?>
					<div class="guide-step glass">
						<span class="num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
						<div>
							<h3><?php echo esc_html( (string) ( $item['step'] ?? '' ) ); ?></h3>
							<p><?php echo esc_html( (string) ( $item['detail'] ?? '' ) ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Chi phí & lưu ý', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Điểm cần tính trong ngân sách', 'duy-study' ); ?></h2>
			<ul class="check-list">
				<?php foreach ( (array) ( $level_data['costsNotes'] ?? [] ) as $item ) : ?>
					<li><?php echo esc_html( (string) $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Lộ trình tiếp theo', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Sau bậc ' . $level['name'] ); ?></h2>
			<p class="muted"><?php echo esc_html( (string) ( $level_data['pathway'] ?? '' ) ); ?></p>
			<div class="level-links">
				<?php foreach ( $other_levels as $slug => $item ) : ?>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_country_level_url( $country_slug, (string) $slug ) ); ?>"><?php echo esc_html( $item['name'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php $level_faqs = function_exists( 'duy_faq_items' ) ? duy_faq_items( $country_slug, $level_slug ) : []; ?>
<?php if ( $level_faqs ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="section-head" style="text-align:left;max-width:none;margin:0 0 1rem">
				<span class="eyebrow"><?php esc_html_e( 'Câu hỏi thường gặp', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( 'Câu hỏi thường gặp về du học ' . $country_name . ' bậc ' . $level['name'] ); ?></h2>
			</div>
			<?php duy_part( 'faq-accordion', [ 'items' => $level_faqs ] ); ?>
		</div>
	</section>
<?php endif; ?>

<section class="band">
	<div class="wrap grid g2" style="align-items:start">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM hỗ trợ', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Đồng hành hồ sơ ' . $level['name'] . ' tại ' . $country_name ); ?></h2>
			<ul class="check-list">
				<?php foreach ( (array) ( $level_data['duyStudySupport'] ?? [] ) as $item ) : ?>
					<li><?php echo esc_html( (string) $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php duy_part( 'consultation-form', [ 'source' => 'country-level-' . $country_slug . '-' . $level_slug ] ); ?>
	</div>
</section>
<?php
get_footer();
