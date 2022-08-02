<?php

defined('ABSPATH') || exit;

/**
 * Admin product custom fields.
 */
class WC_Wottica_Admin_Product
{
    protected $error_msg = '';

    public function __construct()
    {
        add_action('init', [$this, 'session_start_admin']);
        add_action('woocommerce_product_after_variable_attributes', [$this, 'mytheme_woo_add_custom_variation_fields'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'mytheme_woo_add_custom_variation_fields_save'], 10, 2);

        add_filter('woocommerce_product_data_tabs', [$this, 'custom_product_tabs']);
        add_filter('woocommerce_product_data_panels', [$this, 'extra_options_product_tab_content']);
        add_action('woocommerce_process_product_meta_simple', [$this, 'save_extra_option_fields']);
        add_action('woocommerce_process_product_meta_variable', [$this, 'save_extra_option_fields']);
        add_filter('product_type_options', [$this,  'add_extra_product_option']);
        add_action('admin_notices', [$this,  'my_admin_notices']);
    }

    public function custom_product_tabs($tabs)
    {
        $tabs['lens'] = [
            'label' => __('Lentes', 'woocommerce'),
            'target' => 'lens_options',
            'class' => ['show_if_lens'],
        ];

        $tabs['frame'] = [
          'label' => __('Armações', 'woocommerce'),
          'target' => 'frame_options',
          'class' => ['show_if_frame'],
      ];

        return $tabs;
    }

    public function extra_options_product_tab_content()
    {
        global $wpdb;
        global $post;

        $result = $wpdb->get_results(
          $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id DESC', ['lens', 'product']),
            ARRAY_A
        );
        echo "<div id='lens_options' class='panel woocommerce_options_panel'>";
        foreach ($result as $index => $row) {
            $options = $this->get_items($row['id']);
            $value = get_post_meta($post->ID, $row['identifier'], true);

            echo "<div class='options_group'>";
            woocommerce_wp_select([
              'id' => $row['identifier'],
              'label' => __($row['name'], 'woocommerce'),
              'options' => $options,
              'value' => $value,
            ]);
            echo '</div>';
        }
        echo '</div>';

        $result = $wpdb->get_results(
          $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id DESC', ['frame', 'product']),
            ARRAY_A
        );
        echo "<div id='frame_options' class='panel woocommerce_options_panel'>";

        foreach ($result as $index => $row) {
            $options = $this->get_items($row['id']);
            $value = get_post_meta($post->ID, $row['identifier'], true);

            echo "<div class='options_group'>";
            woocommerce_wp_select([
            'id' => $row['identifier'],
            'label' => __($row['name'], 'woocommerce'),
            'options' => $options,
            'value' => $value,
          ]);
            echo '</div>';
        }

        echo '</div>';
    }

    public function save_extra_option_fields($post_id)
    {
        $_SESSION['my_admin_notices'] = 'TESTE';

        global $wpdb;
        $result = $wpdb->get_results(
        $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE location = %s
            ORDER BY id DESC', ['product']),
            ARRAY_A
        );

        foreach ($result as $row) {
            if (isset($_POST[$row['identifier']])) {
                update_post_meta($post_id, $row['identifier'], $_POST[$row['identifier']]);
            }
        }

        update_post_meta($post_id, '_lens', isset($_POST['_lens']) ? 'yes' : 'no');
        update_post_meta($post_id, '_frame', isset($_POST['_frame']) ? 'yes' : 'no');
    }

    public function add_extra_product_option($product_type_options)
    {
        $product_type_options['lens'] = [
            'id' => '_lens',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label' => __('Lentes', 'woocommerce'),
            'description' => __('Dados das lentes.', 'woocommerce'),
            'default' => 'yes',
        ];

        $product_type_options['frame'] = [
          'id' => '_frame',
          'wrapper_class' => 'show_if_simple show_if_variable',
          'label' => __('Armações', 'woocommerce'),
          'description' => __('Dados das armações.', 'woocommerce'),
          'default' => 'no',
      ];

        return $product_type_options;
    }

    public function mytheme_woo_add_custom_variation_fields($loop, $variation_data, $variation)
    {
        echo '<div class="options_group form-row form-row-full show_if_lens">';
        echo '<h3 style="padding-left:0 !important; margin-top:15px; border-top:1px solid #eee">Dados Lentes</h3>';
        global $wpdb;
        global $post;

        $result = $wpdb->get_results(
            $wpdb->prepare('SELECT *
              FROM wottica_taxonomy
              WHERE type = %s AND location = %s
              ORDER BY id DESC', ['lens', 'variation']),
              ARRAY_A
          );
        foreach ($result as $index => $row) {
            $options = $this->get_items($row['id']);
            $value = get_post_meta($variation->ID, $row['identifier'], true);

            echo "<div class='form-row form-row-full'>";
            woocommerce_wp_select([
                'id' => $row['identifier'].'['.$variation->ID.']',
                'label' => __($row['name'], 'woocommerce'),
                'options' => $options,
                'value' => $value,
              ]);
            echo '</div>';
        }

        $esferico_de = get_post_meta($variation->ID, 'esferico_de', true);
        echo "<div class='form-row form-row-first'>";
        woocommerce_wp_select([
          'id' => 'esferico_de'.'['.$variation->ID.']',
          'label' => __('Esférico de', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-30, 20, 0.25),
          'value' => $esferico_de,
        ]);
        echo '</div>';

        $esferico_ate = get_post_meta($variation->ID, 'esferico_ate', true);
        echo "<div class='form-row form-row-last'>";
        woocommerce_wp_select([
          'id' => 'esferico_ate'.'['.$variation->ID.']',
          'label' => __('Esférico ate', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-30, 20, 0.25),
          'value' => $esferico_ate,
        ]);
        echo '</div>';

        $cilindrico_de = get_post_meta($variation->ID, 'cilindrico_de', true);
        echo "<div class='form-row form-row-first'>";
        woocommerce_wp_select([
          'id' => 'cilindrico_de'.'['.$variation->ID.']',
          'label' => __('Cilindrico de', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-10, 0, 0.25),
          'value' => $cilindrico_de,
        ]);
        echo '</div>';

        $cilindrico_ate = get_post_meta($variation->ID, 'cilindrico_ate', true);
        echo "<div class='form-row form-row-last'>";
        woocommerce_wp_select([
          'id' => 'cilindrico_ate'.'['.$variation->ID.']',
          'label' => __('Cilindrico ate', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-10, 0, 0.25),
          'value' => $cilindrico_ate,
        ]);
        echo '</div>';

        $adicao_de = get_post_meta($variation->ID, 'adicao_de', true);
        echo "<div class='form-row form-row-first'>";
        woocommerce_wp_select([
          'id' => 'adicao_de'.'['.$variation->ID.']',
          'label' => __('Adição de', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-0.75, 3.5, 0.25),
          'value' => $adicao_de,
        ]);
        echo '</div>';

        $adicao_ate = get_post_meta($variation->ID, 'adicao_ate', true);
        echo "<div class='form-row form-row-last'>";
        woocommerce_wp_select([
          'id' => 'adicao_ate'.'['.$variation->ID.']',
          'label' => __('Adição ate', 'woocommerce'),
          'options' => $this->get_items_taxonomy(-0.75, 3.5, 0.25),
          'value' => $adicao_ate,
        ]);
        echo '</div>';

        echo '</div>';

        echo '<div class="options_group form-row form-row-full show_if_frame">';
        echo '<h3 style="padding-left:0 !important; margin-top:15px; border-top:1px solid #eee">Dados Armações</h3>';

        $result = $wpdb->get_results(
          $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id DESC', ['frame', 'variation']),
            ARRAY_A
        );
        foreach ($result as $index => $row) {
            $options = $this->get_items($row['id']);
            $value = get_post_meta($variation->ID, $row['identifier'], true);

            echo "<div class='options_group'>";
            woocommerce_wp_select([
              'id' => $row['identifier'].'['.$variation->ID.']',
              'label' => __($row['name'], 'woocommerce'),
              'options' => $options,
              'value' => $value,
            ]);
            echo '</div>';
        }
        echo '</div>';
    }

    public function mytheme_woo_add_custom_variation_fields_save($post_id)
    {
        global $wpdb;
        $result = $wpdb->get_results(
        $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id DESC', ['variation']),
            ARRAY_A
        );

        foreach ($result as $row) {
            $woocommerce_variation = $_POST[$row['identifier']][$post_id];
            if (isset($woocommerce_variation)) {
                update_post_meta($post_id, $row['identifier'], esc_attr($woocommerce_variation));
            }
        }

        if (isset($_POST['esferico_de'][$post_id])) {
            update_post_meta($post_id, 'esferico_de', esc_attr($_POST['esferico_de'][$post_id]));
        }
        if (isset($_POST['esferico_ate'][$post_id])) {
            update_post_meta($post_id, 'esferico_ate', esc_attr($_POST['esferico_ate'][$post_id]));
        }
        if (isset($_POST['cilindrico_de'][$post_id])) {
            update_post_meta($post_id, 'cilindrico_de', esc_attr($_POST['cilindrico_de'][$post_id]));
        }
        if (isset($_POST['cilindrico_ate'][$post_id])) {
            update_post_meta($post_id, 'cilindrico_ate', esc_attr($_POST['cilindrico_ate'][$post_id]));
        }
        if (isset($_POST['adicao_de'][$post_id])) {
            update_post_meta($post_id, 'adicao_de', esc_attr($_POST['adicao_de'][$post_id]));
        }
        if (isset($_POST['adicao_ate'][$post_id])) {
            update_post_meta($post_id, 'adicao_ate', esc_attr($_POST['adicao_ate'][$post_id]));
        }
    }

    public function session_start_admin()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public function my_admin_notices()
    {
        if (!empty($_SESSION['my_admin_notices'])) {
            ?>
            <div class="notice notice-warning is-dismissible"> 
              <p><?php echo $_SESSION['my_admin_notices']; ?></p>
            </div>
        <?php
          unset($_SESSION['my_admin_notices']);
        }
    }

    private function get_items($taxonomy)
    {
        global $wpdb;
        $options[''] = __('Selecione um valor', 'woocommerce');

        $resultItems = $wpdb->get_results(
        $wpdb->prepare('SELECT *
          FROM wottica_taxonomy_itens
          WHERE taxonomy_id = %d
          ORDER BY id DESC', $taxonomy),
          ARRAY_A
        );

        foreach ($resultItems as $item) {
            $options[$item['id']] = $item['name'];
        }

        return $options;
    }

    private function get_items_taxonomy($min = 0, $max = 10, $add = 1)
    {
        $options[''] = __('Selecione um valor', 'woocommerce');
        $value = $min;

        while ($value <= $max) {
            $options["$value"] = "$value";
            $value += $add;
        }

        return $options;
    }
}

new WC_Wottica_Admin_Product();
