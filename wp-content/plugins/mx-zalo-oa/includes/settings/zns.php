<?php


defined("ABSPATH") or exit("No script kiddies please!");
$current_section = isset($_REQUEST["section"]) ? esc_html($_REQUEST["section"]) : "template";
echo wp_nonce_field('zns_setting', 'zns_setting_nonce');
echo '<div class="wrap">';

$quota = $this->zalo_request_get('quota');

echo '<div class="zns_info">
        <div class="zns_info_box">
            <div class="zns_info_item">
                <span>Quota (số lượng)</span>
                <strong>';

if ($quota) {
    $error = isset($quota['error']) && $quota['error'] ? esc_html($quota['error']) : '';
    $message = isset($quota['message']) && $quota['message'] ? esc_html($quota['message']) : '';
    $dailyQuota = isset($quota['data']['dailyQuota']) ? intval($quota['data']['dailyQuota']) : 0;
    $remainingQuota = isset($quota['data']['remainingQuota']) ? intval($quota['data']['remainingQuota']) : 0;
    if (!$error) {
        echo esc_html($remainingQuota) . '/' . esc_html($dailyQuota);
    } else {
        echo '(' . esc_html($error) . ') ' . esc_html($message);
    }
}

echo '</strong>
            </div>
        </div>
    </div>';

echo '<div class="updated below-h2" id="message"><p>Tin ZNS có thể gửi cho những SĐT đã quan tâm OA</p></div>';

echo '<ul class="subsubsub">
        <li>
            <a href="?page=' . esc_attr(MX_ZALOOA_TEXTDOMAIN) . '&tab=zns&section=template" class="' . ($current_section == 'template' ? 'current' : '') . '"> ' . __('Danh sách template', MX_ZALOOA_TEXTDOMAIN) . '</a>
        </li>
        <li>
            | <a href="?page=' . esc_attr(MX_ZALOOA_TEXTDOMAIN) . '&tab=zns&section=general" class="' . ($current_section == 'general' ? 'current' : '') . '"> ' . __('Cấu hình gửi tin', MX_ZALOOA_TEXTDOMAIN) . '</a>
        </li>
    </ul>';

echo '<br class="clear">';

$file = dirname(__FILE__) . '/section-zns-' . esc_attr($current_section) . '.php';
if (file_exists($file)) {
    include_once $file;
}

echo '</div>';
