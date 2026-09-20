<?php
/**
 * Mockup V2 data bridge.
 *
 * @package DUY_Study
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function duy_mockup_v2_payload(): array {
	static $payload = null;

	if ( null !== $payload ) {
		return $payload;
	}

	$file = DUY_THEME_DIR . '/inc/mockup-v2-data.json';
	if ( ! file_exists( $file ) ) {
		$payload = [];

		return $payload;
	}

	$decoded = json_decode( (string) file_get_contents( $file ), true );
	$payload = is_array( $decoded ) ? $decoded : [];

	return $payload;
}

function duy_mockup_v2_country_slug_from_item( array $country ): string {
	$map = [
		'US' => 'my',
		'AU' => 'uc',
		'CA' => 'canada',
		'NZ' => 'new-zealand',
		'TR' => 'tho-nhi-ky',
		'SG' => 'singapore',
		'KR' => 'han-quoc',
		'DE' => 'duc',
		'NL' => 'ha-lan',
		'GB' => 'anh',
		'MY' => 'malaysia',
		'CH' => 'thuy-sy',
		'PH' => 'philippines',
	];

	$code = strtoupper( (string) ( $country['code'] ?? '' ) );
	if ( isset( $map[ $code ] ) ) {
		return $map[ $code ];
	}

	return duy_slugify( (string) ( $country['name'] ?? $country['title'] ?? '' ) );
}

function duy_mockup_v2_countries(): array {
	$items = duy_mockup_v2_payload()['countries'] ?? [];
	if ( ! is_array( $items ) ) {
		return [];
	}

	$countries = [];
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$slug = duy_mockup_v2_country_slug_from_item( $item );
		if ( '' === $slug ) {
			continue;
		}
		$countries[ $slug ] = $item;
	}

	return $countries;
}

function duy_mockup_v2_schools(): array {
	$items = duy_mockup_v2_payload()['schools'] ?? [];

	return is_array( $items ) ? array_values( array_filter( $items, 'is_array' ) ) : [];
}

function duy_mockup_v2_string_items( $rows, string $key = 'item' ): array {
	if ( ! is_array( $rows ) ) {
		return [];
	}

	$items = [];
	foreach ( $rows as $row ) {
		$value = is_array( $row ) ? ( $row[ $key ] ?? '' ) : $row;
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			$items[] = $value;
		}
	}

	return $items;
}

function duy_mockup_v2_bac_data(): array {
	$items = duy_mockup_v2_payload()['bac'] ?? [];

	$data = is_array( $items ) ? $items : [];

	if ( function_exists( 'duy_rows' ) ) {
		foreach ( duy_rows( 'v2_bac_pages' ) as $row ) {
			$slug = sanitize_title( (string) ( $row['level_slug'] ?? '' ) );
			if ( '' === $slug ) {
				continue;
			}

			$data[ $slug ] = array_merge(
				$data[ $slug ] ?? [],
				array_filter(
					[
						'tagline'         => $row['tagline'] ?? '',
						'overview'        => $row['overview'] ?? '',
						'whoFor'          => duy_mockup_v2_string_items( $row['who_for'] ?? [] ),
						'requirements'    => duy_mockup_v2_string_items( $row['requirements'] ?? [] ),
						'timeline'        => array_values( array_filter( (array) ( $row['timeline'] ?? [] ), 'is_array' ) ),
						'costsNotes'      => duy_mockup_v2_string_items( $row['costs_notes'] ?? [] ),
						'pathway'         => $row['pathway'] ?? '',
						'duyStudySupport' => duy_mockup_v2_string_items( $row['duy_study_support'] ?? [] ),
						'countryNote'     => $row['country_note'] ?? '',
					],
					static fn( $value ) => '' !== $value && [] !== $value
				)
			);
		}
	}

	return $data;
}

function duy_mockup_v2_levels(): array {
	return [
		'thpt'         => [ 'name' => 'THPT', 'icon' => 'cap' ],
		'cao-dang'    => [ 'name' => 'Cao đẳng', 'icon' => 'layers' ],
		'dai-hoc'     => [ 'name' => 'Đại học', 'icon' => 'book' ],
		'sau-dai-hoc' => [ 'name' => 'Sau đại học', 'icon' => 'route' ],
		'anh-ngu'     => [ 'name' => 'Anh ngữ', 'icon' => 'globe' ],
	];
}

function duy_mockup_v2_level_by_slug( string $slug ): ?array {
	$levels = duy_mockup_v2_levels();

	return $levels[ $slug ] ?? null;
}

function duy_mockup_v2_majors(): array {
	$items = duy_mockup_v2_payload()['majors'] ?? [];
	if ( ! is_array( $items ) ) {
		$items = [];
	}

	$majors = array_values(
		array_filter(
			array_map(
				static function ( $item ) {
					if ( ! is_array( $item ) ) {
						return null;
					}
					$item['slug'] = preg_replace( '/^nganh-/', '', (string) ( $item['id'] ?? '' ) );

					return $item;
				},
				$items
			)
		)
	);

	if ( function_exists( 'duy_rows' ) ) {
		$indexed = [];
		foreach ( $majors as $major ) {
			$slug = (string) ( $major['slug'] ?? '' );
			if ( $slug ) {
				$indexed[ $slug ] = $major;
			}
		}

		foreach ( duy_rows( 'v2_majors' ) as $row ) {
			$slug = sanitize_title( (string) ( $row['slug'] ?? '' ) );
			$slug = preg_replace( '/^nganh-/', '', $slug );
			if ( '' === $slug ) {
				continue;
			}

			$why = array_map(
				static fn( $item ) => [
					'icon' => $item['icon'] ?? 'sparkles',
					't'    => $item['t'] ?? $item['title'] ?? '',
					'd'    => $item['d'] ?? $item['desc'] ?? '',
				],
				array_values( array_filter( (array) ( $row['why'] ?? [] ), 'is_array' ) )
			);

			$indexed[ $slug ] = array_merge(
				$indexed[ $slug ] ?? [ 'id' => 'nganh-' . $slug, 'slug' => $slug ],
				array_filter(
					[
						'id'        => 'nganh-' . $slug,
						'slug'      => $slug,
						'icon'      => $row['icon'] ?? '',
						'title'     => $row['title'] ?? '',
						'lead'      => $row['lead'] ?? '',
						'overview'  => $row['overview'] ?? '',
						'match'     => duy_mockup_v2_string_items( $row['match'] ?? [] ),
						'countries' => duy_mockup_v2_string_items( $row['countries'] ?? [] ),
						'why'       => $why,
						'careers'   => duy_mockup_v2_string_items( $row['careers'] ?? [] ),
					],
					static fn( $value ) => '' !== $value && [] !== $value
				)
			);
		}

		$majors = array_values( $indexed );
	}

	return $majors;
}

function duy_mockup_v2_major_by_slug( string $slug ): ?array {
	foreach ( duy_mockup_v2_majors() as $major ) {
		if ( ( $major['slug'] ?? '' ) === $slug ) {
			return $major;
		}
	}

	return null;
}

function duy_mockup_v2_school_matches_major( array $school, array $major ): bool {
	$matches = array_values( array_filter( (array) ( $major['match'] ?? [] ), 'is_string' ) );
	if ( ! $matches ) {
		return false;
	}

	$haystack = [
		(string) ( $school['major'] ?? '' ),
		(string) ( $school['desc'] ?? '' ),
	];

	foreach ( (array) ( $school['programs'] ?? [] ) as $program ) {
		if ( is_string( $program ) ) {
			$haystack[] = $program;
		}
	}

	$haystack = function_exists( 'mb_strtolower' )
		? mb_strtolower( implode( ' ', $haystack ), 'UTF-8' )
		: strtolower( implode( ' ', $haystack ) );
	foreach ( $matches as $match ) {
		$needle = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $match ), 'UTF-8' ) : strtolower( trim( $match ) );
		if ( '' !== $needle && str_contains( $haystack, $needle ) ) {
			return true;
		}
	}

	return false;
}

function duy_mockup_v2_schools_for_major( string $major_slug, string $country_slug = '', int $limit = 0 ): array {
	$major = duy_mockup_v2_major_by_slug( $major_slug );
	if ( ! $major ) {
		return [];
	}

	$schools = array_filter(
		duy_demo_schools(),
		static function ( array $school ) use ( $major, $country_slug ): bool {
			if ( $country_slug && duy_country_slug( (string) ( $school['c'] ?? '' ) ) !== $country_slug ) {
				return false;
			}

			return duy_mockup_v2_school_matches_major( $school, $major );
		}
	);

	$schools = function_exists( 'duy_items_featured_then_newest' )
		? duy_items_featured_then_newest( array_values( $schools ), 'school' )
		: array_values( $schools );

	return $limit > 0 ? array_slice( $schools, 0, $limit ) : $schools;
}
