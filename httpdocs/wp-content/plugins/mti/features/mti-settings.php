<?php

// If this file is called directly, abort.
if (!defined('MTI_DIR')) die;

// the class.
class MTI_Settings
{
    private static $instance = false;
    private static $all_settings = null;
    private $settings_list;
    private $tab_list;

    const SETTING_PAGE_FRIENDLY_TITLE = 'MTI General Configuration';
    const SETTING_PAGE_SLUG = MTI_SLUG . '-admin';
    const SETTING_OBJECT_NAME = MTI_PREFIX . 'settings_object';

    /**
     * Implement singleton
     *
     * @uses self::setup
     * @since 1.0.0
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
    public function __construct()
    {
        require_once(MTI_DIR . '/inc/settings-base.php');

        $this->settings_list = $this->build_settings();
        $this->tab_list = $this->build_setting_sections();

        // link scripts, actions, files, etc.
        $this->link_resources();
    }

    /**
     * Links any necessary files, scripts, etc.
     *
     * @uses wp_enqueue_script, wp_enqueue_style
     * @since 1.0.0
     * @return null
     */
    private function link_resources()
    {
        // Initialize and register settings. 
        add_action('admin_init', [$this, 'configure_setting_page_display']);

        // Add settings page.
        add_action('admin_menu', [$this, 'add_settings_page']);

        // Add settings link to plugins page.
        add_filter('plugin_action_links_' . MTI_BASENAME, [$this, 'add_settings_link']);

        // Scripts for settings page - dynamically show / hide based on checked options.
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts_and_styles']);

        add_filter('custom_menu_order', [$this, 'mti_submenu_order']);
    }

    /**
     * Enqueue scripts and styles
     *
     * @uses wp_enqueue_script, wp_enqueue_style
     * @since 1.0.0
     * @return null
     */
    function enqueue_scripts_and_styles($page)
    {
        if ($page !== $this->plugin_hook_suffix) return;

        wp_enqueue_style(MTI_SHORT_SLUG . '-settings-css', MTI_URL . '/assets/css/settings.css');
        wp_enqueue_script(MTI_SHORT_SLUG . '-settings-js', MTI_URL . '/assets/js/settings.js', ['jquery'], '', true);
    }

    function mti_submenu_order($menu_ord)
    {
        global $submenu;

        // Enable the next line to see all menu orders
        //echo '<div style="padding-left: 10em;"><pre>' . print_r($submenu, true) . '</pre></div>';

        $arr = array();
        $arr[] = $submenu['mti-admin'][2];
        $arr[] = $submenu['mti-admin'][0];
        $arr[] = $submenu['mti-admin'][1];
        $submenu['mti-admin'] = $arr;

        return $menu_ord;
    }

    /**
     * Adds a link to the settings page on the plugins list
     *
     * @since 1.0.0
     * @return array of links to show
     */
    public function add_settings_link($actions)
    {
        $actions[] = '<a href="' . esc_url(admin_url('/admin.php?page=' . SELF::SETTING_PAGE_SLUG)) . '">' . __('Settings') . '</a>';
        $actions[] = '<a href="https://mtisolutions.ca" target="_blank">More From MTI</a>';

        return $actions;
    }

    /**
     * Build all of the setting sections and tabs.
     * The order of these items will determine the tab and section order.
     * If the same tab_title is used for multiple sections, they will print under the same tab.
     * If the section_title is left null, it will not print on the page.
     * If 1+ tab_titles are used and one is left null, the whole section will not print on any page.  Once 1 tab_title is used, it must be used on all or they will be left out.
     * 
     * @since 1.0.0
     * @return array Setting sections object
     */
    private function build_setting_sections()
    {
        return apply_filters('mti_build_settings_sections', array());
    }

    /**
     * Build all of the settings for this plugin as one single array so that it only uses one db record
     * 
     * @since 1.0.0
     * @return array Setting object
     */
    private static function build_settings()
    {
        return apply_filters('mti_build_setting_objects', array());
    }

    /**
     * Registers the settings and setting sections so they can be displayed using the settings API
     * 
     * @uses register_setting, add_settings_section, add_settings_field
     * @since 1.0.0
     * @return null
     */
    public function configure_setting_page_display()
    {
        // Option group (section ID), option name (one row in database with an array for all settings), args (sanitized)
        register_setting(self::SETTING_OBJECT_NAME, self::SETTING_OBJECT_NAME, [$this, 'sanitize']);

        // register all of the tab/section names
        foreach ($this->tab_list as $key => $item)
            add_settings_section($key, $item['section_title'], [$this, 'print_section_info'], SELF::SETTING_PAGE_SLUG);

        // Add setting fields from 'settings_list' array
        foreach ($this->settings_list as $key => $item) {

            // add 'name' and any additional arguments
            $args = ['name' => $key];
            if (isset($item['arg']))
                $args += $item['arg'];

            // if there is a tool tip, add it here so it can be included in output
            if (isset($item['tip'])) {
                $tip = '<span class="help_tip ' . MTI_PREFIX . 'help_tip"><span>' . (isset($item['info']) ? $item['info'] : $item['descr']) . '</span></span>';
            } else {
                $tip = '';
            }

            add_settings_field(
                $key, // ID
                $item['title'] . $tip, // Title and a help tip icon if exists
                [$this, 'print_field'], // Callback
                SELF::SETTING_PAGE_SLUG, // Page
                $item['tab'], // Section ID
                $args // Optional args	
            );
        }
    }

    /**
     * Adds the settings page to the admin menu
     * 
     * @uses add_submenu_page (or add_options_page)
     * @since 1.0.0
     * @return null
     */
    public function add_settings_page()
    {
        $this->plugin_hook_suffix = add_menu_page(
            __('MTI Custom Configuration', MTI_SHORT_SLUG),
            __('MTI Admin', MTI_SHORT_SLUG),
            'manage_options',
            self::SETTING_PAGE_SLUG,
            [$this, 'create_settings_page'],
            'dashicons-performance',
            10
        );

        // If this page was to be under "Settings" we would use add_options_page rather than add_submenu_page
        $this->plugin_hook_suffix = add_submenu_page(
            self::SETTING_PAGE_SLUG,
            __('MTI Custom Configuration', MTI_SHORT_SLUG),
            __('MTI Admin', MTI_SHORT_SLUG),
            'manage_options',
            self::SETTING_PAGE_SLUG,
            [$this, 'create_settings_page']
        );

        // // If this page was to be under "Settings" we would use add_options_page rather than add_submenu_page
        // $this->plugin_hook_suffix = add_submenu_page(
        //     'woocommerce',
        //     'MTI WooCommerce Controls',
        //     $this->settings_menu_name,
        //     'manage_options',
        //     $this->settings_page_name,
        //     [$this, 'create_settings_page']
        // );
    }


    /**
     * Get the option that is saved or the default.
     *
     * @param string $index. The option we want to get.
     * @since 1.0.0
     */
    public static function get_settings($index = false, $strip_prefix = false)
    {
        if (self::$all_settings == null) {
            $default_settings = self::build_settings();
            self::$all_settings = $saved_settings = get_option(self::SETTING_OBJECT_NAME);

            if ($saved_settings == false)
                self::$all_settings = $saved_settings = array();

            // if already exists - update anything needed for version control, deprecation, etc.
            // if (self::$all_settings !== false) { }

            // likely needed: create any missing entries that have been recently added to the software
            $new_settings = array_diff_key($default_settings, self::$all_settings);
            foreach ($new_settings as $key => $val)
                self::$all_settings[$key] = $val['val'];

            // if wanted: remove any entries that are no longer used... but beware that it will remove anything that is not loaded in with settings... so it will delete settings that may be temporarily removed or not loaded because they are set to only load on admin pages
            // $old_settings = array_diff_key(self::$all_settings, $default_settings);
            // foreach ($old_settings as $key => $val)
            //     unset(self::$all_settings[$key]);

            // if any changes, save it back to the db for next time
            if ($saved_settings !== self::$all_settings)
                update_option(self::SETTING_OBJECT_NAME, self::$all_settings);
        }

        if ($index)
            return isset(self::$all_settings[$index]) ? self::$all_settings[$index] : null;
        else if ($strip_prefix === false)
            return self::$all_settings;
        else {
            if ($strip_prefix === true) $strip_prefix = MTI_PREFIX;

            $replacedKeys = str_replace($strip_prefix, "", array_keys(self::$all_settings));
            return array_combine($replacedKeys, self::$all_settings);
        }
    }

    /**
     * Call back required to print the settings page
     * 
     * @uses /templates/admin/settings.php
     * @since 1.0.0
     * @return null
     */
    public function create_settings_page()
    {
        $this->setting_object = self::get_settings();

        require(MTI_DIR . '/templates/admin/settings.php');
    }

    /**
     * Sanitize callback used for settings entries
     *
     * @param array $input Contains all settings fields as array keys
     * @since 1.0.0
     * @return mixed sanitized input
     */
    public function sanitize($input)
    {
        $new_input = self::get_settings();
        $arr = $this->settings_list;

        foreach ($arr as $key => $item) {
            switch ($item['type']) {
                case 'checkbox':
                    if (isset($input[$key])) {
                        $new_input[$key] = ($input[$key] == 1 ? 1 : 0);
                    } else {
                        $new_input[$key] = 0;
                    }
                    break;
                case 'text':
                case 'textarea':
                case 'select':
                    if (isset($input[$key]))
                        $new_input[$key] = sanitize_text_field($input[$key]);
                    break;
                case 'number':
                    if (isset($input[$key]))
                        $new_input[$key] = absint($input[$key]);
                    break;
                case 'color':
                    if (isset($input[$key]))
                        $new_input[$key] = sanitize_hex_color($input[$key]);
                    break;
            }
        }

        return $new_input;
    }


    /**
     * When adding a section title to the settings page, it needs a call back, this is a blank default one that can be used.
     * You could print additional text here to output below the heading if wanted
     */
    public function print_section_info()
    {
        return;
    }


    /**
     * While adding all setting fields a call back needs to be referenced to print them back out.
     * This is the callback that is used to format the settings page.
     *
     * @param array $args Contains all the optional arguments passed into the callback
     */
    public function print_field(array $args)
    {
        $field = $args['name'];

        switch ($this->settings_list[$field]['type']) {

            case 'checkbox':

                $fieldset = '<fieldset><label><input id="%1$s" type="checkbox" name="%2$s[%1$s]" value="1" %3$s />%4$s</label></fieldset>';

                printf(
                    $fieldset,
                    esc_attr($field),
                    self::SETTING_OBJECT_NAME,
                    checked($this->setting_object[$field], 1, false),
                    //                    isset($this->setting_object[$field]) && (1 == $this->setting_object[$field])  ? 'checked="checked" ' : '',
                    $this->settings_list[$field]['descr']
                );
                break;

            case 'select':

                $options = $this->settings_list[$field]['options'];
                foreach ($options as $item) {
                    $items[$item] =  $item;
                }

                printf(
                    '<select id="%1$s" name="%2$s[%1$s]">',
                    esc_attr($field),
                    self::SETTING_OBJECT_NAME
                );

                foreach ($items as $value => $option) {
                    printf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        esc_attr($value),
                        selected($value, $this->setting_object[$field], false),
                        esc_html($option)
                    );
                }

                printf(
                    '</select>'
                );
                break;

            case 'text':
            case 'number':
            case 'textarea':
            case 'color':

                switch ($this->settings_list[$field]['type']) {
                    case 'text':
                        $fieldset = '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />';
                        break;
                    case 'number':
                        $fieldset = '<input type="number" id="%1$s" name="%2$s[%1$s]" value="%3$s" />';
                        break;
                    case 'textarea':
                        $fieldset = '<textarea id="%1$s" name="%2$s[%1$s]">%3$s</textarea>';
                        break;
                    case 'color':
                        $fieldset = '<input type="color" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="mti-cstm-color-field" data-default-color="#000000"/> ';
                }

                $fieldset .= isset($this->settings_list[$field]['descr']) ? '<p class="description">%4$s</p>' : '';
                $descr = isset($this->settings_list[$field]['descr']) ? $this->settings_list[$field]['descr'] : '';

                printf(
                    $fieldset,
                    esc_attr($field),
                    self::SETTING_OBJECT_NAME,
                    isset($this->setting_object[$field]) ? esc_attr($this->setting_object[$field]) : '',
                    $descr
                );
                break;
        }
    }


    /**
     * personal variation of the do_settings_sections function to allow specific sections to be printed.
     * Code is based on WP's standard function so may need to be updated over time
     * 
     * @param string $page The slug name of the page whose settins sections you want to print
     * @param mixed $sections The slug name(s) of the sections to include either in an array, comma seperated list or single name
     * 
     * @since 1.0.0
     * @return null
     */
    function do_settings_sections($page, $sections = null)
    {
        global $wp_settings_sections, $wp_settings_fields;

        if (!isset($wp_settings_sections[$page])) return;

        // convert to array where needed
        if ($sections && !is_array($sections))
            $sections = array_map('trim', explode(',', $sections));

        foreach ((array) $wp_settings_sections[$page] as $section) {

            // if sections was provided but is not found, skip
            if ($sections && !in_array($section['id'], $sections)) continue;

            if ($section['title']) {
                echo "<h2>{$section['title']}</h2>\n";
            }

            if ($section['callback']) {
                call_user_func($section['callback'], $section);
            }

            if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
                continue;
            }
            echo '<table class="form-table" role="presentation">';
            do_settings_fields($page, $section['id']);
            echo '</table>';
        }
    }
}
MTI_Settings::instance();
