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
        add_action('wp_ajax_lki_update_user_photo', [$this, 'lki_update_user_photo']);
        add_action('wp_ajax_nopriv_lki_update_user_photo', [$this, 'lki_update_user_photo']);
        add_action('wp_ajax_lki_get_user_photo', [$this, 'lki_get_user_photo']);
        add_action('wp_ajax_nopriv_lki_get_user_photo', [$this, 'lki_get_user_photo']);
        add_action('wp_ajax_lki_delete_user_photo', [$this, 'lki_delete_user_photo']);
        add_action('wp_ajax_nopriv_lki_delete_user_photo', [$this, 'lki_delete_user_photo']);

        add_action('wp_ajax_lki_update_user_prescription', [$this, 'lki_update_user_prescription']);
        add_action('wp_ajax_nopriv_lki_update_user_prescription', [$this, 'lki_update_user_prescription']);
        add_action('wp_ajax_lki_get_user_prescription', [$this, 'lki_get_user_prescription']);
        add_action('wp_ajax_nopriv_lki_get_user_prescription', [$this, 'lki_get_user_prescription']);
        add_action('wp_ajax_lki_delete_user_prescription', [$this, 'lki_delete_user_prescription']);
        add_action('wp_ajax_nopriv_lki_delete_user_prescription', [$this, 'lki_delete_user_prescription']);
    }

    public static function lki_delete_user_prescription()
    {
        if (!is_user_logged_in()) {
            return;
        }

        if (empty($_POST['prescription_photo_id'])) {
            return;
        }

        $found = get_user_meta(get_current_user_id(), 'lki_precription_photos', true);

        if (empty($found)) {
            return;
        }

        $data = json_decode($found, true);
        $key = array_search($_POST['prescription_photo_id'], array_column($data, 'id'));

        if ($key === false) {
            return;
        }

        wp_delete_attachment($data[$key]['id'], true);
        unlink($data[$key]['photo']);
        unset($data[$key]);
        $data = array_values($data);
        update_user_meta(get_current_user_id(), 'lki_precription_photos', json_encode($data));

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
    }

    public static function lki_get_user_prescription()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $data = [];

        $found = get_user_meta(get_current_user_id(), 'lki_precription_photos', true);
        if (!empty($found)) {
            $data = json_decode($found, true);
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
    }

    public static function lki_update_user_prescription()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $data = [];

        if (!empty($_POST['prescription_photo_id']) && !empty($_POST['prescription_photo'])) {
            $found = get_user_meta(get_current_user_id(), 'lki_precription_photos', true);

            if (!empty($found)) {
                $data = json_decode($found, true);
            }

            $data[] = ['id' => $_POST['prescription_photo_id'], 'photo' => $_POST['prescription_photo']];
            update_user_meta(get_current_user_id(), 'lki_precription_photos', json_encode($data));
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
    }

    public static function lki_delete_user_photo()
    {
        if (!is_user_logged_in()) {
            return;
        }

        if (empty($_POST['facial_photo_id'])) {
            return;
        }

        $found = get_user_meta(get_current_user_id(), 'lki_facial_photos', true);

        if (empty($found)) {
            return;
        }

        $data = json_decode($found, true);
        $key = array_search($_POST['facial_photo_id'], array_column($data, 'id'));

        if ($key === false) {
            return;
        }

        wp_delete_attachment($data[$key]['id'], true);
        unlink($data[$key]['photo']);
        unset($data[$key]);
        $data = array_values($data);
        update_user_meta(get_current_user_id(), 'lki_facial_photos', json_encode($data));

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
    }

    public static function lki_get_user_photo()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $data = [];

        $found = get_user_meta(get_current_user_id(), 'lki_facial_photos', true);
        if (!empty($found)) {
            $data = json_decode($found, true);
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
    }

    public static function lki_update_user_photo()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $data = [];

        if (!empty($_POST['facial_photo_id']) && !empty($_POST['facial_photo'])) {
            $found = get_user_meta(get_current_user_id(), 'lki_facial_photos', true);

            if (!empty($found)) {
                $data = json_decode($found, true);
            }

            $data[] = ['id' => $_POST['facial_photo_id'], 'photo' => $_POST['facial_photo']];
            update_user_meta(get_current_user_id(), 'lki_facial_photos', json_encode($data));
        }

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
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

        exit;
    }

    public static function lki_get_lens()
    {
        global $wpdb;
        $args = [];

        if (!empty($_POST['esferico'])) {
            $args[] = [
              'key' => '_wottica_lens_esferico_de',
              'value' => $_POST['esferico'],
              'compare' => '<=',
            ];
            $args[] = [
              'key' => '_wottica_lens_esferico_ate',
              'value' => $_POST['esferico'],
              'compare' => '>=',
            ];
        }

        if (!empty($_POST['cilindrico'])) {
            $args[] = [
            'key' => '_wottica_lens_cilindrico_de',
            'value' => $_POST['cilindrico'],
            'compare' => '<=',
          ];
            $args[] = [
            'key' => '_wottica_lens_cilindrico_ate',
            'value' => $_POST['cilindrico'],
            'compare' => '>=',
          ];
        }

        if (!empty($_POST['adicao'])) {
            $args[] = [
              'key' => '_wottica_lens_adicao_de',
              'value' => $_POST['adicao'],
              'compare' => '<=',
            ];
            $args[] = [
              'key' => '_wottica_lens_adicao_ate',
              'value' => $_POST['adicao'],
              'compare' => '>=',
            ];
        }

        if (!empty($_POST['marca'])) {
            $args[] = [
              'key' => '_wottica_lens_marca',
              'value' => $_POST['marca'],
              'compare' => '=',
            ];
        }

        if (!empty($_POST['material'])) {
            $args[] = [
              'key' => '_wottica_lens_material',
              'value' => $_POST['material'],
              'compare' => '=',
            ];
        }

        $data = WC_Wottica_Api::get_products_filters($args);

        echo json_encode([
          'data' => empty($data) ? [] : $data,
          'status' => true,
          'message' => '',
        ]);

        exit;
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
        foreach ($filters as $i => $filter) {
            $compare = empty($filter['compare']) ? '=' : $filter['compare'];

            $join .= " INNER JOIN {$wpdb->prefix}postmeta m{$i} ON ( {$wpdb->prefix}posts.ID = m{$i}.post_id ) ";
            $where .= " AND ( m{$i}.meta_key = '{$filter['key']}' AND ";
            if (!is_array($filter['value'])) {
                $where .= " m{$i}.meta_value {$compare} '{$filter['value']}') ";
            } else {
                foreach ($filter['value'] as $j => $value) {
                    if ($j == 0) {
                        $where .= ' (';
                    }
                    $where .= "CAST(m{$i}.meta_value AS DECIMAL) {$compare} '{$value}' ";
                    if (count($filter['value']) > $j + 1) {
                        $where .= ' OR ';
                    }
                }
                $where .= ')) ';
            }
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
        if (empty($results)) {
            return [];
        }

        $ids = array_column($results, 'ID');
        $products = wc_get_products(['include' => $ids]);
        foreach ($products as $index => $product) {
            $data[] = $product->get_data();
        }

        return $data;
    }
}

new WC_Wottica_Api();
