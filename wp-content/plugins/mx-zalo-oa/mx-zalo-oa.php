<?php
/*
* Plugin Name: MX - Zalo OA
* Version: 1.0
* Description: MX - Zalo OA
* Author: MX
*/

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('MX_ZALOOA_VERSION_NUM'))
    define('MX_ZALOOA_VERSION_NUM', '1.0');
if (!defined('MX_ZALOOA_URL'))
    define('MX_ZALOOA_URL', plugin_dir_url(__FILE__));
if (!defined('MX_ZALOOA_BASENAME'))
    define('MX_ZALOOA_BASENAME', plugin_basename(__FILE__));
if (!defined('MX_ZALOOA_PLUGIN_DIR'))
    define('MX_ZALOOA_PLUGIN_DIR', plugin_dir_path(__FILE__));
if (!defined('MX_ZALOOA_TEXTDOMAIN'))
    define('MX_ZALOOA_TEXTDOMAIN', 'mx-zalo-oa');
if (!defined('WOO_POINT_PATH'))
    define('WOO_POINT_PATH', 'woo_point/woo_point.php');

include_once(ABSPATH.'wp-admin/includes/plugin.php');
include_once MX_ZALOOA_PLUGIN_DIR . 'includes/zalooa-sms-table.php';
include_once MX_ZALOOA_PLUGIN_DIR . 'includes/zalooa-send-background-process.php';
include 'includes/main.php';

