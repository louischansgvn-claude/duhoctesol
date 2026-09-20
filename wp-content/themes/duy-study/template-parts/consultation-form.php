<?php
/**
 * Consultation form.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title   = (string) duy_get_arg( $args, 'title', 'Đăng ký ngay để tư vấn 1:1 cùng chuyên gia!' );
$source = (string) duy_get_arg( $args, 'source', '' );
$badge   = (string) duy_get_arg( $args, 'badge', 'Hoàn toàn miễn phí' );
$form_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'duyConsult' ) : 'duyConsult' . wp_rand( 1000, 9999 );
$year_now = (int) date_i18n( 'Y' );
?>
<form class="form-card glass glass-strong<?php echo $badge ? ' has-burst' : ''; ?>" data-duy-consultation-form novalidate>
	<?php if ( $badge ) : ?>
		<span class="free-burst"><span><?php echo esc_html( $badge ); ?></span></span>
	<?php endif; ?>
	<h3 style="font-size:1.35rem;margin-bottom:.35rem"><?php echo esc_html( $title ); ?></h3>
	<p class="muted" style="margin-bottom:1rem"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM sẽ liên hệ để gợi ý lộ trình phù hợp với hồ sơ của bạn.', 'duy-study' ); ?></p>
	<input type="text" name="website" autocomplete="off" tabindex="-1" class="hp" aria-hidden="true" hidden>
	<input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>">
	<div class="field role-field">
		<label id="<?php echo esc_attr( $form_id . 'RoleLabel' ); ?>"><?php esc_html_e( 'Bạn là', 'duy-study' ); ?></label>
		<div class="role-toggle" role="group" aria-labelledby="<?php echo esc_attr( $form_id . 'RoleLabel' ); ?>">
			<button class="active" type="button" data-role="student" aria-pressed="true"><?php esc_html_e( 'Học sinh', 'duy-study' ); ?></button>
			<button type="button" data-role="parent" aria-pressed="false"><?php esc_html_e( 'Phụ huynh', 'duy-study' ); ?></button>
		</div>
	</div>
	<input type="hidden" name="role" value="student">
	<div class="form-grid">
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Name' ); ?>"><?php esc_html_e( 'Họ tên', 'duy-study' ); ?> <span class="req">*</span></label>
			<input id="<?php echo esc_attr( $form_id . 'Name' ); ?>" name="name" required autocomplete="name">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Phone' ); ?>"><?php esc_html_e( 'Số điện thoại', 'duy-study' ); ?> <span class="req">*</span></label>
			<input id="<?php echo esc_attr( $form_id . 'Phone' ); ?>" name="phone" required autocomplete="tel" inputmode="tel">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Email' ); ?>"><?php esc_html_e( 'Email', 'duy-study' ); ?> <span class="req">*</span></label>
			<input id="<?php echo esc_attr( $form_id . 'Email' ); ?>" name="email" required type="email" autocomplete="email">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'BirthYear' ); ?>"><?php esc_html_e( 'Năm sinh', 'duy-study' ); ?></label>
			<select id="<?php echo esc_attr( $form_id . 'BirthYear' ); ?>" name="birth_year">
				<option value=""><?php esc_html_e( 'Chọn năm sinh', 'duy-study' ); ?></option>
				<?php for ( $year = $year_now - 12; $year >= $year_now - 70; $year-- ) : ?>
					<option value="<?php echo esc_attr( (string) $year ); ?>"><?php echo esc_html( (string) $year ); ?></option>
				<?php endfor; ?>
			</select>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Country' ); ?>"><?php esc_html_e( 'Quốc gia quan tâm', 'duy-study' ); ?></label>
			<select id="<?php echo esc_attr( $form_id . 'Country' ); ?>" name="country">
				<?php foreach ( duy_country_options() as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'EnglishCertificate' ); ?>"><?php esc_html_e( 'Chứng chỉ tiếng Anh', 'duy-study' ); ?></label>
			<input id="<?php echo esc_attr( $form_id . 'EnglishCertificate' ); ?>" name="english_certificate" placeholder="<?php esc_attr_e( 'Ví dụ: IELTS 6.5 hoặc Chưa có', 'duy-study' ); ?>" autocomplete="off">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Location' ); ?>"><?php esc_html_e( 'Nơi ở', 'duy-study' ); ?></label>
			<input id="<?php echo esc_attr( $form_id . 'Location' ); ?>" name="location" placeholder="<?php esc_attr_e( 'Tỉnh/thành hoặc địa chỉ hiện tại', 'duy-study' ); ?>" autocomplete="address-level1">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Time' ); ?>"><?php esc_html_e( 'Dự định nhập học', 'duy-study' ); ?></label>
			<input id="<?php echo esc_attr( $form_id . 'Time' ); ?>" name="intended_time" placeholder="<?php esc_attr_e( 'Ví dụ: 09/2026', 'duy-study' ); ?>">
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id . 'Office' ); ?>"><?php esc_html_e( 'Văn phòng gần nhất', 'duy-study' ); ?></label>
			<select id="<?php echo esc_attr( $form_id . 'Office' ); ?>" name="office">
				<?php foreach ( duy_office_options() as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="field full">
			<label for="<?php echo esc_attr( $form_id . 'Note' ); ?>"><?php esc_html_e( 'Ghi chú hồ sơ', 'duy-study' ); ?></label>
			<textarea id="<?php echo esc_attr( $form_id . 'Note' ); ?>" name="message" rows="3"></textarea>
		</div>
	</div>
	<div class="form-msg" data-form-message aria-live="polite"></div>
	<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Gửi thông tin', 'duy-study' ); ?></button>
</form>
