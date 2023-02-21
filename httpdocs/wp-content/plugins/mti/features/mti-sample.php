<?php

/**
 ** MTI Loan Applications
 ** Version 1.0.0
 **/
class MTI_Sample
{

	// Version
	const FEATURE_SLUG		= 'mti_sample';
	const VERSION            = '1.0.0';
	const REVISION           = '0001';

	private static $instance = false;

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
		add_action('init', [$this, 'action_init_register_post_types_and_taxonomies']);

		// load related files

		// scripts and styles
	}

	function enqueue_scripts_and_styles()
	{
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

	public function get_sample_data($category = null)
	{
		$args = array(
			'post_type' => 'sample',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'sample_display_order',
					'type' => 'numeric',
					'compare' => 'EXISTS'
				)
			),
			'meta_key' => 'sample_display_order',
			'orderby' => 'meta_value_num title',
			'order' => 'ASC',
		);

		if ($category)
			$args['category_name'] = $category;

		$args = apply_filters('get_sample_data__args', $args);

		return new WP_Query($args);
	}

	public function get_sample_card_info__filter($post)
	{
		$post_info = get_post_meta($post->ID);

		return array(
			'ID' => $post->ID,
			'img' => get_the_post_thumbnail_url($post, array('450', '450')),
			'title' => $post->post_title,
			'job' => $post_info['sample_job_title'][0] ?? null,
			'desc' => apply_filters('the_content', $post->post_content),
		);
	}
	
} // Class
MTI_Sample::instance();
