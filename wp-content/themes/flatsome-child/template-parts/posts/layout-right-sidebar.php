<?php
	do_action('flatsome_before_blog');
?>

<?php if(!is_single() && flatsome_option('blog_featured') == 'top'){ get_template_part('template-parts/posts/featured-posts'); } ?>

<div class="row <?php if(flatsome_option('blog_layout_divider')) echo 'row-divided ';?>">
	<?php
		if ( is_category() ) :
			// show an optional category description
			$category_description = category_description();
			if ( ! empty( $category_description ) ) :
				echo apply_filters( 'category_archive_meta', '<div class="large-12 col"><div class="taxonomy-description">' . $category_description . '</div></div>' );
			endif;

		elseif ( is_tag() ) :
			// show an optional tag description
			$tag_description = tag_description();
			if ( ! empty( $tag_description ) ) :
				echo apply_filters( 'tag_archive_meta', '<div class="large-12 col"><div class="taxonomy-description">' . $tag_description . '</div></div>' );
			endif;

		endif;
	?>
	<div class="large-8 area-news-content col">
	<?php if (is_category( 'khuyen-mai' )) : ?>
		<div class="title-news-sale">
			<h3>Tin khuyến mại</h3>
			<p>Chương trình khuyến mại hấp dẫn của tháng, giảm giá các sản phẩm điện thoại, máy tính bảng, phụ kiện có giá rẻ, hấp dẫn của Xuân Toại Mobile.</p>
		</div>
	<?php endif;?>
	<?php if (!is_category( 'khuyen-mai' )) : ?>
		<div class="title-news"><h3>Tin mới nhất</h3></div>
	<?php endif;?>
	<?php if(!is_single() && flatsome_option('blog_featured') == 'content'){ get_template_part('template-parts/posts/featured-posts'); } ?>
	<?php
		if(is_single()){
			get_template_part( 'template-parts/posts/single');
			comments_template();
		} elseif(flatsome_option('blog_style_archive') && (is_archive() || is_search())){
			get_template_part( 'template-parts/posts/archive', flatsome_option('blog_style_archive') );
		} else {
			get_template_part( 'template-parts/posts/archive', flatsome_option('blog_style') );
		}
	?>
	</div>
	<div class="post-sidebar large-4 col">
		<?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
		<?php if (is_category( 'khuyen-mai' )) : ?>
			<?php echo do_shortcode('[block id="tin-tuc-moi-nhat"]'); ?>
		<?php endif;?>
		<?php if (!is_category( 'khuyen-mai' )) : ?>
			<?php get_sidebar(); ?>
		<?php endif;?>
		<?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
	</div>
</div>

<?php
	do_action('flatsome_after_blog');
?>
