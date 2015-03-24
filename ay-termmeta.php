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

  public static $meta_fields = array();

  public static function init() {
    register_activation_hook( __FILE__, array('AyTermMeta', 'install') );
    add_action( 'admin_init', array('AyTermMeta', 'init_forms') );
    add_action( 'created_term',  array('AyTermMeta', 'term_input_add_save'), 10, 3 );
    add_action( 'edited_terms',  array('AyTermMeta', 'term_input_edit_save'), 10, 2 );

    global $wpdb;
    $wpdb->termmeta = $wpdb->prefix . "termmeta";
  }

  public static function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "termmeta"; 
    if (!empty ($wpdb->charset)) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }
    if (!empty ($wpdb->collate)) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        term_id bigint(20) unsigned NOT NULL DEFAULT '0',
        meta_key varchar(255) DEFAULT NULL,
        meta_value longtext,
        UNIQUE KEY meta_id (meta_id)
        ) $charset_collate;
      ALTER TABLE {$table_name}
        ADD PRIMARY KEY (meta_id),
        ADD KEY term_id (term_id),
        ADD KEY meta_key (meta_key);";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

  }

  public static function init_forms() {

    foreach(self::$meta_fields as $term_name => $fields) {
      add_action( $term_name . '_edit_form_fields',  array('AyTermMeta', 'term_input_edit') );
      add_action( $term_name . '_add_form_fields',  array('AyTermMeta', 'term_input_add') );
    }

  }

  public static function term_input_add($tag) {
    ?>
    <?php foreach(self::$meta_fields[$tag] as $input) : ?>
      <div class="form-field term-<?php echo $input->name; ?>-wrap">
        <label for="<?php echo $input->name; ?>"><?php echo $input->label; ?></label>
        <?php self::display_field($input); ?>
        <p><?php echo $input->description; ?></p>
      </div>
    <?php endforeach; ?>
    <?php
  }

  public static function term_input_edit($tag) {
    ?>
    <?php foreach(self::$meta_fields[$tag->taxonomy] as $input) : ?>
      <tr class="form-field term-<?php echo $input->name; ?>-wrap">
        <th scope="row"><label for="<?php echo $input->name; ?>"><?php echo $input->label; ?></label></th>
        <td>
          <?php self::display_field($input, $tag->term_id); ?>
          <p class="description"><?php echo $input->description; ?></p>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php
  }

  private static function display_field($input, $term_id = 0) {
    $default_value = get_term_meta($term_id, $input->name, true);

    if($input->type == 'checkbox' AND (!$default_value OR !count($default_value))){
      $default_value = array();
    }

    ?>
    <?php if($input->type == 'input') : ?>

      <input type="text" id="<?php echo $input->name; ?>" name="<?php echo $input->name; ?>" value="<?php echo  $default_value; ?>" />

    <?php elseif($input->type == 'radio') : ?>

      <?php foreach($input->options as $value => $label) : ?>
      <input type="radio" name="<?php echo $input->name; ?>" value="<?php echo $value; ?>" <?php if($value == $default_value) : ?>checked<?php endif; ?>/> <?php echo $label; ?>&nbsp;&nbsp;&nbsp;
      <?php endforeach; ?>

    <?php elseif($input->type == 'select') : ?>

      <select name="<?php echo $input->name; ?>" id="<?php echo $input->name; ?>">
      <?php foreach($input->options as $value => $label) : ?>
      <option value="<?php echo $value; ?>" <?php if($value == $default_value) : ?>selected<?php endif; ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
      </select>

    <?php elseif($input->type == 'checkbox') : ?>

      <?php foreach($input->options as $value => $label) : ?>
      <input type="checkbox" name="<?php echo $input->name; ?>[]" value="<?php echo $value; ?>" <?php if(in_array($value, $default_value)) : ?>checked<?php endif; ?>/> <?php echo $label; ?><br/>
      <?php endforeach; ?>

    <?php endif; ?>
    <?php
  }
 
  public static function term_input_add_save($term_id, $tt_id, $taxonomy) {

    self::term_save($term_id, $taxonomy);

  }
 
  public static function term_input_edit_save($term_id, $taxonomy) {

    self::term_save($term_id, $taxonomy);

  }

  private static function term_save($term_id, $taxonomy) {
    
    if(isset(self::$meta_fields[$taxonomy])) {
      foreach(self::$meta_fields[$taxonomy] as $input) {
        if(isset($_POST[$input->name])) {
          $value = $_POST[$input->name];
          if(is_array($value)) {
            $value = array_map( 'esc_attr', $value );
          } else {
            $value = esc_attr($value);
          }
          update_term_meta($term_id, $input->name, $value);
        } elseif($input->type == 'checkbox'){
          delete_term_meta($term_id, $input->name);
        }
      }
    }

  }

  public static function addMeta($term, $name, $label, $type = 'input', $description = '', $options = array()) {

    if(!isset(self::$meta_fields[$term])) {
      self::$meta_fields[$term] = array();
    }

    $meta = new StdClass();
    $meta->name = $name;
    $meta->label = $label;
    $meta->type = $type;
    $meta->description = $description;
    $meta->options = $options;
    self::$meta_fields[$term][] = $meta;

  }

}

AyTermMeta::init();

function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
  return add_metadata('term', $term_id, $meta_key, $meta_value, $unique);
}

function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
  return update_metadata('term', $term_id, $meta_key, $meta_value, $prev_value);
}

function get_term_meta( $term_id, $key = '', $single = false ) {
  return get_metadata('term', $term_id, $key, $single);
}

function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
  return delete_metadata('term', $term_id, $meta_key, $meta_value);
}
