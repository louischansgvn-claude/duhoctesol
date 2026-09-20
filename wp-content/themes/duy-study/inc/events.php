<?php
/**
 * Imported event recaps (sự kiện đã diễn ra).
 *
 * Bridges `event` CPT posts imported from the events manifest (identity meta
 * `_duy_ev_id`) into the theme's front-end data layer so they render on
 * /su-kien/ and /su-kien/{slug}.
 *
 * Chỉ chạm tới bài `event`; không liên quan tới các lớp dữ liệu trường học
 * (inc/high-schools.php, inc/postsecondary.php, inc/intl-schools.php, inc/othc.php).
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query imported event recaps and map them to the theme's event card shape.
 * Result is cached per-request. Keyed by slug (card `id`).
 *
 * @return array<string,array>
 */
function duy_ev_cpt_cards(): array {
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
			'post_type'        => 'event',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'suppress_filters' => false,
			'meta_key'         => '_duy_ev_id',
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

		$thumb_id  = get_post_thumbnail_id( $post->ID );
		$thumb_url = $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'full' ) : '';
		$thumb_alt = $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';

		$date_text = (string) get_post_meta( $post->ID, '_duy_ev_date_text', true );
		$datetime  = (string) get_post_meta( $post->ID, '_duy_ev_datetime', true );

		$desc = (string) $post->post_excerpt;
		if ( function_exists( 'duy_hs_public_text' ) ) {
			$desc = duy_hs_public_text( $desc );
		}

		$cache[ $id ] = [
			'id'        => $id,
			'date'      => $datetime,
			'datetime'  => $datetime,
			'featured'  => false,
			'n'         => get_the_title( $post ),
			'c'         => $date_text,
			'type'      => (string) get_post_meta( $post->ID, '_duy_ev_type', true ) ?: 'Hội thảo',
			'tag'       => '',
			'time'      => $date_text,
			'place'     => '',
			'agenda'    => [],
			'desc'      => $desc,
			'cta'       => 'Xem hình ảnh',
			'ev_post'   => (int) $post->ID,
			'thumb_url' => $thumb_url,
			'thumb_alt' => $thumb_alt,
		];
	}

	return $cache;
}
