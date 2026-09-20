<?php
/**
 * Site footer.
 *
 * @package DUY_Study
 */
$duy_hotline        = (string) duy_option( 'hotline', '0906.510.747' );
$duy_hotline_digits = duy_phone_digits( $duy_hotline );
$duy_zalo           = (string) duy_option( 'zalo', $duy_hotline );
$duy_zalo_digits    = duy_phone_digits( $duy_zalo );
$duy_messenger      = (string) duy_option( 'messenger', duy_url( '/lien-he/' ) );
if ( '' === $duy_messenger || '#' === $duy_messenger ) {
	$duy_messenger = duy_url( '/lien-he/' );
}
?>
</main>
<footer class="site">
	<div class="wrap">
		<div class="footer-focus glass glass-strong">
			<div class="footer-brand">
				<a class="logo" href="<?php echo esc_url( duy_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Ban Du học Hội TESOL TP.HCM trang chủ', 'duy-study' ); ?>">
					<?php echo wp_kses_post( duy_logo_markup() ); ?>
				</a>
				<p class="footer-tagline">
					<strong><?php esc_html_e( 'BAN DU HỌC HỘI TESOL TP.HCM', 'duy-study' ); ?></strong>
					<?php esc_html_e( 'Hân hạnh đồng hành cùng bạn tối ưu hóa Lộ trình Du học - Định cư các nước Úc, Mỹ, Canada, Châu Âu, Singapore, Malaysia, Hàn Quốc.', 'duy-study' ); ?>
				</p>
			</div>
			<div class="footer-offices" aria-label="<?php esc_attr_e( 'Văn phòng Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?>">
				<?php foreach ( duy_offices() as $office ) : ?>
					<?php
					$duy_office_name  = (string) ( $office['name'] ?? '' );
					$duy_office_title = duy_office_display_name( $duy_office_name );
					?>
					<div class="footer-office">
						<span><?php echo duy_icon( 'pin' ); ?></span>
						<div>
							<strong><?php echo esc_html( $duy_office_title ); ?></strong>
							<p><?php echo esc_html( $office['address'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<a class="footer-hotline" href="tel:<?php echo esc_attr( $duy_hotline_digits ); ?>">
				<span><?php esc_html_e( 'Hotline', 'duy-study' ); ?></span>
				<strong><?php echo esc_html( $duy_hotline ); ?></strong>
			</a>
		</div>
		<div class="foot-bottom">
			<span>&copy; 2026 <?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM.', 'duy-study' ); ?></span>
			<span><a href="<?php echo esc_url( duy_url( '/chinh-sach-bao-mat/' ) ); ?>"><?php esc_html_e( 'Chính sách bảo mật', 'duy-study' ); ?></a></span>
		</div>
	</div>
	</footer>
	<div class="sticky-contact">
		<a class="sc-zalo" href="https://zalo.me/<?php echo esc_attr( $duy_zalo_digits ?: $duy_hotline_digits ); ?>" title="<?php esc_attr_e( 'Zalo', 'duy-study' ); ?>" aria-label="<?php esc_attr_e( 'Zalo', 'duy-study' ); ?>"><?php echo duy_icon( 'chat', 'ic-lg' ); ?></a>
		<a class="sc-mes" href="<?php echo esc_url( $duy_messenger ); ?>" title="<?php esc_attr_e( 'Messenger', 'duy-study' ); ?>" aria-label="<?php esc_attr_e( 'Messenger', 'duy-study' ); ?>"><?php echo duy_icon( 'send', 'ic-lg' ); ?></a>
		<a class="sc-call" href="tel:<?php echo esc_attr( $duy_hotline_digits ); ?>" title="<?php esc_attr_e( 'Gọi ngay', 'duy-study' ); ?>" aria-label="<?php esc_attr_e( 'Gọi ngay', 'duy-study' ); ?>"><?php echo duy_icon( 'phone', 'ic-lg' ); ?></a>
	</div>
<?php wp_footer(); ?>
</body>
</html>
