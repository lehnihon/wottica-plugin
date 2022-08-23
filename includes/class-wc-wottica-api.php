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
        $args = [];

        if (!empty($_POST['esferico'])) {
            $args[] = [
              'key' => '_wottica_lens_esferico_de',
              'value' => '-10',
            ];
        }

        $data = WC_Wottica_Api::get_products_filters($args);

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

    public static function get_products_filters($filters)
    {
        global $wpdb;
        $data = [];
        $join = '';
        $where = '';
        foreach ($filters as $index => $filter) {
            $join .= " INNER JOIN {$wpdb->prefix}postmeta m{$index} ON ( {$wpdb->prefix}posts.ID = m{$index}.post_id ) ";
            $where .= " AND ( m{$index}.meta_key = '{$filter['key']}' AND CAST(m{$index}.meta_value AS DECIMAL) > '{$filter['value']}' ) ";
        }
        $query = "
          SELECT ID
          FROM {$wpdb->prefix}posts
          $join
          WHERE {$wpdb->prefix}posts.post_type = 'product'
          AND {$wpdb->prefix}posts.post_status = 'publish'
          $where
          GROUP BY {$wpdb->prefix}posts.ID
          ORDER BY {$wpdb->prefix}posts.post_date DESC;
        ";

        $results = $wpdb->get_results($query);
        $ids = array_column($results, 'ID');
        $products = wc_get_products(['include' => $ids]);
        foreach ($products as $index => $product) {
            $data[] = $product->get_data();
        }

        return $data;
    }
}

new WC_Wottica_Api();
