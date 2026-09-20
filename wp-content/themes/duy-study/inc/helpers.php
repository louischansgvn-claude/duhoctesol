<?php
/**
 * Shared helpers.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_current_lang(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		return (string) pll_current_language( 'slug' );
	}

	return isset( $_GET['lang'] ) && 'en' === sanitize_key( wp_unslash( $_GET['lang'] ) ) ? 'en' : 'vi';
}

function duy_t_dict(): array {
	return [
		'nav.guides'       => [ 'vi' => 'Lộ trình du học', 'en' => 'Study roadmap' ],
		'nav.countries'    => [ 'vi' => 'Quốc gia', 'en' => 'Countries' ],
		'nav.schools'      => [ 'vi' => 'Chọn trường', 'en' => 'Schools' ],
		'nav.scholarships' => [ 'vi' => 'Học bổng', 'en' => 'Scholarships' ],
		'nav.events'       => [ 'vi' => 'Sự kiện', 'en' => 'Events' ],
		'nav.contact'      => [ 'vi' => 'Liên hệ', 'en' => 'Contact' ],
		'cta.consult'      => [ 'vi' => 'Đăng ký tư vấn', 'en' => 'Book consultation' ],
		'hero.title'       => [ 'vi' => 'Hành trình du học bắt đầu từ một lộ trình rõ ràng', 'en' => 'Start your study abroad journey with a clear roadmap' ],
		'hero.lede'        => [ 'vi' => 'Tìm trường, học bổng và sự kiện phù hợp với mục tiêu của bạn.', 'en' => 'Find schools, scholarships, and events that fit your goals.' ],
	];
}

function duy_t( string $key, string $fallback = '' ): string {
	$lang = duy_current_lang();
	$dict = duy_t_dict();
	$text = $dict[ $key ][ $lang ] ?? $dict[ $key ]['vi'] ?? $fallback;

	if ( function_exists( 'pll__' ) ) {
		return pll__( $text );
	}

	return $text;
}

function duy_te( string $key, string $fallback = '' ): void {
	echo esc_html( duy_t( $key, $fallback ) );
}

function duy_asset_uri( string $path = '' ): string {
	$path = ltrim( $path, '/' );

	return DUY_THEME_URI . ( $path ? '/' . $path : '' );
}

/**
 * Bản thay thế của ảnh theme, tải lên Media Library với tiền tố `duy-asset-`
 * (ví dụ `duy-asset-photo-student-group.webp` thay cho `photo-student-group.webp`).
 * Có cơ chế này vì máy Windows không có FTPS: Theme File Editor chỉ ghi được
 * file văn bản, nên ảnh đã tối ưu được đưa lên qua Media Library.
 *
 * @return array<string,array{url:string,w:int,h:int}>
 */
function duy_img_overrides(): array {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$cached = get_transient( 'duy_img_overrides' );
	if ( is_array( $cached ) ) {
		$memo = $cached;

		return $memo;
	}

	$memo = [];

	if ( function_exists( 'get_posts' ) ) {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name LIKE 'duy-asset-%'", ARRAY_A );
		foreach ( (array) $rows as $row ) {
			$url = (string) wp_get_attachment_url( (int) $row['ID'] );
			if ( '' === $url ) {
				continue;
			}
			$meta                = (array) wp_get_attachment_metadata( (int) $row['ID'] );
			$key                 = substr( (string) $row['post_name'], strlen( 'duy-asset-' ) );
			$memo[ $key ]        = [
				'url' => $url,
				'w'   => (int) ( $meta['width'] ?? 0 ),
				'h'   => (int) ( $meta['height'] ?? 0 ),
			];
		}
	}

	set_transient( 'duy_img_overrides', $memo, 12 * HOUR_IN_SECONDS );

	return $memo;
}

function duy_flush_img_overrides(): void {
	delete_transient( 'duy_img_overrides' );
}
add_action( 'add_attachment', 'duy_flush_img_overrides' );
add_action( 'delete_attachment', 'duy_flush_img_overrides' );
add_action( 'edit_attachment', 'duy_flush_img_overrides' );

/**
 * Ảnh trong uploads (logo trường, ảnh sự kiện…) cũng có thể được thay bằng bản
 * tối ưu `duy-asset-<tên file>` trong Media Library — cùng cơ chế với ảnh theme.
 *
 * @return array{url:string,w:int,h:int}
 */
function duy_optimized_media( string $url ): array {
	if ( '' === $url ) {
		return [ 'url' => '', 'w' => 0, 'h' => 0 ];
	}

	$override = duy_img_override( (string) wp_parse_url( $url, PHP_URL_PATH ) );

	return [
		'url' => '' !== $override['url'] ? $override['url'] : $url,
		'w'   => $override['w'],
		'h'   => $override['h'],
	];
}

/** Thuộc tính width/height dạng chuỗi, rỗng khi không biết kích thước. */
function duy_size_attrs( int $width, int $height ): string {
	return $width > 0 && $height > 0 ? sprintf( ' width="%d" height="%d"', $width, $height ) : '';
}

/** @return array{url:string,w:int,h:int} */
function duy_img_override( string $file ): array {
	$key = pathinfo( ltrim( $file, '/' ), PATHINFO_FILENAME );

	return duy_img_overrides()[ $key ] ?? [ 'url' => '', 'w' => 0, 'h' => 0 ];
}

/**
 * [width, height] của ảnh theme (hoặc bản thay thế trong Media) để in vào thẻ
 * <img> — thiếu kích thước là nguyên nhân chính gây nhảy layout (CLS).
 * SVG bỏ qua vì getimagesize không đọc được kích thước tin cậy.
 *
 * @return array{0:int,1:int}
 */
function duy_img_dims( string $file ): array {
	static $memo = [];

	$file = ltrim( $file, '/' );
	if ( isset( $memo[ $file ] ) ) {
		return $memo[ $file ];
	}

	$override = duy_img_override( $file );
	if ( $override['w'] > 0 && $override['h'] > 0 ) {
		return $memo[ $file ] = [ $override['w'], $override['h'] ];
	}

	$path = DUY_THEME_DIR . '/assets/img/' . $file;
	if ( str_ends_with( strtolower( $file ), '.svg' ) || ! is_file( $path ) ) {
		return $memo[ $file ] = [ 0, 0 ];
	}

	$key  = 'duy_img_dims_' . md5( $file . '|' . (string) filemtime( $path ) );
	$dims = get_transient( $key );
	if ( ! is_array( $dims ) || 2 !== count( $dims ) ) {
		$info = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$dims = $info ? [ (int) $info[0], (int) $info[1] ] : [ 0, 0 ];
		set_transient( $key, $dims, WEEK_IN_SECONDS );
	}

	return $memo[ $file ] = $dims;
}

function duy_phone_digits( string $value ): string {
	return preg_replace( '/\D+/', '', $value ) ?: '';
}

function duy_field( string $name, $post_id = null, $default = null ) {
	$post_id = $post_id ?: ( function_exists( 'get_the_ID' ) ? get_the_ID() : 0 );
	$value   = null;

	if ( is_string( $post_id ) && str_starts_with( $post_id, 'term:' ) ) {
		$term_id = (int) substr( $post_id, 5 );
		if ( $term_id && function_exists( 'carbon_get_term_meta' ) ) {
			$value = carbon_get_term_meta( $term_id, $name );
		} elseif ( $term_id && function_exists( 'get_term_meta' ) ) {
			$value = get_term_meta( $term_id, $name, true );
		}
	} elseif ( $post_id && function_exists( 'carbon_get_post_meta' ) ) {
		$value = carbon_get_post_meta( (int) $post_id, $name );
	} elseif ( $post_id && function_exists( 'get_post_meta' ) ) {
		$value = get_post_meta( (int) $post_id, $name, true );
	}

	if ( '' === $value || null === $value || [] === $value ) {
		return $default;
	}

	return $value;
}

function duy_option( string $name, $default = null ) {
	$value = null;

	if ( function_exists( 'carbon_get_theme_option' ) ) {
		$value = carbon_get_theme_option( $name );
	} elseif ( function_exists( 'get_option' ) ) {
		$value = get_option( $name, null );
		if ( null === $value ) {
			$value = get_option( 'duy_' . $name, null );
		}
	}

	if ( '' === $value || null === $value || [] === $value ) {
		return $default;
	}

	return $value;
}

function duy_rows( string $name, $post_id = null ): array {
	$rows = null === $post_id ? duy_option( $name, [] ) : duy_field( $name, $post_id, [] );

	if ( ! is_array( $rows ) ) {
		return [];
	}

	return array_values(
		array_map(
			static function ( $row ) {
				if ( is_array( $row ) && ! array_key_exists( 'value', $row ) && array_key_exists( 'value_text', $row ) ) {
					$row['value'] = $row['value_text'];
				}

				return $row;
			},
			$rows
		)
	);
}

function duy_image( string $name, $post_id = null, string $fallback = '', string $alt = '', string $class = '', string $loading = 'lazy' ): string {
	$value = null === $post_id ? duy_option( $name ) : duy_field( $name, $post_id );

	if ( is_array( $value ) ) {
		$value = $value['id'] ?? $value['ID'] ?? $value['url'] ?? '';
	}

	if ( is_numeric( $value ) && function_exists( 'wp_get_attachment_image' ) ) {
		$attributes = [
			'loading' => $loading,
			'alt'     => $alt,
		];
		if ( $class ) {
			$attributes['class'] = $class;
		}

		return (string) wp_get_attachment_image( (int) $value, 'large', false, $attributes );
	}

	if ( is_string( $value ) && '' !== $value ) {
		$class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';

		return sprintf(
			'<img src="%1$s" alt="%2$s"%3$s loading="%4$s">',
			esc_url( $value ),
			esc_attr( $alt ),
			$class_attr,
			esc_attr( $loading )
		);
	}

	if ( $fallback ) {
		return duy_img( $fallback, $alt, $class, $loading );
	}

	return '';
}

function duy_public_text( string $value ): string {
	$value = (string) preg_replace( '/\s*\{\{TODO[^}]*\}\}/u', '', $value );

	return trim( (string) preg_replace( '/\s{2,}/u', ' ', $value ) );
}

function duy_public_value( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'duy_public_value', $value );
	}

	return is_string( $value ) ? duy_public_text( $value ) : $value;
}

function duy_strip_todo_markers_from_html( string $html ): string {
	return (string) preg_replace( '/\s*\{\{TODO[^}]*\}\}/u', '', $html );
}

function duy_start_public_output_safety_buffer(): void {
	if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() ) {
		return;
	}

	ob_start( 'duy_strip_todo_markers_from_html' );
}
add_action( 'template_redirect', 'duy_start_public_output_safety_buffer', 0 );

function duy_media_image_url( $value, string $fallback = '' ): string {
	if ( is_array( $value ) ) {
		$value = $value['id'] ?? $value['ID'] ?? $value['url'] ?? '';
	}

	if ( is_numeric( $value ) && function_exists( 'wp_get_attachment_image_url' ) ) {
		$url = wp_get_attachment_image_url( (int) $value, 'large' );

		return $url ? (string) $url : '';
	}

	if ( is_string( $value ) && '' !== $value ) {
		if ( preg_match( '#^https?://#i', $value ) ) {
			return $value;
		}

		return duy_img_uri( $value );
	}

	return $fallback ? duy_img_uri( $fallback ) : '';
}

function duy_media_image_markup( $value, string $alt, string $class = '', string $fallback = '', string $loading = 'lazy' ): string {
	if ( is_array( $value ) ) {
		$value = $value['id'] ?? $value['ID'] ?? $value['url'] ?? '';
	}

	if ( is_numeric( $value ) && function_exists( 'wp_get_attachment_image' ) ) {
		$attributes = [
			'alt'     => $alt,
			'loading' => $loading,
		];
		if ( $class ) {
			$attributes['class'] = $class;
		}

		return (string) wp_get_attachment_image( (int) $value, 'large', false, $attributes );
	}

	$src = duy_media_image_url( $value, $fallback );
	if ( ! $src ) {
		return '';
	}

	$class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';

	return sprintf(
		'<img src="%1$s" alt="%2$s"%3$s loading="%4$s">',
		esc_url( $src ),
		esc_attr( $alt ),
		$class_attr,
		esc_attr( $loading )
	);
}

function duy_youtube_id( string $url ): string {
	$url = trim( $url );
	if ( '' === $url ) {
		return '';
	}

	if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $url ) ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) ) {
		return '';
	}

	$host = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path = trim( (string) ( $parts['path'] ?? '' ), '/' );

	if ( str_contains( $host, 'youtu.be' ) && preg_match( '#^([A-Za-z0-9_-]{11})#', $path, $matches ) ) {
		return $matches[1];
	}

	if ( str_contains( $host, 'youtube.com' ) ) {
		if ( ! empty( $parts['query'] ) ) {
			parse_str( (string) $parts['query'], $query );
			if ( ! empty( $query['v'] ) && preg_match( '/^[A-Za-z0-9_-]{11}$/', (string) $query['v'] ) ) {
				return (string) $query['v'];
			}
		}

		if ( preg_match( '#^(embed|shorts)/([A-Za-z0-9_-]{11})#', $path, $matches ) ) {
			return $matches[2];
		}
	}

	return '';
}

function duy_url( string $path = '/' ): string {
	return home_url( $path );
}

function duy_icon( string $name, string $class = '' ): string {
	$classes = array_filter(
		array_map(
			'sanitize_html_class',
			preg_split( '/\s+/', trim( 'ic ' . $class ) )
		)
	);

	$href = duy_asset_uri( 'assets/icons/sprite.svg#i-' . sanitize_key( $name ) );

	return sprintf(
		'<svg class="%1$s" aria-hidden="true" focusable="false"><use href="%2$s"></use></svg>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $href )
	);
}

/**
 * Pick a stable demo photo from a themed pool, varied per item so repeated
 * cards do not all show the same placeholder image. Replaced by real featured
 * images once content is added.
 */
function duy_demo_photo( string $seed, string $type = 'campus' ): string {
	$pools = [
		'campus'  => [ 'photo-campus-library.webp', 'photo-campus-steps.webp', 'photo-classroom.webp', 'photo-library-study.webp', 'photo-student-group.webp' ],
		'event'   => [ 'photo-event-workshop.webp', 'photo-team-office.webp', 'photo-student-group.webp', 'photo-classroom.webp' ],
		'news'    => [ 'photo-article-laptop.webp', 'photo-library-study.webp', 'photo-campus-library.webp', 'photo-classroom.webp' ],
		'student' => [ 'photo-student-group.webp', 'photo-campus-steps.webp', 'photo-campus-library.webp', 'photo-library-study.webp' ],
	];
	$pool = $pools[ $type ] ?? $pools['campus'];
	$idx  = '' === $seed ? 0 : ( abs( crc32( $seed ) ) % count( $pool ) );
	return $pool[ $idx ];
}

function duy_logo_markup(): string {
	$custom_logo_id = function_exists( 'get_theme_mod' ) ? (int) get_theme_mod( 'custom_logo' ) : 0;
	if ( $custom_logo_id && function_exists( 'wp_get_attachment_image' ) ) {
		$logo = wp_get_attachment_image(
			$custom_logo_id,
			'full',
			false,
			[
				'class'    => 'logo-img',
				'alt'      => 'Ban Du học Hội TESOL TP.HCM',
				'loading'  => 'eager',
				'decoding' => 'async',
			]
		);
		if ( $logo ) {
			return (string) $logo;
		}
	}

	// logo.webp nhẹ hơn logo.png khoảng 60%; giữ .png làm phương án dự phòng.
	foreach ( [ 'logo.svg', 'logo.webp', 'logo.png' ] as $file ) {
		if ( ! file_exists( DUY_THEME_DIR . '/assets/img/' . $file ) && '' === duy_img_override( $file )['url'] ) {
			continue;
		}

		[ $width, $height ] = duy_img_dims( $file );

		return sprintf(
			'<img class="logo-img" src="%1$s" alt="%2$s"%3$s loading="eager" decoding="async" fetchpriority="high">',
			esc_url( duy_img_uri( $file ) ),
			esc_attr__( 'Ban Du học Hội TESOL TP.HCM', 'duy-study' ),
			$width > 0 && $height > 0 ? sprintf( ' width="%d" height="%d"', $width, $height ) : ''
		);
	}

	return '<span class="lg"><b>DUY</b> <i>Study</i></span>';
}

/**
 * Văn phòng: `name` + `address` (chuỗi hiển thị) và các trường tách riêng cho
 * schema LocalBusiness (street/ward/city/postal_code/lat/lng/maps_url/hours/phone).
 * Trường nào chưa có dữ liệu thì để '' — schema sẽ bỏ thuộc tính, không bịa.
 */
function duy_offices(): array {
	$shape = static function ( array $office ): array {
		$building = trim( (string) ( $office['building'] ?? '' ) );
		$street   = trim( (string) ( $office['address'] ?? '' ) );
		$ward     = trim( (string) ( $office['ward'] ?? '' ) );
		$city     = trim( (string) ( $office['city'] ?? '' ) );
		$parts    = array_values( array_filter( [ $building, $street, $ward, $city ] ) );

		return [
			'name'        => trim( (string) ( $office['name'] ?? '' ) ),
			// Chuỗi hiển thị (footer/liên hệ) giữ nguyên như trước; `display` chỉ dùng cho bộ mặc định.
			'address'     => (string) ( $office['display'] ?? ( implode( ' – ', array_slice( $parts, 0, 1 ) ) . ( count( $parts ) > 1 ? ' – ' . implode( ', ', array_slice( $parts, 1 ) ) : '' ) ) ),
			'building'    => $building,
			'street'      => $street,
			'ward'        => $ward,
			'city'        => $city,
			'postal_code' => trim( (string) ( $office['postal_code'] ?? '' ) ),
			'lat'         => trim( (string) ( $office['lat'] ?? '' ) ),
			'lng'         => trim( (string) ( $office['lng'] ?? '' ) ),
			'maps_url'    => trim( (string) ( $office['maps_url'] ?? '' ) ),
			'hours'       => trim( (string) ( $office['hours'] ?? '' ) ),
			'phone'       => trim( (string) ( $office['phone'] ?? '' ) ),
		];
	};

	$offices = duy_rows( 'offices' );
	if ( $offices ) {
		return array_map( $shape, $offices );
	}

	return array_map(
		$shape,
		[
			[
				'name'     => 'TP.HCM',
				'display'  => 'Toà nhà BV Bank – 412 Nguyễn Thị Minh Khai, P. Bàn Cờ',
				'building' => 'Toà nhà BV Bank',
				'address'  => '412 Nguyễn Thị Minh Khai',
				'ward'     => 'P. Bàn Cờ',
				'city'     => 'Thành phố Hồ Chí Minh',
			],
			[
				'name'     => 'Đà Nẵng',
				'display'  => 'Khách sạn Hilton – 50 Bạch Đằng, P. Hải Châu',
				'building' => 'Khách sạn Hilton',
				'address'  => '50 Bạch Đằng',
				'ward'     => 'P. Hải Châu',
				'city'     => 'Đà Nẵng',
			],
			[
				'name'     => 'Buôn Ma Thuột',
				'display'  => '198 Nguyễn Thị Minh Khai, P. Buôn Ma Thuột',
				'address'  => '198 Nguyễn Thị Minh Khai',
				'ward'     => 'P. Buôn Ma Thuột',
				'city'     => 'Đắk Lắk',
			],
		]
	);
}

function duy_office_display_name( string $name ): string {
	$name = trim( $name );

	return preg_match( '/^\s*VP\b/u', $name ) ? $name : 'VP ' . $name;
}

function duy_office_slug( string $name, int $index = 0 ): string {
	$name = trim( (string) preg_replace( '/^\s*VP\s+/u', '', $name ) );
	$slug = sanitize_title( $name );

	return $slug ?: 'office-' . ( $index + 1 );
}

function duy_office_options(): array {
	$options = [];

	foreach ( duy_offices() as $index => $office ) {
		$name = (string) ( $office['name'] ?? '' );
		$slug = duy_office_slug( $name, (int) $index );

		if ( isset( $options[ $slug ] ) ) {
			$slug .= '-' . ( $index + 1 );
		}

		$options[ $slug ] = duy_office_display_name( $name );
	}

	return $options;
}

/**
 * <img> for a YouTube thumbnail using the high-res maxresdefault (1280×720),
 * with a runtime fallback to hqdefault (480×360) for videos that lack an HD
 * thumbnail. Keeps the facade crisp on large cards.
 */
function duy_youtube_thumb_markup( string $id, string $alt ): string {
	$eid = rawurlencode( $id );
	$max = 'https://i.ytimg.com/vi/' . $eid . '/maxresdefault.jpg';
	$hq  = 'https://i.ytimg.com/vi/' . $eid . '/hqdefault.jpg';

	return sprintf(
		'<img src="%1$s" onerror="this.onerror=null;this.src=\'%2$s\'" alt="%3$s" loading="lazy" decoding="async">',
		esc_url( $max ),
		esc_url( $hq ),
		esc_attr( $alt )
	);
}
