<?php
/**
 * Fallback template.
 *
 * @package DUY_Study
 */

get_header();
?>
<section class="page-hero wrap">
	<div class="breadcrumb"><a href="<?php echo esc_url( duy_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'duy-study' ); ?></a> / <?php esc_html_e( 'Nội dung', 'duy-study' ); ?></div>
	<h1><?php esc_html_e( 'Nội dung Ban Du học Hội TESOL TP.HCM', 'duy-study' ); ?></h1>
</section>
<section class="band" style="padding-top:1rem">
	<div class="wrap grid g3">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'card glass' ); ?>>
				<div class="body">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="muted"><?php the_excerpt(); ?></div>
				</div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<div class="empty-state glass">
			<span class="icon-tile"><?php echo duy_icon( 'search' ); ?></span>
			<h3><?php esc_html_e( 'Chưa có nội dung.', 'duy-study' ); ?></h3>
		</div>
	<?php endif; ?>
	</div>
</section>
<?php
get_footer();
