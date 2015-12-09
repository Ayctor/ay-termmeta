=== AY Term Meta ===
Contributors: gastonpowered
Tags: term, terms, category, categories, tag, tags, taxonomy, taxonomies, meta, metas, metadata
Requires at least: 3.5
Tested up to: 4.4
Stable tag: 0.9.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add some meta to your terms like tags, categories or custom taxonomies

== Description ==

Activate the plugin and add some code to your functions.php

Basic function :

- AyTermMeta::addMeta($term, $name, $label, $type = 'input', $description = '', $options = array()) {} Generate all meta management in WordPress Admin

Advanced functions :

You can use term_meta functions similar to post_meta function to add / update / get / delete metas

- function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {}
- function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {}
- function get_term_meta( $term_id, $key = '', $single = false ) {}
- function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {}

== Installation ==

Download the plugin and put it in /wp-content/plugins/. Then activate it and add some code in your theme.

== Screenshots ==

1. The add screen
2. The edit screen
