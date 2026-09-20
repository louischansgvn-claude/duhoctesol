<?php
/**
 * SEO: <title>/description, canonical + Open Graph, robots, XML sitemap,
 * robots.txt, Search Console / GA4 hooks and JSON-LD schema.
 *
 * Meta của trang trường được chuẩn hoá LÚC RENDER (duy_seo_school_meta):
 * dataset import có title/description bị cắt "…" và viết thường "thpt" —
 * không sửa dataset/seeder (seeder bị guard trên production).
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Route ảo còn là dữ liệu placeholder → noindex cho tới khi có content thật. */
const DUY_SEO_NOINDEX_PATTERNS = [
	'#^hoc-bong/[^/]+$#',
	'#^hoc-sinh/[^/]+$#',
];

const DUY_SEO_TITLE_MAX = 65;
const DUY_SEO_DESC_MAX  = 158;

function duy_seo_path(): string {
	return function_exists( 'duy_request_path' ) ? duy_request_path() : trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
}

function duy_seo_canonical_url( string $path ): string {
	return home_url( '' === $path ? '/' : '/' . trim( $path, '/' ) . '/' );
}

function duy_seo_format_title( string $title ): string {
	$title = trim( duy_public_text( $title ) );

	if ( '' === $title || 'Ban Du học Hội TESOL TP.HCM' === $title ) {
		return 'Ban Du học Hội TESOL TP.HCM — Tư vấn du học Mỹ, Úc, Canada, New Zealand';
	}

	// Tiêu đề đã chứa thương hiệu (vd. "Ban Du học Hội TESOL TP.HCM đồng hành cùng…") thì không lặp lại hậu tố.
	if ( str_ends_with( $title, ' — Ban Du học Hội TESOL TP.HCM' ) || str_contains( $title, 'Ban Du học Hội TESOL TP.HCM' ) ) {
		return $title;
	}

	return $title . ' — Ban Du học Hội TESOL TP.HCM';
}

function duy_seo_item_title( array $items, string $id ): string {
	$item = duy_find_by_id( $items, $id );

	return $item ? duy_public_text( (string) ( $item['n'] ?? $item['title'] ?? '' ) ) : '';
}

/* -------------------------------------------------------------------------
 * Text helpers
 * ---------------------------------------------------------------------- */

function duy_seo_plain( string $text ): string {
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = wp_strip_all_tags( $text );

	return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
}

/** "Du học thpt Canada" → "Du học THPT Canada" (dataset viết thường bậc học); "thpt" đứng riêng luôn là viết tắt → THPT. */
function duy_seo_fix_level_case( string $text ): string {
	$text = (string) preg_replace( '/(?<![\p{L}\p{N}])thpt(?![\p{L}\p{N}])/iu', 'THPT', $text );
	$map  = [
		'thpt'        => 'THPT',
		'trung học'   => 'Trung học',
		'sau đại học' => 'Sau đại học',
		'đại học'     => 'Đại học',
		'cao đẳng'    => 'Cao đẳng',
		'anh ngữ'     => 'Anh ngữ',
	];

	return (string) preg_replace_callback(
		'/(Du học\s+)(thpt|trung học|sau đại học|đại học|cao đẳng|anh ngữ)(?![\p{L}\p{N}])/iu',
		static fn( array $m ): string => $m[1] . ( $map[ mb_strtolower( $m[2] ) ] ?? $m[2] ),
		$text
	);
}

/** Cắt description ≤ $max ký tự tại ranh giới dấu phẩy/khoảng trắng, bỏ "…", kết thúc bằng dấu câu. */
function duy_seo_trim_description( string $text, int $max = DUY_SEO_DESC_MAX ): string {
	$text = duy_seo_plain( $text );
	$text = trim( (string) preg_replace( '/(\s*(\.\.\.|…))+$/u', '', $text ) );

	if ( '' === $text ) {
		return '';
	}

	if ( mb_strlen( $text ) > $max ) {
		$cut = mb_substr( $text, 0, $max - 1 );
		$pos = mb_strrpos( $cut, ', ' );
		if ( false === $pos || $pos < (int) ( $max * 0.6 ) ) {
			$pos = mb_strrpos( $cut, ' ' );
		}
		if ( false !== $pos && $pos > (int) ( $max * 0.5 ) ) {
			$cut = mb_substr( $cut, 0, $pos );
		}
		$text = $cut;
	}

	// Bỏ dấu phẩy/hai chấm/gạch ngang lơ lửng ở cuối (do cắt hoặc do dataset), rồi kết thúc bằng dấu câu.
	$text = trim( (string) preg_replace( '/[\s,;:–-]+$/u', '', $text ) );

	if ( '' !== $text && ! preg_match( '/[.!?]$/u', $text ) ) {
		$text .= '.';
	}

	return $text;
}

function duy_seo_with_brand_suffix( string $text ): string {
	$suffix = ' Ban Du học Hội TESOL TP.HCM tư vấn 1:1.';

	if ( '' === $text || str_contains( $text, 'Ban Du học Hội TESOL TP.HCM' ) || mb_strlen( $text ) + mb_strlen( $suffix ) > DUY_SEO_DESC_MAX ) {
		return $text;
	}

	return $text . $suffix;
}

function duy_seo_ends_with_ellipsis( string $text ): bool {
	return (bool) preg_match( '/(\.\.\.|…)\s*$/u', $text );
}

/* -------------------------------------------------------------------------
 * School posts (571 trường import)
 * ---------------------------------------------------------------------- */

/** slug → tên nước (VI). */
function duy_seo_country_names(): array {
	static $map = null;

	if ( null === $map ) {
		$map = function_exists( 'duy_default_terms' ) ? (array) ( duy_default_terms()['country'] ?? [] ) : [];
		if ( ! $map ) {
			$map = [
				'my'          => 'Mỹ',
				'uc'          => 'Úc',
				'canada'      => 'Canada',
				'new-zealand' => 'New Zealand',
				'tho-nhi-ky'  => 'Thổ Nhĩ Kỳ',
				'singapore'   => 'Singapore',
				'han-quoc'    => 'Hàn Quốc',
				'duc'         => 'Đức',
				'ha-lan'      => 'Hà Lan',
				'anh'         => 'Anh',
				'malaysia'    => 'Malaysia',
				'thuy-sy'     => 'Thụy Sỹ',
				'philippines' => 'Philippines',
			];
		}
	}

	return $map;
}

/** Post `school` đã publish cho route truong/{slug}, cache theo slug. */
function duy_seo_school_post( string $path ): ?WP_Post {
	static $cache = [];

	$path = trim( $path, '/' );
	if ( ! str_starts_with( $path, 'truong/' ) || ! function_exists( 'get_page_by_path' ) ) {
		return null;
	}

	$slug = basename( $path );
	if ( ! array_key_exists( $slug, $cache ) ) {
		$post           = get_page_by_path( $slug, OBJECT, 'school' );
		$cache[ $slug ] = ( $post instanceof WP_Post && 'publish' === $post->post_status ) ? $post : null;
	}

	return $cache[ $slug ];
}

/** [slug, label] bậc học của post trường (meta import → term study_level → THPT). */
function duy_seo_school_level( int $post_id ): array {
	$labels = [
		'thpt'        => 'THPT',
		'trung-hoc'   => 'THPT',
		'cao-dang'    => 'Cao đẳng',
		'dai-hoc'     => 'Đại học',
		'sau-dai-hoc' => 'Sau đại học',
		'thac-si'     => 'Thạc sĩ',
		'anh-ngu'     => 'Anh ngữ',
	];

	$slug = '';
	foreach ( [ '_duy_othc_level_slug', '_duy_caau_level_slug', '_duy_ps_level_slug' ] as $key ) {
		$slug = (string) get_post_meta( $post_id, $key, true );
		if ( '' !== $slug ) {
			break;
		}
	}

	if ( ! isset( $labels[ $slug ] ) ) {
		$slug  = '';
		$terms = wp_get_object_terms( $post_id, 'study_level', [ 'fields' => 'slugs' ] );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( (array) $terms as $term_slug ) {
				if ( isset( $labels[ $term_slug ] ) ) {
					$slug = (string) $term_slug;
					break;
				}
			}
		}
	}

	if ( ! isset( $labels[ $slug ] ) ) {
		$slug = 'thpt';
	}

	return [ 'trung-hoc' === $slug ? 'thpt' : $slug, $labels[ $slug ] ];
}

/** [slug, tên] quốc gia của post trường theo term `country` (mặc định Mỹ). */
function duy_seo_school_country( int $post_id ): array {
	$names = duy_seo_country_names();
	$slug  = 'my';
	$terms = wp_get_object_terms( $post_id, 'country', [ 'fields' => 'slugs' ] );

	if ( ! is_wp_error( $terms ) ) {
		foreach ( (array) $terms as $term_slug ) {
			if ( isset( $names[ $term_slug ] ) ) {
				$slug = (string) $term_slug;
				break;
			}
		}
	}

	return [ $slug, $names[ $slug ] ?? 'Mỹ' ];
}

/** Tên trường không có tiền tố "Trường THPT / Đại học / Cao đẳng…" (chỉ dùng cho meta, không đổi H1). */
function duy_seo_school_display_name( string $title ): string {
	$title = duy_seo_plain( $title );
	$title = (string) preg_replace( '/^(Trường\s+(THPT|Trung học|Cao đẳng|Đại học|Cao đẳng cộng đồng)\s+|Đại học\s+|Cao đẳng\s+|Học viện\s+)/iu', '', $title );

	return trim( $title );
}

/**
 * Title/description chuẩn hoá cho một post trường.
 *
 * @return array{title:string,description:string,name:string,level_slug:string,level_label:string,country_slug:string,country_name:string,state:string}
 */
function duy_seo_school_meta( int $post_id ): array {
	static $cache = [];

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$post = get_post( $post_id );
	$name = duy_seo_school_display_name( (string) ( $post->post_title ?? '' ) );

	[ $level_slug, $level_label ]     = duy_seo_school_level( $post_id );
	[ $country_slug, $country_name ]  = duy_seo_school_country( $post_id );
	$state                            = duy_seo_plain( (string) get_post_meta( $post_id, '_duy_hs_state', true ) );

	$title = duy_seo_fix_level_case( duy_seo_plain( (string) get_post_meta( $post_id, '_duy_meta_title', true ) ) );
	if ( '' === $title || duy_seo_ends_with_ellipsis( $title ) || mb_strlen( $title ) > DUY_SEO_TITLE_MAX ) {
		$title = $name . ' | Du học ' . $level_label . ' ' . $country_name;
		if ( mb_strlen( $title ) > DUY_SEO_TITLE_MAX ) {
			$title = $name . ' | Ban Du học Hội TESOL TP.HCM';
		}
		if ( mb_strlen( $title ) > DUY_SEO_TITLE_MAX ) {
			$title = $name;
		}
	}

	$description = duy_seo_plain( (string) get_post_meta( $post_id, '_duy_meta_description', true ) );
	if ( '' === $description || duy_seo_ends_with_ellipsis( $description ) ) {
		// Dataset đã cắt description giữa câu ("…yêu cầu đầu vào,…") → dựng lại từ dữ liệu thay vì giữ mảnh câu.
		$phrases = [
			'thpt'        => 'trường THPT',
			'cao-dang'    => 'trường cao đẳng',
			'dai-hoc'     => 'trường đại học',
			'sau-dai-hoc' => 'chương trình sau đại học',
			'thac-si'     => 'chương trình thạc sĩ',
			'anh-ngu'     => 'trường Anh ngữ',
		];
		$where       = $country_name . ( '' !== $state ? ' tại ' . $state : '' );
		$description = sprintf(
			'Tìm hiểu %s, %s %s: chương trình học, điều kiện đầu vào, học phí tham khảo và học bổng. Ban Du học Hội TESOL TP.HCM tư vấn 1:1.',
			$name,
			$phrases[ $level_slug ] ?? 'trường',
			$where
		);
	}
	$description = duy_seo_with_brand_suffix( duy_seo_trim_description( duy_seo_fix_level_case( $description ) ) );

	$cache[ $post_id ] = compact( 'title', 'description', 'name', 'level_slug', 'level_label', 'country_slug', 'country_name', 'state' );

	return $cache[ $post_id ];
}

/** Số trường thực tế của quốc gia × bậc học (cùng nguồn với trang bậc học và sitemap). */
function duy_seo_country_level_count( string $country_slug, string $level_slug ): int {
	static $counts = null;

	if ( null === $counts ) {
		$counts = [];
		$labels = duy_level_school_labels();
		foreach ( duy_demo_schools() as $school ) {
			$c     = duy_country_slug( (string) ( $school['c'] ?? '' ) );
			$level = (string) ( $school['level'] ?? '' );
			foreach ( $labels as $slug => $names ) {
				if ( in_array( $level, $names, true ) ) {
					$counts[ $c . '|' . $slug ] = ( $counts[ $c . '|' . $slug ] ?? 0 ) + 1;
				}
			}
		}
	}

	return (int) ( $counts[ $country_slug . '|' . $level_slug ] ?? 0 );
}

/* -------------------------------------------------------------------------
 * <title>
 * ---------------------------------------------------------------------- */

function duy_seo_title( string $path ): string {
	$path = trim( $path, '/' );

	// 404 thật (kể cả 404.php được include từ route ảo với slug không tồn tại).
	if ( function_exists( 'is_404' ) && is_404() ) {
		return duy_seo_format_title( 'Không tìm thấy trang' );
	}

	// Trang trường: meta_title import đã chuẩn hoá (không cắt "…", ≤ 65 ký tự, viết hoa bậc học).
	$school = duy_seo_school_post( $path );
	if ( $school ) {
		return duy_seo_school_meta( $school->ID )['title'];
	}

	if ( '' === $path ) {
		return 'Ban Du học Hội TESOL TP.HCM – Tư vấn du học Mỹ, Úc, Canada, New Zealand';
	}

	$parts = explode( '/', $path );

	if ( 'quoc-gia' === ( $parts[0] ?? '' ) ) {
		if ( empty( $parts[1] ) ) {
			return duy_seo_format_title( 'Quốc gia du học' );
		}

		$country = duy_country_by_slug( (string) $parts[1] );
		$title   = duy_public_text( (string) ( $country['title'] ?? 'Du học ' . ( $country['name'] ?? $parts[1] ) ) );
		if ( ! empty( $parts[2] ) ) {
			if ( 'video' === $parts[2] ) {
				$title = 'Video du học ' . duy_public_text( (string) ( $country['name'] ?? $parts[1] ) );
			} else {
				$levels = duy_mockup_v2_levels();
				$level  = $levels[ $parts[2] ]['name'] ?? $parts[2];
				$title .= ' bậc ' . duy_public_text( (string) $level );
			}
		}

		return duy_seo_format_title( $title );
	}

	if ( 'nganh-hoc' === ( $parts[0] ?? '' ) ) {
		if ( empty( $parts[1] ) ) {
			return duy_seo_format_title( 'Ngành học HOT' );
		}

		$major = function_exists( 'duy_mockup_v2_major_by_slug' ) ? duy_mockup_v2_major_by_slug( (string) $parts[1] ) : null;

		return duy_seo_format_title( 'Du học ngành ' . duy_public_text( (string) ( $major['title'] ?? $parts[1] ) ) );
	}

	if ( 'lo-trinh-du-hoc' === ( $parts[0] ?? '' ) ) {
		if ( empty( $parts[1] ) ) {
			return duy_seo_format_title( 'Lộ trình du học' );
		}

		$guide = duy_guide_by_slug( (string) $parts[1] );

		return duy_seo_format_title( duy_public_text( (string) ( $guide['title'] ?? 'Lộ trình du học' ) ) );
	}

	$single_maps = [
		'truong'   => [ 'items' => duy_demo_schools(), 'fallback' => 'Thông tin trường' ],
		'hoc-bong' => [ 'items' => duy_demo_scholarships(), 'fallback' => 'Thông tin học bổng' ],
		'su-kien'  => [ 'items' => duy_demo_events(), 'fallback' => 'Sự kiện du học' ],
		'hoc-sinh' => [ 'items' => duy_demo_stories(), 'fallback' => 'Câu chuyện học sinh' ],
		'tin-tuc'  => [ 'items' => duy_demo_news(), 'fallback' => 'Tin tức du học' ],
	];

	if ( isset( $single_maps[ $parts[0] ?? '' ] ) && ! empty( $parts[1] ) ) {
		$title = duy_seo_item_title( $single_maps[ $parts[0] ]['items'], (string) $parts[1] );

		return duy_seo_format_title( $title ?: $single_maps[ $parts[0] ]['fallback'] );
	}

	$map = [
		'truong'             => 'Danh sách trường du học',
		'hoc-bong'           => 'Học bổng du học',
		'su-kien'            => 'Sự kiện du học',
		'hoc-sinh'           => 'Câu chuyện học sinh',
		'tin-tuc'            => 'Tin tức du học',
		've-chung-toi'       => 'Về chúng tôi',
		'lien-he'            => 'Liên hệ tư vấn du học',
		'faq'                => 'Câu hỏi thường gặp',
		'dich-vu'            => 'Dịch vụ tư vấn du học',
		'tai-cam-nang'       => 'Tải cẩm nang du học',
		'chinh-sach-bao-mat' => 'Chính sách bảo mật',
	];

	foreach ( $map as $prefix => $title ) {
		if ( $path === $prefix || str_starts_with( $path, $prefix . '/' ) ) {
			return duy_seo_format_title( $title );
		}
	}

	if ( function_exists( 'get_the_title' ) && is_singular() ) {
		return duy_seo_format_title( get_the_title() );
	}

	return duy_seo_format_title( '' );
}

function duy_filter_document_title( string $title ): string {
	if ( is_admin() ) {
		return $title;
	}

	return duy_seo_request_title( duy_seo_path() );
}
add_filter( 'pre_get_document_title', 'duy_filter_document_title', 20 );

/* -------------------------------------------------------------------------
 * Meta description
 * ---------------------------------------------------------------------- */

function duy_seo_description( string $path ): string {
	$path = trim( $path, '/' );

	$school = duy_seo_school_post( $path );
	if ( $school ) {
		return duy_seo_school_meta( $school->ID )['description'];
	}

	if ( '' === $path ) {
		return duy_seo_brand_description();
	}

	if ( str_starts_with( $path, 'quoc-gia/' ) ) {
		$parts        = explode( '/', $path );
		$country      = duy_country_by_slug( (string) ( $parts[1] ?? basename( $path ) ) );
		$country_name = duy_public_text( (string) ( $country['name'] ?? $parts[1] ?? '' ) );

		if ( 'video' === ( $parts[2] ?? '' ) ) {
			return 'Tổng hợp video du học ' . $country_name . ' từ kênh YouTube chính của Ban Du học Hội TESOL TP.HCM, giúp học sinh và phụ huynh xem nhanh trường, ngành và lộ trình hồ sơ.';
		}

		// Trang bậc học: description riêng theo bậc (trước đây dùng lead của quốc gia → trùng mọi bậc).
		if ( ! empty( $parts[2] ) ) {
			$levels     = duy_mockup_v2_levels();
			$level_name = duy_public_text( (string) ( $levels[ $parts[2] ]['name'] ?? $parts[2] ) );
			$count      = duy_seo_country_level_count( (string) $parts[1], (string) $parts[2] );
			$list       = $count > 0 ? sprintf( 'danh sách %d trường, ', $count ) : '';

			return sprintf( 'Du học %s bậc %s: %shọc phí tham khảo, điều kiện đầu vào, hồ sơ và học bổng. Ban Du học Hội TESOL TP.HCM tư vấn lộ trình 1:1.', $country_name, $level_name, $list );
		}

		return duy_public_text( (string) ( $country['lead'] ?? 'Khám phá điểm đến du học, chi phí tham khảo, trường nổi bật, học bổng và checklist visa cùng Ban Du học Hội TESOL TP.HCM.' ) );
	}

	if ( str_starts_with( $path, 'nganh-hoc/' ) && function_exists( 'duy_mockup_v2_major_by_slug' ) ) {
		$major = duy_mockup_v2_major_by_slug( basename( $path ) );
		return duy_public_text( (string) ( $major['lead'] ?? 'Khám phá ngành học HOT, cơ hội nghề nghiệp và trường tiêu biểu cùng Ban Du học Hội TESOL TP.HCM.' ) );
	}

	if ( str_starts_with( $path, 'lo-trinh-du-hoc/' ) ) {
		$guide = duy_guide_by_slug( basename( $path ) );
		return $guide['lead'] ?? 'Lộ trình du học từng bước giúp gia đình chủ động thời gian, chi phí, hồ sơ trường, visa và hành trang.';
	}

	if ( str_starts_with( $path, 'tin-tuc/' ) ) {
		$article = duy_find_by_id( duy_demo_news(), basename( $path ) );
		if ( $article && ! empty( $article['lead'] ) ) {
			return duy_public_text( (string) $article['lead'] );
		}
	}

	if ( str_starts_with( $path, 'su-kien/' ) ) {
		$event = duy_find_by_id( duy_demo_events(), basename( $path ) );
		if ( $event ) {
			$desc = duy_seo_plain( duy_public_text( (string) ( $event['desc'] ?? '' ) ) );
			if ( mb_strlen( $desc ) >= 70 ) {
				return $desc;
			}
			$when_where = array_filter( [ duy_seo_plain( (string) ( $event['time'] ?? $event['c'] ?? '' ) ), duy_seo_plain( (string) ( $event['place'] ?? '' ) ) ] );

			return sprintf(
				'%s%s. %s',
				duy_seo_plain( (string) ( $event['n'] ?? 'Sự kiện du học' ) ),
				$when_where ? ' – ' . implode( ', ', $when_where ) : '',
				'' !== $desc ? $desc : 'Đăng ký tham dự để gặp đại diện trường và được Ban Du học Hội TESOL TP.HCM tư vấn lộ trình du học 1:1.'
			);
		}
	}

	$map = [
		'lo-trinh-du-hoc'    => 'Tổng quan lộ trình du học từ định hướng, kế hoạch, hồ sơ, thư mời đến chuẩn bị lên đường.',
		'quoc-gia'           => 'So sánh các điểm đến du học Mỹ, Úc, Canada, New Zealand, Thổ Nhĩ Kỳ, Singapore, Hàn Quốc, Đức và Hà Lan cùng Ban Du học Hội TESOL TP.HCM.',
		'nganh-hoc'          => 'Những nhóm ngành được học sinh và phụ huynh quan tâm nhất, kèm định hướng, cơ hội nghề nghiệp và trường tiêu biểu.',
		'truong'             => 'Tìm trường du học theo quốc gia, bậc học, ngành học và chi phí tham khảo.',
		'hoc-bong'           => 'Học bổng du học Mỹ, Úc, Canada, New Zealand: giá trị, điều kiện, deadline và hồ sơ cần chuẩn bị. Ban Du học Hội TESOL TP.HCM hướng dẫn săn học bổng theo từng hồ sơ.',
		'su-kien'            => 'Lịch hội thảo, webinar và buổi tư vấn du học của Ban Du học Hội TESOL TP.HCM tại TP.HCM, Đà Nẵng, Buôn Ma Thuột và online: đăng ký để gặp trực tiếp đại diện trường.',
		'hoc-sinh'           => 'Câu chuyện học sinh Ban Du học Hội TESOL TP.HCM: hành trình chọn trường, xin học bổng, visa và cuộc sống du học qua chia sẻ của học viên và nhà trường.',
		'tin-tuc'            => 'Tin tức du học mới nhất từ Ban Du học Hội TESOL TP.HCM: chính sách visa, học bổng, chi phí và kinh nghiệm chuẩn bị hồ sơ cho học sinh, phụ huynh Việt Nam.',
		've-chung-toi'       => 'Ban Du học Hội TESOL TP.HCM đồng hành cùng học sinh và phụ huynh từ chọn trường đến ngày lên đường.',
		'lien-he'            => 'Liên hệ Ban Du học Hội TESOL TP.HCM qua hotline 0906.510.747 và 3 văn phòng hỗ trợ tại TP.HCM, Đà Nẵng, Buôn Ma Thuột.',
		'faq'                => 'Câu hỏi thường gặp về visa, học phí, hồ sơ và dịch vụ tư vấn du học Ban Du học Hội TESOL TP.HCM.',
		'dich-vu'            => 'Dịch vụ tư vấn du học, chọn trường, học bổng, hồ sơ visa và chuẩn bị trước khi lên đường.',
		'tai-cam-nang'       => 'Tải cẩm nang du học Ban Du học Hội TESOL TP.HCM: timeline 12 tháng, checklist hồ sơ, ngân sách dự kiến và các mốc visa cho học sinh, phụ huynh.',
		'chinh-sach-bao-mat' => 'Chính sách bảo mật của Ban Du học Hội TESOL TP.HCM: cách chúng tôi thu thập, sử dụng và bảo vệ thông tin liên hệ của học sinh, phụ huynh khi đăng ký tư vấn.',
	];

	foreach ( $map as $prefix => $description ) {
		if ( $path === $prefix || str_starts_with( $path, $prefix . '/' ) ) {
			return $description;
		}
	}

	return 'Ban Du học Hội TESOL TP.HCM đồng hành cùng học sinh và phụ huynh trong lộ trình du học.';
}

/* -------------------------------------------------------------------------
 * og:image
 * ---------------------------------------------------------------------- */

/**
 * Ảnh OG mặc định: file trong theme → attachment slug `og-default` trong Media
 * (đường upload từ Windows, vì Theme Editor không đưa được file nhị phân) → ảnh campus.
 */
function duy_seo_default_image(): string {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	if ( file_exists( DUY_THEME_DIR . '/assets/img/og-default.jpg' ) ) {
		return $url = duy_img_uri( 'og-default.jpg' );
	}

	$attachment = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'og-default', OBJECT, 'attachment' ) : null;
	if ( $attachment instanceof WP_Post ) {
		$media = (string) wp_get_attachment_image_url( $attachment->ID, 'full' );
		if ( '' !== $media ) {
			return $url = $media;
		}
	}

	return $url = duy_img_uri( 'photo-campus-library.webp' );
}

function duy_seo_absolute_url( string $url ): string {
	if ( str_starts_with( $url, '//' ) ) {
		return ( is_ssl() ? 'https:' : 'http:' ) . $url;
	}
	if ( str_starts_with( $url, '/' ) ) {
		return home_url( $url );
	}

	return $url;
}

/** Đường dẫn file local của một URL thuộc wp-content (để đọc kích thước ảnh), '' nếu không phải. */
function duy_seo_local_path( string $url ): string {
	$url_path     = (string) wp_parse_url( $url, PHP_URL_PATH );
	$content_path = rtrim( (string) wp_parse_url( content_url(), PHP_URL_PATH ), '/' );

	if ( '' !== $content_path && str_starts_with( $url_path, $content_path . '/' ) ) {
		return WP_CONTENT_DIR . substr( $url_path, strlen( $content_path ) );
	}

	return '';
}

/** [width, height] của ảnh local (cache transient theo mtime), [0,0] nếu không đọc được. */
function duy_seo_image_dims( string $url ): array {
	$local = duy_seo_local_path( $url );
	if ( '' === $local || ! is_file( $local ) ) {
		return [ 0, 0 ];
	}

	$key  = 'duy_seo_img_' . md5( $local . '|' . (string) filemtime( $local ) );
	$dims = get_transient( $key );
	if ( ! is_array( $dims ) || 2 !== count( $dims ) ) {
		$info = @getimagesize( $local ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$dims = $info ? [ (int) $info[0], (int) $info[1] ] : [ 0, 0 ];
		set_transient( $key, $dims, WEEK_IN_SECONDS );
	}

	return $dims;
}

/**
 * Ảnh OG theo trang: trường → ảnh campus trong bài (không phải logo) → featured → ảnh quốc gia;
 * quốc gia/bậc học → ảnh quốc gia; còn lại → og-default.jpg.
 *
 * @return array{url:string,width:int,height:int}
 */
function duy_seo_image( string $path ): array {
	$url   = '';
	$parts = explode( '/', trim( $path, '/' ) );

	if ( 'truong' === ( $parts[0] ?? '' ) && ! empty( $parts[1] ) ) {
		$post = duy_seo_school_post( $path );
		if ( $post ) {
			if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $post->post_content, $m ) ) {
				foreach ( $m[1] as $src ) {
					$file = strtolower( basename( (string) wp_parse_url( $src, PHP_URL_PATH ) ) );
					if ( str_contains( $file, 'logo' ) || str_ends_with( $file, '.svg' ) ) {
						continue;
					}
					$url = $src;
					break;
				}
			}
			if ( '' === $url && has_post_thumbnail( $post ) ) {
				$url = (string) get_the_post_thumbnail_url( $post, 'full' );
			}
			if ( '' === $url ) {
				$url = duy_img_uri( duy_country_image( duy_seo_school_country( $post->ID )[1] ) );
			}
		}
	} elseif ( 'quoc-gia' === ( $parts[0] ?? '' ) && ! empty( $parts[1] ) ) {
		$country = duy_country_by_slug( (string) $parts[1] );
		if ( $country ) {
			$url = duy_img_uri( duy_country_image( (string) ( $country['name'] ?? '' ) ) );
		}
	} elseif ( 'tin-tuc' === ( $parts[0] ?? '' ) && ! empty( $parts[1] ) ) {
		// Bài viết là WP post thật: ảnh đại diện → ảnh bìa (Carbon cover_image) → mặc định.
		$article = duy_find_by_id( duy_demo_news(), (string) $parts[1] );
		$post_id = (int) ( $article['post_id'] ?? 0 );
		if ( $post_id ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$url = (string) get_the_post_thumbnail_url( $post_id, 'full' );
			}
			if ( '' === $url && function_exists( 'duy_field' ) ) {
				$url = duy_media_image_url( duy_field( 'cover_image', $post_id, '' ) );
			}
		}
	}

	if ( '' === $url ) {
		$url = duy_seo_default_image();
	}

	$url  = duy_seo_absolute_url( $url );
	$dims = duy_seo_image_dims( $url );

	return [
		'url'    => $url,
		'width'  => $dims[0],
		'height' => $dims[1],
	];
}

/* -------------------------------------------------------------------------
 * Robots (noindex cho trang tiện ích / trang mỏng / placeholder)
 * ---------------------------------------------------------------------- */

function duy_seo_is_noindex_route( string $path ): bool {
	$path = trim( $path, '/' );
	foreach ( DUY_SEO_NOINDEX_PATTERNS as $pattern ) {
		if ( ! preg_match( $pattern, $path ) ) {
			continue;
		}

		// Học bổng / câu chuyện là WP post thật (không phải demo) → index bình thường.
		$type = str_starts_with( $path, 'hoc-bong/' ) ? 'scholarship' : 'student_story';
		$post = function_exists( 'get_page_by_path' ) ? get_page_by_path( basename( $path ), OBJECT, $type ) : null;

		return ! ( $post instanceof WP_Post && 'publish' === $post->post_status );
	}

	return false;
}

/** 404, search, date/author/category/tag/tax/attachment archive — không index, không canonical/OG. */
function duy_seo_is_utility_request(): bool {
	if ( is_404() || is_search() ) {
		return true;
	}

	// Route ảo của theme không bao giờ là archive của WP dù query vars có match rewrite cũ.
	if ( function_exists( 'duy_route_is_shim_path' ) && duy_route_is_shim_path( duy_seo_path() ) ) {
		return false;
	}

	return is_date() || is_author() || is_category() || is_tag() || is_tax() || is_attachment();
}

function duy_seo_robots( array $robots ): array {
	if ( is_admin() ) {
		return $robots;
	}

	// Trang kết quả có bộ lọc là biến thể trùng nội dung của hub → noindex nhưng vẫn follow
	// để link tới từng trường được đi tiếp. Trang ?trang=N không lọc thì vẫn index.
	if ( duy_seo_is_utility_request() || duy_seo_is_noindex_route( duy_seo_path() ) || duy_seo_request_filtered() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'duy_seo_robots', 20 );

/* -------------------------------------------------------------------------
 * JSON-LD
 * ---------------------------------------------------------------------- */

function duy_seo_org_id(): string {
	return home_url( '/#organization' );
}

function duy_seo_website_id(): string {
	return home_url( '/#website' );
}

/** Mô tả thương hiệu cố định (Organization/WebSite/llms.txt) — không lấy description của trang đang xem. */
function duy_seo_brand_description(): string {
	return 'Ban Du học Hội TESOL TP.HCM tư vấn du học Mỹ, Úc, Canada, New Zealand: chọn trường, học bổng, hồ sơ visa. 3 văn phòng TP.HCM, Đà Nẵng, Buôn Ma Thuột – Hotline 0906.510.747.';
}

/** sameAs: option → mặc định là page Facebook và kênh YouTube đã xác minh của Ban Du học Hội TESOL TP.HCM; chỉ nhận URL http(s). */
function duy_seo_same_as(): array {
	$urls = [
		(string) duy_option( 'facebook', 'https://www.facebook.com/DuhocDuyStudy/' ),
		(string) duy_option( 'youtube_url', function_exists( 'duy_official_youtube_channel_url' ) ? duy_official_youtube_channel_url() : '' ),
		(string) duy_option( 'tiktok_url', '' ),
		(string) duy_option( 'instagram', '' ),
		(string) duy_option( 'zalo_oa_url', '' ),
	];

	$urls = array_filter(
		array_map( 'trim', $urls ),
		static fn( string $url ): bool => (bool) preg_match( '#^https?://#i', $url )
	);

	return array_values( array_unique( $urls ) );
}

/** ImageObject logo (PNG trong theme) kèm kích thước; [] nếu không có file. */
function duy_seo_logo(): array {
	static $logo = null;

	if ( null !== $logo ) {
		return $logo;
	}

	$logo = [];
	foreach ( [ 'logo.png', 'logo-full.png' ] as $file ) {
		if ( ! file_exists( DUY_THEME_DIR . '/assets/img/' . $file ) ) {
			continue;
		}
		$url  = duy_seo_absolute_url( duy_img_uri( $file ) );
		$dims = duy_seo_image_dims( $url );
		$logo = [
			'@type' => 'ImageObject',
			'url'   => $url,
		];
		if ( $dims[0] > 0 && $dims[1] > 0 ) {
			$logo['width']  = $dims[0];
			$logo['height'] = $dims[1];
		}
		break;
	}

	return $logo;
}

/** PostalAddress từ một văn phòng (duy_offices); chỉ in trường có dữ liệu. */
function duy_seo_postal_address( array $office ): array {
	$street = implode( ', ', array_filter( [ $office['building'] ?? '', $office['street'] ?? '', $office['ward'] ?? '' ] ) );
	if ( '' === $street ) {
		$street = (string) ( $office['address'] ?? '' );
	}

	return array_filter(
		[
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street,
			'addressLocality' => (string) ( $office['city'] ?? '' ),
			'addressRegion'   => (string) ( $office['region'] ?? '' ),
			'postalCode'      => (string) ( $office['postal_code'] ?? '' ),
			'addressCountry'  => 'VN',
		],
		static fn( string $value ): bool => '' !== $value
	);
}

/**
 * "Mo-Fr 08:30-17:30, Sa 08:30-12:00" → OpeningHoursSpecification[]; [] nếu không parse được.
 */
function duy_seo_opening_hours( string $hours ): array {
	$days  = [ 'Mo' => 'Monday', 'Tu' => 'Tuesday', 'We' => 'Wednesday', 'Th' => 'Thursday', 'Fr' => 'Friday', 'Sa' => 'Saturday', 'Su' => 'Sunday' ];
	$order = array_keys( $days );
	$specs = [];

	foreach ( preg_split( '/\s*[,;]\s*/', trim( $hours ) ) ?: [] as $chunk ) {
		if ( ! preg_match( '/^(Mo|Tu|We|Th|Fr|Sa|Su)(?:-(Mo|Tu|We|Th|Fr|Sa|Su))?\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/i', $chunk, $m ) ) {
			continue;
		}
		$from = array_search( ucfirst( strtolower( $m[1] ) ), $order, true );
		$to   = '' !== ( $m[2] ?? '' ) ? array_search( ucfirst( strtolower( $m[2] ) ), $order, true ) : $from;
		if ( false === $from || false === $to || $to < $from ) {
			continue;
		}
		$specs[] = [
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array_map( static fn( string $abbr ): string => $days[ $abbr ], array_slice( $order, $from, $to - $from + 1 ) ),
			'opens'     => $m[3],
			'closes'    => $m[4],
		];
	}

	return $specs;
}

/** Question[] cho FAQPage từ mảng [{q,a}]. */
function duy_seo_faq_entities( array $faqs ): array {
	$entities = [];
	foreach ( $faqs as $faq ) {
		$q = trim( duy_seo_plain( (string) ( $faq['q'] ?? '' ) ) );
		$a = trim( duy_seo_plain( (string) ( $faq['a'] ?? '' ) ) );
		if ( '' === $q || '' === $a ) {
			continue;
		}
		$entities[] = [
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $a,
			],
		];
	}

	return $entities;
}

/** FAQ hiển thị trên đường dẫn này (khớp template): /faq/, /quoc-gia/{c}/, /quoc-gia/{c}/{level}/. */
function duy_seo_faqs_for_path( string $path ): array {
	$path = trim( $path, '/' );

	if ( 'faq' === $path && function_exists( 'duy_faq_groups' ) ) {
		return array_merge( ...array_values( array_map( 'array_values', duy_faq_groups() ) ) ?: [ [] ] );
	}

	if ( ! function_exists( 'duy_faq_items' ) || ! preg_match( '#^quoc-gia/([^/]+)(?:/([^/]+))?$#', $path, $m ) ) {
		return [];
	}

	$level = $m[2] ?? '';
	if ( '' !== $level && ( 'video' === $level || ! isset( duy_mockup_v2_levels()[ $level ] ) ) ) {
		return [];
	}
	if ( ! duy_country_by_slug( $m[1] ) ) {
		return [];
	}

	return duy_faq_items( $m[1], $level );
}

/** Trường của quốc gia × bậc học, cùng bộ lọc với page-templates/bac-hoc.php (A→Z). */
function duy_seo_level_school_cards( string $country_slug, string $level_slug ): array {
	$country = duy_country_by_slug( $country_slug );
	if ( ! $country ) {
		return [];
	}
	$country_name = duy_public_text( (string) ( $country['name'] ?? '' ) );
	$accepted     = duy_level_school_labels()[ $level_slug ] ?? [];
	$schools      = array_values(
		array_filter(
			duy_demo_schools(),
			static function ( array $school ) use ( $country_name, $accepted ): bool {
				if ( (string) ( $school['c'] ?? '' ) !== $country_name ) {
					return false;
				}

				return ! $accepted || in_array( (string) ( $school['level'] ?? '' ), $accepted, true );
			}
		)
	);
	usort( $schools, static fn( array $a, array $b ): int => strnatcasecmp( (string) ( $a['n'] ?? '' ), (string) ( $b['n'] ?? '' ) ) );

	return $schools;
}

/** slug quốc gia → mã ISO 3166-1 alpha-2 cho addressCountry. */
function duy_seo_country_iso( string $slug ): string {
	$map = [
		'my'          => 'US',
		'canada'      => 'CA',
		'uc'          => 'AU',
		'new-zealand' => 'NZ',
		'anh'         => 'GB',
		'ha-lan'      => 'NL',
		'malaysia'    => 'MY',
		'thuy-sy'     => 'CH',
		'singapore'   => 'SG',
		'duc'         => 'DE',
		'philippines' => 'PH',
		'tho-nhi-ky'  => 'TR',
		'han-quoc'    => 'KR',
	];

	return $map[ $slug ] ?? '';
}

function duy_seo_schema_graph( string $path, string $canonical, string $description ): array {
	$hotline = trim( (string) duy_option( 'hotline', '0906.510.747' ) );
	$email   = trim( (string) duy_option( 'email', '' ) );
	$logo    = duy_seo_logo();
	$offices = duy_offices();
	$founded = trim( (string) duy_option( 'org_founding_year', '' ) );
	$legal   = trim( (string) duy_option( 'org_legal_name', '' ) );

	$org = [
		'@type'         => 'EducationalOrganization',
		'@id'           => duy_seo_org_id(),
		'name'          => 'Ban Du học Hội TESOL TP.HCM',
		'alternateName' => [ 'Ban Du học Hội TESOL TP.HCM', 'BAN DU HỌC HỘI TESOL TP.HCM' ],
		'slogan'        => 'Avenue to New World',
		'url'           => home_url( '/' ),
		'description'   => duy_seo_brand_description(),
		'telephone'     => $hotline,
		'areaServed'    => 'VN',
		'knowsAbout'    => [ 'Du học Mỹ', 'Du học Úc', 'Du học Canada', 'Du học New Zealand', 'Học bổng du học', 'Visa du học', 'Du học THPT' ],
		'contactPoint'  => [
			[
				'@type'             => 'ContactPoint',
				'telephone'         => $hotline,
				'contactType'       => 'customer service',
				'areaServed'        => 'VN',
				'availableLanguage' => [ 'vi', 'en' ],
			],
		],
	];
	if ( $legal ) {
		$org['legalName'] = $legal;
	}
	if ( $logo ) {
		$org['logo']  = $logo;
		$org['image'] = $logo['url'];
	}
	if ( is_email( $email ) ) {
		$org['email'] = $email;
	}
	if ( preg_match( '/^(19|20)\d{2}$/', $founded ) ) {
		$org['foundingDate'] = $founded;
	}
	$same_as = duy_seo_same_as();
	if ( $same_as ) {
		$org['sameAs'] = $same_as;
	}
	if ( $offices ) {
		$org['address'] = duy_seo_postal_address( $offices[0] );
	}

	$graph = [
		$org,
		[
			'@type'       => 'WebSite',
			'@id'         => duy_seo_website_id(),
			'name'        => 'Ban Du học Hội TESOL TP.HCM',
			'alternateName' => 'Ban Du học Hội TESOL TP.HCM',
			'url'         => home_url( '/' ),
			'publisher'   => [ '@id' => duy_seo_org_id() ],
			'inLanguage'  => 'vi-VN',
			'description' => duy_seo_brand_description(),
		],
	];

	foreach ( $offices as $index => $office ) {
		$phone = trim( (string) ( $office['phone'] ?? '' ) ) ?: $hotline;
		$item  = [
			'@type'              => 'LocalBusiness',
			'@id'                => home_url( '/#office-' . ( $index + 1 ) ),
			'name'               => 'Ban Du học Hội TESOL TP.HCM – ' . duy_office_display_name( (string) ( $office['name'] ?? '' ) ),
			'url'                => home_url( '/lien-he/' ),
			'telephone'          => $phone,
			'address'            => duy_seo_postal_address( $office ),
			'parentOrganization' => [ '@id' => duy_seo_org_id() ],
		];
		if ( $logo ) {
			$item['image'] = $logo['url'];
		}
		if ( is_numeric( $office['lat'] ?? '' ) && is_numeric( $office['lng'] ?? '' ) ) {
			$item['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $office['lat'],
				'longitude' => (float) $office['lng'],
			];
		}
		if ( preg_match( '#^https?://#i', (string) ( $office['maps_url'] ?? '' ) ) ) {
			$item['hasMap'] = (string) $office['maps_url'];
		}
		$hours = duy_seo_opening_hours( (string) ( $office['hours'] ?? '' ) );
		if ( $hours ) {
			$item['openingHoursSpecification'] = $hours;
		}
		$graph[] = $item;
	}

	$faqs = duy_seo_faq_entities( duy_seo_faqs_for_path( $path ) );
	if ( $faqs ) {
		$graph[] = [
			'@type'      => 'FAQPage',
			'@id'        => $canonical . '#faq',
			'mainEntity' => $faqs,
		];
	}

	// Trang trường: WebPage + thực thể trường (HighSchool / CollegeOrUniversity / EducationalOrganization).
	$school = duy_seo_school_post( $path );
	if ( $school ) {
		$meta      = duy_seo_school_meta( $school->ID );
		$image     = duy_seo_image( $path );
		$school_id = $canonical . '#school';
		$types     = [
			'thpt'        => 'HighSchool',
			'cao-dang'    => 'CollegeOrUniversity',
			'dai-hoc'     => 'CollegeOrUniversity',
			'sau-dai-hoc' => 'CollegeOrUniversity',
			'thac-si'     => 'CollegeOrUniversity',
			'anh-ngu'     => 'EducationalOrganization',
		];
		$website   = trim( (string) get_post_meta( $school->ID, '_duy_hs_website', true ) );
		if ( '' === $website && function_exists( 'duy_field' ) ) {
			$website = trim( (string) duy_field( 'website', $school->ID, '' ) );
		}

		$graph[] = [
			'@type'              => 'WebPage',
			'@id'                => $canonical . '#webpage',
			'url'                => $canonical,
			'name'               => $meta['title'],
			'description'        => $meta['description'],
			'inLanguage'         => 'vi-VN',
			'isPartOf'           => [ '@id' => duy_seo_website_id() ],
			'about'              => [ '@id' => $school_id ],
			'mainEntity'         => [ '@id' => $school_id ],
			'primaryImageOfPage' => [
				'@type' => 'ImageObject',
				'url'   => $image['url'],
			],
			'datePublished'      => (string) get_post_time( 'c', true, $school ),
			'dateModified'       => (string) get_post_modified_time( 'c', true, $school ),
			'publisher'          => [ '@id' => duy_seo_org_id() ],
		];

		$entity = [
			'@type'       => $types[ $meta['level_slug'] ] ?? 'EducationalOrganization',
			'@id'         => $school_id,
			'name'        => $meta['name'],
			'description' => $meta['description'],
			'image'       => $image['url'],
			'address'     => array_filter(
				[
					'@type'          => 'PostalAddress',
					'addressRegion'  => $meta['state'],
					'addressCountry' => duy_seo_country_iso( $meta['country_slug'] ),
				],
				static fn( string $value ): bool => '' !== $value
			),
		];
		if ( preg_match( '#^https?://#i', $website ) ) {
			$entity['url']    = $website;
			$entity['sameAs'] = [ $website ];
		}
		$graph[] = $entity;
	}

	// Trang bậc học: ItemList trường (tối đa 50) để AI/Google đọc được danh sách mà không cần render JS.
	if ( preg_match( '#^quoc-gia/([^/]+)/([^/]+)$#', $path, $m ) && 'video' !== $m[2] && isset( duy_mockup_v2_levels()[ $m[2] ] ) ) {
		$cards = duy_seo_level_school_cards( $m[1], $m[2] );
		if ( $cards ) {
			$country_name = duy_public_text( (string) ( duy_country_by_slug( $m[1] )['name'] ?? $m[1] ) );
			$level_name   = duy_public_text( (string) ( duy_mockup_v2_levels()[ $m[2] ]['name'] ?? $m[2] ) );
			$position     = 0;
			$graph[]      = [
				'@type'           => 'ItemList',
				'@id'             => $canonical . '#schools',
				'name'            => sprintf( 'Trường %s tại %s', $level_name, $country_name ),
				'numberOfItems'   => count( $cards ),
				'itemListElement' => array_map(
					static function ( array $card ) use ( &$position ): array {
						return [
							'@type'    => 'ListItem',
							'position' => ++$position,
							'name'     => duy_public_text( (string) ( $card['n'] ?? '' ) ),
							'url'      => duy_school_url( (string) ( $card['id'] ?? '' ) ),
						];
					},
					array_slice( $cards, 0, 50 )
				),
			];
		}
	}

	// Trang video quốc gia: VideoObject cho video có ngày đăng (Google bắt buộc uploadDate;
	// video thiếu ngày thì bỏ qua thay vì bịa).
	if ( preg_match( '#^quoc-gia/([^/]+)/video$#', $path, $video_match ) ) {
		$video_country = duy_country_by_slug( $video_match[1] );
		if ( $video_country ) {
			$video_term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', $video_match[1], 'country' ) : null;
			$video_ref  = ( $video_term && ! is_wp_error( $video_term ) ) ? 'term:' . (int) $video_term->term_id : null;

			foreach ( array_slice( duy_country_video_items( (string) $video_match[1], (array) $video_country, $video_ref ), 0, 20 ) as $video ) {
				$video_id   = (string) ( $video['id'] ?? '' );
				$video_date = trim( (string) ( $video['published'] ?? '' ) );
				if ( '' === $video_id || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $video_date ) ) {
					continue;
				}

				$graph[] = [
					'@type'        => 'VideoObject',
					'@id'          => $canonical . '#video-' . $video_id,
					'name'         => duy_seo_plain( (string) ( $video['title'] ?? '' ) ),
					'description'  => duy_seo_plain( (string) ( $video['desc'] ?: $video['title'] ?? '' ) ),
					'thumbnailUrl' => 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/maxresdefault.jpg',
					'uploadDate'   => $video_date,
					'contentUrl'   => 'https://www.youtube.com/watch?v=' . rawurlencode( $video_id ),
					'embedUrl'     => 'https://www.youtube.com/embed/' . rawurlencode( $video_id ),
					'publisher'    => [ '@id' => duy_seo_org_id() ],
					'inLanguage'   => 'vi-VN',
				];
			}
		}
	}

	if ( str_starts_with( $path, 'su-kien/' ) ) {
		$event = duy_find_by_id( duy_demo_events(), basename( $path ) );
		if ( $event ) {
			$graph[] = [
				'@type'       => 'Event',
				'@id'         => $canonical . '#event',
				'name'        => $event['n'] ?? '',
				'description' => implode( ', ', $event['agenda'] ?? [] ),
				'startDate'   => duy_seo_iso_datetime( $event['time'] ?? '' ),
				'eventStatus' => 'https://schema.org/EventScheduled',
				'eventAttendanceMode' => str_contains( strtolower( $event['place'] ?? '' ), 'online' ) || str_contains( strtolower( $event['place'] ?? '' ), 'zoom' ) ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
				'location'    => [
					'@type' => 'Place',
					'name'  => $event['place'] ?? '',
				],
				'organizer'   => [ '@id' => home_url( '/#organization' ) ],
			];
		}
	}

	if ( str_starts_with( $path, 'tin-tuc/' ) ) {
		$article = duy_find_by_id( duy_demo_news(), basename( $path ) );
		if ( $article ) {
			[ $published, $modified ] = duy_seo_news_dates( $article );
			$image                    = duy_seo_image( $path );
			$graph[]                  = [
				'@type'            => 'Article',
				'@id'              => $canonical . '#article',
				'headline'         => duy_seo_plain( (string) ( $article['n'] ?? '' ) ),
				'description'      => duy_seo_plain( (string) ( $article['lead'] ?? $description ) ),
				'image'            => $image['url'],
				'mainEntityOfPage' => $canonical,
				'author'           => [ '@id' => duy_seo_org_id() ],
				'publisher'        => [ '@id' => duy_seo_org_id() ],
				'datePublished'    => $published,
				'dateModified'     => $modified,
				'inLanguage'       => 'vi-VN',
			];
		}
	}

	return $graph;
}

/** [published, modified] ISO-8601 của bài tin tức: WP post thật → ngày post; demo → ngày trong dữ liệu. */
function duy_seo_news_dates( array $article ): array {
	$post = ! empty( $article['post_id'] ) ? get_post( (int) $article['post_id'] ) : null;
	if ( $post instanceof WP_Post ) {
		return [ (string) get_post_time( 'c', true, $post ), (string) get_post_modified_time( 'c', true, $post ) ];
	}

	$published = duy_seo_iso_date( (string) ( $article['c'] ?? '' ) ) . 'T08:00:00+07:00';

	return [ $published, $published ];
}

function duy_seo_iso_datetime( string $value ): string {
	if ( preg_match( '#(\d{2}):(\d{2}),\s*(\d{2})/(\d{2})/(\d{4})#', $value, $matches ) ) {
		return sprintf( '%04d-%02d-%02dT%02d:%02d:00+07:00', (int) $matches[5], (int) $matches[4], (int) $matches[3], (int) $matches[1], (int) $matches[2] );
	}

	return gmdate( DATE_ATOM );
}

function duy_seo_iso_date( string $value ): string {
	if ( preg_match( '#(\d{2})/(\d{2})/(\d{4})#', $value, $matches ) ) {
		return sprintf( '%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1] );
	}

	return gmdate( 'Y-m-d' );
}

/* -------------------------------------------------------------------------
 * <head> output
 * ---------------------------------------------------------------------- */

/** Search Console / Bing verification + GA4 (theme options, không hard-code ID). */
function duy_seo_head_verification(): void {
	if ( is_admin() ) {
		return;
	}

	$gsc = trim( (string) duy_option( 'gsc_verification', '' ) );
	if ( preg_match( '/content=["\']([^"\']+)["\']/', $gsc, $m ) ) {
		$gsc = $m[1];
	}
	if ( '' !== $gsc ) {
		printf( "\t" . '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $gsc ) );
	}

	$bing = trim( (string) duy_option( 'bing_verification', '' ) );
	if ( preg_match( '/content=["\']([^"\']+)["\']/', $bing, $m ) ) {
		$bing = $m[1];
	}
	if ( '' !== $bing ) {
		printf( "\t" . '<meta name="msvalidate.01" content="%s">' . "\n", esc_attr( $bing ) );
	}

	$ga4 = strtoupper( trim( (string) duy_option( 'ga4_id', '' ) ) );
	if ( preg_match( '/^G-[A-Z0-9]{4,}$/', $ga4 ) && ! is_user_logged_in() ) {
		printf(
			"\t" . '<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s"></script>' . "\n" .
			"\t" . '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","%1$s");</script>' . "\n",
			esc_attr( $ga4 )
		);
	}
}
add_action( 'wp_head', 'duy_seo_head_verification', 1 );

function duy_render_seo_meta(): void {
	if ( is_admin() ) {
		return;
	}

	$path        = duy_seo_path();
	$description = duy_seo_trim_description( duy_seo_description( $path ) );

	// 404 / search / archive rác: chỉ description; không canonical, OG, schema (robots noindex qua wp_robots).
	if ( duy_seo_is_utility_request() ) {
		printf( "\t" . '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		return;
	}

	$canonical  = duy_seo_request_canonical( $path );
	$title      = duy_seo_request_title( $path );
	$image      = duy_seo_image( $path );
	$school     = duy_seo_school_post( $path );
	$news       = str_starts_with( $path, 'tin-tuc/' ) ? duy_find_by_id( duy_demo_news(), basename( $path ) ) : null;
	$is_article = $school || $news;
	$published  = '';
	$modified   = '';

	if ( $school ) {
		$published = (string) get_post_time( 'c', true, $school );
		$modified  = (string) get_post_modified_time( 'c', true, $school );
	} elseif ( $news ) {
		[ $published, $modified ] = duy_seo_news_dates( $news );
	}

	$schema = [
		'@context' => 'https://schema.org',
		'@graph'   => duy_seo_schema_graph( $path, $canonical, $description ),
	];
	duy_seo_pagination_links();
	?>
	<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:locale" content="vi_VN">
	<meta property="og:site_name" content="Ban Du học Hội TESOL TP.HCM">
	<meta property="og:type" content="<?php echo $is_article ? 'article' : 'website'; ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $image['url'] ); ?>">
	<?php if ( $image['width'] > 0 && $image['height'] > 0 ) : ?>
	<meta property="og:image:width" content="<?php echo (int) $image['width']; ?>">
	<meta property="og:image:height" content="<?php echo (int) $image['height']; ?>">
	<?php endif; ?>
	<?php if ( $is_article && $published ) : ?>
	<meta property="article:published_time" content="<?php echo esc_attr( $published ); ?>">
	<meta property="article:modified_time" content="<?php echo esc_attr( $modified ?: $published ); ?>">
	<?php endif; ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<meta name="twitter:image" content="<?php echo esc_url( $image['url'] ); ?>">
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
	<?php
}
add_action( 'wp_head', 'duy_render_seo_meta', 3 );

/* -------------------------------------------------------------------------
 * robots.txt
 * ---------------------------------------------------------------------- */

function duy_seo_robots_txt( string $output, $public ): string {
	if ( ! $public ) {
		return $output;
	}

	$sitemap = preg_match( '/^Sitemap:\s*\S+/mi', $output, $m ) ? $m[0] : 'Sitemap: ' . home_url( '/wp-sitemap.xml' );

	$lines = [
		'User-agent: *',
		'Disallow: /wp-admin/',
		'Disallow: /?s=',
		'Disallow: /search/',
		'Allow: /wp-admin/admin-ajax.php',
		'',
		'# AI / answer engines: cho phép crawl (WAF của host cũng phải whitelist các UA này).',
		'User-agent: GPTBot',
		'User-agent: OAI-SearchBot',
		'User-agent: ChatGPT-User',
		'User-agent: ClaudeBot',
		'User-agent: Claude-SearchBot',
		'User-agent: Claude-User',
		'User-agent: PerplexityBot',
		'User-agent: Perplexity-User',
		'User-agent: Google-Extended',
		'User-agent: Applebot',
		'User-agent: Applebot-Extended',
		'User-agent: Amazonbot',
		'User-agent: meta-externalagent',
		'User-agent: Bingbot',
		'Allow: /',
		'',
		$sitemap,
	];

	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'duy_seo_robots_txt', 10, 2 );

/* -------------------------------------------------------------------------
 * XML sitemap
 * ---------------------------------------------------------------------- */

/** Bỏ sitemap users (author archive đã 301 về trang chủ). */
function duy_seo_sitemap_add_provider( $provider, string $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'duy_seo_sitemap_add_provider', 10, 2 );

/** Taxonomy archive không index (trang mỏng trùng /tin-tuc/) → không đưa vào sitemap. */
add_filter( 'wp_sitemaps_taxonomies', '__return_empty_array' );

function duy_seo_sitemap_post_types( array $post_types ): array {
	// scholarship: chỉ WP post thật mới có trong sitemap (demo là route ảo, đang noindex).
	return array_intersect_key( $post_types, array_flip( [ 'page', 'post', 'school', 'event', 'scholarship' ] ) );
}
add_filter( 'wp_sitemaps_post_types', 'duy_seo_sitemap_post_types' );

function duy_virtual_sitemap_routes(): array {
	$routes = [
		'',
		'lo-trinh-du-hoc',
		'quoc-gia',
		'nganh-hoc',
		'truong',
		'hoc-bong',
		'su-kien',
		'hoc-sinh',
		'tin-tuc',
		've-chung-toi',
		'lien-he',
		'faq',
		'dich-vu',
		'tai-cam-nang',
		'chinh-sach-bao-mat',
	];

	$countries = duy_demo_countries();

	foreach ( $countries as $country_slug => $country ) {
		$country_slug = (string) $country_slug;
		$routes[]     = 'quoc-gia/' . $country_slug;

		// Chỉ index trang bậc học khi quốc gia đó thực sự có trường ở bậc này,
		// tránh đưa trang rỗng vào sitemap.
		foreach ( array_keys( duy_mockup_v2_levels() ) as $level_slug ) {
			if ( duy_country_level_has_schools( $country_slug, $level_slug ) ) {
				$routes[] = 'quoc-gia/' . $country_slug . '/' . $level_slug;
			}
		}

		// Trang video theo quốc gia chỉ tồn tại khi có video cho quốc gia đó.
		if ( duy_country_video_items( $country_slug, (array) $country ) ) {
			$routes[] = 'quoc-gia/' . $country_slug . '/video';
		}
	}

	foreach ( duy_mockup_v2_majors() as $major ) {
		if ( ! empty( $major['slug'] ) ) {
			$routes[] = 'nganh-hoc/' . (string) $major['slug'];
		}
	}

	foreach ( duy_demo_guides() as $guide ) {
		if ( ! empty( $guide['slug'] ) ) {
			$routes[] = 'lo-trinh-du-hoc/' . (string) $guide['slug'];
		}
	}

	// Bài tin tức demo (route ảo). Bài là WP post thật đã nằm trong wp-sitemap-posts-post → bỏ qua để không trùng.
	foreach ( duy_demo_news() as $article ) {
		if ( ! empty( $article['id'] ) && empty( $article['post_id'] ) ) {
			$routes[] = 'tin-tuc/' . (string) $article['id'];
		}
	}

	$routes = array_values(
		array_filter(
			array_unique( $routes ),
			static fn( string $route ): bool => ! duy_seo_is_noindex_route( $route )
		)
	);

	return $routes;
}

/**
 * MAX(post_modified_gmt) của trường theo quốc gia và quốc gia×bậc học
 * (key 'truong', 'quoc-gia/{c}', 'quoc-gia/{c}/{level}'), cache 1 giờ.
 */
function duy_seo_school_lastmod_map(): array {
	$map = get_transient( 'duy_seo_lastmod_map' );
	if ( is_array( $map ) ) {
		return $map;
	}

	global $wpdb;
	$map = [];

	$overall = $wpdb->get_var( "SELECT MAX(post_modified_gmt) FROM {$wpdb->posts} WHERE post_type = 'school' AND post_status = 'publish'" );
	if ( $overall ) {
		$map['truong'] = (string) $overall;
	}

	$rows = $wpdb->get_results(
		"SELECT t.slug AS country, MAX(p.post_modified_gmt) AS m
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
		 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'country'
		 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
		 WHERE p.post_type = 'school' AND p.post_status = 'publish'
		 GROUP BY t.slug",
		ARRAY_A
	);
	foreach ( (array) $rows as $row ) {
		$map[ 'quoc-gia/' . $row['country'] ] = (string) $row['m'];
	}

	$rows = $wpdb->get_results(
		"SELECT c.slug AS country, l.slug AS level, MAX(p.post_modified_gmt) AS m
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->term_relationships} rc ON rc.object_id = p.ID
		 INNER JOIN {$wpdb->term_taxonomy} tc ON tc.term_taxonomy_id = rc.term_taxonomy_id AND tc.taxonomy = 'country'
		 INNER JOIN {$wpdb->terms} c ON c.term_id = tc.term_id
		 INNER JOIN {$wpdb->term_relationships} rl ON rl.object_id = p.ID
		 INNER JOIN {$wpdb->term_taxonomy} tl ON tl.term_taxonomy_id = rl.term_taxonomy_id AND tl.taxonomy = 'study_level'
		 INNER JOIN {$wpdb->terms} l ON l.term_id = tl.term_id
		 WHERE p.post_type = 'school' AND p.post_status = 'publish'
		 GROUP BY c.slug, l.slug",
		ARRAY_A
	);
	foreach ( (array) $rows as $row ) {
		$map[ 'quoc-gia/' . $row['country'] . '/' . $row['level'] ] = (string) $row['m'];
	}

	set_transient( 'duy_seo_lastmod_map', $map, HOUR_IN_SECONDS );

	return $map;
}
add_action( 'save_post_school', static function (): void { delete_transient( 'duy_seo_lastmod_map' ); } );

function duy_seo_route_lastmod( string $route ): string {
	static $map  = null;
	static $file = null;

	if ( null === $map ) {
		$map  = duy_seo_school_lastmod_map();
		$file = filemtime( DUY_THEME_DIR . '/inc/mockup-v2-data.json' ) ?: time();
	}

	if ( str_starts_with( $route, 'tin-tuc/' ) ) {
		$article = duy_find_by_id( duy_demo_news(), basename( $route ) );
		if ( $article ) {
			return duy_seo_iso_date( (string) ( $article['c'] ?? '' ) ) . 'T00:00:00+00:00';
		}
	}

	if ( isset( $map[ $route ] ) ) {
		$ts = strtotime( $map[ $route ] . ' UTC' );
		if ( $ts ) {
			return gmdate( DATE_W3C, max( $ts, (int) $file ) );
		}
	}

	return gmdate( DATE_W3C, (int) $file );
}

function duy_register_virtual_sitemap_provider(): void {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) || ! function_exists( 'wp_register_sitemap_provider' ) ) {
		return;
	}

	$provider = new class() extends WP_Sitemaps_Provider {
		public function __construct() {
			$this->name        = 'duyroutes';
			$this->object_type = 'duy-route';
		}

		public function get_url_list( $page_num, $object_subtype = '' ) {
			return array_map(
				static fn( string $route ): array => [
					'loc'     => duy_seo_canonical_url( $route ),
					'lastmod' => duy_seo_route_lastmod( $route ),
				],
				duy_virtual_sitemap_routes()
			);
		}

		public function get_max_num_pages( $object_subtype = '' ) {
			return 1;
		}
	};

	wp_register_sitemap_provider( 'duyroutes', $provider );
}
// Ưu tiên 0: phải đăng ký TRƯỚC khi WordPress lõi dựng rewrite rule cho sitemap
// (wp_sitemaps_get_server chạy ở init/10). Đăng ký muộn thì provider vẫn hiện
// trong sitemap index nhưng URL không có rewrite rule → rơi xuống router theme
// và trả về HTML thay vì XML.
add_action( 'init', 'duy_register_virtual_sitemap_provider', 0 );

/* -------------------------------------------------------------------------
 * BreadcrumbList (JSON-LD ở footer — lấy đúng breadcrumb template đã render)
 * ---------------------------------------------------------------------- */

function duy_seo_breadcrumb_jsonld(): void {
	if ( is_admin() || ! function_exists( 'duy_breadcrumb_items' ) || duy_seo_is_utility_request() ) {
		return;
	}

	$items = duy_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	$canonical = duy_seo_canonical_url( duy_seo_path() );
	$elements  = [];
	foreach ( array_values( $items ) as $index => $item ) {
		$elements[] = [
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => (string) $item['label'],
			'item'     => '' !== (string) $item['url'] ? duy_seo_absolute_url( (string) $item['url'] ) : $canonical,
		];
	}

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'@id'             => $canonical . '#breadcrumb',
		'itemListElement' => $elements,
	];

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'duy_seo_breadcrumb_jsonld', 5 );

/* -------------------------------------------------------------------------
 * llms.txt — bản đồ nội dung dạng Markdown cho AI/answer engine (llmstxt.org)
 * ---------------------------------------------------------------------- */

/** Tiêu đề trang bỏ hậu tố thương hiệu, dùng làm nhãn link trong llms.txt. */
function duy_seo_route_label( string $route ): string {
	$title = duy_seo_title( $route );
	$title = (string) preg_replace( '/\s+[—–-]\s+Ban Du học Hội TESOL TP.HCM$/u', '', $title );

	return trim( $title );
}

function duy_seo_llms_line( string $route ): string {
	$description = duy_seo_trim_description( duy_seo_description( $route ), 160 );

	return sprintf( '- [%s](%s)%s', duy_seo_route_label( $route ), duy_seo_canonical_url( $route ), '' !== $description ? ': ' . $description : '' );
}

function duy_seo_llms_txt(): void {
	if ( is_admin() || 'llms.txt' !== duy_seo_path() ) {
		return;
	}

	global $wp_query;
	if ( $wp_query instanceof WP_Query ) {
		$wp_query->is_404 = false; // WP đã đánh dấu 404 trước template_redirect → duy_seo_title sẽ trả "Không tìm thấy trang".
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Robots-Tag: noindex' );

	$routes    = duy_virtual_sitemap_routes();
	$hotline   = trim( (string) duy_option( 'hotline', '0906.510.747' ) );
	$offices   = array_map(
		static fn( array $office ): string => duy_office_display_name( (string) $office['name'] ) . ' – ' . (string) $office['address'],
		duy_offices()
	);
	$countries = duy_demo_countries();
	$schools   = count( duy_demo_schools() );

	$hubs = [ '', 'lo-trinh-du-hoc', 'quoc-gia', 'nganh-hoc', 'truong', 'hoc-bong', 'su-kien', 'hoc-sinh', 'tin-tuc', 've-chung-toi', 'dich-vu', 'faq', 'tai-cam-nang' ];

	$out   = [];
	$out[] = '# Ban Du học Hội TESOL TP.HCM';
	$out[] = '';
	$out[] = '> ' . duy_seo_brand_description();
	$out[] = '';
	$out[] = sprintf(
		'Ban Du học Hội TESOL TP.HCM ("Avenue to New World") là đơn vị tư vấn du học tại Việt Nam, đồng hành cùng học sinh và phụ huynh từ chọn quốc gia, trường, học bổng đến hồ sơ visa và chuẩn bị lên đường. Trọng tâm: du học Mỹ, Úc, Canada, New Zealand; ngoài ra có Anh, Hà Lan, Singapore, Malaysia, Thụy Sỹ, Đức, Hàn Quốc, Thổ Nhĩ Kỳ, Philippines. Văn phòng: %s. Hotline %s. Nội dung tiếng Việt.',
		implode( '; ', $offices ),
		$hotline
	);
	$out[] = '';
	$out[] = sprintf(
		'Website có %d trang trường (THPT, cao đẳng, đại học, Anh ngữ) tại %d quốc gia, trang quốc gia theo bậc học, ngành học, lộ trình, học bổng, sự kiện và tin tức. Học phí và điều kiện đầu vào là số liệu tham khảo, cần xác nhận với Ban Du học Hội TESOL TP.HCM tại thời điểm nộp hồ sơ.',
		$schools,
		count( $countries )
	);
	$out[] = '';
	$out[] = '## Trang chính';
	foreach ( $hubs as $route ) {
		$out[] = duy_seo_llms_line( $route );
	}

	$out[] = '';
	$out[] = '## Quốc gia và bậc học';
	foreach ( $routes as $route ) {
		if ( preg_match( '#^quoc-gia/[^/]+(/[^/]+)?$#', $route ) && ! str_ends_with( $route, '/video' ) ) {
			$out[] = duy_seo_llms_line( $route );
		}
	}

	$out[] = '';
	$out[] = '## Ngành học';
	foreach ( $routes as $route ) {
		if ( preg_match( '#^nganh-hoc/[^/]+$#', $route ) ) {
			$out[] = duy_seo_llms_line( $route );
		}
	}

	$out[] = '';
	$out[] = '## Lộ trình du học';
	foreach ( $routes as $route ) {
		if ( preg_match( '#^lo-trinh-du-hoc/[^/]+$#', $route ) ) {
			$out[] = duy_seo_llms_line( $route );
		}
	}

	$out[] = '';
	$out[] = '## Danh sách trường';
	$out[] = sprintf( '- [Tìm trường du học](%s): %d trang trường theo quốc gia, bậc học, ngành và chi phí tham khảo; mỗi trang có chương trình học, điều kiện đầu vào, học phí tham khảo, học bổng.', duy_seo_canonical_url( 'truong' ), $schools );
	$out[] = sprintf( '- [Sitemap trang trường (XML)](%s)', home_url( '/wp-sitemap-posts-school-1.xml' ) );

	$out[] = '';
	$out[] = '## Tin tức';
	foreach ( $routes as $route ) {
		if ( preg_match( '#^tin-tuc/[^/]+$#', $route ) ) {
			$out[] = duy_seo_llms_line( $route );
		}
	}
	foreach ( duy_demo_news() as $article ) {
		if ( ! empty( $article['post_id'] ) && ! empty( $article['id'] ) ) {
			$out[] = duy_seo_llms_line( 'tin-tuc/' . (string) $article['id'] );
		}
	}

	$out[] = '';
	$out[] = '## Liên hệ';
	$out[] = duy_seo_llms_line( 'lien-he' );
	$out[] = '- Hotline: ' . $hotline;
	foreach ( $offices as $office ) {
		$out[] = '- ' . $office;
	}

	$out[] = '';
	$out[] = '## Chính sách';
	$out[] = duy_seo_llms_line( 'chinh-sach-bao-mat' );
	$out[] = sprintf( '- [Sitemap XML](%s)', home_url( '/wp-sitemap.xml' ) );
	$out[] = '';

	echo implode( "\n", $out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/plain
	exit;
}
add_action( 'template_redirect', 'duy_seo_llms_txt', 1 );

/* -------------------------------------------------------------------------
 * IndexNow (Bing / Copilot / DuckDuckGo) — tự báo khi đăng/sửa/gỡ bài
 * ---------------------------------------------------------------------- */

const DUY_INDEXNOW_POST_TYPES = [ 'post', 'page', 'school', 'event', 'scholarship' ];

/** Key từ theme option (Bing Webmaster Tools → API access → IndexNow); '' khi chưa cấu hình. */
function duy_indexnow_key(): string {
	$key = trim( (string) duy_option( 'indexnow_key', '' ) );

	return preg_match( '/^[A-Za-z0-9-]{8,128}$/', $key ) ? $key : '';
}

/** Phục vụ /{key}.txt (IndexNow xác minh chủ sở hữu) — theme không tạo được file ở docroot nên trả qua route ảo. */
function duy_indexnow_key_file(): void {
	$key = duy_indexnow_key();
	if ( is_admin() || '' === $key || duy_seo_path() !== $key . '.txt' ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'X-Robots-Tag: noindex' );
	echo $key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi [A-Za-z0-9-] đã kiểm tra
	exit;
}
add_action( 'template_redirect', 'duy_indexnow_key_file', 1 );

/** Gom URL trong request; gửi 1 lần ở shutdown (non-blocking). */
function duy_indexnow_queue( string $url = '' ): array {
	static $urls = [];

	if ( '' !== $url ) {
		if ( ! $urls ) {
			add_action( 'shutdown', 'duy_indexnow_flush' );
		}
		$urls[ $url ] = true;
	}

	return array_keys( $urls );
}

function duy_indexnow_on_transition( string $new_status, string $old_status, WP_Post $post ): void {
	if ( ! in_array( $post->post_type, DUY_INDEXNOW_POST_TYPES, true ) || '' === duy_indexnow_key() ) {
		return;
	}
	if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
		return; // nháp ↔ nháp: không có URL công khai thay đổi.
	}
	if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
		return;
	}

	// Gỡ bài / đưa vào thùng rác: báo URL cũ (bỏ hậu tố __trashed) để công cụ tìm kiếm cào lại và thấy 404.
	$public            = clone $post;
	$public->post_status = 'publish';
	$public->post_name   = (string) preg_replace( '/__trashed$/', '', (string) $post->post_name );
	if ( '' === $public->post_name ) {
		return;
	}

	$url = (string) get_permalink( $public );
	if ( '' === $url || ! str_starts_with( $url, home_url( '/' ) ) ) {
		return;
	}

	duy_indexnow_queue( $url );
}
add_action( 'transition_post_status', 'duy_indexnow_on_transition', 10, 3 );

function duy_indexnow_flush(): void {
	$urls = duy_indexnow_queue();
	$key  = duy_indexnow_key();
	$host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );

	if ( ! $urls || '' === $key || '' === $host || in_array( $host, [ 'localhost', '127.0.0.1' ], true ) ) {
		return;
	}

	$body = [
		'host'        => $host,
		'key'         => $key,
		'keyLocation' => home_url( '/' . $key . '.txt' ),
		'urlList'     => array_slice( $urls, 0, 10000 ),
	];

	$response = wp_remote_post(
		'https://api.indexnow.org/indexnow',
		[
			'timeout'  => 5,
			'blocking' => false,
			'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
			'body'     => wp_json_encode( $body, JSON_UNESCAPED_SLASHES ),
		]
	);

	update_option(
		'duy_indexnow_last',
		[
			'time'  => time(),
			'urls'  => $body['urlList'],
			'error' => is_wp_error( $response ) ? $response->get_error_message() : '',
		],
		false
	);
}

/* -------------------------------------------------------------------------
 * Trang kết quả có phân trang / bộ lọc
 *
 * Template gọi duy_seo_pagination([...]) TRƯỚC get_header() để <head> biết
 * đang ở trang mấy. Quy ước index: trang ?trang=N canonical về chính nó và
 * vẫn index (nội dung khác nhau); URL có bộ lọc (q/country/level/major/fee)
 * là biến thể trùng lặp → noindex, follow.
 * ---------------------------------------------------------------------- */

/**
 * @param array|null $data ['base' => path, 'query' => duy_finder_query(), 'page_data' => duy_paginate(...)]
 */
function duy_seo_pagination( ?array $data = null ): array {
	static $current = [];

	if ( null !== $data ) {
		$current = $data;
	}

	return $current;
}

function duy_seo_request_page(): int {
	$pagination = duy_seo_pagination();

	return max( 1, (int) ( $pagination['page_data']['page'] ?? 0 ) );
}

/** Trang hiện tại có bộ lọc đang bật không. */
function duy_seo_request_filtered(): bool {
	$pagination = duy_seo_pagination();

	return ! empty( $pagination['query'] ) && duy_finder_has_filters( (array) $pagination['query'] );
}

/** Canonical tự trỏ: giữ tham số lọc và số trang để không gộp nhầm các URL khác nội dung. */
function duy_seo_request_canonical( string $path ): string {
	$pagination = duy_seo_pagination();

	if ( empty( $pagination['base'] ) || empty( $pagination['query'] ) ) {
		return duy_seo_canonical_url( $path );
	}

	return duy_finder_page_url( (string) $pagination['base'], (array) $pagination['query'], duy_seo_request_page() );
}

/** Tiêu đề kèm "– Trang N" từ trang 2 trở đi để không trùng tiêu đề trang 1. */
function duy_seo_request_title( string $path ): string {
	$title = duy_seo_title( $path );
	$page  = duy_seo_request_page();

	return $page > 1 ? $title . ' – Trang ' . $page : $title;
}

/** <link rel="prev"/"next"> cho trang kết quả (Bing và nhiều AI crawler vẫn dùng). */
function duy_seo_pagination_links(): void {
	$pagination = duy_seo_pagination();
	if ( empty( $pagination['page_data'] ) || (int) ( $pagination['page_data']['pages'] ?? 1 ) < 2 ) {
		return;
	}

	$page  = duy_seo_request_page();
	$pages = (int) $pagination['page_data']['pages'];
	$base  = (string) $pagination['base'];
	$query = (array) $pagination['query'];

	if ( $page > 1 ) {
		printf( "\t" . '<link rel="prev" href="%s">' . "\n", esc_url( duy_finder_page_url( $base, $query, $page - 1 ) ) );
	}
	if ( $page < $pages ) {
		printf( "\t" . '<link rel="next" href="%s">' . "\n", esc_url( duy_finder_page_url( $base, $query, $page + 1 ) ) );
	}
}
