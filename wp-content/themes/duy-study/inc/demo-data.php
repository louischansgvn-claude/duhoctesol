<?php
/**
 * Demo content aligned with docs/mockup/duy-study-mockup.html.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_slugify( string $value ): string {
	return sanitize_title( remove_accents( $value ) );
}

function duy_country_slug( string $country ): string {
	$map = [
		'Mỹ'          => 'my',
		'Úc'          => 'uc',
		'Canada'     => 'canada',
		'New Zealand' => 'new-zealand',
		'Thổ Nhĩ Kỳ' => 'tho-nhi-ky',
		'Singapore'   => 'singapore',
		'Hàn Quốc'    => 'han-quoc',
		'Đức'         => 'duc',
		'Hà Lan'      => 'ha-lan',
		'Anh'         => 'anh',
		'Malaysia'    => 'malaysia',
		'Thụy Sỹ'     => 'thuy-sy',
		'Philippines' => 'philippines',
	];

	return $map[ $country ] ?? duy_slugify( $country );
}

function duy_country_code( string $country ): string {
	$map = [
		'Mỹ'          => 'US',
		'Úc'          => 'AU',
		'Canada'     => 'CA',
		'New Zealand' => 'NZ',
		'Thổ Nhĩ Kỳ' => 'TR',
		'Singapore'   => 'SG',
		'Hàn Quốc'    => 'KR',
		'Đức'         => 'DE',
		'Hà Lan'      => 'NL',
		'Anh'         => 'GB',
		'Malaysia'    => 'MY',
		'Thụy Sỹ'     => 'CH',
		'Philippines' => 'PH',
	];

	return $map[ $country ] ?? '';
}

function duy_country_image( string $country ): string {
	$map = [
		'Mỹ'          => 'photo-campus-library.webp',
		'Úc'          => 'photo-campus-steps.webp',
		'Canada'     => 'photo-library-study.webp',
		'New Zealand' => 'photo-classroom.webp',
		'Thổ Nhĩ Kỳ' => 'photo-article-laptop.webp',
		'Singapore'   => 'photo-campus-library.webp',
		'Hàn Quốc'    => 'photo-student-group.webp',
		'Đức'         => 'photo-campus-steps.webp',
		'Hà Lan'      => 'photo-classroom.webp',
		'Anh'         => 'photo-library-study.webp',
		'Malaysia'    => 'photo-student-group.webp',
		'Thụy Sỹ'     => 'photo-campus-steps.webp',
		'Philippines' => 'photo-classroom.webp',
	];

	return $map[ $country ] ?? 'photo-campus-library.webp';
}

function duy_img_uri( string $file ): string {
	$override = duy_img_override( $file );

	return '' !== ( $override['url'] ?? '' ) ? (string) $override['url'] : duy_asset_uri( 'assets/img/' . ltrim( $file, '/' ) );
}

/**
 * Thẻ trường dựng từ 572 post `school` (mỗi lớp importer tự đọc meta riêng) tốn
 * ~1,3 giây mỗi request. Cache 12 giờ, xoá khi có post school được lưu
 * (duy_flush_school_caches) để dữ liệu import luôn đúng.
 */
function duy_demo_schools(): array {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$cached = get_transient( 'duy_schools_cards' );
	if ( is_array( $cached ) && $cached ) {
		$memo = $cached;

		return $memo;
	}

	$memo = duy_build_school_cards();
	set_transient( 'duy_schools_cards', $memo, 12 * HOUR_IN_SECONDS );

	return $memo;
}

/** Xoá cache danh sách + thứ tự trường (gọi khi post school thay đổi). */
function duy_flush_school_caches(): void {
	delete_transient( 'duy_schools_cards' );
	delete_transient( 'duy_school_order' );
}
add_action( 'save_post_school', 'duy_flush_school_caches' );
add_action( 'deleted_post', 'duy_flush_school_caches' );

function duy_build_school_cards(): array {
	// All school cards now come from the imported `school` CPT layers merged below.
	$schools = [];

	$indexed = [];
	foreach ( $schools as $school ) {
		$id = (string) ( $school['id'] ?? '' );
		if ( '' === $id ) {
			continue;
		}
		$indexed[ $id ] = $school;
	}

	if ( function_exists( 'duy_mockup_v2_schools' ) ) {
		foreach ( duy_mockup_v2_schools() as $school ) {
			$id = (string) ( $school['id'] ?? '' );
			if ( '' === $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $school );
		}
	}

	// Imported high schools (THPT) from the `school` CPT.
	if ( function_exists( 'duy_hs_cpt_cards' ) ) {
		foreach ( duy_hs_cpt_cards() as $id => $school ) {
			if ( '' === (string) $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $school );
		}
	}

	// Imported US colleges & universities (cao-dang / dai-hoc) from `school` CPT.
	if ( function_exists( 'duy_ps_cpt_cards' ) ) {
		foreach ( duy_ps_cpt_cards() as $id => $school ) {
			if ( '' === (string) $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $school );
		}
	}

	// Imported Canada & Australia schools (THPT / cao-dang / dai-hoc) from `school` CPT.
	if ( function_exists( 'duy_caau_cpt_cards' ) ) {
		foreach ( duy_caau_cpt_cards() as $id => $school ) {
			if ( '' === (string) $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $school );
		}
	}

	// Imported other-countries schools (THPT / dai-hoc / anh-ngu) from `school` CPT.
	if ( function_exists( 'duy_othc_cpt_cards' ) ) {
		foreach ( duy_othc_cpt_cards() as $id => $school ) {
			if ( '' === (string) $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $school );
		}
	}

	return array_values( $indexed );
	}

/**
 * Học bổng là WP post thật (CPT scholarship, Carbon fields) → shape card demo
 * (qua duy_post_card_fallback) + post_id/date. Cache theo request.
 *
 * @return array<string,array>
 */
function duy_scholarship_cpt_cards(): array {
	static $cards = null;

	if ( null !== $cards ) {
		return $cards;
	}

	$cards = [];
	if ( ! function_exists( 'get_posts' ) || ! function_exists( 'duy_post_card_fallback' ) ) {
		return $cards;
	}

	$posts = get_posts(
		[
			'post_type'        => 'scholarship',
			'post_status'      => 'publish',
			'posts_per_page'   => 200,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);

	foreach ( $posts as $post ) {
		$card = duy_post_card_fallback( (int) $post->ID, 'scholarship' );
		$slug = (string) $post->post_name;
		if ( ! $card || '' === $slug ) {
			continue;
		}

		$deadline = (string) ( $card['d'] ?? '' );
		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $deadline, $m ) ) {
			$card['d'] = $m[3] . '/' . $m[2] . '/' . $m[1]; // Carbon date → dd/mm/yyyy như dữ liệu demo.
		}

		$school = duy_association_entries( 'school', (int) $post->ID, 'post', 'school' );
		if ( $school ) {
			$card['school'] = get_the_title( (int) $school[0]['id'] );
		}

		$card['id']       = $slug;
		$card['post_id']  = (int) $post->ID;
		$card['date']     = get_the_date( 'Y-m-d', $post );
		$card['featured'] = duy_boolish( duy_field( 'is_featured', (int) $post->ID, false ) );
		$card['n']        = duy_public_text( (string) $card['n'] );
		$cards[ $slug ]   = $card;
	}

	return $cards;
}

/** Học bổng = WP post thật + học bổng demo chưa trùng slug (demo đang noindex cho tới khi có nội dung thật). */
function duy_demo_scholarships(): array {
	$indexed = duy_scholarship_cpt_cards();

	foreach ( duy_demo_scholarships_defaults() as $item ) {
		$id = (string) ( $item['id'] ?? '' );
		if ( '' !== $id && ! isset( $indexed[ $id ] ) ) {
			$indexed[ $id ] = $item;
		}
	}

	return array_values( $indexed );
}

function duy_demo_scholarships_defaults(): array {
	return [
		[ 'id' => 'principal-50', 'date' => '2026-06-14', 'featured' => true, 'n' => 'Học bổng Hiệu trưởng 50%', 'c' => 'Úc', 'v' => '50% học phí', 'value' => '50%', 'level' => 'Đại học', 'd' => '30/08/2026', 'tag' => 'pink', 'school' => 'University of Sydney' ],
		[ 'id' => 'global-excellence', 'date' => '2026-06-13', 'n' => 'Global Excellence Award', 'c' => 'Canada', 'v' => '25-100%', 'value' => '25%', 'level' => 'Đại học', 'd' => '15/09/2026', 'tag' => '', 'school' => 'University of Toronto' ],
		[ 'id' => 'international-merit', 'date' => '2026-06-12', 'n' => 'International Merit', 'c' => 'New Zealand', 'v' => 'Tới NZ$20.000', 'value' => '25%', 'level' => 'Đại học', 'd' => '01/10/2026', 'tag' => 'cyan', 'school' => 'University of Auckland' ],
		[ 'id' => 'future-leaders', 'date' => '2026-06-11', 'n' => 'Future Leaders 2026', 'c' => 'Mỹ', 'v' => 'Toàn phần', 'value' => 'Toàn phần', 'level' => 'Thạc sĩ', 'd' => '20/08/2026', 'tag' => 'pink', 'school' => 'Arizona State University' ],
		[ 'id' => 'stem-30', 'date' => '2026-06-10', 'n' => 'STEM Scholarship', 'c' => 'Úc', 'v' => '30% học phí', 'value' => '25%', 'level' => 'Đại học', 'd' => '12/09/2026', 'tag' => 'cyan', 'school' => 'Monash University' ],
		[ 'id' => 'early-bird-ca', 'date' => '2026-06-09', 'n' => 'Early Bird Award', 'c' => 'Canada', 'v' => '5.000 CAD', 'value' => '25%', 'level' => 'Đại học', 'd' => '05/08/2026', 'tag' => '', 'school' => 'University of Toronto' ],
		[ 'id' => 'turkiye-pathway', 'date' => '2026-06-08', 'n' => 'Türkiye Pathway Support', 'c' => 'Thổ Nhĩ Kỳ', 'v' => 'Một phần học phí', 'value' => '25%', 'level' => 'Đại học', 'd' => '18/09/2026', 'tag' => 'pink', 'school' => 'METU' ],
	];
}

function duy_demo_events(): array {
	// Toàn bộ sự kiện đến từ `event` CPT đã import (xem inc/events.php).
	$events = [];

	$indexed = [];
	foreach ( $events as $event ) {
		$id = (string) ( $event['id'] ?? '' );
		if ( '' !== $id ) {
			$indexed[ $id ] = $event;
		}
	}

	// Sự kiện đã diễn ra được import vào `event` CPT.
	if ( function_exists( 'duy_ev_cpt_cards' ) ) {
		foreach ( duy_ev_cpt_cards() as $id => $event ) {
			if ( '' === (string) $id ) {
				continue;
			}
			$indexed[ $id ] = array_merge( $indexed[ $id ] ?? [], $event );
		}
	}

	return array_values( $indexed );
}

function duy_demo_pairs(): array {
	return [
		[ 'id' => 'tuong-van', 'name' => 'Nguyễn Tường Vân', 'school' => 'University of Sydney', 'code' => 'AU', 'award' => 'Học bổng 100%', 'i' => 'V', 'quote' => 'Mình hiểu rõ từng bước và không còn sợ phần visa như lúc đầu.' ],
		[ 'id' => 'thao-vy-video', 'name' => 'Liễu Dương Thảo Vy', 'school' => 'RMIT University', 'code' => 'AU', 'award' => 'Học bổng 5.000 AUD', 'i' => 'V', 'quote' => 'Timeline rõ giúp mình nộp học bổng sớm hơn dự định.' ],
		[ 'id' => 'khuong-duy', 'name' => 'Hoàng Khương Duy', 'school' => 'UTS Sydney', 'code' => 'AU', 'award' => 'Học bổng tiến sĩ 100%', 'i' => 'D', 'quote' => 'Ban Du học Hội TESOL TP.HCM giúp mình gom lại câu chuyện nghiên cứu mạch lạc hơn.' ],
	];
}

/**
 * Bài viết là WP post thật (Bài viết → Đăng) → cùng shape với dữ liệu demo
 * (id = slug, n, cat, c, lead, image, post_id, date, featured) để mọi khối tin tức,
 * sitemap và schema dùng chung. Cache theo request.
 *
 * @return array<string,array>
 */
function duy_news_cpt_cards(): array {
	static $cards = null;

	if ( null !== $cards ) {
		return $cards;
	}

	$cards = [];
	if ( ! function_exists( 'get_posts' ) ) {
		return $cards;
	}

	$posts = get_posts(
		[
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => 200,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);

	foreach ( $posts as $post ) {
		$slug = (string) $post->post_name;
		if ( '' === $slug ) {
			continue;
		}

		$cat        = 'Tin tức';
		$categories = get_the_category( $post->ID );
		if ( $categories && ! in_array( strtolower( (string) $categories[0]->name ), [ 'uncategorized', 'chưa phân loại', 'chưa được phân loại' ], true ) ) {
			$cat = (string) $categories[0]->name;
		}

		$lead = function_exists( 'duy_field' ) ? (string) duy_field( 'lead', $post->ID, '' ) : '';
		if ( '' === $lead ) {
			$lead = '' !== trim( (string) $post->post_excerpt ) ? (string) $post->post_excerpt : wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 32, '…' );
		}

		$image = '';
		if ( has_post_thumbnail( $post ) ) {
			$image = (string) get_the_post_thumbnail_url( $post, 'large' );
		} elseif ( function_exists( 'duy_field' ) ) {
			$image = duy_media_image_url( duy_field( 'cover_image', $post->ID, '' ) );
		}

		$cards[ $slug ] = [
			'id'       => $slug,
			'post_id'  => (int) $post->ID,
			'date'     => get_the_date( 'Y-m-d', $post ),
			'featured' => function_exists( 'duy_field' ) && duy_boolish( duy_field( 'is_featured', $post->ID, false ) ),
			'n'        => duy_public_text( get_the_title( $post ) ),
			'cat'      => $cat,
			'c'        => $cat . ' · ' . get_the_date( 'd/m/Y', $post ),
			'lead'     => duy_public_text( $lead ),
			'image'    => $image,
		];
	}

	return $cards;
}

/** Tin tức = WP post thật (mới nhất trước) + bài demo chưa bị trùng slug. */
function duy_demo_news(): array {
	$indexed = duy_news_cpt_cards();

	foreach ( duy_demo_news_defaults() as $article ) {
		$id = (string) ( $article['id'] ?? '' );
		if ( '' !== $id && ! isset( $indexed[ $id ] ) ) {
			$indexed[ $id ] = $article;
		}
	}

	return array_values( $indexed );
}

function duy_demo_news_defaults(): array {
	return [
		[ 'id' => 'visa-500-2026', 'date' => '2026-06-12', 'featured' => true, 'n' => 'Úc cập nhật chính sách visa 500 cho 2026', 'cat' => 'Visa', 'c' => 'Visa · 12/06/2026', 'lead' => 'Những điểm cần kiểm tra trước khi nộp hồ sơ visa du học Úc trong kỳ mới.' ],
		[ 'id' => 'scholarship-tips', 'date' => '2026-06-09', 'n' => '5 mẹo săn học bổng toàn phần hiệu quả', 'cat' => 'Kinh nghiệm', 'c' => 'Kinh nghiệm · 09/06/2026', 'lead' => 'Cách xây hồ sơ học bổng có câu chuyện rõ, số liệu cụ thể và deadline chủ động.' ],
		[ 'id' => 'deadline-sep', 'date' => '2026-06-04', 'n' => 'Deadline học bổng kỳ tháng 9 sắp đóng', 'cat' => 'Học bổng', 'c' => 'Học bổng · 04/06/2026', 'lead' => 'Danh sách deadline để học sinh kiểm tra cùng chuyên viên trước khi nộp.' ],
		[ 'id' => 'canada-pgwp', 'date' => '2026-06-02', 'n' => 'Canada và lộ trình sau tốt nghiệp', 'cat' => 'Visa', 'c' => 'Visa · 02/06/2026', 'lead' => 'Các thuật ngữ cần hiểu khi cân nhắc học tập và làm việc sau tốt nghiệp tại Canada.' ],
		[ 'id' => 'parent-budget', 'date' => '2026-05-28', 'n' => 'Phụ huynh nên dự trù ngân sách du học thế nào?', 'cat' => 'Kinh nghiệm', 'c' => 'Kinh nghiệm · 28/05/2026', 'lead' => 'Khung ngân sách dự kiến gồm học phí, sinh hoạt, bảo hiểm và khoản dự phòng.' ],
		[ 'id' => 'us-it-major', 'date' => '2026-05-20', 'n' => 'Du học ngành công nghệ thông tin tại Mỹ', 'cat' => 'Kinh nghiệm', 'c' => 'Kinh nghiệm · 20/05/2026', 'lead' => 'Gợi ý cách chọn trường, ngành, học phí và cơ hội thực tập cho nhóm ngành công nghệ.' ],
		[ 'id' => 'us-living-cost', 'date' => '2026-05-18', 'n' => 'Chi phí sinh hoạt khi du học tại Mỹ', 'cat' => 'Kinh nghiệm', 'c' => 'Kinh nghiệm · 18/05/2026', 'lead' => 'So sánh chi phí theo bang, loại chỗ ở và phong cách sống để gia đình lập ngân sách.' ],
		[ 'id' => 'us-health-insurance', 'date' => '2026-05-12', 'n' => 'Bảo hiểm cho du học sinh Mỹ', 'cat' => 'Visa', 'c' => 'Visa · 12/05/2026', 'lead' => 'Các loại bảo hiểm trường yêu cầu và câu hỏi cần hỏi trước khi chọn gói.' ],
		[ 'id' => 'us-culture', 'date' => '2026-05-08', 'n' => 'Văn hoá Mỹ: điều du học sinh cần biết', 'cat' => 'Kinh nghiệm', 'c' => 'Kinh nghiệm · 08/05/2026', 'lead' => 'Những khác biệt về lớp học, giao tiếp, tự lập và văn hóa campus.' ],
		[ 'id' => 'australia-scholarship', 'date' => '2026-05-02', 'n' => 'Học bổng du học Úc: phân loại và hồ sơ', 'cat' => 'Học bổng', 'c' => 'Học bổng · 02/05/2026', 'lead' => 'Tổng quan các nhóm học bổng merit, early-bird, ngành STEM và cách chuẩn bị hồ sơ.' ],
	];
}

function duy_demo_stories(): array {
	return [
		[ 'id' => 'minh-anh', 'q' => 'Lộ trình rõ ràng giúp mình tự tin nộp hồ sơ sớm và nhận học bổng 50%.', 'n' => 'Minh Anh', 's' => 'University of Sydney · Úc', 'i' => 'M' ],
		[ 'id' => 'quoc-bao', 'q' => 'Đội ngũ hỗ trợ tận tình từ bài luận đến phỏng vấn visa.', 'n' => 'Quốc Bảo', 's' => 'University of Toronto · Canada', 'i' => 'Q' ],
		[ 'id' => 'thao-vy', 'q' => 'Mình từng rất mơ hồ về chi phí, Ban Du học Hội TESOL TP.HCM giúp mọi thứ minh bạch.', 'n' => 'Thảo Vy', 's' => 'University of Auckland · New Zealand', 'i' => 'T' ],
		[ 'id' => 'an-nhien', 'q' => 'Gia đình mình có một checklist rõ ràng thay vì chạy deadline phút cuối.', 'n' => 'An Nhiên', 's' => 'Arizona State University · Mỹ', 'i' => 'A' ],
	];
}

function duy_demo_guides(): array {
	return [
		'g-visao'    => [
			'slug'      => 'vi-sao-nen-di-du-hoc',
			'title'     => 'Vì sao nên đi du học?',
			'lead'      => 'Du học không chỉ là tấm bằng — đó là bước ngoặt về tư duy, nghề nghiệp và con người bạn.',
			'outcome'   => 'Hiểu rõ động lực và giá trị du học trước khi bắt đầu.',
			'hub_tasks' => [
				'Tiếp cận nền giáo dục & môi trường học thuật hàng đầu',
				'Mở cơ hội việc làm và định cư quốc tế',
				'Trải nghiệm đa văn hóa, mở rộng góc nhìn & quan hệ',
				'Rèn tính tự lập, bản lĩnh và sự tự tin',
			],
			'st'        => 'Bốn giá trị lớn nhất',
			'steps'     => [ [ 'h' => 'Tiếp cận nền giáo dục hàng đầu', 'p' => 'Học trong môi trường hiện đại, chương trình tiên tiến, nâng cao chuyên môn và tư duy toàn cầu.' ], [ 'h' => 'Mở ra cơ hội việc làm & định cư', 'p' => 'Bằng cấp quốc tế từ trường uy tín mở đường làm việc và ở lại tại nhiều quốc gia phát triển.' ], [ 'h' => 'Mở rộng vốn văn hoá & trải nghiệm sống', 'p' => 'Hoà mình vào môi trường đa văn hoá, mở rộng các mối quan hệ và góc nhìn về thế giới.' ], [ 'h' => 'Tăng tính tự lập & sự tự tin', 'p' => 'Tự xoay xở cuộc sống nơi xứ người giúp bạn trưởng thành, bản lĩnh và tự tin hơn.' ] ],
			'extra_type' => 'none',
		],
		'g-kehoach'  => [
			'slug'      => 'len-ke-hoach-du-hoc',
			'title'     => 'Lên kế hoạch du học',
			'lead'      => 'Một kế hoạch rõ ràng từ đầu giúp bạn chủ động về thời gian, chi phí và cơ hội học bổng.',
			'outcome'   => 'Chọn nước, trường, ngành và dựng kế hoạch tài chính – thời gian.',
			'hub_tasks' => [
				'Chọn quốc gia theo ngôn ngữ, chi phí, cơ hội định cư',
				'Chọn trường & ngành phù hợp năng lực, mục tiêu',
				'Lập kế hoạch tài chính: học phí + sinh hoạt phí',
				'Tìm học bổng phù hợp hồ sơ',
				'Đánh giá & ôn IELTS/TOEFL, lên timeline nộp hồ sơ',
			],
			'st'        => 'Bốn việc cần xác định',
			'steps'     => [ [ 'h' => 'Chọn quốc gia', 'p' => 'Cân nhắc ngôn ngữ, văn hoá, chi phí sinh hoạt và cơ hội định cư. Ban Du học Hội TESOL TP.HCM tư vấn 5 điểm đến: Mỹ, Úc, Canada, New Zealand, Thổ Nhĩ Kỳ.' ], [ 'h' => 'Chọn trường & ngành', 'p' => 'Tìm trường uy tín đúng ngành bạn theo đuổi, đảm bảo chương trình phù hợp mục tiêu nghề nghiệp.' ], [ 'h' => 'Xác định thời gian du học', 'p' => 'Lên timeline theo kỳ nhập học, tính cả kỳ nghỉ và thực tập để hoàn thành đúng dự định.' ], [ 'h' => 'Dự trù chi phí', 'p' => 'Tính đủ học phí, sinh hoạt phí và các khoản khác. Tham khảo chi phí theo từng quốc gia bên dưới.' ] ],
			'extra_type' => 'cost_table',
		],
		'g-hoso'     => [
			'slug'      => 'chuan-bi-ho-so-du-hoc',
			'title'     => 'Chuẩn bị hồ sơ du học',
			'lead'      => 'Hồ sơ mạnh và đúng yêu cầu giúp tăng cơ hội nhận offer và học bổng.',
			'outcome'   => 'Hoàn thiện hồ sơ học thuật & giấy tờ để xin offer và visa.',
			'hub_tasks' => [
				'Học bạ/bảng điểm + chứng chỉ tiếng Anh (IELTS/TOEFL)',
				'Thư động lực (SOP), hồ sơ học thuật, thư giới thiệu',
				'Giấy tờ chứng minh tài chính cho visa',
				'(Dưới 18 tuổi) giấy giám hộ, xác nhận phụ huynh công chứng, thông tin nơi ở',
				'Rà soát theo yêu cầu từng trường trước khi nộp',
			],
			'st'        => 'Năm bước nộp hồ sơ',
			'steps'     => [ [ 'h' => 'Xác định trường & ngành mục tiêu', 'p' => 'Chốt danh sách trường và chương trình phù hợp năng lực, mục tiêu.' ], [ 'h' => 'Kiểm tra điều kiện học thuật & ngoại ngữ', 'p' => 'Đối chiếu yêu cầu đầu vào và chứng chỉ tiếng Anh (IELTS/TOEFL).' ], [ 'h' => 'Chuẩn bị hồ sơ cho từng trường', 'p' => 'Bảng điểm, thư giới thiệu, bài luận và các giấy tờ theo yêu cầu mỗi trường.' ], [ 'h' => 'Chuẩn bị hồ sơ tài chính & thư mục đích', 'p' => 'Kế hoạch tài chính rõ ràng và statement of purpose thuyết phục.' ], [ 'h' => 'Nộp hồ sơ qua Ban Du học Hội TESOL TP.HCM', 'p' => 'Chuyên viên rà soát, hoàn thiện và nộp đơn đúng deadline cho bạn.' ] ],
			'extra_type' => 'phases',
		],
		'g-thumoi'   => [
			'slug'      => 'sau-khi-nhan-thu-moi',
			'title'     => 'Sau khi nhận thư mời nhập học',
			'lead'      => 'Nhận offer là một cột mốc lớn — đây là các bước để biến offer thành tấm vé lên đường.',
			'outcome'   => 'Biến offer thành tấm vé: xác nhận, đóng phí, CoE, visa.',
			'hub_tasks' => [
				'Kiểm tra kỹ offer: ngành, ngày nhập học, điều kiện kèm theo',
				'Xác nhận chấp nhận offer đúng hạn',
				'Đóng đặt cọc/học phí kỳ đầu + bảo hiểm y tế',
				'Nhận CoE/CAS/I-20 làm cơ sở xin visa',
				'Hoàn thiện hồ sơ visa: CoE, hộ chiếu, chứng minh tài chính',
			],
			'st'        => 'Năm bước sau khi có offer',
			'steps'     => [ [ 'h' => 'Kiểm tra thư mời & thông tin học bổng', 'p' => 'Đọc kỹ chương trình, ngày nhập học và các điều kiện; báo ngay nếu có sai sót.' ], [ 'h' => 'Chấp nhận thư mời', 'p' => 'Xác nhận với trường qua form/email trong thời hạn quy định.' ], [ 'h' => 'Đóng đặt cọc & các khoản phí', 'p' => 'Nộp đặt cọc, học phí đầu kỳ và bảo hiểm y tế qua kênh chuyển tiền uy tín.' ], [ 'h' => 'Nhận CoE / CAS', 'p' => 'Trường cấp xác nhận ghi danh — giấy tờ cốt lõi để xin visa.' ], [ 'h' => 'Chuẩn bị hồ sơ visa', 'p' => 'Tập hợp CoE, hộ chiếu, chứng minh tài chính và giấy tờ theo yêu cầu, nộp đúng hạn.' ] ],
			'extra_type' => 'faq',
		],
		'g-lenduong' => [
			'slug'      => 'truoc-khi-len-duong',
			'title'     => 'Trước khi lên đường',
			'lead'      => 'Chuẩn bị kỹ những ngày cuối giúp bạn tự tin bắt đầu cuộc sống mới.',
			'outcome'   => 'Chuẩn bị vé, chỗ ở, tài chính và tinh thần cho ngày khởi hành.',
			'hub_tasks' => [
				'Đặt vé máy bay & đăng ký đưa đón sân bay',
				'Tìm & đăng ký chỗ ở theo gợi ý của trường',
				'Chuyển học phí/sinh hoạt phí qua kênh an toàn (vd Flywire)',
				'Soạn hành lý theo quy định, tránh đồ cấm; mua SIM khi đến nơi',
				'Tìm hiểu văn hóa, khí hậu; kết nối cộng đồng du học sinh',
			],
			'st'        => 'Bốn việc cần chuẩn bị',
			'steps'     => [ [ 'h' => 'Tham gia buổi định hướng tiền du học', 'p' => 'Buổi Pre-departure của Ban Du học Hội TESOL TP.HCM và trường đối tác trang bị kiến thức cần thiết.' ], [ 'h' => 'Tìm chỗ ở & đặt vé máy bay', 'p' => 'Tham khảo chỗ ở do trường giới thiệu và đặt vé phù hợp lịch nhập học.' ], [ 'h' => 'Đăng ký dịch vụ đưa đón sân bay', 'p' => 'Đăng ký đón tại sân bay nếu cần để chuyến đi đầu tiên suôn sẻ.' ], [ 'h' => 'Đóng gói hành lý theo cẩm nang', 'p' => 'Soạn hành lý theo cẩm nang Pre-departure: giấy tờ quan trọng và vật dụng thiết yếu.' ] ],
			'extra_type' => 'checklist',
		],
	];
}

function duy_demo_faqs(): array {
	return [
		[ 'q' => 'Nếu visa bị trễ so với ngày nhập học thì sao?', 'a' => 'Nhiều trường cho phép hoãn nhập học (deferral) sang kỳ kế tiếp. Ban Du học Hội TESOL TP.HCM sẽ hỗ trợ bạn làm việc với trường.' ],
		[ 'q' => 'Có được hoàn phí nếu visa bị từ chối không?', 'a' => 'Tùy chính sách từng trường; thường hoàn lại phần lớn học phí đã đóng khi có thư từ chối visa.' ],
		[ 'q' => 'Hồ sơ visa mất bao lâu để xử lý?', 'a' => 'Thời gian khác nhau theo quốc gia và thời điểm; nên nộp sớm ngay khi có CoE/CAS.' ],
		[ 'q' => 'Du học sinh dưới 18 tuổi cần thủ tục gì thêm?', 'a' => 'Cần người giám hộ và xác nhận sắp xếp chỗ ở phù hợp theo quy định của nước đến.' ],
		[ 'q' => 'Nên nhập cảnh trước ngày nhập học bao lâu?', 'a' => 'Thường 1–2 tuần để ổn định chỗ ở, làm thủ tục và làm quen môi trường.' ],
		[ 'q' => 'Ban Du học Hội TESOL TP.HCM có thu phí tư vấn ban đầu không?', 'a' => 'Liên hệ hotline 0906.510.747 hoặc để lại thông tin để được báo rõ chính sách phí theo từng gói dịch vụ. Ban Du học Hội TESOL TP.HCM luôn thông báo chi phí trước khi bắt đầu hồ sơ.' ],
	];
}

function duy_country_overview_default( string $slug, array $country ): string {
	$overviews = [
		'my'           => 'Mỹ là thị trường rất rộng, phù hợp khi học sinh cần nhiều mức trường và học bổng để tối ưu hồ sơ. Gia đình nên bắt đầu từ loại trường, bang, ngân sách và câu chuyện Study Plan để mọi lựa chọn nhất quán trước vòng visa.',
		'uc'           => 'Úc phù hợp với hồ sơ muốn hệ thống bằng cấp rõ, thành phố sinh viên lớn, ngành ứng dụng mạnh và lộ trình sau tốt nghiệp cần được tính từ đầu. Khi shortlist, nên đặt cạnh nhau thành phố, học phí, visa 500, bảo hiểm OSHC và cơ hội thực tập.',
		'canada'       => 'Canada nổi bật ở lộ trình college, university và co-op, giúp học sinh cân bằng chi phí với trải nghiệm làm việc thực tế. Quyết định tốt thường bắt đầu từ tỉnh bang, DLI, điều kiện PGWP và kế hoạch tài chính đủ chặt.',
		'new-zealand'  => 'New Zealand hợp với học sinh cần môi trường an toàn, lớp học gần gũi và nhịp sống dễ thích nghi. Hành trình nên được thiết kế quanh ngành học, thành phố, yêu cầu tiếng Anh, ngân sách NZD và điều kiện visa tại thời điểm nộp.',
		'tho-nhi-ky'   => 'Thổ Nhĩ Kỳ là điểm đến mới cho gia đình muốn cân nhắc học phí cạnh tranh và trải nghiệm giao thoa Á-Âu. Nội dung trường, học bổng và visa cần được xác minh kỹ theo từng kỳ vì dữ liệu thay đổi nhanh.',
	];

	return $overviews[ $slug ] ?? ( $country['lead'] ?? '' );
}

function duy_country_reason_defaults( string $slug, array $fallback = [] ): array {
	$reasons = [
		'my'           => [
			[ 'icon' => 'cap', 't' => 'Hệ trường rộng và linh hoạt', 'd' => $fallback[0] ?? 'Đại học nghiên cứu, liberal arts college, community college và pathway tạo nhiều cách vào trường phù hợp năng lực.' ],
			[ 'icon' => 'award', 't' => 'Học bổng cạnh tranh', 'd' => $fallback[1] ?? 'Học bổng thường xét GPA, tiếng Anh, bài luận, hoạt động và độ phù hợp với trường; cần chuẩn bị sớm.' ],
			[ 'icon' => 'target', 't' => 'Study Plan cần nhất quán', 'd' => $fallback[2] ?? 'Visa F-1 cần câu chuyện chọn ngành, trường, bang và tài chính rõ ràng để tránh hồ sơ rời rạc.' ],
		],
		'uc'           => [
			[ 'icon' => 'shield', 't' => 'Bằng cấp được công nhận rộng', 'd' => $fallback[0] ?? 'Khung AQF giúp học sinh đối chiếu lộ trình từ nghề, cao đẳng, đại học đến sau đại học rõ hơn.' ],
			[ 'icon' => 'route', 't' => 'Thành phố quyết định ngân sách', 'd' => $fallback[1] ?? 'Sydney, Melbourne, Brisbane, Perth, Adelaide và Canberra khác nhau rõ về chi phí, trường và cơ hội nghề.' ],
			[ 'icon' => 'heart', 't' => 'Ngành ứng dụng mạnh', 'd' => $fallback[2] ?? 'IT, kỹ thuật, y tế, giáo dục và dữ liệu thường được quan tâm vì gắn với nhu cầu nhân lực.' ],
		],
		'canada'       => [
			[ 'icon' => 'award', 't' => 'Chi phí và chất lượng cân bằng', 'd' => $fallback[0] ?? 'College, university và pathway giúp gia đình chọn lộ trình phù hợp ngân sách, học lực và mục tiêu nghề nghiệp.' ],
			[ 'icon' => 'route', 't' => 'Co-op và PGWP cần kiểm tra', 'd' => $fallback[1] ?? 'Co-op và PGWP có thể hỗ trợ kinh nghiệm sau tốt nghiệp nhưng điều kiện thay đổi theo chương trình.' ],
			[ 'icon' => 'map', 't' => 'Tỉnh bang tạo khác biệt lớn', 'd' => $fallback[2] ?? 'Khí hậu, chi phí, cơ hội việc làm và cộng đồng sinh viên thay đổi rõ giữa Ontario, BC, Quebec và các tỉnh khác.' ],
		],
		'new-zealand'  => [
			[ 'icon' => 'shield', 't' => 'An toàn và dễ thích nghi', 'd' => $fallback[0] ?? 'Môi trường sống yên bình và lớp học tương tác phù hợp học sinh cần nhịp học bền vững.' ],
			[ 'icon' => 'user', 't' => 'Quy mô học gần gũi', 'd' => $fallback[1] ?? 'Các trường lớn có nhiều lựa chọn ngành nhưng trải nghiệm học tập thường cá nhân hóa hơn.' ],
			[ 'icon' => 'route', 't' => 'Visa sau tốt nghiệp cần lên sớm', 'd' => $fallback[2] ?? 'Điều kiện post-study work phụ thuộc ngành, bậc học và chính sách tại thời điểm nộp.' ],
		],
		'tho-nhi-ky'   => [
			[ 'icon' => 'award', 't' => 'Học phí cạnh tranh', 'd' => $fallback[0] ?? 'Phù hợp gia đình tìm lựa chọn chi phí mềm hơn nhưng vẫn cần xác minh theo trường và ngành.' ],
			[ 'icon' => 'globe', 't' => 'Cầu nối Á-Âu', 'd' => $fallback[1] ?? 'Môi trường đa văn hóa, vị trí kết nối châu Âu và châu Á tạo trải nghiệm học tập khác biệt.' ],
			[ 'icon' => 'cap', 't' => 'Nhiều chương trình tiếng Anh', 'd' => $fallback[2] ?? 'Một số ngành kỹ thuật, y sinh, kiến trúc có lựa chọn bằng tiếng Anh; cần rà soát từng chương trình.' ],
		],
	];

	return $reasons[ $slug ] ?? [];
}

function duy_demo_countries(): array {
	$countries = [
		'my' => [ 'id' => 'country-us', 'code' => 'US', 'cls' => 'cc-us', 'name' => 'Mỹ', 'title' => 'Du học Mỹ', 'lead' => 'Mỹ phù hợp với học sinh muốn nhiều lựa chọn trường, ngành và bang; hồ sơ cần thể hiện rõ mục tiêu học tập, ngân sách và kế hoạch nghề nghiệp sau tốt nghiệp.', 'stats' => [ [ 'F-1', 'visa du học chính' ], [ 'I-20', 'giấy tờ cốt lõi từ trường' ], [ 'CPT/OPT', 'lộ trình thực tập và làm việc' ], [ '2+2', 'lộ trình college giúp tối ưu chi phí' ] ], 'why' => [ 'Hệ thống trường rất rộng: đại học nghiên cứu, liberal arts college, community college và pathway, giúp học sinh chọn lộ trình sát năng lực.', 'Cơ hội học bổng đa dạng, thường dựa trên GPA, tiếng Anh, bài luận, hoạt động ngoại khóa và mức độ phù hợp với trường.', 'Hồ sơ visa Mỹ cần nhất quán giữa chọn trường, ngành, bang, tài chính và kế hoạch nghề nghiệp; vì vậy phải chuẩn bị Study Plan sớm.' ], 'system' => [ 'High School: phù hợp học sinh muốn làm quen môi trường Mỹ sớm, cần kế hoạch giám hộ và tài chính rõ.', 'Community College: lộ trình 2+2 giúp học 2 năm đầu chi phí mềm hơn rồi chuyển tiếp lên đại học.', 'University: lựa chọn cử nhân/sau đại học đa dạng, cạnh tranh theo ranking, ngành và học bổng.' ], 'visa' => [ 'Thư nhập học và I-20', 'Đóng SEVIS fee', 'Điền DS-160', 'Đặt lịch và phỏng vấn F-1', 'Hồ sơ tài chính và Study Plan nhất quán' ], 'cost' => [ 'Học phí: dao động lớn theo loại trường, bang và ranking', 'Community college/2+2 thường là hướng tối ưu ngân sách', 'Sinh hoạt phí phụ thuộc thành phố; nên lập ngân sách theo từng năm học.', 'Học bổng có thể từ mức hỗ trợ nhỏ đến phần lớn học phí, cần nộp sớm và chuẩn bị bài luận.' ], 'schools' => [ 'harvard-university', 'stanford-university', 'massachusetts-institute-of-technology', 'university-of-california-berkeley', 'columbia-university' ], 'schols' => [ 'future-leaders' ], 'news' => [ 'us-it-major', 'us-living-cost', 'us-health-insurance' ] ],
		'uc' => [ 'id' => 'country-au', 'code' => 'AU', 'cls' => 'cc-au', 'name' => 'Úc', 'title' => 'Du học Úc', 'lead' => 'Úc là điểm đến mạnh về chất lượng đào tạo, hệ thống bằng cấp rõ ràng, nhiều thành phố sinh viên lớn và lộ trình sau tốt nghiệp cần được lên kế hoạch từ đầu.', 'stats' => [ [ 'Top 3', 'điểm đến du học phổ biến' ], [ '9', 'trường trong Top 100 QS 2026' ], [ '48h/2 tuần', 'quy định làm thêm trong kỳ học' ], [ 'Visa 500', 'student visa' ] ], 'why' => [ 'Khung bằng cấp AQF giúp lộ trình từ nghề, cao đẳng, đại học đến sau đại học rõ ràng và dễ đối chiếu khi chuyển tiếp.', 'Sydney, Melbourne, Brisbane, Perth, Adelaide và Canberra đều có nhóm trường mạnh riêng, ảnh hưởng trực tiếp đến chi phí và cơ hội nghề nghiệp.', 'Các ngành IT, kỹ thuật, y tế, giáo dục và dữ liệu thường được quan tâm vì gắn với nhu cầu nhân lực và chính sách sau tốt nghiệp.' ], 'system' => [ 'Secondary school: phù hợp học sinh muốn học sớm tại môi trường quốc tế, cần kiểm tra yêu cầu tuổi và giám hộ.', 'Foundation/VET/TAFE/Diploma: lộ trình chuyển tiếp hoặc thực hành, có thể tối ưu chi phí trước khi vào đại học.', 'Bachelor/Master: nhiều lựa chọn tại Go8, đại học công nghệ và regional; yêu cầu IELTS/GPA thay đổi theo trường.' ], 'visa' => [ 'Offer Letter và CoE', 'OSHC bảo hiểm du học sinh', 'Chứng minh tài chính theo yêu cầu hiện hành', 'GS statement thay cho GTE', 'Khám sức khỏe và nộp Visa Subclass 500' ], 'cost' => [ 'Trung học: khoảng 20.000 - 30.000 AUD/năm', 'VET/TAFE/Dự bị: khoảng 15.000 - 28.000 AUD/năm', 'Đại học: khoảng 30.000 - 45.000 AUD/năm; nhóm Go8 có thể cao hơn', 'Sinh hoạt: Sydney/Melbourne thường cao hơn Brisbane, Perth, Adelaide, Canberra' ], 'schools' => [ 'the-university-of-melbourne-unimelb-bang-victoria', 'the-university-of-new-south-wales-unsw-sydney-bang-nsw', 'the-university-of-sydney-usyd-bang-nsw', 'australian-national-university-anu-th-do-canberra', 'monash-university-bang-victoria', 'the-university-of-queensland-uq-bang-queensland' ], 'schols' => [ 'principal-50', 'stem-30' ], 'news' => [ 'australia-scholarship', 'deadline-sep' ] ],
		'canada' => [ 'id' => 'country-ca', 'code' => 'CA', 'cls' => 'cc-ca', 'name' => 'Canada', 'title' => 'Du học Canada', 'lead' => 'Canada phù hợp với gia đình muốn cân bằng giữa chất lượng đào tạo, chi phí, co-op và cơ hội làm việc sau tốt nghiệp; lựa chọn tỉnh bang cần gắn với ngành và ngân sách.', 'stats' => [ [ 'Co-op', 'thực tập hưởng lương ở nhiều chương trình' ], [ 'PGWP', 'ở lại làm việc sau tốt nghiệp' ], [ '30-50k CAD', 'ngân sách năm tham khảo' ], [ 'College', 'lộ trình thực tiễn, học nhanh - làm sớm' ] ], 'why' => [ 'Canada có nhiều lộ trình từ trung học, college, đại học đến sau đại học, trong đó college mạnh về ứng dụng thực tế.', 'Co-op và PGWP là hai yếu tố quan trọng giúp sinh viên tích lũy kinh nghiệm, nhưng điều kiện thay đổi theo chương trình và chính sách.', 'Thành phố và tỉnh bang khác nhau rõ về chi phí, khí hậu, cơ hội việc làm và cộng đồng sinh viên quốc tế.' ], 'system' => [ 'College: hướng thực hành, tiết kiệm và phù hợp học sinh muốn đi làm sớm hoặc chuyển tiếp.', 'University: phù hợp học thuật, nghiên cứu, ngành chuyên sâu hoặc mục tiêu Master/PhD.', 'Pathway/EAP: hỗ trợ khi tiếng Anh chưa đủ chuẩn; cần kiểm tra trường có chấp nhận Duolingo/TOEFL/IELTS hay không.' ], 'visa' => [ 'Letter of Acceptance', 'Proof of funds', 'Study permit', 'Biometrics/khám sức khỏe nếu được yêu cầu', 'Kế hoạch học tập và tài chính rõ ràng' ], 'cost' => [ 'Tổng chi phí trung bình: 30.000 - 50.000 CAD/năm', 'College và thành phố nhỏ thường giúp giảm ngân sách so với đại học lớn.', 'Sinh viên có thể làm thêm theo quy định hiện hành, nhưng không nên xem đây là nguồn tài chính chính.', 'Học bổng và co-op cần kiểm tra theo từng trường, ngành và kỳ nhập học.' ], 'schools' => [ 'university-of-toronto-uoft', 'mcmaster-university', 'university-of-waterloo', 'western-university-university-of-western-ontario', 'queen-s-university', 'seneca-polytechnic-toronto' ], 'schols' => [ 'global-excellence', 'early-bird-ca' ], 'news' => [ 'canada-pgwp', 'parent-budget' ] ],
		'new-zealand' => [ 'id' => 'country-nz', 'code' => 'NZ', 'cls' => 'cc-nz', 'name' => 'New Zealand', 'title' => 'Du học New Zealand', 'lead' => 'New Zealand nổi bật với môi trường học an toàn, quy mô lớp học gần gũi, hệ thống đại học chất lượng và lộ trình visa cần chuẩn bị kỹ về tài chính, sức khỏe, mục tiêu học tập.', 'stats' => [ [ 'Top 3%', 'nhóm đại học hàng đầu thế giới' ], [ 'PGW', 'post-study work visa' ], [ 'NZD', 'chi phí cần tính theo khóa học' ], [ 'An toàn', 'môi trường sống thân thiện' ] ], 'why' => [ 'Môi trường học tập yên bình, lớp học tương tác và chất lượng sống cao phù hợp học sinh cần nhịp học bền vững.', 'Các trường đại học lớn có nhiều lựa chọn ở kinh doanh, kỹ thuật, sức khỏe, khoa học ứng dụng và giáo dục.', 'Sau tốt nghiệp có thể xem xét visa làm việc theo điều kiện ngành, bậc học và chính sách tại thời điểm nộp.' ], 'system' => [ 'Secondary school/Foundation: phù hợp học sinh cần làm quen môi trường trước khi vào bậc đại học.', 'Bachelor/Master: lựa chọn tại các đại học lớn như Auckland, Otago, Waikato, Canterbury, Massey.', 'Vocational/Pathway: phù hợp hồ sơ cần lộ trình thực hành hoặc nâng tiếng Anh trước khóa chính.' ], 'visa' => [ 'Offer of Place hoặc thư chấp nhận nhập học', 'Bằng cấp/bảng điểm phù hợp khóa học', 'Chứng minh tài chính cho học phí và sinh hoạt', 'Kế hoạch học tập và trở về rõ ràng', 'Sức khỏe, lý lịch tư pháp và hồ sơ cá nhân' ], 'cost' => [ 'Học phí phụ thuộc bậc học, trường và ngành', 'Visa student fee/levy cần kiểm tra theo biểu phí hiện hành', 'Sinh hoạt phí thay đổi theo thành phố, loại chỗ ở và phong cách sống.', 'Học bổng có thể giảm một phần chi phí, cần rà soát deadline từng trường.' ], 'schools' => [ 'the-university-of-auckland-uoa', 'university-of-otago-dunedin', 'university-of-waikato-hamilton-and-tauranga', 'university-of-canterbury-uc-christchurch', 'massey-university-auckland-palmerston-north-wellington', 'victoria-university-of-wellington-vuw' ], 'schols' => [ 'international-merit' ], 'news' => [ 'parent-budget' ] ],
		'tho-nhi-ky' => [ 'id' => 'country-tr', 'code' => 'TR', 'cls' => 'cc-tr', 'name' => 'Thổ Nhĩ Kỳ', 'title' => 'Du học Thổ Nhĩ Kỳ', 'lead' => 'Điểm đến mới với học phí cạnh tranh, vị trí giao thoa Á-Âu và nhiều chương trình học bằng tiếng Anh.', 'stats' => [ [ 'Chi phí', 'cạnh tranh' ], [ 'Á-Âu', 'môi trường đa văn hóa' ], [ 'English', 'nhiều chương trình quốc tế' ], [ 'Bursları', 'học bổng chính phủ' ] ], 'why' => [ 'Phù hợp gia đình tìm lựa chọn chi phí mềm hơn.', 'Văn hóa đa dạng, vị trí kết nối châu Âu và châu Á.', 'Một số ngành kỹ thuật, y sinh, kiến trúc có lựa chọn tốt.' ], 'system' => [ 'Undergraduate: nhiều chương trình tiếng Anh ở đại học công/tư.', 'Graduate: nhóm ngành kỹ thuật, kinh tế, khoa học xã hội.', 'Language prep: có thể cần dự bị tiếng tùy chương trình.' ], 'visa' => [ 'Acceptance letter', 'Proof of accommodation/funds', 'Health insurance', 'Student visa appointment' ], 'cost' => [ 'Học phí: khoảng 3.000 - 12.000 USD/năm', 'Sinh hoạt: thấp hơn nhiều nước nói tiếng Anh', 'Học bổng: tùy trường/chính phủ từng năm.' ], 'schools' => [ 'istanbul-okan-university', 'istanbul-university-dai-hoc-istanbul', 'istanbul-ayd-n-university-iau', 'bilkent-university-ankara', 'ko-university-istanbul', 'hacettepe-university' ], 'schols' => [ 'turkiye-pathway' ], 'news' => [ 'parent-budget' ] ],
	];

	if ( function_exists( 'duy_mockup_v2_countries' ) ) {
		foreach ( duy_mockup_v2_countries() as $slug => $country ) {
			$countries[ $slug ] = array_merge( $countries[ $slug ] ?? [], $country );
		}
	}

	foreach ( $countries as $slug => &$country ) {
		$country['overview'] = $country['overview'] ?? duy_country_overview_default( (string) $slug, $country );
		if ( isset( $country['why'][0] ) && is_string( $country['why'][0] ) ) {
			$country['why'] = duy_country_reason_defaults( (string) $slug, $country['why'] );
		}
	}
	unset( $country );

	return $countries;
}

function duy_find_by_id( array $items, string $id ): ?array {
	foreach ( $items as $item ) {
		if ( ( $item['id'] ?? '' ) === $id ) {
			return $item;
		}
	}

	return null;
}

function duy_school_acronym( string $name ): string {
	if ( preg_match( '/\(([^)]+)\)/', $name, $matches ) ) {
		return strtoupper( trim( $matches[1] ) );
	}

	$stop_words = [ 'of', 'the', 'and' ];
	$letters    = [];
	foreach ( preg_split( '/\s+/', preg_replace( '/[^A-Za-z0-9\s]/', ' ', $name ) ) as $word ) {
		$word = trim( $word );
		if ( '' === $word || in_array( strtolower( $word ), $stop_words, true ) ) {
			continue;
		}
		$letters[] = strtoupper( substr( $word, 0, 1 ) );
	}

	return implode( '', array_slice( $letters, 0, 5 ) ) ?: strtoupper( substr( $name, 0, 3 ) );
}

function duy_school_currency_label( string $country ): string {
	$map = [
		'Mỹ'          => 'USD',
		'Úc'          => 'AUD',
		'Canada'     => 'CAD',
		'New Zealand' => 'NZD',
		'Thổ Nhĩ Kỳ' => 'USD',
		'Anh'         => 'GBP',
		'Hà Lan'      => 'EUR',
		'Đức'         => 'EUR',
		'Thụy Sỹ'     => 'CHF',
		'Singapore'   => 'SGD',
		'Malaysia'    => 'MYR',
		'Hàn Quốc'    => 'KRW',
	];

	return $map[ $country ] ?? 'USD';
}

function duy_school_profile_defaults( array $school ): array {
	$name     = (string) ( $school['n'] ?? 'Trường đối tác' );
	$country  = (string) ( $school['c'] ?? '' );
	$city     = (string) ( $school['city'] ?? $country );
	$major    = (string) ( $school['major'] ?? 'ngành học trọng tâm' );
	$level    = (string) ( $school['level'] ?? 'Đại học' );
	$programs = array_values( (array) ( $school['programs'] ?? [] ) );
	$currency = duy_school_currency_label( $country );

	$program_a = $programs[0] ?? 'Business';
	$program_b = $programs[1] ?? 'Data Science';
	$program_c = $programs[2] ?? 'Academic English Pathway';

	return [
		'full_name'        => $name,
		'acronym'          => duy_school_acronym( $name ),
		'founded'          => '',
		'type'             => str_contains( strtolower( $name ), 'college' ) ? 'College công lập/tư thục' : 'Đại học nghiên cứu/ứng dụng',
		'students'         => ' sinh viên',
		'website'          => '',
		'campuses'         => [
			[ 'name' => 'Campus chính', 'location' => $city . ', ' . $country, 'focus' => implode( ', ', array_filter( [ $program_a, $program_b ] ) ) ],
			[ 'name' => 'Khu học tập/sinh viên', 'location' => $city, 'focus' => 'Thư viện, lab, career service và hoạt động sinh viên' ],
		],
		'rankings'         => [
			[ 'org' => 'Xếp hạng tổng quan', 'position' => (string) ( $school['r'] ?? '' ) ],
			[ 'org' => 'QS/THE theo ngành', 'position' => '' ],
			[ 'org' => 'Đánh giá tuyển dụng', 'position' => '' ],
		],
		'subject_rankings' => 'Nhóm ngành ' . $major . ' cần xác minh theo bảng xếp hạng và năm tuyển sinh mới nhất.',
		'key_stats'        => [
			[ 'value' => (string) ( $school['code'] ?? duy_country_code( $country ) ), 'label' => 'mã điểm đến' ],
			[ 'value' => $level, 'label' => 'bậc học chính' ],
			[ 'value' => $major, 'label' => 'ngành trọng tâm' ],
			[ 'value' => '', 'label' => 'sinh viên quốc tế' ],
		],
		'why'              => [
			[ 'icon' => 'cap', 'title' => 'Phù hợp hồ sơ theo ngành', 'desc' => $name . ' phù hợp với học sinh quan tâm ' . strtolower( $major ) . ' và cần so sánh yêu cầu đầu vào theo từng chương trình.' ],
			[ 'icon' => 'map', 'title' => 'Vị trí học tập rõ ràng', 'desc' => 'Campus tại ' . $city . ' giúp gia đình dự trù chi phí sinh hoạt, chỗ ở và nhịp di chuyển trước khi nộp hồ sơ.' ],
			[ 'icon' => 'award', 'title' => 'Cần kiểm tra học bổng sớm', 'desc' => 'Học bổng, deadline và điều kiện từng kỳ có thể thay đổi; Ban Du học Hội TESOL TP.HCM giữ nhãn cho các claim chưa xác minh.' ],
		],
		'programs_ug'      => [
			[ 'name' => $program_a, 'desc' => 'Chương trình bậc cử nhân, yêu cầu GPA/tiếng Anh theo ngành.' ],
			[ 'name' => $program_b, 'desc' => 'Lựa chọn phù hợp học sinh muốn gắn học thuật với dự án và cơ hội thực tập.' ],
		],
		'programs_pg'      => [
			[ 'name' => $program_a . ' Graduate Pathway', 'desc' => 'Lộ trình sau đại học cho hồ sơ đã có nền tảng chuyên ngành liên quan.' ],
			[ 'name' => $program_b . ' Professional Program', 'desc' => 'Phù hợp người học muốn nâng kỹ năng nghề nghiệp hoặc chuyển hướng ngành.' ],
		],
		'programs_pathway' => [
			[ 'name' => $program_c, 'desc' => 'Pathway/dự bị giúp học sinh bổ sung tiếng Anh hoặc học thuật trước khóa chính.' ],
		],
		'entry_ug'         => 'Tốt nghiệp THPT hoặc tương đương, GPA và môn nền theo yêu cầu ngành; portfolio/interview nếu chương trình yêu cầu.',
		'entry_pg'         => 'Bằng cử nhân liên quan, bảng điểm, CV, thư mục tiêu và kinh nghiệm tùy ngành.',
		'english'          => [
			[ 'test' => 'IELTS Academic', 'score' => '6.0 - 6.5 hoặc cao hơn theo ngành' ],
			[ 'test' => 'TOEFL/PTE/Duolingo', 'score' => 'Có thể được chấp nhận tùy trường và kỳ nhập học' ],
		],
		'intakes'          => 'Kỳ nhập học chính cần xác nhận theo chương trình; nên chuẩn bị hồ sơ trước 6-12 tháng.',
		'tuition'          => [
			[ 'level' => 'Đại học', 'local' => $currency . '/năm', 'vnd' => 'Quy đổi VND theo tỷ giá tại thời điểm nộp hồ sơ.' ],
			[ 'level' => 'Sau đại học', 'local' => $currency . '/năm', 'vnd' => 'Phụ thuộc ngành, số tín chỉ và học bổng.' ],
			[ 'level' => 'Pathway/English', 'local' => $currency . '/khóa', 'vnd' => 'Tính riêng bảo hiểm, tài liệu và phí ghi danh nếu có.' ],
		],
		'living_costs'     => [
			[ 'item' => 'Nhà ở', 'cost' => ' theo thành phố và loại phòng' ],
			[ 'item' => 'Ăn uống/di chuyển', 'cost' => ' theo phong cách sống' ],
			[ 'item' => 'Bảo hiểm/phí phụ', 'cost' => ' theo quy định trường/quốc gia' ],
		],
		'career_stats'     => 'Career service, internship/co-op và tỷ lệ việc làm cần đối chiếu với báo cáo mới nhất của trường.',
		'salary'           => [
			[ 'field' => $major, 'range' => 'Mức lương khởi điểm cần xác minh theo quốc gia và bang/tỉnh.' ],
			[ 'field' => $program_a, 'range' => 'Phụ thuộc vị trí, kỹ năng và chính sách làm việc sau tốt nghiệp.' ],
		],
		'partners'         => 'Danh sách đối tác tuyển dụng, lab, placement hoặc industry project cần trường xác nhận trước khi công bố.',
		'visa_note'        => 'Điều kiện visa, làm thêm và ở lại sau tốt nghiệp phụ thuộc quốc gia, bậc học và thời điểm nộp.',
	];
}

function duy_scholarship_profile_defaults( array $scholarship ): array {
	$name    = (string) ( $scholarship['n'] ?? 'Học bổng du học' );
	$country = (string) ( $scholarship['c'] ?? '' );
	$value   = (string) ( $scholarship['v'] ?? '' );
	$school  = (string) ( $scholarship['school'] ?? 'Trường đối tác' );

	return [
		'issuer'              => $school . ' / đơn vị cấp học bổng cần xác nhận.',
		'quota'               => '',
		'intake'              => 'Kỳ nhập học gần nhất cần kiểm tra theo trường.',
		'intro'               => $name . ' phù hợp với hồ sơ có thành tích học tập rõ, mục tiêu ngành nhất quán và chuẩn bị sớm trước deadline.',
		'benefits'            => [
			[ 'title' => 'Hỗ trợ học phí', 'desc' => 'Giá trị công bố: ' . $value . '. Phạm vi áp dụng cần xác nhận theo thư học bổng.' ],
			[ 'title' => 'Tăng sức cạnh tranh hồ sơ', 'desc' => 'Học bổng giúp hồ sơ nổi bật hơn khi câu chuyện học tập, ngành và thành tích được trình bày chặt chẽ.' ],
			[ 'title' => 'Tư vấn ngân sách gia đình', 'desc' => 'Ban Du học Hội TESOL TP.HCM giúp phụ huynh tính phần còn lại: học phí, sinh hoạt, bảo hiểm và khoản dự phòng.' ],
		],
		'eligibility'         => [
			'GPA/học lực theo yêu cầu từng ngành và từng kỳ.',
			'IELTS/TOEFL/PTE hoặc chứng chỉ tương đương nếu trường yêu cầu.',
			'Bài luận, CV, hoạt động hoặc portfolio thể hiện mục tiêu học tập.',
			'Nộp hồ sơ trước deadline và đáp ứng điều kiện thư mời nếu có.',
		],
		'documents'           => [
			'Bảng điểm, bằng tốt nghiệp hoặc giấy xác nhận học sinh/sinh viên.',
			'Chứng chỉ tiếng Anh và hộ chiếu còn hạn.',
			'CV, bài luận mục tiêu học tập, thư giới thiệu nếu học bổng yêu cầu.',
			'Minh chứng thành tích, hoạt động hoặc portfolio theo ngành.',
		],
		'how_to_steps'        => [
			[ 'title' => 'Đánh giá hồ sơ', 'desc' => 'Chuyên viên rà soát học lực, tiếng Anh, ngành và ngân sách để xác định độ phù hợp.' ],
			[ 'title' => 'Lập checklist học bổng', 'desc' => 'Chốt tài liệu cần chuẩn bị, lịch viết luận và deadline nộp trường.' ],
			[ 'title' => 'Hoàn thiện câu chuyện ứng tuyển', 'desc' => 'Kết nối thành tích, mục tiêu học tập và lý do chọn trường thành một hồ sơ mạch lạc.' ],
			[ 'title' => 'Nộp và theo dõi kết quả', 'desc' => 'Theo dõi phản hồi trường, bổ sung giấy tờ và cập nhật phụ huynh theo từng mốc.' ],
		],
		'applicable_programs' => [
			(string) ( $scholarship['level'] ?? 'Đại học' ),
			'Ngành/chương trình tại ' . $school . ' cần xác nhận theo kỳ.',
			'Quốc gia: ' . $country,
		],
	];
}

function duy_event_profile_defaults( array $event ): array {
	$type = (string) ( $event['type'] ?? 'Sự kiện' );
	$name = (string) ( $event['n'] ?? 'Sự kiện Ban Du học Hội TESOL TP.HCM' );
	$slug = (string) ( $event['id'] ?? '' );
	$school_ids = [ 'the-university-of-melbourne', 'the-university-of-new-south-wales', 'sydney' ];

	if ( str_contains( strtolower( $name . ' ' . $slug ), 'canada' ) ) {
		$school_ids = [ 'university-of-waterloo', 'dalhousie-university', 'sheridan-college', 'university-of-windsor' ];
	} elseif ( str_contains( strtolower( $name . ' ' . $slug ), 'new-zealand' ) || str_contains( strtolower( $name . ' ' . $slug ), 'nz' ) ) {
		$school_ids = [ 'university-of-auckland', 'university-of-otago', 'university-of-waikato' ];
	} elseif ( str_contains( strtolower( $name . ' ' . $slug ), 'turkey' ) || str_contains( strtolower( $name . ' ' . $slug ), 'tho-nhi-ky' ) ) {
		$school_ids = [ 'metu' ];
	}

	return [
		'audience' => 'Học sinh lớp 10-12, sinh viên, phụ huynh đang tìm lộ trình du học hoặc học bổng trong 6-18 tháng tới.',
		'fee'      => 'Miễn phí đăng ký trước.',
		'benefits' => [
			[ 'title' => 'Cập nhật thông tin đúng ngữ cảnh', 'desc' => 'Nắm điểm cần kiểm tra về trường, học bổng, visa và ngân sách theo từng quốc gia.' ],
			[ 'title' => 'Hỏi đáp theo hồ sơ thật', 'desc' => 'Mang bảng điểm, tiếng Anh, ngành quan tâm để chuyên viên gợi ý hướng đi sát hơn.' ],
			[ 'title' => 'Có checklist sau sự kiện', 'desc' => 'Người tham dự nhận danh sách bước tiếp theo để không bỏ lỡ deadline.' ],
		],
		'speakers' => [
			[ 'photo' => 'photo-team-office.webp', 'name' => 'Chuyên viên Ban Du học Hội TESOL TP.HCM', 'role' => 'Tư vấn lộ trình', 'org' => 'Ban Du học Hội TESOL TP.HCM', 'bio' => 'Phụ trách đọc hồ sơ ban đầu, gợi ý shortlist trường và chuyển thông tin sự kiện thành checklist hành động cho gia đình.' ],
			[ 'photo' => 'photo-student-group.webp', 'name' => 'Đại diện trường/đối tác', 'role' => $type . ' guest', 'org' => 'Đơn vị tham gia', 'bio' => 'Chia sẻ góc nhìn tuyển sinh, yêu cầu đầu vào, học bổng và các điểm phụ huynh nên hỏi trước khi nộp hồ sơ.' ],
		],
		'videos'   => [
			[ 'title' => 'Video giới thiệu bối cảnh sự kiện', 'url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE', 'desc' => 'Xem nhanh cách chuẩn bị thông tin trước khi gặp chuyên viên hoặc đại diện trường.' ],
			[ 'title' => 'Gợi ý chuẩn bị câu hỏi', 'url' => 'https://youtu.be/M7lc1UVf-VE', 'desc' => 'Checklist nhanh để buổi tư vấn tập trung vào đúng hồ sơ và mục tiêu của gia đình.' ],
		],
		'gallery'  => [ 'photo-event-workshop.webp', 'photo-student-group.webp', 'photo-team-office.webp', 'photo-campus-library.webp' ],
		'schools'  => $school_ids,
		'who'      => [
			'Gia đình chưa biết nên chọn quốc gia, ngành hoặc bậc học nào.',
			'Học sinh muốn so sánh trường, học bổng và chi phí trước khi nộp.',
			'Phụ huynh cần hiểu timeline, hồ sơ tài chính và rủi ro visa.',
		],
	];
}

function duy_news_profile_defaults( array $article ): array {
	$topic = (string) ( $article['cat'] ?? 'Tin tức' );

	return [
		'key_takeaways' => [
			'Luôn kiểm tra thông tin mới nhất từ trường hoặc cơ quan xử lý hồ sơ trước khi ra quyết định.',
			'Đặt thông tin trong bối cảnh hồ sơ riêng: học lực, ngân sách, ngành và thời điểm nhập học.',
			'Ban Du học Hội TESOL TP.HCM có thể giúp gia đình chuyển bài viết thành checklist hành động cụ thể.',
		],
		'body_blocks'    => [
			[ 'title' => 'Bối cảnh cần hiểu', 'body' => 'Nhóm chủ đề ' . $topic . ' thường thay đổi theo quốc gia, trường và kỳ nhập học. Vì vậy bài viết này chỉ là khung tham khảo để gia đình biết câu hỏi cần đặt ra.' ],
			[ 'title' => 'Điểm cần kiểm tra với hồ sơ cá nhân', 'body' => 'Học sinh nên đối chiếu yêu cầu học thuật, tiếng Anh, tài chính, deadline và mục tiêu nghề nghiệp trước khi chọn bước tiếp theo.' ],
			[ 'title' => 'Ban Du học Hội TESOL TP.HCM hỗ trợ gì', 'body' => 'Chuyên viên giúp đọc yêu cầu, lập timeline, so sánh lựa chọn và nhắc các điểm cần xác nhận trước khi nộp hồ sơ.' ],
		],
		'quote'          => 'Một bài viết tốt không thay thế tư vấn cá nhân, nhưng giúp gia đình biết nên hỏi gì trước khi bắt đầu.',
	];
}

function duy_story_profile_defaults( array $item ): array {
	$name    = (string) ( $item['name'] ?? $item['n'] ?? 'Học sinh Ban Du học Hội TESOL TP.HCM' );
	$school  = (string) ( $item['school'] ?? $item['s'] ?? 'Trường đối tác' );
	$award   = (string) ( $item['award'] ?? 'Kết quả cần xác nhận.' );
	$country = str_contains( $school, 'Canada' ) || str_contains( $school, 'Toronto' ) ? 'Canada' : ( str_contains( $school, 'Auckland' ) ? 'New Zealand' : 'Úc' );

	return [
		'major'        => ' ngành học',
		'level'        => 'Đại học/Sau đại học',
		'year'         => '2026',
		'country'      => $country,
		'story_blocks' => [
			[ 'title' => 'Điểm xuất phát', 'desc' => $name . ' bắt đầu với nhiều câu hỏi về trường, ngành, học bổng và cách kể câu chuyện hồ sơ.' ],
			[ 'title' => 'Khó khăn chính', 'desc' => 'Áp lực deadline, yêu cầu giấy tờ và cách chứng minh mục tiêu học tập khiến gia đình cần một timeline rõ.' ],
			[ 'title' => 'Ban Du học Hội TESOL TP.HCM đồng hành', 'desc' => 'Chuyên viên cùng học sinh rà soát hồ sơ, chọn trường phù hợp, chỉnh câu chuyện ứng tuyển và chuẩn bị checklist visa.' ],
			[ 'title' => 'Kết quả', 'desc' => $award . ' tại ' . $school . '. Kết quả thật cần đối chiếu hồ sơ trước khi công bố rộng rãi.' ],
		],
		'advice'       => 'Chuẩn bị sớm giúp mình có thời gian hiểu lựa chọn của bản thân, thay vì chỉ chạy theo deadline.',
		'result_stats' => [
			[ 'value' => '1-1', 'label' => 'lộ trình đồng hành' ],
			[ 'value' => '', 'label' => 'học bổng/offer xác nhận' ],
			[ 'value' => '4', 'label' => 'mốc hồ sơ chính' ],
		],
	];
}

function duy_about_defaults(): array {
	return [
		'strengths' => [
			[ 'icon' => 'target', 'title' => 'Định hướng theo hồ sơ', 'desc' => 'Không bắt đầu bằng ranking, mà bắt đầu bằng mục tiêu học tập, năng lực, ngân sách và thời điểm nhập học.' ],
			[ 'icon' => 'shield', 'title' => 'Minh bạch từng mốc', 'desc' => 'Gia đình biết việc nào đang làm, giấy tờ nào còn thiếu và claim nào cần xác nhận trước khi nộp.' ],
			[ 'icon' => 'heart', 'title' => 'Đồng hành cùng phụ huynh', 'desc' => 'Phụ huynh được giải thích rõ về chi phí, rủi ro, deadline và vai trò của từng quyết định.' ],
		],
		'services'  => [
			[ 'icon' => 'target', 'title' => 'Tư vấn', 'desc' => 'Định hướng quốc gia, ngành, bậc học, ngân sách, shortlist trường và học bổng.' ],
			[ 'icon' => 'route', 'title' => 'Hỗ trợ học sinh', 'desc' => 'Hồ sơ trường, bài luận, visa, pre-departure và cập nhật tiến độ theo từng giai đoạn.' ],
		],
		'process'   => [
			[ 'title' => 'Khám phá hồ sơ', 'desc' => 'Lắng nghe mục tiêu, năng lực học thuật, ngân sách và mốc thời gian.' ],
			[ 'title' => 'Thiết kế lộ trình', 'desc' => 'So sánh quốc gia, trường, ngành, học bổng và rủi ro cần kiểm tra.' ],
			[ 'title' => 'Hoàn thiện hồ sơ', 'desc' => 'Chuẩn hóa giấy tờ, bài luận, form trường, học bổng và visa.' ],
			[ 'title' => 'Chuẩn bị lên đường', 'desc' => 'Checklist chỗ ở, vé bay, hành lý, nhập học và ngày đầu tại campus.' ],
		],
		'team'      => [
			[ 'name' => 'Chuyên viên TP.HCM', 'office' => 'BV Bank', 'role' => 'Tư vấn lộ trình', 'exp' => ' năm kinh nghiệm' ],
			[ 'name' => 'Chuyên viên Đà Nẵng', 'office' => 'Hilton', 'role' => 'Hỗ trợ hồ sơ', 'exp' => ' năm kinh nghiệm' ],
			[ 'name' => 'Chuyên viên Buôn Ma Thuột', 'office' => 'Buôn Ma Thuột', 'role' => 'Chăm sóc phụ huynh', 'exp' => ' năm kinh nghiệm' ],
		],
		'partners'  => [
			'Đối tác trường và tổ chức giáo dục cần xác nhận trước khi công bố logo.',
			'Thành tựu/hồ sơ tiêu biểu cần user cung cấp bằng chứng trước khi public.',
		],
	];
}

function duy_service_packages(): array {
	return [
		[ 'icon' => 'target', 'label' => 'Định hướng', 'title' => 'Gói định hướng lộ trình', 'for' => 'Học sinh chưa rõ quốc gia/ngành/trường', 'desc' => 'Phân tích hồ sơ, mục tiêu nghề nghiệp, ngân sách và timeline để tạo bản đồ lựa chọn ban đầu.', 'includes' => [ 'Buổi tư vấn 1-1', 'So sánh 3-5 hướng đi', 'Checklist bước tiếp theo' ] ],
		[ 'icon' => 'cap', 'label' => 'Chọn trường & học bổng', 'title' => 'Gói chọn trường & học bổng', 'for' => 'Hồ sơ cần shortlist và chiến lược ứng tuyển', 'desc' => 'Rà soát điều kiện, deadline, học bổng và cách kể câu chuyện hồ sơ cho từng trường.', 'includes' => [ 'Shortlist trường', 'Bảng học bổng cần nộp', 'Timeline bài luận/hồ sơ' ] ],
		[ 'icon' => 'book', 'label' => 'Hồ sơ trường', 'title' => 'Gói hồ sơ trường', 'for' => 'Học sinh đã chốt trường/ngành', 'desc' => 'Chuẩn hóa giấy tờ, form trường, bài luận, thư giới thiệu và theo dõi phản hồi.', 'includes' => [ 'Checklist giấy tờ', 'Rà soát đơn', 'Theo dõi offer' ] ],
		[ 'icon' => 'shield', 'label' => 'Visa & trước bay', 'title' => 'Gói visa & trước bay', 'for' => 'Hồ sơ đã có offer/CoE/CAS/LOA', 'desc' => 'Lập checklist visa, nhắc mốc nộp, chuẩn bị pre-departure và hỗ trợ ngày lên đường.', 'includes' => [ 'Checklist visa', 'Pre-departure', 'Hướng dẫn nhập học' ] ],
	];
}

function duy_download_guide_outline(): array {
	return [
		[ 'title' => 'Phần 1: Chọn hướng đi', 'desc' => 'Cách so sánh quốc gia, ngành, bậc học và mục tiêu nghề nghiệp.' ],
		[ 'title' => 'Phần 2: Ngân sách', 'desc' => 'Khung học phí, sinh hoạt, bảo hiểm, khoản dự phòng và học bổng.' ],
		[ 'title' => 'Phần 3: Hồ sơ', 'desc' => 'Checklist bảng điểm, tiếng Anh, bài luận, tài chính và visa.' ],
		[ 'title' => 'Phần 4: Trước khi lên đường', 'desc' => 'Chỗ ở, vé bay, hành lý, SIM, ngân hàng và ngày đầu tại trường.' ],
	];
}

function duy_faq_groups(): array {
	return [
		'Lộ trình' => [
			[ 'q' => 'Nên bắt đầu chuẩn bị du học trước bao lâu?', 'a' => 'Nên bắt đầu trước 6-12 tháng để có thời gian chọn trường, học bổng, hồ sơ và visa. Một số học bổng cần chuẩn bị sớm hơn.' ],
			[ 'q' => 'Chưa biết chọn nước nào thì bắt đầu từ đâu?', 'a' => 'Hãy bắt đầu bằng mục tiêu ngành, ngân sách, khả năng tiếng Anh và mong muốn sau tốt nghiệp; sau đó mới so sánh quốc gia.' ],
		],
		'Chi phí'  => [
			[ 'q' => 'Chi phí du học gồm những khoản nào?', 'a' => 'Học phí, sinh hoạt, bảo hiểm, phí hồ sơ, visa, vé bay và khoản dự phòng. Mỗi quốc gia cần bảng ngân sách riêng.' ],
			[ 'q' => 'Có thể vừa học vừa làm để tự chi trả không?', 'a' => 'Làm thêm chỉ nên là nguồn hỗ trợ, không nên là nguồn tài chính chính vì quy định và thời lượng học thay đổi theo quốc gia.' ],
		],
		'Visa'    => [
			[ 'q' => 'Visa cần chứng minh tài chính thế nào?', 'a' => 'Yêu cầu khác nhau theo quốc gia và thời điểm nộp; gia đình cần kiểm tra checklist mới nhất trước khi chuẩn bị giấy tờ.' ],
			[ 'q' => 'Nếu visa trễ so với ngày nhập học thì sao?', 'a' => 'Có thể cần xin deferral hoặc cập nhật kế hoạch nhập học với trường; quyết định phụ thuộc trường và tình trạng hồ sơ.' ],
		],
		'Học bổng' => [
			[ 'q' => 'Học bổng xét những yếu tố nào?', 'a' => 'Thường xét học lực, tiếng Anh, ngành, bài luận, hoạt động và mức phù hợp với trường. Điều kiện cụ thể cần xác nhận theo từng học bổng.' ],
			[ 'q' => 'Có nên nộp nhiều học bổng cùng lúc không?', 'a' => 'Có thể, nhưng cần ưu tiên học bổng phù hợp nhất và kiểm soát deadline để chất lượng hồ sơ không bị dàn trải.' ],
		],
		'Dịch vụ' => [
			[ 'q' => 'Ban Du học Hội TESOL TP.HCM hỗ trợ những bước nào?', 'a' => 'Định hướng, chọn trường, học bổng, hồ sơ trường, visa, pre-departure và cập nhật tiến độ cho gia đình.' ],
			[ 'q' => 'Tư vấn ban đầu có mất phí không?', 'a' => 'Liên hệ hotline 0906.510.747 hoặc để lại thông tin để được báo rõ chính sách phí theo từng gói dịch vụ. Ban Du học Hội TESOL TP.HCM luôn thông báo chi phí trước khi bắt đầu hồ sơ.' ],
		],
	];
}

/**
 * FAQ mặc định theo quốc gia (và bậc THPT dùng chung mọi nước) cho trang
 * /quoc-gia/{c}/ và /quoc-gia/{c}/{level}/ — nguồn cho accordion + FAQPage schema.
 * Nội dung theo brand voice, không số liệu chưa xác nhận (docs/seo-geo-plan.md §4.1).
 *
 * @return array<string,array<int,array{q:string,a:string}>>
 */
function duy_faq_defaults(): array {
	return [
		'my'          => [
			[ 'q' => 'Du học Mỹ cần chuẩn bị hồ sơ gì?', 'a' => 'Học bạ hoặc bảng điểm dịch công chứng, chứng chỉ tiếng Anh (TOEFL, IELTS, Duolingo; bậc THPT nhiều trường nhận ELTiS hoặc TOEFL Junior), thư giới thiệu, bài luận hoặc phỏng vấn tuỳ trường, và giấy tờ chứng minh tài chính. Sau khi có thư mời và I-20, học sinh nộp visa F-1 (DS-160, phí SEVIS, phỏng vấn tại Lãnh sự quán). Ban Du học Hội TESOL TP.HCM rà từng mục trước khi nộp.' ],
			[ 'q' => 'Du học sinh Mỹ có được làm thêm không?', 'a' => 'Sinh viên diện F-1 được làm việc trong khuôn viên trường tối đa 20 giờ mỗi tuần trong kỳ học. Làm việc ngoài trường cần diện CPT hoặc OPT do trường phê duyệt. Học sinh THPT không đi làm thêm.' ],
			[ 'q' => 'Nên chọn cao đẳng cộng đồng hay đại học 4 năm?', 'a' => 'Cao đẳng cộng đồng theo lộ trình 2+2 giúp giảm chi phí hai năm đầu rồi chuyển tiếp lên đại học. Đại học 4 năm phù hợp hồ sơ mạnh và muốn ổn định ngay từ đầu. Ban Du học Hội TESOL TP.HCM so sánh theo ngân sách, điểm số và ngành mục tiêu trước khi chốt trường.' ],
			[ 'q' => 'Học bổng du học Mỹ có khó xin không?', 'a' => 'Nhiều trường cấp học bổng theo thành tích (GPA, tiếng Anh, hoạt động ngoại khoá) với mức phổ biến là một phần học phí; học bổng toàn phần cạnh tranh cao. Nộp sớm, hồ sơ đầy đủ và chọn đúng nhóm trường sẽ tăng cơ hội.' ],
		],
		'uc'          => [
			[ 'q' => 'Visa du học Úc là loại nào và cần gì?', 'a' => 'Visa Student subclass 500. Hồ sơ gồm thư xác nhận nhập học (CoE), yêu cầu Genuine Student, chứng minh tài chính, bảo hiểm y tế OSHC và tiếng Anh theo yêu cầu của trường và visa.' ],
			[ 'q' => 'Du học sinh Úc được làm thêm bao nhiêu giờ?', 'a' => 'Trong kỳ học tối đa 48 giờ mỗi hai tuần; kỳ nghỉ chính thức không giới hạn. Quy định có thể thay đổi, Ban Du học Hội TESOL TP.HCM cập nhật tại thời điểm nộp hồ sơ.' ],
			[ 'q' => 'Sau tốt nghiệp có được ở lại làm việc không?', 'a' => 'Visa Temporary Graduate (subclass 485) cho phép ở lại làm việc sau tốt nghiệp; thời hạn tuỳ bậc học và quy định hiện hành, cần kiểm tra trước khi chọn ngành.' ],
			[ 'q' => 'Nên bắt đầu chuẩn bị trước kỳ nhập học bao lâu?', 'a' => 'Khoảng 9 đến 12 tháng cho kỳ nhập học chính tháng 2 và tháng 7, đủ để thi tiếng Anh, xin thư mời, chuẩn bị tài chính và visa.' ],
		],
		'canada'      => [
			[ 'q' => 'Study permit Canada cần điều kiện gì?', 'a' => 'Thư mời (LOA) từ trường trong danh sách DLI, thư xác nhận PAL/TAL của tỉnh bang (áp dụng cho đa số bậc học từ 2024), chứng minh tài chính theo mức quy định, khám sức khoẻ, lý lịch tư pháp và tiếng Anh hoặc tiếng Pháp theo yêu cầu trường.' ],
			[ 'q' => 'Học THPT tại Canada theo hệ nào?', 'a' => 'Trường công lập theo học khu (school district hoặc school board) hoặc trường tư thục, nội trú. Học sinh quốc tế thường vào lớp 9 đến 12, ở homestay hoặc ký túc xá, tốt nghiệp nhận bằng THPT của tỉnh bang (ví dụ OSSD tại Ontario).' ],
			[ 'q' => 'Có được ở lại làm việc sau tốt nghiệp không?', 'a' => 'Chương trình PGWP cho phép ở lại làm việc sau khi hoàn thành chương trình đủ điều kiện; điều kiện thay đổi theo chính sách từng năm nên cần kiểm tra trước khi chọn trường và ngành.' ],
			[ 'q' => 'Chi phí du học Canada gồm những khoản nào?', 'a' => 'Học phí, phí nộp hồ sơ, bảo hiểm y tế, chỗ ở (homestay hoặc ký túc xá), sinh hoạt phí và vé máy bay. Mức cụ thể tuỳ tỉnh bang và trường, xem chi phí tham khảo trên từng trang trường.' ],
		],
		'new-zealand' => [
			[ 'q' => 'Du học New Zealand phù hợp bậc nào?', 'a' => 'THPT (lớp 9 đến 13 theo hệ NCEA), cao đẳng, đại học công lập và các chương trình tiếng Anh. Môi trường an toàn, lớp học nhỏ, phù hợp học sinh đi sớm.' ],
			[ 'q' => 'Visa du học New Zealand cần gì?', 'a' => 'Fee Paying Student Visa: thư mời và xác nhận đã đóng học phí, chứng minh tài chính sinh hoạt, bảo hiểm, khám sức khoẻ; học sinh dưới 18 tuổi cần homestay hoặc giám hộ do trường sắp xếp.' ],
			[ 'q' => 'Du học sinh New Zealand được làm thêm không?', 'a' => 'Sinh viên đủ điều kiện được làm tối đa 20 giờ mỗi tuần trong kỳ học và toàn thời gian trong kỳ nghỉ, tuỳ điều kiện ghi trên visa.' ],
			[ 'q' => 'Kỳ nhập học New Zealand vào tháng mấy?', 'a' => 'Đại học thường tháng 2 và tháng 7; THPT nhập học đầu năm (tháng 1 đến 2) và có thể giữa năm tuỳ trường.' ],
		],
		'anh'         => [
			[ 'q' => 'Visa du học Anh là loại nào và cần gì?', 'a' => 'Student visa (trước đây gọi là Tier 4) cho khoá học tại cơ sở có giấy phép bảo trợ: cần CAS do trường cấp, chứng minh tài chính học phí và sinh hoạt theo mức quy định, tiếng Anh theo yêu cầu của trường hoặc IELTS for UKVI, và khám lao tại cơ sở được chỉ định. Học sinh dưới 18 tuổi học trường nội trú dùng Child Student visa.' ],
			[ 'q' => 'Du học sinh Anh có được làm thêm không?', 'a' => 'Sinh viên bậc đại học trở lên tại trường đủ điều kiện thường được làm tối đa 20 giờ mỗi tuần trong kỳ học và toàn thời gian trong kỳ nghỉ; khoá dưới bậc đại học bị giới hạn thấp hơn hoặc không được làm. Điều kiện ghi rõ trên visa, Ban Du học Hội TESOL TP.HCM kiểm tra theo từng khoá.' ],
			[ 'q' => 'Sau tốt nghiệp có được ở lại Anh làm việc không?', 'a' => 'Graduate visa cho phép ở lại làm việc sau khi hoàn thành bậc đại học trở lên tại Anh. Thời hạn và điều kiện thay đổi theo chính sách từng năm nên cần kiểm tra trước khi chọn khoá học.' ],
			[ 'q' => 'Học THPT tại Anh theo hệ nào?', 'a' => 'GCSE (tương đương lớp 10 đến 11) rồi A-Level hoặc IB (lớp 12 đến 13) tại trường nội trú hoặc college. A-Level là nền tảng xét tuyển đại học Anh và được nhiều nước công nhận; học sinh Việt Nam thường vào từ năm GCSE hoặc thẳng A-Level tuỳ tuổi và học lực.' ],
		],
		'ha-lan'      => [
			[ 'q' => 'Hà Lan có nhiều chương trình dạy bằng tiếng Anh không?', 'a' => 'Có. Hà Lan thuộc nhóm nước không nói tiếng Anh có nhiều chương trình cử nhân và thạc sĩ giảng dạy bằng tiếng Anh nhất châu Âu, ở cả đại học nghiên cứu (WO) và đại học khoa học ứng dụng (HBO). Không cần biết tiếng Hà Lan để nhập học các chương trình này.' ],
			[ 'q' => 'Giấy phép cư trú du học Hà Lan xin thế nào?', 'a' => 'Trường được công nhận thay mặt sinh viên nộp hồ sơ xin MVV và giấy phép cư trú (VVR) lên Sở Di trú IND. Sinh viên cần hộ chiếu, chứng minh tài chính sinh hoạt theo mức IND quy định, và đóng học phí hoặc khoản đặt cọc theo yêu cầu của trường trước khi hồ sơ được nộp.' ],
			[ 'q' => 'Du học sinh Hà Lan được làm thêm không?', 'a' => 'Sinh viên ngoài EU được làm thêm với giới hạn giờ mỗi tuần hoặc làm toàn thời gian trong các tháng hè; nhà tuyển dụng phải xin giấy phép lao động (TWV) cho sinh viên. Nên xem làm thêm là hỗ trợ, không phải nguồn tài chính chính.' ],
			[ 'q' => 'Sau tốt nghiệp có được ở lại tìm việc không?', 'a' => 'Có "orientation year" (zoekjaar) cho phép sinh viên tốt nghiệp ở lại tìm việc tối đa một năm và làm việc không cần giấy phép lao động trong thời gian đó. Điều kiện nộp cần kiểm tra tại thời điểm tốt nghiệp.' ],
		],
		'singapore'   => [
			[ 'q' => 'Visa du học Singapore là gì?', 'a' => 'Student\'s Pass do Cục Xuất nhập cảnh ICA cấp sau khi có thư nhập học từ trường được phép tuyển sinh viên quốc tế; trường thường hỗ trợ nộp trực tuyến qua hệ thống SOLAR. Hồ sơ gồm hộ chiếu, ảnh, giấy tờ học tập và chứng minh tài chính.' ],
			[ 'q' => 'Hệ thống trường tại Singapore gồm những gì?', 'a' => 'Đại học công lập (NUS, NTU, SMU và các trường khác), trường tư thục liên kết cấp bằng của đại học Anh, Úc, Mỹ (ví dụ PSB Academy, Kaplan), các polytechnic và chương trình tiếng Anh, dự bị đại học. Bằng liên kết cần kiểm tra đơn vị cấp và sự công nhận.' ],
			[ 'q' => 'Du học sinh Singapore có được làm thêm không?', 'a' => 'Chỉ sinh viên của một số trường được phê duyệt mới được làm thêm với giới hạn giờ trong kỳ học; nhiều trường tư không cho phép. Gia đình cần xác nhận với trường trước khi tính vào ngân sách.' ],
			[ 'q' => 'Chi phí du học Singapore gồm những khoản nào?', 'a' => 'Học phí theo trường và bằng cấp, sinh hoạt phí thuộc mức cao trong khu vực (chỗ ở là khoản lớn nhất), bảo hiểm, phí Student\'s Pass và vé máy bay. Lợi thế là gần Việt Nam, nhiều chương trình có thời gian học ngắn nên tổng chi phí có thể thấp hơn Anh, Úc.' ],
		],
		'malaysia'    => [
			[ 'q' => 'Vì sao nên cân nhắc du học Malaysia?', 'a' => 'Chi phí học và sinh hoạt thấp hơn Anh, Úc, môi trường học bằng tiếng Anh, có campus chi nhánh của đại học nước ngoài và các chương trình liên kết cấp bằng Anh, Úc (ví dụ APU, Taylor\'s, Sunway). Gần Việt Nam nên đi lại thuận tiện.' ],
			[ 'q' => 'Visa du học Malaysia làm thế nào?', 'a' => 'Student Pass được xin qua hệ thống EMGS (Education Malaysia Global Services) do trường khởi tạo sau khi có thư nhập học. Sinh viên cần hộ chiếu còn hạn, ảnh, khám sức khoẻ và bảo hiểm; thời gian xử lý thường vài tuần nên nộp sớm trước kỳ nhập học.' ],
			[ 'q' => 'Du học sinh Malaysia có được làm thêm không?', 'a' => 'Chỉ được làm thêm trong kỳ nghỉ dài từ 7 ngày trở lên, tối đa 20 giờ mỗi tuần ở một số ngành nghề nhất định và phải có phép. Vì vậy không nên tính thu nhập làm thêm vào ngân sách du học.' ],
			[ 'q' => 'Chương trình 3+0 hoặc 2+1 tại Malaysia là gì?', 'a' => 'Học toàn bộ (3+0) hoặc một phần (2+1) chương trình của đại học Anh, Úc ngay tại Malaysia rồi nhận bằng của trường đó. Cách này giúp giảm chi phí đáng kể; cần kiểm tra bằng cấp được công nhận tại nơi dự định làm việc.' ],
		],
		'thuy-sy'     => [
			[ 'q' => 'Du học Thụy Sỹ mạnh về ngành gì?', 'a' => 'Quản trị khách sạn, du lịch và ẩm thực với các trường như BHMS, HTMI theo mô hình học kết hợp thực tập; ngoài ra là các đại học công lập mạnh về kỹ thuật, khoa học tự nhiên và tài chính. Ban Du học Hội TESOL TP.HCM tập trung nhóm trường quản trị khách sạn dạy bằng tiếng Anh.' ],
			[ 'q' => 'Chương trình có thực tập hưởng lương không?', 'a' => 'Nhiều trường quản trị khách sạn xếp kỳ thực tập có lương tại Thụy Sỹ hoặc nước khác xen kẽ giữa các kỳ học, giúp bù một phần chi phí và tích luỹ kinh nghiệm. Mức lương và điều kiện thực tập theo trường và quy định hiện hành.' ],
			[ 'q' => 'Visa du học Thụy Sỹ cần gì?', 'a' => 'Visa loại D cho khoá học dài hạn: thư nhập học, chứng minh tài chính, kế hoạch học tập, chứng chỉ tiếng Anh cho chương trình quốc tế, bảo hiểm y tế; sau nhập cảnh đăng ký giấy phép cư trú tại bang nơi học. Thời gian xét duyệt có thể vài tháng nên cần nộp sớm.' ],
			[ 'q' => 'Chi phí du học Thụy Sỹ có cao không?', 'a' => 'Sinh hoạt phí thuộc nhóm cao nhất thế giới; học phí trường tư ngành khách sạn cao nhưng ở nhiều trường đã gồm ăn ở tại campus, và kỳ thực tập hưởng lương giúp cân đối. Gia đình nên lập bảng ngân sách theo từng năm học cùng Ban Du học Hội TESOL TP.HCM.' ],
		],
		'duc'         => [
			[ 'q' => 'Học đại học Đức có miễn học phí không?', 'a' => 'Đa số đại học công lập không thu học phí hoặc chỉ thu phí học kỳ (semester contribution) khá thấp; một số bang và chương trình thạc sĩ quốc tế có thu học phí. Cần kiểm tra theo từng trường và bang trước khi nộp.' ],
			[ 'q' => 'Tốt nghiệp THPT Việt Nam có vào thẳng đại học Đức không?', 'a' => 'Thường phải qua dự bị đại học (Studienkolleg) hoặc hoàn thành một phần chương trình đại học tại Việt Nam tuỳ hồ sơ. Chương trình tiếng Đức cần B1 để vào Studienkolleg và TestDaF/DSH để vào đại học; bậc thạc sĩ có nhiều chương trình dạy bằng tiếng Anh hơn.' ],
			[ 'q' => 'Tài khoản phong toả khi du học Đức là gì?', 'a' => 'Sinh viên chứng minh tài chính bằng tài khoản phong toả (Sperrkonto) với số tiền tối thiểu do Đức quy định cho một năm, mỗi tháng được rút một phần để sinh hoạt. Mức tiền thay đổi theo năm, Ban Du học Hội TESOL TP.HCM cập nhật tại thời điểm nộp.' ],
			[ 'q' => 'Có được làm thêm và ở lại Đức sau tốt nghiệp không?', 'a' => 'Sinh viên được làm thêm theo số ngày hoặc giờ luật định mỗi năm. Sau tốt nghiệp có giấy phép cư trú để tìm việc phù hợp chuyên ngành trong thời hạn quy định; điều kiện cần kiểm tra khi nộp hồ sơ.' ],
		],
		'han-quoc'    => [
			[ 'q' => 'Du học Hàn Quốc thường theo lộ trình nào?', 'a' => 'Phổ biến là khoá tiếng Hàn (visa D-4) tại trung tâm ngôn ngữ của đại học, đạt TOPIK theo yêu cầu rồi chuyển lên đại học hoặc thạc sĩ (visa D-2). Một số chương trình dạy bằng tiếng Anh nhận IELTS hoặc TOEFL thay cho TOPIK.' ],
			[ 'q' => 'Visa du học Hàn Quốc cần gì?', 'a' => 'Giấy nhập học của trường, chứng minh tài chính (số dư sổ tiết kiệm theo mức quy định), học bạ hoặc bằng cấp, lý lịch tư pháp, khám sức khoẻ; hồ sơ nộp tại Đại sứ quán hoặc Lãnh sự quán Hàn Quốc. Ban Du học Hội TESOL TP.HCM rà giấy tờ theo checklist mới nhất.' ],
			[ 'q' => 'Du học sinh Hàn Quốc có được làm thêm không?', 'a' => 'Sinh viên D-2 và D-4 được làm thêm sau khi có xác nhận của trường và giấy phép của Cục Xuất nhập cảnh, giới hạn giờ tuỳ bậc học và trình độ TOPIK. Làm thêm không phép ảnh hưởng đến việc gia hạn visa.' ],
			[ 'q' => 'Học bổng du học Hàn Quốc có không?', 'a' => 'Học bổng chính phủ (GKS) cạnh tranh cao; nhiều đại học cấp học bổng theo TOPIK và GPA, giảm một phần đến toàn bộ học phí. Nộp sớm và giữ điểm tốt là điều kiện quan trọng để duy trì học bổng các kỳ sau.' ],
		],
		'tho-nhi-ky'  => [
			[ 'q' => 'Vì sao cân nhắc du học Thổ Nhĩ Kỳ?', 'a' => 'Học phí và sinh hoạt phí thấp hơn nhiều nước châu Âu, nhiều đại học có chương trình giảng dạy bằng tiếng Anh, vị trí giao thoa Á Âu. Bằng cấp cần kiểm tra sự công nhận tại nơi dự định làm việc sau này.' ],
			[ 'q' => 'Visa du học Thổ Nhĩ Kỳ làm thế nào?', 'a' => 'Xin visa du học tại Đại sứ quán với thư chấp nhận của trường, chứng minh tài chính và bảo hiểm; sau khi nhập cảnh xin giấy phép cư trú (ikamet) trong thời hạn quy định. Trường thường hướng dẫn các bước sau nhập cảnh.' ],
			[ 'q' => 'Học bổng Türkiye Bursları là gì?', 'a' => 'Học bổng chính phủ Thổ Nhĩ Kỳ dành cho sinh viên quốc tế, có thể gồm học phí, sinh hoạt phí, chỗ ở và vé máy bay tuỳ diện. Mức cạnh tranh cao, nộp trực tuyến theo đợt hàng năm; Ban Du học Hội TESOL TP.HCM hỗ trợ chuẩn bị hồ sơ và bài luận.' ],
			[ 'q' => 'Có cần biết tiếng Thổ Nhĩ Kỳ không?', 'a' => 'Chương trình dạy bằng tiếng Anh không bắt buộc, nhưng nhiều trường yêu cầu học tiếng Thổ cơ bản trong năm đầu để sinh hoạt. Chương trình dạy bằng tiếng Thổ cần chứng chỉ TÖMER.' ],
		],
		'philippines' => [
			[ 'q' => 'Du học Philippines phù hợp với ai?', 'a' => 'Học sinh, sinh viên và người đi làm muốn nâng tiếng Anh nhanh với lớp 1:1 chi phí thấp tại các trường ESL ở Cebu, Baguio, Clark; cũng có đại học dạy bằng tiếng Anh ở một số ngành. Phù hợp làm bước đệm trước khi du học các nước nói tiếng Anh.' ],
			[ 'q' => 'Học tiếng Anh ở Philippines cần visa gì?', 'a' => 'Khoá ngắn hạn thường nhập cảnh diện du lịch rồi trường hỗ trợ xin Special Study Permit (SSP) và gia hạn lưu trú; khoá dài hạn hoặc đại học cần visa sinh viên 9(f). Trường ESL thường lo toàn bộ thủ tục này.' ],
			[ 'q' => 'Chương trình ESL tại Philippines học như thế nào?', 'a' => 'Lịch học dày, thường 6 đến 10 giờ mỗi ngày gồm lớp 1:1 và lớp nhóm, ở ký túc xá trong campus bao ăn. Nhiều trường có khoá IELTS, TOEIC bảo đảm đầu ra hoặc khoá tiếng Anh học thuật để chuẩn bị du học.' ],
			[ 'q' => 'Chi phí và độ an toàn khi học ở Philippines?', 'a' => 'Chi phí trọn gói (học, ở, ăn) thấp so với Anh, Úc, Mỹ. Nên chọn trường có giấy phép TESDA hoặc CHED, campus khép kín, có quản lý sinh viên nói tiếng Việt; Ban Du học Hội TESOL TP.HCM chỉ giới thiệu trường đã kiểm tra.' ],
		],
		'level:thpt'  => [
			[ 'q' => 'Du học THPT từ lớp mấy là phù hợp?', 'a' => 'Thường từ lớp 9 đến 10 để đủ thời gian thích nghi và hoàn thành chương trình tốt nghiệp bản địa, thuận lợi khi xét tuyển đại học. Vào lớp 11 vẫn khả thi nếu học lực tốt.' ],
			[ 'q' => 'Học sinh dưới 18 tuổi ở với ai?', 'a' => 'Homestay do trường hoặc tổ chức đối tác kiểm định, ký túc xá nội trú, hoặc người giám hộ hợp pháp. Nhiều nước yêu cầu giấy tờ giám hộ khi xin visa.' ],
			[ 'q' => 'Cần tiếng Anh ở mức nào?', 'a' => 'Tuỳ trường: THPT Mỹ thường dùng ELTiS, Duolingo hoặc TOEFL Junior; Úc, Canada, New Zealand có thể nhận IELTS hoặc bài kiểm tra riêng, kèm chương trình ESL hỗ trợ nếu chưa đạt.' ],
			[ 'q' => 'Phụ huynh cần chuẩn bị tài chính ra sao?', 'a' => 'Chứng minh khả năng chi trả học phí và sinh hoạt phí cho ít nhất năm đầu (sổ tiết kiệm, thu nhập) với giấy tờ rõ nguồn gốc. Ban Du học Hội TESOL TP.HCM hướng dẫn chuẩn bị theo yêu cầu từng nước.' ],
		],
		'level:cao-dang' => [
			[ 'q' => 'Cao đẳng khác đại học ở điểm nào?', 'a' => 'Chương trình 1 đến 3 năm thiên về thực hành và ngành nghề cụ thể, học phí và điều kiện đầu vào thường mềm hơn đại học. Nhiều nước như Canada, Mỹ, Úc có lộ trình chuyển tiếp từ cao đẳng lên đại học.' ],
			[ 'q' => 'Bằng cao đẳng có chuyển tiếp lên đại học được không?', 'a' => 'Có, qua thoả thuận chuyển tiếp (articulation, lộ trình 2+2): tín chỉ được công nhận để vào năm 3 đại học. Cần chọn trường và ngành có thoả thuận chuyển tiếp ngay từ khi nộp hồ sơ.' ],
			[ 'q' => 'Điều kiện đầu vào cao đẳng là gì?', 'a' => 'Tốt nghiệp THPT với điểm trung bình theo yêu cầu trường, tiếng Anh IELTS, TOEFL hoặc Duolingo ở mức trường quy định (thường thấp hơn đại học); một số ngành cần môn nền tảng hoặc portfolio.' ],
			[ 'q' => 'Học cao đẳng có cơ hội việc làm hoặc ở lại không?', 'a' => 'Tuỳ nước và độ dài chương trình: co-op tại Canada, CPT/OPT tại Mỹ, visa sau tốt nghiệp tại Canada, Úc. Điều kiện thay đổi theo chính sách nên cần kiểm tra trước khi chọn chương trình.' ],
		],
		'level:dai-hoc' => [
			[ 'q' => 'Hồ sơ xin học đại học cần những gì?', 'a' => 'Học bạ THPT (bảng điểm 3 năm), chứng chỉ tiếng Anh, bài luận và thư giới thiệu (Mỹ), SAT/ACT tuỳ trường, chứng minh tài chính. Nếu học lực hoặc tiếng Anh chưa đạt, nhiều nước có chương trình dự bị (foundation) hoặc pathway.' ],
			[ 'q' => 'Nên bắt đầu chuẩn bị hồ sơ đại học khi nào?', 'a' => 'Trước kỳ nhập học khoảng 12 tháng: thi tiếng Anh, chọn 5 đến 8 trường ở các nhóm cạnh tranh khác nhau, nộp sớm để có học bổng. Mỹ thường có deadline từ tháng 11 đến tháng 1; Úc, Canada nhận theo từng đợt trong năm.' ],
			[ 'q' => 'Học bổng đại học xét những yếu tố nào?', 'a' => 'GPA, tiếng Anh, hoạt động ngoại khoá, bài luận và độ phù hợp với ngành và trường. Nhiều trường tự động xét học bổng theo thành tích khi nộp hồ sơ, một số học bổng lớn cần đơn riêng và deadline sớm hơn.' ],
			[ 'q' => 'Học xong đại học có được ở lại làm việc không?', 'a' => 'Tuỳ nước: Mỹ có OPT (ngành STEM được dài hơn), Canada có PGWP, Úc có visa 485, Anh có Graduate visa, New Zealand có post-study work visa. Quy định thay đổi hàng năm, Ban Du học Hội TESOL TP.HCM kiểm tra tại thời điểm nộp hồ sơ.' ],
		],
		'level:sau-dai-hoc' => [
			[ 'q' => 'Học thạc sĩ cần điều kiện gì?', 'a' => 'Bằng cử nhân ngành liên quan với GPA theo yêu cầu, tiếng Anh (thường IELTS 6.5 trở lên), CV, thư giới thiệu, thư trình bày mục tiêu học tập; một số ngành cần GMAT, GRE hoặc kinh nghiệm làm việc.' ],
			[ 'q' => 'Thạc sĩ coursework khác thạc sĩ research thế nào?', 'a' => 'Coursework học môn và làm đồ án, kéo dài 1 đến 2 năm, phù hợp định hướng đi làm. Research làm luận văn với giáo sư hướng dẫn, cần đề cương nghiên cứu và thư đồng ý hướng dẫn, thường đi kèm học bổng hoặc tài trợ nghiên cứu.' ],
			[ 'q' => 'Có học bổng thạc sĩ không?', 'a' => 'Có học bổng của trường theo thành tích, học bổng chính phủ (Chevening, Australia Awards, Erasmus Mundus, DAAD, GKS) và tài trợ nghiên cứu. Hầu hết yêu cầu nộp sớm nhiều tháng và hồ sơ học thuật mạnh.' ],
			[ 'q' => 'Sau thạc sĩ có ở lại làm việc được không?', 'a' => 'Visa sau tốt nghiệp ở nhiều nước dài hơn cho bậc thạc sĩ so với cử nhân, ngành STEM có ưu thế. Cần chọn chương trình đủ điều kiện ngay từ đầu vì không phải khoá nào cũng được tính.' ],
		],
		'level:anh-ngu' => [
			[ 'q' => 'Khoá tiếng Anh ở nước ngoài dành cho ai?', 'a' => 'Học sinh cần nâng tiếng Anh trước khoá chính (pathway, EAP), người đi làm muốn khoá ngắn từ 4 đến 24 tuần, hoặc gia đình muốn con trải nghiệm môi trường quốc tế trước khi du học dài hạn.' ],
			[ 'q' => 'Học tiếng Anh ở nước ngoài cần visa gì?', 'a' => 'Khoá ngắn trong thời hạn miễn visa hoặc visa du lịch có thể không cần visa sinh viên (tuỳ nước); khoá dài cần visa sinh viên như Úc subclass 500, Canada study permit cho khoá trên 6 tháng, Anh Student visa. Ban Du học Hội TESOL TP.HCM tư vấn theo độ dài khoá và nước đến.' ],
			[ 'q' => 'Học tiếng Anh xong có chuyển lên khoá chính không?', 'a' => 'Nhiều trung tâm tiếng Anh thuộc hoặc liên kết với đại học, cao đẳng có lộ trình pathway: đạt trình độ đầu ra là vào thẳng khoá chính mà không cần thi lại IELTS. Cần xác nhận thoả thuận này trước khi đăng ký.' ],
			[ 'q' => 'Nên chọn nước nào để học tiếng Anh?', 'a' => 'Philippines tiết kiệm và nhiều giờ học 1:1; Úc, Canada, Anh, New Zealand, Mỹ chi phí cao hơn nhưng có môi trường bản ngữ và lộ trình pathway tốt; Malaysia, Singapore gần Việt Nam với chi phí vừa phải. Chọn theo mục tiêu sau khoá tiếng Anh.' ],
		],
	];
}

/**
 * FAQ cho trang quốc gia hoặc quốc gia × bậc học.
 * Trang bậc học = FAQ bậc (nếu có) + FAQ quốc gia. Admin ghi đè FAQ quốc gia
 * bằng complex `faqs` trên term country. Trả về [] khi không có nội dung →
 * template không render, schema không in FAQPage.
 *
 * @return array<int,array{q:string,a:string}>
 */
function duy_faq_items( string $country_slug, string $level_slug = '' ): array {
	$country_slug = sanitize_title( $country_slug );
	$level_slug   = sanitize_title( $level_slug );
	$defaults     = duy_faq_defaults();
	$clean        = static fn( array $rows ): array => array_values(
		array_filter(
			array_map(
				static fn( $row ): array => [
					'q' => is_array( $row ) ? trim( duy_public_text( (string) ( $row['q'] ?? '' ) ) ) : '',
					'a' => is_array( $row ) ? trim( duy_public_text( (string) ( $row['a'] ?? '' ) ) ) : '',
				],
				$rows
			),
			static fn( array $row ): bool => '' !== $row['q'] && '' !== $row['a']
		)
	);

	$country_faqs = [];
	if ( '' !== $country_slug ) {
		$term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', $country_slug, 'country' ) : null;
		if ( $term && ! is_wp_error( $term ) && function_exists( 'duy_rows' ) ) {
			$country_faqs = $clean( duy_rows( 'faqs', 'term:' . (int) $term->term_id ) );
		}
		if ( ! $country_faqs ) {
			$country_faqs = $clean( $defaults[ $country_slug ] ?? [] );
		}
	}

	$level_faqs = '' !== $level_slug ? $clean( $defaults[ 'level:' . $level_slug ] ?? [] ) : [];

	return array_merge( $level_faqs, $country_faqs );
}
