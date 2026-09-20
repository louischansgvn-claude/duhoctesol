<?php
/**
 * Single school template — delegates to the universal blog/article layout
 * (`single-school-hs.php`) for EVERY school (THPT, university/college, demo).
 *
 * @package DUY_Study
 */

$id     = (string) duy_route_value( 'id', '' );
$school = duy_find_by_id( duy_demo_schools(), $id );

if ( ! $school ) {
	include duy_template_path( '404.php' );
	return;
}

$school_post = function_exists( 'get_page_by_path' ) ? get_page_by_path( $id, OBJECT, 'school' ) : null;

set_query_var( 'hs_school', $school );
set_query_var( 'hs_post_id', $school_post ? (int) $school_post->ID : 0 );
include duy_template_path( 'single-school-hs.php' );
