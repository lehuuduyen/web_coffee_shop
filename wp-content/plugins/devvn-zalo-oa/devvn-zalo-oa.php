<?php
/*
* Plugin Name: DevVN - Zalo OA for WordPress
* Version: 1.1.5
* Requires PHP: 7.2
* Description: Tích hợp Zalo OA vào WordPress + Woocommerce để gửi thông tin đơn hàng
* Author: Lê Văn Toản
* Author URI: https://levantoan.com
* Plugin URI: https://levantoan.com
* Text Domain: devvn-zalo-oa
* Domain Path: /languages
* WC requires at least: 3.5.4
* WC tested up to: 8.5.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'DEVVN_ZALOOA_VERSION_NUM' ) )
    define( 'DEVVN_ZALOOA_VERSION_NUM', '1.1.5' );
if ( !defined( 'DEVVN_ZALOOA_URL' ) )
    define( 'DEVVN_ZALOOA_URL', plugin_dir_url( __FILE__ ) );
if ( !defined( 'DEVVN_ZALOOA_BASENAME' ) )
    define( 'DEVVN_ZALOOA_BASENAME', plugin_basename( __FILE__ ) );
if ( !defined( 'DEVVN_ZALOOA_PLUGIN_DIR' ) )
    define( 'DEVVN_ZALOOA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( !defined( 'DEVVN_ZALOOA_TEXTDOMAIN' ) )
    define( 'DEVVN_ZALOOA_TEXTDOMAIN', 'devvn-zalo-oa' );


include_once DEVVN_ZALOOA_PLUGIN_DIR . 'includes/zalooa-sms-table.php';
include_once DEVVN_ZALOOA_PLUGIN_DIR . 'includes/zalooa-send-background-process.php';
include_once DEVVN_ZALOOA_PLUGIN_DIR . 'includes/get-follows-background-process.php';

if(extension_loaded('ionCube Loader')) {
    if(file_exists(plugin_dir_path(__FILE__) . 'license.php')){
        include_once plugin_dir_path(__FILE__) . 'license.php';
    }
    include 'includes/main.php';
}else{
    function devvn_zalooa_admin_notice__error() {
        $class = 'notice notice-alt notice-warning notice-error';
        $title = '<h2 class="notice-title">Chú ý!</h2>';
        $message = __( 'Để Plugin <strong>DevVN - Zalo OA for WordPress</strong> hoạt động, bắt buộc cần kích hoạt <strong>php extension ionCube</strong>.', DEVVN_ZALOOA_TEXTDOMAIN );
        $btn = '<p><a href="https://levantoan.com/huong-dan-kich-hoat-extension-ioncube/" target="_blank" rel="nofollow" class="button-primary">Xem hướng dẫn tại đây</a></a></p>';

        printf( '<div class="%1$s">%2$s<p>%3$s</p>%4$s</div>', esc_attr( $class ), $title, $message, $btn );
    }
    add_action( 'admin_notices', 'devvn_zalooa_admin_notice__error' );
}
