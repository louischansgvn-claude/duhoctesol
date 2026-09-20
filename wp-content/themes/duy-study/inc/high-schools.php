<?php
/**
 * High-school (THPT) integration.
 *
 * Bridges the imported `school` CPT posts (US high schools, tagged
 * country=Mỹ + study_level=THPT, data in `_duy_hs_*` meta) into the
 * theme's front-end data layer so they render on /quoc-gia/my/thpt/,
 * /truong/ and /truong/{slug}.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal/editorial markers that must never appear on the public site.
 * Any sentence/segment containing one of these is dropped from display.
 */
function duy_hs_internal_markers(): array {
	return [
		'cần xác minh', 'chưa xác minh', 'cần đối chiếu', 'đối chiếu thêm', 'đối chiếu',
		'trước khi publish', 'trước khi import', 'giữ nhãn', 'chưa thấy', 'cần kiểm',
		'cần verify', 'verify', 'nên nhấn', 'nội dung nên', 'mẫu nghiên cứu', 'chưa rõ',
		'cần cập nhật', '{{todo', 'nên đối chiếu', 'chưa công bố', 'nghiên cứu này',
		'educatius', 'according to secondary', 'secondary public references', 'official profile',
		'if displayed', 'cross-check',
	];
}

/**
 * Strip internal/verification sentences from a public text field.
 * Splits into segments (sentence / newline / " | ") and removes any segment
 * that contains an internal marker. Returns cleaned text (may be empty).
 */
function duy_hs_public_text( string $text ): string {
	$text = trim( $text );
	if ( '' === $text ) {
		return '';
	}
	$markers = duy_hs_internal_markers();
	// Normalise separators, then split into sentence-ish segments.
	$parts = preg_split( '/(?<=[.!?…])\s+|\n+|\s*\|\s*/u', $text );
	$keep  = [];
	foreach ( (array) $parts as $seg ) {
		$seg = trim( $seg );
		if ( '' === $seg ) {
			continue;
		}
		$low  = mb_strtolower( $seg );
		$skip = false;
		foreach ( $markers as $m ) {
			if ( mb_strpos( $low, $m ) !== false ) {
				$skip = true;
				break;
			}
		}
		if ( ! $skip ) {
			$keep[] = $seg;
		}
	}
	return trim( implode( ' ', $keep ) );
}

/**
 * Public "why choose" bullets: split on " | ", drop internal ones.
 */
function duy_hs_public_list( string $raw ): array {
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return [];
	}
	$markers = duy_hs_internal_markers();
	$out     = [];
	foreach ( array_map( 'trim', explode( '|', $raw ) ) as $item ) {
		if ( '' === $item ) {
			continue;
		}
		$low = mb_strtolower( $item );
		foreach ( $markers as $m ) {
			if ( mb_strpos( $low, $m ) !== false ) {
				continue 2;
			}
		}
		$out[] = $item;
	}
	return $out;
}

/**
 * Vietnamese labels for enumerated English field values.
 * Proper nouns / terms (state names, Niche, PG, IB…) are left as-is.
 */
function duy_hs_vi( string $field, string $value ): string {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	$maps = [
		'school_type'      => [ 'public' => 'Công lập', 'private' => 'Tư thục', 'charter' => 'Charter' ],
		'category'         => [ 'day school' => 'Ngoại trú', 'boarding school' => 'Nội trú', 'day' => 'Ngoại trú', 'boarding' => 'Nội trú' ],
		'religion'         => [ 'no religious affiliation' => 'Không theo tôn giáo', 'catholic' => 'Công giáo', 'christian' => 'Cơ Đốc giáo', 'lutheran' => 'Tin Lành Luther' ],
		'one_year_diploma' => [ 'yes' => 'Có', 'no' => 'Không' ],
	];
	$m = $maps[ $field ] ?? [];
	return $m[ mb_strtolower( $value ) ] ?? $value;
}

/**
 * "9th - 12th" / "9-12th, PG" → "Lớp 9–12" / "Lớp 9–12, PG".
 */
function duy_hs_grades( string $g ): string {
	$g = trim( $g );
	if ( '' === $g ) {
		return '';
	}
	$g = preg_replace( '/(\d+)(st|nd|rd|th)/i', '$1', $g ); // 9th -> 9
	$g = preg_replace( '/\s*-\s*/', '–', $g );             // dash -> en dash
	$g = preg_replace( '/\s+/', ' ', $g );
	return 'Lớp ' . trim( $g );
}

/**
 * Extract a founding year from a messy founded string; '' if none.
 */
function duy_hs_founded_year( string $raw ): string {
	return preg_match( '/\b(1[7-9]\d{2}|20\d{2})\b/', $raw, $m ) ? $m[1] : '';
}

/**
 * Fee band bucket from a yearly program fee (USD).
 */
function duy_hs_fee_band( int $usd ): string {
	if ( $usd <= 0 ) {
		return '';
	}
	if ( $usd < 25000 ) {
		return 'low';
	}
	if ( $usd <= 45000 ) {
		return 'mid';
	}
	return 'high';
}

/**
 * Format a USD amount, e.g. 18950 => "Từ 18.950 USD".
 */
function duy_hs_fee_label( $lowest, string $spring_status = '', string $fall_status = '' ): string {
	$n = (int) preg_replace( '/[^0-9]/', '', (string) $lowest );
	if ( $n > 0 ) {
		return 'Từ ' . number_format( $n, 0, ',', '.' ) . ' USD';
	}
	$status = trim( $spring_status ?: $fall_status );
	return $status ? ( 'Chương trình: ' . $status ) : 'Học phí: cần xác minh';
}

/**
 * Query imported high-school posts and map them to the theme's card shape.
 * Result is cached per-request.
 *
 * @return array<string,array>  keyed by slug (card `id`).
 */
function duy_hs_cpt_cards(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = [];

	if ( ! function_exists( 'get_posts' ) ) {
		return $cache;
	}

	// A school is a "THPT" when tagged Bậc học = THPT (the signal editors set),
	// or — for back-compat — it carries the import's `_duy_hs_school_id` meta.
	$posts = get_posts(
		[
			'post_type'        => 'school',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'tax_query'        => [
				[
					'taxonomy' => 'study_level',
					'field'    => 'slug',
					'terms'    => 'thpt',
				],
			],
			// Canada/Australia and other-countries THPT posts are mapped by
			// inc/intl-schools.php / inc/othc.php instead — exclude them here to
			// avoid duplicates.
			'meta_query'       => [
				[
					'key'     => '_duy_caau_id',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_duy_othc_id',
					'compare' => 'NOT EXISTS',
				],
			],
		]
	);
	// Include any legacy import posts missing the term.
	$legacy = get_posts(
		[
			'post_type'        => 'school',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'meta_key'         => '_duy_hs_school_id',
			'suppress_filters' => false,
		]
	);
	$seen = [];
	foreach ( $posts as $p ) {
		$seen[ $p->ID ] = true;
	}
	foreach ( $legacy as $pid ) {
		if ( empty( $seen[ $pid ] ) ) {
			$posts[] = get_post( $pid );
		}
	}

	foreach ( $posts as $post ) {
		if ( ! $post ) {
			continue;
		}
		$id = $post->post_name;
		if ( '' === $id ) {
			continue;
		}
		$m = static fn( string $key ) => (string) get_post_meta( $post->ID, '_duy_hs_' . $key, true );

		// Featured image (logo) → used as the card thumbnail when present.
		$thumb_id  = get_post_thumbnail_id( $post->ID );
		$thumb_url = $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
		$thumb_alt = $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
		$thumb_bg  = (string) get_post_meta( $post->ID, '_duy_hs_thumb_bg', true ) ?: 'light';

		// Country from the assigned term (fallback Mỹ).
		$country_terms = wp_get_object_terms( $post->ID, 'country', [ 'fields' => 'names' ] );
		$country_name  = ( ! is_wp_error( $country_terms ) && $country_terms ) ? (string) $country_terms[0] : 'Mỹ';
		$country_code  = function_exists( 'duy_country_code' ) ? duy_country_code( $country_name ) : 'US';

		$state       = $m( 'state' );
		$school_type = $m( 'school_type' );
		$category    = $m( 'category' );
		$lowest      = $m( 'lowest_program_fee' );
		$type_disp   = $m( 'school_type_display' );          // SEO import
		$tuition     = $m( 'tuition_display' );              // SEO import
		$tuition_num = (int) preg_replace( '/[^0-9]/', '', $m( 'tuition_from' ) ?: $lowest );

		$note = $type_disp
			? [ $type_disp ]
			: array_values(
				array_filter(
					[ duy_hs_vi( 'school_type', $school_type ), duy_hs_vi( 'category', $category ) ],
					static fn( $v ) => '' !== trim( (string) $v )
				)
			);

		$cache[ $id ] = [
			'id'       => $id,
			'date'     => get_post_time( 'Y-m-d', false, $post ),
			'featured' => false,
			'n'        => get_the_title( $post ),
			'c'        => $country_name,
			'code'     => $country_code ?: 'US',
			'r'        => implode( ' · ', $note ),
			'fee'      => $tuition ?: duy_hs_fee_label( $lowest, $m( 'spring_status' ), $m( 'fall_status' ) ),
			'feeBand'  => duy_hs_fee_band( $tuition_num ),
			'level'    => 'THPT',
			'major'    => '',
			'tag'      => '',
			'city'     => $state ?: $country_name,
			'programs' => [],
			'desc'     => duy_hs_public_text( $post->post_excerpt ?: wp_trim_words( (string) $m( 'profile' ), 40 ) ),
			'hs_post'  => (int) $post->ID,
			'thumb_url' => $thumb_url,
			'thumb_alt' => $thumb_alt,
			'thumb_bg'  => $thumb_bg,
		];
	}

	return $cache;
}
