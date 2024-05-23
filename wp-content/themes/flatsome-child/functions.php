<?php
// Add custom Theme Functions here
//Copy từng phần và bỏ vào file functions.php của theme:
//xoa mã bưu điện thanh toán
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
	unset($fields['billing']['billing_postcode']);
	unset($fields['billing']['billing_country']);
	unset($fields['billing']['billing_address_2']);
	unset($fields['billing']['billing_company']);


	return $fields;
}
function register_my_menu() {
	register_nav_menu('product-menu',__( 'Menu Danh mục' ));
}
add_action( 'init', 'register_my_menu' );
//Doan code thay chữ giảm giá bằng % sale
//* Add stock status to archive pages
add_filter( 'woocommerce_get_availability', 'custom_override_get_availability', 1, 2);

// The hook in function $availability is passed via the filter!
function custom_override_get_availability( $availability, $_product ) {
	if ( $_product->is_in_stock() ) $availability['availability'] = __('Còn hàng', 'woocommerce');
	return $availability;
}
// Enqueue Scripts and Styles.
add_action( 'wp_enqueue_scripts', 'flatsome_enqueue_scripts_styles' );
function flatsome_enqueue_scripts_styles() {
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'flatsome-ionicons', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
}
function new_excerpt_more( $more ) {
	return '';
}
add_filter('excerpt_more', 'new_excerpt_more');
class Auto_Save_Images{

	function __construct(){     

		add_filter( 'content_save_pre',array($this,'post_save_images') ); 
	}

	function post_save_images( $content ){
		if( ($_POST['save'] || $_POST['publish'] )){
			set_time_limit(240);
			global $post;
			$post_id=$post->ID;
			$preg=preg_match_all('/<img.*?src="(.*?)"/',stripslashes($content),$matches);
			if($preg){
				foreach($matches[1] as $image_url){
					if(empty($image_url)) continue;
					$pos=strpos($image_url,$_SERVER['HTTP_HOST']);
					if($pos===false){
						$res=$this->save_images($image_url,$post_id);
						$replace=$res['url'];
						$content=str_replace($image_url,$replace,$content);
					}
				}
			}
		}
		remove_filter( 'content_save_pre', array( $this, 'post_save_images' ) );
		return $content;
	}

	function save_images($image_url,$post_id){
		$file=file_get_contents($image_url);
		$post = get_post($post_id);
		$posttitle = $post->post_title;
		$postname = sanitize_title($posttitle);
		$im_name = "$postname-$post_id.jpg";
		$res=wp_upload_bits($im_name,'',$file);
		$this->insert_attachment($res['file'],$post_id);
		return $res;
	}

	function insert_attachment($file,$id){
		$dirs=wp_upload_dir();
		$filetype=wp_check_filetype($file);
		$attachment=array(
			'guid'=>$dirs['baseurl'].'/'._wp_relative_upload_path($file),
			'post_mime_type'=>$filetype['type'],
			'post_title'=>preg_replace('/\.[^.]+$/','',basename($file)),
			'post_content'=>'',
			'post_status'=>'inherit'
		);
		$attach_id=wp_insert_attachment($attachment,$file,$id);
		$attach_data=wp_generate_attachment_metadata($attach_id,$file);
		wp_update_attachment_metadata($attach_id,$attach_data);
		return $attach_id;
	}
}
new Auto_Save_Images();
// Add our custom product cat rewrite rules
function devvn_product_category_rewrite_rules($flash = false) {
	$terms = get_terms( array(
		'taxonomy' => 'product_cat',
		'post_type' => 'product',
		'hide_empty' => false,
	));
	if($terms && !is_wp_error($terms)){
		$siteurl = esc_url(home_url('/'));
		foreach ($terms as $term){
			$term_slug = $term->slug;
			$baseterm = str_replace($siteurl,'',get_term_link($term->term_id,'product_cat'));
			add_rewrite_rule($baseterm.'?$','index.php?product_cat='.$term_slug,'top');
			add_rewrite_rule($baseterm.'page/([0-9]{1,})/?$', 'index.php?product_cat='.$term_slug.'&paged=$matches[1]','top');
			add_rewrite_rule($baseterm.'(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?product_cat='.$term_slug.'&feed=$matches[1]','top');
		}
	}
	if ($flash == true)
		flush_rewrite_rules(false);
}
add_action('init', 'devvn_product_category_rewrite_rules');

// Add custom Theme Functions here
 function limited_title($char) {
$title = get_the_title($post->ID);$title = substr($title,0,$char);echo $title; 
}

function mx_ux_builder_element(){
	add_ux_builder_shortcode('mx_hook', array(
		'name'      => __( 'MX' ),
		'thumbnail' => '',
		'info'      => '{{ hook }}',
		'options'   => array(
			'hook' => array(
				'type'    => 'select',
				'heading' => 'Hook',
				'default' => 'mx_load_more_product',
				'options' =>  array(
					'mx_load_more_product'          => 'mx_load_more_product',
				) ,
			),
		),
	));
}
add_action('mx_load_more_product', function(){
	echo do_shortcode( '[mx_load_more_product]' );
});

add_action('ux_builder_setup', 'mx_ux_builder_element');
add_shortcode( 'mx_hook', function ( $atts ) {
	extract( shortcode_atts( array(
		'hook' => 'mx_load_more_product',
	), $atts ) );
	ob_start();
	do_action( $hook );

	return ob_get_clean();
} );
function mx_load_more_product($atts) {
	extract(shortcode_atts(array(
		'_id' => 'mx_product-'.rand(),
		'number' => 10,
		'view_more' => false,
		'paged' => 1,
		'post__not_in' => array(),

	), $atts));

	ob_start();
	$product_visibility_term_ids = wc_get_product_visibility_term_ids();
	$args = [
		'posts_per_page'      => $number,
		'post_status'         => 'publish',
		'post_type'           => 'product',
		'no_found_rows'       => 1,
		'ignore_sticky_posts' => 1,
		'orderby' => 'rand',
		'order' => 'DESC',
	];
	if($view_more){
		$args['no_found_rows'] = 0;
		$args['orderby'] = 'date';
		$args['paged'] = $paged;
		$args['post__not_in'] = $not_in;
	}
	$products = new WP_Query( $args );
	$output = '';
	$ids = array();
	if($view_more){
		$max_pages = $products->max_num_pages;
	}
	if ( $products->have_posts() ) : ?>
<?php while ( $products->have_posts() ) : $products->the_post();
	if(!$view_more) $ids[] = get_the_ID();
?>
<div class="col">
	<div class="col-inner">
		<?php woocommerce_show_product_loop_sale_flash(); ?>
		<div class="product-small box has-hover box-normal box-text-bottom">
			<div class="box-image">
				<div class="">
					<a href="<?php echo get_the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
						<?php
	echo woocommerce_get_product_thumbnail('woocommerce_thumbnail');
						?>
					</a>
				</div>
				<div class="image-tools top right show-on-hover">
					<?php do_action('flatsome_product_box_tools_top'); ?>
				</div>

				<div class="image-tools <?php echo flatsome_product_box_actions_class(); ?>">
					<?php do_action('flatsome_product_box_actions'); ?>
				</div>
			</div>

			<div class="box-text text-center">
				<?php
	do_action('woocommerce_before_shop_loop_item_title');

	echo '<div class="title-wrapper">';
	do_action('woocommerce_shop_loop_item_title');
	echo '</div>';

	echo '<div class="price-wrapper">';
	do_action('woocommerce_after_shop_loop_item_title');
	echo '</div>';


	do_action('flatsome_product_box_after');

				?>
			</div>
		</div>
	</div>
</div>
<?php endwhile; ?>
<?php endif;
	wp_reset_postdata();
?>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	if($view_more){
		$max_pages = $products->max_num_pages;
		$result = [
			'max' => $max_pages,
			'html' => $output,
		];
		return json_encode($result);
	}else{
		$output = '<div class="row  equalize-box large-columns-5 medium-columns-3 small-columns-2 row-small" id="gg">'.$output.'</div><div class="container text-center"> <button class="view-more-button products-archive button primary" id="load-more" data-not_in="'.json_encode($ids).'">Xem thêm</button> </div>';
		return $output;
	}

}
add_shortcode('mx_load_more_product', 'mx_load_more_product');

function mx_load_more() {
	echo do_shortcode( '[mx_load_more_product view_more="true" paged="'.$_POST["paged"].'" not_in="'.$_POST["not_in"].'"]' );
	exit;
}
add_action('wp_ajax_mx_load_more', 'mx_load_more');
add_action('wp_ajax_nopriv_mx_load_more', 'mx_load_more');

function mx_load_more_js() {
	if(is_front_page ()) :
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		let currentPage = 0;
		$('#load-more').on('click', function() {
			currentPage++; // Do currentPage + 1, because we want to load the next page
			$('#load-more').addClass('loading');
			$.ajax({
				type: 'POST',
				url: '/wp-admin/admin-ajax.php',
				dataType: 'json',
				data: {
					action: 'mx_load_more',
					paged: currentPage,
					not_in: $('#load-more').data('not_in')
				},
				success: function (res) {
					$('#load-more').removeClass('loading');
					if(currentPage  >= res.max) {
						$('#load-more').hide();
					}
					$('#gg').append(res.html);
				}
			});
		});
	});
</script>
<?php endif;
}
add_action( 'wp_footer', 'mx_load_more_js' );


add_filter( 'the_title', 'short_title_product', 10, 2 );
function short_title_product( $title, $id ) {
    if (get_post_type( $id ) === 'product' & !is_single() ) {
        return wp_trim_words( $title, 10 ); // thay đổi số từ bạn muốn thêm
    } else {
        return $title;
    }
}