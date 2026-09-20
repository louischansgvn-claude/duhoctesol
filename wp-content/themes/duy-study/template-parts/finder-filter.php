<?php
/**
 * Finder filter sidebar.
 *
 * Hai chế độ:
 * - mặc định: lọc phía client (JS đọc data-filter-* và lọc thẻ trong DOM).
 * - server: bọc trong <form method="get">, input có name thật → lọc phía server,
 *   hoạt động cả khi tắt JS; JS chỉ nạp lại phần kết quả bằng fetch.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type    = (string) duy_get_arg( $args, 'type', 'schools' );
$heading = (string) duy_get_arg( $args, 'heading', 'Bộ lọc' );
$action  = (string) duy_get_arg( $args, 'action', '' );
$server  = '' !== $action;
$query   = $server ? duy_finder_query() : [];
$preset  = $server ? (string) $query['country'] : duy_current_country_filter();

/** name="..." cho chế độ server, giữ name cũ cho chế độ client. */
$field = static function ( string $param, string $fallback ) use ( $server ): string {
	return $server ? $param : $fallback;
};
$value_of = static fn( string $key ): string => (string) ( $query[ $key ] ?? '' );
?>
<aside class="filter glass" data-finder-filter="<?php echo esc_attr( $type ); ?>">
	<?php if ( $server ) : ?>
		<form id="<?php echo esc_attr( $type ); ?>FilterForm" method="get" action="<?php echo esc_url( $action ); ?>" data-finder-form>
	<?php endif; ?>
	<h4><?php echo esc_html( $heading ); ?></h4>
	<div class="filter-group">
		<label for="<?php echo esc_attr( $type ); ?>Keyword"><?php esc_html_e( 'Từ khóa', 'duy-study' ); ?></label>
		<input id="<?php echo esc_attr( $type ); ?>Keyword" data-filter-keyword type="search"
			<?php if ( $server ) : ?>name="q" value="<?php echo esc_attr( $value_of( 'q' ) ); ?>"<?php endif; ?>
			placeholder="<?php esc_attr_e( 'Nhập tên hoặc ngành', 'duy-study' ); ?>">
	</div>
	<?php if ( in_array( $type, [ 'schools', 'scholarships' ], true ) ) : ?>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Quốc gia', 'duy-study' ); ?></h4>
			<?php foreach ( duy_country_options() as $slug => $label ) : ?>
				<label>
					<input data-filter-country type="radio" name="<?php echo esc_attr( $field( 'country', $type . '_country' ) ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $preset, $slug ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( 'schools' === $type ) : ?>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Bậc học', 'duy-study' ); ?></h4>
			<?php foreach ( [ '' => 'Tất cả', 'dai-hoc' => 'Đại học', 'cao-dang' => 'Cao đẳng', 'thpt' => 'THPT', 'anh-ngu' => 'Anh ngữ' ] as $slug => $label ) : ?>
				<label><input data-filter-level type="radio" name="<?php echo esc_attr( $field( 'level', 'school_level' ) ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $value_of( 'level' ), $slug ); ?>> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</div>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Ngành', 'duy-study' ); ?></h4>
			<?php foreach ( [ '' => 'Tất cả', 'cntt' => 'CNTT', 'kinh-doanh' => 'Kinh doanh', 'ky-thuat' => 'Kỹ thuật', 'y-suc-khoe' => 'Y - Sức khỏe' ] as $slug => $label ) : ?>
				<label><input data-filter-major type="radio" name="<?php echo esc_attr( $field( 'major', 'school_major' ) ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $value_of( 'major' ), $slug ); ?>> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</div>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Học phí', 'duy-study' ); ?></h4>
			<?php foreach ( [ '' => 'Tất cả', 'low' => 'Thấp', 'mid' => 'Trung bình', 'high' => 'Cao' ] as $slug => $label ) : ?>
				<label><input data-filter-fee type="radio" name="<?php echo esc_attr( $field( 'fee', 'school_fee' ) ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $value_of( 'fee' ), $slug ); ?>> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( 'events' === $type ) : ?>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Loại sự kiện', 'duy-study' ); ?></h4>
			<?php foreach ( [ '' => 'Tất cả', 'online' => 'Online', 'van-phong' => 'Văn phòng', 'hoi-thao' => 'Hội thảo' ] as $slug => $label ) : ?>
				<label><input data-filter-event type="radio" name="event_type" value="<?php echo esc_attr( $slug ); ?>"> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( 'news' === $type ) : ?>
		<div class="filter-group">
			<h4><?php esc_html_e( 'Chủ đề', 'duy-study' ); ?></h4>
			<?php foreach ( [ '' => 'Tất cả', 'visa' => 'Visa', 'hoc-bong' => 'Học bổng', 'kinh-nghiem' => 'Kinh nghiệm' ] as $slug => $label ) : ?>
				<label><input data-filter-news type="radio" name="news_cat" value="<?php echo esc_attr( $slug ); ?>"> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( $server ) : ?>
			<div class="filter-actions">
				<button class="btn btn-primary btn-sm" type="submit"><?php esc_html_e( 'Áp dụng', 'duy-study' ); ?></button>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( $action ); ?>" data-filter-reset><?php esc_html_e( 'Xoá lọc', 'duy-study' ); ?></a>
			</div>
		</form>
	<?php else : ?>
		<button class="btn btn-ghost btn-sm" type="button" data-filter-reset><?php esc_html_e( 'Reset lọc', 'duy-study' ); ?></button>
	<?php endif; ?>
</aside>
