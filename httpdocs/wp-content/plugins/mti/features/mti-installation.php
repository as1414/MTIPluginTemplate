<?php

if (!defined('MTI_BASENAME')) die;


/**
 ** MTI Helper functionality
 ** Version 1.0.0
 **/
class MTI_Installation
{
    // Version
    const FEATURE_SLUG        = 'mti_installation';
    const VERSION            = '1.0.0';
    const REVISION           = '0001';

    private static $instance = false;
    private static $all_notices = null;

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
        register_activation_hook(MTI_BASENAME, [$this, 'mti_activate']);
        register_uninstall_hook(MTI_BASENAME, 'mti_uninstall');

        // load related files

        if (is_admin()) {
            // admin Notices, scripts and styles
            add_action('admin_notices', [$this, 'create_notices']);
            add_action('wp_ajax_mti_notice_dismiss', [$this, 'mti_notice_dismiss']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts_and_styles']);
        }
    }

    /**
     * Version Check	
     *
     * @since 1.0.0
     */
    function action_init_check_version()
    {
        if (MTI_Helper::check_version(Self::FEATURE_SLUG, Self::VERSION)) {
            // Do remaining version upgrade tasks here
        }
    }

    function enqueue_admin_scripts_and_styles()
    {
        wp_enqueue_style(MTI_SLUG . '-installation-css', MTI_URL . '/assets/css/installation.css');
        wp_enqueue_script(MTI_SLUG . '-installation-js', MTI_URL . '/assets/js/installation.js', ['jquery'], '', true);
    }


    function mti_activate()
    {
        // Prevent plugin activation if the minimum PHP version requirement is not met.
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            deactivate_plugins(MTI_BASENAME);
            $msg = '<p><strong>MTI WooCommerce Quantity Controls</strong> requires PHP version 5.4 or greater. Your server runs ' . PHP_VERSION . '.</p>';
            wp_die($msg, 'Plugin Activation Error', array('response' => 200, 'back_link' => TRUE));
        }

        // Store time of first plugin activation (add_option does nothing if the option already exists).
        add_option('mti_first_activate', time());
    }


    static function mti_uninstall()
    {
        // If not called by WordPress, die.
        if (!defined('WP_UNINSTALL_PLUGIN')) die;

        // Single site options.
        delete_option('mti_setting_object');
        delete_option('mti_first_activate');
        delete_option('mti_notices_object');
    }


    public function create_notices()
    {
        $notices = $this->get_notices();

        foreach ($notices as $key => $notice) {

            if ($notice['is_dismissed']) return false;

            $now = time();
            $activate_time = get_option('mti_first_activate', false);

            if (!isset($activate_time) || $activate_time == false || ($now - $activate_time) / 86400 < $notice['activate_days']) return false;

            require(MTI_DIR . $notice['template']);
        }
    }

    public function mti_notice_dismiss()
    {
        update_option('mti_dismiss_notice', true);
        wp_die();
    }

    private function build_notices()
    {
        return [
            'feedback_notice1'  => [
                'is_dismissed'      => false,
                'activate_days'     => 2,
                'template'          => __('/templates/admin/notice.php', MTI_SLUG),
                'html'              => __('', MTI_SLUG)
            ]
        ];
    }


    /**
     * Get the notice that is saved or the default.
     *
     * @param string $index. The notice we want to get.
     * @since 1.0.0
     */
    private function get_notices($index = false)
    {
        if (self::$all_notices == null) {
            $default_notices = $this->build_notices();
            $orig_saved_notices = $saved_notices = get_option('mti_notices_object');

            // if saved exists - update anything needed for version control, deprecation, etc.
            // if ($saved_notices !== false) { }

            // overwrite any keys in the new/default settings that existed in the old/saved settings
            self::$all_notices = (isset($saved_notices) && is_array($saved_notices) && $default_notices !== $saved_notices) ? array_merge($default_notices, $saved_notices) : $default_notices;

            // if any changes, save it back to the db for next time
            if ($orig_saved_notices !== self::$all_notices) {
                update_option('mti_notices_object', self::$all_notices);
            };
        }

        if ($index)
            return isset(self::$all_notices[$index]) ? self::$all_notices[$index] : null;
        else
            return self::$all_notices;
    }
}
MTI_Installation::instance();
