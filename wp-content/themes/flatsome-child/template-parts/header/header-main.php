<div id="masthead" class="header-main <?php header_inner_class('main'); ?>">
      <div class="header-inner flex-row container <?php flatsome_logo_position(); ?>" role="navigation">
	<?php $icon_style = flatsome_option('menu_icon_style'); ?>
		<div class="nav-icon has-icon mx_menu">

			<a href="#" data-open="#main-menu" data-pos="<?php echo flatsome_option('mobile_overlay');?>" data-bg="main-menu-overlay" data-color="<?php echo flatsome_option('mobile_overlay_color');?>" class="<?php echo get_flatsome_icon_class($icon_style, 'small'); ?>" aria-label="<?php echo __('Menu','flatsome'); ?>" aria-controls="main-menu" aria-expanded="false">

				<?php echo get_flatsome_icon('icon-menu'); ?>

				<?php if(flatsome_option('menu_icon_title')) echo '<span class="menu-title uppercase hide-for-small">'.__('Menu','flatsome').'</span>'; ?>
			</a>

		</div>
          <!-- Logo -->
          <div id="logo" class="flex-col logo">
<!-- 			<a id="btnHeaderMenu" href="#"><i class="icon-menu"></i></a> -->
            <?php get_template_part('template-parts/header/partials/element','logo'); ?>
		
          </div>
		  <div class="flex-col">
			  <a id="btnHeaderMenu" href="#">
				  <span>Danh mục</span><i class="icon-arrow-down"></i>
			  </a>
			  <div class="header-main-menu">
				  <div class="menu-top-menu-container">
					  <?php if ( is_active_sidebar( 'menu-main' ) ) : ?>
					  <?php dynamic_sidebar( 'menu-main' ); ?>
					  <?php endif; ?>
				  </div>
			  </div>
		  </div>
          <!-- Mobile Left Elements -->
          <div class="flex-col show-for-medium flex-left">
            <ul class="mobile-nav nav nav-left <?php flatsome_nav_classes('main-mobile'); ?>">
              <?php flatsome_header_elements('header_mobile_elements_left','mobile'); ?>
            </ul>
          </div>

          <!-- Left Elements -->
          <div class="flex-col hide-for-medium flex-left
            <?php if(get_theme_mod('logo_position', 'left') == 'left') echo 'flex-grow'; ?>">
            <ul class="header-nav header-nav-main nav nav-left <?php flatsome_nav_classes('main'); ?>" >
              <?php flatsome_header_elements('header_elements_left'); ?>
            </ul>
          </div>

          <!-- Right Elements -->
          <div class="flex-col hide-for-medium flex-right">
            <ul class="header-nav header-nav-main nav nav-right <?php flatsome_nav_classes('main'); ?>">
              <?php flatsome_header_elements('header_elements_right'); ?>
            </ul>
          </div>

          <!-- Mobile Right Elements -->
          <div class="flex-col show-for-medium flex-right">
            <ul class="mobile-nav nav nav-right <?php flatsome_nav_classes('main-mobile'); ?>">
              <?php flatsome_header_elements('header_mobile_elements_right','mobile'); ?>
            </ul>
          </div>

      </div>
     
      <?php if(get_theme_mod('header_divider', 1)) { ?>
      <div class="container"><div class="top-divider full-width"></div></div>
      <?php }?>
</div>