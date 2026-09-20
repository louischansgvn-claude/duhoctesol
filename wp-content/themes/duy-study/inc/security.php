<?php
/**
 * Basic WordPress hardening.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'wp_head', 'wp_generator' );

function duy_disable_author_archives(): void {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'duy_disable_author_archives' );

function duy_hide_rest_users( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\\d]+)'] );
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'duy_hide_rest_users' );

function duy_send_frontend_security_headers(): void {
	if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	// TODO server-level: enable Strict-Transport-Security after production SSL is confirmed.
}
add_action( 'send_headers', 'duy_send_frontend_security_headers' );
