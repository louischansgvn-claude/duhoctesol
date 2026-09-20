<?php
/**
 * Asset loading.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset version = file mtime, so any edit busts browser/CDN cache automatically.
 * Falls back to the theme version if the file is missing.
 */
function duy_asset_ver( string $relative ): string {
	$path  = DUY_THEME_DIR . '/' . ltrim( $relative, '/' );
	$mtime = file_exists( $path ) ? filemtime( $path ) : 0;

	return $mtime ? (string) $mtime : DUY_THEME_VERSION;
}

function duy_enqueue_assets(): void {
	wp_enqueue_style( 'duy-main', DUY_THEME_URI . '/assets/css/main.css', [], duy_asset_ver( 'assets/css/main.css' ) );
	wp_enqueue_script( 'duy-main', DUY_THEME_URI . '/assets/js/main.js', [], duy_asset_ver( 'assets/js/main.js' ), true );

	wp_localize_script(
		'duy-main',
		'DUY_AJAX',
		[
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'duy_ajax' ),
		]
	);
}
add_action( 'wp_enqueue_scripts', 'duy_enqueue_assets' );

/**
 * LiteSpeed Cache (khi cài): nonce `duy_ajax` được thay bằng ESI block nên trang
 * cache lâu hơn 12 giờ vẫn gửi form tư vấn được. Không có LiteSpeed thì filter không chạy.
 */
function duy_litespeed_nonces( array $nonces ): array {
	$nonces[] = 'duy_ajax';

	return array_values( array_unique( $nonces ) );
}
add_filter( 'litespeed_nonces', 'duy_litespeed_nonces' );

