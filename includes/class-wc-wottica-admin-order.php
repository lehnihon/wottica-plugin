<?php

defined('ABSPATH') || exit;

/**
 * Admin taxonomy.
 */
class WC_Wottica_Admin_Order
{
    public function __construct()
    {
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'my_custom_checkout_field_display_admin_order_meta'], 10, 1);
        add_action('woocommerce_admin_order_item_headers', [$this, 'action_woocommerce_admin_order_item_headers'], 10, 1);
        add_action('woocommerce_admin_order_item_values', [$this, 'action_woocommerce_admin_order_item_values'], 10, 3);
    }

    public function my_custom_checkout_field_display_admin_order_meta($order)
    {
        $userId = $order->get_user_id();

        $prescription = get_user_meta($userId, 'lki_facial_photos', true);
        if (!empty($prescription)) {
            echo '<h3>Fotos</h3>';
            $data = json_decode($prescription, true);

            foreach ($data as $index => $value) {
                echo "<a target='_blank' href='".$value['photo']."'>Fotos ".($index + 1).'</a>';
            }
        }

        $prescription = get_user_meta($userId, 'lki_precription_photos', true);
        if (!empty($prescription)) {
            echo '<h3>Precrições</h3>';
            $data = json_decode($prescription, true);

            foreach ($data as $index => $value) {
                echo "<a target='_blank' href='".$value['photo']."'>Prescrição ".($index + 1).'</a>';
            }
        }
    }

    public function action_woocommerce_admin_order_item_headers($order)
    {
        echo '<th class="my-class">Lente</th>';
    }

  // Add content
  public function action_woocommerce_admin_order_item_values($product, $item, $item_id)
  {
      // Only for "line_item" items type, to avoid errors
      if (!$item->is_type('line_item')) {
          return;
      }

      $id = $item->get_meta('_lens_id');
      $name = $item->get_meta('_lens_name');
      $url = get_permalink($id);

      // NOT empty
      if (!empty($name)) {
          echo '<td><a target="_blank" href="'.$url.'">'.$name.'</a></td>';
      } else {
          echo '<td>-</td>';
      }
  }
}

new WC_Wottica_Admin_Order();
