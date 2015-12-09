<?php
/**
 * @author Erwan Guillon <erwan@ayctor.com>
 * @license https://www.gnu.org/licenses/gpl-2.0.html GPL2
 * @link https://github.com/Ayctor/ay-termmeta Github repository
 */

/**
 * Plugin Name: AY Term Meta
 * Plugin URI: http://ayctor.github.io/ay-termmeta/
 * Description: Add meta to terms
 * Version: 0.9.2
 * Author: Ayctor
 * Author URI: http://ayctor.com/
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class AyTermMeta {

  /**
   * Meta fields to manage.
   * @var array
   */
  private static $meta_fields = array();

  /**
   * Init function.
   *
   * Register activation hook and term management actions.
   * 
   * @return void
   */
  public static function init() {
    register_activation_hook( __FILE__, array('AyTermMeta', 'install') );
    add_action( 'admin_init', array('AyTermMeta', 'admin_init') );
    add_action( 'created_term',  array('AyTermMeta', 'term_input_add_save'), 10, 3 );
    add_action( 'edited_terms',  array('AyTermMeta', 'term_input_edit_save'), 10, 2 );

    global $wpdb;
    $wpdb->termmeta = $wpdb->prefix . "termmeta";
  }

  /**
   * Create the table if not exists.
   * @return void
   */
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

  /**
   * Init the custom fields and JS enqueue process.
   * 
   * @return void
   */
  public static function admin_init() {

    foreach(self::$meta_fields as $term_name => $fields) {
      add_action( $term_name . '_edit_form_fields',  array('AyTermMeta', 'term_input_edit') );
      add_action( $term_name . '_add_form_fields',  array('AyTermMeta', 'term_input_add') );
    }

    // Load JS only on term pages and when file input type is present
    global $pagenow;
    if($pagenow == 'edit-tags.php' AND isset($_GET['taxonomy']) AND isset(self::$meta_fields[$_GET['taxonomy']]) ) {
      $file = false;
      foreach(self::$meta_fields[$_GET['taxonomy']] as $input) {
        if($input->type == 'file') $file = true;
      }
      if($file) add_action('admin_enqueue_scripts',array('AyTermMeta', 'scripts'));
    }

  }

  /**
   * Display the field line in the add form.
   * @param  String $term The taxonomy.
   * @return void
   */
  public static function term_input_add($term) {
    ?>
    <?php foreach(self::$meta_fields[$term] as $input) : ?>
      <div class="form-field term-<?php echo $input->name; ?>-wrap">
        <label for="<?php echo $input->name; ?>"><?php echo $input->label; ?></label>
        <?php self::display_field($input); ?>
        <p><?php echo $input->description; ?></p>
      </div>
    <?php endforeach; ?>
    <?php
  }

  /**
   * Display the field line in the edit form.
   * @param  Object $term Current term.
   * @return void
   */
  public static function term_input_edit($term) {
    ?>
    <?php foreach(self::$meta_fields[$term->taxonomy] as $input) : ?>
      <tr class="form-field term-<?php echo $input->name; ?>-wrap">
        <th scope="row"><label for="<?php echo $input->name; ?>"><?php echo $input->label; ?></label></th>
        <td>
          <?php self::display_field($input, $term->term_id); ?>
          <p class="description"><?php echo $input->description; ?></p>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php
  }

  /**
   * Display input based on input type.
   * @param  Object  $input   The input added.
   * @param  integer $term_id Term ID.
   * @return void
   */
  private static function display_field($input, $term_id = 0) {
    $default_value = get_term_meta($term_id, $input->name, true);

    if($input->type == 'checkbox' AND (!$default_value OR !count($default_value))){
      $default_value = array();
    }

    ?>
    <?php if($input->type == 'radio') : ?>

      <?php foreach($input->options as $value => $label) : ?>
      <input type="radio" name="<?php echo $input->name; ?>" value="<?php echo $value; ?>" <?php if($value == $default_value) : ?>checked<?php endif; ?>/> <?php echo $label; ?>&nbsp;&nbsp;&nbsp;
      <?php endforeach; ?>

    <?php elseif($input->type == 'select') : ?>

      <select name="<?php echo $input->name; ?>" id="<?php echo $input->name; ?>">
      <option value=""></option>
      <?php foreach($input->options as $value => $label) : ?>
      <option value="<?php echo $value; ?>" <?php if($value == $default_value) : ?>selected<?php endif; ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
      </select>

    <?php elseif($input->type == 'checkbox') : ?>

      <?php foreach($input->options as $value => $label) : ?>
      <input type="checkbox" name="<?php echo $input->name; ?>[]" value="<?php echo $value; ?>" <?php if(in_array($value, $default_value)) : ?>checked<?php endif; ?>/> <?php echo $label; ?><br/>
      <?php endforeach; ?>

    <?php elseif($input->type == 'file') : ?>

      <input type="hidden" id="<?php echo $input->name; ?>" name="<?php echo $input->name; ?>" value="<?php echo $default_value; ?>" />
      <?php if($default_value != '') : ?>
        <?php if(wp_attachment_is_image( $default_value )) : ?>
          <?php $image = wp_get_attachment_image_src($default_value, 'thumbnail'); ?>
          <img src="<?php if(isset($image[0])) echo $image[0]; ?>" alt="" class="file-rep">
        <?php else : ?>
          <?php $url = wp_get_attachment_url( $default_value ); ?>
          <a href="<?php if($url) echo $url; ?>" title="" class="file-rep">Download file</a>
        <?php endif; ?>
      <br>
      <button class="button del-file">Remove file</button>
      <?php endif; ?>
      <button class="button btn-file" data-name="<?php echo $input->label; ?>" data-target="<?php echo $input->name; ?>"><?php if($default_value != '') : ?>Change file<?php else : ?>Add file<?php endif; ?></button>

    <?php elseif($input->type == 'textarea') : ?>

      <textarea name="<?php echo $input->name; ?>" id="<?php echo $input->name; ?>" rows="5"><?php echo  $default_value; ?></textarea>

    <?php else : ?>

      <input type="text" id="<?php echo $input->name; ?>" name="<?php echo $input->name; ?>" value="<?php echo  $default_value; ?>" />

    <?php endif; ?>
    <?php
  }

  /**
   * Save term meta after add wrapper.
   * @param  int $term_id     Term ID.
   * @param  int $tt_id       Term ID.
   * @param  String $taxonomy The taxonomy.
   * @return void
   */
  public static function term_input_add_save($term_id, $tt_id, $taxonomy) {

    self::term_save($term_id, $taxonomy);

  }

  /**
   * Save term meta after edit wrapper.
   * @param  int $term_id     Term ID.
   * @param  String $taxonomy The taxonomy.
   * @return void
   */
  public static function term_input_edit_save($term_id, $taxonomy) {

    self::term_save($term_id, $taxonomy);

  }

  /**
   * The actual save function.
   * @param  int $term_id     Term ID.
   * @param  string $taxonomy The taxonomy.
   * @return void
   */
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

  /**
   * User function to add meta to terms.
   * @param string $term        Term name.
   * @param string $name        Meta name.
   * @param string $label       Form label.
   * @param string $type        Type of input.
   * @param string $description Description of the field.
   * @param array  $options     Options for select/radio/checkbox.
   */
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

  /**
   * Enqueue scripts for image upload
   * @return void
   */
  public static function scripts() {
    wp_enqueue_media();
    wp_enqueue_script( 'aytermmeta', plugins_url( 'assets/script.js', __FILE__ ), array( 'jquery','media-upload','thickbox' ) );
  }

}

AyTermMeta::init();

/**
 * Add meta data field to a term.
 * @param int  $term_id       Term ID.
 * @param string  $meta_key   Metadataname.
 * @param mixed  $meta_value  Metadata value. Must be serializable if non-scalar.
 * @param boolean $unique     Optional. Whether the same key should not be added.
 *                            Default false.
 *
 * @return int|bool Meta ID on success, false on failure.
 */
if(!function_exists('add_term_meta')){

  function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
    return add_metadata('term', $term_id, $meta_key, $meta_value, $unique);
  }

}

/**
 * Update term meta field based on term ID.
 * @param int  $term_id       Term ID.
 * @param string  $meta_key   Metadataname.
 * @param mixed  $meta_value  Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value  Optional. Previous value to check before removing.
 *                            Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
if(!function_exists('update_term_meta')){

  function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
    return update_metadata('term', $term_id, $meta_key, $meta_value, $prev_value);
  }

}

/**
* Retrieve term meta field for a term.
*
* @param int    $term_id Term ID.
* @param string $key     Optional. The meta key to retrieve. By default, returns
*                        data for all keys. Default empty.
* @param bool   $single  Optional. Whether to return a single value. Default false.
* @return mixed Will be an array if $single is false. Will be value of meta data
*               field if $single is true.
*/
if(!function_exists('get_term_meta')){

  function get_term_meta( $term_id, $key = '', $single = false ) {
    return get_metadata('term', $term_id, $key, $single);
  }

}

/**
* Remove metadata matching criteria from a term.
*
* You can match based on the key, or key and value. Removing based on key and
* value, will keep from removing duplicate metadata with the same key. It also
* allows removing all metadata matching key, if needed.
*
* @param int    $term_id    Term ID.
* @param string $meta_key   Metadata name.
* @param mixed  $meta_value Optional. Metadata value. Must be serializable if
*                           non-scalar. Default empty.
* @return bool True on success, false on failure.
*/
if(!function_exists('delete_term_meta')){

  function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
    return delete_metadata('term', $term_id, $meta_key, $meta_value);
  }

}
