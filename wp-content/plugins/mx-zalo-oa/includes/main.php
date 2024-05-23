<?php

defined("ABSPATH") or exit("No script kiddies please!");
if (!class_exists("MX_ZaloOA_Dacbiet_Class")) {
    class MX_ZaloOA_Dacbiet_Class {
        public $_optionName = "";
        public $_optionNamePrefix = "zalooa_";
        public $_template_all_name = "";
        private $_zalooa_phone_db = "";
        private $_zalooa_mess_db = "";
        protected $send_tracking_id = NULL;
        protected $get_follows = NULL;
        protected static $instance = NULL;
        public static function init() {
            is_null(self::$instance) && (self::$instance = new self());
            return self::$instance;
        }
        public function __construct() {
            $this->set_optionName();
            $this->_template_all_name = "template_all_" . $this->get_option("appid");
            $this->_zalooa_phone_db = new ZaloOA_Phone_DB();
            $this->_zalooa_mess_db = new ZaloOA_Mess_DB();
            add_filter("plugin_action_links_" . MX_ZALOOA_BASENAME, [$this, "add_action_links"], 10, 2);
            add_action("admin_menu", [$this, "admin_menu"]);
            add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);
            add_action("admin_init", [$this, "register_mysettings"]);
            add_action("wp_ajax_zalo_get_authorization", [$this, "zalo_get_authorization_func"]);
            add_action("init", [$this, "custom_zalo_verify"]);
            add_action("login_with_refresh_token_event", [$this, "login_with_refresh_token_callback"], 10, 1);
            add_action("send_mail_refresh_token_expire_event", [$this, "send_mail_refresh_token_expire_callback"], 10, 1);
            add_action("wp_ajax_load_template_parameter", [$this, "load_template_parameter_func"]);
            add_action("wp_ajax_view_template_zns", [$this, "view_template_zns_func"]);
            add_action("woocommerce_new_order", [$this, "send_zalo_on_new_order"], 10, 2);
            add_action("woocommerce_order_status_changed", [$this, "send_zalo_on_change_status"], 10, 3);
            add_action("woocommerce_order_status_completed", [$this, "send_zalo_on_completed_status"], 20);
            add_action("send_zns_again_event", [$this, "send_zns_again_event_callback"], 10);
            add_action("wp_ajax_nopriv_zalo_webhook", [$this, "zalo_webhook_func"]);
            $this->send_tracking_id = new ZaloOA_Send_Process();
            add_action("wp_ajax_send_again_mess", [$this, "send_again_mess_func"]);
            add_action("before_woocommerce_init", function () {
                if (class_exists("Automattic\\WooCommerce\\Utilities\\FeaturesUtil")) {
                    Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility("custom_order_tables", MX_ZALOOA_BASENAME, true);
                }
            });
            add_filter("woocommerce_thankyou_order_key", [$this, "woocommerce_thankyou_order_key"]);
            add_filter("wp_mail_to", [$this, "filter_email_recipients"]);
            add_action('rest_api_init',  [$this, "register_user_notification_route"]);
        }
        public function woocommerce_thankyou_order_key($key) {
            if (!$key) {
                global $wp;
                $current_url = urldecode(home_url(add_query_arg([], $wp->request)));
                $query_string = wp_parse_url($current_url, PHP_URL_QUERY);
                parse_str($query_string, $query_params);
                $key = isset($query_params["key"]) ? $query_params["key"] : "";
            }
            return $key;
        }
        public function load_plugins_scripts() {
            wp_enqueue_style("mx-zalooa-style", plugins_url("assets/css/mx-style.css", dirname(__FILE__)), [], MX_ZALOOA_VERSION_NUM, "all");
            wp_enqueue_script("mx-zalooa-script", plugins_url("assets/js/mx-script.js", dirname(__FILE__)), ["jquery"], MX_ZALOOA_VERSION_NUM, true);
            $array = ["ajaxurl" => admin_url("admin-ajax.php"), "siteurl" => home_url(), "code" => defined("ZALOOA_LICENSE") ? ZALOOA_LICENSE : ""];
            wp_localize_script("mx-zalooa-script", "mx_zalooa_array", $array);
        }

        public function admin_enqueue_scripts() {
            $current_screen = get_current_screen();
            if (isset($current_screen->base) && in_array($current_screen->base, ["toplevel_page_" . MX_ZALOOA_TEXTDOMAIN])) {
                wp_enqueue_media();
                wp_enqueue_style("magnific-popup", plugins_url("assets/lib/magnific-popup/magnific-popup.css", dirname(__FILE__)), [], MX_ZALOOA_VERSION_NUM, "all");
                wp_enqueue_style("flatpickr", plugins_url("assets/lib/flatpickr/flatpickr.min.css", dirname(__FILE__)), [], MX_ZALOOA_VERSION_NUM, "all");
                wp_enqueue_style("zalooa-admin-styles", plugins_url("assets/css/admin-style.css", dirname(__FILE__)), [], MX_ZALOOA_VERSION_NUM, "all");
                wp_enqueue_script("magnific-popup", plugins_url("assets/lib/magnific-popup/magnific-popup.js", dirname(__FILE__)), ["jquery"], MX_ZALOOA_VERSION_NUM, true);
                wp_enqueue_script("flatpickr", plugins_url("assets/lib/flatpickr/flatpickr.min.js", dirname(__FILE__)), ["jquery"], MX_ZALOOA_VERSION_NUM, true);
                wp_enqueue_script("zalooa-admin-script", plugins_url("assets/js/admin-script.js", dirname(__FILE__)), ["jquery"], MX_ZALOOA_VERSION_NUM, true);
                $array = ["ajax_url" => admin_url("admin-ajax.php"), "code" => defined("ZALOOA_LICENSE") ? ZALOOA_LICENSE : ""];
                wp_localize_script("zalooa-admin-script", "mx_zalooa_admin", $array);
            }
        }
        public function add_action_links($links, $file) {
            if (strpos($file, MX_ZALOOA_TEXTDOMAIN . ".php") !== false) {
                $settings_link = "<a href=\"" . admin_url("admin.php?page=" . MX_ZALOOA_TEXTDOMAIN) . "\" title=\"" . __("Settings") . "\">" . __("Settings") . "</a>";
                array_unshift($links, $settings_link);
            }
            return $links;
        }
        public function admin_menu() {
            add_menu_page(__("Cài đặt Zalo OA", MX_ZALOOA_TEXTDOMAIN), __("MX - Zalo OA", MX_ZALOOA_TEXTDOMAIN), "manage_options", MX_ZALOOA_TEXTDOMAIN, [$this, "settings_page"]);
        }
        public function settings_page() {
            include MX_ZALOOA_PLUGIN_DIR . "includes/settings/main.php";
        }
        public function set_optionName() {
            $this->_optionName = apply_filters("zalooa_option", ["appid" => ["args" => "", "group" => "zalo-general-group", "default" => ""], "secret_key" => ["args" => "", "group" => "zalo-general-group", "default" => ""], "redirect_uri_key" => ["args" => "", "group" => "zalo-general-group", "default" => ""], "webhook_url_key" => ["args" => "", "group" => "zalo-general-group", "default" => ""], "mess_new_order" => ["args" => "", "group" => "section-new-order", "default" => ""], "mess_change_order" => ["args" => "", "group" => "section-change-order", "default" => ""], "mess_to_completed" => ["args" => "", "group" => "section-to-completed", "default" => ""], "zns_mess" => ["args" => "", "group" => "section-zns-mess", "default" => ""], "test_mode" => ["args" => "", "group" => "section-zns-mess", "default" => ""], "login_active" => ["args" => "", "group" => "section-login", "default" => ""]]);
        }
        public function get_option($name) {
            $default = isset($this->_optionName[$name]["default"]) && $this->_optionName[$name]["default"] ? $this->_optionName[$name]["default"] : "";
            return get_option($this->_optionNamePrefix . $name, $default);
        }
        public function register_mysettings() {
            foreach ($this->_optionName as $name => $options) {
                $group = isset($options["group"]) ? $options["group"] : "";
                $args = isset($options["args"]) && is_array($options["args"]) ? $options["args"] : [];
                if ($group) {
                    register_setting($group, $this->_optionNamePrefix . $name, $args);
                }
            }
        }
        public function generateCodeChallenge($code_verifier) {
            $ascii_code_verifier = mb_convert_encoding($code_verifier, "ASCII");
            $sha256_hash = hash("sha256", $ascii_code_verifier, true);
            $base64_code_challenge = rtrim(strtr(base64_encode($sha256_hash), "+/", "-_"), "=");
            return $base64_code_challenge;
        }
        public function get_redirect_uri_prefix() {
            return apply_filters("zalooa_redirect_uri_prefix", "zalo-verify-");
        }
        public function get_redirect_uri($key = false) {
            $redirect_uri_key = $this->get_option("redirect_uri_key");
            if ($redirect_uri_key) {
                $redirect_uri = $this->get_redirect_uri_prefix() . $redirect_uri_key;
                if ($key) {
                    return $redirect_uri;
                }
                return site_url($redirect_uri);
            }
            return false;
        }
        public function zalo_get_authorization_func() {
            if (!wp_verify_nonce($_REQUEST["nonce"], "zalo_setting")) {
                exit("No naughty business please");
            }
            $app_id = $this->get_option("appid");
            $code_verifier = wp_generate_password("43", false);
            $code_challenge = $this->generateCodeChallenge($code_verifier);
            $redirect_uri = $this->get_redirect_uri();
            $url = "https://oauth.zaloapp.com/v4/oa/permission";
            if (!$app_id || !$code_challenge) {
                wp_send_json_error("Thiếu thông tin!");
            }
            update_option("zalooa_code_verifier", $code_verifier, "no");
            $url = add_query_arg(["app_id" => $app_id, "redirect_uri" => urlencode($redirect_uri), "code_challenge" => $code_challenge, "state" => "mx"], $url);
            wp_send_json_success($url);
            exit;
        }
        public function custom_zalo_verify() {
            $redirect_uri = $this->get_redirect_uri(true);
            if ($redirect_uri && strpos($_SERVER["REQUEST_URI"], "/" . $redirect_uri) !== false) {
                $oa_id = isset($_GET["oa_id"]) && $_GET["oa_id"] ? wp_unslash($_GET["oa_id"]) : "";
                $code = isset($_GET["code"]) && $_GET["code"] ? wp_unslash($_GET["code"]) : "";
                if (!$oa_id || !$code) {
                    wp_safe_redirect(home_url(), 301);
                    exit;
                }
                zalooa_get_template("zalo-verify.php");
                $response = $this->login_with_authorization_code($code);
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                if (apply_filters("zalo_debug", false)) {
                    ob_start();
                    print_r($response_body);
                    $logbody = ob_get_clean();
                    $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "custom_zalo_verify: " . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                    file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
                }
                $setting_url = admin_url("admin.php?page=mx-zalo-oa&tab=general");
                $error_message = "";
                $this->clear_scheduled();
                $this->clear_scheduled("send_mail_refresh_token_expire_event");
                if (!is_wp_error($response) && $response_code == 200) {
                    $response_body = json_decode($response_body, true);
                    $access_token = isset($response_body["access_token"]) && $response_body["access_token"] ? $response_body["access_token"] : "";
                    $refresh_token = isset($response_body["refresh_token"]) && $response_body["refresh_token"] ? $response_body["refresh_token"] : "";
                    $expires_in = isset($response_body["expires_in"]) && $response_body["expires_in"] ? $response_body["expires_in"] : "";
                    if ($access_token && $refresh_token) {
                        set_transient($this->get_name_transient_access_token(), $response_body, time() + $expires_in);
                        delete_option("zalooa_access_token_error");
                        $scheduled_time = time() + 86400;
                        wp_schedule_single_event($scheduled_time, "login_with_refresh_token_event", [$refresh_token]);
                        $url_redirect = add_query_arg(["zaloerror" => 0, "mess" => __("Kết nối thành công")], $setting_url);
                        echo "                            <script>\r\n                                window.location.href = \"";
                        echo $url_redirect;
                        echo "\";\r\n                            </script>\r\n                            ";
                        exit;
                    }
                    $error_name = isset($response_body["error_name"]) && $response_body["error_name"] ? $response_body["error_name"] : "";
                    $error_description = isset($response_body["error_description"]) && $response_body["error_description"] ? $response_body["error_description"] : "";
                    $error_message = $error_name ? $error_name : "Có lỗi không xác định trong quá trình cấp quyền";
                } else {
                    $error_message = $response->get_error_message();
                }
                if ($error_message) {
                    delete_transient($this->get_name_transient_access_token());
                    $url_redirect = add_query_arg(["zaloerror" => 1, "mess" => $error_message], $setting_url);
                    echo "                        <script>\r\n                            window.location.href = \"";
                    echo $url_redirect;
                    echo "\";\r\n                        </script>\r\n                        ";
                }
                exit;
            }
        }
        public function get_name_transient_access_token() {
            return "zalooa_access_token_data_" . $this->get_option("appid");
        }
        public function clear_scheduled($name = "login_with_refresh_token_event") {
            wp_unschedule_hook($name);
            $timestamp = wp_next_scheduled($name);
            while ($timestamp) {
                wp_unschedule_event($timestamp, $name);
                $timestamp = wp_next_scheduled($name);
            }
        }
        public function get_access_token($key = "access_token") {
            $zalooa_access_token_data = get_transient($this->get_name_transient_access_token());
            if ($zalooa_access_token_data) {
                return isset($zalooa_access_token_data[$key]) && $zalooa_access_token_data[$key] ? $zalooa_access_token_data[$key] : false;
            }
            return false;
        }
        public function login_with_authorization_code($code) {
            $app_id = $this->get_option("appid");
            $secret_key = $this->get_option("secret_key");
            $code_verifier = get_option("zalooa_code_verifier");
            $url = "https://oauth.zaloapp.com/v4/oa/access_token";
            $headers = ["Content-Type" => "application/x-www-form-urlencoded", "secret_key" => $secret_key];
            $body = ["code" => $code, "app_id" => $app_id, "grant_type" => "authorization_code", "code_verifier" => $code_verifier];
            $response = wp_remote_post($url, ["headers" => $headers, "body" => $body, "timeout" => 50]);
            return $response;
        }
        public function get_access_token_by_authorization_code($code, $state) {
            $app_id = $this->get_option("appid");
            $secret_key = $this->get_option("secret_key");
            $code_verifier = get_transient($state);
            $url = "https://oauth.zaloapp.com/v4/access_token";
            $headers = ["Content-Type" => "application/x-www-form-urlencoded", "secret_key" => $secret_key];
            $body = ["code" => $code, "app_id" => $app_id, "grant_type" => "authorization_code", "code_verifier" => $code_verifier];
            delete_transient($state);
            $response = wp_remote_post($url, ["headers" => $headers, "body" => $body, "timeout" => 50]);
            return $response;
        }
        public function get_user_info($access_token) {
            $fields = apply_filters("get_user_info_fields", ["id", "name", "picture"]);
            $url = "https://graph.zalo.me/v2.0/me?fields=" . implode(",", $fields);
            $headers = ["Content-Type" => "application/json", "access_token" => $access_token];
            $response = wp_remote_get($url, ["headers" => $headers, "timeout" => 50]);
            return $response;
        }
        public function login_with_refresh_token($refresh_token) {
            $app_id = $this->get_option("appid");
            $secret_key = $this->get_option("secret_key");
            $url = "https://oauth.zaloapp.com/v4/oa/access_token";
            $headers = ["Content-Type" => "application/x-www-form-urlencoded", "secret_key" => $secret_key];
            $body = ["refresh_token" => $refresh_token, "app_id" => $app_id, "grant_type" => "refresh_token"];
            $response = wp_remote_post($url, ["headers" => $headers, "body" => $body, "timeout" => 50]);
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $error_message = "";
            if (!is_wp_error($response) && $response_code == 200) {
                $this->clear_scheduled();
                $response_body = json_decode($response_body, true);
                $access_token = isset($response_body["access_token"]) && $response_body["access_token"] ? $response_body["access_token"] : "";
                $refresh_token = isset($response_body["refresh_token"]) && $response_body["refresh_token"] ? $response_body["refresh_token"] : "";
                $expires_in = isset($response_body["expires_in"]) && $response_body["expires_in"] ? $response_body["expires_in"] : 90000;
                $error_description = isset($response_body["error_description"]) && $response_body["error_description"] ? $response_body["error_description"] : "";
                if ($access_token && $refresh_token) {
                    delete_transient($this->get_name_transient_access_token());
                    set_transient($this->get_name_transient_access_token(), $response_body, DAY_IN_SECONDS);
                    delete_option("zalooa_access_token_error");
                    $scheduled_time = time() + 86400;
                    wp_schedule_single_event($scheduled_time, "login_with_refresh_token_event", [$refresh_token]);
                    if (apply_filters("zalo_debug", false)) {
                        ob_start();
                        print_r($response_body);
                        print_r($refresh_token);
                        print_r($refresh_token);
                        $logbody = ob_get_clean();
                        $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "Đã vào tới hoàn thành khi login_with_refresh_token: " . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                        file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
                    }
                } else {
                    $error_message = $error_description ? $error_description : "Có lỗi không xác định trong quá trình cấp quyền.";
                }
            } else {
                $error_message = $response->get_error_message();
            }
            if ($error_message) {
                if (apply_filters("zalo_debug", false)) {
                    ob_start();
                    print_r(wp_remote_retrieve_body($response));
                    $logbody = ob_get_clean();
                    $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "login_with_refresh_token: " . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                    file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
                }
                update_option("zalooa_access_token_error", true, "no");
                $this->send_mail_log($error_message);
            }
        }
        public function send_mail_log($mess) {
            $to = get_option("admin_email");
            $subject = "Có lỗi khi lấy token Zalo OA - " . get_bloginfo("name");
            $body = "Bạn hãy vào kiểm tra lại kết nối với Zalo OA ngay nhé <br><a href=\"" . admin_url("admin.php?page=mx-zalo-oa&tab=general") . "\" target=\"_blank\" rel=\"nofollow\">Kiểm tra tại đây</a><br>";
            $body .= "Thông báo lỗi: " . $mess;
            $headers = ["Content-Type: text/html; charset=UTF-8"];
            $sendmail = wp_mail($to, $subject, $body, $headers);
        }
        public function login_with_refresh_token_callback($refresh_token) {
            $this->login_with_refresh_token($refresh_token);
        }
        public function send_mail_refresh_token_expire_callback($item) {
        }
        public function get_buttons_type() {
            return apply_filters("zalo_buttons_type", ["oa.open.url" => "Đường dẫn", "oa.open.phone" => "Gọi điện", "oa.query.show" => "Gửi text định trước trong value (Hiện)", "oa.query.hide" => "Gửi text định trước trong value (Ẩn)", "oa.open.sms" => "Nhắn tin SMS"]);
        }
        public function get_filter_view() {
            $filter_content_args = $this->get_filter_content_args();
            ob_start();
?>
            <div class="content_filter_view">
                <h2>Các biến có thể dùng:</h2>
                <ul>
                    <?php foreach ($filter_content_args as $key => $item) : ?>
                        <li><?= $key ?>: <?= $item ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Ngoài ra có thể dùng các field mặc định của woo như: {billing_email} {billing_first_name} {shipping_first_name} và còn nhiều field khác. Với định dạng {billing_[tên field]} hoặc {shipping_[tên field]}</p>
                <p>Với các meta field thì dùng biến có định dạng như sau: {meta_[tên meta]}. Ví dụ {meta_your_meta_field}</p>
            </div>
        <?php
            return ob_get_clean();
        }
        public function get_filter_content_args() {
            return apply_filters(
                "zalooa_filter_content_args",
                [
                    "{order_id}" => __("Mã đơn hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_key}" => __("Order Key. Dùng cho giá trị link xem chi tiết đơn hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_total}" => __("Tổng tiền đơn hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_total_view}" => __("Tổng tiền đơn hàng có định dạng. Không dùng cho template ZNS", MX_ZALOOA_TEXTDOMAIN),
                    "{order_view_detail}" => __("Link xem chi tiết đơn hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_view_detail_urlencode}" => __("Link xem chi tiết đơn hàng. Đã encode url. Không có tên domain trong link", MX_ZALOOA_TEXTDOMAIN),
                    "{order_status}" => __("Trạng thái đơn hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_full_name}" => __("Tên khách hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{order_shipping_total}" => __("Tổng tiền ship", MX_ZALOOA_TEXTDOMAIN),
                    "{order_create_date}" => __("Ngày tạo đơn", MX_ZALOOA_TEXTDOMAIN),
                    "{product_name_zns}" => __("Tên sản phẩm. Nếu template đánh giá thì không chọn cái này", MX_ZALOOA_TEXTDOMAIN),
                    "{note}" => __("Ghi chú của khách hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{phone_number}" => __("Số điện thoại khách hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{address}" => __("Địa chỉ của khách hàng", MX_ZALOOA_TEXTDOMAIN),
                    "{point}" => __("Điểm thưởng gia tăng", MX_ZALOOA_TEXTDOMAIN),
                    "{total_point}" => __("Tổng điểm hiện tại", MX_ZALOOA_TEXTDOMAIN),
                ]
            );
        }
        public function sendTelegram($text = 'GGWP') {
            $chatID = '2017431548';
            $token = 'bot5534522502:AAETkunIvpVGC93HFl-JfTtElJfo0q3OoUY';
            $url = "https://api.telegram.org/" . $token . "/sendMessage?parse_mode=html&chat_id=" . $chatID;
            $url = $url . "&text=" . $text;
            file_get_contents($url);
        }
        public function mess_filter_content($content, $orderThis, $args = []) {
            if (!$orderThis) {
                return $content;
            }
            preg_match_all("/{(\\S*)}/", $content, $matches);
            $old_content = $content;
            $filter_content_args = $this->get_filter_content_args();
            foreach ($matches[1] as $m) {
                $pattern = "/{" . $m . "}/";
                $this_value = "";
                if (has_filter("mess_filter_content_" . $m)) {
                    $this_value = apply_filters("mess_filter_content_" . $m, "", $content, $orderThis);
                } else {
                    if (isset($filter_content_args["{" . $m . "}"])) {
                        if ($m == "point" && isset($args["point"])) {
                            $this_value = $args["point"];
                        }
                        if ($m == "total_point" && isset($args["total_point"])) {
                            $this_value = $args["total_point"];
                        }
                        switch ($m) {
                            case "order_id":
                                $this_value = $orderThis->get_id();
                                break;
                            case "note":
                                $customer_note = $orderThis->get_customer_note();
                                $this_value = $customer_note ? mb_substr($orderThis->get_customer_note(), 0, 100) : apply_filters("note_empty_text", "(Không có)", MX_ZALOOA_TEXTDOMAIN);
                                break;
                            case "phone_number":
                                $this_value = $orderThis->get_billing_phone();
                                break;
                            case "address":
                                $this_value = mb_substr($orderThis->get_billing_address_1(), 0, 80);
                                break;
                            case "order_total":
                                $this_value = $orderThis->get_total();
                                break;
                            case "order_total_view":
                                $this_value = wp_strip_all_tags(wc_price($orderThis->get_total()));
                                break;
                            case "order_shipping_total":
                                $this_value = $orderThis->get_shipping_total();
                                break;
                            case "order_view_detail":
                                $this_value = $orderThis->get_checkout_order_received_url();
                                break;
                            case "order_key":
                                $this_value = mb_substr($orderThis->get_order_key(), 0, 30);
                                break;
                            case "order_view_detail_urlencode":
                                $this_value = urlencode(str_replace(home_url(), "", $orderThis->get_checkout_order_received_url()));
                                $this_value = mb_substr($this_value, 0, 200);
                                break;
                            case "order_status":
                                $this_value = wc_get_order_status_name($orderThis->get_status());
                                break;
                            case "order_create_date":
                                $this_value = $orderThis->get_date_created() ? gmdate("H:i:s d/m/Y", $orderThis->get_date_created()->getOffsetTimestamp()) : "";
                                break;
                            case "order_full_name":
                                $this_value = mb_substr($orderThis->get_formatted_billing_full_name(), 0, 30);
                                break;
                            case "product_name_zns":
                                $items = $orderThis->get_items();
                                $limit = 100;
                                $product_name = [];
                                foreach ($items as $item) {
                                    $quantity = (float) $item->get_quantity();
                                    $product_name[] = $quantity . " x " . $item->get_name();
                                }
                                $this_value = implode("|", $product_name);
                                if ($limit < mb_strlen($this_value)) {
                                    $this_value = mb_substr($this_value, 0, $limit - 3) . "...";
                                }
                                break;
                        }
                    } else {
                        if (strpos($m, "billing_") === 0) {
                            $billing_field = str_replace("billing_", "", $m);
                            $method = "get_billing_" . $billing_field;
                            if (method_exists($orderThis, $method)) {
                                $this_value = $orderThis->{$method}();
                            }
                        } else {
                            if (strpos($m, "shipping_") === 0) {
                                $shipping_field = str_replace("shipping_", "", $m);
                                $method = "get_shipping_" . $shipping_field;
                                if (method_exists($orderThis, $method)) {
                                    $this_value = $orderThis->{$method}();
                                }
                            } else {
                                if (strpos($m, "meta_") === 0) {
                                    $meta_field = str_replace("meta_", "", $m);
                                    $this_value = $orderThis->get_meta($meta_field);
                                } else {
                                    if ($m == "product_name" && isset($args["product_name"])) {
                                        $this_value = $args["product_name"];
                                    }
                                }
                            }
                        }
                    }
                }
                $content = preg_replace($pattern, $this_value, $content);
            }
            return apply_filters("mess_filter_content", $content, $old_content, $orderThis);
        }
        public function get_default($name = "", $type = "") {
            $out = "";
            switch ($name) {
                case "banner_url":
                    $out = MX_ZALOOA_URL . "assets/images/banner.png";
                    break;
                case "header":
                    $out = __("Bạn có đơn hàng mới", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "text1":
                    $out = __("• Cảm ơn bạn đã mua hàng.\r\n• Thông tin đơn hàng của bạn như sau:", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "table_code":
                    $out = __("Mã đơn hàng", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "table_code_value":
                    $out = __("{order_id}", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "table_custom1":
                    $out = __("Tổng đơn", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "table_custom1_value":
                    $out = __("{order_total_view}", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "text2":
                    $out = __("📱 Quý khách lưu ý điện thoại. Xin cảm ơn!", MX_ZALOOA_TEXTDOMAIN);
                    break;
                case "buttons":
                    $out = [["title" => __("Xem chi tiết đơn hàng!", MX_ZALOOA_TEXTDOMAIN), "image_icon" => MX_ZALOOA_URL . "assets/images/basket.png", "type" => "oa.open.url", "value" => "{order_view_detail}"], ["title" => __("Gọi Hotline", MX_ZALOOA_TEXTDOMAIN), "image_icon" => MX_ZALOOA_URL . "assets/images/hotline.png", "type" => "oa.open.phone", "value" => ""], ["title" => "", "image_icon" => MX_ZALOOA_URL . "assets/images/sms-1.png", "type" => "oa.query.show", "value" => ""], ["title" => "", "image_icon" => "", "type" => "oa.query.show", "value" => ""]];
                    break;
                default:
                    if ($type == "promotion") {
                        switch ($name) {
                            case "banner_url":
                                $out = MX_ZALOOA_URL . "assets/images/giamgia.jpg";
                                break;
                            case "header":
                                $out = __("Khuyến mãi khủng trong tháng", MX_ZALOOA_TEXTDOMAIN);
                                break;
                            case "text1":
                                $out = __("Khuyến mãi khủng trong tháng dành riêng cho bạn. Thông tin chi tiết như sau:", MX_ZALOOA_TEXTDOMAIN);
                                break;
                            case "table_code_value":
                            case "table_custom1":
                            case "table_custom1_value":
                                $out = "";
                                break;
                            case "text2":
                                $out = __("Áp dụng tất cả cửa hàng trên toàn quốc", MX_ZALOOA_TEXTDOMAIN);
                                break;
                            case "buttons":
                                $out = [["title" => __("Tham khảo chương trình!", MX_ZALOOA_TEXTDOMAIN), "image_icon" => "", "type" => "oa.open.url", "value" => ""], ["title" => __("Liên hệ chăm sóc viên", MX_ZALOOA_TEXTDOMAIN), "image_icon" => MX_ZALOOA_URL . "assets/images/hotline.png", "type" => "oa.open.phone", "value" => ""], ["title" => "", "image_icon" => "", "type" => "oa.query.show", "value" => ""], ["title" => "", "image_icon" => "", "type" => "oa.query.show", "value" => ""]];
                                break;
                        }
                    }
                    return apply_filters("zalooa_get_default", $out, $name, $type);
            }
        }
        public function get_option_default($name = "mess_new_order", $type = "", $values = []) {
            if ($type) {
                switch ($type) {
                    case "new_order":
                        $name = "mess_new_order";
                        break;
                    case "to_completed":
                        $name = "mess_to_completed";
                        break;
                    case "change_order":
                        $name = "mess_change_order";
                        break;
                }
            }
            $mess_new_order = $values ? $values : $this->get_option($name);
            $first = isset($mess_new_order["first"]) && $mess_new_order["first"] ? false : true;
            $active = isset($mess_new_order["active"]) && $mess_new_order["active"] ? 1 : 0;
            $banner = isset($mess_new_order["banner"]) ? esc_url($mess_new_order["banner"]) : "";
            $header = isset($mess_new_order["header"]) ? sanitize_text_field($mess_new_order["header"]) : "";
            $header_align = isset($mess_new_order["header_align"]) ? sanitize_text_field($mess_new_order["header_align"]) : "left";
            $text1 = isset($mess_new_order["text1"]) ? $mess_new_order["text1"] : "";
            $text1_align = isset($mess_new_order["text1_align"]) ? sanitize_text_field($mess_new_order["text1_align"]) : "left";
            $table_code = isset($mess_new_order["table_code"]) ? sanitize_text_field($mess_new_order["table_code"]) : "";
            $table_code_value = isset($mess_new_order["table_code_value"]) ? sanitize_text_field($mess_new_order["table_code_value"]) : "";
            $table_status = isset($mess_new_order["table_status"]) ? sanitize_text_field($mess_new_order["table_status"]) : __("Trạng thái", MX_ZALOOA_TEXTDOMAIN);
            $table_custom1 = isset($mess_new_order["table_custom1"]) ? sanitize_text_field($mess_new_order["table_custom1"]) : "";
            $table_custom1_value = isset($mess_new_order["table_custom1_value"]) ? sanitize_text_field($mess_new_order["table_custom1_value"]) : "";
            $table_custom2 = isset($mess_new_order["table_custom2"]) ? sanitize_text_field($mess_new_order["table_custom2"]) : "";
            $table_custom2_value = isset($mess_new_order["table_custom2_value"]) ? sanitize_text_field($mess_new_order["table_custom2_value"]) : "";
            $table_custom3 = isset($mess_new_order["table_custom3"]) ? sanitize_text_field($mess_new_order["table_custom3"]) : "";
            $table_custom3_value = isset($mess_new_order["table_custom3_value"]) ? sanitize_text_field($mess_new_order["table_custom3_value"]) : "";
            $table_custom4 = isset($mess_new_order["table_custom4"]) ? sanitize_text_field($mess_new_order["table_custom4"]) : "";
            $table_custom4_value = isset($mess_new_order["table_custom4_value"]) ? sanitize_text_field($mess_new_order["table_custom4_value"]) : "";
            $table_custom5 = isset($mess_new_order["table_custom5"]) ? sanitize_text_field($mess_new_order["table_custom5"]) : "";
            $table_custom5_value = isset($mess_new_order["table_custom5_value"]) ? sanitize_text_field($mess_new_order["table_custom5_value"]) : "";
            $text2 = isset($mess_new_order["text2"]) ? $mess_new_order["text2"] : "";
            $text2_align = isset($mess_new_order["text2_align"]) ? sanitize_text_field($mess_new_order["text2_align"]) : "center";
            $buttons = isset($mess_new_order["buttons"]) ? (array) $mess_new_order["buttons"] : [];
            if ($first) {
                if (!$active) {
                    $active = 0;
                }
                if (!$banner) {
                    $banner = $this->get_default("banner_url", $name);
                }
                if (!$header) {
                    $header = $this->get_default("header", $name);
                }
                if (!$text1) {
                    $text1 = $this->get_default("text1", $name);
                }
                if (!$table_code) {
                    $table_code = $this->get_default("table_code", $name);
                }
                if (!$table_code_value) {
                    $table_code_value = $this->get_default("table_code_value", $name);
                }
                if (!$table_custom1) {
                    $table_custom1 = $this->get_default("table_custom1", $name);
                }
                if (!$table_custom1_value) {
                    $table_custom1_value = $this->get_default("table_custom1_value", $name);
                }
                if (!$text2) {
                    $text2 = $this->get_default("text2", $name);
                }
                if (!$buttons && empty($buttons)) {
                    $buttons = (array) $this->get_default("buttons", $name);
                }
            }
            return compact("first", "active", "banner", "header", "header_align", "text1", "text1_align", "table_code", "table_code_value", "table_status", "table_custom1", "table_custom1_value", "table_custom2", "table_custom2_value", "table_custom3", "table_custom3_value", "table_custom4", "table_custom4_value", "table_custom5", "table_custom5_value", "text2", "text2_align", "buttons");
        }
        public function send_zalo_on_new_order($order_id, $order) {
            $zns_mess = $this->get_option("zns_mess");
            if ($order && !is_wp_error($order)) {
                $phone = $order->get_billing_phone();
                if ($phone) {
                    add_filter("mess_filter_content_order_status", [$this, "new_order_status"]);
                    $this->send_mess($phone, $order);
                    remove_filter("mess_filter_content_order_status", [$this, "new_order_status"]);
                }
                $new_order_admin_active = isset($zns_mess["new_order_admin"]["active"]) ? $zns_mess["new_order_admin"]["active"] : 0;
                $new_order_admin_template_id = isset($zns_mess["new_order_admin"]["template_id"]) ? $zns_mess["new_order_admin"]["template_id"] : "";
                $new_order_admin_phone = isset($zns_mess["new_order_admin"]["phone"]) ? $zns_mess["new_order_admin"]["phone"] : "";
                if ($new_order_admin_active && $new_order_admin_phone && $new_order_admin_template_id) {
                    add_filter("mess_filter_content_order_status", [$this, "new_order_status"]);
                    $new_order_admin_phone = explode(",", $new_order_admin_phone);
                    if ($new_order_admin_phone) {
                        foreach ($new_order_admin_phone as $item) {
                            $this->send_mess(trim($item), $order, "new_order_admin");
                        }
                    }
                    remove_filter("mess_filter_content_order_status", [$this, "new_order_status"]);
                }
            }
        }
        public function new_order_status() {
            return apply_filters("zalooa_new_order_status", __("Đơn hàng mới", MX_ZALOOA_TEXTDOMAIN));
        }
        public function send_zalo_on_change_status($order_id, $from, $to) {
            $order = wc_get_order($order_id);
            if ($order && !is_wp_error($order)) {
                if ($from == "pending" && $to == "processing" && $order->get_payment_method() == "cod" || $from == "pending" && $to == "on-hold" && $order->get_payment_method() == "cod" || $from == "pending" && $to == "processing" && $order->get_payment_method() == "bacs" || $from == "pending" && $to == "on-hold" && $order->get_payment_method() == "bacs") {
                    return false;
                }
                $phone = $order->get_billing_phone();
                if ($phone) {
                    if ($to == "completed") {
                        $this->send_mess($phone, $order, "to_completed");
                    } else {
                        $this->send_mess($phone, $order, "change_order");
                    }
                }
            }
        }
        public function send_zalo_on_completed_status($order_id) {
            $order = wc_get_order($order_id);
            if ($order && !is_wp_error($order)) {
                $phone = $order->get_billing_phone();
                if ($phone) {
                    if (is_plugin_active(WOO_POINT_PATH)) {
                        $this->send_mess($phone, $order, "point");
                    }
                    $this->send_mess($phone, $order, "reviews_order");
                }
            }
        }

        public function get_payload_format($type, $value) {
            if (has_filter("zalo_payload_format_" . $type)) {
                $out = apply_filters("zalo_payload_format_" . $type, $value, $type, $value);
            } else {
                switch ($type) {
                    case "oa.open.url":
                        $out = ["url" => $value];
                        break;
                    case "oa.open.phone":
                        $out = ["phone_code" => $value];
                        break;
                    case "oa.open.sms":
                        $value = explode("|", $value);
                        $phone = isset($value[0]) ? sanitize_text_field($value[0]) : "";
                        $content = isset($value[1]) ? sanitize_text_field($value[1]) : "";
                        $out = ["content" => $content, "phone_code" => $phone];
                        break;
                    default:
                        $out = $value;
                }
            }
            return $out;
        }
        public function send_mess_zns($phone, $order, $type) {
            $phone = $this->vn_phone_format($phone);
            $reviews_order_days = 0;
            if (has_action("zalooa_send_mess_" . $type)) {
                do_action("zalooa_send_mess_" . $type, $phone, $order);
            } else {
                $zns_mess = $this->get_option("zns_mess");
                $test_mode = $this->get_option("test_mode");
                $data_args = [];
                switch ($type) {
                    case "new_order_admin":
                        $new_order_admin_active = isset($zns_mess["new_order_admin"]["active"]) ? $zns_mess["new_order_admin"]["active"] : 0;
                        $new_order_admin_template_id = isset($zns_mess["new_order_admin"]["template_id"]) ? $zns_mess["new_order_admin"]["template_id"] : "";
                        $new_order_admin_parameter = isset($zns_mess["new_order_admin"]["parameter"]) ? $zns_mess["new_order_admin"]["parameter"] : [];
                        if ($new_order_admin_active && $new_order_admin_template_id && $new_order_admin_parameter) {
                            $data = ["mode" => "development", "phone" => $phone, "template_id" => strval($new_order_admin_template_id), "tracking_id" => "tracking_" . $order->get_id() . "_" . time() . rand(0, 100), "template_data" => $this->zns_parameter_filter($new_order_admin_parameter, $order, [], $new_order_admin_template_id)];
                            $data_args[] = apply_filters("send_zns_new_order_to_admin_data", $data);
                        }
                        break;
                    case "new_order":
                        $new_order_active = isset($zns_mess["new_order"]["active"]) ? $zns_mess["new_order"]["active"] : 0;
                        $new_order_template_id = isset($zns_mess["new_order"]["template_id"]) ? $zns_mess["new_order"]["template_id"] : "";
                        $new_order_parameter = isset($zns_mess["new_order"]["parameter"]) ? $zns_mess["new_order"]["parameter"] : [];
                        if ($new_order_active && $new_order_template_id && $new_order_parameter) {
                            $data = ["phone" => $phone, "template_id" => strval($new_order_template_id), "tracking_id" => "tracking_" . $order->get_id() . "_" . time(), "template_data" => $this->zns_parameter_filter($new_order_parameter, $order, [], $new_order_template_id)];
                            if ($test_mode) {
                                $data["mode"] = "development";
                            }
                            $data_args[] = $data;
                        }
                        break;
                    case "to_completed":
                        $completed_active = isset($zns_mess["completed"]["active"]) ? $zns_mess["completed"]["active"] : 0;
                        $completed_template_id = isset($zns_mess["completed"]["template_id"]) ? $zns_mess["completed"]["template_id"] : "";
                        $completed_parameter = isset($zns_mess["completed"]["parameter"]) ? $zns_mess["completed"]["parameter"] : [];
                        if ($completed_active && $completed_template_id && $completed_parameter) {
                            $data = ["phone" => $phone, "template_id" => strval($completed_template_id), "tracking_id" => "tracking_" . $order->get_id() . "_" . time(), "template_data" => $this->zns_parameter_filter($completed_parameter, $order, [], $completed_template_id)];
                            if ($test_mode) {
                                $data["mode"] = "development";
                            }
                            $data_args[] = $data;
                        }
                        break;
                    case "point":
                        $point_active = isset($zns_mess["point"]["active"]) ? $zns_mess["point"]["active"] : 0;
                        $point_template_id = isset($zns_mess["point"]["template_id"]) ? $zns_mess["point"]["template_id"] : "";
                        $point_parameter = isset($zns_mess["point"]["parameter"]) ? $zns_mess["point"]["parameter"] : [];
                        $user_id = $order->get_user_id();
                        if ($point_active && $point_template_id && $point_parameter && $user_id) {
                            $MX_Woo_Point_DB = new MX_Woo_Point_DB();
                            $args = [
                                'point' => $MX_Woo_Point_DB->get_latest_order_by_user_id($user_id, $order->get_id()),
                                'total_point' => $MX_Woo_Point_DB->get_total_points_by_user_id($user_id),
                            ];
                            $data = ["phone" => $phone, "template_id" => strval($point_template_id), "tracking_id" => "tracking_point_" . $order->get_id() . "_" . time(), "template_data" => $this->zns_parameter_filter($point_parameter, $order, $args, $point_template_id)];
                            if ($test_mode) {
                                $data["mode"] = "development";
                            }
                            $data_args[] = $data;
                        }
                        break;
                    case "change_order":
                        $change_order_active = isset($zns_mess["change_order"]["active"]) ? $zns_mess["change_order"]["active"] : 0;
                        $change_order_template_id = isset($zns_mess["change_order"]["template_id"]) ? $zns_mess["change_order"]["template_id"] : "";
                        $change_order_parameter = isset($zns_mess["change_order"]["parameter"]) ? $zns_mess["change_order"]["parameter"] : [];
                        if ($change_order_active && $change_order_template_id && $change_order_parameter) {
                            $data = ["phone" => $phone, "template_id" => strval($change_order_template_id), "tracking_id" => "tracking_" . $order->get_id() . "_" . time(), "template_data" => $this->zns_parameter_filter($change_order_parameter, $order, [], $change_order_template_id)];
                            if ($test_mode) {
                                $data["mode"] = "development";
                            }
                            $data_args[] = $data;
                        }
                        break;
                    case "reviews_order":
                        $reviews_order_active = isset($zns_mess["reviews"]["active"]) ? $zns_mess["reviews"]["active"] : 0;
                        $reviews_order_days = isset($zns_mess["reviews"]["days"]) ? intval($zns_mess["reviews"]["days"]) : 0;
                        $reviews_order_template_id = isset($zns_mess["reviews"]["template_id"]) ? $zns_mess["reviews"]["template_id"] : "";
                        $reviews_order_parameter = isset($zns_mess["reviews"]["parameter"]) ? $zns_mess["reviews"]["parameter"] : [];
                        if ($reviews_order_active && $reviews_order_template_id && $reviews_order_parameter) {
                            $items = $order->get_items();
                            foreach ($items as $item) {
                                $product_name = $item->get_name();
                                $product_id = $item->get_product_id();
                                $limit = 100;
                                if ($limit < mb_strlen($product_name)) {
                                    $product_name = mb_substr($product_name, 0, $limit - 3) . "...";
                                }
                                $data = ["phone" => $phone, "template_id" => strval($reviews_order_template_id), "tracking_id" => "tracking_reviews_" . $order->get_id() . "_" . $product_id . "_" . time(), "template_data" => $this->zns_parameter_filter($reviews_order_parameter, $order, ["product_name" => $product_name], $reviews_order_template_id)];
                                if ($test_mode) {
                                    $data["mode"] = "development";
                                }
                                $data_args[] = $data;
                            }
                        }
                        break;
                }
                $data_args = (array) apply_filters("send_mess_zns_" . $type . "_data_args", $data_args, $phone, $order, $type);
                $this->send_zns($data_args, $type, $reviews_order_days);
            }
        }
        public function send_zns($data_args, $type = "", $reviews_order_days = 0) {
            if ($data_args) {
                $send_process = false;
                foreach ($data_args as $data) {
                    $phone = isset($data["phone"]) ? $data["phone"] : "";
                    $tracking_id = isset($data["tracking_id"]) ? $data["tracking_id"] : "";
                    $template_id = isset($data["template_id"]) ? $data["template_id"] : "";
                    $mode = isset($data["mode"]) ? $data["mode"] : "";
                    if ($phone) {
                        $new_data = ["phone" => $phone, "mess_id" => 0, "tracking_id" => $tracking_id, "status" => "pending", "template_id" => $template_id, "data_respon" => "", "data_send" => maybe_serialize($data), "time" => current_time("mysql")];
                        $tin_reviews = $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                        if ($tin_reviews) {
                            $current_hour = intval(date_i18n("H"));
                            if ($type == "reviews_order" && $reviews_order_days) {
                                $scheduled_time = $this->get_scheduled_time($reviews_order_days);
                                wp_schedule_single_event($scheduled_time, "send_zns_again_event", [$tracking_id]);
                                $new_data = ["data_respon" => maybe_serialize(["message" => "Đã lên lịch vào " . date("d/m/Y H:i", $scheduled_time)])];
                                $tin_reviews = $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                            } else {
                                if ($mode == "development" || 6 <= $current_hour && $current_hour <= 21) {
                                    if (apply_filters("zalo_enable_background_process", true)) {
                                        $this->send_tracking_id->push_to_queue($tracking_id);
                                        $send_process = true;
                                    } else {
                                        $this->send_by_tracking_id($tracking_id);
                                    }
                                } else {
                                    $scheduled_time = $this->get_scheduled_time();
                                    wp_schedule_single_event($scheduled_time, "send_zns_again_event", [$tracking_id]);
                                    $new_data = ["data_respon" => maybe_serialize(["message" => "Đã lên lịch vào " . date("d/m/Y H:i", $scheduled_time)])];
                                    $tin_reviews = $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                                }
                            }
                        }
                    }
                }
                if ($send_process) {
                    $this->send_tracking_id->save()->dispatch();
                }
            }
        }
        public function get_scheduled_time($days = 0) {
            date_default_timezone_set("Asia/Ho_Chi_Minh");
            $current_time = current_time("timestamp", true);
            $current_hour = date("H", $current_time);
            $scheduled_time = $current_time;
            if ($days == 0) {
                if (21 < $current_hour) {
                    $next_day = strtotime("+1 day", $current_time);
                    $scheduled_time = strtotime("06:15:00", $next_day);
                } else {
                    if ($current_hour < 6) {
                        $scheduled_time = strtotime("06:15:00", $current_time);
                    }
                }
            } else {
                $next_day = strtotime("+" . $days . " day", $current_time);
                $next_day_h = 21 < $current_hour || $current_hour < 6 ? "06:15:00" : date("H:i:s", $current_time);
                $scheduled_time = strtotime($next_day_h, $next_day);
            }
            return $scheduled_time;
        }
        public function get_scheduled_time_fixed($date, $days = 0) {
            date_default_timezone_set("Asia/Ho_Chi_Minh");
            $fixed_time = strtotime($date);
            $current_hour = date("H", $fixed_time);
            $scheduled_time = $fixed_time;
            if ($days == 0) {
                if (21 < $current_hour) {
                    $next_day = strtotime("+1 day", $fixed_time);
                    $scheduled_time = strtotime("06:15:00", $next_day);
                } else {
                    if ($current_hour < 6) {
                        $scheduled_time = strtotime("06:15:00", $fixed_time);
                    }
                }
            } else {
                $next_day = strtotime("+" . $days . " day", $fixed_time);
                $next_day_h = 21 < $current_hour || $current_hour < 6 ? "06:15:00" : date("H:i:s", $fixed_time);
                $scheduled_time = strtotime($next_day_h, $next_day);
            }
            return $scheduled_time;
        }
        public function send_mess($phone, $order, $type = "new_order") {
            if (!$phone || !$order || is_wp_error($order)) {
                return false;
            }
            $phone = $this->vn_phone_format($phone);
            $user_id = $this->_zalooa_phone_db->get_user_id($phone);
            if (!empty($user_id)) {
                $this->send_mess_zns($phone, $order, $type);
            }

            return false;
            $mess_new_order = $this->get_option_default("", $type);
            $active = isset($mess_new_order["active"]) && $mess_new_order["active"] ? true : false;
            if ($active && !$user_id) {
                $data = ["user_id" => $phone];
                $json_data = json_encode($data);
                $getprofile = $this->zalo_request_get("getprofile", "", ["data" => urlencode($json_data)]);
                $error = isset($getprofile["error"]) && $getprofile["error"] == 0 ? 0 : $getprofile["error"];
                $message = isset($getprofile["message"]) ? $getprofile["message"] : "";
                $data = isset($getprofile["data"]) ? $getprofile["data"] : [];
                if (!$error && $data) {
                    $user_id = isset($data["user_id"]) ? $data["user_id"] : "";
                    $display_name = isset($data["display_name"]) ? $data["display_name"] : "";
                    if ($user_id) {
                        $this->_zalooa_phone_db->add_data_by_userid($user_id, ["phone" => $phone, "user_id" => $user_id, "zalo_active" => 1, "name" => $display_name, "data" => maybe_serialize($data), "time" => current_time("mysql")]);
                    }
                }
            }
            if ($user_id && $active) {
                $this->send_mess_transaction($phone, $user_id, $order, $type);
            } else {
                $this->send_mess_zns($phone, $order, $type);
            }
        }
        public function vn_phone_format($phone) {
            if (substr($phone, 0, 3) !== "+84" && substr($phone, 0, 1) !== "+") {
                $phone = preg_replace("/[^0-9]/", "", $phone);
                if (substr($phone, 0, 1) === "0") {
                    $phone = ltrim($phone, "0");
                    $phone = "+84" . $phone;
                } else {
                    $phone = "+84" . $phone;
                }
            }
            return $phone;
        }
        public function zns_parameter_filter($parameter, $order, $args = [], $template_id = 0) {
            if (!$parameter || !$order || is_wp_error($order)) {
                return false;
            }
            $new_parameter = [];
            foreach ($parameter as $key => $filter) {
                if (!$filter) {
                    $filter = "{" . $key . "}";
                }
                $value = strval($this->mess_filter_content($filter, $order, $args));
                if ($template_id) {
                    $data = get_option("zalooa_zns_template_" . $template_id);
                    if ($data) {
                        $listParams = isset($data["listParams"]) ? $data["listParams"] : [];
                        foreach ($listParams as $item) {
                            $name = isset($item["name"]) ? $item["name"] : "";
                            $type_data = isset($item["type"]) ? strtolower($item["type"]) : "";
                            $maxLength = isset($item["maxLength"]) ? intval($item["maxLength"]) : 30;
                            $minLength = isset($item["minLength"]) ? intval($item["minLength"]) : 0;
                            if ($key == $name) {
                                switch ($type_data) {
                                    case "date":
                                    case "datetime":
                                        if (strpos($value, " ") !== false) {
                                            $value = date_i18n("H:i:s d/m/Y", strtotime($value));
                                        } else {
                                            $value = date_i18n("d/m/Y", strtotime($value));
                                        }
                                        break;
                                    case "number":
                                        $value = floatval(wp_strip_all_tags($value));
                                        break;
                                    default:
                                        $value = mb_substr($value, $minLength, $maxLength);
                                }
                            }
                        }
                    }
                }
                $new_parameter[$key] = apply_filters("zalooa_zns_parameter_filter", $value, $filter, $parameter, $order, $args, $template_id);
            }
            return $new_parameter;
        }
        public function zns_custom_parameter_filter($parameter, $post_data, $args = [], $template_id = 0) {
            if (!$parameter || !$post_data || is_wp_error($post_data)) {
                return false;
            }
            $new_parameter = [];
            foreach ($parameter as $key => $filter) {
                if (!$filter) {
                    $filter = $key;
                }
                if (isset($args[$filter])) {
                    $value = esc_attr($args[$filter]);
                    if (!$value) {
                        $value = apply_filters("zalooa_null_value", "(Không nhập)");
                    }
                } else {
                    $value = isset($post_data[$filter]) && $post_data[$filter] ? $post_data[$filter] : apply_filters("zalooa_null_value", "(Không nhập)");
                }
                if (is_array($value)) {
                    $value = implode(", ", $value);
                }
                $value = esc_attr($value);
                if ($template_id) {
                    $data = get_option("zalooa_zns_template_" . $template_id);
                    if ($data) {
                        $listParams = isset($data["listParams"]) ? $data["listParams"] : [];
                        foreach ($listParams as $item) {
                            $name = isset($item["name"]) ? $item["name"] : "";
                            $type_data = isset($item["type"]) ? strtolower($item["type"]) : "";
                            $maxLength = isset($item["maxLength"]) ? intval($item["maxLength"]) : 30;
                            $minLength = isset($item["minLength"]) ? intval($item["minLength"]) : 0;
                            if ($key == $name) {
                                switch ($type_data) {
                                    case "date":
                                    case "datetime":
                                        if (strpos($value, " ") !== false) {
                                            $value = date_i18n("H:i:s d/m/Y", strtotime($value));
                                        } else {
                                            $value = date_i18n("d/m/Y", strtotime($value));
                                        }
                                        break;
                                    case "number":
                                        $value = floatval(wp_strip_all_tags($value));
                                        break;
                                    default:
                                        $value = mb_substr($value, $minLength, $maxLength);
                                }
                            }
                        }
                    }
                }
                $new_parameter[$key] = strval(apply_filters("zalooa_parameter_filter", $value, $filter, $parameter, $post_data, $args));
            }
            return $new_parameter;
        }
        public function zalo_endpoint_url($name = "quota") {
            $url_mappings = [
                "send_zns" => "https://business.openapi.zalo.me/message/template",
                "quota" => "https://business.openapi.zalo.me/message/quota",
                "template_all" => "https://business.openapi.zalo.me/template/all",
                "template_info" => "https://business.openapi.zalo.me/template/info",
                "getfollowers" => "https://openapi.zalo.me/v2.0/oa/getfollowers",
                "getprofile" => "https://openapi.zalo.me/v2.0/oa/getprofile",
                "transaction" => "https://openapi.zalo.me/v3.0/oa/message/transaction",
                "promotion" => "https://openapi.zalo.me/v3.0/oa/message/promotion"
            ];
            return $url_mappings[$name] ?? "";
        }

        public function zalo_request_post($endpoint, $data_send, $data) {
            $access_token = $this->get_access_token();
            if (!$access_token) {
                return NULL;
            }
            $headers = ["Content-Type" => "application/json", "access_token" => $access_token];
            $body = $data_send;
            $tracking_id = isset($body["tracking_id"]) && $body["tracking_id"] ? $body["tracking_id"] : (isset($data["tracking_id"]) && $data["tracking_id"] ? $data["tracking_id"] : "");
            if (!$tracking_id) {
                return NULL;
            }
            $url = $this->zalo_endpoint_url($endpoint);
            if ($endpoint == "promotion") {
                $user_id = isset($body["recipient"]["user_id"]) ? sanitize_text_field($body["recipient"]["user_id"]) : "";
                if ($user_id) {
                    $response_promotion = wp_remote_post("https://openapi.zalo.me/v2.0/oa/quota/message", ["headers" => $headers, "body" => json_encode(["user_id" => $user_id, "type" => "promotion"]), "timeout" => 10]);
                    $response_promotion_code = wp_remote_retrieve_response_code($response_promotion);
                    $response_promotion_body = json_decode(wp_remote_retrieve_body($response_promotion), true);
                    if (apply_filters("zalo_debug", false)) {
                        ob_start();
                        print_r($response_promotion_code);
                        print_r($response_promotion_body);
                        $logbody = ob_get_clean();
                        $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "zalo check quota: " . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                        file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
                    }
                    if ($response_promotion_code == 200 && !is_wp_error($response_promotion)) {
                        $error = isset($response_promotion_body["error"]) ? $response_promotion_body["error"] : "";
                        $message = isset($response_promotion_body["message"]) ? $response_promotion_body["message"] : "";
                        $remain = isset($response_promotion_body["data"]["remain"]) ? $response_promotion_body["data"]["remain"] : "";
                        $total = isset($response_promotion_body["data"]["total"]) ? $response_promotion_body["data"]["total"] : "";
                        if ($error != 0 || $remain <= 0) {
                            $out = isset($data["data_respon"]) && $data["data_respon"] ? maybe_unserialize($data["data_respon"]) : [];
                            $out["message"] = "(" . $error . ") " . $message . " | Hạn mức gửi tin truyền thông cá nhân: " . $remain . "/" . $total;
                            $new_data = ["mess_id" => 0, "status" => "error", "data_respon" => maybe_serialize($out), "time" => current_time("mysql")];
                            $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }
            $response = wp_remote_post($url, ["headers" => $headers, "body" => json_encode($body), "timeout" => 50]);
            $out = ["error" => 1, "message" => ""];
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            if (apply_filters("zalo_debug", false)) {
                ob_start();
                print_r($response_body);
                $logbody = ob_get_clean();
                $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "zalo_request_post: " . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
            }
            if ($response_code != 200 || is_wp_error($response)) {
                $error_message = $response->get_error_message();
                if (wp_remote_retrieve_response_code($response) === 408) {
                    $out["message"] = "Quá thời gian phản hồi máy chủ";
                } else {
                    $out["message"] = $error_message;
                }
            } else {
                $out = $response_body;
            }
            if (in_array($endpoint, ["send_zns"])) {
                $new_data = ["phone" => isset($body["phone"]) && $body["phone"] ? $body["phone"] : 0, "mess_id" => 0, "tracking_id" => isset($body["tracking_id"]) && $body["tracking_id"] ? $body["tracking_id"] : time(), "status" => "error", "template_id" => isset($body["template_id"]) && $body["template_id"] ? $body["template_id"] : 0, "data_respon" => maybe_serialize($out), "data_send" => maybe_serialize($body), "time" => current_time("mysql")];
                if ($out["error"] == 0) {
                    $new_data["status"] = "completed";
                    $new_data["mess_id"] = isset($out["data"]["msg_id"]) ? sanitize_text_field($out["data"]["msg_id"]) : 0;
                }

                if ($this->_zalooa_mess_db->add_data($tracking_id, $new_data) && in_array($out["error"], apply_filters("error_code_resend_zns", ["-234", "-211", "-32", "-133", "-124"]))) {
                    $scheduled_time = $this->get_scheduled_time(1);
                    wp_schedule_single_event($scheduled_time, "send_zns_again_event", [$tracking_id]);
                    $out["message"] = $out["message"] . " Đã lên lịch gửi lại vào " . date("d/m/Y H:i", $scheduled_time);
                    $out["error"] = 0;
                    $new_data = [
                        "data_respon" => maybe_serialize($out),
                        "status" => "pending"
                    ];
                    $tin_reviews = $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                }
            } else {
                if (in_array($endpoint, ["transaction"])) {
                    $transaction = $out;
                    $error = isset($transaction["error"]) && $transaction["error"] == 0 ? 0 : $transaction["error"];
                    $message = isset($transaction["message"]) ? $transaction["message"] : "";
                    $data_repson = isset($transaction["data"]) ? $transaction["data"] : [];
                    $new_data = ["status" => "error", "data_respon" => maybe_serialize($out), "data_send" => maybe_serialize($body), "time" => current_time("mysql")];
                    if ($error == 0) {
                        $new_data["status"] = "completed";
                        $new_data["mess_id"] = isset($data_repson["message_id"]) ? sanitize_text_field($data_repson["message_id"]) : 0;
                    }
                    $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                    if ($error && !empty($data)) {
                        $phone = isset($data["phone"]) && $data["phone"] ? $data["phone"] : "";
                        $template_id = isset($data["template_id"]) && $data["template_id"] ? $data["template_id"] : "";
                        $type = explode("_", $template_id);
                        $type = isset($type[1]) ? sanitize_text_field($type[1]) : "";
                        $order_id = explode("_", $tracking_id);
                        $order_id = isset($order_id[1]) ? intval($order_id[1]) : "";
                        if ($order_id && $type) {
                            $order = wc_get_order($order_id);
                            if ($order && !is_wp_error($order)) {
                                $this->send_mess_zns($phone, $order, $type);
                            }
                        }
                    }
                } else {
                    if (in_array($endpoint, ["promotion"])) {
                        $new_data = ["mess_id" => 0, "status" => "error", "data_respon" => maybe_serialize($out), "data_send" => maybe_serialize($body), "time" => current_time("mysql")];
                        if ($out["error"] == 0) {
                            $message_id = isset($out["data"]["msg_id"]) ? sanitize_text_field($out["data"]["msg_id"]) : (isset($out["data"]["message_id"]) ? sanitize_text_field($out["data"]["message_id"]) : 0);
                            $new_data["status"] = "completed";
                            $new_data["mess_id"] = $message_id;
                        }
                        $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                    }
                }
            }
            return $out;
        }
        public function zalo_request_get($endpoint = "", $data = [], $parameter = []) {
            $access_token = $this->get_access_token();
            $headers = ["Content-Type" => "application/json", "access_token" => $access_token];
            $body = $data;
            $url = $this->zalo_endpoint_url($endpoint);
            if ($parameter) {
                $url = add_query_arg($parameter, $url);
            }
            if ($url) {
                $response = wp_remote_get($url, ["headers" => $headers, "body" => $body]);
                if (apply_filters("zalo_debug", false)) {
                    ob_start();
                    print_r(json_decode(wp_remote_retrieve_body($response), true));
                    $logbody = ob_get_clean();
                    $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "zalo_request_get: " . $url . PHP_EOL . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                    file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
                }
                if ($response && !is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    return json_decode($body, true);
                }
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    if (wp_remote_retrieve_response_code($response) === 408) {
                        return ["data" => "", "error" => 1, "message" => $error_message];
                    }
                }
            }
            return false;
        }
        public function get_template_all($live = false) {
            if ($live) {
                $out = ["error" => 1, "message" => "", "data" => []];
                $template_all = $this->zalo_request_get("template_all", "", ["offset" => 0, "limit" => 100, "status" => 1]);
                $error = isset($template_all["error"]) && $template_all["error"] == 0 ? 0 : $template_all["error"];
                $message = isset($template_all["message"]) ? $template_all["message"] : "";
                $data = isset($template_all["data"]) ? $template_all["data"] : [];
                if ($error) {
                    $out["message"] = "(" . $error . ") " . $message;
                    delete_option($this->_template_all_name);
                    return $out;
                }
                update_option($this->_optionNamePrefix . "template_all", $data, "no");
                if (!$data) {
                    $out["message"] = "Chưa có template nào được duyệt. Hãy <a href=\"https://account.zalo.cloud/\" target=\"_blank\" rel=\"nofollow\">vào đây để tạo template</a>";
                    return $out;
                }
                $out["error"] = 0;
                $out["data"] = $data;
                return $out;
            }
            $data = get_option($this->_optionNamePrefix . "template_all");
            return $data;
        }
        public function view_template_zns_func() {
            if (!wp_verify_nonce($_REQUEST["nonce"], "zns_setting")) {
                exit("No naughty business please");
            }
            $template_id = isset($_POST["template_id"]) ? sanitize_text_field(wp_unslash($_POST["template_id"])) : "";
            if (!$template_id) {
                wp_send_json_error("Thiếu template_id");
            }
            $data = get_option("zalooa_zns_template_" . $template_id);
            if ($data) {
                $previewUrl = isset($data["previewUrl"]) ? $data["previewUrl"] : "";
            } else {
                $template_all = $this->zalo_request_get("template_info", "", ["template_id" => $template_id]);
                $error = isset($template_all["error"]) && $template_all["error"] == 0 ? 0 : $template_all["error"];
                $message = isset($template_all["message"]) ? $template_all["message"] : "";
                $data = isset($template_all["data"]) ? $template_all["data"] : [];
                if ($error) {
                    wp_send_json_error($message);
                }
                if (!$data) {
                    wp_send_json_error("Không có thông tin");
                }
                update_option("zalooa_zns_template_" . $template_id, $data, "no");
                $previewUrl = isset($data["previewUrl"]) ? $data["previewUrl"] : "";
            }
            if ($previewUrl) {
                wp_send_json_success(["url" => $previewUrl]);
            } else {
                wp_send_json_error("Không có link preview");
            }
            exit;
        }
        public function load_template_parameter_func() {
            if (!wp_verify_nonce($_REQUEST["nonce"], "zns_setting")) {
                exit("No naughty business please");
            }
            $setting_name = isset($_POST["name_field"]) ? sanitize_text_field(wp_unslash($_POST["name_field"])) : "";
            $setting_name = str_replace("template_id", "parameter", $setting_name);
            $template_id = isset($_POST["template_id"]) ? sanitize_text_field(wp_unslash($_POST["template_id"])) : "";
            $woo_action = isset($_POST["woo_action"]) ? sanitize_text_field(wp_unslash($_POST["woo_action"])) : "";
            $type = isset($_POST["type"]) ? sanitize_text_field(wp_unslash($_POST["type"])) : "select";
            $template_all = $this->zalo_request_get("template_info", "", ["template_id" => $template_id]);
            $error = isset($template_all["error"]) && $template_all["error"] == 0 ? 0 : $template_all["error"];
            $message = isset($template_all["message"]) ? $template_all["message"] : "";
            $data = isset($template_all["data"]) ? $template_all["data"] : [];
            if ($error) {
                wp_send_json_error($message);
            }
            if (!$data) {
                wp_send_json_error("Không có thông tin");
            }
            update_option("zalooa_zns_template_" . $template_id, $data, "no");
            $out = [];
            $out["html"] = $this->get_html_parameter_template($template_id, $setting_name, $woo_action, $type);
            wp_send_json_success($out);
            exit;
        }
        public function get_html_parameter_template($template_id, $setting_name, $action = "new_order", $type = "select", $value_rules = []) {
            $data = get_option("zalooa_zns_template_" . $template_id);
            $listParams = isset($data["listParams"]) ? $data["listParams"] : [];
            $filter_content_args = $this->get_filter_content_args();
            $zns_mess = empty($value_rules) ? $this->get_option("zns_mess") : $value_rules;
            ob_start();
        ?>
            <table class="table_template_style">
                <thead>
                    <th>Tham số</th>
                    <th>Giá trị</th>
                </thead>
                <tbody>
                    <?php
                    foreach ($listParams as $item) {
                        $name = isset($item["name"]) ? $item["name"] : "";
                        $type_data = isset($item["type"]) ? $item["type"] : "";
                        $maxLength = isset($item["maxLength"]) ? $item["maxLength"] : 30;
                        $minLength = isset($item["minLength"]) ? $item["minLength"] : 0;
                        $value_option = isset($zns_mess[$action]["parameter"][$name]) ? $zns_mess[$action]["parameter"][$name] : "";
                        $first = isset($zns_mess[$action]["parameter"][$name]) ? false : true;
                        if ($name) {
                    ?>
                            <tr>
                                <td><?= $name ?></td>
                                <td>
                                    <?php if ($type == "select") : ?>
                                        <select name="<?= esc_attr($setting_name) ?>[<?= $name ?>]">
                                            <option value="">Chọn giá trị</option>
                                            <?php foreach ($filter_content_args as $key => $value) : ?>
                                                <?php
                                                $selected = false;
                                                if ($first && (
                                                    ($name == "name" && $key == "{order_full_name}") ||
                                                    ($name == "order_code" && $key == "{order_id}") ||
                                                    ($name == "price" && $key == "{order_total}") ||
                                                    ($name == "status" && $key == "{order_status}") ||
                                                    ($name == "date" && $key == "{order_create_date}") ||
                                                    ($name == "product_name" && $key == "{product_name}") ||
                                                    ($name == "note" && $key == "{note}") ||
                                                    ($name == "phone_number" && $key == "{phone_number}") ||
                                                    ($name == "address" && $key == "{address}") ||
                                                    ($name == "custom_url" && $key == "{order_view_detail_urlencode}") ||
                                                    ($name == "order_view_detail_urlencode" && $key == "{order_view_detail_urlencode}") ||
                                                    ($name == "order_key" && $key == "{order_key}")
                                                ) || $value_option == $key) : ?>
                                                    <?php $selected = true; ?>
                                                <?php endif; ?>
                                                <option value="<?= esc_attr($key) ?>" <?= $selected ? "selected=\"selected\"" : "" ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <?php if ($type == "bylink") : ?>
                                            Kiểu data: <?= esc_attr($type_data) ?><br>
                                            Max length: <?= esc_attr($maxLength) ?><br>
                                            <input type="hidden" name="<?= esc_attr($setting_name) ?>[<?= $name ?>]" value="">
                                        <?php else : ?>
                                            <input type="text" name="<?= esc_attr($setting_name) ?>[<?= $name ?>]" value="<?= esc_attr($value_option) ?>">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
<?php
            return ob_get_clean();
        }

        public function has_access_token() {
            $zalooa_access_token_data = get_transient($this->get_name_transient_access_token());
            $zalooa_access_token_error = get_option("zalooa_access_token_error");
            $appid = $this->get_option("appid");
            return $zalooa_access_token_data && !$zalooa_access_token_error && $appid;
        }
        public function send_zns_again_event_callback($tracking_id) {
            $this->send_by_tracking_id($tracking_id);
        }
        public function send_by_tracking_id($tracking_id, $over_send = false) {
            if (!$tracking_id) {
                return NULL;
            }
            $data = $this->_zalooa_mess_db->get_data_by_tracking_id($tracking_id);
            $data_send = $data->data_send ? maybe_unserialize($data->data_send) : [];
            $status = $data->status;
            $template_id = $data->template_id;
            if (!$over_send && in_array($status, ["completed", "rated"])) {
                return true;
            }
            $test_mode = $this->get_option("test_mode");
            if ($test_mode) {
                $data_send["mode"] = "development";
            }
            $endpoint = "send_zns";
            if (strpos($template_id, "transaction_") !== false) {
                $endpoint = "transaction";
            } else {
                if (strpos($template_id, "promotion_campaign_") !== false) {
                    $endpoint = "promotion";
                }
            }
            if ($data_send) {
                $this->zalo_request_post($endpoint, $data_send, (array) $data);
            }
            return true;
        }
        public function zalo_webhook_func() {
            $POST = json_decode(file_get_contents("php://input"), true);
            if (apply_filters("zalo_debug", false)) {
                ob_start();
                echo "JSON POST" . PHP_EOL;
                print_r($POST);
                echo "POST" . PHP_EOL;
                print_r($_POST);
                echo "GET" . PHP_EOL;
                print_r($_GET);
                $logbody = ob_get_clean();
                $log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . "zalo_webhook_func: " . PHP_EOL . $logbody . PHP_EOL . "-------------------------" . PHP_EOL;
                file_put_contents(MX_ZALOOA_PLUGIN_DIR . "/log_zalo_" . $this->get_option("appid") . ".txt", $log, FILE_APPEND);
            }
            if (isset($_POST) && empty($_POST)) {
                $_POST = $POST;
            }
            if (isset($_POST["message"]["text"]) && $_POST["message"]["text"] == "This is testing message") {
                wp_send_json_success("okie testing");
            }
            $event_name = isset($_POST["event_name"]) ? sanitize_text_field($_POST["event_name"]) : "";
            $message = isset($_POST["message"]) ? $_POST["message"] : "";
            $app_id = isset($_POST["app_id"]) ? sanitize_text_field($_POST["app_id"]) : "";
            $key = isset($_GET["key"]) ? sanitize_text_field($_GET["key"]) : "";
            if ($key != $this->get_option("webhook_url_key")) {
                wp_send_json_error("Lỗi bảo mật");
            }
            if ($app_id == $this->get_option("appid")) {
                switch ($event_name) {
                    case "user_feedback":
                        $tracking_id = isset($message["tracking_id"]) ? wp_unslash($message["tracking_id"]) : "";
                        $feedbacks = isset($message["feedbacks"]) ? array_map("sanitize_text_field", $message["feedbacks"]) : [];
                        $note = isset($message["note"]) ? wp_unslash($message["note"]) : "";
                        $rate = isset($message["rate"]) ? intval($message["rate"]) : 5;
                        $timestamp = isset($message["timestamp"]) ? $message["timestamp"] : "";
                        $data = $this->_zalooa_mess_db->get_data_by_tracking_id($tracking_id);
                        if ($data->status == "rated") {
                            wp_send_json_success("Đã đánh giá");
                        }
                        $tracking_args = explode("_", $tracking_id);
                        $product_id = isset($tracking_args[3]) ? intval($tracking_args[3]) : "";
                        $order_id = isset($tracking_args[2]) ? intval($tracking_args[2]) : "";
                        if ($product_id) {
                            $product = wc_get_product($product_id);
                            if ($product && !is_wp_error($product)) {
                                $parent_id = $product->get_parent_id();
                                if ($parent_id) {
                                    $product = wc_get_product($parent_id);
                                    if (!$product || is_wp_error($product)) {
                                        wp_send_json_error("Không tìm thấy sp");
                                    }
                                }
                                $commentdata = ["comment_post_ID" => $product->get_id(), "comment_content" => $note, "comment_parent" => 0, "comment_type" => "review", "comment_approved" => 1, "comment_date" => date_i18n("Y-m-d H:i:s", $timestamp)];
                                $order = wc_get_order($order_id);
                                if ($order && !is_wp_error($order)) {
                                    $email = $order->get_billing_email();
                                    $name = $order->get_formatted_billing_full_name();
                                    if ($email) {
                                        $commentdata["comment_author_email"] = $email;
                                    }
                                    if ($name) {
                                        $commentdata["comment_author"] = $name;
                                    }
                                }
                                $comment_id = wp_insert_comment($commentdata);
                                if ($comment_id) {
                                    if ($rate) {
                                        update_comment_meta($comment_id, "rating", $rate);
                                    }
                                    add_comment_meta($comment_id, "verified", true);
                                    add_comment_meta($comment_id, "zalo_tracking", $tracking_id);
                                    if ($feedbacks) {
                                        $quick_tags = array_map("esc_attr", $feedbacks);
                                        add_comment_meta($comment_id, "quick_tag", $quick_tags);
                                    }
                                    $new_data = ["status" => "rated"];
                                    $this->_zalooa_mess_db->add_data($tracking_id, $new_data);
                                    do_action("zalooa_after_user_feedback", $comment_id, $message);
                                    $this->clear_transients_review($product->get_id());
                                    wp_send_json_success();
                                }
                            }
                        }
                        break;
                    default:
                        wp_send_json_success();
                }
            }
            wp_send_json_error();
            exit;
        }
        public function clear_transients_review($post_id) {
            if (function_exists("mx_reviews")) {
                mx_reviews()->mx_clear_transients_count_review($post_id);
            } else {
                if ("product" === get_post_type($post_id)) {
                    $product = wc_get_product($post_id);
                    $product->set_rating_counts(WC_Comments::get_rating_counts_for_product($product));
                    $product->set_average_rating(WC_Comments::get_average_rating_for_product($product));
                    $product->set_review_count(WC_Comments::get_review_count_for_product($product));
                    $product->save();
                }
            }
        }
        public function send_again_mess_func() {
            if (!wp_verify_nonce($_REQUEST["nonce"], "action_send_again")) {
                exit("No naughty business please");
            }
            $trackingid = isset($_POST["trackingid"]) ? wp_unslash($_POST["trackingid"]) : "";
            if (!$trackingid) {
                wp_send_json_error(__("Thiếu thông tin đầu vào", "mx-zalo-oa"));
            }
            $out = [];
            if ($trackingid) {
                $data = $this->_zalooa_mess_db->get_data_by_tracking_id($trackingid);
                $data_send = $data->data_send ? maybe_unserialize($data->data_send) : [];
                $template_id = $data->template_id;
                $test_mode = $this->get_option("test_mode");
                if ($test_mode) {
                    $data_send["mode"] = "development";
                }
                $endpoint = "send_zns";
                if (strpos($template_id, "transaction_") !== false) {
                    $endpoint = "transaction";
                } else {
                    if (strpos($template_id, "promotion_campaign_") !== false) {
                        $endpoint = "promotion";
                    }
                }
                if ($data_send) {
                    $out = $this->zalo_request_post($endpoint, $data_send, (array) $data);
                }
            }
            $error = isset($out["error"]) ? $out["error"] : 0;
            $message = isset($out["message"]) ? $out["message"] : "";
            if ($error) {
                wp_send_json_error($message ? $message : __("Có lỗi khi gửi yêu cầu", "mx-zalo-oa"));
            } else {
                wp_clear_scheduled_hook("send_zns_again_event", [$trackingid]);
                wp_send_json_success($message ? $message : __("Đã yêu cầu gửi lại", "mx-zalo-oa"));
            }
            exit;
        }



        public function filter_email_recipients($recipients) {
            foreach ($recipients as $key => $recipient) {
                if (strpos($recipient, "@zalo.me") !== false) {
                    unset($recipients[$key]);
                }
            }
            return $recipients;
        }


        public function register_user_notification_route() {
            $base_args = array(
                'methods'             => 'POST',
                'callback'            => function ($request) {
                    $params = $request->get_params();
                    $phone = $this->vn_phone_format($params['phone_number'] ?? '');
                    $key = $params['key'] ?? '';
                    $zns_mess = $this->get_option("zns_mess");
                    $follow_key = $zns_mess["follow"]["key"] ?? null;

                    if ($follow_key != $key) {
                        return new WP_Error('invalid_token', 'Invalid or expired token.', array('status' => 400));
                    }

                    $test_mode = $this->get_option("test_mode");
                    $follow_active = $zns_mess["follow"]["active"] ?? 0;
                    $follow_template_id = $zns_mess["follow"]["template_id"] ?? "";
                    $follow_parameter = $params;

                    if ($follow_active && $follow_template_id && $follow_parameter) {
                        $data = get_option("zalooa_zns_template_" . $follow_template_id);

                        if ($data) {
                            $data_args = array(
                                "phone"         => $phone,
                                "template_id"   => strval($follow_template_id),
                                "tracking_id"   => "tracking_follow_" . time(),
                                "template_data" => array(),
                            );

                            $listParams = $data["listParams"] ?? array();
                            foreach ($listParams as $item) {
                                if (!empty($item['name']) && !empty($params[$item['name']])) {
                                    $data_args['template_data'][$item['name']] = $params[$item['name']];
                                }
                            }

                            if ($test_mode) {
                                $data_args["mode"] = "development";
                            }
                        }
                    }

                    return $this->zalo_request_post("send_zns", $data_args ?? array(), array());
                },
                'permission_callback' => '__return_true',
                'args'                => array(
                    'key' => array(
                        'default'           => '',
                        'sanitize_callback' => function ($param, $request, $key) {
                            return filter_var($param, FILTER_SANITIZE_STRING);
                        },
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param);
                        }
                    ),
                ),
            );
            register_rest_route('notification', '/user/register', $base_args);
        }
    }

    if (!function_exists("mx_zalo_main")) {
        function mx_zalo_main() {
            return MX_ZaloOA_Dacbiet_Class::init();
        }
        mx_zalo_main();
    }
}
if (!function_exists("zalooa_get_template")) {
    function zalooa_get_template($template_name, $args = [], $template_path = "", $default_path = "") {
        if (!empty($args) && is_array($args)) {
            extract($args);
        }
        $located = zalooa_locate_template($template_name, $template_path, $default_path);
        if (!file_exists($located)) {
            zalooa_doing_it_wrong("zalooa_get_template", sprintf(__("%s không tồn tại.", "mx-zalooa"), "<code>" . $located . "</code>"), "2.1");
        } else {
            $located = apply_filters("zalooa_get_template", $located, $template_name, $args, $template_path, $default_path);
            do_action("zalooa_before_template_part", $template_name, $template_path, $located, $args);
            include $located;
            do_action("zalooa_after_template_part", $template_name, $template_path, $located, $args);
        }
    }
}
if (!function_exists("zalooa_locate_template")) {
    function zalooa_locate_template($template_name, $template_path = "", $default_path = "") {
        if (!$template_path) {
            $template_path = apply_filters("zalooa_template_path", "mx-zalooa/");
        }
        if (!$default_path) {
            $default_path = untrailingslashit(plugin_dir_path(dirname(__FILE__))) . "/templates/";
        }
        $template = locate_template([trailingslashit($template_path) . $template_name, $template_name]);
        if (!$template) {
            $template = $default_path . $template_name;
        }
        return apply_filters("zalooa_locate_template", $template, $template_name, $template_path);
    }
}
if (!function_exists("zalooa_doing_it_wrong")) {
    function zalooa_doing_it_wrong($function, $message, $version) {
        $message .= " Backtrace: " . wp_debug_backtrace_summary();
        if (is_ajax()) {
            do_action("doing_it_wrong_run", $function, $message, $version);
            error_log($function . " was called incorrectly. " . $message . ". This message was added in version " . $version . ".");
        } else {
            _doing_it_wrong($function, $message, $version);
        }
    }
}
