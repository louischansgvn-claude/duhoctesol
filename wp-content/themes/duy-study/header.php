<?php
/**
 * Site header.
 *
 * @package DUY_Study
 */
$duy_about_links = [
	[ 'label' => __( 'Về chúng tôi', 'duy-study' ), 'url' => duy_route_path( 've-chung-toi' ) ],
	[ 'label' => __( 'Học sinh tiêu biểu', 'duy-study' ), 'url' => duy_route_path( 'hoc-sinh' ) ],
	[ 'label' => __( 'Dịch vụ & Ưu đãi', 'duy-study' ), 'url' => duy_route_path( 'dich-vu' ) ],
	[ 'label' => __( 'Liên hệ', 'duy-study' ), 'url' => duy_route_path( 'lien-he' ) ],
];

$duy_country_links = [
	[ 'label' => __( 'Du học Mỹ', 'duy-study' ), 'name' => __( 'Mỹ', 'duy-study' ), 'slug' => 'my' ],
	[ 'label' => __( 'Du học Úc', 'duy-study' ), 'name' => __( 'Úc', 'duy-study' ), 'slug' => 'uc' ],
	[ 'label' => __( 'Du học Canada', 'duy-study' ), 'name' => __( 'Canada', 'duy-study' ), 'slug' => 'canada' ],
	[ 'label' => __( 'Du học New Zealand', 'duy-study' ), 'name' => __( 'New Zealand', 'duy-study' ), 'slug' => 'new-zealand' ],
	[ 'label' => __( 'Du học Thổ Nhĩ Kỳ', 'duy-study' ), 'name' => __( 'Thổ Nhĩ Kỳ', 'duy-study' ), 'slug' => 'tho-nhi-ky' ],
];

$duy_level_links = [
	[ 'label' => __( 'THPT', 'duy-study' ), 'slug' => 'thpt' ],
	[ 'label' => __( 'Cao đẳng', 'duy-study' ), 'slug' => 'cao-dang' ],
	[ 'label' => __( 'Đại học', 'duy-study' ), 'slug' => 'dai-hoc' ],
	[ 'label' => __( 'Sau đại học', 'duy-study' ), 'slug' => 'sau-dai-hoc' ],
];

$duy_other_country_links = [
	[ 'label' => __( 'Anh', 'duy-study' ), 'slug' => 'anh' ],
	[ 'label' => __( 'Hà Lan', 'duy-study' ), 'slug' => 'ha-lan' ],
	[ 'label' => __( 'Singapore', 'duy-study' ), 'slug' => 'singapore' ],
	[ 'label' => __( 'Malaysia', 'duy-study' ), 'slug' => 'malaysia' ],
	[ 'label' => __( 'Thụy Sỹ', 'duy-study' ), 'slug' => 'thuy-sy' ],
	[ 'label' => __( 'Đức', 'duy-study' ), 'slug' => 'duc' ],
	[ 'label' => __( 'Hàn Quốc', 'duy-study' ), 'slug' => 'han-quoc' ],
	[ 'label' => __( 'Philippines', 'duy-study' ), 'slug' => 'philippines' ],
];

$duy_news_links = [
	[ 'label' => __( 'Tin tức', 'duy-study' ), 'url' => duy_route_path( 'tin-tuc' ) ],
	[ 'label' => __( 'Học bổng Hot', 'duy-study' ), 'url' => duy_route_path( 'hoc-bong' ) ],
	[ 'label' => __( 'Sự kiện', 'duy-study' ), 'url' => duy_route_path( 'su-kien' ) ],
];

$duy_major_links = [
	[ 'label' => __( 'Kinh tế', 'duy-study' ), 'slug' => 'kinh-te' ],
	[ 'label' => __( 'Sức khoẻ', 'duy-study' ), 'slug' => 'suc-khoe' ],
	[ 'label' => __( 'Công nghệ', 'duy-study' ), 'slug' => 'cong-nghe' ],
	[ 'label' => __( 'Giáo dục', 'duy-study' ), 'slug' => 'giao-duc' ],
	[ 'label' => __( 'Tiếng Anh', 'duy-study' ), 'slug' => 'tieng-anh' ],
];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="appbar">
	<div class="wrap">
		<div class="appbar-inner glass glass-strong">
			<a class="logo" href="<?php echo esc_url( duy_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Ban Du học Hội TESOL TP.HCM trang chủ', 'duy-study' ); ?>">
				<?php echo wp_kses_post( duy_logo_markup() ); ?>
			</a>

			<nav aria-label="<?php esc_attr_e( 'Primary navigation', 'duy-study' ); ?>">
				<ul class="nav-links" id="navLinks">
					<li class="has-drop">
						<a href="<?php echo esc_url( duy_route_path( 've-chung-toi' ) ); ?>" aria-haspopup="true"><?php esc_html_e( 'Về chúng tôi', 'duy-study' ); ?> <span class="caret"></span></a>
						<div class="drop glass glass-strong">
							<?php foreach ( $duy_about_links as $duy_link ) : ?>
								<a href="<?php echo esc_url( $duy_link['url'] ); ?>"><?php echo esc_html( $duy_link['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					</li>
					<?php foreach ( $duy_country_links as $duy_country ) : ?>
						<li class="has-drop">
							<a href="<?php echo esc_url( duy_route_path( 'quoc-gia/' . $duy_country['slug'] ) ); ?>" aria-haspopup="true"><?php echo esc_html( $duy_country['label'] ); ?> <span class="caret"></span></a>
							<div class="drop glass glass-strong">
								<span class="drop-label"><?php esc_html_e( 'Bậc học', 'duy-study' ); ?></span>
								<?php foreach ( $duy_level_links as $duy_level ) : ?>
									<a href="<?php echo esc_url( duy_route_path( 'quoc-gia/' . $duy_country['slug'] . '/' . $duy_level['slug'] ) ); ?>"><?php echo esc_html( $duy_level['label'] ); ?></a>
								<?php endforeach; ?>
								<span class="drop-sep" aria-hidden="true"></span>
								<a href="<?php echo esc_url( add_query_arg( 'country', $duy_country['slug'], duy_route_path( 'hoc-bong' ) ) ); ?>"><?php esc_html_e( 'Học bổng', 'duy-study' ); ?></a>
								<a href="<?php echo esc_url( duy_country_video_url( $duy_country['slug'] ) ); ?>"><?php echo esc_html( sprintf( __( 'Video du học %s', 'duy-study' ), $duy_country['name'] ) ); ?></a>
							</div>
						</li>
					<?php endforeach; ?>
					<li class="has-drop drop-right">
						<a href="<?php echo esc_url( duy_route_path( 'quoc-gia' ) ); ?>" aria-haspopup="true"><?php esc_html_e( 'Du học các nước', 'duy-study' ); ?> <span class="caret"></span></a>
						<div class="drop glass glass-strong">
							<?php foreach ( $duy_other_country_links as $duy_country ) : ?>
								<a href="<?php echo esc_url( duy_route_path( 'quoc-gia/' . $duy_country['slug'] ) ); ?>"><?php echo esc_html( $duy_country['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					</li>
					<li class="has-drop drop-right">
						<a href="<?php echo esc_url( duy_route_path( 'tin-tuc' ) ); ?>" aria-haspopup="true"><?php esc_html_e( 'Tin tức', 'duy-study' ); ?> <span class="caret"></span></a>
						<div class="drop glass glass-strong">
							<?php foreach ( $duy_news_links as $duy_link ) : ?>
								<a href="<?php echo esc_url( $duy_link['url'] ); ?>"><?php echo esc_html( $duy_link['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					</li>
					<li class="has-drop drop-right">
						<a href="<?php echo esc_url( duy_major_hub_url() ); ?>" aria-haspopup="true"><?php esc_html_e( 'Ngành học HOT', 'duy-study' ); ?> <span class="caret"></span></a>
						<div class="drop glass glass-strong">
							<?php foreach ( $duy_major_links as $duy_major ) : ?>
								<a href="<?php echo esc_url( duy_major_url( $duy_major['slug'] ) ); ?>"><?php echo esc_html( $duy_major['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					</li>
				</ul>
			</nav>

			<div class="appbar-cta">
				<a class="btn btn-primary btn-sm" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
				<button class="menu-toggle" id="menuToggle" aria-label="<?php esc_attr_e( 'Menu', 'duy-study' ); ?>" aria-controls="navLinks" aria-expanded="false"><span></span><span></span><span></span></button>
			</div>
		</div>
	</div>
</header>
<main id="main" class="duy-main">
