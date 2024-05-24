<!DOCTYPE html>
<!--[if IE 9 ]> <html <?php language_attributes(); ?> class="ie9 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if IE 8 ]> <html <?php language_attributes(); ?> class="ie8 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>"> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
	<!-- <script src="https://kit.fontawesome.com/62ecb389af.js" crossorigin="anonymous"></script> -->


</head>

<body <?php body_class(); ?>>

<?php do_action( 'flatsome_after_body_open' ); ?>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'flatsome' ); ?></a>
<div id="wrapper">
<div class="header-overlay"></div>
	<?php do_action( 'flatsome_before_header' ); ?>

	<header id="header" class="header <?php flatsome_header_classes(); ?>">
		<div class="header-wrapper">
			<?php get_template_part( 'template-parts/header/header', 'wrapper' ); ?>
		</div>
	</header>
 	<div class="menu-mobile-section">
		<div class="menu-mobile-header flex-row">
			<div class="logo flex-col">
				 <?php get_template_part('template-parts/header/partials/element','logo'); ?>
			</div>
			<div class="search-mobile-header flex-col">
				<?php echo do_shortcode('[search style="'.flatsome_option('header_search_form_style').'"]'); ?>
			</div>
			<div class="cart-mobile-header flex-col">
				<?php
				// Get Cart replacement for catalog_mode
				if(flatsome_option('catalog_mode')) { get_template_part('template-parts/header/partials/element','cart-replace'); return;}
				$cart_style = flatsome_option('header_cart_style');
				$custom_cart_content = flatsome_option('html_cart_header');
				$icon_style = flatsome_option('cart_icon_style');
				$icon = flatsome_option('cart_icon');
				// $disable_mini_cart = apply_filters( 'flatsome_disable_mini_cart', is_cart() || is_checkout() );
				// if ( $disable_mini_cart ) {
				// 	$cart_style = 'link';
				// }
				?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php _e('Cart', 'woocommerce'); ?>" class="header-cart-link <?php echo get_flatsome_icon_class($icon_style, 'small'); ?>">

					<?php
					if(flatsome_option('custom_cart_icon')) { ?>
					<span class="image-icon header-cart-icon" data-icon-label="<?php echo WC()->cart->cart_contents_count; ?>">
						<img class="cart-img-icon" alt="<?php _e('Cart', 'woocommerce'); ?>" src="<?php echo do_shortcode(flatsome_option('custom_cart_icon')); ?>"/>
					</span>
					<?php }
					else { ?>
					<?php if(!$icon_style) { ?>
					<span class="cart-icon image-icon">
						<strong><?php echo WC()->cart->cart_contents_count; ?></strong>
					</span>
					<?php } else { ?>
					<i class="icon-shopping-<?php echo $icon;?>"
					   data-icon-label="<?php echo WC()->cart->cart_contents_count; ?>">
					</i>
					<?php } ?>
					<?php }  ?>
				</a>
			</div>
		</div>
		<div class="list-menu-mobile">
			<div class="main-menu-mobile">
				<ul class="nav-menu-mobile">
					<?php 
						flatsome_header_elements( 'mobile_sidebar', 'sidebar' ); 
					?>
				</ul>
			</div>
		</div>
	</div> 
	<?php do_action( 'flatsome_after_header' ); ?>

	<main id="main" class="<?php flatsome_main_classes(); ?>">
