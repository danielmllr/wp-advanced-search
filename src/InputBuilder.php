<?php

namespace WPAS;
require_once('StdObject.php');
require_once('TermsWalker.php');

class InputBuilder extends StdObject {

    private function __construct() {}

    /**
     * Initializes and returns an Input object according to the
     * given field type and arguments
     *
     * @param string $input_name
     * @param string $field_type
     * @param array  $args
     * @return object
     */
    public static function make($input_name, $field_type, $args) {
        self::validate($field_type);
        return call_user_func("self::$field_type", $input_name, $args);
    }

    public static function validate($field_type) {
        if (FieldType::isValid($field_type)) return;
        $err_msg = self::validationErrorMsg(
            array('Argument 1 `$field_type` ' .
                'must be a valid FieldType.'));
        throw new \InvalidArgumentException($err_msg);
    }

    /**
     * Generates a search field
     */
    public static function search($input_name, $args) {
        $defaults = array(
            'label' => '',
            'format' => 'text',
            'values' => array()
        );
        $args = self::parseArgs($args, $defaults);
        if (isset($_REQUEST['search_query'])) {
            $args['values'] = $_REQUEST['search_query'];
        }

        return new Input($input_name, $args);
    }

    /**
     * Generates a submit button
     */
    public static function submit($input_name, $args) {
        $defaults = array(
            'values' => array('Search')
        );
        $args = self::parseArgs($args, $defaults);
        $args['format'] = 'submit';
        return new Input($input_name, $args);
    }

    public static function meta_key($input_name, $args) {
        $defaults = array(
            'label' => '',
            'meta_key' => '',
            'format' => 'select',
            'values' => array()
        );
        $args = self::parseArgs($args, $defaults);
        return new Input($input_name, $args);
    }

    public static function order($input_name, $args) {
        $defaults = array(
            'label' => '',
            'format' => 'select',
            'values' => array('ASC' => 'ASC', 'DESC' => 'DESC')
        );

        $args = self::parseArgs($input_name, $defaults);
        return new Input($input_name, $args);
    }

    public static function orderby($input_name, $args) {
        $defaults = array(
                        'label' => '',
                        'format' => 'select',
                        'values' => array(  'ID' => 'ID',
                                            'post_author' => 'Author',
                                            'post_title' => 'Title',
                                            'post_date' => 'Date',
                                            'post_modified' => 'Modified',
                                            'post_parent' => 'Parent ID',
                                            'rand' => 'Random',
                                            'comment_count' => 'Comment Count',
                                            'menu_order' => 'Menu Order' )
                        );

        if (isset($args['orderby_values']) && is_array($args['orderby_values'])) {
            $args['values'] = array(); // orderby_values overrides normal values
            foreach ($args['orderby_values'] as $k=>$v) {
                if (isset($v['label'])) $label = $v['label'];
                else $label = $k;
                $args['values'][$k] = $label; // add to the values array
            }
        }
        $args = self::parseArgs($input_name, $defaults);
        return new Input($input_name, $args);
    }

    public static function author($input_name, $args) {
        $defaults = array(
            'label' => '',
            'format' => 'select',
            'authors' => array()
        );
        $args = self::parseArgs($input_name, $defaults);


        $authors_list = $args['authors'];

        $the_authors_list = array();

        if (count($authors_list) < 1) {
            $authors = get_users(array('who' => 'authors'));
            foreach ($authors as $author) {
                $the_authors_list[$author->ID] = $author->display_name;
            }
        } else {
            foreach ($authors_list as $author) {
                if (get_userdata($author)) {
                    $user = get_userdata($author);
                    $the_authors_list[$author] = $user->display_name;
                }
            }
        }

        $args['values'] = $the_authors_list;

        return new Input($input_name, $args);
    }

    public static function post_type($input_name, $args) {
        $defaults = array(
            'label' => '',
            'format' => 'select',
            'values' => array('post' => 'Post', 'page' => 'Page')
        );
        $args = self::parseArgs($input_name, $defaults);
        $values = $args['values'];

        if (count($values) < 1) {
            $post_types = get_post_types(array('public' => true));
            foreach ( $post_types as $post_type ) {
                $obj = get_post_type_object($post_type);
                $post_type_id = $obj->name;
                $post_type_name = $obj->labels->name;
                $values[$post_type_id] = $post_type_name;
            }
        }

        $args['values'] = $values;
        return new Input($input_name, $args);
    }

    public static function date($input_name, $args) {
        $default_date_type = 'year';
        $defaults = array(
            'label' => '',
            'format' => 'select',
            'date_type' => $default_date_type,
            'date_format' => false,
            'values' => array() );
        $date_type_to_var = array(
            'year' => 'date_y',
            'month' => 'date_m',
            'day' => 'date_d'
        );
        $args = self::parseArgs($input_name, $defaults);

        if (isset($date_type_to_var[$args['date_type']])) {
            $input_name = $date_type_to_var[$args['date_type']];
        } else {
            $args['date_type'] = $default_date_type;
            $input_name = $date_type_to_var[$default_date_type];
        }

        return new Input($input_name, $args);
    }

    public static function html($input_name, $args) {
        $defaults = array(
            'label' => '',
            'values' => array()
        );
        $args = self::parseArgs($input_name, $defaults);
        $args['format'] = 'html';
        return new Input($input_name, $args);
    }

    public static function generic($input_name, $args) {
        return new Input($input_name, $args);
    }

    public static function posts_per_page($input_name, $args) {
        $defaults = array(
            'format' => 'select',
            'values' => array(10 => "10", 25 => "25", 50 => "50")
        );
        $args = self::parseArgs($input_name, $defaults);
        return new Input($input_name, $args);
    }

    public static function taxonomy($input_name, $args) {
        $defaults = array(
            'label' => '',
            'taxonomy' => 'category',
            'format' => 'select',
            'term_format' => 'slug',
            'hide_empty' => false,
            'terms' => array(),
            'nested' => false,
            'term_args' => array()
        );

        $term_defaults = array(
            'hide_empty' => false
        );

        extract(self::parseArgs($args, $defaults));

        //$this->term_formats[$taxonomy] = $term_format;

        $the_tax = get_taxonomy($taxonomy);
        $tax_name = $the_tax->labels->name;
        $tax_slug = $the_tax->name;

        if (!$the_tax) return; // No taxonomy found

        if (isset($term_args) && is_array($term_args)) {
            $term_args = self::parseArgs($term_args, $term_defaults);
        }

        $term_values = array();
        $walker = new TermsWalker(array('taxonomy' => $taxonomy,
                                        'term_format' => $term_format),
                                  $term_args);
        $max_depth = ($nested) ? 0 : -1;

        if (isset($terms) && is_array($terms) && (count($terms) < 1)) {
            // No terms specified; populate with all terms
            if ($nested) {
                $term_values = $walker->build_nested_terms_array( $max_depth );
            } else {
                $term_values = $walker->build_basic_terms_array();
            }

        } else { // Custom term list
            $args['nested'] = false; // Disallow nesting for custom term lists
            foreach ($terms as $term_identifier) {
                $term = get_term_by($term_format, $term_identifier, $taxonomy);
                if ($term) {
                    $term_objects[] = $term;
                }
            }

            foreach ($term_objects as $term) {
                switch($term_format) {
                    case 'id' :
                    case 'ID' :
                        $term_values[$term->term_id] = $term->name;
                        break;
                    case 'Name' :
                    case 'name' :
                        $term_values[$term->name] = $term->name;
                        break;
                    default :
                        $term_values[$term->slug] = $term->name;
                        break;
                }
            }

        }

        if (empty($values)) {
            // Populate with values unless this is a text or textarea field
            if (!($format == 'text' || $format == 'textarea')) {
                $args['values'] = $term_values;
            }
        }

        return new Input($input_name, $args);
    }

    /**
     *  Returns an array of dates in which content has been published
     *
     *  @since 1.0
     */
    function get_dates($date_type = 'year', $format = false) {

        $display_format = "Y";
        $compare_format = "Y";

        if ($date_type == 'month') {
            $display_format = "M Y";
            $compare_format = "Y-m";
        } else if ($date_type == 'day') {
            $display_format = "M j, Y";
            $compare_format = "Y-m-d";
        }

        if ($format) $display_format = $format;

        $post_type = $this->wp_query_args['post_type'];
        $post_status = (!empty($this->wp_query_args['post_status'])) ? $this->wp_query_args['post_status'] : 'publish';
        $posts = get_posts(array('numberposts' => -1, 'post_type' => $post_type, 'post_status' => $post_status));
        $previous_display = "";
        $previous_value = "";
        $count = 0;

        $dates = array();

        foreach($posts as $post) {
            $post_date = strtotime($post->post_date);
            $current_display = date_i18n($display_format, $post_date);
            $current_value = date($compare_format, $post_date);

            if ($previous_value != $current_value) {
                $dates[$current_value] = $current_display;
            }
            $previous_display = $current_display;
            $previous_value = $current_value;

        }
        return $dates;
    }

}