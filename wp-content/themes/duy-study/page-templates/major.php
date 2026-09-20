<?php
/**
 * Major detail template.
 *
 * @package DUY_Study
 */

$slug  = (string) duy_route_value( 'slug', '' );
$major = duy_mockup_v2_major_by_slug( $slug );

if ( ! $major ) {
	include duy_template_path( '404.php' );
	return;
}

$schools = duy_public_value( duy_mockup_v2_schools_for_major( $slug, '', 4 ) );
$major   = duy_public_value( $major );
$why     = array_values( (array) ( $major['why'] ?? [] ) );
$title   = (string) ( $major['title'] ?? '' );

get_header();
?>
<section class="page-hero wrap screen active">
	<?php echo wp_kses_post( duy_breadcrumb( [ [ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ], [ 'label' => 'Ngành học HOT', 'url' => duy_major_hub_url() ], [ 'label' => $title ] ] ) ); ?>
	<span class="eyebrow"><?php esc_html_e( 'Ngành học', 'duy-study' ); ?></span>
	<h1><?php echo duy_icon_tile( (string) ( $major['icon'] ?? 'sparkles' ) ); ?> <?php echo esc_html( 'Ngành ' . $title ); ?></h1>
	<p><?php echo esc_html( (string) ( $major['lead'] ?? '' ) ); ?></p>
	<div class="hero-cta">
		<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php echo esc_html( 'Tư vấn ngành ' . $title ); ?></a>
		<a class="btn btn-ghost" href="<?php echo esc_url( duy_route_path( 'truong' ) ); ?>"><?php esc_html_e( 'Xem trường', 'duy-study' ); ?></a>
	</div>
</section>

<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong" style="margin-bottom:2.4rem">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Tổng quan ngành', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( $title . ' có gì hấp dẫn?' ); ?></h2>
				<p><?php echo esc_html( (string) ( $major['overview'] ?? '' ) ); ?></p>
				<div class="info-list">
					<p><?php echo duy_icon( 'globe' ); ?> <span><?php echo esc_html( 'Quốc gia tiêu biểu: ' . implode( ' · ', (array) ( $major['countries'] ?? [] ) ) ); ?></span></p>
				</div>
			</div>
			<div class="media-art major-edu-art" aria-hidden="true">
				<?php echo duy_icon_tile( (string) ( $major['icon'] ?? 'sparkles' ) ); ?>
				<div class="major-art-orbit">
					<span><?php echo esc_html( $title ); ?></span>
					<span><?php esc_html_e( 'Trường', 'duy-study' ); ?></span>
					<span><?php esc_html_e( 'Nghề nghiệp', 'duy-study' ); ?></span>
				</div>
			</div>
		</div>

		<div class="section-head" style="text-align:left;max-width:none;margin:0 0 1rem">
			<span class="eyebrow"><?php esc_html_e( 'Vì sao chọn', 'duy-study' ); ?></span>
			<h2><?php echo esc_html( 'Lý do nên cân nhắc ngành ' . $title ); ?></h2>
		</div>
		<div class="bento reasons" style="margin-bottom:2.4rem">
			<?php if ( ! empty( $why[0] ) ) : ?>
				<div class="glass large reason reason-feature">
					<span class="chip pink" style="align-self:flex-start;margin-bottom:.7rem"><?php echo duy_icon( 'star', 'ic-fill' ); ?> <?php esc_html_e( 'Điểm mạnh', 'duy-study' ); ?></span>
					<?php echo duy_icon_tile( (string) ( $why[0]['icon'] ?? 'sparkles' ) ); ?>
					<h3><?php echo esc_html( (string) ( $why[0]['t'] ?? '' ) ); ?></h3>
					<p><?php echo esc_html( (string) ( $why[0]['d'] ?? '' ) ); ?></p>
				</div>
			<?php endif; ?>
			<?php foreach ( array_slice( $why, 1 ) as $item ) : ?>
				<div class="glass reason">
					<?php echo duy_icon_tile( (string) ( $item['icon'] ?? 'sparkles' ) ); ?>
					<h3><?php echo esc_html( (string) ( $item['t'] ?? '' ) ); ?></h3>
					<p><?php echo esc_html( (string) ( $item['d'] ?? '' ) ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="grid g2" style="margin-bottom:2.4rem;align-items:start">
			<div class="glass glass-strong single-hero-card">
				<span class="eyebrow"><?php esc_html_e( 'Cơ hội nghề nghiệp', 'duy-study' ); ?></span>
				<h2><?php echo esc_html( 'Sau tốt nghiệp ngành ' . $title ); ?></h2>
				<ul class="check-list">
					<?php foreach ( (array) ( $major['careers'] ?? [] ) as $career ) : ?>
						<li><?php echo esc_html( (string) $career ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php duy_part( 'consultation-form', [ 'source' => 'major-' . $slug ] ); ?>
		</div>

		<div class="results-top">
			<h2><?php echo esc_html( 'Trường tiêu biểu ngành ' . $title ); ?></h2>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( duy_route_path( 'truong' ) ); ?>"><?php esc_html_e( 'Xem tất cả', 'duy-study' ); ?> <?php echo duy_icon( 'arrow' ); ?></a>
		</div>
		<div class="grid g3">
			<?php if ( $schools ) : ?>
				<?php foreach ( $schools as $school ) : ?>
					<?php duy_part( 'card-school', [ 'item' => $school ] ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="glass single-hero-card" style="grid-column:1/-1">
					<h3><?php echo esc_html( 'Trường cho ngành ' . $title . ' đang được cập nhật' ); ?></h3>
					<p class="muted"><?php esc_html_e( 'Đăng ký tư vấn để Ban Du học Hội TESOL TP.HCM gợi ý trường phù hợp với hồ sơ và mục tiêu của bạn.', 'duy-study' ); ?></p>
					<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>" style="margin-top:.6rem"><?php esc_html_e( 'Nhận gợi ý trường', 'duy-study' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
