<?php


defined("ABSPATH") or exit("No script kiddies please!");

if (!$this->has_access_token()) {
	exit;
}
extract($this->get_option_default());
$template_all = $this->get_template_all();
if (!$template_all) {
	echo 'Chưa có template nào được duyệt. Hãy <a href="https://account.zalo.cloud/" target="_blank" rel="nofollow">vào đây để tạo template</a>';
} else {
	$zns_mess = $this->get_option("zns_mess");
	$test_mode = $this->get_option("test_mode");
	$new_order_active = isset($zns_mess["new_order"]["active"]) ? $zns_mess["new_order"]["active"] : 0;
	$new_order_template_id = isset($zns_mess["new_order"]["template_id"]) ? $zns_mess["new_order"]["template_id"] : "";
	$new_order_admin_active = isset($zns_mess["new_order_admin"]["active"]) ? $zns_mess["new_order_admin"]["active"] : 0;
	$new_order_admin_template_id = isset($zns_mess["new_order_admin"]["template_id"]) ? $zns_mess["new_order_admin"]["template_id"] : "";
	$new_order_admin_phone = isset($zns_mess["new_order_admin"]["phone"]) ? $zns_mess["new_order_admin"]["phone"] : "";
	$change_order_active = isset($zns_mess["change_order"]["active"]) ? $zns_mess["change_order"]["active"] : 0;
	$change_order_template_id = isset($zns_mess["change_order"]["template_id"]) ? $zns_mess["change_order"]["template_id"] : "";
	$completed_active = isset($zns_mess["completed"]["active"]) ? $zns_mess["completed"]["active"] : 0;
	$completed_template_id = isset($zns_mess["completed"]["template_id"]) ? $zns_mess["completed"]["template_id"] : "";
	$point_active = isset($zns_mess["point"]["active"]) ? $zns_mess["point"]["active"] : 0;
	$point_template_id = isset($zns_mess["point"]["template_id"]) ? $zns_mess["point"]["template_id"] : "";
	$reviews_active = isset($zns_mess["reviews"]["active"]) ? $zns_mess["reviews"]["active"] : 0;
	$reviews_days = isset($zns_mess["reviews"]["days"]) ? intval($zns_mess["reviews"]["days"]) : 10;
	$reviews_template_id = isset($zns_mess["reviews"]["template_id"]) ? $zns_mess["reviews"]["template_id"] : "";
	$follow_active = isset($zns_mess["follow"]["active"]) ? $zns_mess["follow"]["active"] : 0;
	$follow_template_id = isset($zns_mess["follow"]["template_id"]) ? $zns_mess["follow"]["template_id"] : "";
	$follow_key = !empty($zns_mess["follow"]["key"]) ? $zns_mess["follow"]["key"] : md5(uniqid('', true));
?>
	<div class="wrap">
		<form method="post" action="options.php" novalidate="novalidate">
			<?php settings_fields("section-zns-mess"); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?php echo $this->_optionNamePrefix . "test_mode"; ?>">Chế độ development </label></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "test_mode"); ?>" id="<?php echo esc_attr($this->_optionNamePrefix . "test_mode"); ?>" value="1" <?php checked(1, $test_mode); ?>> Kích hoạt </label><br>
							<small>Chế độ development chỉ hỗ trợ gửi thử mẫu ZNS đến quản trị viên của ứng dụng hoặc quản trị viên của OA.</small>
						</td>
					</tr>
				</tbody>
			</table>
			<h2>Hành động gửi tin ZNS</h2>
			<table class="table_template_style table_template_zns_style">
				<thead>
					<tr>
						<th>Kích hoạt</th>
						<th>Hành động</th>
						<th>Chọn template</th>
						<th>Tham số</th>
					</tr>
				</thead>
				<tbody>

					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[new_order_admin][active]"); ?>" value="1" <?php checked(1, $new_order_admin_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td>
							<?php _e("Gửi tin cho sđt admin OA khi có đơn hàng mới", MX_ZALOOA_TEXTDOMAIN); ?><br>
							<?php _e("Tin này gửi ở chế độ development nên sẽ không mất tiền zns", MX_ZALOOA_TEXTDOMAIN); ?><br>
							<?php _e("Số điện thoại Admin OA. Mỗi số cách nhau dấu phẩy (,)", MX_ZALOOA_TEXTDOMAIN); ?><br>
							<input style="width: 100%; margin-top: 10px" type="text" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[new_order_admin][phone]"); ?>" value="<?php echo esc_attr($new_order_admin_phone); ?>">
						</td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[new_order_admin][template_id]"); ?>" data-woo_action="new_order_admin" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $new_order_admin_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($new_order_admin_template_id) : ?>
									<?php echo $this->get_html_parameter_template($new_order_admin_template_id, $this->_optionNamePrefix . "zns_mess[new_order_admin][parameter]", "new_order_admin"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[new_order][active]"); ?>" value="1" <?php checked(1, $new_order_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td><?php _e("Khi có đơn hàng mới", MX_ZALOOA_TEXTDOMAIN); ?></td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[new_order][template_id]"); ?>" data-woo_action="new_order" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $new_order_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($new_order_template_id) : ?>
									<?php echo $this->get_html_parameter_template($new_order_template_id, $this->_optionNamePrefix . "zns_mess[new_order][parameter]", "new_order"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>


					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[change_order][active]"); ?>" value="1" <?php checked(1, $change_order_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td><?php _e("Khi thay đổi trạng thái đơn hàng", MX_ZALOOA_TEXTDOMAIN); ?></td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[change_order][template_id]"); ?>" data-woo_action="change_order" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $change_order_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($change_order_template_id) : ?>
									<?php echo $this->get_html_parameter_template($change_order_template_id, $this->_optionNamePrefix . "zns_mess[change_order][parameter]", "change_order"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>


					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[completed][active]"); ?>" value="1" <?php checked(1, $completed_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td><?php _e("Khi đơn hoàn thành", MX_ZALOOA_TEXTDOMAIN); ?></td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[completed][template_id]"); ?>" data-woo_action="completed" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $completed_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($completed_template_id) : ?>
									<?php echo $this->get_html_parameter_template($completed_template_id, $this->_optionNamePrefix . "zns_mess[completed][parameter]", "completed"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php if (is_plugin_active(WOO_POINT_PATH)) : ?>
    					<tr>
    						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[point][active]"); ?>" value="1" <?php checked(1, $point_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
    						<td><?php _e("Tích lũy điểm thưởng", MX_ZALOOA_TEXTDOMAIN); ?></td>
    						<td>
    							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[point][template_id]"); ?>" data-woo_action="point" class="get_template_parameter">
    								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
    								<?php foreach ($template_all as $item) : ?>
    									<?php
    									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
    									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
    									?>
    									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $point_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
    								<?php endforeach; ?>
    							</select>
    						</td>
    						<td>
    							<div class="load_parameter">
    								<?php if ($point_template_id) : ?>
    									<?php echo $this->get_html_parameter_template($point_template_id, $this->_optionNamePrefix . "zns_mess[point][parameter]", "point"); ?>
    								<?php endif; ?>
    							</div>
    						</td>
    					</tr>
					<?php endif?>
					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[reviews][active]"); ?>" value="1" <?php checked(1, $reviews_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td>
							Gửi tin nhắn đánh giá sản phẩm sau <input type="number" min="0" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[reviews][days]"); ?>" class="reviews_days" value="<?php echo esc_attr($reviews_days); ?>"> ngày từ khi đơn hoàn thành<br>
							<small>Đơn có bao nhiêu sản phẩm sẽ gửi bấy nhiêu tin đánh giá. Đánh giá sẽ được tự động thêm vào hệ thống reviews của woocommerce qua Webhook</small>
						</td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[reviews][template_id]"); ?>" data-woo_action="reviews" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $reviews_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($reviews_template_id) : ?>
									<?php echo $this->get_html_parameter_template($reviews_template_id, $this->_optionNamePrefix . "zns_mess[reviews][parameter]", "reviews"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<tr>
						<td><label><input type="checkbox" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[follow][active]"); ?>" value="1" <?php checked(1, $follow_active); ?>> <?php _e("Kích hoạt", MX_ZALOOA_TEXTDOMAIN); ?></label></td>
						<td><?php _e("Người quan tâm", MX_ZALOOA_TEXTDOMAIN); ?>
							<table class="table_template_style">
								<thead>
									<tr>
										<th style="width: 200px;">Tham số</th>
										<th>Giá trị</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="width: 200px;">Mã bảo mật</td>
										<td><input style="width: 100%; margin-top: 10px" type="text" name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[follow][key]"); ?>" value="<?php echo esc_attr($follow_key); ?>"></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td>
							<select name="<?php echo esc_attr($this->_optionNamePrefix . "zns_mess[follow][template_id]"); ?>" data-woo_action="follow" class="get_template_parameter">
								<option><?php _e("Chọn mẫu tin ZNS", MX_ZALOOA_TEXTDOMAIN); ?></option>
								<?php foreach ($template_all as $item) : ?>
									<?php
									$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
									$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
									?>
									<option value="<?php echo esc_attr($templateId); ?>" <?php selected($templateId, $follow_template_id); ?>>#<?php echo $templateId . " - " . $templateName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<div class="load_parameter">
								<?php if ($completed_template_id) : ?>
									<?php echo $this->get_html_parameter_template($follow_template_id, $this->_optionNamePrefix . "zns_mess[follow][parameter]", "follow", "bylink"); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php do_action("zalooa_setting_zns_mess", $this); ?>
				</tbody>
			</table>
			<?php do_settings_fields("section-zns-mess", "zns-mess"); ?>
			<?php do_settings_sections("section-zns-mess"); ?>
			<div class="text-center">
				<?php submit_button(); ?>
			</div>
		</form>
	</div>

<?php
}
