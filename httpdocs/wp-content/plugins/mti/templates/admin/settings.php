<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) die;

$tabs = array();
foreach ($this->tab_list as $key => $item)
    if (isset($item['tab_title']) && !in_array($item['tab_title'], $tabs))
        $tabs[$key] = $item['tab_title'];

//Get the active tab from the $_GET param
$default_tab = array_key_first($tabs);
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>

<!-- Our admin page content should all be inside .wrap -->
<div class="wrap <?php echo self::SETTING_PAGE_SLUG; ?>_wrap">
    <div class="mti_admin_links">
        <a href="https://wordpress.org/support/plugin/mti-wc-quantity-controls/" target="_blank"><?php esc_html_e('Support & Suggestions', MTI_SLUG); ?></a>
        |
        <a href="https://wordpress.org/support/plugin/mti-wc-quantity-controls/reviews/?rate=5#new-post" target="_blank"><?php esc_html_e('Rate this plugin', MTI_SLUG); ?></a>
    </div>
    <h1><?php echo self::SETTING_PAGE_FRIENDLY_TITLE; ?></h1>

    <!-- Here are our tabs -->
    <?php if (sizeof($tabs) > 0) : ?>
        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $slug => $title)
                printf('<a href="?page=%1$s&tab=%2$s" class="nav-tab mti-tab %2$s-tab %4$s">%3$s</a>', self::SETTING_PAGE_SLUG, $slug, $title, $tab === $slug ? ' nav-tab-active' : '');
            ?>
        </nav>

        <div class="<?php echo $tab; ?> tab-content">
            <?php switch ($tab):
                case 'custom_layout':
                    echo '<p>custom html will go here</p>';
                    break;
                case 'wc_admin':
            ?>
                    <!-- Our admin page content should all be inside .wrap -->
                    <div class="wrap">
                        <h1>MTI Barnet Admin</h1>
                        <div class=" tab-content">
                            <div class="mti-nbh-admin-holder">
                                <div class="heading">Orders</div>
                                <div><input type="text" id="orderId" placeholder="Order ID" value="" /></div>
                                <div><input type="button" id="print_invoice" value="Print Invoice"></div>
                                <div><input type="number" id="box_qty" value="1" min="0" /></div>
                                <div><input type="button" id="print_box_sheets" value="Print Box Sheets"></div>
                                <div class="heading">Barnet Inventory</div>
                                <div><a target="_blank" href="/?update_product_levels=1" id="update_inventory_levels">Update Inventory Levels</a></div>
                                <div><a target="_blank" href="/?update_barnet_inventory=1" id="update_all_inventory">Update All Inventory</a></div>
                                <div><a target="_blank" href="/?remove_draft_products=1" id="remove_draft_products">Remove Draft Products</a></div>
                                <div><a target="_blank" href="/?update_barnet_inventory=1&dump_and_die=1&missing_images=1" id="get_missing_images">Get Missing Images</a></div>
                                <div class="heading">Barnet Troubleshooting</div>
                                <div class="span2"><a href="/?update_barnet_inventory=1&dump_and_die=1" id="view_all_products">View All Products</a></div>
                                <div><input type="text" id="sku" placeholder="SKU" /></div>
                                <div><a id="get_one_sku">Get One SKU</a></div>
                                <div class="heading">Other</div>
                                <div><a class="disabled" id="clear_features_cache">Clear Features Cache</a></div>
                            </div>
                        </div>
                    </div>
            <?php
                    break;
                default:
                    $sections = array();

                    foreach ($this->tab_list as $key => $item)
                        if ($this->tab_list[$tab]['tab_title'] == $item['tab_title'])
                            $sections[] = $key;

                    echo '<form method="post" action="options.php" class="mti-options">';

                    // print hidden nonce, etc. needed for the settings form
                    settings_fields(self::SETTING_OBJECT_NAME);

                    // print out the settings using custom method to print out the settings based on a particular section so you can use easier with tabs                    
                    $this->do_settings_sections(self::SETTING_PAGE_SLUG, $sections);

                    submit_button();

                    echo '</form>';

                    break;
            endswitch; ?>
        </div>
    <?php else :
        echo '<form method="post" action="options.php" class="mti-options">';

        // print hidden nonce, etc. needed for the settings form
        settings_fields(self::SETTING_OBJECT_NAME);

        // print out the settings sections with the settings fields using: do_settings_sections(self::SETTING_PAGE_SLUG);
        // alternatively, use custom method to print out the settings based on a particular section so you can use easier with tabs
        $this->do_settings_sections(self::SETTING_PAGE_SLUG);

        submit_button();

        echo '</form>';
    endif; ?>

    <?php
    //// TESTING ////
    //echo 'settings: ' . var_export(MTI_Settings::get_settings(), true);
    /////////////////
    ?>
</div>