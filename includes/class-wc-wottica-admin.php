<?php

defined('ABSPATH') || exit;

/**
 * Test.
 */
class WC_Wottica_Admin
{
    public function __construct()
    {
        add_action('init', [$this, 'register_custom_taxonomy'], 0);
        add_action('woocommerce_product_after_variable_attributes', [$this, 'mytheme_woo_add_custom_variation_fields'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'mytheme_woo_add_custom_variation_fields_save'], 10, 2);

        add_filter('woocommerce_product_data_tabs', [$this, 'custom_product_tabs']);
        add_filter('woocommerce_product_data_panels', [$this, 'giftcard_options_product_tab_content']);
        add_action('woocommerce_process_product_meta_simple', [$this, 'save_giftcard_option_fields']);
        add_action('woocommerce_process_product_meta_variable', [$this, 'save_giftcard_option_fields']);
        add_filter('product_type_options', [$this,  'add_gift_card_product_option']);
    }

    public function register_custom_taxonomy()
    {
        $this->taxonomy_brand();
        $this->taxonomy_material();
    }


    function custom_product_tabs($tabs)
    {

        $tabs['giftcard'] = array(
            'label'        => __('Lentes', 'woocommerce'),
            'target'    => 'giftcard_options',
            'class'        => array('show_if_gift_card'),
        );

        return $tabs;
    }



    function giftcard_options_product_tab_content()
    {
?>
        <div id='giftcard_options' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <?php
                woocommerce_wp_checkbox(array(
                    'id'         => '_allow_personal_message',
                    'label'     => __('Allow the customer to add a personal message', 'woocommerce'),
                ));

                woocommerce_wp_text_input(array(
                    'id'                => '_valid_for_days',
                    'label'                => __('Gift card validity (in days)', 'woocommerce'),
                    'desc_tip'            => 'true',
                    'description'        => __('Enter the number of days the gift card is valid for.', 'woocommerce'),
                    'type'                 => 'number',
                    'custom_attributes'    => array(
                        'min'    => '1',
                        'step'    => '1',
                    ),
                ));

                ?>
            </div>
        </div>
<?php
    }


    function save_giftcard_option_fields($post_id)
    {

        $allow_personal_message = isset($_POST['_allow_personal_message']) ? 'yes' : 'no';
        update_post_meta($post_id, '_allow_personal_message', $allow_personal_message);

        if (isset($_POST['_valid_for_days'])) :
            update_post_meta($post_id, '_valid_for_days', absint($_POST['_valid_for_days']));
        endif;

        update_post_meta($post_id, '_redeem_in_stores', (array) $_POST['_redeem_in_stores']);

        $is_gift_card = isset($_POST['_gift_card']) ? 'yes' : 'no';
        update_post_meta($post_id, '_gift_card', $is_gift_card);
    }



    function mytheme_woo_add_custom_variation_fields($loop, $variation_data, $variation)
    {

        echo '<div class="options_group form-row form-row-full show_if_gift_card">';

        // Text Field
        woocommerce_wp_text_input(
            array(
                'id'          => '_variable_text_field[' . $variation->ID . ']',
                'label'       => __('Índice de Refração', 'woocommerce'),
                'placeholder' => 'Digite o indice',
                'desc_tip'    => true,
                'description' => __("Texto de ajuda.", "woocommerce"),
                'value' => get_post_meta($variation->ID, '_variable_text_field', true)
            )
        );

        echo '</div>';
    }

    function mytheme_woo_add_custom_variation_fields_save($post_id)
    {

        // Text Field
        $woocommerce_text_field = $_POST['_variable_text_field'][$post_id];
        update_post_meta($post_id, '_variable_text_field', esc_attr($woocommerce_text_field));
    }

    /**
     * Add 'Gift Card' product option
     */
    function add_gift_card_product_option($product_type_options)
    {

        $product_type_options['gift_card'] = array(
            'id'            => '_gift_card',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label'         => __('Lentes', 'woocommerce'),
            'description'   => __('Gift Cards allow users to put in personalised messages.', 'woocommerce'),
            'default'       => 'no'
        );

        return $product_type_options;
    }


    private function taxonomy_brand()
    {
        $nameSingular = "Marca";
        $namePlural = "Marcas";
        $gender = "a";

        $labels = array(
            'name'                       => "{$namePlural}",
            'singular_name'              => "{$nameSingular}",
            'menu_name'                  => "{$namePlural}",
            'all_items'                  => "Tod{$gender}s {$namePlural}",
            'parent_item'                => "{$nameSingular} Pai",
            'parent_item_colon'          => "{$nameSingular} Pai:",
            'new_item_name'              => "Nov{$gender} {$nameSingular}",
            'add_new_item'               => "Adicionar nov{$gender} {$nameSingular}",
            'edit_item'                  => "Editar {$nameSingular}",
            'update_item'                => "Atualizar {$nameSingular}",
            'separate_items_with_commas' => "Separar {$nameSingular} com virgulas",
            'search_items'               => "Procurar {$namePlural}",
            'add_or_remove_items'        => "Adicionar or remover {$namePlural}",
            'choose_from_most_used'      => "Escolher d{$gender} mais usad{$gender} {$namePlural}",
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );
        register_taxonomy('brand', 'product', $args);
    }
    private function taxonomy_material()
    {
        $nameSingular = "Material";
        $namePlural = "Materiais";
        $gender = "o";

        $labels = array(
            'name'                       => "{$namePlural}",
            'singular_name'              => "{$nameSingular}",
            'menu_name'                  => "{$namePlural}",
            'all_items'                  => "Tod{$gender}s {$namePlural}",
            'parent_item'                => "{$nameSingular} Pai",
            'parent_item_colon'          => "{$nameSingular} Pai:",
            'new_item_name'              => "Nov{$gender} {$nameSingular}",
            'add_new_item'               => "Adicionar nov{$gender} {$nameSingular}",
            'edit_item'                  => "Editar {$nameSingular}",
            'update_item'                => "Atualizar {$nameSingular}",
            'separate_items_with_commas' => "Separar {$nameSingular} com virgulas",
            'search_items'               => "Procurar {$namePlural}",
            'add_or_remove_items'        => "Adicionar or remover {$namePlural}",
            'choose_from_most_used'      => "Escolher d{$gender} mais usad{$gender} {$namePlural}",
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );
        register_taxonomy('material', 'product', $args);
    }
}

new WC_Wottica_Admin();
