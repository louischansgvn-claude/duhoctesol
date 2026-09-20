<?php
/**
 * Postsecondary (US college / university) integration.
 *
 * Bridges the imported `school` CPT posts (US colleges & universities, tagged
 * country=Mỹ + study_level=cao-dang|dai-hoc, identity meta `_duy_ps_id`) into
 * the theme's front-end data layer so they render on /quoc-gia/my/cao-dang/,
 * /quoc-gia/my/dai-hoc/, /truong/ and /truong/{slug} — exactly like the THPT
 * set does, but as their own two study levels.
 *
 * Kept separate from the THPT layer (inc/high-schools.php) on purpose: the two
 * never mix because postsecondary posts carry `_duy_ps_id` (not
 * `_duy_hs_school_id`) and study_level cao-dang|dai-hoc (not thpt).
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vietnamese display level for a postsecondary study_level slug.
 */
function duy_ps_level_label( string $slug ): string {
	$map = [ 'cao-dang' => 'Cao đẳng', 'dai-hoc' => 'Đại học' ];
	return $map[ $slug ] ?? 'Đại học';
}

/**
 * Query imported college/university posts and map them to the theme's card
 * shape. Result is cached per-request. Keyed by slug (card `id`).
 *
 * @return array<string,array>
 */
function duy_ps_cpt_cards(): array {
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
			'meta_key'         => '_duy_ps_id',
			'tax_query'        => [
				[
					'taxonomy' => 'study_level',
					'field'    => 'slug',
					'terms'    => [ 'cao-dang', 'dai-hoc' ],
				],
			],
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

		// Level from the assigned study_level term (fallback Đại học).
		$level_slug = (string) get_post_meta( $post->ID, '_duy_ps_level_slug', true );
		$level      = duy_ps_level_label( $level_slug );

		$state    = (string) get_post_meta( $post->ID, '_duy_hs_state', true );
		$type     = (string) get_post_meta( $post->ID, '_duy_ps_type', true );
		$fee      = (string) get_post_meta( $post->ID, '_duy_ps_fee', true );
		$fee_band = (string) get_post_meta( $post->ID, '_duy_ps_fee_band', true );

		$desc = (string) $post->post_excerpt;
		if ( function_exists( 'duy_hs_public_text' ) ) {
			$desc = duy_hs_public_text( $desc );
		}

		$cache[ $id ] = [
			'id'        => $id,
			'date'      => get_post_time( 'Y-m-d', false, $post ),
			'featured'  => false,
			'n'         => get_the_title( $post ),
			'c'         => 'Mỹ',
			'code'      => 'US',
			'r'         => $type,
			'fee'       => $fee,
			'feeBand'   => $fee_band,
			'level'     => $level,
			'major'     => '',
			'tag'       => '',
			'city'      => $state ?: 'Mỹ',
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
