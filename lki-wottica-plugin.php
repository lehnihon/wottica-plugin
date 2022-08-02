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
            global $wpdb;

            $sql = 'CREATE TABLE `wottica_taxonomy` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(191),
              `type` varchar(25),
              `identifier` varchar(191) NOT NULL UNIQUE,
              `location` varchar(25),
              `status` TINYINT(1) DEFAULT 1,
              `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            $wpdb->query($sql);

            $sql = 'INSERT INTO `wottica_taxonomy` (name,type,identifier,location) VALUES 
              ("Material","lens","wottica_lens_material","product"),
              ("Disponibilidade","lens","wottica_lens_disponibilidade","product"),
              ("Marca","lens","wottica_lens_marca","product"),
              ("Natureza de lente","lens","wottica_lens_natureza","product"),
              ("Qualidade","lens","wottica_lens_qualidade","product"),
              ("Aplicação","lens","wottica_lens_aplicacao","product"),
              ("Cor da lente","frame","wottica_frame_corlente","product"),
              ("Formato da armação","frame","wottica_frame_formato","product"),
              ("Gênero","frame","wottica_frame_genero","product"),
              ("Peso","frame","wottica_frame_peso","product"),
              ("Material da armação","frame","wottica_frame_material","product"),
              ("Material da haste","frame","wottica_frame_materialhaste","product"),
              ("Altura da lente","frame","wottica_frame_altura","product"),
              ("Largura da lente","frame","wottica_frame_largura","product"),
              ("Largura da ponte","frame","wottica_frame_larguraponte","product"),
              ("Largura da frontal","frame","wottica_frame_largurafrontal","product"),
              ("Comprimento da haste","frame","wottica_frame_comprimento","product"),
              ("Tipo de apoio","frame","wottica_frame_apoio","product")';
            $wpdb->query($sql);

            $sql = 'CREATE TABLE `wottica_taxonomy_itens` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(191),
              `taxonomy_id` int(11),
              `status` TINYINT(1) DEFAULT 1,
              `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            $wpdb->query($sql);
        }

        public static function plugin_deactivation()
        {
        }

        public static function plugin_scripts()
        {
            wp_enqueue_style('lki-style', plugins_url('assets/css/style.css', __FILE__));
            wp_enqueue_script('lki-script', plugins_url('assets/js/script.js', __FILE__), ['jquery']);
            wp_localize_script('lki-script', 'localizeObj', ['user' => wp_get_current_user()]);
        }

        public static function admin_plugin_scripts()
        {
            wp_enqueue_style('lki-admin-style', plugins_url('assets/css/adm_style.css', __FILE__));
            wp_enqueue_script('lki-admin-script', plugins_url('assets/js/adm_script.js', __FILE__), ['jquery']);
        }

        private function includes()
        {
            include_once 'includes/class-wc-wottica-admin-product.php';
            include_once 'includes/class-wc-wottica-admin-taxonomy.php';
            include_once 'includes/class-wc-wottica-api.php';
        }
    }

    register_activation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_activation']);
    register_deactivation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_deactivation']);

    add_action('plugins_loaded', ['LKI_Wottica_Plugin', 'get_instance']);
}
