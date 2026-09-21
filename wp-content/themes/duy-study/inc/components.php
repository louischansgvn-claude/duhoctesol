<?php
/**
 * Component helpers for mockup parity.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_part( string $name, array $args = [] ): void {
	get_template_part( 'template-parts/' . $name, null, $args );
}

function duy_render_part( string $name, array $args = [] ): string {
	ob_start();
	duy_part( $name, $args );

	return (string) ob_get_clean();
}

function duy_get_arg( array $args, string $key, $default = null ) {
	return array_key_exists( $key, $args ) ? $args[ $key ] : $default;
}

function duy_img( string $file, string $alt, string $class = '', string $loading = 'lazy', array $attrs = [] ): string {
	$attrs['src']     = duy_img_uri( $file );
	$attrs['alt']     = $alt;
	$attrs['loading'] = $loading;
	if ( $class ) {
		$attrs['class'] = $class;
	}

	// Kích thước nội tại giúp trình duyệt chừa đúng chỗ trước khi ảnh tải xong (giảm CLS).
	if ( empty( $attrs['width'] ) && empty( $attrs['height'] ) ) {
		[ $width, $height ] = duy_img_dims( $file );
		if ( $width > 0 && $height > 0 ) {
			$attrs['width']  = $width;
			$attrs['height'] = $height;
		}
	}
	if ( empty( $attrs['decoding'] ) ) {
		$attrs['decoding'] = 'async';
	}

	$attr_html = '';
	foreach ( [ 'src', 'alt', 'class', 'width', 'height', 'loading', 'fetchpriority', 'decoding' ] as $name ) {
		if ( ! isset( $attrs[ $name ] ) || '' === (string) $attrs[ $name ] ) {
			continue;
		}

		$attr_html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( (string) $attrs[ $name ] ) );
	}

	return sprintf(
		'<img%1$s>',
		$attr_html
	);
}

function duy_route_context( ?array $context = null ) {
	static $current = [];

	if ( null !== $context ) {
		$current = $context;
	}

	return $current;
}

function duy_route_value( string $key, $default = null ) {
	$context = duy_route_context();

	return $context[ $key ] ?? $default;
}

function duy_route_path( string $path ): string {
	return duy_url( '/' . trim( $path, '/' ) . '/' );
}

function duy_country_url_from_slug( string $slug ): string {
	return duy_route_path( 'quoc-gia/' . $slug );
}

function duy_country_url( string $country ): string {
	return duy_country_url_from_slug( duy_country_slug( $country ) );
}

function duy_country_level_url( string $country_slug, string $level_slug ): string {
	return duy_route_path( 'quoc-gia/' . sanitize_title( $country_slug ) . '/' . sanitize_title( $level_slug ) );
}

function duy_country_video_url( string $country_slug ): string {
	return duy_route_path( 'quoc-gia/' . sanitize_title( $country_slug ) . '/video' );
}

function duy_official_youtube_channel_url(): string {
	return 'https://www.youtube.com/channel/UCnRMq3sEWAtESzSz_acvkXQ';
}

function duy_official_youtube_videos(): array {
	return [
		// --- Video mới từ kênh YouTube chính thức (đồng bộ 2026-07-27) ---
		[ 'id' => 'jyewMNzw5qg', 'countries' => [ 'tho-nhi-ky' ], 'level' => 'dai-hoc', 'title' => 'Du học Thổ Nhĩ Kỳ cùng Istanbul Okan University', 'school' => 'Istanbul Okan University', 'school_slug' => 'istanbul-okan-university', 'desc' => 'Giới thiệu Istanbul Okan University và lộ trình đại học tại Thổ Nhĩ Kỳ.', 'published' => '2026-07-18' ],
		[ 'id' => 'aaR3WyE9-Tw', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học Canada cùng St. Jude\'s Academy', 'school' => 'St. Jude\'s Academy', 'school_slug' => '', 'desc' => 'Trường tư thục St. Jude\'s Academy cho học sinh chọn bậc THPT tại Canada.', 'published' => '2026-07-18' ],
		[ 'id' => 'MA5jyMNnSWs', 'countries' => [ 'canada' ], 'level' => '', 'title' => 'Học ngành gì khi du học tại Canada?', 'school' => 'Du học Canada', 'school_slug' => '', 'desc' => 'Gợi ý cách chọn ngành khi xây lộ trình du học Canada.', 'published' => '2026-07-18' ],
		[ 'id' => 'G6QuNaS27HE', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học Canada cùng Toronto District Christian High School', 'school' => 'Toronto District Christian High School', 'school_slug' => '', 'desc' => 'Lựa chọn trường trung học Cơ Đốc tại khu vực Toronto, Canada.', 'published' => '2026-07-17' ],
		[ 'id' => 'o7owpqXKTE0', 'countries' => [ 'thuy-sy' ], 'level' => 'dai-hoc', 'title' => 'Có thể nhận bằng cử nhân chỉ mới 20 tuổi?', 'school' => 'Du học Thụy Sỹ', 'school_slug' => '', 'desc' => 'Lộ trình rút ngắn thời gian lấy bằng cử nhân và những điều cần kiểm tra.', 'published' => '2026-07-17' ],
		[ 'id' => 'VuP0IeItCf8', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học THPT Canada cùng NIC - Newton International College', 'school' => 'Newton International College', 'school_slug' => 'newton-international-college', 'desc' => 'Newton International College cho học sinh Việt Nam học bậc THPT tại Canada.', 'published' => '2026-07-17' ],
		[ 'id' => 'ia1v9SmWhJA', 'countries' => [ 'thuy-sy' ], 'level' => '', 'title' => 'Du học miễn phí tại Thụy Sỹ có hay không?', 'school' => 'Du học Thụy Sỹ', 'school_slug' => '', 'desc' => 'Giải đáp về chi phí và học bổng khi cân nhắc Thụy Sỹ.', 'published' => '2026-07-17' ],
		[ 'id' => 'QM1e50J8lWw', 'countries' => [ 'canada' ], 'level' => '', 'title' => 'Vì sao các bạn nữ đang chọn những ngành này ở Canada?', 'school' => 'Du học Canada', 'school_slug' => '', 'desc' => 'Các nhóm ngành đang được học sinh nữ lựa chọn nhiều tại Canada.', 'published' => '' ],
		[ 'id' => 'Czzf54dHwDs', 'countries' => [ 'canada' ], 'level' => 'cao-dang', 'title' => 'Du học Canada cùng Mohawk College', 'school' => 'Mohawk College', 'school_slug' => 'mohawk-college-hamilton', 'desc' => 'Mohawk College và lộ trình cao đẳng thực hành tại Canada.', 'published' => '' ],
		[ 'id' => '-yaTucCW1Ik', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Du học THPT Mỹ trọn gói chỉ 24.900 USD', 'school' => 'Du học THPT Mỹ', 'school_slug' => '', 'desc' => 'Thông tin gói chương trình THPT Mỹ và các khoản cần xác nhận thêm.', 'published' => '' ],
		[ 'id' => '2CUW6pc01hg', 'countries' => [ 'thuy-sy' ], 'level' => 'sau-dai-hoc', 'title' => 'Du học thạc sĩ tại Thụy Sỹ nên hay không?', 'school' => 'Du học Thụy Sỹ', 'school_slug' => '', 'desc' => 'Những điểm cần cân nhắc trước khi chọn bậc thạc sĩ tại Thụy Sỹ.', 'published' => '' ],
		[ 'id' => 'JVFf_6qAqUs', 'countries' => [ 'canada' ], 'level' => 'sau-dai-hoc', 'title' => 'Du học thạc sĩ tại Canada cùng University of Windsor', 'school' => 'University of Windsor', 'school_slug' => 'university-of-windsor', 'desc' => 'Chương trình thạc sĩ tại University of Windsor, Canada.', 'published' => '' ],
		[ 'id' => 'tecrWHZ-nAM', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Giải tỏa nỗi lo ngôn ngữ khi cho con du học THPT Canada', 'school' => 'Du học THPT Canada', 'school_slug' => '', 'desc' => 'Cách chuẩn bị ngôn ngữ cho con trước khi vào bậc THPT tại Canada.', 'published' => '' ],
		[ 'id' => 'apE3TzVQlC4', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học THPT Canada cùng MISA - Metro International Secondary Academy', 'school' => 'Metro International Secondary Academy (MISA)', 'school_slug' => 'metro-international-secondary-academy', 'desc' => 'MISA - lựa chọn trung học tại Canada cho học sinh quốc tế.', 'published' => '' ],
		[ 'id' => 'zyeBApkoZMQ', 'countries' => [ 'malaysia' ], 'level' => 'dai-hoc', 'title' => 'Học ngành Data Science tại APU có gì thú vị?', 'school' => 'Asia Pacific University (APU)', 'school_slug' => 'asia-pacific-university-of-technology-and-innovation-apu', 'desc' => 'Ngành Data Science tại APU và cơ hội nghề nghiệp sau tốt nghiệp.', 'published' => '' ],
		[ 'id' => 'mx03Hl353wA', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Trải nghiệm hè 3 châu lục cùng CATS Academy Boston', 'school' => 'CATS Academy Boston', 'school_slug' => '', 'desc' => 'Chương trình hè trải nghiệm nhiều châu lục cùng CATS Academy Boston.', 'published' => '' ],
		[ 'id' => '1fSEcsy5fWE', 'countries' => [ 'my' ], 'level' => 'dai-hoc', 'title' => 'Du học Mỹ vào thẳng đại học ở 16, 17 tuổi?', 'school' => 'Du học Mỹ', 'school_slug' => '', 'desc' => 'Lộ trình vào thẳng đại học Mỹ sớm và điều kiện hồ sơ đi kèm.', 'published' => '' ],
		[ 'id' => 'MEZIDem_I2g', 'countries' => [ 'my' ], 'level' => '', 'title' => 'Du học hè Mỹ miễn phí có hay không?', 'school' => 'Du học hè Mỹ', 'school_slug' => '', 'desc' => 'Giải đáp về chương trình hè tại Mỹ và các khoản chi phí thực tế.', 'published' => '' ],
		[ 'id' => 'y_GSoMEKyH8', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Du học THPT Mỹ cùng St. Thomas More School', 'school' => 'St. Thomas More School', 'school_slug' => '', 'desc' => 'St. Thomas More School cho học sinh chọn bậc THPT tại Mỹ.', 'published' => '' ],
		[ 'id' => '_DVjmoQnjx4', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học Canada cùng St. John Brebeuf Regional Secondary School', 'school' => 'St. John Brebeuf Regional Secondary School', 'school_slug' => '', 'desc' => 'Trường trung học St. John Brebeuf tại Canada.', 'published' => '' ],
		[ 'id' => 'h6oNUo7s3Jg', 'countries' => [ 'my' ], 'level' => 'cao-dang', 'title' => 'Du học cao đẳng cộng đồng Mỹ tại Whatcom Community College', 'school' => 'Whatcom Community College', 'school_slug' => 'whatcom-community-college', 'desc' => 'Lộ trình cao đẳng cộng đồng tại Whatcom Community College, Mỹ.', 'published' => '' ],
		[ 'id' => 'RuwOCy27tOs', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Vì sao nên chọn tư thục thay công lập khi du học THPT Canada', 'school' => 'Du học THPT Canada', 'school_slug' => '', 'desc' => 'So sánh trường tư thục và công lập khi chọn bậc THPT tại Canada.', 'published' => '' ],
		[ 'id' => '8Z5y3sbWQpI', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học THPT Canada cùng UIS - Urban International School', 'school' => 'Urban International School (UIS)', 'school_slug' => 'urban-international-school', 'desc' => 'Urban International School - lựa chọn THPT tại Toronto, Canada.', 'published' => '' ],
		[ 'id' => 'G7tuBa9ie1Q', 'countries' => [ 'my' ], 'level' => 'dai-hoc', 'title' => 'Học khối ngành STEM cùng University of Bridgeport', 'school' => 'University of Bridgeport', 'school_slug' => 'university-of-bridgeport', 'desc' => 'Khối ngành STEM tại University of Bridgeport, Mỹ.', 'published' => '' ],
		[ 'id' => '9J2ujuDd3xE', 'countries' => [ 'canada' ], 'level' => 'cao-dang', 'title' => 'Du học Canada cùng Algonquin College', 'school' => 'Algonquin College', 'school_slug' => 'algonquin-college-ottawa', 'desc' => 'Algonquin College và các chương trình cao đẳng ứng dụng tại Canada.', 'published' => '' ],
		[ 'id' => 'uyMiFi26XxQ', 'countries' => [ 'thuy-sy' ], 'level' => 'dai-hoc', 'title' => 'Du học Thụy Sỹ cùng BHMS - Business & Hotel Management School', 'school' => 'Business & Hotel Management School (BHMS)', 'school_slug' => 'bhms', 'desc' => 'BHMS - quản trị khách sạn và kinh doanh tại Thụy Sỹ.', 'published' => '' ],
		[ 'id' => 'm1pOzXy0V0o', 'countries' => [ 'thuy-sy' ], 'level' => 'dai-hoc', 'title' => 'Du học Thụy Sỹ cùng HTMI - Hotel and Tourism Management Institute', 'school' => 'Hotel and Tourism Management Institute (HTMI)', 'school_slug' => 'htmi', 'desc' => 'HTMI - quản trị khách sạn và du lịch tại Thụy Sỹ.', 'published' => '' ],
		[ 'id' => 'VQSMBKQ4gOg', 'countries' => [ 'canada' ], 'level' => 'cao-dang', 'title' => 'Du học Canada cùng St. Lawrence College', 'school' => 'St. Lawrence College', 'school_slug' => 'st-lawrence-college-kingston', 'desc' => 'St. Lawrence College và lộ trình cao đẳng tại Ontario, Canada.', 'published' => '' ],
		[ 'id' => '6oMjurc3yCY', 'countries' => [ 'my' ], 'level' => 'dai-hoc', 'title' => 'Lộ trình trở thành bác sĩ y khoa tại Mỹ cùng MCPHS', 'school' => 'MCPHS University', 'school_slug' => 'mcphs-university', 'desc' => 'Lộ trình theo ngành y khoa - dược tại MCPHS University, Mỹ.', 'published' => '' ],
		[ 'id' => 'd4J3QQvoHTM', 'countries' => [ 'canada' ], 'level' => 'dai-hoc', 'title' => 'Du học Canada cùng KPU - Kwantlen Polytechnic University', 'school' => 'Kwantlen Polytechnic University (KPU)', 'school_slug' => 'kwantlen-polytechnic-university-kpu', 'desc' => 'KPU và lựa chọn đại học ứng dụng tại British Columbia, Canada.', 'published' => '' ],
		[ 'id' => 'aXzBbYTJ1fs', 'countries' => [ 'malaysia' ], 'level' => 'dai-hoc', 'title' => 'Du học Malaysia cùng APU - Asia Pacific University', 'school' => 'Asia Pacific University (APU)', 'school_slug' => 'asia-pacific-university-of-technology-and-innovation-apu', 'desc' => 'Giới thiệu Asia Pacific University và lộ trình đại học tại Malaysia.', 'published' => '' ],
		[ 'id' => 'VjTdm2H1v0Y', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Học bổng THPT Mỹ 100% cùng CATS Boston', 'school' => 'CATS Academy Boston', 'school_slug' => '', 'desc' => 'Thông tin học bổng bậc THPT Mỹ cùng CATS Boston.', 'published' => '' ],
		[ 'id' => 'wmp3TWFM-Fo', 'countries' => [ 'singapore' ], 'level' => 'dai-hoc', 'title' => 'Du học Singapore cùng PSB Academy', 'school' => 'PSB Academy', 'school_slug' => 'psb-academy', 'desc' => 'PSB Academy và lộ trình học tại Singapore.', 'published' => '' ],
		[ 'id' => 'fW0iQ6tZGpU', 'countries' => [ 'canada' ], 'level' => 'dai-hoc', 'title' => 'Du học Canada ngành kinh tế có khó xin việc?', 'school' => 'Du học Canada', 'school_slug' => '', 'desc' => 'Ngành kinh tế tại Canada và câu hỏi về cơ hội việc làm sau tốt nghiệp.', 'published' => '' ],
		[ 'id' => 'aqICkKg0xSg', 'countries' => [ 'canada' ], 'level' => '', 'title' => 'Visa du lịch Canada cho khách hàng Quảng Bình', 'school' => 'Dịch vụ visa Canada', 'school_slug' => '', 'desc' => 'Chia sẻ hồ sơ visa Canada thực tế của khách hàng Ban Du học Hội TESOL TP.HCM.', 'published' => '' ],
		[ 'id' => 'it74iMxngik', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học THPT Canada cùng J. Addison School', 'school' => 'J. Addison School', 'school_slug' => 'j-addison-school', 'desc' => 'J. Addison School - lựa chọn THPT nội trú tại Canada.', 'published' => '' ],
		[ 'id' => '3AZF0QpgGyg', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Học bổng du học THPT Canada cùng UIS', 'school' => 'Urban International School (UIS)', 'school_slug' => 'urban-international-school', 'desc' => 'Học bổng bậc THPT Canada tại Urban International School.', 'published' => '' ],
		[ 'id' => 'pZd8kK1a_8g', 'countries' => [ 'canada' ], 'level' => 'thpt', 'title' => 'Du học THPT Canada cùng UIS - Urban International School', 'school' => 'Urban International School (UIS)', 'school_slug' => 'urban-international-school', 'desc' => 'Giới thiệu Urban International School cho bậc THPT tại Canada.', 'published' => '' ],
		[ 'id' => 'I9OFlRpze0I', 'countries' => [ 'canada' ], 'level' => '', 'title' => 'Visa du học Canada cho học sinh Bảo Lộc', 'school' => 'Visa du học Canada', 'school_slug' => '', 'desc' => 'Kết quả visa du học Canada của học sinh Ban Du học Hội TESOL TP.HCM.', 'published' => '' ],
		[ 'id' => '2M3H3Yo3yF8', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Du học hè 3 châu lục cùng CATS Global Schools', 'school' => 'CATS Global Schools', 'school_slug' => '', 'desc' => 'Chương trình hè liên châu lục cùng hệ thống CATS Global Schools.', 'published' => '' ],
		[ 'id' => 'xc2kb0vjoaI', 'countries' => [ 'my' ], 'level' => '', 'title' => 'Trại hè 2 tuần tại Mỹ - học bổng toàn phần đang mở', 'school' => 'Trại hè Mỹ', 'school_slug' => '', 'desc' => 'Thông tin trại hè tại Mỹ và suất học bổng đang mở.', 'published' => '' ],
		[ 'id' => 'PKhBHKOp0Bo', 'countries' => [ 'canada' ], 'level' => '', 'title' => 'Học sinh Quảng Bình nhận visa du học Canada', 'school' => 'Visa du học Canada', 'school_slug' => '', 'desc' => 'Học sinh Ban Du học Hội TESOL TP.HCM nhận visa du học Canada.', 'published' => '' ],
		[ 'id' => 'kCazZ08-_m0', 'countries' => [ 'my' ], 'level' => '', 'title' => 'Summer Camp 2026 - trải nghiệm mùa hè tại Mỹ', 'school' => 'Summer Camp Mỹ', 'school_slug' => '', 'desc' => 'Chương trình Summer Camp 2026 tại Mỹ.', 'published' => '' ],
		[ 'id' => 'mctFI7BjtKI', 'countries' => [ 'my' ], 'level' => 'thpt', 'title' => 'Học bổng giao lưu văn hóa Mỹ J-1 2026', 'school' => 'Chương trình giao lưu văn hóa J-1', 'school_slug' => '', 'desc' => 'Học bổng giao lưu văn hóa Mỹ diện J-1 năm 2026.', 'published' => '' ],
		[ 'id' => '4FcUVUEwG_o', 'countries' => [ 'my' ], 'title' => 'Du học ngành Y tại Mỹ cùng MCPHS', 'school' => 'MCPHS', 'desc' => 'Video từ kênh YouTube chính của Ban Du học Hội TESOL TP.HCM về lộ trình ngành Y tại Mỹ.', 'published' => '2026-07-05' ],
		[ 'id' => 'Vm_udCY4zEQ', 'countries' => [ 'my' ], 'title' => 'Du học thạc sĩ Mỹ chuyên ngành nghệ thuật', 'school' => 'Full Sail University', 'desc' => 'Gợi ý cho hồ sơ quan tâm nhóm ngành nghệ thuật và sáng tạo tại Mỹ.', 'published' => '2026-07-05' ],
		[ 'id' => '5ue34BGAcsY', 'countries' => [ 'my' ], 'title' => 'Du học Mỹ nhận bằng kép', 'school' => 'Whatcom Community College', 'desc' => 'Nội dung về lựa chọn chương trình bằng kép khi lên kế hoạch du học Mỹ.', 'published' => '2026-07-05' ],
		[ 'id' => '9Acf87T3wH8', 'countries' => [ 'my' ], 'title' => 'Du học Mỹ cùng Full Sail University', 'school' => 'Full Sail University', 'desc' => 'Tìm hiểu trường Full Sail University và các điểm cần hỏi khi chọn ngành.', 'published' => '2026-07-04' ],
		[ 'id' => 'wf-ILZASO7k', 'countries' => [ 'my' ], 'title' => 'Du học Mỹ cùng University of Bridgeport', 'school' => 'University of Bridgeport', 'desc' => 'Video giới thiệu lựa chọn trường tại Mỹ từ kênh Ban Du học Hội TESOL TP.HCM.', 'published' => '2026-07-04' ],
		[ 'id' => '4k_A0rxo1Oc', 'countries' => [ 'my' ], 'title' => 'Lộ trình vào ngành công nghệ cao tại Mỹ', 'school' => 'University of Idaho', 'desc' => 'Gợi ý lộ trình ngành công nghệ cao cho học sinh quan tâm du học Mỹ.', 'published' => '2026-07-04' ],
		[ 'id' => 'kyOyZFGCui0', 'countries' => [ 'my' ], 'title' => 'Cơ hội nhận học bổng từ University of Idaho', 'school' => 'University of Idaho', 'desc' => 'Video về học bổng và các điểm cần chuẩn bị khi nộp hồ sơ.', 'published' => '2026-07-03' ],
		[ 'id' => 'cbKgTB8Up4s', 'countries' => [ 'my' ], 'title' => 'Học chương trình cử nhân song bằng tại Mỹ có khó không?', 'school' => 'Hartwick College', 'desc' => 'Chia sẻ về chương trình song bằng và cách gia đình nên đặt câu hỏi khi chọn trường.', 'published' => '2026-07-03' ],
		[ 'id' => '-D92pqSP9Eg', 'countries' => [ 'my' ], 'title' => 'Du học Mỹ cùng Hartwick College', 'school' => 'Hartwick College', 'desc' => 'Tổng quan lựa chọn Hartwick College trong danh sách trường Mỹ.', 'published' => '2026-07-03' ],
		[ 'id' => 'T6Wi2bPc29E', 'countries' => [ 'my' ], 'title' => 'Du học Mỹ cùng Tulsa University', 'school' => 'Tulsa University', 'desc' => 'Video từ kênh Ban Du học Hội TESOL TP.HCM về một lựa chọn trường tại Mỹ.', 'published' => '' ],
		[ 'id' => 'mjzggsUf9hg', 'countries' => [ 'canada' ], 'title' => 'Lộ trình trở thành bác sĩ y khoa tại Canada', 'school' => 'KPU', 'desc' => 'Nội dung định hướng ngành Y và lộ trình học tại Canada.', 'published' => '2026-07-05' ],
		[ 'id' => 'O2hAfB6HHFc', 'countries' => [ 'canada' ], 'title' => 'Vì sao nên cho con du học từ bậc THPT Canada', 'school' => 'Newton International College', 'desc' => 'Gợi ý cho phụ huynh đang cân nhắc lộ trình THPT tại Canada.', 'published' => '2026-07-05' ],
		[ 'id' => 'Iv_BAreoVJ0', 'countries' => [ 'canada' ], 'title' => 'Du học Canada cùng Fanshawe College', 'school' => 'Fanshawe College', 'desc' => 'Video giới thiệu Fanshawe College trên kênh YouTube chính của Ban Du học Hội TESOL TP.HCM.', 'published' => '2026-07-04' ],
		[ 'id' => '6U9HatQo6Vw', 'countries' => [ 'canada' ], 'title' => 'Học Dental Hygiene tại Algonquin College, Canada', 'school' => 'Algonquin College', 'desc' => 'Nội dung phù hợp với học sinh quan tâm nhóm ngành sức khỏe tại Canada.', 'published' => '2026-07-03' ],
		[ 'id' => 'QMDQIJZtxtE', 'countries' => [ 'canada' ], 'title' => 'Du học Canada tại University of Windsor', 'school' => 'University of Windsor', 'desc' => 'Tìm hiểu University of Windsor và các câu hỏi cần chuẩn bị trước tư vấn.', 'published' => '2026-07-03' ],
		[ 'id' => 'fygqsuRgLbU', 'countries' => [ 'canada' ], 'title' => 'Khởi đầu việc du học Canada cùng ILAC và Ban Du học Hội TESOL TP.HCM', 'school' => 'ILAC', 'desc' => 'Video về lộ trình tiếng Anh và bước khởi đầu khi chọn Canada.', 'published' => '' ],
		[ 'id' => 'BLUVU3gFlk8', 'countries' => [ 'canada' ], 'title' => 'Du học Canada tại Fanshawe College', 'school' => 'Fanshawe College', 'desc' => 'Tìm hiểu thêm về lựa chọn cao đẳng tại Canada.', 'published' => '' ],
		[ 'id' => 'GOnOILNXCOA', 'countries' => [ 'canada' ], 'title' => 'Du học Canada - lộ trình chuyển tiếp đại học tại Coquitlam College', 'school' => 'Coquitlam College', 'desc' => 'Nội dung về pathway/chuyển tiếp đại học tại Canada.', 'published' => '' ],
		[ 'id' => 'Vpp34aVNa4Q', 'countries' => [ 'canada' ], 'title' => 'Du học THPT tại St. John\'s Academy, BC, Canada', 'school' => 'St. John\'s Academy', 'desc' => 'Video cho phụ huynh và học sinh quan tâm bậc THPT tại British Columbia.', 'published' => '' ],
		[ 'id' => 'j4jyyXKhiD8', 'countries' => [ 'canada' ], 'title' => 'Du học Canada ngành giáo dục mầm non tại Nova Scotia', 'school' => 'Nova Scotia', 'desc' => 'Gợi ý ngành giáo dục mầm non và lựa chọn tỉnh bang tại Canada.', 'published' => '' ],
		[ 'id' => 'HGJ_bXtXI-Q', 'countries' => [ 'uc' ], 'title' => 'Du học cao đẳng nghề Úc ngành Agriculture', 'school' => 'College/VET Australia', 'desc' => 'Video từ kênh Ban Du học Hội TESOL TP.HCM về lộ trình cao đẳng nghề tại Úc.', 'published' => '' ],
		[ 'id' => 'bJFv96YD1Vg', 'countries' => [ 'uc' ], 'title' => 'Update chính sách du học Úc 2025', 'school' => 'Du học Úc', 'desc' => 'Cập nhật chính sách và các điểm gia đình cần kiểm tra khi chuẩn bị hồ sơ Úc.', 'published' => '' ],
		[ 'id' => 'sP4HfV9sYSk', 'countries' => [ 'uc' ], 'title' => 'Du học cao đẳng nghề Úc cùng Stanley College', 'school' => 'Stanley College', 'desc' => 'Video giới thiệu lộ trình nghề và trường tại Úc.', 'published' => '' ],
		[ 'id' => '9_H4_ebr3u0', 'countries' => [ 'uc' ], 'title' => 'Du học nghề Úc khối ngành hot', 'school' => 'VET Australia', 'desc' => 'Gợi ý nhóm ngành nghề tại Úc cho học sinh muốn lộ trình thực tiễn.', 'published' => '' ],
		[ 'id' => 'rVW4FqiaFiA', 'countries' => [ 'uc' ], 'title' => 'Chi phí làm hồ sơ du học cao đẳng nghề tại Úc', 'school' => 'Du học Úc', 'desc' => 'Video giúp gia đình đặt câu hỏi về ngân sách và chi phí hồ sơ.', 'published' => '' ],
		[ 'id' => 'cE798e6ptLg', 'countries' => [ 'uc' ], 'title' => 'Tìm hiểu về Genuine Student Requirement khi du học Úc', 'school' => 'Du học Úc', 'desc' => 'Nội dung về yêu cầu hồ sơ và cách chuẩn bị câu chuyện học tập.', 'published' => '' ],
		[ 'id' => 'f18DWhT04wE', 'countries' => [ 'uc' ], 'title' => 'Ngành học nổi bật cùng học bổng hot khi du học Úc', 'school' => 'Du học Úc', 'desc' => 'Video gợi ý ngành học và học bổng để gia đình shortlist trước buổi tư vấn.', 'published' => '' ],
		[ 'id' => '016XORmaTnA', 'countries' => [ 'new-zealand' ], 'title' => 'Update du học New Zealand 2025-2026', 'school' => 'Du học New Zealand', 'desc' => 'Cập nhật tổng quan lộ trình và điểm cần lưu ý khi chọn New Zealand.', 'published' => '' ],
	];
}

/**
 * Nhãn `level` của card trường được chấp nhận cho mỗi slug bậc học.
 * Dùng chung bởi trang bậc học và sitemap để hai nơi không lệch nhau.
 *
 * @return array<string,string[]>
 */
function duy_level_school_labels(): array {
	return [
		'thpt'        => [ 'THPT', 'Trung học', 'Phổ thông' ],
		'cao-dang'    => [ 'Cao đẳng', 'College' ],
		'dai-hoc'     => [ 'Đại học', 'Cử nhân' ],
		'sau-dai-hoc' => [ 'Thạc sĩ', 'Sau đại học', 'Tiến sĩ' ],
		'anh-ngu'     => [ 'Anh ngữ', 'Tiếng Anh' ],
	];
}

/**
 * Quốc gia này có trường nào ở bậc học đó không.
 * Kết quả được đếm sẵn một lần cho mỗi request.
 */
function duy_country_level_has_schools( string $country_slug, string $level_slug ): bool {
	static $counts = null;

	if ( null === $counts ) {
		$counts = [];
		$labels = duy_level_school_labels();
		foreach ( duy_demo_schools() as $school ) {
			$c     = duy_country_slug( (string) ( $school['c'] ?? '' ) );
			$level = (string) ( $school['level'] ?? '' );
			foreach ( $labels as $slug => $names ) {
				if ( in_array( $level, $names, true ) ) {
					$counts[ $c . '|' . $slug ] = true;
				}
			}
		}
	}

	return isset( $counts[ $country_slug . '|' . $level_slug ] );
}

/**
 * Link phụ dưới mỗi video: trang trường (nếu trường đã có trên site) và trang
 * bậc học theo quốc gia. Trả về chuỗi rỗng khi video không gắn trường/bậc nào.
 *
 * @param array  $video        Bản ghi video.
 * @param string $country_slug Slug quốc gia đang xem.
 */
function duy_video_links( array $video, string $country_slug ): string {
	$links = [];

	$school_slug = (string) ( $video['school_slug'] ?? '' );
	if ( $school_slug ) {
		$label    = (string) ( $video['school'] ?? $school_slug );
		$links[] = sprintf(
			'<a class="cv-link cv-link--school" href="%s">%s %s</a>',
			esc_url( duy_school_url( $school_slug ) ),
			duy_icon( 'cap' ),
			esc_html( $label )
		);
	}

	$level_slug = (string) ( $video['level'] ?? '' );
	if ( $level_slug && $country_slug ) {
		$levels = duy_mockup_v2_levels();
		$name   = (string) ( $levels[ $level_slug ]['name'] ?? '' );
		if ( $name ) {
			$links[] = sprintf(
				'<a class="cv-link" href="%s">%s %s</a>',
				esc_url( duy_route_path( 'quoc-gia/' . $country_slug . '/' . $level_slug ) ),
				duy_icon( 'layers' ),
				esc_html( $name )
			);
		}
	}

	return $links ? '<div class="cv-links">' . implode( '', $links ) . '</div>' : '';
}

function duy_country_video_items( string $country_slug, array $country, $country_ref = null ): array {
	$videos = [];

	if ( $country_ref ) {
		$videos = array_values(
			array_filter(
				array_map(
					static function ( array $row ): array {
						return [
							'id'    => duy_youtube_id( (string) ( $row['url'] ?? '' ) ),
							'title' => duy_public_text( (string) ( $row['title'] ?? 'Video du học' ) ),
							'desc'  => duy_public_text( (string) ( $row['desc'] ?? '' ) ),
							'school' => duy_public_text( (string) ( $row['school'] ?? '' ) ),
						];
					},
					duy_rows( 'videos', $country_ref )
				),
				static fn( array $video ): bool => '' !== $video['id']
			)
		);
	}

	if ( $videos ) {
		return $videos;
	}

	$country_slug = sanitize_title( $country_slug );

	return array_values(
		array_filter(
			duy_official_youtube_videos(),
			static fn( array $video ): bool => in_array( $country_slug, (array) ( $video['countries'] ?? [] ), true )
		)
	);
}

function duy_major_hub_url(): string {
	return duy_route_path( 'nganh-hoc' );
}

function duy_major_url( string $slug ): string {
	return duy_route_path( 'nganh-hoc/' . sanitize_title( $slug ) );
}

function duy_post_type_item_url( string $id, string $post_type, string $route_base ): string {
	$id = sanitize_title( $id );

	if ( $id && function_exists( 'get_page_by_path' ) && function_exists( 'get_permalink' ) ) {
		$post = get_page_by_path( $id, OBJECT, $post_type );
		if ( $post instanceof WP_Post ) {
			$permalink = get_permalink( $post );
			if ( $permalink ) {
				return $permalink;
			}
		}
	}

	return duy_route_path( trim( $route_base, '/' ) . '/' . $id );
}

function duy_school_url( string $id ): string {
	return duy_post_type_item_url( $id, 'school', 'truong' );
}

function duy_scholarship_url( string $id ): string {
	return duy_post_type_item_url( $id, 'scholarship', 'hoc-bong' );
}

function duy_event_url( string $id ): string {
	return duy_post_type_item_url( $id, 'event', 'su-kien' );
}

function duy_news_url( string $id ): string {
	return duy_route_path( 'tin-tuc/' . sanitize_title( $id ) );
}

function duy_story_url( string $id ): string {
	return duy_post_type_item_url( $id, 'student_story', 'hoc-sinh' );
}

function duy_guide_url( string $slug ): string {
	return duy_post_type_item_url( $slug, 'guide', 'lo-trinh-du-hoc' );
}

function duy_guide_by_slug( string $slug ): ?array {
	foreach ( duy_demo_guides() as $guide ) {
		if ( ( $guide['slug'] ?? '' ) === $slug ) {
			return $guide;
		}
	}

	return null;
}

function duy_country_by_slug( string $slug ): ?array {
	$countries = duy_demo_countries();

	return $countries[ $slug ] ?? null;
}

function duy_items_by_ids( array $items, array $ids ): array {
	$indexed = [];

	foreach ( $items as $item ) {
		$indexed[ $item['id'] ?? '' ] = $item;
	}

	return array_values(
		array_filter(
			array_map(
				static fn( $id ) => $indexed[ $id ] ?? null,
				$ids
			)
		)
	);
}

/**
 * Trường hiển thị cho một quốc gia.
 *
 * Ưu tiên danh sách "trường tiêu biểu" chọn tay; nếu quốc gia chưa có danh sách
 * đó thì lấy toàn bộ trường đã import theo quốc gia, để trang không bị trống.
 *
 * @param string   $slug        Slug quốc gia (vd: `anh`).
 * @param string[] $curated_ids Danh sách slug trường chọn tay.
 */
function duy_country_school_cards( string $slug, array $curated_ids = [] ): array {
	$schools = duy_demo_schools();
	$curated = duy_items_by_ids( $schools, $curated_ids );
	if ( $curated ) {
		return $curated;
	}

	return array_values(
		array_filter(
			$schools,
			static fn( $item ) => duy_country_slug( (string) ( $item['c'] ?? '' ) ) === $slug
		)
	);
}

function duy_association_entries( string $name, $post_id = null, string $type = '', string $subtype = '' ): array {
	$value = null === $post_id ? duy_option( $name, [] ) : duy_field( $name, $post_id, [] );
	if ( ! is_array( $value ) ) {
		return [];
	}

	$entries = [];
	foreach ( $value as $entry ) {
		if ( is_string( $entry ) ) {
			$parts = explode( ':', $entry );
			$entry = [
				'type'    => $parts[0] ?? '',
				'subtype' => $parts[1] ?? '',
				'id'      => (int) ( $parts[2] ?? 0 ),
			];
		}

		if ( ! is_array( $entry ) ) {
			continue;
		}

		$entry_type    = (string) ( $entry['type'] ?? '' );
		$entry_subtype = (string) ( $entry['subtype'] ?? '' );
		$entry_id      = (int) ( $entry['id'] ?? 0 );

		if ( ! $entry_id || ( $type && $entry_type !== $type ) || ( $subtype && $entry_subtype !== $subtype ) ) {
			continue;
		}

		$entries[] = [
			'type'    => $entry_type,
			'subtype' => $entry_subtype,
			'id'      => $entry_id,
		];
	}

	return $entries;
}

function duy_term_name_for_post( int $post_id, string $taxonomy ): string {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $terms ) || ! $terms ) {
		return '';
	}

	return (string) $terms[0]->name;
}

function duy_post_card_fallback( int $post_id, string $post_type ): ?array {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || $post_type !== $post->post_type ) {
		return null;
	}

	$id      = $post->post_name ?: (string) $post_id;
	$title   = get_the_title( $post );
	$country = duy_term_name_for_post( $post_id, 'country' );

	if ( 'school' === $post_type ) {
		$level = duy_term_name_for_post( $post_id, 'study_level' );
		$major = duy_term_name_for_post( $post_id, 'major' );

		return [
			'id'      => $id,
			'n'       => $title,
			'c'       => $country,
			'code'    => duy_country_code( $country ),
			'r'       => (string) duy_field( 'ranking', $post_id, '' ),
			'fee'     => (string) duy_field( 'fee', $post_id, '' ),
			'feeBand' => (string) duy_field( 'fee_band', $post_id, '' ),
			'level'   => $level,
			'major'   => $major,
			'tag'     => (string) duy_field( 'tag', $post_id, '' ),
			'city'    => (string) duy_field( 'city', $post_id, $country ),
			'desc'    => get_the_excerpt( $post ),
		];
	}

	if ( 'scholarship' === $post_type ) {
		$deadline = duy_field( 'deadline', $post_id, '' );

		return [
			'id'     => $id,
			'n'      => $title,
			'c'      => $country,
			'v'      => (string) duy_field( 'value', $post_id, '' ),
			'value'  => (string) duy_field( 'value', $post_id, '' ),
			'level'  => duy_term_name_for_post( $post_id, 'study_level' ),
			'd'      => is_string( $deadline ) ? $deadline : '',
			'tag'    => '',
			'school' => '',
		];
	}

	if ( 'post' === $post_type ) {
		$category = get_the_category( $post_id );

		return [
			'id'   => $id,
			'n'    => $title,
			'cat'  => $category ? (string) $category[0]->name : '',
			'c'    => get_the_date( 'd/m/Y', $post ),
			'lead' => get_the_excerpt( $post ),
		];
	}

	return null;
}

function duy_associated_content_items( string $field, $post_id, array $demo_items, string $post_type ): array {
	$items   = [];
	$indexed = [];

	foreach ( $demo_items as $item ) {
		if ( ! empty( $item['id'] ) ) {
			$indexed[ (string) $item['id'] ] = $item;
		}
	}

	foreach ( duy_association_entries( $field, $post_id, 'post', $post_type ) as $entry ) {
		$post = get_post( (int) $entry['id'] );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$items[] = $indexed[ $post->post_name ] ?? duy_post_card_fallback( (int) $entry['id'], $post_type );
	}

	return array_values( array_filter( $items ) );
}

function duy_parse_item_date( string $value ): int {
	$value = trim( $value );
	if ( '' === $value ) {
		return 0;
	}

	if ( preg_match( '/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $value, $matches ) ) {
		$value = sprintf( '%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1] );
	}

	$timestamp = strtotime( $value );

	return $timestamp ? (int) $timestamp : 0;
}

function duy_item_date_sort_key( array $item, string $post_type = 'post' ): array {
	$id = (string) ( $item['id'] ?? $item['slug'] ?? '' );

	if ( $id && function_exists( 'get_page_by_path' ) ) {
		$post = get_page_by_path( $id, OBJECT, $post_type );
		if ( $post instanceof WP_Post ) {
			if ( function_exists( 'get_post_timestamp' ) ) {
				$timestamp = get_post_timestamp( $post );
				if ( $timestamp ) {
					return [ 'timestamp' => (int) $timestamp, 'post_id' => (int) $post->ID ];
				}
			}

			$timestamp = strtotime( $post->post_date_gmt ?: $post->post_date );
			if ( $timestamp ) {
				return [ 'timestamp' => (int) $timestamp, 'post_id' => (int) $post->ID ];
			}
		}
	}

	foreach ( [ 'post_date', 'date', 'created_at', 'published_at' ] as $key ) {
		if ( ! empty( $item[ $key ] ) ) {
			$timestamp = duy_parse_item_date( (string) $item[ $key ] );
			if ( $timestamp ) {
				return [ 'timestamp' => $timestamp, 'post_id' => 0 ];
			}
		}
	}

	if ( ! empty( $item['c'] ) ) {
		return [ 'timestamp' => duy_parse_item_date( (string) $item['c'] ), 'post_id' => 0 ];
	}

	return [ 'timestamp' => 0, 'post_id' => 0 ];
}

function duy_item_date_timestamp( array $item, string $post_type = 'post' ): int {
	$sort_key = duy_item_date_sort_key( $item, $post_type );

	return (int) $sort_key['timestamp'];
}

function duy_items_newest_first( array $items, string $post_type = 'post' ): array {
	$indexed = [];

	foreach ( array_values( $items ) as $index => $item ) {
		$sort_key  = is_array( $item ) ? duy_item_date_sort_key( $item, $post_type ) : [ 'timestamp' => 0, 'post_id' => 0 ];
		$indexed[] = [
			'index'     => $index,
			'post_id'   => (int) $sort_key['post_id'],
			'timestamp' => (int) $sort_key['timestamp'],
			'item'      => $item,
		];
	}

	usort(
		$indexed,
		static function ( array $a, array $b ): int {
			if ( $a['timestamp'] === $b['timestamp'] ) {
				if ( $a['post_id'] !== $b['post_id'] ) {
					return $b['post_id'] <=> $a['post_id'];
				}

				return $a['index'] <=> $b['index'];
			}

			return $b['timestamp'] <=> $a['timestamp'];
		}
	);

	return array_column( $indexed, 'item' );
}

function duy_boolish( $value ): bool {
	return true === $value || 1 === $value || '1' === $value || 'yes' === $value || 'on' === $value || 'true' === $value;
}

function duy_item_post_id( array $item, string $post_type = 'post' ): int {
	$sort_key = duy_item_date_sort_key( $item, $post_type );

	return (int) ( $sort_key['post_id'] ?? 0 );
}

function duy_item_is_featured( array $item, string $post_type = 'post' ): bool {
	$post_id = duy_item_post_id( $item, $post_type );
	$field   = $post_id ? duy_field( 'is_featured', $post_id, null ) : null;

	return duy_boolish( $field ) || duy_boolish( $item['featured'] ?? false );
}

function duy_items_featured_then_newest( array $items, string $post_type = 'post' ): array {
	$indexed = [];

	foreach ( array_values( $items ) as $index => $item ) {
		$indexed[] = [
			'index'     => $index,
			'featured'  => is_array( $item ) && duy_item_is_featured( $item, $post_type ) ? 1 : 0,
			'timestamp' => is_array( $item ) ? duy_item_date_timestamp( $item, $post_type ) : 0,
			'item'      => $item,
		];
	}

	usort(
		$indexed,
		static function ( array $a, array $b ): int {
			if ( $a['featured'] !== $b['featured'] ) {
				return $b['featured'] <=> $a['featured'];
			}

			if ( $a['timestamp'] !== $b['timestamp'] ) {
				return $b['timestamp'] <=> $a['timestamp'];
			}

			return $a['index'] <=> $b['index'];
		}
	);

	return array_column( $indexed, 'item' );
}

function duy_featured_items( array $items, string $post_type = 'post', int $limit = 0 ): array {
	$sorted   = duy_items_featured_then_newest( $items, $post_type );
	$featured = array_values(
		array_filter(
			$sorted,
			static fn( $item ) => is_array( $item ) && duy_item_is_featured( $item, $post_type )
		)
	);

	if ( ! $featured ) {
		$featured = $sorted;
	}

	return $limit > 0 ? array_slice( $featured, 0, $limit ) : $featured;
}

function duy_item_is_new( array $item, string $post_type = 'post', int $days = 14 ): bool {
	$timestamp = duy_item_date_timestamp( $item, $post_type );
	if ( ! $timestamp ) {
		return false;
	}

	$now = function_exists( 'current_time' ) ? (int) current_time( 'timestamp' ) : time();
	$age = $now - $timestamp;

	return $age >= 0 && $age <= $days * DAY_IN_SECONDS;
}

function duy_badge_stack( array $item, string $post_type = 'post' ): string {
	$badges = [];
	if ( duy_item_is_featured( $item, $post_type ) ) {
		$badges[] = '<span class="chip pink">' . esc_html__( 'Nổi bật', 'duy-study' ) . '</span>';
	}
	if ( duy_item_is_new( $item, $post_type ) ) {
		$badges[] = '<span class="chip">' . esc_html__( 'Mới', 'duy-study' ) . '</span>';
	}

	return $badges ? '<span class="badge-stack">' . implode( '', $badges ) . '</span>' : '';
}

function duy_deadline_timestamp( array $item ): int {
	foreach ( [ 'deadline', 'd' ] as $key ) {
		if ( ! empty( $item[ $key ] ) ) {
			$timestamp = duy_parse_item_date( (string) $item[ $key ] );
			if ( $timestamp ) {
				return $timestamp;
			}
		}
	}

	return 0;
}

function duy_deadline_days_left( array $item ): ?int {
	$deadline = duy_deadline_timestamp( $item );
	if ( ! $deadline ) {
		return null;
	}

	$now   = function_exists( 'current_time' ) ? (int) current_time( 'timestamp' ) : time();
	$today = strtotime( date( 'Y-m-d', $now ) );

	return (int) ceil( ( $deadline - $today ) / DAY_IN_SECONDS );
}

function duy_scholarships_by_deadline( array $items ): array {
	usort(
		$items,
		static function ( array $a, array $b ): int {
			return duy_deadline_timestamp( $a ) <=> duy_deadline_timestamp( $b );
		}
	);

	return $items;
}

function duy_event_timestamp( array $item ): int {
	if ( ! empty( $item['datetime'] ) ) {
		$timestamp = duy_parse_item_date( (string) $item['datetime'] );
		if ( $timestamp ) {
			return $timestamp;
		}
	}

	if ( ! empty( $item['time'] ) ) {
		$timestamp = duy_parse_item_date( (string) $item['time'] );
		if ( $timestamp ) {
			return $timestamp;
		}
	}

	return 0;
}

function duy_events_by_time( array $items, string $direction = 'asc' ): array {
	usort(
		$items,
		static function ( array $a, array $b ) use ( $direction ): int {
			$result = duy_event_timestamp( $a ) <=> duy_event_timestamp( $b );

			return 'desc' === $direction ? -$result : $result;
		}
	);

	return $items;
}

function duy_split_events_by_time( array $items ): array {
	$now      = function_exists( 'current_time' ) ? (int) current_time( 'timestamp' ) : time();
	$today    = strtotime( date( 'Y-m-d', $now ) );
	$upcoming = [];
	$past     = [];

	foreach ( $items as $item ) {
		$timestamp = duy_event_timestamp( $item );
		if ( $timestamp && $timestamp < $today ) {
			$past[] = $item;
		} else {
			$upcoming[] = $item;
		}
	}

	return [
		'upcoming' => duy_events_by_time( $upcoming ),
		'past'     => duy_events_by_time( $past, 'desc' ),
	];
}

function duy_archive_banner_defaults( string $key ): array {
	$defaults = [
		'school'      => [ 'image' => 'photo-campus-library.webp', 'url' => duy_route_path( 'lien-he' ), 'title' => 'Cần shortlist trường phù hợp?', 'cta' => 'Gửi hồ sơ để Ban Du học Hội TESOL TP.HCM gợi ý' ],
		'scholarship' => [ 'image' => 'photo-article-laptop.webp', 'url' => duy_route_path( 'lien-he' ), 'title' => 'Muốn rà điều kiện học bổng?', 'cta' => 'Nhận checklist ứng tuyển' ],
		'event'       => [ 'image' => 'photo-event-workshop.webp', 'url' => duy_route_path( 'su-kien' ), 'title' => 'Đặt lịch tham dự sự kiện phù hợp', 'cta' => 'Xem lịch và đăng ký' ],
		'news'        => [ 'image' => 'photo-library-study.webp', 'url' => duy_route_path( 'tai-cam-nang' ), 'title' => 'Tải cẩm nang để lưu checklist', 'cta' => 'Nhận cẩm nang qua email' ],
	];

	return $defaults[ $key ] ?? $defaults['school'];
}

function duy_archive_banner_config( string $key ): array {
	$key      = sanitize_key( $key );
	$prefix   = 'archive_banner_' . $key;
	$defaults = duy_archive_banner_defaults( $key );
	$enabled  = duy_option( $prefix . '_enabled', 'yes' );

	if ( ! duy_boolish( $enabled ) ) {
		return [];
	}

	return [
		'key'         => $key,
		'image_field' => $prefix . '_image',
		'image'       => $defaults['image'],
		'url'         => (string) duy_option( $prefix . '_url', $defaults['url'] ),
		'title'       => (string) duy_option( $prefix . '_title', $defaults['title'] ),
		'cta'         => (string) duy_option( $prefix . '_cta', $defaults['cta'] ),
	];
}

/**
 * Breadcrumb đã render trong request (để in BreadcrumbList JSON-LD ở footer —
 * schema phải khớp breadcrumb hiển thị, nên lấy đúng dữ liệu template đã dùng).
 *
 * @param array|null $items Truyền mảng để ghi nhận; null để đọc.
 * @return array<int,array{label:string,url:string}>
 */
function duy_breadcrumb_items( ?array $items = null ): array {
	static $current = [];

	if ( null !== $items ) {
		$current = $items;
	}

	return $current;
}

function duy_breadcrumb( array $items ): string {
	$parts    = [];
	$recorded = [];

	foreach ( $items as $item ) {
		$label = trim( duy_public_text( (string) ( $item['label'] ?? '' ) ) );
		$url   = (string) ( $item['url'] ?? '' );
		if ( '' === $label ) {
			continue;
		}
		$recorded[] = [ 'label' => $label, 'url' => $url ];
		$parts[]    = '<li>' . ( $url ? sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( $label ) ) : '<span aria-current="page">' . esc_html( $label ) . '</span>' ) . '</li>';
	}

	if ( ! duy_breadcrumb_items() ) {
		duy_breadcrumb_items( $recorded );
	}

	return '<nav class="breadcrumb" aria-label="Breadcrumb"><ol>' . implode( '', $parts ) . '</ol></nav>';
}

/** Ảnh bìa tin tức: ảnh thật của WP post (`image`) → ảnh placeholder theo seed. */
function duy_news_cover_markup( array $item, string $alt, string $fallback = '', string $loading = 'lazy' ): string {
	$image = (string) ( $item['image'] ?? '' );
	if ( '' !== $image ) {
		return sprintf( '<img src="%1$s" alt="%2$s" loading="%3$s" decoding="async">', esc_url( $image ), esc_attr( $alt ), esc_attr( $loading ) );
	}

	return duy_img( $fallback ?: duy_demo_photo( (string) ( $item['id'] ?? '' ), 'news' ), $alt, '', $loading );
}

function duy_icon_tile( string $icon = 'sparkles' ): string {
	return '<span class="icon-tile">' . duy_icon( $icon ) . '</span>';
}

function duy_event_date_parts( array $event ): array {
	$label = (string) ( $event['c'] ?? '' );
	if ( preg_match( '/(\d{2})\/(\d{2})/', $label, $matches ) ) {
		return [ 'day' => $matches[1], 'month' => 'T' . $matches[2] ];
	}

	// Chỉ có năm (vd "2024"): hiện năm thay vì nhãn giữ chỗ.
	if ( preg_match( '/(\d{4})/', $label, $matches ) ) {
		return [ 'day' => $matches[1], 'month' => '' ];
	}

	return [ 'day' => 'D', 'month' => 'DAY' ];
}

function duy_country_options(): array {
	return [
		''             => 'Tất cả quốc gia',
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
	];
}

function duy_current_country_filter(): string {
	return isset( $_GET['country'] ) ? sanitize_key( wp_unslash( $_GET['country'] ) ) : '';
}

function duy_page_hero( string $title, string $lead, array $breadcrumb, string $eyebrow = '' ): void {
	?>
	<section class="page-hero wrap screen active">
		<?php echo wp_kses_post( duy_breadcrumb( $breadcrumb ) ); ?>
		<?php if ( $eyebrow ) : ?>
			<span class="eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>
		<h1><?php echo esc_html( $title ); ?></h1>
		<p><?php echo esc_html( $lead ); ?></p>
	</section>
	<?php
}

function duy_filter_country_items( array $items, string $country_slug ): array {
	if ( ! $country_slug ) {
		return $items;
	}

	return array_values(
		array_filter(
			$items,
			static fn( $item ) => duy_country_slug( (string) ( $item['c'] ?? '' ) ) === $country_slug
		)
	);
}

/* -------------------------------------------------------------------------
 * Finder phía server: lọc / sắp xếp / phân trang
 *
 * Trang /truong/ trước đây render toàn bộ 572 thẻ trường rồi để JS lọc trong
 * DOM (HTML 833 KB, ~8.500 node — chậm trên điện thoại và tốn crawl budget).
 * Nay server lọc + phân trang, JS chỉ nạp lại phần kết quả bằng fetch.
 * ---------------------------------------------------------------------- */

const DUY_FINDER_PER_PAGE = 24;

/** Tham số lọc hợp lệ đọc từ query string (đã sanitize). */
function duy_finder_query(): array {
	$get = static fn( string $key ): string => isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- bộ lọc công khai, chỉ đọc.

	$sort = $get( 'sort' );
	$page = (int) $get( 'trang' );

	return [
		'q'       => mb_substr( $get( 'q' ), 0, 80 ),
		'country' => sanitize_key( $get( 'country' ) ),
		'level'   => sanitize_key( $get( 'level' ) ),
		'major'   => sanitize_key( $get( 'major' ) ),
		'fee'     => sanitize_key( $get( 'fee' ) ),
		'sort'    => in_array( $sort, [ 'featured', 'title' ], true ) ? $sort : 'featured',
		'page'    => max( 1, $page ),
	];
}

/** Có bộ lọc nào đang bật không (dùng để noindex trang kết quả lọc). */
function duy_finder_has_filters( array $query ): bool {
	foreach ( [ 'q', 'country', 'level', 'major', 'fee' ] as $key ) {
		if ( '' !== (string) ( $query[ $key ] ?? '' ) ) {
			return true;
		}
	}

	return false;
}

/** Lọc danh sách trường theo query — giữ đúng ngữ nghĩa của bộ lọc phía JS trước đây. */
function duy_finder_filter_schools( array $schools, array $query ): array {
	$keyword = mb_strtolower( trim( (string) ( $query['q'] ?? '' ) ) );

	return array_values(
		array_filter(
			$schools,
			static function ( array $item ) use ( $query, $keyword ): bool {
				if ( '' !== $query['country'] && duy_country_slug( (string) ( $item['c'] ?? '' ) ) !== $query['country'] ) {
					return false;
				}
				if ( '' !== $query['level'] && duy_slugify( (string) ( $item['level'] ?? '' ) ) !== $query['level'] ) {
					return false;
				}
				if ( '' !== $query['major'] && duy_slugify( (string) ( $item['major'] ?? '' ) ) !== $query['major'] ) {
					return false;
				}
				if ( '' !== $query['fee'] && (string) ( $item['feeBand'] ?? $item['fee_band'] ?? '' ) !== $query['fee'] ) {
					return false;
				}
				if ( '' !== $keyword ) {
					$haystack = mb_strtolower( implode( ' ', array_filter( [ $item['n'] ?? '', $item['city'] ?? '', $item['c'] ?? '', $item['level'] ?? '', $item['major'] ?? '', $item['r'] ?? '', $item['fee'] ?? '' ], 'is_string' ) ) );
					if ( ! str_contains( $haystack, $keyword ) ) {
						return false;
					}
				}

				return true;
			}
		)
	);
}

/**
 * Thứ tự "nổi bật trước, mới sau" của toàn bộ trường, cache 12 giờ.
 * Tính trực tiếp phải tra post + Carbon meta cho từng trường (~4 giây/572 trường),
 * trong khi thứ tự này chỉ đổi khi có trường được thêm/sửa.
 *
 * @return array{order:string[],featured:string[]}
 */
function duy_school_order_cache( array $schools ): array {
	$cached = get_transient( 'duy_school_order_v2' );
	if ( is_array( $cached ) && isset( $cached['order'], $cached['featured'] ) && count( $cached['order'] ) === count( $schools ) ) {
		return $cached;
	}

	$sorted   = duy_items_featured_then_newest( $schools, 'school' );
	$order    = [];
	$featured = [];
	foreach ( $sorted as $item ) {
		$id = (string) ( $item['id'] ?? '' );
		if ( '' === $id ) {
			continue;
		}
		$order[] = $id;
		if ( duy_item_is_featured( $item, 'school' ) ) {
			$featured[] = $id;
		}
	}

	$cached = [ 'order' => $order, 'featured' => $featured ];
	set_transient( 'duy_school_order_v2', $cached, 12 * HOUR_IN_SECONDS );

	return $cached;
}

/** Trường nổi bật cho dải đầu trang /truong/ (dùng thứ tự đã cache). */
function duy_school_featured_cards( array $schools, int $limit = 6 ): array {
	$cache = duy_school_order_cache( $schools );
	$index = [];
	foreach ( $schools as $item ) {
		$index[ (string) ( $item['id'] ?? '' ) ] = $item;
	}

	$picked = array_values( array_filter( array_map( static fn( string $id ) => $index[ $id ] ?? null, $cache['featured'] ) ) );
	if ( ! $picked ) {
		$picked = array_values( array_filter( array_map( static fn( string $id ) => $index[ $id ] ?? null, $cache['order'] ) ) );
	}

	return array_slice( $picked, 0, $limit );
}

/**
 * Trường tương tự để liên kết chéo giữa các trang trường: cùng quốc gia và bậc học,
 * nếu không đủ thì lấy thêm trường cùng quốc gia. Giúp crawler đi sâu mà không phải
 * quay lại trang /truong/, và giúp người đọc so sánh nhanh.
 */
function duy_related_school_cards( string $country_name, string $level_label, string $exclude_id, int $limit = 3 ): array {
	$schools = duy_demo_schools();
	$pick    = static function ( bool $match_level ) use ( $schools, $country_name, $level_label, $exclude_id ): array {
		return array_values(
			array_filter(
				$schools,
				static function ( array $item ) use ( $country_name, $level_label, $exclude_id, $match_level ): bool {
					if ( (string) ( $item['id'] ?? '' ) === $exclude_id || (string) ( $item['c'] ?? '' ) !== $country_name ) {
						return false;
					}

					return ! $match_level || (string) ( $item['level'] ?? '' ) === $level_label;
				}
			)
		);
	};

	$items = $pick( '' !== $level_label );
	if ( count( $items ) < $limit ) {
		foreach ( $pick( false ) as $item ) {
			if ( ! in_array( $item, $items, true ) ) {
				$items[] = $item;
			}
		}
	}

	shuffle( $items );

	return duy_public_value( array_slice( $items, 0, $limit ) );
}

function duy_finder_sort_items( array $items, string $mode, string $post_type = 'school' ): array {
	if ( 'title' === $mode ) {
		usort( $items, static fn( array $a, array $b ): int => strnatcasecmp( (string) ( $a['n'] ?? '' ), (string) ( $b['n'] ?? '' ) ) );

		return $items;
	}

	if ( 'school' === $post_type ) {
		$order = array_flip( duy_school_order_cache( duy_demo_schools() )['order'] );
		usort(
			$items,
			static fn( array $a, array $b ): int => ( $order[ (string) ( $a['id'] ?? '' ) ] ?? PHP_INT_MAX ) <=> ( $order[ (string) ( $b['id'] ?? '' ) ] ?? PHP_INT_MAX )
		);

		return $items;
	}

	return duy_items_featured_then_newest( $items, $post_type );
}

/**
 * Cắt trang.
 *
 * @return array{items:array,page:int,pages:int,total:int,from:int,to:int}
 */
function duy_paginate( array $items, int $page = 1, int $per_page = DUY_FINDER_PER_PAGE ): array {
	$total = count( $items );
	$pages = max( 1, (int) ceil( $total / max( 1, $per_page ) ) );
	$page  = min( max( 1, $page ), $pages );
	$from  = $total ? ( $page - 1 ) * $per_page + 1 : 0;

	return [
		'items' => array_slice( $items, ( $page - 1 ) * $per_page, $per_page ),
		'page'  => $page,
		'pages' => $pages,
		'total' => $total,
		'from'  => $from,
		'to'    => min( $total, $page * $per_page ),
	];
}

/** URL của một trang kết quả, giữ nguyên các tham số lọc đang bật. */
function duy_finder_page_url( string $base_path, array $query, int $page ): string {
	$args = [];
	foreach ( [ 'q', 'country', 'level', 'major', 'fee' ] as $key ) {
		if ( '' !== (string) ( $query[ $key ] ?? '' ) ) {
			$args[ $key ] = (string) $query[ $key ];
		}
	}
	if ( 'featured' !== ( $query['sort'] ?? 'featured' ) ) {
		$args['sort'] = (string) $query['sort'];
	}
	if ( $page > 1 ) {
		$args['trang'] = $page;
	}

	$url = duy_route_path( $base_path );

	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Thanh phân trang: link thật để crawler đi tiếp, có aria-label và trang hiện tại.
 * Hiện tối đa 2 trang lân cận + trang đầu/cuối.
 */
function duy_pagination_markup( string $base_path, array $query, array $page_data ): string {
	if ( ( $page_data['pages'] ?? 1 ) < 2 ) {
		return '<nav class="pagination" data-finder-pagination aria-label="Phân trang"></nav>';
	}

	$current = (int) $page_data['page'];
	$pages   = (int) $page_data['pages'];
	$link    = static function ( int $page, string $label, string $class = '', string $aria = '' ) use ( $base_path, $query ): string {
		return sprintf(
			'<a class="page-link%1$s" href="%2$s"%3$s>%4$s</a>',
			$class ? ' ' . esc_attr( $class ) : '',
			esc_url( duy_finder_page_url( $base_path, $query, $page ) ),
			$aria ? ' aria-label="' . esc_attr( $aria ) . '"' : '',
			esc_html( $label )
		);
	};

	$parts = [];
	if ( $current > 1 ) {
		$parts[] = $link( $current - 1, '‹ Trước', 'page-prev', 'Trang trước' );
	}

	$window = [ 1, $pages ];
	for ( $i = $current - 1; $i <= $current + 1; $i++ ) {
		if ( $i > 0 && $i <= $pages ) {
			$window[] = $i;
		}
	}
	$window = array_values( array_unique( $window ) );
	sort( $window );

	$previous = 0;
	foreach ( $window as $page ) {
		if ( $previous && $page - $previous > 1 ) {
			$parts[] = '<span class="page-gap" aria-hidden="true">…</span>';
		}
		$parts[]  = $page === $current
			? '<span class="page-link is-current" aria-current="page">' . esc_html( (string) $page ) . '</span>'
			: $link( $page, (string) $page, '', 'Trang ' . $page );
		$previous = $page;
	}

	if ( $current < $pages ) {
		$parts[] = $link( $current + 1, 'Sau ›', 'page-next', 'Trang sau' );
	}

	return '<nav class="pagination" data-finder-pagination aria-label="Phân trang">' . implode( '', $parts ) . '</nav>';
}

/** Dòng "Hiển thị x–y trong z" cho khối kết quả. */
function duy_result_range_text( array $page_data, string $noun = 'trường' ): string {
	if ( ! $page_data['total'] ) {
		return 'Không có ' . $noun . ' phù hợp';
	}

	if ( $page_data['pages'] < 2 ) {
		return sprintf( '%d %s', $page_data['total'], $noun );
	}

	return sprintf( '%d–%d trong %d %s', $page_data['from'], $page_data['to'], $page_data['total'], $noun );
}
