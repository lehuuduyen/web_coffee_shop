<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
	$review_count = $product->get_review_count();
// Ensure visibility.
if ( fl_woocommerce_version_check( '4.4.0' ) ) {
	if ( empty( $product ) || false === wc_get_loop_product_visibility( $product->get_id() ) || ! $product->is_visible() ) {
		return;
	}
} else {
	if ( empty( $product ) || ! $product->is_visible() ) {
		return;
	}
}

// Check stock status.
$out_of_stock = ! $product->is_in_stock();

// Extra post classes.
$classes   = array();
$classes[] = 'product-small';
$classes[] = 'col';
$classes[] = 'has-hover';

if ( $out_of_stock ) $classes[] = 'out-of-stock';

?>

<div <?php wc_product_class( $classes, $product ); ?>>
	<div class="col-inner">
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
	<div class="product-small box <?php echo flatsome_product_box_class(); ?>">
		<div class="box-image">
			<div class="<?php echo flatsome_product_box_image_class(); ?>">
				<a href="<?php echo get_the_permalink(); ?>" aria-label="<?php echo esc_attr( $product->get_title() ); ?>">
					<?php
						/**
						 *
						 * @hooked woocommerce_get_alt_product_thumbnail - 11
						 * @hooked woocommerce_template_loop_product_thumbnail - 10
						 */
						do_action( 'flatsome_woocommerce_shop_loop_images' );
					?>
				</a>
				<div class="item-hotsale"></div>
			</div>
			<div class="image-tools is-small top right show-on-hover">
				<?php do_action( 'flatsome_product_box_tools_top' ); ?>
			</div>
			<div class="image-tools is-small hide-for-small bottom left show-on-hover">
				<?php do_action( 'flatsome_product_box_tools_bottom' ); ?>
			</div>
			<div class="image-tools <?php echo flatsome_product_box_actions_class(); ?>">
				<?php do_action( 'flatsome_product_box_actions' ); ?>
			</div>
			<?php if ( $out_of_stock ) { ?><div class="out-of-stock-label"><?php _e( 'Out of stock', 'woocommerce' ); ?></div><?php } ?>
		</div>

		<div class="box-text <?php echo flatsome_product_box_text_class(); ?>">
			<?php
				$short_promotion = get_field( 'short_promotion' );
				$cpu_laptop = get_field( 'cpu_laptop' );
				$screen_size = get_field( 'screen_size' );
				$ram_laptop = get_field( 'ram_laptop' );
				$hdd_laptop = get_field( 'hdd_laptop' );
				$chipset_laptop = get_field( 'chipset_laptop' );
				
				do_action( 'woocommerce_before_shop_loop_item_title' );
				echo '<div class="title-wrapper">';
				do_action( 'woocommerce_shop_loop_item_title' );
				echo '</div>';
				echo '<div class="price-wrapper">';
				do_action( 'woocommerce_after_shop_loop_item_title' );
				
				if ( is_front_page() ) {
					if ($cpu_laptop) { 
						echo '<div class="configuration">';
						echo '<div class="cpu-laptop"><span>CPU:</span>' . $cpu_laptop . '</div>';
						if ($screen_size) { echo '<div class="screen-laptop"><span>Màn hình:</span>' . $screen_size . '</div>'; }
						echo '</div>';
					}
				}					
				if ( is_archive() ) {
					echo '<div class="item-product-more-info">';
					if ($screen_size) { echo '<span>' . $screen_size . '</span>'; }
					if ($ram_laptop) { echo '<span>' . $ram_laptop . '</span>'; }
					if ($hdd_laptop) { echo '<span>' . $hdd_laptop . '</span>'; }
					if ($chipset_laptop) { echo '<span>' . $chipset_laptop . '</span>'; }
					echo '</div>';
				}
				
				if ($short_promotion) { echo '<div class="promotion">' . $short_promotion . '</div>'; }
				if ($review_count) { echo '<span class="text-count-review">' . esc_html( $review_count ) . ' đánh giá</span>'; }
				echo '</div>';

				do_action( 'flatsome_product_box_after' );

			?>
		</div>
	</div>
	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
	</div>
</div>
