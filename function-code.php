//Disable wordpress redirect to a "similar page" in case of 404 error
function stop_404_guessing( $url ) {
	$lang = function_exists('pll_current_language') ? get_site_url().'/'. pll_current_language('slug') : '';
	if (strpos($url, $lang) === false){
		return ( is_404() ) ? false : $url;
	}
}
add_filter( 'redirect_canonical', 'stop_404_guessing' );

//Update data order woocommerce after save
add_action('woocommerce_after_order_object_save', 'some_order_action', 10, 10);
function some_order_action($order) {
    if ($order && $order->get_items()) {
        $new_order_slip = get_post_meta($order->get_id(), 'new_order_slip', true);
        if ($new_order_slip) {
            $tt = 0;
            $cl = 0;
            foreach ($order->get_items() as $item_id => $item_values) {
                $cost = wc_get_order_item_meta($item_id, '_unit_price');
                $qty = wc_get_order_item_meta($item_id, '_qty');
                if ($cost && $qty) {
                    $subtotal = (float) $cost * (int) $qty;
                    $tt = $tt + $subtotal;
                }
            }
            $tt = $order->get_subtotal() - $tt;
            $cl = $order->get_total() - $tt;
            update_post_meta($order->get_id(), 'order_total_ct', $cl);
            update_post_meta($order->get_id(), '_order_total', $cl);
        }
    }
}
//add meta value custom to order item line
function action_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {
    // The WC_Product instance Object
    $product = $item->get_product();

    $unit_price = wc_get_price_excluding_tax($product);

    $item->update_meta_data('_unit_price', $unit_price);
}

add_action('woocommerce_checkout_create_order_line_item', 'action_woocommerce_checkout_create_order_line_item', 10, 4);

//Hide meta data from Order Items on Admin Order Page (WooCommerce)
function woocommerce_hidden_order_itemmeta_unit_price($arr) {
    $arr[] = '_unit_price';
    return $arr;
}

add_filter('woocommerce_hidden_order_itemmeta', 'woocommerce_hidden_order_itemmeta_unit_price', 10, 1);

add_action('woocommerce_new_order_item', 'custom_woocommerce_new_order_item', 10, 3);

//Update data order when create
function custom_woocommerce_new_order_item($item_id, $item, $order_id) {
// Get Order
    $order = !empty($order) ? $order : wc_get_order($order_id);
    if (!$order || !is_a($order, '\WC_Order')) {
        return;
    }
    $vibe_split_orders_split_from = get_post_meta($order_id, '_vibe_split_orders_split_from', true);
    if ($vibe_split_orders_split_from) {
        foreach ($order->get_items() as $item_id => $item_values) {
            $cost = wc_get_order_item_meta($item_id, '_unit_price');
            $qty = wc_get_order_item_meta($item_id, '_qty');
            if ($cost && $qty) {
                $subtotal = (float) $cost * (int) $qty;
                wc_update_order_item_meta($item_id, '_line_subtotal', $subtotal);
                wc_update_order_item_meta($item_id, '_line_total', $subtotal);
            }
        }
    }
}
