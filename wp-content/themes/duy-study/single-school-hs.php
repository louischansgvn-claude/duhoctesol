<?php
/**
 * Universal single-school template — blog/article layout.
 *
 * Renders EVERY school (THPT import, university/college posts, and code-only
 * demo schools) in one consistent single-column article. Each content block is
 * assembled with a source fallback chain:
 *   1) THPT import meta  `_duy_hs_*`
 *   2) University Carbon fields  (duy_field / duy_rows)
 *   3) demo-array values  (`$demo` from duy_demo_schools())
 * so old + new + future schools all use the same presentation.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demo = (array) get_query_var( 'hs_school' );
$id   = (int) get_query_var( 'hs_post_id' );
$id   = $id ?: ( isset( $demo['hs_post'] ) ? (int) $demo['hs_post'] : 0 );

if ( ! $demo && ! $id ) {
	include duy_template_path( '404.php' );
	return;
}

// --- SEO article model (namphong-style import) ------------------------------
// content_html is a complete, public-clean article (facts, sections, images).
// Render it as-is; no field assembly.
if ( $id && get_post_meta( $id, '_duy_seo_article', true ) ) {
	$title = get_the_title( $id );
	$state = (string) get_post_meta( $id, '_duy_hs_state', true );
	$badge = (string) get_post_meta( $id, '_duy_hs_badge', true );

	// Country context (country-aware). US imports keep the original Mỹ/USA/my
	// display exactly; Canada/Australia imports resolve from their country term.
	$country_slugs = wp_get_object_terms( $id, 'country', [ 'fields' => 'slugs' ] );
	$country_slug  = ( ! is_wp_error( $country_slugs ) && $country_slugs ) ? (string) $country_slugs[0] : 'my';
	$country_map   = [
		'my'          => [ 'name' => 'Mỹ', 'loc' => 'USA' ],
		'canada'      => [ 'name' => 'Canada', 'loc' => 'Canada' ],
		'uc'          => [ 'name' => 'Úc', 'loc' => 'Australia' ],
		'anh'         => [ 'name' => 'Anh', 'loc' => 'United Kingdom' ],
		'ha-lan'      => [ 'name' => 'Hà Lan', 'loc' => 'Netherlands' ],
		'new-zealand' => [ 'name' => 'New Zealand', 'loc' => 'New Zealand' ],
		'malaysia'    => [ 'name' => 'Malaysia', 'loc' => 'Malaysia' ],
		'thuy-sy'     => [ 'name' => 'Thụy Sỹ', 'loc' => 'Switzerland' ],
		'singapore'   => [ 'name' => 'Singapore', 'loc' => 'Singapore' ],
		'duc'         => [ 'name' => 'Đức', 'loc' => 'Germany' ],
		'philippines' => [ 'name' => 'Philippines', 'loc' => 'Philippines' ],
		'tho-nhi-ky'  => [ 'name' => 'Thổ Nhĩ Kỳ', 'loc' => 'Türkiye' ],
		'han-quoc'    => [ 'name' => 'Hàn Quốc', 'loc' => 'South Korea' ],
	];
	$cc            = $country_map[ $country_slug ] ?? [ 'name' => 'Mỹ', 'loc' => 'USA' ];
	$country_name  = $cc['name'];
	$country_loc   = $cc['loc'];

	// Study-level context: THPT (default) vs college/university. Postsecondary
	// posts carry `_duy_ps_level_slug` (US) or `_duy_caau_level_slug` (Canada/AU)
	// = cao-dang|dai-hoc; THPT posts default to the THPT presentation.
	$ps_level_slug = (string) (
		get_post_meta( $id, '_duy_othc_level_slug', true )
			?: get_post_meta( $id, '_duy_caau_level_slug', true )
			?: get_post_meta( $id, '_duy_ps_level_slug', true )
	);
	if ( 'anh-ngu' === $ps_level_slug ) {
		$level_label = 'Anh ngữ';
		$level_path  = '/quoc-gia/' . $country_slug . '/anh-ngu/';
	} elseif ( 'cao-dang' === $ps_level_slug || 'dai-hoc' === $ps_level_slug ) {
		$level_label = function_exists( 'duy_ps_level_label' ) ? duy_ps_level_label( $ps_level_slug ) : 'Đại học';
		$level_path  = '/quoc-gia/' . $country_slug . '/' . $ps_level_slug . '/';
	} else {
		$level_label = 'THPT';
		$level_path  = '/quoc-gia/' . $country_slug . '/thpt/';
	}

	get_header();
	duy_page_hero(
		$title,
		trim( $state ? $state . ', ' . $country_loc . ' · ' . $level_label : $level_label . ' ' . $country_name ),
		[
			[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
			[ 'label' => $level_label . ' ' . $country_name, 'url' => duy_url( $level_path ) ],
			[ 'label' => $title ],
		],
		'School'
	);
	?>
	<section class="band" style="padding-top:1.25rem">
		<article class="wrap school-article seo-article">
			<div class="sa-meta">
				<span class="sa-chip"><?php echo duy_icon( 'globe' ); ?> <?php echo esc_html( $country_name . ' · ' . $level_label ); ?></span>
				<?php if ( $state ) : ?><span class="sa-loc"><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $state . ', ' . $country_loc ); ?></span><?php endif; ?>
				<?php if ( $badge ) : ?><span class="sa-badge"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
				<span class="sa-updated"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( 'Cập nhật ' . get_post_modified_time( 'd/m/Y', false, $id ) ); ?></span>
			</div>
			<div class="seo-body"><?php echo wp_kses_post( get_post_field( 'post_content', $id ) ); ?></div>
			<?php
			$related_schools = duy_related_school_cards( $country_name, $level_label, (string) get_post_field( 'post_name', $id ), 3 );
			if ( $related_schools ) :
				?>
				<h2><?php echo esc_html( 'Trường ' . $level_label . ' khác tại ' . $country_name ); ?></h2>
				<div class="grid g3">
					<?php foreach ( $related_schools as $related_school ) : ?>
						<?php duy_part( 'card-school', [ 'item' => $related_school ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="sa-consult" id="school-consult">
				<?php duy_part( 'consultation-form', [ 'source' => 'truong-' . ( $demo['id'] ?? $id ) ] ); ?>
			</div>
		</article>
	</section>
	<?php
	get_footer();
	return;
}

// --- Source readers ---------------------------------------------------------
$has_hs = $id && '' !== (string) get_post_meta( $id, '_duy_hs_school_id', true ); // THPT import model?
$m  = static fn( string $k ) => $id ? (string) get_post_meta( $id, '_duy_hs_' . $k, true ) : '';     // THPT meta
$cf = static fn( string $k, string $fb = '' ) => $id && function_exists( 'duy_field' ) ? (string) duy_field( $k, $id, $fb ) : $fb; // Carbon simple
$cr = static fn( string $k ) => $id && function_exists( 'duy_rows' ) ? (array) duy_rows( $k, $id ) : []; // Carbon rows
$d  = static fn( string $k, string $fb = '' ) => (string) ( $demo[ $k ] ?? $fb );                    // demo array
$clean = static fn( string $s ) => function_exists( 'duy_public_text' ) ? (string) duy_public_text( $s ) : $s; // strip
$first = static function ( ...$vals ) {
	foreach ( $vals as $v ) {
		if ( '' !== trim( (string) $v ) ) {
			return (string) $v;
		}
	}
	return '';
};

$fee_fmt = static function ( $v ): string {
	$n = (int) preg_replace( '/[^0-9]/', '', (string) $v );
	return $n > 0 ? number_format( $n, 0, ',', '.' ) . ' USD' : '';
};

// --- Identity ---------------------------------------------------------------
$name         = $id ? get_the_title( $id ) : $d( 'n' );
$country_name = $d( 'c', 'Mỹ' );
$level_label  = $has_hs ? 'THPT' : $clean( $d( 'level', 'Đại học' ) );
$state        = $first( $m( 'state' ), $d( 'city' ) );
$website      = $first( $m( 'website' ), $cf( 'website' ) );
$full_name    = $first( $m( 'full_name' ), $cf( 'full_name' ) );

$hero = $clean( duy_hs_public_text( $first( get_post_field( 'post_excerpt', $id ), $m( 'hero' ), $d( 'desc' ) ) ) );
$profile_text = duy_hs_public_text( $first( (string) get_post_field( 'post_content', $id ), $m( 'profile' ) ) );

// --- Quick facts (Vietnamised, non-empty) -----------------------------------
$facts = array_filter(
	[
		'Tên đầy đủ'     => $full_name && $full_name !== $name ? $full_name : '',
		'Bang / Thành phố' => $state,
		'Quốc gia'       => $country_name,
		'Bậc học'        => $level_label,
		'Ngành trọng tâm' => $has_hs ? '' : $clean( $d( 'major' ) ),
		'Loại trường'    => $has_hs ? duy_hs_vi( 'school_type', $m( 'school_type' ) ) : $clean( $cf( 'type' ) ),
		'Hình thức'      => duy_hs_vi( 'category', $m( 'category' ) ),
		'Lớp nhận'       => $has_hs ? duy_hs_grades( $m( 'grades' ) ) : '',
		'Năm thành lập'  => $first( duy_hs_founded_year( $m( 'founded' ) ), $clean( $cf( 'founded' ) ) ),
		'Quy mô sinh viên' => $has_hs ? '' : $clean( $cf( 'students' ) ),
		'Tôn giáo'       => duy_hs_vi( 'religion', $m( 'religion' ) ),
		'Bằng 1 năm'     => duy_hs_vi( 'one_year_diploma', $m( 'one_year_diploma' ) ),
		'Đánh giá Niche' => $m( 'niche' ),
		'Xếp hạng'       => $has_hs ? '' : $clean( $d( 'r' ) ),
	],
	static fn( $v ) => '' !== trim( (string) $v )
);

// --- Highlight bullets -------------------------------------------------------
$why_items = duy_hs_public_list( $m( 'why_choose' ) );
if ( ! $why_items ) {
	foreach ( $cr( 'why' ) as $w ) {
		$t = trim( trim( (string) ( $w['title'] ?? '' ) ) . ( ! empty( $w['desc'] ) ? ': ' . trim( (string) $w['desc'] ) : '' ), ': ' );
		if ( '' !== $t ) {
			$why_items[] = $clean( $t );
		}
	}
}

// --- Campus ------------------------------------------------------------------
$campus_text = duy_hs_public_text( $m( 'campus' ) );
if ( '' === $campus_text ) {
	$parts = [];
	foreach ( $cr( 'campuses' ) as $c ) {
		$parts[] = trim( trim( (string) ( $c['name'] ?? '' ) . ' — ' . (string) ( $c['location'] ?? '' ), ' —' ) . '. ' . (string) ( $c['focus'] ?? '' ) );
	}
	$campus_text = $clean( trim( implode( "\n", array_filter( $parts ) ) ) );
}

// --- Programs / Admissions ---------------------------------------------------
$programs = duy_hs_public_text( $m( 'programs' ) );
if ( '' === $programs ) {
	$plist = [];
	foreach ( [ 'programs_ug' => 'Đại học', 'programs_pg' => 'Sau đại học', 'programs_pathway' => 'Pathway' ] as $k => $lbl ) {
		foreach ( $cr( $k ) as $row ) {
			$plist[] = trim( trim( (string) ( $row['name'] ?? '' ) ) . ( ! empty( $row['desc'] ) ? ' — ' . trim( (string) $row['desc'] ) : '' ) );
		}
	}
	if ( ! $plist && ! empty( $demo['programs'] ) ) {
		$plist = array_map( 'strval', (array) $demo['programs'] );
	}
	$programs = $clean( implode( "\n", array_filter( $plist ) ) );
}

$admissions = duy_hs_public_text( $m( 'admissions' ) );
if ( '' === $admissions ) {
	$admissions = $clean( trim( implode( "\n", array_filter( [ $cf( 'entry_ug' ), $cf( 'entry_pg' ), $cf( 'intakes' ) ] ) ) ) );
}

// --- Fees --------------------------------------------------------------------
$fee_rows = array_filter(
	[
		'Kỳ Spring — trạng thái'       => $m( 'spring_status' ),
		'Kỳ Spring — phí chương trình' => $fee_fmt( $m( 'spring_program_fee' ) ),
		'Kỳ Fall — trạng thái'         => $m( 'fall_status' ),
		'Kỳ Fall — phí chương trình'   => $fee_fmt( $m( 'fall_program_fee' ) ),
		'Phí ghi danh (app fee)'       => $fee_fmt( $m( 'app_fee' ) ),
		'Phí thấp nhất'                => $fee_fmt( $m( 'lowest_program_fee' ) ),
		'Ghi chú ưu đãi'               => $m( 'discount_note' ),
	],
	static fn( $v ) => '' !== trim( (string) $v )
);
if ( ! $fee_rows ) {
	foreach ( $cr( 'tuition' ) as $t ) {
		$lvl = trim( (string) ( $t['level'] ?? '' ) );
		$val = trim( trim( (string) ( $t['local'] ?? '' ) ) . ' ' . trim( (string) ( $t['vnd'] ?? '' ) ) );
		if ( '' !== $lvl && '' !== $val ) {
			$fee_rows[ $clean( $lvl ) ] = $clean( $val );
		}
	}
}
$fee_hero = $first( $fee_fmt( $m( 'lowest_program_fee' ) ), $clean( $d( 'fee' ) ) );

// --- English / Scholarship / Career -----------------------------------------
$english_txt = duy_hs_public_text( trim( $m( 'english_summary' ) . "\n" . $m( 'english' ) ) );
if ( '' === $english_txt ) {
	$e = [];
	foreach ( $cr( 'english' ) as $r ) {
		$e[] = trim( trim( (string) ( $r['test'] ?? '' ) ) . ( ! empty( $r['score'] ) ? ': ' . trim( (string) $r['score'] ) : '' ), ': ' );
	}
	$english_txt = $clean( implode( "\n", array_filter( $e ) ) );
}

$scholarship = duy_hs_public_text( $m( 'scholarship_note' ) );

$career = duy_hs_public_text( $m( 'career_note' ) );
if ( '' === $career ) {
	$career = $clean( trim( implode( "\n", array_filter( [ $cf( 'career_stats' ), $cf( 'partners' ), $cf( 'visa_note' ) ] ) ) ) );
}

// --- Related scholarships + logo --------------------------------------------
$related_scholarships = array_values(
	array_filter(
		function_exists( 'duy_demo_scholarships' ) ? duy_demo_scholarships() : [],
		static fn( $item ) => ( $item['c'] ?? '' ) === $country_name
	)
);
$has_scholarship = ( '' !== $scholarship ) || $related_scholarships;

$logo_id  = $id ? get_post_thumbnail_id( $id ) : 0;
$logo_url = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'medium_large' ) : '';
$logo_alt = $logo_id ? (string) get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) : '';
$logo_bg  = $id ? ( (string) get_post_meta( $id, '_duy_hs_thumb_bg', true ) ?: 'light' ) : 'light';

$prose_section = static function ( string $title, string $text ): void {
	if ( '' === trim( $text ) ) {
		return;
	}
	echo '<h2>' . esc_html( $title ) . '</h2>';
	echo '<div class="sa-prose">' . wp_kses_post( wpautop( $text ) ) . '</div>';
};

get_header();
duy_page_hero(
	$name,
	trim( implode( ' · ', array_filter( [ $facts['Loại trường'] ?? '', $state ? $state . ', ' . $country_name : $country_name, $level_label ] ) ) ),
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Tìm trường', 'url' => duy_route_path( 'truong' ) ],
		[ 'label' => $name ],
	],
	'School'
);
?>
<section class="band" style="padding-top:1.25rem">
	<article class="wrap school-article">

		<div class="sa-meta">
			<span class="sa-chip"><?php echo duy_icon( 'globe' ); ?> <?php echo esc_html( $country_name . ' · ' . $level_label ); ?></span>
			<?php if ( $state ) : ?>
				<span class="sa-loc"><?php echo duy_icon( 'pin' ); ?> <?php echo esc_html( $state . ', ' . $country_name ); ?></span>
			<?php endif; ?>
			<?php if ( $has_scholarship ) : ?>
				<span class="sa-badge"><?php esc_html_e( 'Đang có học bổng', 'duy-study' ); ?></span>
			<?php endif; ?>
			<?php if ( $id ) : ?>
				<span class="sa-updated"><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( 'Cập nhật ' . get_post_modified_time( 'd/m/Y', false, $id ) ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $hero ) : ?>
			<p class="sa-lead"><?php echo esc_html( $hero ); ?></p>
		<?php endif; ?>

		<hr class="sa-div">

		<div class="sa-idblock">
			<?php if ( $logo_url ) : ?>
				<div class="sa-logo<?php echo 'dark' === $logo_bg ? ' dark' : ''; ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo_alt ?: ( 'Logo ' . $name ) ); ?>" loading="eager" decoding="async">
				</div>
			<?php else : ?>
				<div class="sa-logo"><?php echo wp_kses_post( duy_img( duy_country_image( $country_name ), 'Ảnh minh họa ' . $name ) ); ?></div>
			<?php endif; ?>
			<div class="sa-facts">
				<?php foreach ( $facts as $label => $value ) : ?>
					<p><b><?php echo esc_html( $label ); ?>:</b> <?php echo esc_html( $value ); ?></p>
				<?php endforeach; ?>
				<?php if ( $website ) : ?>
					<p><b><?php esc_html_e( 'Website', 'duy-study' ); ?>:</b> <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $website ); ?></a></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $why_items ) : ?>
			<h2><?php esc_html_e( 'Điểm nổi bật', 'duy-study' ); ?></h2>
			<ul class="sa-list">
				<?php foreach ( $why_items as $why ) : ?>
					<li><?php echo esc_html( $why ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php $prose_section( 'Giới thiệu về trường', $profile_text ); ?>

		<?php if ( $campus_text ) : ?>
			<h2><?php esc_html_e( 'Cơ sở vật chất', 'duy-study' ); ?></h2>
			<figure class="sa-figure"><?php echo wp_kses_post( duy_img( duy_country_image( $country_name ), 'Campus ' . $name, '', 'lazy' ) ); ?></figure>
			<div class="sa-prose"><?php echo wp_kses_post( wpautop( $campus_text ) ); ?></div>
		<?php endif; ?>

		<?php $prose_section( 'Chương trình học', $programs ); ?>
		<?php $prose_section( 'Tuyển sinh & đầu vào', $admissions ); ?>

		<?php if ( $fee_rows ) : ?>
			<h2><?php esc_html_e( 'Học phí & kỳ nhập học', 'duy-study' ); ?></h2>
			<div class="sa-panel">
				<p class="sa-note"><?php esc_html_e( 'Mang tính tham khảo, vui lòng liên hệ để được cập nhật thông tin mới nhất.', 'duy-study' ); ?></p>
				<?php if ( $fee_hero ) : ?>
					<p class="sa-feehero"><b><?php esc_html_e( 'Phí tham khảo:', 'duy-study' ); ?></b> <span><?php echo esc_html( $fee_hero ); ?></span></p>
				<?php endif; ?>
				<table class="cost-table">
					<tbody>
						<?php foreach ( $fee_rows as $label => $value ) : ?>
							<tr><td><?php echo esc_html( $label ); ?></td><td><?php echo esc_html( $value ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ( $english_txt ) : ?>
			<h2><?php esc_html_e( 'Yêu cầu tiếng Anh', 'duy-study' ); ?></h2>
			<div class="sa-panel"><div class="sa-prose"><?php echo wp_kses_post( wpautop( $english_txt ) ); ?></div></div>
		<?php endif; ?>

		<?php if ( $scholarship ) : ?>
			<h2><?php esc_html_e( 'Học bổng & hỗ trợ tài chính', 'duy-study' ); ?></h2>
			<div class="sa-panel"><div class="sa-prose"><?php echo wp_kses_post( wpautop( $scholarship ) ); ?></div></div>
		<?php endif; ?>

		<?php $prose_section( 'Định hướng nghề nghiệp', $career ); ?>

		<?php
		$related_schools = duy_related_school_cards( $country_name, $level_label, (string) ( $demo['id'] ?? '' ), 3 );
		if ( $related_schools ) :
			?>
			<h2><?php echo esc_html( 'Trường khác tại ' . $country_name ); ?></h2>
			<div class="grid g3">
				<?php foreach ( $related_schools as $related_school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $related_school ] ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $related_scholarships ) : ?>
			<h2><?php esc_html_e( 'Học bổng liên quan', 'duy-study' ); ?></h2>
			<div class="grid g3">
				<?php foreach ( array_slice( $related_scholarships, 0, 3 ) as $scholarship_item ) : ?>
					<?php duy_part( 'card-scholarship', [ 'item' => $scholarship_item ] ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="sa-consult" id="school-consult">
			<?php duy_part( 'consultation-form', [ 'source' => 'truong-' . ( $demo['id'] ?? $id ) ] ); ?>
		</div>

	</article>
</section>
<?php
get_footer();
