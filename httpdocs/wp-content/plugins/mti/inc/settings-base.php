<?php

if (!defined('ABSPATH')) die;


// add sections and tabs to mti settings pages
add_filter('mti_build_settings_sections', function ($sections) {

    $sections['configuration'] = ['section_title' => null, 'tab_title' => __('General', MTI_SLUG)];

    return $sections;
});

// add setting objects to tabs and pages
add_filter('mti_build_setting_objects', function ($settings) {

    $base_settings = array(

        'mti_install_child'             => [
            'val' => false,
            'title' => __('Install MTI Child Functionality', MTI_SLUG),
            'type' => 'checkbox',
            'tab' => 'configuration',
            'descr' => __('Load MTI Child functionality.', MTI_SLUG),
            'info' => __('Turn this setting on to enable the functionality.', MTI_SLUG),
            'tip' => true
        ],
        'mti_install_session'             => [
            'val' => false,
            'title' => __('Install MTI Session Functionality', MTI_SLUG),
            'type' => 'checkbox',
            'tab' => 'configuration',
            'descr' => __('Load MTI Session functionality.', MTI_SLUG),
            'info' => __('Turn this setting on to enable the functionality.', MTI_SLUG),
            'tip' => true
        ],
    );

    return array_merge($settings, $base_settings);
});
