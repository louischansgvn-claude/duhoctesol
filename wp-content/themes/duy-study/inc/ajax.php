<?php
/**
 * AJAX handlers.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_ajax_post_text( string $key ): string {
	return sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
}

function duy_ajax_post_textarea( string $key ): string {
	return sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? '' ) );
}

function duy_lead_set_meta( int $lead_id, string $key, $value ): void {
	// Lead fields reuse names like `country` that other Carbon containers define
	// as associations, so write Carbon's storage key directly for this CPT.
	update_post_meta( $lead_id, '_' . $key, $value );
}

function duy_consultation_rate_limit_key(): string {
	$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );

	return 'duy_consult_' . md5( $ip );
}

function duy_consultation_is_rate_limited(): bool {
	$key   = duy_consultation_rate_limit_key();
	$count = (int) get_transient( $key );

	if ( $count >= 5 ) {
		return true;
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	return false;
}

function duy_ajax_strlen( string $value ): int {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

function duy_send_lead_email( array $lead ): bool {
	$to = sanitize_email( (string) duy_option( 'email', 'louischan.sgvn@gmail.com' ) );
	if ( ! is_email( $to ) ) {
		return false;
	}

	$subject = sprintf( '[Ban Du học Hội TESOL TP.HCM] Lead tư vấn mới - %s', $lead['name'] );
	$lines   = [
		'Lead tư vấn mới từ website Ban Du học Hội TESOL TP.HCM.',
		'',
		'Họ tên: ' . $lead['name'],
		'Điện thoại: ' . $lead['phone'],
		'Email: ' . $lead['email'],
		'Vai trò: ' . $lead['role_label'],
		'Năm sinh: ' . ( $lead['birth_year'] ?: 'Chưa nhập' ),
		'Chứng chỉ tiếng Anh: ' . ( $lead['english_certificate'] ?: 'Chưa nhập' ),
		'Nơi ở: ' . ( $lead['location'] ?: 'Chưa nhập' ),
		'Quốc gia quan tâm: ' . ( $lead['country_label'] ?: 'Chưa chọn' ),
		'Dự định nhập học: ' . ( $lead['intended_time'] ?: 'Chưa nhập' ),
		'Văn phòng gần nhất: ' . ( $lead['office_label'] ?: 'Chưa chọn' ),
		'Nguồn form: ' . $lead['source'],
		'URL gửi form: ' . $lead['referer'],
		'',
		'Ghi chú:',
		$lead['message'] ?: 'Không có',
	];
	$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

	if ( is_email( $lead['email'] ) ) {
		$headers[] = sprintf( 'Reply-To: %s <%s>', $lead['name'], $lead['email'] );
	}

	return wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
}

function duy_handle_consultation(): void {
	check_ajax_referer( 'duy_ajax', 'nonce' );

	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( [ 'message' => 'OK' ] );
	}

	if ( duy_consultation_is_rate_limited() ) {
		wp_send_json_error( [ 'message' => 'Bạn đã gửi hơi nhanh. Vui lòng thử lại sau ít phút hoặc gọi hotline để được hỗ trợ ngay.' ], 429 );
	}

	$name          = duy_ajax_post_text( 'name' );
	$email         = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone         = duy_ajax_post_text( 'phone' );
	$phone_digits  = duy_phone_digits( $phone );
	$birth_year    = duy_ajax_post_text( 'birth_year' );
	$english_certificate = duy_ajax_post_text( 'english_certificate' );
	$location      = duy_ajax_post_text( 'location' );
	$country       = duy_ajax_post_text( 'country' );
	$intended_time = duy_ajax_post_text( 'intended_time' );
	$office        = duy_ajax_post_text( 'office' );
	$message       = duy_ajax_post_textarea( 'message' );
	$source        = duy_ajax_post_text( 'source' );
	$referer       = wp_get_referer() ?: home_url( add_query_arg( [] ) );
	$role          = duy_ajax_post_text( 'role' );
	$roles         = [
		'student' => 'Học sinh',
		'parent'  => 'Phụ huynh',
	];
	$countries      = duy_country_options();
	$office_options = duy_office_options();

	if ( ! isset( $roles[ $role ] ) ) {
		wp_send_json_error( [ 'message' => 'Vai trò chưa hợp lệ.' ], 422 );
	}

	if ( ! array_key_exists( $country, $countries ) ) {
		wp_send_json_error( [ 'message' => 'Quốc gia quan tâm chưa hợp lệ.' ], 422 );
	}

	if ( ! isset( $office_options[ $office ] ) ) {
		wp_send_json_error( [ 'message' => 'Văn phòng gần nhất chưa hợp lệ.' ], 422 );
	}

	if ( '' === $source ) {
		$source = sanitize_text_field( wp_parse_url( $referer, PHP_URL_PATH ) ?: 'unknown' );
	}

	if ( '' === $name || strlen( $name ) < 2 ) {
		wp_send_json_error( [ 'message' => 'Vui lòng nhập họ tên.' ], 422 );
	}

	if ( duy_ajax_strlen( $name ) > 120 ) {
		wp_send_json_error( [ 'message' => 'Họ tên quá dài.' ], 422 );
	}

	if ( duy_ajax_strlen( $email ) > 160 ) {
		wp_send_json_error( [ 'message' => 'Email quá dài.' ], 422 );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Email chưa đúng định dạng.' ], 422 );
	}

	if ( duy_ajax_strlen( $phone ) > 32 ) {
		wp_send_json_error( [ 'message' => 'Số điện thoại quá dài.' ], 422 );
	}

	if ( strlen( $phone_digits ) < 8 || strlen( $phone_digits ) > 15 ) {
		wp_send_json_error( [ 'message' => 'Số điện thoại chưa hợp lệ.' ], 422 );
	}

	if ( '' !== $birth_year ) {
		$year     = (int) $birth_year;
		$min_year = (int) date_i18n( 'Y' ) - 70;
		$max_year = (int) date_i18n( 'Y' ) - 12;
		if ( (string) $year !== $birth_year || $year < $min_year || $year > $max_year ) {
			wp_send_json_error( [ 'message' => 'Năm sinh chưa hợp lệ.' ], 422 );
		}
	}

	if ( duy_ajax_strlen( $english_certificate ) > 80 ) {
		wp_send_json_error( [ 'message' => 'Thông tin chứng chỉ tiếng Anh quá dài.' ], 422 );
	}

	if ( duy_ajax_strlen( $location ) > 160 ) {
		wp_send_json_error( [ 'message' => 'Thông tin nơi ở quá dài.' ], 422 );
	}

	if ( duy_ajax_strlen( $intended_time ) > 80 ) {
		wp_send_json_error( [ 'message' => 'Thông tin dự định nhập học quá dài.' ], 422 );
	}

	if ( duy_ajax_strlen( $message ) > 2000 ) {
		wp_send_json_error( [ 'message' => 'Ghi chú hồ sơ quá dài.' ], 422 );
	}

	if ( duy_ajax_strlen( $source ) > 120 ) {
		$source = substr( $source, 0, 120 );
	}

	$lead_id = wp_insert_post(
		[
			'post_type'   => 'lead',
			'post_status' => 'publish',
			'post_title'  => sprintf( 'Lead - %s - %s', $name, current_time( 'Y-m-d H:i' ) ),
		],
		true
	);

	if ( is_wp_error( $lead_id ) ) {
		wp_send_json_error( [ 'message' => 'Chưa lưu được thông tin. Vui lòng thử lại sau.' ], 500 );
	}

	$lead = [
		'name'          => $name,
		'phone'         => $phone,
		'email'         => $email,
		'birth_year'    => $birth_year,
		'english_certificate' => $english_certificate,
		'location'      => $location,
		'country'       => $country,
		'country_label' => $countries[ $country ] ?? '',
		'intended_time' => $intended_time,
		'office'        => $office,
		'office_label'  => $office_options[ $office ],
		'role'          => $role,
		'role_label'    => $roles[ $role ],
		'message'       => $message,
		'source'        => $source,
		'referer'       => $referer,
		'status'        => 'new',
	];

	foreach ( [ 'name', 'phone', 'email', 'birth_year', 'english_certificate', 'location', 'country', 'country_label', 'intended_time', 'office', 'office_label', 'role', 'message', 'source', 'status' ] as $field ) {
		duy_lead_set_meta( (int) $lead_id, $field, $lead[ $field ] );
	}
	update_post_meta( (int) $lead_id, 'referer', esc_url_raw( $referer ) );
	update_post_meta( (int) $lead_id, 'phone_digits', $phone_digits );

	$mail_sent = duy_send_lead_email( $lead );
	update_post_meta( (int) $lead_id, 'email_sent', $mail_sent ? '1' : '0' );

	wp_send_json_success(
		[
			'message'    => 'Cảm ơn bạn. Ban Du học Hội TESOL TP.HCM đã ghi nhận thông tin và sẽ liên hệ sớm.',
			'email_sent' => $mail_sent,
		]
	);
}
add_action( 'wp_ajax_duy_consultation', 'duy_handle_consultation' );
add_action( 'wp_ajax_nopriv_duy_consultation', 'duy_handle_consultation' );
