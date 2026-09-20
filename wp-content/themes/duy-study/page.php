<?php
/**
 * Default page template.
 *
 * @package DUY_Study
 */

get_header();
?>
<section class="page-hero wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<div class="breadcrumb"><a href="<?php echo esc_url( duy_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'duy-study' ); ?></a> / <?php the_title(); ?></div>
			<h1><?php the_title(); ?></h1>
		</article>
	<?php endwhile; ?>
</section>
<section class="band" style="padding-top:1rem">
	<div class="wrap">
		<div class="glass" style="padding:clamp(1.3rem,3vw,2rem)">
			<?php
			rewind_posts();
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</div>
</section>
<?php
get_footer();
