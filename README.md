# Term Meta

## Description

Add meta to terms in Wordpress

## Usage

Activate the plugin and add some code to your `functions.php`

Use the function `AyTermMeta::addMeta` to add a meta to a term

```php
/**
 * User function to add meta to terms.
 * @param string $term        Term name.
 * @param string $name        Meta name.
 * @param string $label       Form label.
 * @param string $type        Type of input.
 * @param string $description Description of the field.
 * @param array  $options     Options for select/radio/checkbox.
 */
  addMeta($term, $name, $label, $type = 'input', $description = '', $options = array()) {}
```

### Basic example

#### Add an excerpt to a tag

```php
AyTermMeta::addMeta('category', 'excerpt', 'Excerpt', 'input', 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme.');
```

#### All term types

This code

```php
AyTermMeta::addMeta('category', 'tag_excerpt', 'Excerpt', 'input', 'Excerpt description');

$radio_options = array(
  'male' => 'Male',
  'female' => 'Female',
  'unknown' => 'Unknown'
);
AyTermMeta::addMeta('category', 'gender', 'Gender', 'radio', 'Radio description', $radio_options);

$select_options = array(
  'africa' => 'Africa',
  'america' => 'america',
  'asia' => 'Asia',
  'europe' => 'Europe',
  'oceania' => 'Oceania'
);
AyTermMeta::addMeta('category', 'continent', 'Continent', 'select', 'Select description', $select_options);

$checkbox_options = array(
  'patatoes' => 'patatoes',
  'salad' => 'salad',
  'tomatoes' => 'tomatoes'
);
AyTermMeta::addMeta('category', 'food', 'Food', 'checkbox', 'Checkbox description', $checkbox_options);

AyTermMeta::addMeta('category', 'bio', 'Bio', 'textarea', 'Textarea description');

AyTermMeta::addMeta('category', 'myimage', 'My Image', 'file', 'Image description');
```

will generate these views

![add form](http://ayctor.github.io/ay-termmeta/screenshot-1.png "add form")
Add form

![edit form](http://ayctor.github.io/ay-termmeta/screenshot-2.png "edit form")
Edit form

### Advanced usage

You can use `term_meta` functions similar to `post_meta` function to add / update / get / delete metas

```php
function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {}

function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {}

function get_term_meta( $term_id, $key = '', $single = false ) {}

function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {}
```

The documentation is the same as `post_meta`. You can find it here :

- [add_post_meta](https://codex.wordpress.org/Function_Reference/add_post_meta)
- [update_post_meta](https://codex.wordpress.org/Function_Reference/update_post_meta)
- [get_post_meta](https://codex.wordpress.org/Function_Reference/get_post_meta)
- [delete_post_meta](https://codex.wordpress.org/Function_Reference/delete_post_meta)

## TODO

- ~~Add textarea input type~~
- ~~Add image input type~~
- Show the meta in the table listing
- Add term thumbnail
