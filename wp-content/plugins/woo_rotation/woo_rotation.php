<?php

/**
 * Plugin Name: woo_rotation
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0
 * Author: WOO_rotation
 * Author URI: http://yourwebsiteurl.com/
 **/

defined('ABSPATH') or die('Hey, you can\t access this file, you silly human!');

if (is_admin()) {
  new Woo_Rotation();
}

class Woo_Rotation
{
  public $plugin_path;
  public $plugin_url;

  public function __construct()
  {
    $this->plugin_path = plugin_dir_path(dirname(__FILE__, 1)) . 'woo_rotation';
    $this->plugin_url = plugin_dir_url(dirname(__FILE__)) . 'woo_rotation';
    add_action('admin_menu', array($this, 'themeslug_rotation_enqueue_style'));
    add_action('admin_menu', array($this, 'add_menu_rotation_option'));
  }

  function themeslug_rotation_enqueue_style()
  {
    wp_enqueue_style('add_rotation_style', $this->plugin_url . '/assets/styles/rotation-styles.css');
    wp_enqueue_script('add_rotation_script', $this->plugin_url . '/assets/scripts/rotation-scripts.js');
  }

  public function add_menu_rotation_option()
  {
    $this->plugin_rotation_option();
  }

  public function plugin_rotation_option()
  {
    add_menu_page('Vòng quay', 'Vòng quay', 'manage_options', 'rotation',  array($this, 'admin_rotation_template'));
  }

  function admin_rotation_template()
  {
    return require_once("$this->plugin_path/templates/admin.php");
  }
}
function plugin_setup_rotation_db()
{
  // Function change serialized
  set_time_limit(-1);
  global $wpdb;
  try {
    if (!function_exists('dbDelta')) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }
  
    $ptbd_table_name = $wpdb->prefix . 'woo_list_rotations';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          name VARCHAR(255) NOT NULL,
          point BIGINT NOT NULL,
          rate INT NULL,
          status INT DEFAULT 1, 
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }
    $ptbd_table_name = $wpdb->prefix . 'woo_user_turn_rotations';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          user_id BIGINT NOT NULL,
          status INT DEFAULT 1, 
          date VARCHAR(255)  NULL,
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }
    $ptbd_table_name = $wpdb->prefix . 'woo_history_user_rotation';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          user_id BIGINT NOT NULL,
          user_parent BIGINT  NULL,
          product_id INT NULL,
          total_order INT NOT NULL,
          order_id INT NULL,
          commission INT DEFAULT 0,
          commission_level2 INT DEFAULT 0,
          minimum_spending INT  NULL,
          date VARCHAR(255)  NULL,
          month VARCHAR(255)  NULL,
          year VARCHAR(255)  NULL,
          payment_method TEXT  NULL,
          status INT DEFAULT 1, 
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }
  } catch (\Exception $ex) {
  }
}
function active_plugin_rotation()
{
  flush_rewrite_rules();
  plugin_setup_rotation_db();
}

register_activation_hook(__FILE__, 'active_plugin_rotation');
