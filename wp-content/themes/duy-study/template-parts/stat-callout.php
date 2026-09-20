<?php
/**
 * Stat callout.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="stat-callout">
	<b><?php echo esc_html( (string) duy_get_arg( $args, 'value', '' ) ); ?></b>
	<span class="muted"><?php echo esc_html( (string) duy_get_arg( $args, 'label', '' ) ); ?></span>
</div>
