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
        add_shortcode('wottica-lens', [$this, 'shortcode_wottica_lens']);
    }

    public function remove_add_to_cart_loop()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    }

    public function remove_add_to_cart_single()
    {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    public function shortcode_wottica_lens()
    {
        global $product;
        if (empty($product)) {
            return '';
        }
        $link = home_url("/lentes-checkout?id={$product->get_id()}");

        echo "<a href='{$link}' class='button'>Selecionar lente</a>";
    }

    public function replace_add_to_cart_loop()
    {
        global $product;
        $link = $product->get_permalink();

        echo do_shortcode("<a href='{$link}' class='button'>Ver mais</a>");
    }

    public function my_custom_add_to_cart_redirect($url)
    {
        $url = home_url('/lentes-checkout');

        return $url;
    }
}

new WC_Wottica_Product();
