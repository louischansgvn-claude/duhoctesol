<?php
/**
 * Theme setup.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo' );

	register_nav_menus(
		[
			'primary' => __( 'Primary navigation', 'duy-study' ),
			'footer'  => __( 'Footer navigation', 'duy-study' ),
		]
	);

	add_image_size( 'duy-card', 720, 540, true );
	add_image_size( 'duy-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'duy_theme_setup' );

function duy_disable_wp_emoji_assets(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'duy_disable_wp_emoji_assets' );

function duy_register_pll_strings(): void {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	foreach ( duy_t_dict() as $key => $translations ) {
		pll_register_string( $key, $translations['vi'], 'Ban Du học Hội TESOL TP.HCM' );
	}
}
add_action( 'init', 'duy_register_pll_strings' );

/**
 * Dọn <head>: theme tự in canonical (inc/seo.php) nên bỏ rel_canonical của lõi
 * (đang in 2 canonical trên trang trường); bỏ shortlink/RSD/REST/oEmbed discovery.
 */
function duy_clean_wp_head(): void {
	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	// Theme không dùng block editor ngoài front-end → không cần global styles / block library CSS (~24 KB inline).
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
}
add_action( 'init', 'duy_clean_wp_head' );

function duy_dequeue_block_styles(): void {
	if ( is_admin() ) {
		return;
	}

	foreach ( [ 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'core-block-supports' ] as $handle ) {
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'duy_dequeue_block_styles', 100 );

/* -------------------------------------------------------------------------
 * Bảo trì: sinh lại kích thước ảnh cho attachment còn thiếu
 *
 * Các importer nạp logo trường vào thư mục riêng trong uploads nên WordPress
 * chưa tạo bản thu nhỏ — card phải tải ảnh gốc (có file tới 1,7 MB). Hàm này
 * tạo bản medium/large cho những ảnh còn thiếu, chạy theo lô nhỏ để không quá
 * tải hosting. Chỉ admin đã đăng nhập + đúng token mới gọi được.
 *
 *   /?duy_regen=1&token=<DUY_REGEN_TOKEN>&limit=10[&dry=1]
 * ---------------------------------------------------------------------- */

const DUY_REGEN_TOKEN = 'ds-regen-2026-09-20-4c81f6';

/**
 * Ảnh cần sinh lại kích thước khi chưa có metadata, hoặc đủ lớn để có bản
 * thu nhỏ mà lại chưa có. Ảnh nhỏ hơn ngưỡng `medium` thì WordPress không tạo
 * bản nào cả — đó là đúng, không phải thiếu.
 */
function duy_regen_needs_sizes( int $attachment_id ): bool {
	$meta = wp_get_attachment_metadata( $attachment_id );

	if ( ! is_array( $meta ) || empty( $meta['width'] ) ) {
		return true;
	}

	$threshold = max( 1, (int) get_option( 'medium_size_w', 300 ) );

	return (int) $meta['width'] > $threshold && empty( $meta['sizes'] );
}

function duy_regen_thumbnails(): void {
	if ( empty( $_GET['duy_regen'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Không có quyền.', 'duy-regen', [ 'response' => 403 ] );
	}

	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( DUY_REGEN_TOKEN, $token ) ) {
		wp_die( 'Token sai.', 'duy-regen', [ 'response' => 403 ] );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$limit = min( 25, max( 1, (int) ( $_GET['limit'] ?? 10 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$dry   = ! empty( $_GET['dry'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ids   = get_posts(
		[
			'post_type'        => 'attachment',
			// Chỉ ảnh raster: SVG không có bản thu nhỏ nên sẽ bị quét lại mãi.
			'post_mime_type'   => [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ],
			'post_status'      => 'inherit',
			'posts_per_page'   => 400,
			'fields'           => 'ids',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'offset'           => max( 0, (int) ( $_GET['offset'] ?? 0 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);

	$done      = [];
	$scanned   = 0;
	$remaining = 0;
	$started   = microtime( true );

	foreach ( $ids as $id ) {
		++$scanned;

		$file = get_attached_file( (int) $id );
		if ( ! $file || ! file_exists( $file ) ) {
			continue;
		}

		// Lọc theo đuôi file: một số ảnh import có post_mime_type không khớp đuôi thật,
		// và SVG thì không bao giờ có bản thu nhỏ.
		if ( ! in_array( strtolower( (string) pathinfo( $file, PATHINFO_EXTENSION ) ), [ 'jpg', 'jpeg', 'png', 'webp', 'gif' ], true ) ) {
			continue;
		}

		if ( ! duy_regen_needs_sizes( (int) $id ) ) {
			continue;
		}

		if ( count( $done ) >= $limit || microtime( true ) - $started > 20 ) {
			++$remaining;
			continue;
		}

		if ( $dry ) {
			$done[] = [ 'id' => (int) $id, 'file' => basename( $file ), 'kb' => (int) round( filesize( $file ) / 1024 ) ];
			continue;
		}

		$meta = wp_generate_attachment_metadata( (int) $id, $file );
		if ( is_array( $meta ) ) {
			wp_update_attachment_metadata( (int) $id, $meta );
			$done[] = [ 'id' => (int) $id, 'file' => basename( $file ), 'sizes' => count( $meta['sizes'] ?? [] ) ];
		}
	}

	if ( ! $dry && $done ) {
		duy_flush_school_caches();
	}

	wp_send_json(
		[
			'scanned'   => $scanned,
			'processed' => count( $done ),
			'remaining' => $remaining,
			'seconds'   => round( microtime( true ) - $started, 1 ),
			'items'     => $done,
		]
	);
}
add_action( 'template_redirect', 'duy_regen_thumbnails', 1 );
