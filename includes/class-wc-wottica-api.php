<?php

defined('ABSPATH') || exit;

/**
 * Admin product custom fields.
 */
class WC_Wottica_Api
{
    protected $error_msg = '';

    public function __construct()
    {
        add_action('wp_ajax_lki_get_lens', [$this, 'lki_get_lens']);
        add_action('wp_ajax_nopriv_lki_get_lens', [$this, 'lki_get_lens']);
        add_action('wp_ajax_lki_get_lens_data', [$this, 'lki_get_lens_data']);
        add_action('wp_ajax_nopriv_lki_get_lens_data', [$this, 'lki_get_lens_data']);
    }

    public static function lki_get_lens()
    {
        $args = [
            'status' => 'publish',
            'category' => ['lentes'],
        ];
        $products = wc_get_products($args);
        foreach ($products as $product) {
            $data[] = $product->get_data();
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit();
    }

    public static function lki_get_lens_data()
    {
        if (empty($_POST['post_id'])) {
            echo json_encode([
              'data' => [],
              'status' => false,
              'message' => 'post id vazio',
            ]);
            exit();
        }
        $post_id = $_POST['post_id'];
        $handle = new WC_Product_Variable($post_id);
        $variations = $handle->get_children();

        $data = [];

        foreach ($variations as $id) {
            $esferico_ate = get_post_meta($id, 'esferico_ate', true);
            $cilindrico_de = get_post_meta($id, 'cilindrico_de', true);
            $cilindrico_ate = get_post_meta($id, 'cilindrico_ate', true);
            $adicao_de = get_post_meta($id, 'adicao_de', true);
            $adicao_ate = get_post_meta($id, 'adicao_ate', true);
            $data[] = [
              'esferico_de' => $esferico_de,
              'esferico_ate' => $esferico_ate,
              'cilindrico_de' => $cilindrico_de,
              'cilindrico_ate' => $cilindrico_ate,
              'adicao_de' => $adicao_de,
              'adicao_ate' => $adicao_ate,
            ];
        }

        echo json_encode([
          'data' => $data,
          'status' => true,
          'message' => '',
        ]);

        exit();
    }
}

new WC_Wottica_Api();
