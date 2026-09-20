<?php
/**
 * Single news article.
 *
 * @package DUY_Study
 */

$id         = (string) duy_route_value( 'id', '' );
$news_items = duy_items_newest_first( duy_demo_news(), 'post' );
$article    = $id ? duy_find_by_id( $news_items, $id ) : null;
$post       = $id ? get_page_by_path( $id, OBJECT, 'post' ) : null;
$post_id    = $post ? (int) $post->ID : 0;

if ( ! $article && have_posts() ) {
	the_post();
	$categories = get_the_category();
	$post_id = (int) get_the_ID();
	$article = [
		'id'   => (string) get_post_field( 'post_name' ),
		'n'    => get_the_title(),
		'cat'  => $categories ? (string) $categories[0]->name : 'Tin tức',
		'c'    => get_the_date(),
		'lead' => get_the_excerpt(),
	];
	rewind_posts();
}

if ( ! $article ) {
	include duy_template_path( '404.php' );
	return;
}

$profile       = duy_news_profile_defaults( $article );
$lead          = (string) duy_field( 'lead', $post_id, $article['lead'] ?? '' );
$takeaway_rows = duy_rows( 'key_takeaways', $post_id );
$body_rows     = duy_rows( 'body_blocks', $post_id );
$quote_field   = (string) duy_field( 'quote', $post_id, '' );
$quote         = '' !== $quote_field ? $quote_field : (string) $profile['quote'];

// Bài viết thật (WP post có nội dung trong editor) → render post_content; khối demo chỉ cho bài mẫu.
$post_content = $post_id ? (string) get_post_field( 'post_content', $post_id ) : '';
$real_body    = ! $body_rows && '' !== trim( wp_strip_all_tags( $post_content ) );
$body_html    = '';
$toc          = [];
if ( $real_body ) {
	$body_html = (string) apply_filters( 'the_content', $post_content );
	$body_html = (string) preg_replace_callback(
		'/<h2([^>]*)>(.*?)<\/h2>/is',
		static function ( array $m ) use ( &$toc ): string {
			$title = trim( wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $m[1], $id_match ) ) {
				$toc[] = [ 'id' => $id_match[1], 'title' => $title ];

				return $m[0];
			}
			$id    = 'muc-' . ( count( $toc ) + 1 );
			$toc[] = [ 'id' => $id, 'title' => $title ];

			return '<h2 id="' . esc_attr( $id ) . '"' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$body_html
	);
} else {
	$toc = array_map(
		static fn( int $index, array $block ): array => [ 'id' => 'muc-' . ( $index + 1 ), 'title' => (string) ( $block['title'] ?? '' ) ],
		array_keys( $body_rows ?: $profile['body_blocks'] ),
		array_values( $body_rows ?: $profile['body_blocks'] )
	);
}

if ( ! $takeaway_rows && ! $real_body ) {
	$takeaway_rows = array_map(
		static fn( $item ) => [ 'item' => $item ],
		$profile['key_takeaways']
	);
}

$body_blocks = $body_rows ?: $profile['body_blocks'];

$related = array_values(
	array_filter(
		$news_items,
		static fn( $item ) => ( $item['id'] ?? '' ) !== ( $article['id'] ?? '' ) && ( $item['cat'] ?? '' ) === ( $article['cat'] ?? '' )
	)
);

$latest = array_values(
	array_filter(
		$news_items,
		static fn( $item ) => ( $item['id'] ?? '' ) !== ( $article['id'] ?? '' )
	)
);

$categories = array_values(
	array_unique(
		array_filter(
			array_map(
				static fn( $item ) => (string) ( $item['cat'] ?? '' ),
				$news_items
			)
		)
	)
);

get_header();
duy_page_hero(
	$article['n'],
	$lead,
	[
		[ 'label' => 'Trang chủ', 'url' => duy_url( '/' ) ],
		[ 'label' => 'Tin tức', 'url' => duy_route_path( 'tin-tuc' ) ],
		[ 'label' => $article['n'] ],
	],
	$article['cat']
);
?>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="article-cover">
			<?php if ( $post_id && has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'large', [ 'alt' => 'Ảnh bìa bài viết ' . $article['n'], 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
			<?php else : ?>
				<?php echo wp_kses_post( duy_image( 'cover_image', $post_id, 'photo-article-laptop.webp', 'Ảnh bìa bài viết ' . $article['n'] ) ); ?>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="band">
	<div class="wrap article-layout">
		<article class="article-body glass glass-strong">
			<p class="eyebrow"><?php echo esc_html( $article['c'] ); ?></p>
			<p class="lead"><?php echo esc_html( $lead ); ?></p>

			<?php if ( $takeaway_rows ) : ?>
				<div class="rich-section glass">
					<h2><?php esc_html_e( 'Tóm tắt nhanh', 'duy-study' ); ?></h2>
					<ul class="check-list">
						<?php foreach ( $takeaway_rows as $row ) : ?>
							<li><?php echo esc_html( $row['item'] ?? $row['title'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $real_body ) : ?>
				<div class="post-content">
					<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nội dung editor đã qua the_content (kses theo quyền tác giả). ?>
				</div>
				<?php if ( '' !== $quote_field ) : ?>
					<?php duy_part( 'pull-quote', [ 'text' => $quote_field ] ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php foreach ( $body_blocks as $index => $block ) : ?>
					<section id="<?php echo esc_attr( 'muc-' . ( $index + 1 ) ); ?>">
						<h2><?php echo esc_html( $block['title'] ?? '' ); ?></h2>
						<p><?php echo esc_html( $block['body'] ?? $block['desc'] ?? '' ); ?></p>
					</section>
					<?php if ( 0 === $index ) : ?>
						<?php duy_part( 'pull-quote', [ 'text' => $quote ] ); ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<div class="media-art" style="margin:1.4rem 0;min-height:260px">
					<?php echo wp_kses_post( duy_img( 'photo-library-study.webp', 'Học sinh đọc cẩm nang và ghi chú kế hoạch du học' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="rich-section glass">
				<h2><?php esc_html_e( 'Biến bài viết thành checklist hồ sơ', 'duy-study' ); ?></h2>
				<p><?php esc_html_e( 'Nếu gia đình đang ở đúng nhóm chủ đề này, chuyên viên Ban Du học Hội TESOL TP.HCM có thể giúp đối chiếu thông tin với quốc gia, trường, kỳ nhập học và ngân sách thực tế.', 'duy-study' ); ?></p>
				<a class="btn btn-primary" href="<?php echo esc_url( duy_route_path( 'lien-he' ) ); ?>"><?php esc_html_e( 'Tư vấn theo hồ sơ của tôi', 'duy-study' ); ?></a>
			</div>
		</article>

		<aside class="toc-card glass single-hero-card">
			<?php if ( $toc ) : ?>
				<h3><?php esc_html_e( 'Trong bài viết', 'duy-study' ); ?></h3>
				<?php foreach ( $toc as $entry ) : ?>
					<a href="#<?php echo esc_attr( (string) $entry['id'] ); ?>"><?php echo esc_html( (string) $entry['title'] ); ?></a>
				<?php endforeach; ?>
				<hr>
			<?php endif; ?>
			<h3><?php esc_html_e( 'Bài mới', 'duy-study' ); ?></h3>
			<?php foreach ( array_slice( $latest, 0, 3 ) as $item ) : ?>
				<a href="<?php echo esc_url( duy_news_url( (string) $item['id'] ) ); ?>"><?php echo esc_html( $item['n'] ); ?></a>
			<?php endforeach; ?>
			<hr>
			<h3><?php esc_html_e( 'Chuyên mục', 'duy-study' ); ?></h3>
			<?php foreach ( $categories as $category ) : ?>
				<a href="<?php echo esc_url( duy_route_path( 'tin-tuc' ) . '?category=' . rawurlencode( $category ) ); ?>"><?php echo esc_html( $category ); ?></a>
			<?php endforeach; ?>
		</aside>
	</div>
</section>

<?php if ( $related ) : ?>
	<section class="band">
		<div class="wrap">
			<div class="section-head">
				<span class="eyebrow"><?php esc_html_e( 'Bài liên quan', 'duy-study' ); ?></span>
				<h2><?php esc_html_e( 'Đọc tiếp cùng chủ đề', 'duy-study' ); ?></h2>
			</div>
			<div class="grid g3">
				<?php foreach ( array_slice( $related, 0, 3 ) as $item ) : ?>
					<?php duy_part( 'card-news', [ 'item' => $item ] ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php
get_footer();
