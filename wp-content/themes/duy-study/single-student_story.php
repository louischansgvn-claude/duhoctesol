<?php
/**
 * Single student story.
 *
 * @package DUY_Study
 */

$id      = (string) duy_route_value( 'id', '' );
$pair    = duy_find_by_id( duy_demo_pairs(), $id );
$story   = duy_find_by_id( duy_demo_stories(), $id );
$item    = $pair ?: $story;
$post    = $id ? get_page_by_path( $id, OBJECT, 'student_story' ) : null;
$post_id = $post ? (int) $post->ID : 0;

if ( ! $item ) {
	include duy_template_path( '404.php' );
	return;
}

$name    = (string) ( $item['name'] ?? $item['n'] ?? 'Học sinh Ban Du học Hội TESOL TP.HCM' );
$school  = (string) ( $item['school'] ?? $item['s'] ?? 'Trường đối tác' );
$quote   = (string) ( $item['quote'] ?? $item['q'] ?? '' );
$profile = duy_story_profile_defaults( $item );

$major        = (string) duy_field( 'major', $post_id, $profile['major'] );
$level        = (string) duy_field( 'level', $post_id, $profile['level'] );
$year         = (string) duy_field( 'year', $post_id, $profile['year'] );
$story_blocks = duy_rows( 'story_blocks', $post_id ) ?: $profile['story_blocks'];
$advice       = (string) duy_field( 'advice', $post_id, $profile['advice'] );
$result_stats = duy_rows( 'result_stats', $post_id ) ?: $profile['result_stats'];
$country      = (string) ( $profile['country'] ?? 'Úc' );

$related = array_values(
	array_filter(
		array_merge( duy_demo_pairs(), duy_demo_stories() ),
		static fn( $related_item ) => ( $related_item['id'] ?? '' ) !== $id
	)
);

get_header();
duy_page_hero(
	$name,
	$school,
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Học sinh Ban Du học Hội TESOL TP.HCM', 'url' => duy_route_path( 'hoc-sinh' ) ],
		[ 'label' => $name ],
	],
	'Câu chuyện học sinh'
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="feature-block glass glass-strong">
			<div class="feature-copy">
				<span class="eyebrow"><?php echo esc_html( $item['award'] ?? 'Kết quả học sinh' ); ?></span>
				<h2><?php echo esc_html( $name ); ?></h2>
				<?php duy_part( 'pull-quote', [ 'text' => $quote ?: $advice ] ); ?>
				<div class="info-list">
					<p><?php echo duy_icon( 'cap' ); ?> <?php echo esc_html( $school ); ?></p>
					<p><?php echo duy_icon( 'book' ); ?> <?php echo esc_html( $major ); ?></p>
					<p><?php echo duy_icon( 'calendar' ); ?> <?php echo esc_html( $year ); ?></p>
				</div>
			</div>
			<?php if ( $pair ) : ?>
				<?php duy_part( 'video-pair', [ 'item' => $pair ] ); ?>
			<?php else : ?>
				<div class="media-art">
					<?php echo wp_kses_post( duy_img( 'hero-students.svg', 'Ảnh minh họa câu chuyện ' . $name ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap quick-facts">
		<div class="stat glass"><b><?php echo esc_html( $school ); ?></b><span><?php esc_html_e( 'Trường', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $major ); ?></b><span><?php esc_html_e( 'Ngành', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $level ); ?></b><span><?php esc_html_e( 'Bậc học', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $item['award'] ?? '' ); ?></b><span><?php esc_html_e( 'Học bổng/kết quả', 'duy-study' ); ?></span></div>
		<div class="stat glass"><b><?php echo esc_html( $country ); ?></b><span><?php esc_html_e( 'Quốc gia', 'duy-study' ); ?></span></div>
	</div>
</section>

<section class="band">
	<div class="wrap rich-layout">
		<div class="rich-main">
			<section class="rich-section glass glass-strong">
				<span class="eyebrow"><?php esc_html_e( 'Hành trình', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Từ điểm xuất phát đến kết quả', 'duy-study' ); ?></h2>
				<div class="guide-steps">
					<?php foreach ( $story_blocks as $index => $block ) : ?>
						<div class="guide-step glass">
							<span class="num"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div>
								<h3><?php echo esc_html( $block['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $block['desc'] ?? $block['body'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="rich-section glass">
				<h2><?php esc_html_e( 'Lời khuyên cho học sinh đi sau', 'duy-study' ); ?></h2>
				<?php duy_part( 'pull-quote', [ 'text' => $advice ] ); ?>
			</section>

			<section class="rich-section glass">
				<h2><?php esc_html_e( 'Kết quả nổi bật', 'duy-study' ); ?></h2>
				<div class="story-stats">
					<?php foreach ( $result_stats as $stat ) : ?>
						<div class="stat glass">
							<b><?php echo esc_html( $stat['value'] ?? '' ); ?></b>
							<span><?php echo esc_html( $stat['label'] ?? '' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<?php if ( $related ) : ?>
				<section class="rich-section glass">
					<h2><?php esc_html_e( 'Câu chuyện liên quan', 'duy-study' ); ?></h2>
					<div class="grid g3">
						<?php foreach ( array_slice( $related, 0, 3 ) as $related_item ) : ?>
							<?php duy_part( 'card-story', [ 'item' => $related_item ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>

		<aside class="glass single-hero-card toc-card">
			<h3><?php esc_html_e( 'Bắt đầu lộ trình tương tự', 'duy-study' ); ?></h3>
			<p class="muted"><?php esc_html_e( 'Ban Du học Hội TESOL TP.HCM sẽ giúp bạn chuyển mục tiêu thành timeline, shortlist trường, học bổng và checklist hồ sơ.', 'duy-study' ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Đăng ký tư vấn', 'duy-study' ); ?></a>
			<div class="info-list">
				<p><?php echo duy_icon( 'check' ); ?> <?php esc_html_e( 'So sánh trường và ngành', 'duy-study' ); ?></p>
				<p><?php echo duy_icon( 'check' ); ?> <?php esc_html_e( 'Rà soát học bổng phù hợp', 'duy-study' ); ?></p>
				<p><?php echo duy_icon( 'check' ); ?> <?php esc_html_e( 'Lập timeline hồ sơ', 'duy-study' ); ?></p>
			</div>
		</aside>
	</div>
</section>
<?php
get_footer();
