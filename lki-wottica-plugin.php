<?php

/**
 * Plugin Name: Wottica Plugin
 * Plugin URI: https://solucoesfly.com.br
 * Description: Custom Plugin.
 * Version: 1.0
 * Author: FlyTec
 * Author URI: https://solucoesfly.com.br.
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
define('LKI_Custom_Plugin_VERSION', '1.0');

if (!class_exists('LKI_Wottica_Plugin')) {
    /**
     * Main plugin class.
     */
    class LKI_Wottica_Plugin
    {
        /**
         * Instance of this class.
         *
         * @var Class instance
         */
        protected static $instance = null;

        /**
         * Constructor.
         */
        public function __construct()
        {
            add_action('wp_enqueue_scripts', [$this, 'plugin_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'admin_plugin_scripts']);

            $this->includes();
        }

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public static function plugin_activation()
        {
        }

        public static function plugin_deactivation()
        {
        }

        public static function plugin_scripts()
        {
            wp_enqueue_style('lki-style', plugins_url('assets/css/style.css', __FILE__));
            wp_enqueue_script('lki-script', plugins_url('assets/js/script.js', __FILE__), ['jquery']);
            wp_localize_script('lki-script', 'localizeObj', [
              'user' => wp_get_current_user(),
              'endpoint' => esc_url_raw(rest_url('/wp/v2/media/')),
              'nonce' => wp_create_nonce('wp_rest'),
              'api' => admin_url('admin-ajax.php'),
            ]);
        }

        public static function admin_plugin_scripts()
        {
            wp_enqueue_style('lki-admin-style', plugins_url('assets/css/adm_style.css', __FILE__));
            wp_enqueue_script('lki-admin-script', plugins_url('assets/js/adm_script.js', __FILE__), ['jquery']);
        }

        public static function get_items_taxonomy($min = 0, $max = 10, $add = 0.25, $taxonomy = 0)
        {
            $options = '';
            $value = $min;

            while ($value <= $max) {
                $options .= "('$value','$taxonomy'),";
                $value += $add;
            }

            return $options;
        }

        private function includes()
        {
            // include_once 'includes/class-wc-wottica-admin-product.php';
            include_once 'includes/class-wc-wottica-admin-order.php';
            // include_once 'includes/class-wc-wottica-admin-taxonomy.php';
            include_once 'includes/class-wc-wottica-api.php';
            include_once 'includes/class-wc-wottica-product.php';
            include_once 'includes/class-wc-wottica-cart.php';
        }
    }

    register_activation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_activation']);
    register_deactivation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_deactivation']);

    add_action('plugins_loaded', ['LKI_Wottica_Plugin', 'get_instance']);
}
