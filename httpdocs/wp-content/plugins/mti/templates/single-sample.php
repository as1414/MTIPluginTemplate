<?php 

// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'single' );

?>

<section class="recipe-banner" style="background-image: url(<?php echo get_the_post_thumbnail_url(null, 'full'); ?>);">
	<div class="title-container">
		<h1><?php echo get_the_title(); ?></h1>
		<!--<a href="/recipes/">view all recipes</a>-->
	</div>
</section>

<div class="container single_recipe">
	<div class="row">
		<div class="col-md-12">
			<?php if(have_posts()): while(have_posts()): the_post(); ?>
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<!-- <div class="post_thumbnail text-center">
						<?php //if(has_post_thumbnail()){ the_post_thumbnail('large'); } ?>
					</div> -->

					<!-- <div class="post_title text-center">
						<h1><?php //echo get_the_title(); ?></h1>
					</div> -->

					<div class="post_content">
						<?php the_content(); ?>
					</div>
				</div>
			<?php endwhile; endif; ?>
			<div class="vc_btn3-container vc_btn3-center">
				<a style="background-color:#666666; color:#ffffff;" class="vc_general vc_btn3 vc_btn3-size-lg vc_btn3-shape-rounded vc_btn3-style-custom" a="" href="/recipes/" title="View All Recipes">View All Recipes</a>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>