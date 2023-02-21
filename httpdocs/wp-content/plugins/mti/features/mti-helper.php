<?php

if (!defined('ABSPATH')) die;


/**
 ** MTI Helper functionality
 ** Version 1.0.0
 **/
class MTI_Helper
{

    // Version
    const FEATURE_SLUG        = 'mti_helper';
    const VERSION            = '1.0.0';
    const REVISION           = '0001';

    private static $instance = false;
    private $version         = false;

    /**
     * Implement singleton
     *
     * @uses self::setup
     * @return self
     */
    public static function instance()
    {
        if (!is_a(self::$instance, __CLASS__))
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * Clone
     *
     * @since 1.0.0
     */
    private function __clone()
    {
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        // Initilization
        add_action('init', [$this, 'action_init_check_version']);
        add_filter('woocommerce_locate_template', [$this, 'woo_adon_plugin_template'], 1, 3);


        // load related files

        // scripts and styles
    }


    /**
     * Version Check	
     *
     * @since 1.0.0
     */
    function action_init_check_version()
    {
        if ($this->check_version(Self::FEATURE_SLUG, Self::VERSION)) {
            // Do remaining version upgrade tasks here
        }
    }

    function enqueue_scripts_and_styles()
    {
    }


    // minify css/js on the fly for snippets in code
    public static function minify($input)
    {
        $output = $input;
        // Remove whitespace
        $output = preg_replace('/\s*([{}|:;,])\s+/', '$1', $output);
        // Remove trailing whitespace at the start
        $output = preg_replace('/\s\s+(.*)/', '$1', $output);

        // Remove comments
        // $output = preg_replace('#/\*.*?\*/#s', '', $output);
        $output = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $output);
        return $output;
    }

    /**
     * Retrieves a post_meta field from the database and compares it to the value held within the same variable name of a $_POST object of a submitted form.
     * 
     * @param int $post_id The ID of the post that is being saved/comapared to
     * @param mixed $post_meta_names A string, comma list or array of string names of the post_meta fields  to be updated from the _POST object
     * 
     * @uses $_POST, get_post_meta, update_post_meta, sanitize_text_fields
     * @since 1.0.0
     * @return null
     */
    public static function compare_and_save_post_meta($post_id, $post_meta_names)
    {
        if (!isset($post_id) || !is_numeric($post_id) || $post_meta_names == null) return;

        if (!is_array($post_meta_names))
            $post_meta_names = array_map('trim', explode(',', $post_meta_names));

        foreach ($post_meta_names as $meta) {
            $orig = trim(get_post_meta($post_id, $meta, true));
            $new = sanitize_text_field($_POST[$meta]);

            if ($orig != $new)
                update_post_meta($post_id, $meta, $new);
        }
    }


    public static function enqueue_inline_css($css, $handle = null)
    {
        if (empty($css)) return;

        if (empty($handle))
            $handle = 'mti_inline_css';

        wp_register_style($handle, false);
        wp_enqueue_style($handle);
        wp_add_inline_style($handle, $css);
    }



    public static function get_post_meta($post_id, $key = null, $single = false, $default = null)
    {
        // return standard call for all meta data (no need to use this function)
        if ($key == null)
            return get_post_meta($post_id, $key, $single);

        // not an array but has comma's... make an array
        if (!is_array($key) && strpos($key, ',') !== false)
            $key = array_map('trim', explode(',', $key));

        if (is_array($key)) {
            $default_is_array = is_array($default);

            foreach ($key as $k) {
                $tmp = get_post_meta($post_id, $k, $single);

                if (empty($tmp)) $ret_val[$k] = $default_is_array ? $default[$k] : $default;
                else {
                    $ret_val[$k] = $tmp;
                }
            }
            return $ret_val;
        } else {
            $ret_val = get_post_meta($post_id, $key, $single);

            if (empty($ret_val)) return $default;
            else return $ret_val;
        }
    }




    // TODO: Figure out why this isn't working when looking at products/shop pages
    // prevent product or product_category pages
    // add_action('template_redirect', 'mti_redirect_home');
    public static function redirect_home()
    {
        // Only on product category archive pages (redirect to shop)
        if (is_singular('rcblocks') || is_archive('rcblocks')) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }

    /**
     * Checks environment
     * @return string - environment name
     * @author Adam Smith
     */
    // TODO: TEST THIS FUNCTION... UNTESTED
    public static function check_environment($domain)
    {
        $server = $_SERVER['SERVER_NAME'];
        $env = 'unknown';

        $matches = null;
        $re = '/^((staging\.)|(dev\.)|(development\.))/m';
        preg_match_all($re, $server, $matches);

        if ($server === $domain) {
            $env = 'production';
        } elseif ($matches[0] == 'staging.') {
            $env = 'staging';
        } elseif ($matches[0] == 'dev.' || $matches[0] == 'development.') {
            $env = 'development';
        }

        return $env;
    }


    public static function get_query_string_variable($name, $default = null)
    {
        if (Self::mti_isset($_REQUEST[$name]))
            return $_REQUEST[$name];
        else
            return $default;
    }

    public static function validate_date($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    public static function user_logged_in($capabilities = null, &$return_roles = null)
    {
        /// check if user is logged in 
        if (!is_user_logged_in()) {
            $return_roles = null;
            return false;
        }

        /// return all of the roles that the user is in 
        if ($return_roles)
            $return_roles = wp_get_current_user()->roles;

        /// check if user is a part of a role 
        if (Self::mti_isset($capabilities)) {
            if (!is_array($capabilities)) {
                $capabilities = explode(',', $capabilities);
            }

            /// check for each capability and return false if it fails ANY of them 
            foreach ($capabilities as $capability) {
                if (current_user_can($capability))
                    $auth_flag = true;
            }
            if (!$auth_flag)
                return false;
        }

        return true;
    }

    public static function mti_isset($var, $check_isset = true, $check_empty_or_false = true, $check_empty_string = true)
    {
        if (!$check_empty_or_false && !$check_empty_string && !$check_isset)
            return new WP_Error('No validation was performed on the variable.');

        if ($check_isset && !isset($var))
            return false;

        if ($check_empty_or_false && empty($var))
            return false;

        if ($check_empty_string && $var === '')
            return false;

        return true;
    }


    // Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
    public static function remove_filters_with_method_name($hook_name = '', $method_name = '', $priority = 0)
    {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if (!isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]))
            return false;

        // Loop on filters registered
        foreach ((array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            // Test if filter is an array ! (always for class/method)
            if (isset($filter_array['function']) && is_array($filter_array['function'])) {
                // Test if object is a class and method is equal to param !
                if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name) {
                    unset($wp_filter[$hook_name][$priority][$unique_id]);
                }
            }
        }
        return false;
    }

    // Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
    public static function remove_filters_for_anonymous_class($hook_name = '', $class_name = '', $method_name = '', $priority = 0)
    {
        global $wp_filter;
        // Take only filters on right hook name and priority
        if (!isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]))
            return false;

        // Loop on filters registered
        foreach ((array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            // Test if filter is an array ! (always for class/method)
            if (isset($filter_array['function']) && is_array($filter_array['function'])) {
                // Test if object is a class, class and method is equal to param !
                if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name) {
                    unset($wp_filter[$hook_name][$priority][$unique_id]);
                }
            }
        }
        return false;
    }


    // function to bring in some variables and use them with the building of a cpt template location
    // https://wordpress.stackexchange.com/questions/290030/passing-additional-parameters-to-add-filter-callable
    public static function build_cpt_template_path($template_path, $post_type_slug)
    {
        $args = array(
            'template_path' => $template_path,
            'post_type_slug' => $post_type_slug
        );

        add_filter(
            'template_include',
            function ($template_path) use ($args) {
                if (get_post_type() === $args['post_type_slug']) {
                    if (is_single()) {
                        // checks if the file exists in the theme first, otherwise serve the file from this directory
                        if ($theme_file = locate_template(array('single-' . $args['post_type_slug'] . '.php'))) {
                            $template_path = $theme_file;
                        } else {
                            $template_path = $args['template_path'] . '/single-' . $args['post_type_slug'] . '.php';
                        }
                    } else if (is_archive()) {
                        if ($theme_file = locate_template(array('archive-' . $args['post_type_slug'] . '.php'))) {
                            $template_path = $theme_file;
                        } else {
                            $template_path = $args['template_path'] . '/archive-' . $args['post_type_slug'] . '.php';
                        }
                    }
                }
                return $template_path;
            }
        );
    }


    /**
     * Ability to override the woocommerce templates by simply adding them to the plugin templates folder
     */
    // added with filter above >> add_filter('woocommerce_locate_template', 'woo_adon_plugin_template', 1, 3);
    public static function woo_adon_plugin_template($template, $template_name, $template_path)
    {
        global $woocommerce;
        $_template = $template;
        if (!$template_path)
            $template_path = $woocommerce->template_url;

        $plugin_path = MTI_DIR . '/templates/woocommerce/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );

        if (!$template && file_exists($plugin_path . $template_name))
            $template = $plugin_path . $template_name;

        if (!$template)
            $template = $_template;

        return $template;
    }

    /**
     * Returns true if the version has changed or false if it is the same as the saved option. Updates the DB based on flag. * 
     *
     * @since 1.0.0
     */
    public static function check_version($feature_slug, $current_version, $update_db = true)
    {
        global $wp_version;
        $version_option_name = $feature_slug . '_version';

        // Version Check
        if ($saved_version = get_option($version_option_name, false)) {

            if ($saved_version < $current_version) {
                if ($update_db) update_option($version_option_name, $current_version);

                return true; // do remaining version upgrade tasks
            }
        } else
            add_option($version_option_name, $current_version);


        return false;
    }


    public static function get_mti_setting($index = null)
    {
        if (class_exists('mti_settings'))
            return mti_settings::get_settings($index);

        $setting_object_name = MTI_PREFIX . 'settings_object';

        $settings = get_option($setting_object_name);

        if ($index)
            return isset($settings[$index]) ? $settings[$index] : null;
        else
            return $settings;
    }


    public static function does_plugin_exist($plugin_name, $class_name = null)
    {
        return class_exists($class_name) || (get_option('active_plugins') && in_array($plugin_name, get_option('active_plugins'))) ||
            (get_site_option('active_sitewide_plugins') && array_key_exists($plugin_name, get_site_option('active_sitewide_plugins')));
    }



    public static function get_template_part($slug, $name = null, $plugin_template_path = null, $args = array())
    {
        $name      = (string) $name;
        $template = null;

        if ($name !== '') {
            $templates = array("{$slug}-{$name}.php", $plugin_template_path . "/{$slug}-{$name}.php");
            $template = locate_template($templates, false, true, $args);
        }

        if (!$template) {
            $templates = array("{$slug}.php", $plugin_template_path . "/{$slug}.php");
            $template = locate_template($templates, false, true, $args);
        }

        if (!$template) {
            if ($name !== '') $fallback = $plugin_template_path . "/{$slug}-{$name}.php";
            else $fallback = $plugin_template_path . "/{$slug}.php";
            $template = file_exists($fallback) ? $fallback : '';
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('mti_get_template_part', $template, $slug, $name);

        if ($template) {
            load_template($template, false, $args);
        }
    }

    public static function locate_template($slug, $extension = null, $plugin_template_path = null, $args = null)
    {
        $extension = (string) $extension;
        $template = null;

        if ($extension !== '') {
            $templates = array("{$slug}-{$extension}.php", $plugin_template_path . "/{$slug}-{$extension}.php");
            $template = locate_template($templates, false, true, $args);
        }

        if (!$template) {
            $templates = array("{$slug}.php", $plugin_template_path . "/{$slug}.php");
            $template = locate_template($templates, false, true, $args);
        }

        if (!$template) {
            if ($extension !== '') $fallback = $plugin_template_path . "/{$slug}-{$extension}.php";
            else $fallback = $plugin_template_path . "/{$slug}.php";
            $template = file_exists($fallback) ? $fallback : '';
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('mti_locate_template_part', $template, $slug, $extension);

        return $template;
    }
}
