<?php
/*
	 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
	 * @ PHP 7.2 & 7.3
	 * @ Decoder version: 1.0.6
	 * @ Release: 10/08/2022
	 */

defined("ABSPATH") or exit("No script kiddies please!");

$appid = $this->get_option("appid");
$secret_key = $this->get_option("secret_key");
$redirect_uri_key = $this->get_option("redirect_uri_key");
if (!$redirect_uri_key) {
	$redirect_uri_key = wp_generate_password(12, false);
}
$webhook_url_key = $this->get_option("webhook_url_key");
if (!$webhook_url_key) {
	$webhook_url_key = wp_generate_password(12, false);
}
$zalooa_access_token_data = get_transient($this->get_name_transient_access_token());
$zalooa_access_token_error = get_option("zalooa_access_token_error");
$nonce = wp_create_nonce("zalo_setting");
?>
<div class="wrap tab_general">
	<span></span>
	<h2>Bước 1: Cấu hình thông số</h2>
	<p>Nhập đầy đủ thông tin bên dưới rồi ấn "Lưu thay đổi" sau đó làm tiếp bước 2 bên dưới</p>
	<form method="post" action="options.php" novalidate="novalidate">
		<?php settings_fields('zalo-general-group'); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr($this->_optionNamePrefix . 'appid'); ?>">App ID</label></th>
					<td>
						<input type="text" autocomplete="off" name="<?php echo esc_attr($this->_optionNamePrefix . 'appid'); ?>" id="<?php echo esc_attr($this->_optionNamePrefix . 'appid'); ?>" value="<?php echo esc_attr($appid); ?>"><br>
						<small>Tạo apps và lấy app ID <a href="https://developers.zalo.me/apps" target="_blank" rel="nofollow">tại đây</a></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr($this->_optionNamePrefix . 'secret_key'); ?>">Secret Key</label></th>
					<td>
						<input type="password" autocomplete="off" name="<?php echo esc_attr($this->_optionNamePrefix . 'secret_key'); ?>" id="<?php echo esc_attr($this->_optionNamePrefix . 'secret_key'); ?>" value="<?php echo esc_attr($secret_key); ?>"><br>
						<small>Vào app của bạn <a href="https://developers.zalo.me/apps" target="_blank" rel="nofollow">tại đây</a> > Tiếp theo vào tab cài đặt sẽ thấy "Khóa bí mật của ứng dụng"</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr($this->_optionNamePrefix . 'redirect_uri_key'); ?>"><?php _e("Redirect URI", "mx-zalo-oa"); ?></label></th>
					<td><?php echo site_url($this->get_redirect_uri_prefix()); ?><span id="redirect_uri_key_text"><?php echo esc_attr($redirect_uri_key); ?></span>
						<input type="hidden" name="<?php echo esc_attr($this->_optionNamePrefix . 'redirect_uri_key'); ?>" id="<?php echo esc_attr($this->_optionNamePrefix . 'redirect_uri_key'); ?>" value="<?php echo esc_attr($redirect_uri_key); ?>" />
						<a href="#" class="create_zalo_redirect_uri_key">Tạo link mới</a>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr($this->_optionNamePrefix . 'webhook_url_key'); ?>"><?php _e("Webhook URL", "mx-zalo-oa"); ?></label></th>
					<td><?php echo admin_url("admin-ajax.php?action=zalo_webhook&key="); ?><span id="webhook_url_key_text"><?php echo esc_attr($webhook_url_key); ?></span>
						<input type="hidden" name="<?php echo esc_attr($this->_optionNamePrefix . 'webhook_url_key'); ?>" id="<?php echo esc_attr($this->_optionNamePrefix . 'webhook_url_key'); ?>" value="<?php echo esc_attr($webhook_url_key); ?>" />
						<a href="#" class="create_zalo_webhook_url_key">Tạo link mới</a>
					</td>
				</tr>
				<?php do_settings_fields('zalo-general-group', 'zalooa'); ?>
			</tbody>
		</table>
		<?php do_settings_sections('zalo-general-group'); ?>
		<?php echo submit_button(); ?>
	</form>
	<?php if ($appid) { ?>
		<h2>Bước 2: Xác thực domain</h2>
		<p>Hãy nhập domain của bạn vào cài đặt <a href="https://developers.zalo.me/app/<?php echo esc_attr($appid); ?>/verify-domain" target="_blank" rel="nofollow">tại đây</a></p>

		<h2>Bước 3: Thêm Redirect URI</h2>
		<p>Vào App của bạn > Official Account > Thiết lập chung để thêm "Redirect URI" ở bước 1 vào ô "Official Account Callback Url" <a href="https://developers.zalo.me/app/<?php echo esc_attr($appid); ?>/oa/settings" target="_blank" rel="nofollow">tại đây</a></p>
		<p>Bỏ qua 2 ô "Code Challenge" và "State"</p>

		<h2>Bước 4: Thêm Webhook URL</h2>
		<p>Hiện tại nếu dùng template đánh giá thì mới cần tới webhook. Không dùng có thể bỏ qua bước này</p>
		<p>Vào App của bạn > Webhook > Sau đó nhập link "Webhook URL" ở bước 1 <a href="https://developers.zalo.me/app/<?php echo esc_attr($appid); ?>/webhook" target="_blank" rel="nofollow">vào đây</a></p>

		<h2>Bước 5: Cấp quyền truy cập ZaloOA cho ứng dụng </h2>
		<button style="margin-bottom: 30px;vertical-align: baseline;" type="button" class="button zalooa_access_token" data-nonce="<?php echo esc_attr($nonce); ?>">Get access token</button>

		<div class="mx-notice-<?php echo $zalooa_access_token_error ? 'error' : 'success'; ?>">
			<p><?php echo $zalooa_access_token_error ? 'Có lỗi khi cấp lại quyền với Zalo OA' : 'Kết nối Zalo OA thành công!'; ?></p>
		</div>

</div>
<?php }
