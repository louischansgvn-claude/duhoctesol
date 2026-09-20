<?php
/**
 * URL routing shim for demo parity before database pages are seeded.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_request_path(): string {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$path = wp_parse_url( $uri, PHP_URL_PATH );

	return trim( (string) $path, '/' );
}

function duy_template_path( string $relative ): string {
	return DUY_THEME_DIR . '/' . ltrim( $relative, '/' );
}

/**
 * Route ảo: bỏ cờ 404. Nếu WP không tìm thấy post thật (pagename không tồn tại,
 * hoặc archive CPT mà theme render bằng dữ liệu riêng) thì xoá luôn các cờ
 * page/single/archive — giữ cờ mà không có post khiến get_body_class(),
 * is_page('…') đọc thuộc tính của null / WP_Post_Type (warning trong debug.log).
 * Post thật (trường, bài viết, học bổng CPT) giữ nguyên cờ singular.
 */
function duy_route_reset_query_flags( WP_Query $query ): void {
	$query->is_404 = false;

	if ( $query->get_queried_object() instanceof WP_Post ) {
		return;
	}

	// Đường dẫn 2 cấp (vd quoc-gia/my) khớp rule attachment của WP → is_attachment
	// khiến redirect_canonical gọi get_attachment_link() với $post null.
	$query->is_page              = false;
	$query->is_single            = false;
	$query->is_singular          = false;
	$query->is_attachment        = false;
	$query->is_archive           = false;
	$query->is_post_type_archive = false;
	$query->is_home              = false;
	unset( $query->queried_object, $query->queried_object_id );
}

function duy_route_mark_found(): void {
	global $wp_query;

	if ( $wp_query instanceof WP_Query ) {
		duy_route_reset_query_flags( $wp_query );
	}

	status_header( 200 );
}

function duy_route_template_include( string $template ): string {
	if ( is_admin() ) {
		return $template;
	}

	$path = duy_request_path();

	if ( '' === $path ) {
		return $template;
	}

	$static_routes = [
		'lo-trinh-du-hoc'     => [ 'template' => 'page-templates/lo-trinh.php', 'context' => [ 'route' => 'guides' ] ],
		'quoc-gia'            => [ 'template' => 'page-templates/quoc-gia.php', 'context' => [ 'route' => 'countries' ] ],
		'nganh-hoc'           => [ 'template' => 'page-templates/nganh-hoc.php', 'context' => [ 'route' => 'majors' ] ],
		'truong'              => [ 'template' => 'archive-school.php', 'context' => [ 'route' => 'schools' ] ],
		'hoc-bong'            => [ 'template' => 'archive-scholarship.php', 'context' => [ 'route' => 'scholarships' ] ],
		'su-kien'             => [ 'template' => 'archive-event.php', 'context' => [ 'route' => 'events' ] ],
		'hoc-sinh'            => [ 'template' => 'archive-student_story.php', 'context' => [ 'route' => 'stories' ] ],
		'tin-tuc'             => [ 'template' => 'archive.php', 'context' => [ 'route' => 'news' ] ],
		've-chung-toi'        => [ 'template' => 'page-templates/ve-chung-toi.php', 'context' => [ 'route' => 'about' ] ],
		'lien-he'             => [ 'template' => 'page-templates/lien-he.php', 'context' => [ 'route' => 'contact' ] ],
		'faq'                 => [ 'template' => 'page-templates/faq.php', 'context' => [ 'route' => 'faq' ] ],
		'dich-vu'             => [ 'template' => 'page-templates/dich-vu.php', 'context' => [ 'route' => 'services' ] ],
		'tai-cam-nang'        => [ 'template' => 'page-templates/tai-cam-nang.php', 'context' => [ 'route' => 'download' ] ],
		'chinh-sach-bao-mat'  => [ 'template' => 'page-templates/privacy.php', 'context' => [ 'route' => 'privacy' ] ],
	];

	if ( isset( $static_routes[ $path ] ) ) {
		duy_route_context( $static_routes[ $path ]['context'] );
		$file = duy_template_path( $static_routes[ $path ]['template'] );

		duy_route_mark_found();

		return file_exists( $file ) ? $file : $template;
	}

	if ( preg_match( '#^quoc-gia/([^/]+)/video$#', $path, $matches ) ) {
		duy_route_context(
			[
				'route'          => 'country-video',
				'slug'           => sanitize_title( $matches[1] ),
				'requested_path' => $path,
			]
		);

		$file = duy_template_path( 'page-templates/country-video.php' );

		duy_route_mark_found();

		return file_exists( $file ) ? $file : $template;
	}

	if ( preg_match( '#^quoc-gia/([^/]+)/([^/]+)$#', $path, $matches ) ) {
		duy_route_context(
			[
				'route'          => 'country-level',
				'slug'           => sanitize_title( $matches[1] ),
				'level'          => sanitize_title( $matches[2] ),
				'requested_path' => $path,
			]
		);

		$file = duy_template_path( 'page-templates/bac-hoc.php' );

		duy_route_mark_found();

		return file_exists( $file ) ? $file : $template;
	}

	if ( preg_match( '#^nganh-hoc/([^/]+)$#', $path, $matches ) ) {
		duy_route_context(
			[
				'route'          => 'major',
				'slug'           => sanitize_title( $matches[1] ),
				'requested_path' => $path,
			]
		);

		$file = duy_template_path( 'page-templates/major.php' );

		duy_route_mark_found();

		return file_exists( $file ) ? $file : $template;
	}

	$dynamic_routes = [
		'#^lo-trinh-du-hoc/([^/]+)$#' => [ 'template' => 'page-templates/guide.php', 'key' => 'slug' ],
		'#^quoc-gia/([^/]+)$#'        => [ 'template' => 'page-templates/country.php', 'key' => 'slug' ],
		'#^truong/([^/]+)$#'          => [ 'template' => 'single-school.php', 'key' => 'id' ],
		'#^hoc-bong/([^/]+)$#'        => [ 'template' => 'single-scholarship.php', 'key' => 'id' ],
		'#^su-kien/([^/]+)$#'         => [ 'template' => 'single-event.php', 'key' => 'id' ],
		'#^hoc-sinh/([^/]+)$#'        => [ 'template' => 'single-student_story.php', 'key' => 'id' ],
		'#^tin-tuc/([^/]+)$#'         => [ 'template' => 'single.php', 'key' => 'id' ],
	];

	foreach ( $dynamic_routes as $pattern => $route ) {
		if ( ! preg_match( $pattern, $path, $matches ) ) {
			continue;
		}

		duy_route_context(
			[
				'route'          => $path,
				$route['key']    => sanitize_title( $matches[1] ),
				'requested_path' => $path,
			]
		);

		$file = duy_template_path( $route['template'] );

		duy_route_mark_found();

		return file_exists( $file ) ? $file : $template;
	}

	return $template;
}
add_filter( 'template_include', 'duy_route_template_include', 99 );

function duy_route_is_shim_path( string $path ): bool {
	$path = trim( $path, '/' );

	if ( '' === $path ) {
		return false;
	}

	$static_routes = [
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

	if ( in_array( $path, $static_routes, true ) ) {
		return true;
	}

	foreach ( [
		'#^lo-trinh-du-hoc/[^/]+$#',
		'#^quoc-gia/[^/]+/video$#',
		'#^quoc-gia/[^/]+/[^/]+$#',
		'#^quoc-gia/[^/]+$#',
		'#^nganh-hoc/[^/]+$#',
		'#^truong/[^/]+$#',
		'#^hoc-bong/[^/]+$#',
		'#^su-kien/[^/]+$#',
		'#^hoc-sinh/[^/]+$#',
		'#^tin-tuc/[^/]+$#',
	] as $pattern ) {
		if ( preg_match( $pattern, $path ) ) {
			return true;
		}
	}

	return false;
}

function duy_route_disable_canonical_redirect( $redirect_url, $requested_url ) {
	$requested_path = trim( (string) wp_parse_url( (string) $requested_url, PHP_URL_PATH ), '/' );

	if ( duy_route_is_shim_path( $requested_path ) ) {
		return false;
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'duy_route_disable_canonical_redirect', 10, 2 );

function duy_route_pre_handle_404( bool $preempt, WP_Query $query ): bool {
	if ( is_admin() ) {
		return $preempt;
	}

	if ( duy_route_is_shim_path( duy_request_path() ) ) {
		duy_route_reset_query_flags( $query );
		status_header( 200 );

		return true;
	}

	return $preempt;
}
add_filter( 'pre_handle_404', 'duy_route_pre_handle_404', 10, 2 );

/**
 * Bài viết WordPress (post) sống dưới /tin-tuc/{slug}/ — cùng URL với route tin tức
 * của theme, nên link admin, sitemap posts-post, canonical và card đều trùng nhau.
 * /{slug}/ cũ được WordPress 301 về permalink mới (redirect_canonical).
 */
function duy_route_post_link( string $permalink, WP_Post $post ): string {
	if ( 'post' !== $post->post_type || 'publish' !== $post->post_status || '' === (string) $post->post_name ) {
		return $permalink;
	}

	return home_url( '/tin-tuc/' . $post->post_name . '/' );
}
add_filter( 'post_link', 'duy_route_post_link', 10, 2 );

function duy_route_register_post_rewrite(): void {
	add_rewrite_rule( '^tin-tuc/([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
}
add_action( 'init', 'duy_route_register_post_rewrite' );

/** /{slug}/ (permalink mặc định của WP) → 301 về /tin-tuc/{slug}/ để không có 2 URL cho một bài. */
function duy_route_redirect_post_to_news(): void {
	if ( is_admin() || ! is_singular( 'post' ) || str_starts_with( duy_request_path(), 'tin-tuc/' ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		return;
	}

	$target = get_permalink( $post );
	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'duy_route_redirect_post_to_news', 2 );

function duy_route_flush_rewrites_on_switch(): void {
	if ( function_exists( 'duy_register_cpts' ) ) {
		duy_register_cpts();
	}
	if ( function_exists( 'duy_register_taxonomies' ) ) {
		duy_register_taxonomies();
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'duy_route_flush_rewrites_on_switch' );
