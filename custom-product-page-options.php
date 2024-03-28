<?php
/*
Plugin Name: Custom Product Page Options
Description: Customize the display of elements on WooCommerce product pages.
Version: 1.0
Author: Tuba Saif
*/

add_action('admin_menu', 'custom_product_page_options_menu');
function custom_product_page_options_menu() {
    add_menu_page(
        'Product Page Options',
        'Product Page Options',
        'manage_options',
        'custom-product-page-options',
        'custom_product_page_options_page'
    );
}

function custom_product_page_options_page() {
    // Check if user has permissions
    if (!current_user_can('manage_options')) {
        return;
    }


    if (isset($_POST['custom_product_page_options_submit'])) {
        // Handle form data here (save options, update settings, etc.)
        update_option('hide_product_ratings', isset($_POST['hide_product_ratings']) ? 1 : 0);
        update_option('hide_product_prices', isset($_POST['hide_product_prices']) ? 1 : 0);
        update_option('hide_product_description', isset($_POST['hide_product_description']) ? 1 : 0);
        update_option('hide_product_title', isset($_POST['hide_product_title']) ? 1 : 0);
        update_option('hide_product_sku', isset($_POST['hide_product_sku']) ? 1 : 0);
        update_option('hide_product_meta_category', isset($_POST['hide_product_meta_category']) ? 1 : 0);
        update_option('hide_product_base_category', isset($_POST['hide_product_base_category']) ? 1 : 0);

        ?>
        <div class="updated"><p><?php _e('Settings saved.', 'custom-product-page-options'); ?></p></div>
        <?php
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="hide_product_ratings">Hide Product Ratings</label></th>
                    <td><input type="checkbox" id="hide_product_ratings" name="hide_product_ratings" value="1" <?php checked(get_option('hide_product_ratings'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_prices">Hide Product Prices</label></th>
                    <td><input type="checkbox" id="hide_product_prices" name="hide_product_prices" value="1" <?php checked(get_option('hide_product_prices'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_description">Hide Product Description</label></th>
                    <td><input type="checkbox" id="hide_product_description" name="hide_product_description" value="1" <?php checked(get_option('hide_product_description'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_title">Hide Product Title</label></th>
                    <td><input type="checkbox" id="hide_product_title" name="hide_product_title" value="1" <?php checked(get_option('hide_product_title'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_sku">Hide Product SKU</label></th>
                    <td><input type="checkbox" id="hide_product_sku" name="hide_product_sku" value="1" <?php checked(get_option('hide_product_sku'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_meta_category">Hide Product Meta Category</label></th>
                    <td><input type="checkbox" id="hide_product_meta_category" name="hide_product_meta_category" value="1" <?php checked(get_option('hide_product_meta_category'), 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hide_product_base_category">Hide Product Base Category</label></th>
                    <td><input type="checkbox" id="hide_product_base_category" name="hide_product_base_category" value="1" <?php checked(get_option('hide_product_base_category'), 1); ?>></td>
                </tr>
            </table>
            <input type="hidden" name="custom_product_page_options_submit" value="1">
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

// Apply filters based on saved options

// Disable ratings on product page
if (get_option('hide_product_ratings')) {
    add_filter('woocommerce_product_get_rating_html', 'disable_product_ratings'); 
    function disable_product_ratings() { 
        return ''; 
    }
}

// Disable prices on product page
if (get_option('hide_product_prices')) {
    add_filter('woocommerce_get_price_html', 'disable_product_prices', 10, 2); 
    function disable_product_prices($price_html, $product) { 
        return ''; 
    }
}

// Disable description on product page
if (get_option('hide_product_description')) {
    add_action('wp_head', 'hide_short_description_css' ); 
    function hide_short_description_css() { 
        if (is_product()) { 
            add_filter('woocommerce_short_description', 'empty_short_description' ); 
        } 
    } 
    function empty_short_description() { 
        return ''; 
    }
}

// Disable title on product page
if (get_option('hide_product_title')) {
    add_filter('the_title', 'remove_product_title', 10, 2);
    function remove_product_title($title, $id) {
        if (is_product()) {      
            return '';
        }
        return $title;
    }
}

// Disable SKU on product page
if (get_option('hide_product_sku')) {
    function sv_remove_product_page_skus($enabled) {
        if (!is_admin() && is_product()) {
            return false;
        }
        return $enabled;
    }
    add_filter('wc_product_sku_enabled', 'sv_remove_product_page_skus');
}

// Disable meta category on product page
if (get_option('hide_product_meta_category')) {
    function custom_product_meta_end() {
        $buffered_content = ob_get_clean();
        $modified_content = preg_replace('/<span class="posted_in">(.*?)<\/span>/', '', $buffered_content);
        echo $modified_content;
    }
    add_action('woocommerce_product_meta_end', 'custom_product_meta_end');
}

// Disable meta and base category on product page
if (get_option('hide_product_base_category')) {
    function remove_category($terms, $post_id, $taxonomy) {
        if (is_product() && 'product_cat' === $taxonomy) {
            $default_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ));
        }
    }
}
