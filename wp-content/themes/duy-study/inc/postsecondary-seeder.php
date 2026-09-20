<?php
/**
 * US college / university (postsecondary) seeder for non-production.
 *
 * Imports the bundled dataset (inc/data/postsecondary/postsecondary-import.json)
 * into the `school` CPT as public-clean SEO articles, tagged country=Mỹ +
 * study_level cao-dang|dai-hoc. Featured image = the school logo pre-uploaded to
 * /wp-content/uploads/postsecondary/<file>. Idempotent + non-destructive on the
 * featured image (never force-deletes the file it is about to re-use).
 *
 * Trigger (logged-in admin, non-public site only):
 *   /wp-admin/?duy_ps_seed=1&token=<DUY_HS_SEED_TOKEN>
 *
 * SAFETY: refuses on a public production site and without the token. Remove this
 * file (and inc/data/postsecondary/) before shipping the theme to production.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_ps_seed_run(): void {
	if ( ! isset( $_GET['duy_ps_seed'] ) ) {
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

	$report = duy_ps_seed_import();
	if ( is_string( $report ) ) {
		wp_die( esc_html( $report ) );
	}
	wp_die( '<pre>' . esc_html( duy_ps_seed_report_text( $report ) ) . '</pre>', 'Postsecondary seed', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_ps_seed_run' );

/**
 * Format a seed result array as a plain-text report.
 */
function duy_ps_seed_report_text( array $r ): string {
	return sprintf(
		"POSTSECONDARY SEED DONE\nRows: %d\nImported/updated: %d\nTrashed: %d\nMissing thumbnail: %d\nErrors: %d\n%s",
		$r['rows'] ?? 0,
		$r['imported'] ?? 0,
		$r['trashed'] ?? 0,
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
function duy_ps_seed_import() {
	$file = get_stylesheet_directory() . '/inc/data/postsecondary/postsecondary-import.json';
	if ( ! is_readable( $file ) ) {
		return 'Dataset not found: ' . $file;
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		return 'Dataset unreadable.';
	}

	$country = get_term_by( 'slug', 'my', 'country' );
	$cid     = $country ? (int) $country->term_id : 0;
	$level_term = static function ( string $slug ): int {
		$t = get_term_by( 'slug', $slug, 'study_level' );
		if ( $t ) {
			return (int) $t->term_id;
		}
		$name = ( 'cao-dang' === $slug ) ? 'Cao đẳng' : 'Đại học';
		$new  = wp_insert_term( $name, 'study_level', [ 'slug' => $slug ] );
		return is_wp_error( $new ) ? 0 : (int) $new['term_id'];
	};
	$lid_cd = $level_term( 'cao-dang' );
	$lid_dh = $level_term( 'dai-hoc' );

	$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
	$author = $admins ? (int) $admins[0] : get_current_user_id();
	$updir  = wp_upload_dir();

	$imp = 0; $miss_thumb = 0; $keep = []; $err = [];

	foreach ( $rows as $r ) {
		$psid = (string) ( $r['ps_id'] ?? '' );
		if ( '' === $psid ) {
			$err[] = 'row without ps_id skipped';
			continue;
		}
		$keep[ $psid ] = true;
		try {
			$ex  = get_posts( [ 'post_type' => 'school', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => [ [ 'key' => '_duy_ps_id', 'value' => $psid ] ] ] );
			$pid = $ex ? (int) $ex[0] : 0;
			if ( ! $pid && ! empty( $r['post_slug'] ) ) {
				$p   = get_page_by_path( $r['post_slug'], OBJECT, 'school' );
				$pid = $p ? (int) $p->ID : 0;
			}

			$arr = [
				'post_type'    => 'school',
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

			$level_slug = (string) ( $r['level_slug'] ?? 'dai-hoc' );
			$lid        = ( 'cao-dang' === $level_slug ) ? $lid_cd : $lid_dh;
			if ( $cid ) {
				wp_set_object_terms( $pid, [ $cid ], 'country', false );
			}
			if ( $lid ) {
				wp_set_object_terms( $pid, [ $lid ], 'study_level', false );
			}

			update_post_meta( $pid, '_duy_ps_id', $psid );
			update_post_meta( $pid, '_duy_seo_article', 1 );
			update_post_meta( $pid, '_duy_ps_level_slug', $level_slug );
			update_post_meta( $pid, '_duy_ps_category', (string) ( $r['category'] ?? '' ) );
			update_post_meta( $pid, '_duy_ps_type', (string) ( $r['institution_type'] ?? '' ) );
			update_post_meta( $pid, '_duy_ps_fee', (string) ( $r['fee_display'] ?? '' ) );
			update_post_meta( $pid, '_duy_ps_fee_band', (string) ( $r['fee_band'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_state', (string) ( $r['state'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_badge', (string) ( $r['badge_text'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_website', (string) ( $r['website'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_title', (string) ( $r['meta_title'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_description', (string) ( $r['meta_description'] ?? '' ) );
			update_post_meta( $pid, '_duy_seo_keywords', (string) ( $r['seo_keywords'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_thumb_bg', 'light' );

			// Featured image = logo from the pre-uploaded /uploads/postsecondary/ file.
			// Idempotent + non-destructive: if the current featured image already
			// points at this exact file, do nothing (never delete + re-insert the
			// file we are about to use — that would 404 the thumbnail on re-run).
			$base = (string) ( $r['thumbnail_base'] ?? '' );
			$fpath = $updir['basedir'] . '/postsecondary/' . $base;
			if ( $base && file_exists( $fpath ) ) {
				$cur      = (int) get_post_thumbnail_id( $pid );
				$cur_file = $cur ? (string) get_attached_file( $cur ) : '';
				$cur_ok   = $cur_file && basename( $cur_file ) === $base && file_exists( $cur_file );
				if ( ! $cur_ok ) {
					foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $pid, 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_ps_thumb' ] ) as $old ) {
						if ( basename( (string) get_attached_file( (int) $old ) ) === $base ) {
							continue;
						}
						wp_delete_attachment( (int) $old, true );
					}
					$type = wp_check_filetype( $base, null )['type'] ?: 'image/png';
					$aid  = wp_insert_attachment(
						[
							'post_mime_type' => $type,
							'post_title'     => (string) ( $r['thumbnail_alt'] ?? '' ),
							'post_status'    => 'inherit',
							'guid'           => $updir['baseurl'] . '/postsecondary/' . $base,
						],
						$fpath,
						$pid,
						true
					);
					if ( ! is_wp_error( $aid ) ) {
						update_post_meta( (int) $aid, '_wp_attached_file', 'postsecondary/' . $base );
						update_post_meta( (int) $aid, '_wp_attachment_image_alt', (string) ( $r['thumbnail_alt'] ?? '' ) );
						update_post_meta( (int) $aid, '_duy_ps_thumb', 1 );
						set_post_thumbnail( $pid, (int) $aid );
					}
				}
			} else {
				$miss_thumb++;
			}
			$imp++;
		} catch ( Throwable $e ) {
			$err[] = "$psid: " . $e->getMessage();
		}
	}

	// Trash imported colleges/universities no longer in the dataset.
	$trashed = 0;
	foreach ( get_posts( [ 'post_type' => 'school', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_ps_id' ] ) as $id ) {
		$psid = (string) get_post_meta( $id, '_duy_ps_id', true );
		if ( ! isset( $keep[ $psid ] ) ) {
			wp_trash_post( $id );
			$trashed++;
		}
	}

	return [
		'rows'              => count( $rows ),
		'imported'          => $imp,
		'trashed'           => $trashed,
		'missing_thumbnail' => $miss_thumb,
		'errors'            => $err,
	];
}
