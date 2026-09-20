<?php
/**
 * Services page.
 *
 * @package DUY_Study
 */

$duy_services_clean_text = static function ( string $value ): string {
	$value = (string) preg_replace( '/\s*\{\{TODO[^}]*\}\}/u', '', $value );

	return trim( (string) preg_replace( '/\s{2,}/u', ' ', $value ) );
};

$duy_services_normalize_packages = static function ( array $rows ) use ( $duy_services_clean_text ): array {
	return array_values(
		array_filter(
			array_map(
				static function ( array $package ) use ( $duy_services_clean_text ): array {
					$includes = $package['includes'] ?? [];
					if ( is_string( $includes ) ) {
						$includes = preg_split( '/\r\n|\r|\n/', $includes );
					}
					if ( ! is_array( $includes ) ) {
						$includes = [];
					}

					$includes = array_values(
						array_filter(
							array_map(
								static function ( $include ) use ( $duy_services_clean_text ): string {
									if ( is_array( $include ) ) {
										$include = $include['item'] ?? $include['title'] ?? $include['desc'] ?? '';
									}

									return $duy_services_clean_text( (string) $include );
								},
								$includes
							)
						)
					);

					return [
						'icon'     => $duy_services_clean_text( (string) ( $package['icon'] ?? 'target' ) ),
						'label'    => $duy_services_clean_text( (string) ( $package['label'] ?? $package['title'] ?? '' ) ),
						'title'    => $duy_services_clean_text( (string) ( $package['title'] ?? '' ) ),
						'desc'     => $duy_services_clean_text( (string) ( $package['desc'] ?? '' ) ),
						'for'      => $duy_services_clean_text( (string) ( $package['for'] ?? '' ) ),
						'includes' => $includes,
					];
				},
				$rows
			),
			static fn( array $package ): bool => '' !== $package['title']
		)
	);
};

$packages = $duy_services_normalize_packages( duy_rows( 'service_packages' ) ?: duy_service_packages() );
$service_intro = $duy_services_clean_text( 'Ban Du học Hội TESOL TP.HCM hỗ trợ từ định hướng, chọn trường, học bổng, hồ sơ trường, visa đến chuẩn bị trước khi lên đường. Chính sách ưu đãi thật cần xác nhận.' );
$service_offer = $duy_services_clean_text( 'Các ưu đãi tư vấn, phí dịch vụ hoặc hỗ trợ hồ sơ cần được xác nhận theo từng chương trình, quốc gia và giai đoạn nộp.' );

get_header();
duy_page_hero(
	'Dịch vụ và ưu đãi',
	'Các nhóm hỗ trợ chính trong hành trình du học cùng Ban Du học Hội TESOL TP.HCM.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Dịch vụ và ưu đãi' ],
	],
	'Services'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<?php duy_part( 'feature-block', [ 'eyebrow' => 'Hỗ trợ trọn lộ trình', 'title' => 'Mỗi dịch vụ gắn với một quyết định quan trọng', 'text' => $service_intro, 'image' => 'photo-team-office.webp', 'alt' => 'Minh họa dịch vụ tư vấn du học', 'show_note' => false ] ); ?>
	</div>
</section>

<section class="band">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Gói dịch vụ', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Chọn đúng hỗ trợ cho đúng giai đoạn', 'duy-study' ); ?></h2>
		</div>
		<div class="service-stepper glass glass-strong" data-service-stepper>
			<div class="service-steps" role="tablist" aria-label="<?php esc_attr_e( 'Chọn gói dịch vụ theo lộ trình', 'duy-study' ); ?>">
				<?php foreach ( $packages as $index => $package ) : ?>
					<button
						id="<?php echo esc_attr( 'service-tab-' . $index ); ?>"
						class="service-step <?php echo 0 === $index ? 'is-active' : ''; ?>"
						type="button"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( 'service-panel-' . $index ); ?>"
						tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
						data-service-step="<?php echo esc_attr( (string) $index ); ?>"
					>
						<span class="service-step-dot">
							<span class="service-step-no"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
							<?php echo duy_icon( $package['icon'] ?: 'target' ); ?>
						</span>
						<span class="service-step-label"><?php echo esc_html( $package['label'] ?: $package['title'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="service-panels">
				<?php foreach ( $packages as $index => $package ) : ?>
					<section
						id="<?php echo esc_attr( 'service-panel-' . $index ); ?>"
						class="service-panel <?php echo 0 === $index ? 'is-active' : ''; ?>"
						role="tabpanel"
						aria-labelledby="<?php echo esc_attr( 'service-tab-' . $index ); ?>"
						tabindex="0"
						data-service-panel="<?php echo esc_attr( (string) $index ); ?>"
						<?php echo 0 === $index ? '' : 'hidden'; ?>
					>
						<div class="service-panel-copy">
							<span class="chip pink"><?php echo esc_html( 'Gói ' . ( $index + 1 ) ); ?></span>
							<h3><?php echo esc_html( $package['title'] ); ?></h3>
							<p class="muted"><?php echo esc_html( $package['desc'] ); ?></p>
							<p><b><?php esc_html_e( 'Phù hợp với:', 'duy-study' ); ?></b> <?php echo esc_html( $package['for'] ); ?></p>
							<a class="btn btn-ghost" href="<?php echo esc_url( add_query_arg( 'goi', $package['title'], duy_route_path( 'lien-he' ) ) ); ?>"><?php esc_html_e( 'Hỏi gói này', 'duy-study' ); ?></a>
						</div>
						<div class="service-panel-list">
							<h4><?php esc_html_e( 'Bao gồm', 'duy-study' ); ?></h4>
							<ul class="check-list">
								<?php foreach ( $package['includes'] as $include ) : ?>
									<li><?php echo esc_html( $include ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap grid g2" style="align-items:stretch">
		<div class="glass glass-strong single-hero-card">
			<span class="eyebrow"><?php esc_html_e( 'Ưu đãi', 'duy-study' ); ?></span>
			<h2><?php esc_html_e( 'Chính sách theo hồ sơ và thời điểm', 'duy-study' ); ?></h2>
			<p><?php echo esc_html( $service_offer ); ?></p>
			<ul class="check-list">
				<li><?php esc_html_e( 'Ưu tiên hồ sơ có deadline gần sau khi kiểm tra khả năng nộp kịp.', 'duy-study' ); ?></li>
				<li><?php esc_html_e( 'Gợi ý học bổng và sự kiện phù hợp với hồ sơ hiện tại.', 'duy-study' ); ?></li>
				<li><?php esc_html_e( 'Nhắc mốc giấy tờ quan trọng để phụ huynh theo dõi dễ hơn.', 'duy-study' ); ?></li>
			</ul>
		</div>
		<?php duy_part( 'consultation-form', [ 'source' => 'services' ] ); ?>
	</div>
</section>
<?php
get_footer();
