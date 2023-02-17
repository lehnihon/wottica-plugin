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
        add_action('woocommerce_check_cart_items', [$this, 'action_woocommerce_check_cart_items'], 10);
        add_action('woocommerce_proceed_to_checkout', [$this, 'custom_button_proceed_to_checkout'], 20);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_custom_order_item_meta_data'], 10, 4);
    }

    public function display_cart_item_custom_meta_data($item_data, $cart_item)
    {
        if (isset($cart_item['lens']) && isset($cart_item['lens'])) {
            $item_data[] = [
              'key' => 'Lente',
              'value' => $cart_item['lens']['name'],
            ];
        }

        return $item_data;
    }

    public function add_custom_price($cart_object)
    {
        $id = WC()->session->get('lens_id');
        $price = WC()->session->get('lens_price');
        $name = WC()->session->get('lens_name');
        if (!empty($id)) {
            $key = array_key_last($cart_object->cart_contents);
            $cartItem = WC()->cart->cart_contents[$key];
            $cartItem['lens'] = [
              'id' => $id,
              'price' => $price,
              'name' => $name,
            ];
            WC()->cart->cart_contents[$key] = $cartItem;

            WC()->session->set('lens_id', null);
            WC()->session->set('lens_price', null);
            WC()->session->set('lens_name', null);
        }
        foreach (WC()->cart->cart_contents as $cart_item) {
            $custom_price = $cart_item['data']->get_price() + (isset($cart_item['lens']['price']) ? $cart_item['lens']['price'] : 0);
            $cart_item['data']->set_price($custom_price);
        }
    }

    public function action_woocommerce_check_cart_items()
    {
        remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
    }

    public function custom_button_proceed_to_checkout()
    {
        $link = home_url('/enviar-fotos');

        echo '<a href="'.$link.'" class="checkout-button button alt wc-forward">'.
        __('Enviar fotos', 'woocommerce').'</a>';
    }

    public function save_custom_order_item_meta_data($item, $cart_item_key, $values, $order)
    {
        if (isset($values['lens']['id'])) {
            $item->update_meta_data('_lens_id', $values['lens']['id']);
        }
        if (isset($values['lens']['name'])) {
            $item->update_meta_data('_lens_name', $values['lens']['name']);
        }
        if (isset($values['lens']['price'])) {
            $item->update_meta_data('_lens_price', $values['lens']['price']);
        }
    }
}

new WC_Wottica_Cart();
