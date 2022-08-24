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
            ORDER BY id ASC', ['lens', 'product']),
            ARRAY_A
        );

        echo "<div id='lens_options' class='panel woocommerce_options_panel'>";
        foreach ($result as $index => $row) {
            $value = get_post_meta($post->ID, $row['identifier'], true);

            if (str_contains($row['data_input'], 'select')) {
                $options = $this->get_items($row['id'], $row['data_type']);

                echo "<div class='options_group'>";
                woocommerce_wp_select([
                  'id' => $row['identifier'],
                  'label' => __($row['name'], 'woocommerce'),
                  'options' => $options,
                  'value' => $value,
                ]);
                echo '</div>';
            }

            if (str_contains($row['data_input'], 'text')) {
                echo "<div class='options_group'>";
                woocommerce_wp_text_input([
                  'id' => $row['identifier'],
                  'label' => __($row['name'], 'woocommerce'),
                  'value' => $value,
                ]);
                echo '</div>';
            }

            if (str_contains($row['data_input'], 'file')) {
                echo "<div class='options_group'>";
                $this->custom_input_file($post->ID, $row['identifier_extra'], $row['name']);
                echo '</div>';
            }
        }
        echo '</div>';

        $result = $wpdb->get_results(
          $wpdb->prepare('SELECT *
            FROM wottica_taxonomy
            WHERE type = %s AND location = %s
            ORDER BY id ASC', ['frame', 'product']),
            ARRAY_A
        );
        echo "<div id='frame_options' class='panel woocommerce_options_panel'>";

        foreach ($result as $index => $row) {
            $value = get_post_meta($post->ID, $row['identifier'], true);

            if (str_contains($row['data_input'], 'select')) {
                $options = $this->get_items($row['id'], $row['data_type']);

                echo "<div class='options_group'>";
                woocommerce_wp_select([
                'id' => $row['identifier'],
                'label' => __($row['name'], 'woocommerce'),
                'options' => $options,
                'value' => $value,
              ]);
                echo '</div>';
            }

            if (str_contains($row['data_input'], 'text')) {
                echo "<div class='options_group'>";
                woocommerce_wp_text_input([
                'id' => $row['identifier'],
                'label' => __($row['name'], 'woocommerce'),
                'value' => $value,
              ]);
                echo '</div>';
            }

            if (str_contains($row['data_input'], 'file')) {
                echo "<div class='options_group'>";
                $this->custom_input_file($post->ID, $row['identifier_extra'], $row['name']);
                echo '</div>';
            }
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
            if (isset($_POST[$row['identifier_extra']])) {
                update_post_meta($post_id, $row['identifier_extra'], $_POST[$row['identifier_extra']]);
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
              ORDER BY id ASC', ['lens', 'variation']),
              ARRAY_A
          );
        foreach ($result as $index => $row) {
            $options = $this->get_items($row['id'], $row['data_type']);
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

    private function custom_input_file($postId, $identifier, $name)
    {
        $upload_link = esc_url(get_upload_iframe_src('image', $postId));
        $your_img_id = get_post_meta($postId, $identifier, true);
        $your_img_src = wp_get_attachment_image_src($your_img_id, 'full');
        $you_have_img = is_array($your_img_src); ?>
     
        <div class="<?php echo $identifier; ?> custom-input-file">
          <div class="label-input-file"><?php echo $name; ?> Imagem</div>
          <input type="button" class="upload-custom-img" value="<?php _e('Selecione'); ?>" />
          <input type="button" class="delete-custom-img" value="<?php _e('Remover'); ?>" />    
          <input class="custom-img-id" name="<?php echo $identifier; ?>" id="<?php echo $identifier; ?>" type="hidden" value="<?php echo esc_attr($your_img_id); ?>" />
          <div class="custom-img-container">
              <?php if ($you_have_img) { ?>
                  <img src="<?php echo $your_img_src[0]; ?>" alt="" style="max-width:150px; max-height: 150px;" />
              <?php } ?>
          </div>
        </div>
    <?php
    }

    private function get_items($taxonomy, $type)
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

        $resultItems = $this->sort_data($resultItems, 'value', $type);

        foreach ($resultItems as $item) {
            if ($type == 'number') {
                $options[$item['value']] = $item['value'];
            } else {
                $options[$item['id']] = $item['value'];
            }
        }

        return $options;
    }

    private function sort_data($data, $field, $type)
    {
        $keys = array_column($data, $field);
        array_multisort($keys, SORT_ASC, $type == 'number' ? SORT_NUMERIC : SORT_REGULAR, $data);

        return $data;
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
