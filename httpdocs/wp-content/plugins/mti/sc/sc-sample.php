<?php

if (!defined('ABSPATH')) die;

// do_shortcode('[sc_get_solutions]');

add_shortcode('sc_get_all_solutions_by_type', 'get_all_mti_solutions_by_type');
function get_all_mti_solutions_by_type($atts)
{
    ob_start();
    $other_class = $atts['classes'] ?? '';

    $sol = MTI_Solution::instance();
    $qry = $sol->get_data(true);
    $sols = apply_filters('get_solution_array_by_type', $qry->posts);

    if ($sols && count($sols) > 0) {
        mti_get_template_part('solution-list-by-type', null, MTI_Solution::TEMPLATE_DIR, array('sorted_array' => $sols, 'other_class' => $other_class)); //, 'other_class' => $other_class));
    }
    return ob_get_clean();
}
