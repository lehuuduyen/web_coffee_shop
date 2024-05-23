<?php


defined('ABSPATH') or exit('No script kiddies please!');

$table = new Prefix_ZaloOA_Mess_List_Table();
$table->prepare_items();
$templateid = isset($_REQUEST['templateid']) ? $_REQUEST['templateid'] : '';
$message = '';
if ('delete' === $table->current_action()) {
    $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Đã xóa: %d', 'mx-thecao'), count($_REQUEST['ID'])) . '</p></div>';
}
echo $message;
echo "<form id='zalooa-mess-table' method='GET'>";
echo "<input type='hidden' name='page' value='" . esc_attr($_REQUEST['page']) . "'>";
echo "<input type='hidden' name='tab' value='" . esc_attr($_REQUEST['tab']) . "'>";
echo "<input type='hidden' name='templateid' value='" . esc_attr($templateid) . "'>";
$table->display();
echo '</form>';
