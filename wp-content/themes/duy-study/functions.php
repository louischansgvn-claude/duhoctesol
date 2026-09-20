<?php
/**
 * Theme bootstrap.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DUY_THEME_VERSION', '0.12.0-carbon' );
define( 'DUY_THEME_DIR', get_template_directory() );
define( 'DUY_THEME_URI', get_template_directory_uri() );

$duy_vendor_autoload = DUY_THEME_DIR . '/vendor/autoload.php';
if ( file_exists( $duy_vendor_autoload ) ) {
	require_once $duy_vendor_autoload;
}

$duy_includes = [
	'inc/helpers.php',
	'inc/demo-data.php',
	'inc/mockup-v2-data.php',
	'inc/high-schools.php',
	'inc/hs-seeder.php',
	'inc/postsecondary.php',
	'inc/postsecondary-seeder.php',
	'inc/intl-schools.php',
	'inc/intl-schools-seeder.php',
	'inc/othc.php',
	'inc/othc-seeder.php',
	'inc/events.php',
	'inc/events-seeder.php',
	'inc/components.php',
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/cpt.php',
	'inc/taxonomies.php',
	'inc/fields.php',
	'inc/routes.php',
	'inc/ajax.php',
	'inc/seo.php',
	'inc/security.php',
];

foreach ( $duy_includes as $duy_include ) {
	require_once DUY_THEME_DIR . '/' . $duy_include;
}
