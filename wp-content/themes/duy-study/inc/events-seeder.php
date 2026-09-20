<?php
/**
 * Event-recap seeder for non-production.
 *
 * Imports the bundled dataset (inc/data/events/events-import.json) into the
 * `event` CPT. Featured image = ảnh cover đã upload sẵn tại
 * /wp-content/uploads/events/<file>.
 *
 * Chỉ tạo/sửa bài `event` mang meta `_duy_ev_id`; trash sweep cũng chỉ quét
 * đúng nhóm này, nên toàn bộ dữ liệu trường học (school CPT) không bị ảnh hưởng.
 *
 * Idempotent + non-destructive on the featured image (never force-deletes the
 * file it is about to re-use).
 *
 * Trigger (logged-in admin, non-public site only):
 *   /wp-admin/?duy_ev_seed=1&token=<DUY_HS_SEED_TOKEN>
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_ev_seed_run(): void {
	if ( ! isset( $_GET['duy_ev_seed'] ) ) {
		return;
	}
	if ( ( 'production' === wp_get_environment_type() ) && ( '0' !== (string) get_option( 'blog_public' ) ) ) {
		wp_die( 'Seeder disabled: refusing to run on a public production site.' );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Seeder requires an administrator login.' );
	}
	$token = defined( 'DUY_HS_SEED_TOKEN' ) ? DUY_HS_SEED_TOKEN : '';
	if ( ! $token || ( $_GET['token'] ?? '' ) !== $token ) {
		wp_die( 'Invalid seed token.' );
	}

	$report = duy_ev_seed_import();
	if ( is_string( $report ) ) {
		wp_die( esc_html( $report ) );
	}
	wp_die( '<pre>' . esc_html( duy_ev_seed_report_text( $report ) ) . '</pre>', 'Event seed', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_ev_seed_run' );

/**
 * Format a seed result array as a plain-text report.
 */
function duy_ev_seed_report_text( array $r ): string {
	return sprintf(
		"EVENT SEED DONE\nRows: %d\nImported/updated: %d\nTrashed: %d\nGallery images: %d\nMissing thumbnail: %d\nErrors: %d\n%s",
		$r['rows'] ?? 0,
		$r['imported'] ?? 0,
		$r['trashed'] ?? 0,
		$r['images'] ?? 0,
		$r['missing_thumbnail'] ?? 0,
		count( $r['errors'] ?? [] ),
		implode( "\n", $r['errors'] ?? [] )
	);
}

/**
 * Core import routine — shared by the admin-hook trigger and CLI bootstrap.
 * Idempotent. Returns a result array, or an error string on a fatal precondition.
 *
 * @return array|string
 */
function duy_ev_seed_import() {
	$file = get_stylesheet_directory() . '/inc/data/events/events-import.json';
	if ( ! is_readable( $file ) ) {
		return 'Dataset not found: ' . $file;
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		return 'Dataset unreadable.';
	}

	$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
	$author = $admins ? (int) $admins[0] : get_current_user_id();
	$updir  = wp_upload_dir();

	$imp = 0; $miss_thumb = 0; $images = 0; $keep = []; $err = [];

	foreach ( $rows as $r ) {
		$eid = (string) ( $r['event_id'] ?? '' );
		if ( '' === $eid ) {
			$err[] = 'row without event_id skipped';
			continue;
		}
		$keep[ $eid ] = true;
		try {
			$ex  = get_posts( [ 'post_type' => 'event', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => [ [ 'key' => '_duy_ev_id', 'value' => $eid ] ] ] );
			$pid = $ex ? (int) $ex[0] : 0;
			if ( ! $pid && ! empty( $r['post_slug'] ) ) {
				$p   = get_page_by_path( $r['post_slug'], OBJECT, 'event' );
				$pid = $p ? (int) $p->ID : 0;
			}

			$arr = [
				'post_type'    => 'event',
				'post_status'  => 'publish',
				'post_author'  => $author,
				'post_title'   => (string) ( $r['post_title'] ?? '' ),
				'post_excerpt' => (string) ( $r['excerpt'] ?? '' ),
				'post_content' => (string) ( $r['content_html'] ?? '' ),
			];
			if ( $pid ) {
				$arr['ID'] = $pid;
			} else {
				$arr['post_name'] = (string) ( $r['post_slug'] ?? '' );
			}
			$pid = (int) wp_insert_post( $arr, true );
			if ( ! $pid ) {
				throw new RuntimeException( 'insert failed' );
			}

			update_post_meta( $pid, '_duy_ev_id', $eid );
			update_post_meta( $pid, '_duy_ev_recap', 1 );
			update_post_meta( $pid, '_duy_ev_date_text', (string) ( $r['date_text'] ?? '' ) );
			update_post_meta( $pid, '_duy_ev_datetime', (string) ( $r['datetime'] ?? '' ) );
			update_post_meta( $pid, '_duy_ev_type', (string) ( $r['type'] ?? 'Hội thảo' ) );
			update_post_meta( $pid, '_duy_ev_source_url', (string) ( $r['source_url'] ?? '' ) );
			update_post_meta( $pid, '_duy_ev_images', implode( "\n", (array) ( $r['images'] ?? [] ) ) );

			$images += count( (array) ( $r['images'] ?? [] ) );

			// Featured image = ảnh cover đã upload sẵn tại /uploads/events/.
			// Idempotent + non-destructive: nếu featured image hiện tại đã trỏ
			// đúng file này thì không làm gì (không xoá rồi chèn lại chính file
			// sắp dùng — đó là nguyên nhân ảnh 404 sau lần seed thứ hai).
			$base  = (string) ( $r['cover_base'] ?? '' );
			$fpath = $updir['basedir'] . '/events/' . $base;
			if ( $base && file_exists( $fpath ) ) {
				$cur      = (int) get_post_thumbnail_id( $pid );
				$cur_file = $cur ? (string) get_attached_file( $cur ) : '';
				$cur_ok   = $cur_file && basename( $cur_file ) === $base && file_exists( $cur_file );
				if ( ! $cur_ok ) {
					foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $pid, 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_ev_thumb' ] ) as $old ) {
						if ( basename( (string) get_attached_file( (int) $old ) ) === $base ) {
							continue;
						}
						wp_delete_attachment( (int) $old, true );
					}
					$type = wp_check_filetype( $base, null )['type'] ?: 'image/jpeg';
					$aid  = wp_insert_attachment(
						[
							'post_mime_type' => $type,
							'post_title'     => (string) ( $r['cover_alt'] ?? '' ),
							'post_status'    => 'inherit',
							'guid'           => $updir['baseurl'] . '/events/' . $base,
						],
						$fpath,
						$pid,
						true
					);
					if ( ! is_wp_error( $aid ) ) {
						update_post_meta( (int) $aid, '_wp_attached_file', 'events/' . $base );
						update_post_meta( (int) $aid, '_wp_attachment_image_alt', (string) ( $r['cover_alt'] ?? '' ) );
						update_post_meta( (int) $aid, '_duy_ev_thumb', 1 );
						set_post_thumbnail( $pid, (int) $aid );
					}
				}
			} else {
				$miss_thumb++;
			}
			$imp++;
		} catch ( Throwable $e ) {
			$err[] = "$eid: " . $e->getMessage();
		}
	}

	// Trash imported event recaps no longer in the dataset.
	// Scoped strictly to `_duy_ev_id` posts.
	$trashed = 0;
	foreach ( get_posts( [ 'post_type' => 'event', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_ev_id' ] ) as $id ) {
		$eid = (string) get_post_meta( $id, '_duy_ev_id', true );
		if ( ! isset( $keep[ $eid ] ) ) {
			wp_trash_post( $id );
			$trashed++;
		}
	}

	return [
		'rows'              => count( $rows ),
		'imported'          => $imp,
		'trashed'           => $trashed,
		'images'            => $images,
		'missing_thumbnail' => $miss_thumb,
		'errors'            => $err,
	];
}
