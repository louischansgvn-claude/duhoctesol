<?php
/**
 * Canada / Australia school integration (THPT + Cao đẳng + Đại học).
 *
 * Bridges the imported `school` CPT posts (Canada & Australia, tagged
 * country=canada|uc + study_level=thpt|cao-dang|dai-hoc, identity meta
 * `_duy_caau_id`) into the theme's front-end data layer so they render on
 * /quoc-gia/canada/…, /quoc-gia/uc/…, /truong/ and /truong/{slug}.
 *
 * Kept fully separate from the US layers (inc/high-schools.php,
 * inc/postsecondary.php): those never map these posts because Canada/Australia
 * posts carry `_duy_caau_id` (not `_duy_hs_school_id` / `_duy_ps_id`), and
 * high-schools.php explicitly excludes `_duy_caau_id` posts to avoid double
 * mapping the THPT records.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vietnamese display level for a Canada/Australia study_level slug.
 */
function duy_caau_level_label( string $slug ): string {
	$map = [ 'thpt' => 'THPT', 'cao-dang' => 'Cao đẳng', 'dai-hoc' => 'Đại học' ];
	return $map[ $slug ] ?? 'Đại học';
}

/**
 * Query imported Canada/Australia school posts and map them to the theme's
 * card shape. Result is cached per-request. Keyed by slug (card `id`).
 *
 * @return array<string,array>
 */
function duy_caau_cpt_cards(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = [];

	if ( ! function_exists( 'get_posts' ) ) {
		return $cache;
	}

	$posts = get_posts(
		[
			'post_type'        => 'school',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'meta_key'         => '_duy_caau_id',
		]
	);

	foreach ( $posts as $post ) {
		if ( ! $post ) {
			continue;
		}
		$id = $post->post_name;
		if ( '' === $id ) {
			continue;
		}

		// Featured image (logo) → card thumbnail.
		$thumb_id  = get_post_thumbnail_id( $post->ID );
		$thumb_url = $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
		$thumb_alt = $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
		$thumb_bg  = (string) get_post_meta( $post->ID, '_duy_hs_thumb_bg', true ) ?: 'light';

		// Country from the assigned term (fallback to stored slug meta).
		$country_terms = wp_get_object_terms( $post->ID, 'country', [ 'fields' => 'names' ] );
		$country_name  = ( ! is_wp_error( $country_terms ) && $country_terms ) ? (string) $country_terms[0] : '';
		if ( '' === $country_name ) {
			$country_name = ( 'uc' === get_post_meta( $post->ID, '_duy_caau_country_slug', true ) ) ? 'Úc' : 'Canada';
		}
		$country_code = function_exists( 'duy_country_code' ) ? duy_country_code( $country_name ) : '';

		$level_slug = (string) get_post_meta( $post->ID, '_duy_caau_level_slug', true );
		$level      = duy_caau_level_label( $level_slug );

		$state = (string) get_post_meta( $post->ID, '_duy_hs_state', true );
		$type  = (string) get_post_meta( $post->ID, '_duy_caau_type', true );

		$desc = (string) $post->post_excerpt;
		if ( function_exists( 'duy_hs_public_text' ) ) {
			$desc = duy_hs_public_text( $desc );
		}

		$cache[ $id ] = [
			'id'        => $id,
			'date'      => get_post_time( 'Y-m-d', false, $post ),
			'featured'  => false,
			'n'         => get_the_title( $post ),
			'c'         => $country_name,
			'code'      => $country_code ?: ( 'Úc' === $country_name ? 'AU' : 'CA' ),
			'r'         => $type,
			'fee'       => 'Chi phí tham khảo',
			'feeBand'   => '',
			'level'     => $level,
			'major'     => '',
			'tag'       => '',
			'city'      => $state ?: $country_name,
			'programs'  => [],
			'desc'      => $desc,
			'hs_post'   => (int) $post->ID,
			'thumb_url' => $thumb_url,
			'thumb_alt' => $thumb_alt,
			'thumb_bg'  => $thumb_bg,
		];
	}

	return $cache;
}
