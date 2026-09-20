<?php
/**
 * Canada / Australia (THPT + Cao đẳng + Đại học) seeder for non-production.
 *
 * Imports the bundled dataset (inc/data/canada-australia/canada-australia-import.json)
 * into the `school` CPT as public-clean SEO articles, tagged country=canada|uc +
 * study_level=thpt|cao-dang|dai-hoc. Featured image = the school logo pre-uploaded
 * to /wp-content/uploads/canada-australia/<file>.
 *
 * FULLY ISOLATED from the US import subsystems:
 *   - identity meta is `_duy_caau_id` (never `_duy_ps_id` / `_duy_hs_school_id`),
 *     so the US postsecondary / THPT seeders' trash sweeps can never touch these
 *     posts, and this seeder's trash sweep only ever touches `_duy_caau_id` posts.
 *
 * Idempotent + non-destructive on the featured image (never force-deletes the
 * file it is about to re-use).
 *
 * Trigger (logged-in admin, non-public site only):
 *   /wp-admin/?duy_caau_seed=1&token=<DUY_HS_SEED_TOKEN>
 *
 * SAFETY: refuses on a public production site and without the token. Remove this
 * file (and inc/data/canada-australia/) before shipping the theme to production.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_caau_seed_run(): void {
	if ( ! isset( $_GET['duy_caau_seed'] ) ) {
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

	$report = duy_caau_seed_import();
	if ( is_string( $report ) ) {
		wp_die( esc_html( $report ) );
	}
	wp_die( '<pre>' . esc_html( duy_caau_seed_report_text( $report ) ) . '</pre>', 'Canada/Australia seed', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_caau_seed_run' );

/**
 * Format a seed result array as a plain-text report.
 */
function duy_caau_seed_report_text( array $r ): string {
	return sprintf(
		"CANADA/AUSTRALIA SEED DONE\nRows: %d\nImported/updated: %d\nTrashed: %d\nMissing thumbnail: %d\nErrors: %d\n%s",
		$r['rows'] ?? 0,
		$r['imported'] ?? 0,
		$r['trashed'] ?? 0,
		$r['missing_thumbnail'] ?? 0,
		count( $r['errors'] ?? [] ),
		implode( "\n", $r['errors'] ?? [] )
	);
}

/**
 * Resolve (or lazily create) a taxonomy term id by slug.
 */
function duy_caau_term_id( string $slug, string $taxonomy, string $name ): int {
	$t = get_term_by( 'slug', $slug, $taxonomy );
	if ( $t ) {
		return (int) $t->term_id;
	}
	$new = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
	return is_wp_error( $new ) ? 0 : (int) $new['term_id'];
}

/**
 * Core import routine — shared by the admin-hook trigger and CLI bootstrap.
 * Idempotent. Returns a result array, or an error string on a fatal precondition.
 *
 * @return array|string
 */
function duy_caau_seed_import() {
	$file = get_stylesheet_directory() . '/inc/data/canada-australia/canada-australia-import.json';
	if ( ! is_readable( $file ) ) {
		return 'Dataset not found: ' . $file;
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		return 'Dataset unreadable.';
	}

	$country_ids = [
		'canada' => duy_caau_term_id( 'canada', 'country', 'Canada' ),
		'uc'     => duy_caau_term_id( 'uc', 'country', 'Úc' ),
	];
	$level_ids = [
		'thpt'     => duy_caau_term_id( 'thpt', 'study_level', 'THPT' ),
		'cao-dang' => duy_caau_term_id( 'cao-dang', 'study_level', 'Cao đẳng' ),
		'dai-hoc'  => duy_caau_term_id( 'dai-hoc', 'study_level', 'Đại học' ),
	];

	$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
	$author = $admins ? (int) $admins[0] : get_current_user_id();
	$updir  = wp_upload_dir();

	$imp = 0; $miss_thumb = 0; $keep = []; $err = [];

	foreach ( $rows as $r ) {
		$cid = (string) ( $r['record_id'] ?? '' );
		if ( '' === $cid ) {
			$err[] = 'row without record_id skipped';
			continue;
		}
		$keep[ $cid ] = true;
		try {
			$ex  = get_posts( [ 'post_type' => 'school', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => [ [ 'key' => '_duy_caau_id', 'value' => $cid ] ] ] );
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

			$country_slug = (string) ( $r['country_slug'] ?? '' );
			$level_slug   = (string) ( $r['level_slug'] ?? '' );
			$cterm        = $country_ids[ $country_slug ] ?? 0;
			$lterm        = $level_ids[ $level_slug ] ?? 0;
			if ( $cterm ) {
				wp_set_object_terms( $pid, [ $cterm ], 'country', false );
			}
			if ( $lterm ) {
				wp_set_object_terms( $pid, [ $lterm ], 'study_level', false );
			}

			update_post_meta( $pid, '_duy_caau_id', $cid );
			update_post_meta( $pid, '_duy_seo_article', 1 );
			update_post_meta( $pid, '_duy_caau_level_slug', $level_slug );
			update_post_meta( $pid, '_duy_caau_country_slug', $country_slug );
			update_post_meta( $pid, '_duy_caau_type', (string) ( $r['institution_type'] ?? '' ) );
			update_post_meta( $pid, '_duy_caau_category', (string) ( $r['category'] ?? '' ) );
			// The source `state` sometimes just repeats the country (e.g. "Úc",
			// "Canada"); drop those so the location chip doesn't read "Canada,
			// Canada". Real regions/cities are kept verbatim. Article body is
			// untouched.
			$state_raw = trim( (string) ( $r['state'] ?? '' ) );
			if ( in_array( mb_strtolower( $state_raw ), [ 'úc', 'canada', 'australia', 'úc.', 'canada.' ], true ) ) {
				$state_raw = '';
			}
			update_post_meta( $pid, '_duy_hs_state', $state_raw );
			update_post_meta( $pid, '_duy_hs_badge', (string) ( $r['badge_text'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_website', (string) ( $r['website'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_title', (string) ( $r['meta_title'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_description', (string) ( $r['meta_description'] ?? '' ) );
			update_post_meta( $pid, '_duy_seo_keywords', (string) ( $r['seo_keywords'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_thumb_bg', 'light' );

			// Featured image = logo from the pre-uploaded /uploads/canada-australia/ file.
			// Idempotent + non-destructive: if the current featured image already
			// points at this exact file, do nothing (never delete + re-insert the
			// file we are about to use — that would 404 the thumbnail on re-run).
			$base  = (string) ( $r['thumbnail_base'] ?? '' );
			$fpath = $updir['basedir'] . '/canada-australia/' . $base;
			if ( $base && file_exists( $fpath ) ) {
				$cur      = (int) get_post_thumbnail_id( $pid );
				$cur_file = $cur ? (string) get_attached_file( $cur ) : '';
				$cur_ok   = $cur_file && basename( $cur_file ) === $base && file_exists( $cur_file );
				if ( ! $cur_ok ) {
					foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $pid, 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_caau_thumb' ] ) as $old ) {
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
							'guid'           => $updir['baseurl'] . '/canada-australia/' . $base,
						],
						$fpath,
						$pid,
						true
					);
					if ( ! is_wp_error( $aid ) ) {
						update_post_meta( (int) $aid, '_wp_attached_file', 'canada-australia/' . $base );
						update_post_meta( (int) $aid, '_wp_attachment_image_alt', (string) ( $r['thumbnail_alt'] ?? '' ) );
						update_post_meta( (int) $aid, '_duy_caau_thumb', 1 );
						set_post_thumbnail( $pid, (int) $aid );
					}
				}
			} else {
				$miss_thumb++;
			}
			$imp++;
		} catch ( Throwable $e ) {
			$err[] = "$cid: " . $e->getMessage();
		}
	}

	// Trash imported Canada/Australia schools no longer in the dataset.
	// Scoped strictly to `_duy_caau_id` posts — US imports are never affected.
	$trashed = 0;
	foreach ( get_posts( [ 'post_type' => 'school', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_caau_id' ] ) as $id ) {
		$cid = (string) get_post_meta( $id, '_duy_caau_id', true );
		if ( ! isset( $keep[ $cid ] ) ) {
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
