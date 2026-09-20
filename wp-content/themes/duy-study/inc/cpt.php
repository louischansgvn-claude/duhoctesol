<?php
/**
 * Custom post types.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_register_cpts(): void {
	$public_types = [
		'school'        => [
			'singular'  => 'Trường',
			'plural'    => 'Trường',
			'slug'      => 'truong',
			'menu_icon' => 'dashicons-welcome-learn-more',
		],
		'scholarship'   => [
			'singular'  => 'Học bổng',
			'plural'    => 'Học bổng',
			'slug'      => 'hoc-bong',
			'menu_icon' => 'dashicons-awards',
		],
		'event'         => [
			'singular'  => 'Sự kiện',
			'plural'    => 'Sự kiện',
			'slug'      => 'su-kien',
			'menu_icon' => 'dashicons-calendar-alt',
		],
		'student_story' => [
			'singular'  => 'Câu chuyện học sinh',
			'plural'    => 'Câu chuyện học sinh',
			'slug'      => 'hoc-sinh',
			'menu_icon' => 'dashicons-format-video',
		],
		'guide'         => [
			'singular'  => 'Lộ trình',
			'plural'    => 'Lộ trình',
			'slug'      => 'lo-trinh-du-hoc',
			'menu_icon' => 'dashicons-location-alt',
		],
	];

	foreach ( $public_types as $type => $args ) {
		register_post_type(
			$type,
			[
				'labels'             => [
					'name'               => $args['plural'],
					'singular_name'      => $args['singular'],
					'add_new_item'       => sprintf( 'Thêm %s', $args['singular'] ),
					'edit_item'          => sprintf( 'Sửa %s', $args['singular'] ),
					'new_item'           => sprintf( '%s mới', $args['singular'] ),
					'view_item'          => sprintf( 'Xem %s', $args['singular'] ),
					'search_items'       => sprintf( 'Tìm %s', $args['plural'] ),
					'not_found'          => sprintf( 'Chưa có %s.', $args['plural'] ),
					'not_found_in_trash' => sprintf( 'Không có %s trong thùng rác.', $args['plural'] ),
				],
				'public'             => true,
				'has_archive'        => true,
				'menu_icon'          => $args['menu_icon'],
				'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
				'show_in_rest'       => true,
				'rewrite'            => [ 'slug' => $args['slug'] ],
				'publicly_queryable' => true,
			]
		);
	}

	register_post_type(
		'lead',
		[
			'labels'              => [
				'name'          => 'Lead tư vấn',
				'singular_name' => 'Lead tư vấn',
				'add_new_item'  => 'Thêm lead tư vấn',
				'edit_item'     => 'Sửa lead tư vấn',
				'search_items'  => 'Tìm lead tư vấn',
				'not_found'     => 'Chưa có lead tư vấn.',
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-email-alt2',
			'supports'            => [ 'title', 'revisions' ],
			'show_in_rest'        => false,
			'capability_type'     => 'post',
		]
	);
}
add_action( 'init', 'duy_register_cpts' );
