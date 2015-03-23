<?php
/**
 * Plugin Name: Term meta
 * Plugin URI: http://ayctor.com/
 * Description: Add meta to terms
 * Version: 0.0.1
 * Author: Ayctor
 * Author URI: http://ayctor.com
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class AyTermMeta {

  public static function init() {

  }

  public static function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "liveshoutbox"; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      name tinytext NOT NULL,
      text text NOT NULL,
      url varchar(55) DEFAULT '' NOT NULL,
      UNIQUE KEY id (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

}