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

/**
 * Luat khop truong theo nganh.
 *
 * 571 truong that deu co `major` rong va `programs` rong (importer khong dien),
 * con `desc` chi la cau gioi thieu chung. Tin hieu dung duy nhat la TEN truong:
 * "University of Technology", "Business School", "College of Education"...
 *
 * `exclude` chan khop bay: "Medicine Hat College" lay ten thanh pho o Alberta chu
 * khong phai truong y; "Higher Education"/"Hospitality Education" khong phai nganh
 * su pham. `levels` gioi han bac hoc: nganh Tieng Anh khop ĐUNG bang bac `anh-ngu`
 * nen khong phai doan tu ten.
 */
function duy_major_match_rules(): array {
	return [
		'kinh-te'   => [
			'include' => [ 'business', 'commerce', 'economics', 'management', 'finance',
			               'hospitality', 'hotel', 'kinh doanh', 'kinh te' ],
			'exclude' => [],
			'levels'  => [ 'dai-hoc', 'cao-dang' ],
		],
		'suc-khoe'  => [
			'include' => [ 'medical', 'medicine', 'health science', 'nursing', 'pharmac',
			               'dental', 'y khoa', 'duoc', 'suc khoe' ],
			'exclude' => [ 'medicine hat' ],
			'levels'  => [ 'dai-hoc', 'cao-dang' ],
		],
		'cong-nghe' => [
			'include' => [ 'institute of technology', 'university of technology',
			               'technical university', 'technolog', 'polytechnic', 'engineering',
			               'informatics', 'cong nghe', 'ky thuat', 'bach khoa' ],
			'exclude' => [],
			'levels'  => [ 'dai-hoc', 'cao-dang' ],
		],
		'giao-duc'  => [
			'include' => [ 'college of education', 'school of education', 'teachers college',
			               'teacher', 'normal university', 'su pham' ],
			'exclude' => [ 'higher education', 'hospitality education' ],
			'levels'  => [ 'dai-hoc', 'cao-dang' ],
		],
		'tieng-anh' => [
			'include' => [],                       // khop bang bac hoc, khong doc ten
			'exclude' => [],
			'levels'  => [ 'anh-ngu' ],
		],
	];
}

/** Bo dau + ha chu thuong de so khop khong phu thuoc dau tieng Viet. */
function duy_major_fold( string $value ): string {
	$value = function_exists( 'remove_accents' ) ? remove_accents( $value ) : $value;

	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
}

/**
 * Bac hoc cua the truong, cung cach ma `card-school.php` sinh ra `data-level`
 * (duy_slugify tren nhan hien thi): "Dai hoc" -> dai-hoc, "Anh ngu" -> anh-ngu.
 * Dung chung mot ham de bo loc o trang /truong/ va o day khong lech nhau.
 */
function duy_major_school_level_slug( array $school ): string {
	return duy_slugify( (string) ( $school['level'] ?? '' ) );
}

function duy_mockup_v2_school_matches_major( array $school, array $major ): bool {
	$rules = duy_major_match_rules();
	$rule  = $rules[ (string) ( $major['slug'] ?? '' ) ] ?? null;

	if ( ! $rule ) {
		return false;
	}

	if ( ! in_array( duy_major_school_level_slug( $school ), (array) $rule['levels'], true ) ) {
		return false;
	}

	$name = duy_major_fold( (string) ( $school['n'] ?? '' ) );

	foreach ( (array) $rule['exclude'] as $bad ) {
		if ( '' !== $bad && str_contains( $name, $bad ) ) {
			return false;
		}
	}

	// Khong co tu khoa => luat chi dua vao bac hoc (nganh Tieng Anh).
	if ( ! $rule['include'] ) {
		return true;
	}

	foreach ( (array) $rule['include'] as $needle ) {
		if ( '' !== $needle && str_contains( $name, $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Truong de bu khi mot nganh chua khop du: lay danh sach truong tieu bieu da tuyen
 * cua quoc gia (5 diem den chinh), neu quoc gia do chua co thi lay dai hoc cua chinh
 * quoc gia do theo thu tu chuan cua site. Day la GOI Y truong theo quoc gia, khong
 * phai khang dinh truong manh nganh do.
 */
function duy_major_country_schools( string $country_slug ): array {
	$countries = function_exists( 'duy_demo_countries' ) ? duy_demo_countries() : [];
	$country   = $countries[ $country_slug ] ?? [];
	$all       = function_exists( 'duy_demo_schools' ) ? duy_demo_schools() : [];

	$picked = function_exists( 'duy_items_by_ids' )
		? duy_items_by_ids( $all, array_values( (array) ( $country['schools'] ?? [] ) ) )
		: [];

	if ( $picked ) {
		return $picked;
	}

	$rest = array_filter(
		$all,
		static function ( array $school ) use ( $country_slug ): bool {
			if ( duy_country_slug( (string) ( $school['c'] ?? '' ) ) !== $country_slug ) {
				return false;
			}

			return in_array( duy_major_school_level_slug( $school ), [ 'dai-hoc', 'cao-dang' ], true );
		}
	);

	return function_exists( 'duy_items_featured_then_newest' )
		? duy_items_featured_then_newest( array_values( $rest ), 'school' )
		: array_values( $rest );
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

	// Trang /nganh-hoc/ ma rong thi la ngo cut -> bu bang dai hoc that cua cac quoc gia
	// tieu bieu, moi quoc gia lay luan phien mot truong nen danh sach khop voi dong
	// "Quoc gia tieu bieu" ngay phia tren, khong phai 4 truong cung mot nuoc.
	//
	// KHONG bu tren trang quoc gia ($country_slug co gia tri): o do 5 the nganh nam
	// canh nhau, bu vao la ca 5 the hien y het nhau (Tho Nhi Ky chi co 6 dai hoc).
	// The nganh khong co truong van hien tieu de + mo ta, va ngay phia tren da co
	// luoi truong cua chinh quoc gia do.
	$want = $limit > 0 ? $limit : 4;
	if ( ! $country_slug && count( $schools ) < $want ) {
		$seen = [];
		foreach ( $schools as $school ) {
			$seen[ (string) ( $school['id'] ?? '' ) ] = true;
		}

		$pools = [];
		foreach ( array_values( (array) ( $major['countries'] ?? [] ) ) as $country_name ) {
			$pool = duy_major_country_schools( duy_country_slug( (string) $country_name ) );
			if ( $pool ) {
				$pools[] = array_values( $pool );
			}
		}

		for ( $round = 0; $pools && count( $schools ) < $want; $round++ ) {
			$added = false;
			foreach ( $pools as $pool ) {
				if ( count( $schools ) >= $want ) {
					break;
				}
				$school = $pool[ $round ] ?? null;
				if ( ! is_array( $school ) ) {
					continue;
				}
				$added = true;
				$id    = (string) ( $school['id'] ?? '' );
				if ( '' === $id || isset( $seen[ $id ] ) ) {
					continue;
				}
				$seen[ $id ] = true;
				$schools[]   = $school;
			}
			if ( ! $added ) {
				break;
			}
		}
	}

	return $limit > 0 ? array_slice( $schools, 0, $limit ) : $schools;
}
