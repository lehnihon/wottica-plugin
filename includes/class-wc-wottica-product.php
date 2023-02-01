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
        $iconData = '<svg aria-hidden="true" class="e-font-icon-svg e-far-eye" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="M288 144a110.94 110.94 0 0 0-31.24 5 55.4 55.4 0 0 1 7.24 27 56 56 0 0 1-56 56 55.4 55.4 0 0 1-27-7.24A111.71 111.71 0 1 0 288 144zm284.52 97.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400c-98.65 0-189.09-55-237.93-144C98.91 167 189.34 112 288 112s189.09 55 237.93 144C477.1 345 386.66 400 288 400z"></path></svg>';

        echo "<a href='{$link}' class='button select-lens-btn'><div class='select-lens-row'>{$iconData} <span>Selecionar lente</span></div></a>";
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
