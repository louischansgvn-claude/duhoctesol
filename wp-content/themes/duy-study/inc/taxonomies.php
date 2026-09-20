<?php
/**
 * Taxonomies and default terms.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_register_taxonomies(): void {
	register_taxonomy(
		'country',
		[ 'school', 'scholarship', 'student_story', 'guide', 'post' ],
		[
			'labels'       => [
				'name'          => 'Quốc gia',
				'singular_name' => 'Quốc gia',
				'search_items'  => 'Tìm quốc gia',
				'all_items'     => 'Tất cả quốc gia',
				'edit_item'     => 'Sửa quốc gia',
				'add_new_item'  => 'Thêm quốc gia',
			],
			'public'       => true,
			'publicly_queryable' => false,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);

	register_taxonomy(
		'study_level',
		[ 'school', 'scholarship' ],
		[
			'labels'       => [
				'name'          => 'Bậc học',
				'singular_name' => 'Bậc học',
			],
			'public'       => true,
			'publicly_queryable' => false,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);

	register_taxonomy(
		'major',
		[ 'school' ],
		[
			'labels'       => [
				'name'          => 'Ngành học',
				'singular_name' => 'Ngành học',
			],
			'public'       => true,
			'publicly_queryable' => false,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);

	register_taxonomy(
		'scholarship_value',
		[ 'scholarship' ],
		[
			'labels'       => [
				'name'          => 'Giá trị học bổng',
				'singular_name' => 'Giá trị học bổng',
			],
			'public'       => true,
			'publicly_queryable' => false,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);

	register_taxonomy(
		'event_type',
		[ 'event' ],
		[
			'labels'       => [
				'name'          => 'Loại sự kiện',
				'singular_name' => 'Loại sự kiện',
			],
			'public'       => true,
			'publicly_queryable' => false,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);
}
add_action( 'init', 'duy_register_taxonomies' );

function duy_default_terms(): array {
	return [
		'country'           => [
			'my'           => 'Mỹ',
			'uc'           => 'Úc',
			'canada'       => 'Canada',
			'new-zealand'  => 'New Zealand',
			'tho-nhi-ky'   => 'Thổ Nhĩ Kỳ',
			'singapore'    => 'Singapore',
			'han-quoc'     => 'Hàn Quốc',
			'duc'          => 'Đức',
			'ha-lan'       => 'Hà Lan',
			'anh'          => 'Anh',
			'malaysia'     => 'Malaysia',
			'thuy-sy'      => 'Thụy Sỹ',
			'philippines'  => 'Philippines',
		],
		'study_level'       => [
			'thpt'      => 'THPT',
			'trung-hoc' => 'Trung học',
			'cao-dang'  => 'Cao đẳng',
			'dai-hoc'   => 'Đại học',
			'sau-dai-hoc' => 'Sau đại học',
			'anh-ngu'   => 'Anh ngữ',
			'thac-si'   => 'Thạc sĩ',
		],
		'major'             => [
			'kinh-te'     => 'Kinh tế',
			'suc-khoe'    => 'Sức khoẻ',
			'cong-nghe'   => 'Công nghệ',
			'giao-duc'    => 'Giáo dục',
			'tieng-anh'   => 'Tiếng Anh',
			'kinh-doanh'  => 'Kinh doanh',
			'cntt'        => 'CNTT',
			'ky-thuat'    => 'Kỹ thuật',
			'y-suc-khoe'  => 'Y - Sức khỏe',
		],
		'scholarship_value' => [
			'25'        => '25% học phí',
			'50'        => '50% học phí',
			'toan-phan' => 'Toàn phần',
		],
		'event_type'        => [
			'online'    => 'Online',
			'van-phong' => 'Văn phòng',
			'hoi-thao'  => 'Hội thảo',
		],
		'category'          => [
			'visa'        => 'Visa',
			'hoc-bong'    => 'Học bổng',
			'kinh-nghiem' => 'Kinh nghiệm',
		],
	];
}

function duy_seed_default_terms(): void {
	foreach ( duy_default_terms() as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		foreach ( $terms as $slug => $name ) {
			if ( term_exists( $slug, $taxonomy ) ) {
				continue;
			}

			wp_insert_term(
				$name,
				$taxonomy,
				[
					'slug' => $slug,
				]
			);
		}
	}
}
add_action( 'init', 'duy_seed_default_terms', 20 );

/**
 * Flush rewrite rules một lần mỗi khi cấu trúc URL đổi (bump DUY_REWRITE_VER).
 * 2026-09-18: taxonomy archive (quoc-gia-filter, bac-hoc, nganh-hoc, gia-tri-hoc-bong,
 * loai-su-kien) không còn public → rule cũ trong DB phải bị xoá.
 */
const DUY_REWRITE_VER = '2026-09-19a';

function duy_flush_rewrites_if_needed(): void {
	if ( DUY_REWRITE_VER !== (string) get_option( 'duy_rewrite_ver', '' ) ) {
		flush_rewrite_rules( false );
		update_option( 'duy_rewrite_ver', DUY_REWRITE_VER );
	}
}
add_action( 'init', 'duy_flush_rewrites_if_needed', 99 );
