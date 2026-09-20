<?php
/**
 * One-time THPT (US high school) seeder for non-production environments.
 *
 * Ships the import dataset inside the theme so a review/staging site can be
 * populated with the 148 schools over HTTP (the theme zip deploy carries no
 * database). Idempotent — dedup by school_id -> slug -> canonical_key -> title.
 *
 * Trigger (must be a logged-in admin):
 *   /wp-admin/?duy_hs_seed=1&token=<DUY_HS_SEED_TOKEN>
 *
 * SAFETY: never runs on production, and refuses without the token. Remove this
 * file (and inc/data/) before shipping the theme to production.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DUY_HS_SEED_TOKEN = 'ds-thpt-seed-7f3a91';

function duy_hs_seed_run(): void {
	if ( ! isset( $_GET['duy_hs_seed'] ) ) {
		return;
	}
	// Only run on non-public sites: development/staging env, or a noindex
	// (blog_public=0) site such as the review server. A public production
	// site (indexable) is refused.
	$is_public_prod = ( 'production' === wp_get_environment_type() ) && ( '0' !== (string) get_option( 'blog_public' ) );
	if ( $is_public_prod ) {
		wp_die( 'Seeder disabled: refusing to run on a public production site.' );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Seeder requires an administrator login.' );
	}
	if ( ( $_GET['token'] ?? '' ) !== DUY_HS_SEED_TOKEN ) {
		wp_die( 'Invalid seed token.' );
	}
	// Self-disable after a successful run to shrink the exposure window.
	if ( get_option( 'duy_hs_seeded' ) && empty( $_GET['force'] ) ) {
		wp_die( 'Already seeded at ' . esc_html( gmdate( 'c', (int) get_option( 'duy_hs_seeded' ) ) ) . '. Append &force=1 to re-run.' );
	}

	$file = get_stylesheet_directory() . '/inc/data/us-high-schools.json';
	if ( ! is_readable( $file ) ) {
		wp_die( 'Dataset not found: ' . esc_html( $file ) );
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		wp_die( 'Dataset unreadable.' );
	}

	// Ensure the taxonomy terms exist.
	$country = get_term_by( 'slug', 'my', 'country' ) ?: ( function () {
		$t = wp_insert_term( 'Mỹ', 'country', [ 'slug' => 'my' ] );
		return is_wp_error( $t ) ? null : get_term( $t['term_id'] );
	} )();
	$level = get_term_by( 'slug', 'thpt', 'study_level' ) ?: ( function () {
		$t = wp_insert_term( 'THPT', 'study_level', [ 'slug' => 'thpt' ] );
		return is_wp_error( $t ) ? null : get_term( $t['term_id'] );
	} )();
	if ( ! $country || ! $level ) {
		wp_die( 'Missing taxonomy terms (country/study_level not registered?).' );
	}
	$country_id = (int) $country->term_id;
	$level_id   = (int) $level->term_id;

	$admins    = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
	$author_id = $admins ? (int) $admins[0] : get_current_user_id();

	$carbon_map = [
		'full_name'   => '_full_name',
		'short_name'  => '_acronym',
		'founded'     => '_founded',
		'school_type' => '_type',
		'state'       => '_city',
		'website'     => '_website',
		'school_id'   => '_code',
	];

	$find = static function ( array $r ): int {
		$byMeta = static function ( string $key, string $val ): int {
			if ( '' === $val ) {
				return 0;
			}
			$p = get_posts( [ 'post_type' => 'school', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => [ [ 'key' => $key, 'value' => $val ] ] ] );
			return $p ? (int) $p[0] : 0;
		};
		if ( $id = $byMeta( '_duy_hs_school_id', (string) ( $r['school_id'] ?? '' ) ) ) {
			return $id;
		}
		if ( ! empty( $r['slug'] ) && ( $p = get_page_by_path( $r['slug'], OBJECT, 'school' ) ) ) {
			return (int) $p->ID;
		}
		if ( $id = $byMeta( '_duy_hs_canonical_key', (string) ( $r['canonical_key'] ?? '' ) ) ) {
			return $id;
		}
		if ( ! empty( $r['school_name'] ) ) {
			$p = get_posts( [ 'post_type' => 'school', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'title' => $r['school_name'] ] );
			if ( $p ) {
				return (int) $p[0];
			}
		}
		return 0;
	};

	$split = static function ( $s ): array {
		$s = trim( (string) $s );
		if ( '' === $s ) {
			return [];
		}
		return array_values( array_filter( array_map( 'trim', explode( '|', $s ) ), 'strlen' ) );
	};

	$created = 0;
	$updated = 0;
	$errors  = [];

	foreach ( $rows as $r ) {
		$sid = (string) ( $r['school_id'] ?? '' );
		try {
			$existing = $find( $r );
			$postarr  = [
				'post_type'    => 'school',
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'post_title'   => (string) ( $r['school_name'] ?? '' ),
				'post_excerpt' => (string) ( $r['hero'] ?? '' ),
				'post_content' => (string) ( $r['profile'] ?? '' ),
			];
			if ( $existing ) {
				$postarr['ID'] = $existing;
			} else {
				$postarr['post_name'] = (string) ( $r['slug'] ?? '' );
			}
			$pid = wp_insert_post( $postarr, true );
			if ( is_wp_error( $pid ) ) {
				throw new RuntimeException( $pid->get_error_message() );
			}
			$pid = (int) $pid;

			wp_set_object_terms( $pid, [ $country_id ], 'country', false );
			wp_set_object_terms( $pid, [ $level_id ], 'study_level', false );

			foreach ( $r as $k => $v ) {
				update_post_meta( $pid, '_duy_hs_' . $k, is_scalar( $v ) ? (string) $v : wp_json_encode( $v ) );
			}
			foreach ( $carbon_map as $col => $key ) {
				update_post_meta( $pid, $key, (string) ( $r[ $col ] ?? '' ) );
			}
			update_post_meta( $pid, '_duy_hs_why_choose_list', $split( $r['why_choose'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_research_sources_list', $split( $r['research_sources'] ?? '' ) );

			$existing ? $updated++ : $created++;
		} catch ( Throwable $e ) {
			$errors[] = $sid . ': ' . $e->getMessage();
		}
	}

	update_option( 'duy_hs_seeded', time() );

	$report = sprintf(
		"THPT SEED DONE\nSource: %d\nCreated: %d\nUpdated: %d\nErrors: %d\n%s",
		count( $rows ),
		$created,
		$updated,
		count( $errors ),
		$errors ? implode( "\n", $errors ) : ''
	);
	wp_die( '<pre>' . esc_html( $report ) . '</pre>', 'THPT seed', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_hs_seed_run' );

/**
 * Import bundled school logos (inc/data/thumbs/<slug>.<ext>) as featured images
 * on a review/staging site. Idempotent. Trigger:
 *   /wp-admin/?duy_hs_thumbs=1&token=<TOKEN>
 */
function duy_hs_thumbs_run(): void {
	if ( ! isset( $_GET['duy_hs_thumbs'] ) ) {
		return;
	}
	$is_public_prod = ( 'production' === wp_get_environment_type() ) && ( '0' !== (string) get_option( 'blog_public' ) );
	if ( $is_public_prod ) {
		wp_die( 'Thumbs seeder disabled on a public production site.' );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Requires an administrator login.' );
	}
	if ( ( $_GET['token'] ?? '' ) !== DUY_HS_SEED_TOKEN ) {
		wp_die( 'Invalid token.' );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	$dir  = get_stylesheet_directory() . '/inc/data/thumbs';
	$meta = json_decode( (string) @file_get_contents( get_stylesheet_directory() . '/inc/data/thumbs-meta.json' ), true );
	if ( ! is_array( $meta ) ) {
		wp_die( 'thumbs-meta.json missing/unreadable.' );
	}

	$mime_map = [ 'svg' => 'image/svg+xml', 'avif' => 'image/avif', 'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'ico' => 'image/x-icon' ];
	$set = 0; $skip = 0; $miss = 0; $err = [];

	foreach ( $meta as $slug => $info ) {
		$post = get_page_by_path( $slug, OBJECT, 'school' );
		if ( ! $post ) { $err[] = "$slug: no post"; continue; }
		$ext  = $info['ext'] ?? 'png';
		$src  = "$dir/$slug.$ext";
		if ( ! is_readable( $src ) ) { $miss++; continue; }
		$srcname = "$slug.$ext";

		$cur = (int) get_post_thumbnail_id( $post->ID );
		if ( $cur && $srcname === (string) get_post_meta( $cur, '_duy_hs_thumb_src', true ) ) {
			update_post_meta( $post->ID, '_duy_hs_thumb_bg', $info['bg'] ?? 'light' );
			$skip++;
			continue;
		}
		try {
			$updir    = wp_upload_dir();
			$filename = wp_unique_filename( $updir['path'], $srcname );
			$dest     = $updir['path'] . '/' . $filename;
			if ( ! copy( $src, $dest ) ) { throw new RuntimeException( 'copy failed' ); }
			$type = wp_check_filetype( $filename, null )['type'] ?: ( $mime_map[ $ext ] ?? 'image/png' );
			$aid  = wp_insert_attachment( [ 'post_mime_type' => $type, 'post_title' => $post->post_title, 'post_status' => 'inherit' ], $dest, $post->ID, true );
			if ( is_wp_error( $aid ) ) { throw new RuntimeException( $aid->get_error_message() ); }
			$amd = wp_generate_attachment_metadata( (int) $aid, $dest );
			if ( $amd ) { wp_update_attachment_metadata( (int) $aid, $amd ); }
			update_post_meta( (int) $aid, '_wp_attachment_image_alt', (string) ( $info['alt'] ?? '' ) );
			update_post_meta( (int) $aid, '_duy_hs_thumb', 1 );
			update_post_meta( (int) $aid, '_duy_hs_thumb_src', $srcname );
			if ( $cur && get_post_meta( $cur, '_duy_hs_thumb', true ) ) { wp_delete_attachment( $cur, true ); }
			set_post_thumbnail( $post->ID, (int) $aid );
			update_post_meta( $post->ID, '_duy_hs_thumb_bg', $info['bg'] ?? 'light' );
			$set++;
		} catch ( Throwable $e ) {
			$err[] = "$slug: " . $e->getMessage();
		}
	}
	$report = sprintf( "THUMBS DONE\nSet: %d\nSkipped(existing): %d\nMissing file: %d\nErrors: %d\n%s", $set, $skip, $miss, count( $err ), implode( "\n", $err ) );
	wp_die( '<pre>' . esc_html( $report ) . '</pre>', 'THPT thumbs', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_hs_thumbs_run' );

/**
 * SEO re-import (namphong-style package) for review/staging.
 * Reads inc/data/seo/seo-import.json; content_html already references
 * /wp-content/uploads/thpt-seo/<file> (uploaded separately via FTP).
 * Trigger:  /wp-admin/?duy_hs_seo=1&token=<TOKEN>
 */
function duy_hs_seo_run(): void {
	if ( ! isset( $_GET['duy_hs_seo'] ) ) {
		return;
	}
	if ( ( 'production' === wp_get_environment_type() ) && ( '0' !== (string) get_option( 'blog_public' ) ) ) {
		wp_die( 'Disabled on public production.' );
	}
	if ( ! current_user_can( 'manage_options' ) || ( $_GET['token'] ?? '' ) !== DUY_HS_SEED_TOKEN ) {
		wp_die( 'Requires admin login + valid token.' );
	}

	$dir  = get_stylesheet_directory() . '/inc/data/seo';
	$rows = json_decode( (string) @file_get_contents( "$dir/seo-import.json" ), true );
	$del  = json_decode( (string) @file_get_contents( "$dir/delete.json" ), true ) ?: [];
	if ( ! is_array( $rows ) ) {
		wp_die( 'seo-import.json missing/unreadable.' );
	}

	$country = get_term_by( 'slug', 'my', 'country' );
	$level   = get_term_by( 'slug', 'thpt', 'study_level' );
	$cid     = $country ? (int) $country->term_id : 0;
	$lid     = $level ? (int) $level->term_id : 0;
	$admins  = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
	$author  = $admins ? (int) $admins[0] : get_current_user_id();
	$updir   = wp_upload_dir();

	$imp = 0; $miss_thumb = 0; $keep = []; $err = [];

	foreach ( $rows as $r ) {
		$sid          = (string) ( $r['school_id'] ?? '' );
		$keep[ $sid ] = true;
		try {
			$ex  = get_posts( [ 'post_type' => 'school', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_query' => [ [ 'key' => '_duy_hs_school_id', 'value' => $sid ] ] ] );
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

			if ( $cid ) {
				wp_set_object_terms( $pid, [ $cid ], 'country', false );
			}
			if ( $lid ) {
				wp_set_object_terms( $pid, [ $lid ], 'study_level', false );
			}
			update_post_meta( $pid, '_duy_hs_school_id', $sid );
			update_post_meta( $pid, '_duy_seo_article', 1 );
			update_post_meta( $pid, '_duy_hs_state', (string) ( $r['state'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_tuition_display', (string) ( $r['tuition_display'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_tuition_from', (string) ( $r['tuition_usd_from'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_school_type_display', (string) ( $r['school_type_display'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_badge', (string) ( $r['badge_text'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_title', (string) ( $r['meta_title'] ?? '' ) );
			update_post_meta( $pid, '_duy_meta_description', (string) ( $r['meta_description'] ?? '' ) );
			update_post_meta( $pid, '_duy_seo_keywords', (string) ( $r['seo_keywords'] ?? '' ) );
			update_post_meta( $pid, '_duy_hs_thumb_bg', (string) ( $r['thumb_bg'] ?? 'light' ) );

			// Featured image from the pre-uploaded /uploads/thpt-seo/ file.
			// IDEMPOTENT + NON-DESTRUCTIVE: if the current featured image already
			// points at this exact file (and the file exists), do nothing — do NOT
			// delete + re-insert, because wp_delete_attachment(..., true) force-
			// deletes the file from disk, and re-inserting then references a file
			// that was just deleted (→ broken 404 thumbnail on the next re-run).
			$base = (string) ( $r['thumbnail_base'] ?? '' );
			$file = $updir['basedir'] . '/thpt-seo/' . $base;
			if ( $base && file_exists( $file ) ) {
				$cur      = (int) get_post_thumbnail_id( $pid );
				$cur_file = $cur ? (string) get_attached_file( $cur ) : '';
				$cur_ok   = $cur_file && basename( $cur_file ) === $base && file_exists( $cur_file );
				if ( ! $cur_ok ) {
					foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $pid, 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_hs_thumb' ] ) as $old ) {
						// Never delete the very file we are about to use as featured.
						if ( basename( (string) get_attached_file( (int) $old ) ) === $base ) { continue; }
						wp_delete_attachment( (int) $old, true );
					}
					$type = wp_check_filetype( $base, null )['type'] ?: 'image/png';
					$aid  = wp_insert_attachment( [ 'post_mime_type' => $type, 'post_title' => (string) ( $r['thumbnail_alt'] ?? '' ), 'post_status' => 'inherit', 'guid' => $updir['baseurl'] . '/thpt-seo/' . $base ], $file, $pid, true );
					if ( ! is_wp_error( $aid ) ) {
						update_post_meta( (int) $aid, '_wp_attached_file', 'thpt-seo/' . $base );
						update_post_meta( (int) $aid, '_wp_attachment_image_alt', (string) ( $r['thumbnail_alt'] ?? '' ) );
						update_post_meta( (int) $aid, '_duy_hs_thumb', 1 );
						set_post_thumbnail( $pid, (int) $aid );
					}
				}
			} else {
				$miss_thumb++;
			}
			$imp++;
		} catch ( Throwable $e ) {
			$err[] = "$sid: " . $e->getMessage();
		}
	}

	// Trash schools not in the new set (incl. the delete list).
	$trashed = 0;
	foreach ( get_posts( [ 'post_type' => 'school', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_duy_hs_school_id' ] ) as $id ) {
		$sid = (string) get_post_meta( $id, '_duy_hs_school_id', true );
		if ( ! isset( $keep[ $sid ] ) ) {
			wp_trash_post( $id );
			$trashed++;
		}
	}

	$report = sprintf(
		"SEO SEED DONE\nRows: %d\nImported/updated: %d\nTrashed: %d\nMissing thumbnail: %d\nDelete-list size: %d\nErrors: %d\n%s",
		count( $rows ),
		$imp,
		$trashed,
		$miss_thumb,
		count( $del ),
		count( $err ),
		implode( "\n", $err )
	);
	wp_die( '<pre>' . esc_html( $report ) . '</pre>', 'SEO seed', [ 'response' => 200 ] );
}
add_action( 'admin_init', 'duy_hs_seo_run' );
