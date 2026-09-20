<?php
/**
 * FAQ accordion.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faqs = duy_get_arg( $args, 'items', duy_demo_faqs() );
?>
<div class="faq">
	<?php foreach ( $faqs as $index => $faq ) : ?>
		<details class="glass" <?php echo 0 === $index ? 'open' : ''; ?>>
			<summary><?php echo esc_html( $faq['q'] ?? '' ); ?> <?php echo duy_icon( 'arrow' ); ?></summary>
			<div class="ans"><?php echo esc_html( $faq['a'] ?? '' ); ?></div>
		</details>
	<?php endforeach; ?>
</div>
