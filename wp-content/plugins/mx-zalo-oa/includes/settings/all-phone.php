<?php


defined('ABSPATH') or exit('No script kiddies please!');

$table = new ZaloOA_Phone_List_Table();
$table->prepare_items();
$nonce = wp_create_nonce('zalo_setting');
echo '<form id="zalooa-phone-table" method="GET">' .
    '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '"/>' .
    '<input type="hidden" name="tab" value="' . esc_attr($_REQUEST['tab']) . '"/>';
$table->display();
echo '</form>';
