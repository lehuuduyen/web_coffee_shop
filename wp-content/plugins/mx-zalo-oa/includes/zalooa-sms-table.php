<?php
defined('ABSPATH') || exit;

class ZaloTable_Install {
	private static $db_version = '1.0';
	private static $current_db_version = '';
	private static $table_name = '';
	private static $phone_table_name = '';
	private static $db_version_name = 'zalo_db_version';

	public static function init() {

		global $wpdb;

		self::$table_name = $wpdb->prefix . 'zalooa_mess';
		self::$phone_table_name = $wpdb->prefix . 'zalooa_phone';
		self::$current_db_version = get_option(self::$db_version_name);
	}


	private static function del_tables() {
		global $wpdb;
		//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}vnaddress_cities" );
		//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}zalooa_mess" );
		//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}vnaddress_wards" );
	}

	public static function onactive_create_tables() {
		if (version_compare(self::$current_db_version, self::$db_version, '<')) {
			self::create_tables();
		}
	}
	public static function create_tables() {
		global $wpdb;

		$table_name = self::$table_name;
		$phone_table_name = self::$phone_table_name;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "";

		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql .= "CREATE TABLE {$table_name} (
            ID INT(11) NOT NULL AUTO_INCREMENT,
            phone VARCHAR(20) NOT NULL,
            mess_id VARCHAR(25) NOT NULL,
            tracking_id VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            template_id VARCHAR(30) NOT NULL,
            data_respon TEXT,
            data_send TEXT,
            time DATETIME NOT NULL,
            PRIMARY KEY (ID)
        ) {$charset_collate};";
		}
		if ($wpdb->get_var("SHOW TABLES LIKE '{$phone_table_name}'") != $phone_table_name) {
			$sql .= "CREATE TABLE {$phone_table_name} (
            ID INT(11) NOT NULL AUTO_INCREMENT,
            phone VARCHAR(20) NULL DEFAULT 0,
            user_id VARCHAR(30),
            zalo_active TINYINT(1) NOT NULL DEFAULT 0,
            name VARCHAR(100),
            data TEXT,
            time DATETIME NOT NULL,
            PRIMARY KEY (ID)
        ) {$charset_collate};";
		}
		if ($sql) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			update_option(self::$db_version_name,  self::$db_version, false);
		}

		return true;
	}
}
ZaloTable_Install::init();

register_activation_hook(MX_ZALOOA_BASENAME, 'ZaloTable_Install::onactive_create_tables');


if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('ZaloOA_Mess_DB')) {

	class ZaloOA_Mess_DB {
		private $table_name;

		public function __construct() {
			global $wpdb;
			$this->table_name = $wpdb->prefix . 'zalooa_mess';
		}

		public function insert_data($data) {
			global $wpdb;
			return $wpdb->insert($this->table_name, $data, '%s');
		}

		public function update_data($tracking_id, $data) {
			global $wpdb;
			return $wpdb->update($this->table_name, $data, array('tracking_id' => $tracking_id));
		}

		public function delete_data($tracking_id) {
			global $wpdb;
			return $wpdb->delete($this->table_name, array('tracking_id' => $tracking_id));
		}

		public function delete_like($like_pattern, $column_name = 'tracking_id') {
			global $wpdb;
			$query = $wpdb->prepare("DELETE FROM $this->table_name WHERE $column_name LIKE %s AND status = 'pending'", $like_pattern);
			return $wpdb->query($query);
		}

		public function check_data_exist($tracking_id) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT COUNT(*) FROM $this->table_name WHERE tracking_id = %s", $tracking_id);
			return $wpdb->get_var($query);
		}

		public function get_count_mess($campaign_id, $status = 'total') {
			global $wpdb;
			$sql = "SELECT COUNT(*) FROM $this->table_name WHERE tracking_id LIKE %s";
			if ($status != 'total') {
				$sql .= " AND status = '$status'";
			}
			$query = $wpdb->prepare($sql, 'campaign_' . $campaign_id . '_%');
			return $wpdb->get_var($query);
		}

		public function add_data($tracking_id, $data) {
			if ($this->check_data_exist($tracking_id)) {
				return $this->update_data($tracking_id, $data);
			} else {
				return $this->insert_data($data);
			}
		}

		public function get_data_by_tracking_id($tracking_id) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT * FROM $this->table_name WHERE tracking_id = %s", $tracking_id);
			return $wpdb->get_row($query);
		}

		public function get_data_by_campaign_id($campaign_id, $status = '') {
			global $wpdb;
			$tracking_id = 'campaign_' . $campaign_id . '_%';
			$sql = "SELECT * FROM $this->table_name WHERE tracking_id LIKE %s";
			if ($status) {
				$sql .= " AND status = '$status'";
			}
			$query = $wpdb->prepare($sql, $tracking_id);
			return $wpdb->get_results($query);
		}
	}
}

if (!class_exists('ZaloOA_Phone_DB')) {

	class ZaloOA_Phone_DB {
		private $table_name;

		public function __construct() {
			global $wpdb;
			$this->table_name = $wpdb->prefix . 'users';
		}

		public function insert_data($data) {
			global $wpdb;
			return $wpdb->insert($this->table_name, $data, '%s');
		}

		public function update_data($phone, $data) {
			global $wpdb;
			$wpdb->update($this->table_name, $data, array('phone' => $phone));
		}

		public function delete_data($phone) {
			global $wpdb;
			$wpdb->delete($this->table_name, array('phone' => $phone));
		}

		public function check_data_exist($phone) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT COUNT(*) FROM $this->table_name WHERE phone = %s", $phone);
			return $wpdb->get_var($query);
		}

		public function add_data($phone, $data) {
			if ($this->check_data_exist($phone)) {
				$this->update_data($phone, $data);
			} else {
				$data['phone'] = $phone;
				$this->insert_data($data);
			}
		}

		public function get_user_id($phone) {
			global $wpdb;
			$phone_number = $phone;
			$cleaned_phone_number = preg_replace('/[^0-9]/', '', $phone_number);
			$query = $wpdb->prepare("SELECT ID FROM {$this->table_name} WHERE user_login = %s", $cleaned_phone_number);
			$user_id = $wpdb->get_var($query);
			return $user_id;
		}

		public function get_all_user() {
			global $wpdb;
			$query = "SELECT * FROM $this->table_name WHERE zalo_active = 1";
			return $wpdb->get_results($query);
		}

		public function update_data_by_userid($user_id, $data) {
			global $wpdb;
			return $wpdb->update($this->table_name, $data, array('user_id' => $user_id));
		}

		public function check_data_exist_by_userid($user_id) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT COUNT(*) FROM $this->table_name WHERE user_id = %s", $user_id);
			return $wpdb->get_var($query);
		}

		public function add_data_by_userid($user_id, $data) {
			if ($this->check_data_exist_by_userid($user_id)) {
				return $this->update_data_by_userid($user_id, $data);
			} else {
				return $this->insert_data($data);
			}
		}
	}
}
if ( is_plugin_active(WOO_POINT_PATH) && !class_exists('MX_Woo_Point_DB')) {
	class MX_Woo_Point_DB {
		private $table_name;

		public function __construct() {
			global $wpdb;
			$this->table_name = $wpdb->prefix . 'woo_history_user_point';
		}

		public function get_total_points_by_user_id($user_id) {
			global $wpdb;
			$query = $wpdb->prepare("
                SELECT SUM(point) AS total_points
                FROM $this->table_name
                WHERE user_id = %d
            ", $user_id);
			return $wpdb->get_var($query);
		}

		public function get_latest_order_by_user_id($user_id, $order_id) {
			global $wpdb;
			$query = $wpdb->prepare("
                SELECT point
                FROM $this->table_name
                WHERE user_id = %d AND order_id = %d
                ORDER BY create_at DESC
                LIMIT 1
            ", $user_id, $order_id);
			return $wpdb->get_var($query);
		}
	}
}


if (!class_exists('ZaloOA_Phone_List_Table')) {
	class ZaloOA_Phone_List_Table extends WP_List_Table {
		function __construct() {
			parent::__construct(array(
				'singular' => 'user',
				'plural' => 'users',
				'ajax' => false
			));
		}

		function get_columns() {
			return array(
				'ID' => 'ID',
				'user_login' => 'Phone',
				'user_email' => 'Email',
			);
		}

		function prepare_items() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'users';
			$per_page = 20;
			$current_page = $this->get_pagenum();

			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
			$query = "SELECT * FROM {$table_name} WHERE user_login REGEXP '^84[0-9]{9}$'";
			$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'ID';
			$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'desc';

			$query .= " ORDER BY {$orderby} {$order}";

			$total_items = $wpdb->query($query);
			$total_pages = ceil($total_items / $per_page);

			$offset = ($current_page - 1) * $per_page;
			$query .= " LIMIT {$offset}, {$per_page}";

			$this->items = $wpdb->get_results($query);

			$this->set_pagination_args(array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page' => $per_page
			));
		}

		function column_default($item, $column_name) {
			return $item->$column_name;
		}
	}
}

if (!class_exists('Prefix_ZaloOA_Mess_List_Table')) {
	class Prefix_ZaloOA_Mess_List_Table extends WP_List_Table {
		function __construct() {
			parent::__construct(array(
				'singular' => 'mess',
				'plural' => 'messes',
				'ajax' => false
			));
		}

		function get_columns() {
			return array(
				'cb' => '<input type="checkbox" />',
				'phone' => 'Phone',
				'mess_id' => 'Mess ID',
				'tracking_id' => 'Tracking ID',
				'status' => 'Status',
				'template_id' => 'Template ID',
				'time' => 'Time',
				'action' => 'Action'
			);
		}

		function process_bulk_action() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'zalooa_mess';

			if ('delete' === $this->current_action()) {

				check_admin_referer('bulk-' . $this->_args['plural']);

				$ids = isset($_REQUEST['ID']) ? (array) $_REQUEST['ID'] : array();
				$ids = implode(',', $ids);

				if (!empty($ids)) {
					$wpdb->query("DELETE FROM $table_name WHERE ID IN($ids)");
				}
			}
		}

		function prepare_items() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'zalooa_mess';
			$per_page = 20;
			$current_page = $this->get_pagenum();

			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);

			$this->process_bulk_action();

			$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
			$templateid = isset($_GET['templateid']) ? sanitize_text_field($_GET['templateid']) : '';

			$query = "SELECT * FROM {$table_name} WHERE 1=1";

			if (!empty($status_filter)) {
				$query .= " AND status = '{$status_filter}'";
			}

			if (!empty($templateid)) {
				$query .= " AND template_id = '{$templateid}'";
			}

			$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'time';
			$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'desc';

			$query .= " ORDER BY {$orderby} {$order}";

			$total_items = $wpdb->query($query);
			$total_pages = ceil($total_items / $per_page);

			$offset = ($current_page - 1) * $per_page;
			$query .= " LIMIT {$offset}, {$per_page}";

			$this->items = $wpdb->get_results($query);

			$this->set_pagination_args(array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page' => $per_page
			));
		}

		function column_default($item, $column_name) {
			return $item->$column_name;
		}

		function column_cb($item) {
			return sprintf('<input type="checkbox" name="ID[]" value="%s" />', $item->ID);
		}

		function get_bulk_actions() {
			$actions = array(
				'delete' => __('Xóa', MX_ZALOOA_TEXTDOMAIN)
			);
			return $actions;
		}

		function get_status_complete() {
			return apply_filters('zalooa_get_status_complete', array('completed', 'rated'));
		}

		function column_status($item) {
			$out = '';
			if (!in_array($item->status, $this->get_status_complete())) {
				$data_repson = isset($item->data_respon) ? maybe_unserialize($item->data_respon) : array();
				$error = isset($data_repson['error']) && $data_repson['error'] ? $data_repson['error'] : 0;
				$message = isset($data_repson['message']) && $data_repson['message'] ? $data_repson['message'] : 0;
				if ($error && $message) {
					$out = sprintf(__('Lỗi (%1$s) %2$s', MX_ZALOOA_TEXTDOMAIN), $error, $message);
				} elseif ($message) {
					$out = $message;
				}
			}
			return '<span class="zalo_' . $item->status . '">' . ucfirst($item->status) . '</span><br>' . $out;
		}

		function column_action($item) {
			ob_start();
?>
<a class="button wc-action-button action_send_again" href="#" data-trackingid="<?php echo esc_attr($item->tracking_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('action_send_again')); ?>">Gửi lại</a>
<a class="button wc-action-button action_info" href="#info-<?php echo $item->ID; ?>">Xem chi tiết</a>
<div id="info-<?php echo $item->ID; ?>" class="mfp-hide zalo-zns-info">
	<strong>Data</strong>
	<?php
			$data_send = isset($item->data_send) ? maybe_unserialize($item->data_send) : array();
			echo '<pre>';
			print_r($data_send);
			echo '</pre>';
	?>
</div>
<?php
			return ob_get_clean();
		}
	}
}
