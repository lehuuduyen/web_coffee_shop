<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    include_once DEVVN_ZALOOA_PLUGIN_DIR . '/includes/lib/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    include_once DEVVN_ZALOOA_PLUGIN_DIR . '/includes/lib/wp-background-process.php';
}

class ZaloOA_Send_Process extends WP_Background_Process {

    protected $action = 'zalo_oa_send_tracking_id';

    protected function task( $tracking_id ) {
        //error_log($this->get_unique_campaign_key());
        //error_log('a');
        //sleep(20);
        //error_log('b');
        if(function_exists('devvn_zalo_main') && $tracking_id) {
            devvn_zalo_main()->send_by_tracking_id($tracking_id);
        }
        return false;
    }

    protected function complete() {
        $_zalooa_campaigns_db = new ZaloOA_Campaigns_DB();
        $_zalooa_mess_db = new ZaloOA_Mess_DB();
        $campaigns = $_zalooa_campaigns_db->get_campaign_by('status', 'sending');
        foreach ($campaigns as $item){
            $pending = $_zalooa_mess_db->get_count_mess($item->ID, 'pending');
            if(!$pending){
                $data_sql = array(
                    'status' => 'complete',
                );
                $_zalooa_campaigns_db->add_data($item->ID, $data_sql);
            }
        }
        parent::complete();
    }

}