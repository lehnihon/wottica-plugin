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
              `identifier_extra` varchar(191),
              `location` varchar(25),
              `data_type` varchar(25),
              `data_input` varchar(25),
              `status` TINYINT(1) DEFAULT 1,
              `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            $wpdb->query($sql);

            $sql = 'INSERT INTO `wottica_taxonomy` (name, type, identifier, location, data_type, data_input, identifier_extra) VALUES
              ("Esferico de","lens","_wottica_lens_esferico_de","product","number","select", ""), 
              ("Esferico até","lens","_wottica_lens_esferico_ate","product","number","select", ""),
              ("Cilindrico de","lens","_wottica_lens_cilindrico_de","product","number","select", ""), 
              ("Cilindrico até","lens","_wottica_lens_cilindrico_ate","product","number","select", ""),
              ("Adição de","lens","_wottica_lens_adicao_de","product","number","select", ""), 
              ("Adição até","lens","_wottica_lens_adicao_ate","product","number","select", ""),
              ("Material","lens","_wottica_lens_material","product","string","select", ""),
              ("Disponibilidade","lens","_wottica_lens_disponibilidade","product","string","select", ""),
              ("Marca","lens","_wottica_lens_marca","product","string","select file", "_wottica_lens_marca_file"),
              ("Natureza de lente","lens","_wottica_lens_natureza","product","string","select", ""),
              ("Qualidade","lens","_wottica_lens_qualidade","product","string","select", ""),
              ("Aplicação","lens","_wottica_lens_aplicacao","product","string","select", ""),
              ("Cor da lente","frame","_wottica_frame_corlente","product","string","select", ""),
              ("Formato da armação","frame","_wottica_frame_formato","product","string","select", ""),
              ("Gênero","frame","_wottica_frame_genero","product","string","select", ""),
              ("Peso","frame","_wottica_frame_peso","product","number","text", ""),
              ("Material da armação","frame","_wottica_frame_material","product","string","select", ""),
              ("Material da haste","frame","_wottica_frame_materialhaste","product","string","select", ""),
              ("Altura da lente","frame","_wottica_frame_altura","product","number","text", ""),
              ("Largura da lente","frame","_wottica_frame_largura","product","number","text", ""),
              ("Largura da ponte","frame","_wottica_frame_larguraponte","product","number","text", ""),
              ("Largura da frontal","frame","_wottica_frame_largurafrontal","product","number","text", ""),
              ("Comprimento da haste","frame","_wottica_frame_comprimento","product","number","text", ""),
              ("Tipo de apoio","frame","_wottica_frame_apoio","product","string","select", "")';
            $wpdb->query($sql);

            $sql = 'CREATE TABLE `wottica_taxonomy_itens` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `value` varchar(191),
              `taxonomy_id` int(11),
              `status` TINYINT(1) DEFAULT 1,
              `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            $wpdb->query($sql);

            $esferico_de = LKI_Wottica_Plugin::get_items_taxonomy(-30, 20, 0.25, 1);
            $esferico_ate = LKI_Wottica_Plugin::get_items_taxonomy(-30, 20, 0.25, 2);
            $cilindrico_de = LKI_Wottica_Plugin::get_items_taxonomy(-10, 0, 0.25, 3);
            $cilindrico_ate = LKI_Wottica_Plugin::get_items_taxonomy(-10, 0, 0.25, 4);
            $adicao_de = LKI_Wottica_Plugin::get_items_taxonomy(-0.75, 3.5, 0.25, 5);
            $adicao_ate = substr(LKI_Wottica_Plugin::get_items_taxonomy(-0.75, 3.5, 0.25, 6), 0, -1);
            $sql = "INSERT INTO `wottica_taxonomy_itens` (value,taxonomy_id) VALUES
              $esferico_de
              $esferico_ate
              $cilindrico_de
              $cilindrico_ate
              $adicao_de
              $adicao_ate
            ";
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

        public static function get_items_taxonomy($min = 0, $max = 10, $add = 0.25, $taxonomy)
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
            include_once 'includes/class-wc-wottica-admin-product.php';
            include_once 'includes/class-wc-wottica-admin-taxonomy.php';
            include_once 'includes/class-wc-wottica-api.php';
            include_once 'includes/class-wc-wottica-product.php';
        }
    }

    register_activation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_activation']);
    register_deactivation_hook(__FILE__, ['LKI_Wottica_Plugin', 'plugin_deactivation']);

    add_action('plugins_loaded', ['LKI_Wottica_Plugin', 'get_instance']);
}
