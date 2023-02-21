<?php

if (!defined('ABSPATH')) exit;

/* enqueue scripts and style from parent theme */

function hello_child_enqueue()
{
    wp_enqueue_style('child-style', get_stylesheet_uri()); //, array('hello_elementor_enqueue_style'), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'hello_child_enqueue');
