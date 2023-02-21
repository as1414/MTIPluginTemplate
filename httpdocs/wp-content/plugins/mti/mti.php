<?php
/*
 * Plugin Name: MTI Custom Plugin
 * Description: Custom code provided by MTI
 * Version: 1.0.0
 * Author: MTI Business Solutions
 * WC requires at least: 3.0.0
 * WC tested up to: 4.4.1
 */

if (!defined('ABSPATH')) die;

// plugin constants
define('MTI_DIR', __DIR__);
define('MTI_URL', plugins_url(null, __FILE__));
define('MTI_SLUG', 'mti');
define('MTI_SHORT_SLUG', 'mti');
define('MTI_PREFIX', 'mti_');
define('MTI_BASENAME', plugin_basename(__FILE__));

// initial requirements
require_once(MTI_DIR . '/features/mti-helper.php');
require_once(MTI_DIR . '/features/mti-installation.php');
require_once(MTI_DIR . '/features/mti-settings.php');

// common features

/// TODO: add features, logic here and settings so doesn't need to always run code if not in use
if (MTI_Settings::get_settings('mti_install_child') === 1) require_once(MTI_DIR . '/features/mti-child.php');
if (MTI_Settings::get_settings('mti_install_session') === 1) require_once(MTI_DIR . '/features/mti-session.php');

// ajax features
//require_once(MTI_DIR . '/inc/sample-ajax.php');

// admin or front-end features
if (is_admin()) {
    MTI_Settings::instance();
} else {  // front-end features

}


// the following require WC to be installed to work properly
if (MTI_Helper::does_plugin_exist('woocommerce/woocommerce.php')) {

    //common


    //front end
    if (!is_admin()) {
    }
}
