<?php
/*
Template name: Page - Full Width
*/
get_header(); ?>

<?php do_action( 'flatsome_before_page' ); ?>

<div id="content" role="main" class="content-area">
	<?php if ( is_front_page() ) { ?>
	 
				<div class="menu-mobile">
						<?php if ( is_active_sidebar( 'menu-mobile' ) ) : ?>
						<?php dynamic_sidebar( 'menu-mobile' ); ?>
						<?php endif; ?>
				</div>
			</div>
	 
	 
	<?php } ?>
	
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; // end of the loop. ?>
		
</div>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
