<?php

if (!defined('ABSPATH')) die;

add_action('init', function () {
	$cpt_capability_type      = 'post';
	$cpt_show_in_menu = MTI_Settings::SETTING_PAGE_SLUG;  // could also set to false for more control below
	$cpt_template_directory = MTI_DIR . '/templates';

	/******************************************************/

	// cpt set 1
	$cpt_slug     = 'mti_sample';
	$cpt_name     = 'Samples';
	$cpt_singular = 'Sample';

	MTI_Helper::build_cpt_template_path($cpt_template_directory, $cpt_slug);

	register_post_type($cpt_slug, array(
		'labels' => array(
			'name'          => __($cpt_name),
			'singular_name' => __($cpt_singular),
			'add_new_item'  => __('Add New ' . $cpt_singular),
			'edit_item'     => __('Edit ' . $cpt_singular),
			'new_item'      => __('New ' . $cpt_singular),
			'view_item'     => __('View ' . $cpt_singular),
			'search_items'  => __('Search' . $cpt_name),
		),
		'menu_icon'       => 'dashicons-book-alt',
		'public'          => true,
		'capability_type' => $cpt_capability_type,
		'has_archive'     => true,
		'show_ui'         => true,
		'show_in_menu'    => $cpt_show_in_menu,
		'hierarchical'    => false,
		'supports'        => array('editor', 'author', 'custom-fields', 'title', 'thumbnail', 'revisions', 'comments', 'excerpt'),
		'publicly_queryable'  	=> true,
		'exclude_from_search' 	=> false,
		'show_in_rest'			=> false,
		/*
			'rewrite'         => array(
				'with_front'    => false
			),
			'label'               	=> __( $cpt_name ),
			'description'         	=> __( $cpt_name ),
			'show_tagcloud'   => true,
			'show_in_admin_bar'   	=> false,
			'show_admin_column'   	=> true,
			'menu_position'       	=> 5,
			'can_export'          	=> true,
			*/
	));
	
			/******************************************************/

		// tax set 1
		$tax_slug     = 'mti_sample_status';
		$tax_name     = 'Statuses';
		$tax_singular = 'Status';

		register_taxonomy($tax_slug, array($cpt_slug), array(
			'labels' => array(
				'name'              => __($tax_name),
				'singular_name'     => __($tax_singular),
				'search_items'      => __('Search ' . $tax_name),
				'all_items'         => __('All ' . $tax_name),
				'parent_item'       => __('Parent ' . $tax_singular),
				'parent_item_colon' => __('Parent ' . $tax_singular . ':'),
				'edit_item'         => __('Edit ' . $tax_singular),
				'update_item'       => __('Update ' . $tax_singular),
				'add_new_item'      => __('Add New ' . $tax_singular),
				'new_item_name'     => __('New ' . $tax_singular . ' Name'),
				'menu_name'         => __($tax_name),
				'view_item'         => __('View ' . $tax_singular)
			),
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_menu'	  => true,
			'rewrite'           => true,
		));

		/******************************************************/

	unregister_taxonomy_for_object_type('categories', $cpt_slug);
});



// // add submenus into the primary menu 
// add_action('admin_menu', function () {

//     // create my own sub-menu
//     add_submenu_page(
//         MTI_Settings::SETTING_PAGE_SLUG,
//         __('Samples', MTI_SHORT_SLUG),
//         __('Samples', MTI_SHORT_SLUG),
//         'manage_options',
//         'edit.php?post_type=sample',
//         'edit.php?post_type=sample',
//         2
//     );

//     // add a button to add a new sample, similar to the one that would be auto created if not using a submenu
//     add_submenu_page(
//         MTI_Settings::SETTING_PAGE_SLUG,
//         __('New Sample', MTI_SHORT_SLUG),
//         __('New Sample', MTI_SHORT_SLUG),
//         'manage_options',
//         'post-new.php?post_type=sample',
//         'post-new.php?post_type=sample',
//         3
//     );

//     // move default categories menu into a submenu spot
//     add_submenu_page(
//         MTI_Settings::SETTING_PAGE_SLUG,
//         __('Categories', MTI_SHORT_SLUG),
//         __('Categories', MTI_SHORT_SLUG),
//         'manage_options',
//         'edit-tags.php?taxonomy=category&post_type=sample',
//         'edit-tags.php?taxonomy=category&post_type=sample',
//         4
//     );
// });

// // highlight the correct menu items based on the one that is selected.  Without this it won't highlight properly when selected
// add_action('parent_file', function ($parent_file) {
//     global $submenu_file;

//     if (get_current_screen()->action == 'add' && get_current_screen()->id == 'sample') {
//         $submenu_file = 'post-new.php?post_type=sample';
//     } else if (get_current_screen()->post_type == 'sample') {
//         $parent_file = MTI_Settings::SETTING_PAGE_SLUG;

//         if (get_current_screen()->taxonomy == 'category') {
//             $submenu_file = 'edit-tags.php?taxonomy=category&post_type=sample';
//         } else {
//             var_dump(get_current_screen());
//         }
//     }

//     return $parent_file;
// });


// /// Ensure that we are looking for the right archive and single pages
// add_filter(
//     'template_include',
//     function ($template_path) {
//         $slug = 'sample';

//         if (get_post_type() === $slug) {
//             // checks if the file exists in the theme first, otherwise serve the file from this directory (need to update to check for template file)
//             if (is_single())
//                 return ($theme_file = locate_template(array('single-' . $slug . '.php'))) ? $theme_file : parent::TEMPLATE_DIR . '/single-' . $slug . '.php';
//             else if (is_archive())
//                 return ($theme_file = locate_template(array('archive-' . $slug . '.php'))) ? $theme_file : parent::TEMPLATE_DIR . '/archive-' . $slug . '.php';
//         }
//         return $template_path;
//     }
// );
