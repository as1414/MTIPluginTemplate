<?php


if (!defined('ABSPATH')) die;


add_action('wp_ajax_qty_cart', 'ajax_qty_cart');
add_action('wp_ajax_nopriv_qty_cart', 'ajax_qty_cart');
function ajax_qty_cart()
{
    // Set item key as the hash found in input.qty's name
    $product_id = $_POST['product_id'];

    // Loop over $cart items to get the id from the product
    foreach (WC()->cart->get_cart() as $item_key => $cart_item) {
        if ($product_id == $cart_item['product_id']) {
            $cart_item_key = $item_key;
            break;
        }
    }

    if (!isset($cart_item_key)) {
        do_action('wp_ajax_woocommerce_ajax_add_to_cart');
    }

    // Get the array of values owned by the product we're updating
    $mti_product_values = WC()->cart->get_cart_item($cart_item_key);


    // Get the quantity of the item in the cart
    $mti_product_quantity = apply_filters('woocommerce_stock_amount_cart_item', apply_filters('woocommerce_stock_amount', preg_replace("/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT))), $cart_item_key);

    // Update cart validation
    $passed_validation  = apply_filters('woocommerce_update_cart_validation', true, $cart_item_key, $mti_product_values, $mti_product_quantity);

    // Update the quantity of the item in the cart
    if ($passed_validation) {
        WC()->cart->set_quantity($cart_item_key, $mti_product_quantity, true);
    }

    // Refresh the page
    echo do_shortcode('[woocommerce_cart]');

    die();
}

add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
function woocommerce_ajax_add_to_cart()
{
    if (!isset($_POST['product_id']) || $_POST['product_id'] == 0) die();

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);

    $variation_id = absint($_POST['variation_id']) ?? 0;
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

    $product_status = get_post_status($product_id);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
        }
        WC_AJAX::get_refreshed_fragments();
    } else {

        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
        );

        echo wp_send_json($data);
    }

    wp_die();
}
