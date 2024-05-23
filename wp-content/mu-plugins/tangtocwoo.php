<?php
/**
 * Plugin Name: Gửi Email Không đồng bộ
 * Plugin URI: https://quueenbox.vn
 * Description: tang toc gui mail woo
 * Author: Queenbox
 * Author URI: https://quueenbox.vn
 */


if ( ! defined( 'DOING_CRON' ) || ( defined( 'DOING_CRON' ) && ! DOING_CRON ) ) {

    function wp_mail() {
        $args = func_get_args();
        $args[] = mt_rand();
        wp_schedule_single_event( time() + 5, 'cron_send_mail_queenboxvn', $args );
    }
}

function queenboxvn_cron_send_mail() {

    $args = func_get_args();

    array_pop( $args );

    call_user_func_array( 'wp_mail', $args );
}

add_action( 'cron_send_mail_queenboxvn', 'queenboxvn_cron_send_mail', 10, 10 );