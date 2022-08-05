<?php

defined('ABSPATH') || exit;

/**
 * Admin product custom fields.
 */
class WC_Wottica_Product
{
    public function __construct()
    {
        add_action('init', [$this, 'remove_add_to_cart_loop']);
        add_action('init', [$this, 'remove_add_to_cart_single']);
        add_action('woocommerce_single_product_summary', [$this, 'replace_add_to_cart_single'], 45);
    }

    public function remove_add_to_cart_loop()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    }

    public function remove_add_to_cart_single()
    {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    public function replace_add_to_cart_single()
    {
        global $product;
        $link = home_url("/lentes-checkout?id={$product->get_id()}");
        echo do_shortcode("<a href='{$link}' class='button'>Selecionar lente</a>");
    }

    public function replace_add_to_cart_loop()
    {
        global $product;
        $link = $product->get_permalink();
        do_action('woocommerce_before_add_to_cart_button');
        echo do_shortcode("<a href='{$link}' class='button'>Ver mais</a>");
        do_action('woocommerce_after_add_to_cart_button');
    }

    public function my_custom_add_to_cart_redirect($url)
    {
        $url = home_url('/lentes-checkout');

        return $url;
    }
}

new WC_Wottica_Product();
