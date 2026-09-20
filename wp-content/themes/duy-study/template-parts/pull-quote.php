<?php
/**
 * Pull quote.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pull-quote"><?php echo esc_html( (string) duy_get_arg( $args, 'text', '' ) ); ?></div>
