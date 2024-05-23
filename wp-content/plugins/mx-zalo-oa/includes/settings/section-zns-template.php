<?php


defined("ABSPATH") or exit("No script kiddies please!");

if (!$this->has_access_token()) {
	exit;
}
extract($this->get_option_default());
$template_all = $this->get_template_all(true);
$error = isset($template_all["error"]) && $template_all["error"] == 0 ? 0 : $template_all["error"];
$message = isset($template_all["message"]) ? $template_all["message"] : "";
$data = isset($template_all["data"]) ? $template_all["data"] : [];
if ($error) {
	echo "(" . $error . ") " . $message;
} else {
	if (!$data) {
		echo 'Chưa có template nào được duyệt. Hãy <a href="https://account.zalo.cloud/" target="_blank" rel="nofollow">vào đây để tạo template</a>';
	} else { ?>
		<div class="wrap">
			<table class="table_template_style">
				<thead>
					<tr>
						<th>Template ID</th>
						<th>Template Name</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data as $item) : ?>
						<?php
						$templateId = isset($item["templateId"]) ? intval($item["templateId"]) : "";
						$templateName = isset($item["templateName"]) ? sanitize_text_field($item["templateName"]) : "";
						?>
						<tr>
							<td><?php echo $templateId; ?></td>
							<td><?php echo $templateName; ?></td>
							<td><button type="button" class="button button-primary view_template" data-templateid="<?php echo esc_attr($templateId); ?>">Xem chi tiết</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

<?php }
}
