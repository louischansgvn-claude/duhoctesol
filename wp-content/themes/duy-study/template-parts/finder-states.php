<?php
/**
 * Finder states.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type = (string) duy_get_arg( $args, 'type', 'schools' );
?>
<div class="grid g3" data-finder-grid="<?php echo esc_attr( $type ); ?>">
	<div class="skeleton-card glass" aria-hidden="true"></div>
	<div class="skeleton-card glass" aria-hidden="true"></div>
	<div class="skeleton-card glass" aria-hidden="true"></div>
</div>
<div class="empty-state glass" data-finder-empty="<?php echo esc_attr( $type ); ?>" hidden>
	<?php echo duy_icon_tile( 'search' ); ?>
	<h3><?php esc_html_e( 'Không tìm thấy kết quả phù hợp.', 'duy-study' ); ?></h3>
	<p class="muted"><?php esc_html_e( 'Thử đổi quốc gia, chủ đề hoặc xóa từ khóa để xem thêm lựa chọn.', 'duy-study' ); ?></p>
	<button class="btn btn-ghost btn-sm" type="button" data-filter-reset><?php esc_html_e( 'Xóa bộ lọc', 'duy-study' ); ?></button>
</div>
<div class="finder-actions"><button class="btn btn-ghost" type="button" data-load-more="<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Tải thêm', 'duy-study' ); ?></button></div>
