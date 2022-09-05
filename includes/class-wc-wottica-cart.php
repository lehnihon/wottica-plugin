<?php

defined('ABSPATH') || exit;

/**
 * Admin cart custom fields.
 */
class WC_Wottica_Cart
{
    public function __construct()
    {
        add_filter('woocommerce_get_item_data', [$this, 'display_cart_item_custom_meta_data'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'add_custom_price']);
    }

    public function display_cart_item_custom_meta_data($item_data, $cart_item)
    {
        if (isset($cart_item['lens']) && isset($cart_item['lens'])) {
            $item_data[] = [
              'key' => 'Nome',
              'value' => $cart_item['lens']['name'],
            ];
        }

        return $item_data;
    }

    public function add_custom_price($cart_object)
    {
        foreach ($cart_object->cart_contents as $cart_item) {
            $custom_price = $cart_item['data']->get_price() + (isset($cart_item['lens']['price']) ? $cart_item['lens']['price'] : 0);
            $cart_item['data']->set_price($custom_price);
        }
    }
}

new WC_Wottica_Cart();
