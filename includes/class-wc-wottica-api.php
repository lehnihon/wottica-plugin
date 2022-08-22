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
        add_action('wp_ajax_lki_get_filters', [$this, 'lki_get_filters']);
        add_action('wp_ajax_nopriv_lki_get_filters', [$this, 'lki_get_filters']);
        add_action('wp_ajax_lki_get_lens', [$this, 'lki_get_lens']);
        add_action('wp_ajax_nopriv_lki_get_lens', [$this, 'lki_get_lens']);
    }

    public static function lki_get_filters()
    {
        global $wpdb;

        $result = $wpdb->get_results(
          $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id ASC', ['lens', 'product']),
            ARRAY_A
        );
        $data = [];

        foreach ($result as $index => $row) {
            $options = WC_Wottica_Api::get_options($row['id'], $row['data_type']);
            $data[$index]['identifier'] = $row['identifier'];
            $data[$index]['name'] = $row['name'];
            $data[$index]['options'] = $options;
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit();
    }

    public static function lki_get_lens()
    {
        global $wpdb;

        $args = [
            'status' => 'publish',
            'category' => ['lentes'],
            '_wottica_lens_esferico' => $_POST['esferico'],
        ];
        $products = wc_get_products($args);

        foreach ($products as $index => $product) {
            $data[$index] = $product->get_data();
            if ($product->get_type() == 'variable') {
                foreach ($product->get_available_variations() as $variation) {
                    $variation['meta'] = get_post_meta($variation['variation_id']);
                    $data[$index]['variations'][] = $variation;
                }
            }
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit();
    }

    public static function get_options($taxonomy, $type)
    {
        global $wpdb;
        $options = [];

        $resultItems = $wpdb->get_results(
        $wpdb->prepare('SELECT *
          FROM wottica_taxonomy_itens
          WHERE taxonomy_id = %d
          ORDER BY id DESC', $taxonomy),
          ARRAY_A
        );

        $resultItems = WC_Wottica_Api::sort_data($resultItems, 'value', $type);

        foreach ($resultItems as $item) {
            $options[$item['id']] = $item['value'];
        }

        return $options;
    }

    public static function sort_data($data, $field, $type)
    {
        $keys = array_column($data, $field);
        array_multisort($keys, SORT_ASC, $type == 'number' ? SORT_NUMERIC : SORT_REGULAR, $data);

        return $data;
    }

    public static function show_queries()
    {
        global $wpdb;
        echo '<pre>Query List:';
        print_r($wpdb->queries);
        echo '</pre>';
    }
}

new WC_Wottica_Api();
