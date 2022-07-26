<?php

defined('ABSPATH') || exit;

/**
 * Test.
 */
class WC_Wottica_Admin
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
        ?>
        <div id='lens_options' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <?php
                woocommerce_wp_checkbox([
                    'id' => '_allow_personal_message',
                    'label' => __('Allow the customer to add a personal message', 'woocommerce'),
                ]); ?>
            </div>
        </div>

        <div id='frame_options' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <?php
                woocommerce_wp_checkbox([
                    'id' => '_allow_personal_message',
                    'label' => __('Allow the customer to add a personal message', 'woocommerce'),
                ]); ?>
            </div>
        </div>
<?php
    }

    public function save_extra_option_fields($post_id)
    {
        $_SESSION['my_admin_notices'] = 'TESTE';

        $this->save_lens($post_id);
        $this->save_frame($post_id);
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

    private function save_lens($post_id)
    {
        $allow_personal_message = isset($_POST['_allow_personal_message']) ? 'yes' : 'no';
        update_post_meta($post_id, '_allow_personal_message', $allow_personal_message);

        if (isset($_POST['_valid_for_days'])) {
            update_post_meta($post_id, '_valid_for_days', absint($_POST['_valid_for_days']));
        }

        update_post_meta($post_id, '_lens', isset($_POST['_lens']) ? 'yes' : 'no');
    }

    private function save_frame($post_id)
    {
        $allow_personal_message = isset($_POST['_allow_personal_message']) ? 'yes' : 'no';
        update_post_meta($post_id, '_allow_personal_message', $allow_personal_message);

        if (isset($_POST['_valid_for_days'])) {
            update_post_meta($post_id, '_valid_for_days', absint($_POST['_valid_for_days']));
        }

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

        // Text Field
        woocommerce_wp_text_input(
            [
                'id' => '_variable_text_field['.$variation->ID.']',
                'label' => __('Índice de Refração', 'woocommerce'),
                'placeholder' => 'Digite o indice',
                'desc_tip' => true,
                'description' => __('Texto de ajuda.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, '_variable_text_field', true),
            ]
        );

        echo '</div>';
    }

    public function mytheme_woo_add_custom_variation_fields_save($post_id)
    {
        // Text Field
        $woocommerce_text_field = $_POST['_variable_text_field'][$post_id];
        update_post_meta($post_id, '_variable_text_field', esc_attr($woocommerce_text_field));
    }

    public function session_start_admin()
    {
        if (!session_id()) {
            session_start();
        }
    }
}

new WC_Wottica_Admin();
