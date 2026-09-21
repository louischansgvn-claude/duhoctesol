<?php
/**
 * Carbon Fields field layer.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

function duy_fields_boot_carbon(): void {
	if ( class_exists( Carbon_Fields::class ) ) {
		Carbon_Fields::boot();
	}
}
add_action( 'after_setup_theme', 'duy_fields_boot_carbon' );

function duy_fields_register(): void {
	if ( ! class_exists( Container::class ) || ! class_exists( Field::class ) ) {
		return;
	}

	duy_fields_register_school();
	duy_fields_register_high_school();
	duy_fields_register_scholarship();
	duy_fields_register_event();
	duy_fields_register_student_story();
	duy_fields_register_guide();
	duy_fields_register_country();
	duy_fields_register_news();
	duy_fields_register_options();
	duy_fields_register_lead();
}
add_action( 'carbon_fields_register_fields', 'duy_fields_register' );

function duy_cf_options( array $values ): array {
	return array_combine( $values, $values );
}

function duy_cf_complex( string $name, string $label, array $fields ) {
	return Field::make( 'complex', $name, $label )
		->set_layout( 'tabbed-horizontal' )
		->add_fields( 'item', 'Item', $fields );
}

function duy_cf_association( string $name, string $label, array $types, int $max = -1 ) {
	$field = Field::make( 'association', $name, $label )->set_types( $types );

	if ( $max > -1 ) {
		$field->set_max( $max );
	}

	return $field;
}

function duy_fields_register_school(): void {
	Container::make( 'post_meta', 'Duy School Data' )
		->where( 'post_type', '=', 'school' )
		->add_fields(
			[
				duy_cf_association( 'country', 'Country', [ [ 'type' => 'term', 'taxonomy' => 'country' ] ], 1 ),
				Field::make( 'text', 'code', 'Code' ),
				Field::make( 'text', 'ranking', 'Ranking / note' )->set_help_text( 'Để trống nếu chưa xác nhận được với trường.' ),
				Field::make( 'text', 'fee', 'Fee' ),
				Field::make( 'select', 'fee_band', 'Fee band' )->set_options( [ 'low' => 'Low', 'mid' => 'Mid', 'high' => 'High' ] ),
				duy_cf_association( 'level', 'Study level', [ [ 'type' => 'term', 'taxonomy' => 'study_level' ] ], 1 ),
				duy_cf_association( 'major', 'Major', [ [ 'type' => 'term', 'taxonomy' => 'major' ] ], 1 ),
				Field::make( 'text', 'city', 'City' ),
				duy_cf_complex( 'programs', 'Programs', [ Field::make( 'text', 'name', 'Program' ) ] ),
				Field::make( 'media_gallery', 'gallery', 'Gallery' )->set_type( 'image' )->set_help_text( 'Ảnh khuôn viên trường. Chỉ dùng ảnh có quyền sử dụng.' ),
				Field::make( 'select', 'tag', 'Tag color' )->set_options( [ '' => 'Default', 'cyan' => 'Cyan', 'pink' => 'Pink' ] ),
				Field::make( 'checkbox', 'is_featured', 'Nổi bật' ),
				Field::make( 'text', 'full_name', 'Full name' ),
				Field::make( 'text', 'acronym', 'Acronym' ),
				Field::make( 'text', 'founded', 'Founded' )->set_help_text( 'Để trống nếu chưa xác nhận được với trường.' ),
				Field::make( 'text', 'type', 'School type' ),
				Field::make( 'text', 'students', 'Students' ),
				Field::make( 'text', 'website', 'Website URL' ),
				duy_cf_complex(
					'campuses',
					'Campuses',
					[
						Field::make( 'text', 'name', 'Campus name' ),
						Field::make( 'text', 'location', 'Location' ),
						Field::make( 'textarea', 'focus', 'Focus / facilities' ),
					]
				),
				duy_cf_complex(
					'rankings',
					'Rankings',
					[
						Field::make( 'text', 'org', 'Organization' ),
						Field::make( 'text', 'position', 'Position / note' ),
					]
				),
				Field::make( 'textarea', 'subject_rankings', 'Subject rankings note' ),
				duy_cf_complex(
					'key_stats',
					'Key stats',
					[
						Field::make( 'text', 'value_text', 'Value' ),
						Field::make( 'text', 'label', 'Label' ),
					]
				),
				duy_cf_complex(
					'why',
					'Why this school',
					[
						Field::make( 'select', 'icon', 'Icon' )->set_options( duy_cf_options( [ 'cap', 'map', 'award', 'target', 'shield', 'route', 'star', 'sparkles' ] ) ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'programs_ug',
					'Undergraduate programs',
					[
						Field::make( 'text', 'name', 'Program name' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'programs_pg',
					'Postgraduate programs',
					[
						Field::make( 'text', 'name', 'Program name' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'programs_pathway',
					'Pathway programs',
					[
						Field::make( 'text', 'name', 'Program name' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				Field::make( 'textarea', 'entry_ug', 'Undergraduate entry' ),
				Field::make( 'textarea', 'entry_pg', 'Postgraduate entry' ),
				duy_cf_complex(
					'english',
					'English requirements',
					[
						Field::make( 'text', 'test', 'Test' ),
						Field::make( 'text', 'score', 'Score / note' ),
					]
				),
				Field::make( 'textarea', 'intakes', 'Intakes' ),
				duy_cf_complex(
					'tuition',
					'Tuition rows',
					[
						Field::make( 'text', 'level', 'Level' ),
						Field::make( 'text', 'local', 'Local currency' ),
						Field::make( 'text', 'vnd', 'VND / note' ),
					]
				),
				duy_cf_complex(
					'living_costs',
					'Living costs',
					[
						Field::make( 'text', 'item', 'Item' ),
						Field::make( 'text', 'cost', 'Cost / note' ),
					]
				),
				Field::make( 'textarea', 'career_stats', 'Career stats note' ),
				duy_cf_complex(
					'salary',
					'Salary rows',
					[
						Field::make( 'text', 'field', 'Field' ),
						Field::make( 'text', 'range', 'Range / note' ),
					]
				),
				Field::make( 'textarea', 'partners', 'Industry partners / career service' ),
				Field::make( 'textarea', 'visa_note', 'Visa / post-study note' ),
			]
		);
}

/**
 * US high-school (THPT) data.
 *
 * Field names are prefixed `duy_hs_` so Carbon stores them at the exact
 * `_duy_hs_*` meta keys used by the Excel/JSON import. Editing a school in
 * wp-admin therefore produces the same data model as a bulk import, and the
 * THPT front-end (single-school-hs.php + duy_hs_cpt_cards) renders it.
 *
 * Use this group for schools tagged Bậc học = THPT + Quốc gia = Mỹ.
 */
function duy_fields_register_high_school(): void {
	Container::make( 'post_meta', 'Duy THPT (US High School)' )
		->where( 'post_type', '=', 'school' )
		->add_fields(
			[
				Field::make( 'text', 'duy_hs_school_id', 'School ID (mã trường)' )->set_help_text( 'Khóa chống trùng khi import. VD: USHS-001.' ),
				Field::make( 'text', 'duy_hs_canonical_key', 'Canonical key' ),
				Field::make( 'text', 'duy_hs_full_name', 'Tên đầy đủ' ),
				Field::make( 'text', 'duy_hs_short_name', 'Tên ngắn' ),
				Field::make( 'text', 'duy_hs_founded', 'Năm thành lập' ),
				Field::make( 'text', 'duy_hs_state', 'Bang' ),
				Field::make( 'text', 'duy_hs_school_type', 'Loại trường (PUBLIC/PRIVATE)' ),
				Field::make( 'text', 'duy_hs_category', 'Hình thức (Day / Boarding School)' ),
				Field::make( 'text', 'duy_hs_religion', 'Tôn giáo' ),
				Field::make( 'text', 'duy_hs_grades', 'Lớp nhận' ),
				Field::make( 'text', 'duy_hs_one_year_diploma', 'One-year diploma' ),
				Field::make( 'text', 'duy_hs_niche', 'Niche' ),
				Field::make( 'text', 'duy_hs_website', 'Website' ),
				Field::make( 'text', 'duy_hs_spring_status', 'Spring — trạng thái' )->set_help_text( 'Giữ nguyên: OPEN / CLOSED / N/A / Cần xác minh.' ),
				Field::make( 'text', 'duy_hs_spring_program_fee', 'Spring — program fee (USD)' ),
				Field::make( 'text', 'duy_hs_fall_status', 'Fall — trạng thái' ),
				Field::make( 'text', 'duy_hs_fall_program_fee', 'Fall — program fee (USD)' ),
				Field::make( 'text', 'duy_hs_app_fee', 'Application fee (USD)' ),
				Field::make( 'text', 'duy_hs_lowest_program_fee', 'Phí thấp nhất (USD)' ),
				Field::make( 'textarea', 'duy_hs_discount_note', 'Ghi chú ưu đãi' ),
				Field::make( 'textarea', 'duy_hs_hero', 'Hero / intro ngắn' )->set_help_text( 'Cũng có thể để trong Excerpt của bài.' ),
				Field::make( 'textarea', 'duy_hs_profile', 'Hồ sơ trường (profile)' )->set_help_text( 'Cũng có thể để trong nội dung bài viết.' ),
				Field::make( 'textarea', 'duy_hs_ranking_note', 'Ghi chú xếp hạng' ),
				Field::make( 'textarea', 'duy_hs_why_choose', 'Điểm nổi bật' )->set_help_text( 'Tách mỗi ý bằng dấu " | ".' ),
				Field::make( 'textarea', 'duy_hs_campus', 'Campus / cơ sở vật chất' ),
				Field::make( 'textarea', 'duy_hs_programs', 'Chương trình học' ),
				Field::make( 'textarea', 'duy_hs_admissions', 'Tuyển sinh / admissions' ),
				Field::make( 'textarea', 'duy_hs_english_summary', 'Yêu cầu tiếng Anh (tóm tắt)' ),
				Field::make( 'textarea', 'duy_hs_english', 'Tiếng Anh (chi tiết)' ),
				Field::make( 'textarea', 'duy_hs_cost_note', 'Chi phí (ghi chú)' ),
				Field::make( 'textarea', 'duy_hs_scholarship_note', 'Học bổng (ghi chú)' ),
				Field::make( 'textarea', 'duy_hs_career_note', 'Định hướng nghề nghiệp / đại học' ),
				Field::make( 'textarea', 'duy_hs_research_sources', 'Nguồn xác minh' )->set_help_text( 'Tách mỗi nguồn bằng dấu " | ".' ),
				Field::make( 'text', 'duy_hs_content_status', 'Trạng thái nội dung' ),
			]
		);
}

function duy_fields_register_scholarship(): void {
	Container::make( 'post_meta', 'Duy Scholarship Data' )
		->where( 'post_type', '=', 'scholarship' )
		->add_fields(
			[
				duy_cf_association( 'country', 'Country', [ [ 'type' => 'term', 'taxonomy' => 'country' ] ], 1 ),
				Field::make( 'text', 'value', 'Value' ),
				duy_cf_association( 'value_band', 'Value band', [ [ 'type' => 'term', 'taxonomy' => 'scholarship_value' ] ], 1 ),
				Field::make( 'date', 'deadline', 'Deadline' ),
				duy_cf_association( 'school', 'School', [ [ 'type' => 'post', 'post_type' => 'school' ] ], 1 ),
				Field::make( 'textarea', 'conditions', 'Conditions' ),
				Field::make( 'textarea', 'how_to', 'How to apply' ),
				Field::make( 'image', 'cover_image', 'Cover image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng.' ),
				Field::make( 'checkbox', 'is_featured', 'Nổi bật' ),
				Field::make( 'text', 'issuer', 'Issuer / unit' ),
				Field::make( 'text', 'quota', 'Quota' )->set_help_text( 'Để trống nếu chưa xác nhận được.' ),
				Field::make( 'text', 'intake', 'Intake' ),
				Field::make( 'textarea', 'intro', 'Intro' ),
				duy_cf_complex(
					'benefits',
					'Benefits',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex( 'eligibility', 'Eligibility checklist', [ Field::make( 'text', 'item', 'Item' ) ] ),
				duy_cf_complex( 'documents', 'Required documents', [ Field::make( 'text', 'item', 'Item' ) ] ),
				duy_cf_complex(
					'how_to_steps',
					'How to apply steps',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex( 'applicable_programs', 'Applicable schools / programs', [ Field::make( 'text', 'item', 'Item' ) ] ),
			]
		);
}

function duy_fields_register_event(): void {
	Container::make( 'post_meta', 'Duy Event Data' )
		->where( 'post_type', '=', 'event' )
		->add_fields(
			[
				Field::make( 'date_time', 'datetime', 'Datetime' ),
				duy_cf_association( 'type', 'Event type', [ [ 'type' => 'term', 'taxonomy' => 'event_type' ] ], 1 ),
				Field::make( 'text', 'place', 'Place' ),
				duy_cf_complex(
					'agenda',
					'Agenda',
					[
						Field::make( 'text', 'time', 'Time / milestone' ),
						Field::make( 'text', 'item', 'Agenda item' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				Field::make( 'text', 'register_cta', 'Register CTA' ),
				Field::make( 'image', 'cover_image', 'Cover image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng.' ),
				Field::make( 'checkbox', 'is_featured', 'Nổi bật' ),
				Field::make( 'textarea', 'audience', 'Audience' ),
				Field::make( 'text', 'fee', 'Fee' ),
				duy_cf_complex(
					'benefits',
					'Benefits',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'speakers',
					'Speakers / representatives',
					[
						Field::make( 'image', 'photo', 'Photo' )->set_value_type( 'id' )->set_help_text( 'Ảnh diễn giả. Cần có sự đồng ý của người trong ảnh.' ),
						Field::make( 'text', 'name', 'Name' ),
						Field::make( 'text', 'role', 'Role' ),
						Field::make( 'text', 'org', 'Organization' ),
						Field::make( 'textarea', 'bio', 'Short bio' ),
					]
				),
				duy_cf_complex(
					'videos',
					'YouTube videos',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'text', 'url', 'YouTube URL' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				Field::make( 'media_gallery', 'gallery', 'Gallery' )->set_type( 'image' )->set_help_text( 'Ảnh sự kiện. Chỉ dùng ảnh do mình chụp hoặc có quyền sử dụng.' ),
				duy_cf_association( 'participating_schools', 'Participating schools / partners', [ [ 'type' => 'post', 'post_type' => 'school' ] ] ),
				duy_cf_complex( 'who', 'Who should attend', [ Field::make( 'text', 'item', 'Item' ) ] ),
			]
		);
}

function duy_fields_register_student_story(): void {
	Container::make( 'post_meta', 'Duy Student Story Data' )
		->where( 'post_type', '=', 'student_story' )
		->add_fields(
			[
				Field::make( 'text', 'school', 'School' ),
				Field::make( 'text', 'award', 'Award' )->set_help_text( 'Để trống nếu chưa xác nhận được với trường.' ),
				Field::make( 'textarea', 'quote', 'Quote' ),
				Field::make( 'text', 'student_video', 'Student video URL' ),
				Field::make( 'text', 'school_video', 'School video URL' ),
				Field::make( 'image', 'avatar', 'Avatar/photo' )->set_value_type( 'id' )->set_help_text( 'Ảnh đại diện học viên. Không dùng ảnh nhận diện được mặt nếu chưa có sự đồng ý bằng văn bản.' ),
				duy_cf_association( 'country', 'Country', [ [ 'type' => 'term', 'taxonomy' => 'country' ] ], 1 ),
				Field::make( 'text', 'major', 'Major' ),
				Field::make( 'text', 'level', 'Level' ),
				Field::make( 'text', 'year', 'Year' ),
				duy_cf_complex(
					'story_blocks',
					'Story blocks',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				Field::make( 'textarea', 'advice', 'Advice / quote' ),
				duy_cf_complex(
					'result_stats',
					'Result stats',
					[
						Field::make( 'text', 'value_text', 'Value' ),
						Field::make( 'text', 'label', 'Label' ),
					]
				),
			]
		);
}

function duy_fields_register_guide(): void {
	Container::make( 'post_meta', 'Duy Guide Data' )
		->where( 'post_type', '=', 'guide' )
		->add_fields(
			[
				Field::make( 'text', 'order', 'Order' ),
				Field::make( 'textarea', 'lead', 'Lead' ),
				Field::make( 'textarea', 'hub_outcome', 'Hub timeline outcome' ),
				duy_cf_complex(
					'hub_tasks',
					'Hub checklist tasks',
					[
						Field::make( 'text', 'item', 'Task' ),
					]
				),
				Field::make( 'text', 'section_title', 'Section title' ),
				duy_cf_complex(
					'steps',
					'Steps',
					[
						Field::make( 'text', 'icon', 'Icon' ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				Field::make( 'checkbox', 'is_sequential', 'Sequential steps' ),
				Field::make( 'select', 'extra_type', 'Extra type' )->set_options( duy_cf_options( [ 'none', 'cost_table', 'phases', 'faq', 'checklist' ] ) ),
				Field::make( 'textarea', 'extra', 'Extra data / notes' ),
				Field::make( 'image', 'hero_image', 'Hero image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng.' ),
			]
		);
}

function duy_fields_register_country(): void {
	Container::make( 'term_meta', 'Duy Country Page Data' )
		->where( 'term_taxonomy', '=', 'country' )
		->add_fields(
			[
				Field::make( 'text', 'code', 'Code' ),
				Field::make( 'textarea', 'lead', 'Lead' ),
				Field::make( 'textarea', 'overview', 'Overview' ),
				duy_cf_complex(
					'stats',
						'Stats',
						[
							Field::make( 'text', 'value_text', 'Value' ),
							Field::make( 'text', 'label', 'Label' ),
						]
					),
				duy_cf_complex(
					'why',
					'Why cards',
					[
						Field::make( 'select', 'icon', 'Icon' )->set_options( duy_cf_options( [ 'star', 'cap', 'target', 'layers', 'shield', 'route', 'heart', 'award', 'globe', 'user', 'sparkles', 'map' ] ) ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'system',
					'Education system',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex( 'visa', 'Visa checklist', [ Field::make( 'text', 'item', 'Item' ) ] ),
				duy_cf_complex(
					'cost',
						'Cost rows',
						[
							Field::make( 'text', 'label', 'Label' ),
							Field::make( 'text', 'value_text', 'Value' ),
						]
					),
				duy_cf_association( 'schools', 'Related schools', [ [ 'type' => 'post', 'post_type' => 'school' ] ] ),
				duy_cf_association( 'scholarships', 'Related scholarships', [ [ 'type' => 'post', 'post_type' => 'scholarship' ] ] ),
				duy_cf_association( 'news', 'Related news', [ [ 'type' => 'post', 'post_type' => 'post' ] ] ),
				duy_cf_complex(
					'videos',
					'YouTube videos',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'text', 'school', 'School' )->set_help_text( 'Tùy chọn: tên trường hiển thị dạng chip trên card video.' ),
						Field::make( 'text', 'url', 'YouTube URL' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				)->set_help_text( 'Video YouTube theo từng quốc gia, hiển thị ở trang con /quoc-gia/{slug}/video/.' ),
				duy_cf_complex(
					'faqs',
					'FAQ (Câu hỏi thường gặp)',
					[
						Field::make( 'text', 'q', 'Câu hỏi' ),
						Field::make( 'textarea', 'a', 'Trả lời' ),
					]
				)->set_help_text( 'Ghi đè FAQ mặc định của theme cho trang quốc gia (và trang bậc học của quốc gia này). Trống = dùng FAQ mặc định trong inc/demo-data.php.' ),
				Field::make( 'image', 'hero_image', 'Hero image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng. Country/campus/landmark image.' ),
			]
		);
}

function duy_fields_register_news(): void {
	Container::make( 'post_meta', 'Duy News Data' )
		->where( 'post_type', '=', 'post' )
		->add_fields(
			[
				Field::make( 'textarea', 'lead', 'Lead' ),
				Field::make( 'image', 'cover_image', 'Cover image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng. Article cover.' ),
				Field::make( 'checkbox', 'is_featured', 'Nổi bật' ),
				duy_cf_complex( 'key_takeaways', 'Key takeaways', [ Field::make( 'text', 'item', 'Item' ) ] ),
				duy_cf_complex(
					'body_blocks',
					'Body blocks',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'body', 'Body' ),
					]
				),
				Field::make( 'textarea', 'quote', 'Pull quote' ),
			]
		);
}

function duy_archive_banner_fields(): array {
	$fields = [];
	foreach ( [ 'school' => 'School archive', 'scholarship' => 'Scholarship archive', 'event' => 'Event archive', 'news' => 'News archive' ] as $key => $label ) {
		$prefix   = 'archive_banner_' . $key;
		$fields[] = Field::make( 'checkbox', $prefix . '_enabled', $label . ' banner enabled' )->set_default_value( 'yes' );
		$fields[] = Field::make( 'image', $prefix . '_image', $label . ' banner image' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng.' );
		$fields[] = Field::make( 'text', $prefix . '_url', $label . ' banner URL' );
		$fields[] = Field::make( 'text', $prefix . '_title', $label . ' banner title' );
		$fields[] = Field::make( 'text', $prefix . '_cta', $label . ' banner CTA' );
	}

	return $fields;
}

function duy_fields_register_options(): void {
	Container::make( 'theme_options', 'Duy Settings' )
		->set_page_file( 'duy-settings' )
		->set_page_menu_title( 'Duy Settings' )
		->set_page_menu_position( 58 )
		->set_icon( 'dashicons-admin-site-alt3' )
		->add_fields(
			array_merge(
				[
					duy_cf_complex(
						'offices',
						'Offices',
					[
						Field::make( 'text', 'name', 'Name' ),
						Field::make( 'text', 'building', 'Building' ),
						Field::make( 'text', 'address', 'Address' ),
						Field::make( 'text', 'ward', 'Ward' ),
						Field::make( 'text', 'city', 'City' ),
						Field::make( 'text', 'postal_code', 'Postal code' )->set_help_text( 'Mã bưu chính (schema LocalBusiness). Để trống nếu chưa có.' ),
						Field::make( 'text', 'phone', 'Phone' )->set_help_text( 'Số riêng của văn phòng; trống = dùng Hotline.' ),
						Field::make( 'text', 'lat', 'Latitude' )->set_help_text( 'Toạ độ từ Google Maps, ví dụ 10.7726.' ),
						Field::make( 'text', 'lng', 'Longitude' )->set_help_text( 'Ví dụ 106.6899.' ),
						Field::make( 'text', 'maps_url', 'Google Maps URL' ),
						Field::make( 'text', 'hours', 'Opening hours' )->set_help_text( 'Định dạng schema.org, ví dụ "Mo-Fr 08:30-17:30, Sa 08:30-12:00".' ),
					]
				),
				Field::make( 'text', 'hotline', 'Hotline' )->set_default_value( '0906.510.747' ),
				Field::make( 'text', 'org_legal_name', 'Tên pháp lý công ty' )->set_help_text( 'Ví dụ "Công ty TNHH Tư vấn Ban Du học Hội TESOL TP.HCM" — dùng cho schema Organization (legalName). Trống = bỏ.' ),
				Field::make( 'text', 'org_founding_year', 'Năm thành lập' )->set_help_text( 'Dạng 2015. Trống = schema không in foundingDate.' ),
				Field::make( 'text', 'indexnow_key', 'IndexNow key' )->set_help_text( 'Lấy tại Bing Webmaster Tools → Settings → API access → IndexNow (32 ký tự hex). Khi có key, site tự báo Bing mỗi lần đăng/sửa bài.' ),
				Field::make( 'text', 'email', 'Email' )->set_default_value( 'louischan.sgvn@gmail.com' ),
				Field::make( 'text', 'zalo', 'Zalo' ),
				Field::make( 'text', 'messenger', 'Messenger' ),
				Field::make( 'text', 'facebook', 'Facebook' ),
				Field::make( 'text', 'instagram', 'Instagram' ),
				Field::make( 'text', 'youtube_url', 'YouTube URL' ),
				Field::make( 'text', 'tiktok_url', 'TikTok URL' ),
				Field::make( 'text', 'zalo_oa_url', 'Zalo OA URL' ),
				Field::make( 'text', 'gsc_verification', 'Google Search Console verification' )->set_help_text( 'Mã content của thẻ google-site-verification (dán cả thẻ meta cũng được). Để trống nếu xác minh qua DNS.' ),
				Field::make( 'text', 'bing_verification', 'Bing Webmaster verification (msvalidate.01)' ),
				Field::make( 'text', 'ga4_id', 'Google Analytics 4 Measurement ID' )->set_help_text( 'Dạng G-XXXXXXXXXX. Để trống nếu dùng Site Kit.' ),
				Field::make( 'text', 'global_cta', 'Global CTA' ),
				duy_cf_complex(
					'v2_bac_pages',
					'Mockup v2 - Bậc học pages',
					[
						Field::make( 'text', 'level_slug', 'Level slug' )->set_help_text( 'Match mockup v2 key, e.g. thpt, cao-dang, dai-hoc, sau-dai-hoc. Để trống nếu chưa xác nhận được.' ),
						Field::make( 'text', 'tagline', 'Tagline' ),
						Field::make( 'textarea', 'overview', 'Overview' ),
						duy_cf_complex( 'who_for', 'Who this is for', [ Field::make( 'text', 'item', 'Item' ) ] ),
						duy_cf_complex( 'requirements', 'Requirements', [ Field::make( 'text', 'item', 'Item' ) ] ),
						duy_cf_complex(
							'timeline',
							'Timeline',
							[
								Field::make( 'text', 'step', 'Step' ),
								Field::make( 'textarea', 'detail', 'Detail' ),
							]
						),
						duy_cf_complex( 'costs_notes', 'Cost notes', [ Field::make( 'text', 'item', 'Item' ) ] ),
						Field::make( 'textarea', 'pathway', 'Pathway' ),
						duy_cf_complex( 'duy_study_support', 'Ban Du học Hội TESOL TP.HCM support', [ Field::make( 'text', 'item', 'Item' ) ] ),
						Field::make( 'textarea', 'country_note', 'Country note' )->set_help_text( 'Use {country} placeholder when the note is country-specific. Để trống nếu chưa xác nhận được.' ),
					]
				)->set_help_text( 'Admin source for mockup v2 bậc học data. Keep Để trống nếu chưa xác nhận được. when claims need confirmation.' ),
				duy_cf_complex(
					'v2_majors',
					'Mockup v2 - Ngành học HOT',
					[
						Field::make( 'text', 'slug', 'Slug' )->set_help_text( 'Match mockup v2 major id, e.g. nganh-kinh-te. Để trống nếu chưa xác nhận được.' ),
						Field::make( 'text', 'icon', 'Icon' )->set_help_text( 'Use an existing icon key where possible. Để trống nếu chưa xác nhận được.' ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'lead', 'Lead' ),
						Field::make( 'textarea', 'overview', 'Overview' ),
						duy_cf_complex( 'match', 'Matched school major labels', [ Field::make( 'text', 'item', 'Item' ) ] ),
						duy_cf_complex( 'countries', 'Countries', [ Field::make( 'text', 'item', 'Country' ) ] ),
						duy_cf_complex(
							'why',
							'Why cards',
							[
								Field::make( 'text', 'icon', 'Icon' ),
								Field::make( 'text', 'title', 'Title' ),
								Field::make( 'textarea', 'desc', 'Description' ),
							]
						),
						duy_cf_complex( 'careers', 'Careers', [ Field::make( 'text', 'item', 'Career' ) ] ),
					]
				)->set_help_text( 'Admin source for mockup v2 ngành học HOT data. Keep Để trống nếu chưa xác nhận được. when claims need confirmation.' ),
				duy_cf_complex(
					'home_stats',
						'Home stats',
						[
							Field::make( 'text', 'value_text', 'Value' ),
							Field::make( 'text', 'label', 'Label' ),
						]
					),
				duy_cf_complex(
					'about_strengths',
					'About strengths',
					[
						Field::make( 'text', 'icon', 'Icon' ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'about_services',
					'About service groups',
					[
						Field::make( 'text', 'icon', 'Icon' ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'service_packages',
					'Service packages',
					[
						Field::make( 'text', 'icon', 'Icon' ),
						Field::make( 'text', 'label', 'Stepper label' ),
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
						Field::make( 'text', 'for', 'Best for' ),
						duy_cf_complex( 'includes', 'Includes', [ Field::make( 'text', 'item', 'Item' ) ] ),
					]
				),
				duy_cf_complex(
					'about_process',
					'About process',
					[
						Field::make( 'text', 'title', 'Title' ),
						Field::make( 'textarea', 'desc', 'Description' ),
					]
				),
				duy_cf_complex(
					'about_team',
					'About team',
					[
						Field::make( 'text', 'name', 'Name' ),
						Field::make( 'text', 'office', 'Office' ),
						Field::make( 'text', 'role', 'Role' ),
						Field::make( 'text', 'exp', 'Experience' ),
						Field::make( 'image', 'photo', 'Photo' )->set_value_type( 'id' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng.' ),
					]
					),
					duy_cf_complex( 'about_partners', 'About partners / achievements', [ Field::make( 'text', 'item', 'Item' ) ] ),
				Field::make( 'media_gallery', 'about_meet_gallery', 'About — Hình gặp gỡ đối tác / sự kiện' )->set_type( 'image' )->set_help_text( 'Chỉ dùng ảnh có quyền sử dụng. Slideshow hiển thị cạnh form ở trang Về chúng tôi.' ),
				],
				duy_archive_banner_fields()
			)
		);
}

function duy_fields_register_lead(): void {
	Container::make( 'post_meta', 'Duy Lead Data' )
		->where( 'post_type', '=', 'lead' )
		->add_fields(
			[
				Field::make( 'text', 'name', 'Name' ),
				Field::make( 'text', 'phone', 'Phone' ),
					Field::make( 'text', 'email', 'Email' ),
					Field::make( 'text', 'birth_year', 'Birth year' ),
					Field::make( 'text', 'english_certificate', 'English certificate' ),
					Field::make( 'text', 'location', 'Location' ),
					Field::make( 'text', 'country', 'Country' ),
					Field::make( 'text', 'intended_time', 'Intended time' ),
					Field::make( 'text', 'office', 'Office' ),
					Field::make( 'text', 'role', 'Role' ),
					Field::make( 'textarea', 'message', 'Message' ),
					Field::make( 'text', 'source', 'Source' ),
					Field::make( 'select', 'status', 'Status' )->set_options( [ 'new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed' ] ),
				]
			);
	}
