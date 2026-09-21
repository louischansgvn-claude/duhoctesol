<?php
/**
 * Student story archive.
 *
 * @package DUY_Study
 */

$stories = duy_demo_stories();
$pairs   = duy_demo_pairs();

get_header();
duy_page_hero(
	'Học sinh Ban Du học Hội TESOL TP.HCM',
	'Câu chuyện học sinh và video Ask Ban Du học Hội TESOL TP.HCM từ hai góc nhìn: học viên và trường.',
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Học sinh Ban Du học Hội TESOL TP.HCM' ],
	],
	'Stories'
);
?>
<?php if ( $stories || $pairs ) : ?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php esc_html_e( 'Ask Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Câu chuyện có cả góc nhìn học viên và trường', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Trang học sinh có video-pair, ảnh minh họa và quote để tránh toàn bộ màn là các khung thông tin giống nhau.', 'duy-study' ); ?></p>
			</div>
			<?php duy_part( 'image-cluster', [ 'alt' => 'Học sinh Ban Du học Hội TESOL TP.HCM', 'images' => [ 'hero-students.svg', 'photo-team-office.webp', 'photo-campus-library.webp' ], 'stat' => [ '2', 'góc nhìn trong một câu chuyện' ] ] ); ?>
		</div>
	</div>
</section>
<?php if ( $pairs ) : ?>
<section class="band">
	<div class="wrap">
		<div class="vp-carousel" tabindex="0" aria-label="<?php esc_attr_e( 'Carousel video Ask Duy', 'duy-study' ); ?>">
			<button class="vp-nav vp-prev" type="button" data-vp-prev aria-label="<?php esc_attr_e( 'Video trước', 'duy-study' ); ?>"><?php echo duy_icon( 'arrow' ); ?></button>
			<div class="vp-track">
				<?php foreach ( $pairs as $pair ) : ?>
					<?php duy_part( 'video-pair', [ 'item' => $pair ] ); ?>
				<?php endforeach; ?>
			</div>
			<button class="vp-nav vp-next" type="button" data-vp-next aria-label="<?php esc_attr_e( 'Video tiếp theo', 'duy-study' ); ?>"><?php echo duy_icon( 'arrow' ); ?></button>
		</div>
	</div>
</section>
<?php endif; ?>
<?php if ( $stories ) : ?>
<section class="band">
	<div class="wrap grid g4">
		<?php foreach ( $stories as $story ) : ?>
			<?php duy_part( 'card-story', [ 'item' => $story ] ); ?>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
<?php else : ?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="empty-state glass">
			<?php echo duy_icon_tile( 'cap' ); ?>
			<h2><?php esc_html_e( 'Câu chuyện học sinh đang được cập nhật', 'duy-study' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Chúng tôi chỉ đăng câu chuyện của học sinh có thật, khi học sinh và gia đình đồng ý chia sẻ kết quả của mình.', 'duy-study' ); ?></p>
			<p class="muted"><?php esc_html_e( 'Nếu bạn muốn biết hồ sơ như thế nào thì phù hợp với trường nào, chuyên viên sẽ trao đổi trực tiếp thay vì để bạn đọc một câu chuyện chung chung.', 'duy-study' ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
		</div>
	</div>
</section>
<?php endif; ?>
<?php
get_footer();
