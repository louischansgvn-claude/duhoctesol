<?php
/**
 * Majors hub template.
 *
 * @package DUY_Study
 */

$majors = duy_public_value( duy_mockup_v2_majors() );

get_header();
duy_page_hero(
	'Ngành học HOT',
	'Những nhóm ngành được học sinh và phụ huynh quan tâm nhất — xem định hướng, cơ hội nghề nghiệp và trường tiêu biểu cho từng ngành.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Ngành học HOT' ],
	],
	'Ngành học'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="guide-cards cols-3">
			<?php foreach ( $majors as $major ) : ?>
				<?php
				duy_part(
					'guide-card',
					[
						'title' => (string) ( $major['title'] ?? '' ),
						'text'  => (string) ( $major['lead'] ?? '' ),
						'icon'  => (string) ( $major['icon'] ?? 'sparkles' ),
						'url'   => duy_major_url( (string) ( $major['slug'] ?? '' ) ),
					]
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
