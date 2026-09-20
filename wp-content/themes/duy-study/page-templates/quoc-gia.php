<?php
/**
 * Country hub.
 *
 * @package DUY_Study
 */

$countries = duy_public_value( duy_demo_countries() );

get_header();
duy_page_hero(
	'Quốc gia du học',
	'So sánh 9 điểm đến trọng tâm của Ban Du học Hội TESOL TP.HCM theo hệ thống giáo dục, chi phí, visa, trường tiêu biểu và học bổng nổi bật.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Quốc gia' ],
	],
	'Countries'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap" id="quocgiaHub">
		<?php $qg_counter = 0; ?>
		<?php foreach ( $countries as $slug => $country ) : ?>
			<?php
			++$qg_counter;
			$slug    = (string) $slug;
			$schools = duy_public_value( duy_country_school_cards( $slug, (array) ( $country['schools'] ?? [] ) ) );
			$promo   = $schools[0] ?? null;
			$mini    = array_slice( $schools, 1, 2 );
			?>
			<article class="qg-section glass" id="<?php echo esc_attr( 'qg-' . $country['id'] ); ?>">
				<div class="qg-country">
					<div class="qg-flag <?php echo esc_attr( $country['cls'] ?? '' ); ?>">
						<?php echo wp_kses_post( duy_img( duy_country_image( (string) $country['name'] ), 'Du học ' . $country['name'] ) ); ?>
						<span class="cc-code"><?php echo esc_html( $country['code'] ); ?></span>
						<span class="qg-num"><?php echo esc_html( (string) $qg_counter ); ?></span>
					</div>
					<h2><?php echo esc_html( $country['title'] ); ?></h2>
					<p class="qg-lead"><?php echo esc_html( $country['lead'] ); ?></p>
					<div class="qg-chips">
						<?php foreach ( array_slice( $country['stats'] ?? [], 0, 3 ) as $stat ) : ?>
							<span class="chip" title="<?php echo esc_attr( $stat[1] ?? '' ); ?>"><?php echo esc_html( $stat[0] ?? '' ); ?></span>
						<?php endforeach; ?>
					</div>
					<a class="btn btn-cyan" style="margin-top:auto" href="<?php echo esc_url( duy_country_url_from_slug( $slug ) ); ?>">
						<?php echo esc_html( 'Khám phá ' . $country['title'] ); ?> <?php echo duy_icon( 'arrow' ); ?>
					</a>
				</div>
				<div class="qg-detail">
					<div>
						<span class="eyebrow"><?php esc_html_e( 'Tổng quan', 'duy-study' ); ?></span>
						<p class="qg-desc"><?php echo esc_html( $country['overview'] ?? $country['lead'] ); ?></p>
					</div>
					<?php if ( $promo ) : ?>
						<a class="promo-banner" href="<?php echo esc_url( duy_school_url( (string) $promo['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Trường nổi bật ' . $promo['n'] ); ?>">
							<span class="pb-img">
								<?php echo wp_kses_post( duy_img( duy_country_image( (string) $country['name'] ), 'Trường đối tác nổi bật ' . $promo['n'] ) ); ?>
								<?php /* — slot ảnh thương hiệu, không hiển thị ra UI. */ ?>
							</span>
							<span class="pb-body">
								<span class="pb-tag"><?php echo duy_icon( 'star', 'ic-fill' ); ?> <?php esc_html_e( 'Đối tác nổi bật', 'duy-study' ); ?></span>
								<h4><?php echo esc_html( $promo['n'] ); ?></h4>
								<p><?php echo esc_html( $promo['r'] . ' · ' . $promo['city'] . ' · Học phí ' . $promo['fee'] ); ?></p>
								<span class="btn btn-primary btn-sm" style="align-self:flex-start;margin-top:.7rem"><?php esc_html_e( 'Xem trường', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></span>
							</span>
						</a>
					<?php else : ?>
						<div class="promo-banner">
							<span class="pb-img">
								<?php echo wp_kses_post( duy_img( duy_country_image( (string) $country['name'] ), 'Minh họa du học ' . $country['name'] ) ); ?>
								<?php /* — slot ảnh thương hiệu, không hiển thị ra UI. */ ?>
							</span>
							<span class="pb-body">
								<span class="pb-tag"><?php echo duy_icon( 'sparkles' ); ?> <?php esc_html_e( 'Đang cập nhật', 'duy-study' ); ?></span>
								<h4><?php esc_html_e( 'Danh sách trường đang cập nhật', 'duy-study' ); ?></h4>
								<p><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM sẽ bổ sung trường tiêu biểu khi dữ liệu đối tác được xác minh.', 'duy-study' ); ?></p>
							</span>
						</div>
					<?php endif; ?>
					<?php if ( $mini ) : ?>
						<div>
							<h4 style="font-size:1rem;margin-bottom:.6rem"><?php esc_html_e( 'Trường tiêu biểu khác', 'duy-study' ); ?></h4>
							<div class="qg-mini-grid">
								<?php foreach ( $mini as $school ) : ?>
									<a class="school-mini" href="<?php echo esc_url( duy_school_url( (string) $school['id'] ) ); ?>" aria-label="<?php echo esc_attr( 'Xem trường ' . $school['n'] ); ?>">
										<?php echo duy_icon_tile( 'cap' ); ?>
										<span><b><?php echo esc_html( $school['n'] ); ?></b><span><?php echo esc_html( $school['city'] . ' · ' . $school['fee'] ); ?></span></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php else : ?>
						<a class="btn btn-ghost btn-sm" style="align-self:flex-start" href="<?php echo esc_url( duy_route_path( 'truong' ) . '?country=' . rawurlencode( $slug ) ); ?>">
							<?php echo esc_html( 'Xem tất cả trường tại ' . $country['name'] ); ?> <?php echo duy_icon( 'arrow' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
<?php
get_footer();
