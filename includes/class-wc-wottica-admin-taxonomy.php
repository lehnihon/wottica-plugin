<?php

defined('ABSPATH') || exit;

/**
 * Admin taxonomy.
 */
class WC_Wottica_Admin_Taxonomy
{
    public function __construct()
    {
        add_action('admin_menu', [$this,  'custom_attribute_page']);
    }

    public function custom_attribute_page()
    {
        add_menu_page('Taxonomias do produto', 'Taxonomias', 'manage_options', 'wottica_custom_attribute', [$this, 'custom_attribute_page_content'], 'dashicons-book-alt', 57);
    }

    public function custom_attribute_page_content()
    {
        $this->custom_attribute_page_content_post(); ?>
        <div class="wrap"> 
          <?php if (!empty($_GET['action']) && $_GET['action'] == 'new') {
            $this->custom_attribute_page_content_new();
        } elseif (!empty($_GET['action']) && $_GET['action'] == 'add') {
            $this->custom_attribute_page_content_add();
        } else {
            $this->custom_attribute_page_content_table();
        } ?>
          
      </div>
    <?php
    }

    private function custom_attribute_page_content_post()
    {
        global $wpdb;
        if (!empty($_POST['createtaxonomy'])) {
            check_admin_referer('attributes-wottica', '_wpnonce_attributes-wottica');

            $name = $_POST['name'];
            $identifier = $_POST['identifier'];
            $type = $_POST['type'];
            $location = $_POST['location'];

            $wpdb->insert('wottica_taxonomy', [
              'name' => $name,
              'identifier' => $identifier,
              'type' => $type,
              'location' => $location,
            ]);

            wp_redirect(admin_url('admin.php?page=wottica_custom_attribute'));
            exit;
        }

        if (!empty($_POST['createtaxonomyitem'])) {
            check_admin_referer('attributes-item-wottica', '_wpnonce_attributes-item-wottica');

            $name = $_POST['name'];
            $taxonomyId = $_GET['taxonomyId'];

            $wpdb->insert('wottica_taxonomy_itens', [
              'value' => $name,
              'taxonomy_id' => $taxonomyId,
            ]);

            wp_redirect(admin_url('admin.php?page=wottica_custom_attribute&action=add&taxonomyId='.$taxonomyId));
            exit;
        }
        if (!empty($_GET['action']) && $_GET['action'] == 'delete') {
            $wpdb->delete('wottica_taxonomy_itens', ['id' => $_GET['itemId']]);
            wp_redirect(admin_url('admin.php?page=wottica_custom_attribute&action=add&taxonomyId='.$taxonomyId));
            exit;
        }
    }

    private function custom_attribute_page_content_table()
    {
        global $wpdb;
        $result = $wpdb->get_results(
          'SELECT *
            FROM wottica_taxonomy
            ORDER BY id DESC',
            ARRAY_A
        ); ?>
          <h1 class="wp-heading-inline">Taxonomias</h1>
          <a href="<?php echo admin_url(sprintf('admin.php?%s', http_build_query($_GET))).'&action=new'; ?>" class="page-title-action">Adicionar novo</a>  
          <table class="widefat fixed" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">Nome</th>
                  <th scope="col">Tipo</th>
                  <th scope="col">Localização</th> 
                  <th scope="col" style="width:50px"></th> 
                </tr>
              </thead>
              <tbody>
                  <?php
                  foreach ($result as $index => $row) {
                      ?>
                  <tr <?php if ($index % 2 == 0) {
                          echo 'class="alternate"';
                      } ?>> 
                      <th scope="row"><?php echo $row['name']; ?></th>
                      <td ><?php echo $row['type']; ?></td>
                      <td><?php echo $row['location']; ?></td>
                      <td style="width:50px">
                        <a href="<?php echo admin_url(sprintf('admin.php?%s', http_build_query($_GET))).'&action=add&taxonomyId='.$row['id']; ?>" >
                          <span class="dashicons dashicons-plus-alt"></span>
                        </a>  
                      </td>
                  </tr>
                  <?php
                  } ?>
              </tbody>
          </table>
        <?php
    }

    private function custom_attribute_page_content_add()
    {
        global $wpdb;
        $taxonomyId = $_GET['taxonomyId'];
        $result = $wpdb->get_results(
        $wpdb->prepare('SELECT *
          FROM wottica_taxonomy_itens
          WHERE taxonomy_id = %d
          ORDER BY id DESC', $taxonomyId),
          ARRAY_A
      ); ?>
          <h1 class="wp-heading-inline">Adicionar Items</h1>
          <a href="<?php echo admin_url('admin.php?page=wottica_custom_attribute'); ?>" class="page-title-action">Voltar</a>  
          <form action="" method="post" name="attributes-wottica" id="attributes-wottica" >
            <input name="action" type="hidden" value="adduser" />
            <?php wp_nonce_field('attributes-item-wottica', '_wpnonce_attributes-item-wottica'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="item_name"><?php _e('Nome'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                    <td><input name="name" required type="text" id="item_name" class="regular-text"/></td>
                </tr>
            </table>
            <?php submit_button(__('Criar item'), 'primary', 'createtaxonomyitem', true, ['id' => 'createtaxonomyitemsub']); ?>
            
          </form>
          <br>
          <table class="widefat fixed" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">Nome</th>
                  <th scope="col" style="width:50px"></th> 
                </tr>
              </thead>
              <tbody>
                  <?php
                  foreach ($result as $index => $row) {
                      ?>
                  <tr <?php if ($index % 2 == 0) {
                          echo 'class="alternate"';
                      } ?>> 
                      <th scope="row"><?php echo $row['value']; ?></th>
                      <td style="width:50px">
                        <a href="<?php echo admin_url(sprintf('admin.php?%s', http_build_query($_GET))).'&action=delete&itemId='.$row['id']; ?>" >
                          <span class="dashicons dashicons-remove"></span>
                        </a>  
                      </td>
                  </tr>
                  <?php
                  } ?>
              </tbody>
          </table>
    <?php
    }

    private function custom_attribute_page_content_new()
    {
        global $wpdb; ?>
          <h1 class="wp-heading-inline">Criar Taxonomia</h1>
          <a href="<?php echo admin_url('admin.php?page=wottica_custom_attribute'); ?>" class="page-title-action">Voltar</a>  
          <form action="" method="post" name="attributes-wottica" id="attributes-wottica" >
            <input name="action" type="hidden" value="adduser" />
            <?php wp_nonce_field('attributes-wottica', '_wpnonce_attributes-wottica'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="taxonomy_name"><?php _e('Nome'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                    <td><input name="name" required type="text" id="taxonomy_name" class="regular-text"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="taxonomy_identifier"><?php _e('Identificador'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                    <td><input name="identifier" required type="text" id="taxonomy_identifier" class="regular-text"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="taxonomy_type"><?php _e('Tipo'); ?> </th>
                    <td>
                      <select name="type" id="taxonomy_type">
                        <option value="frame">Armações</option>
                        <option value="lens">Lentes</option>
                      </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="taxonomy_location"><?php _e('Localização'); ?> </th>
                    <td>
                      <select name="location" id="taxonomy_location">
                        <option value="product">Produto</option>
                        <option value="variation">Variação</option>
                      </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Criar taxonomia'), 'primary', 'createtaxonomy', true, ['id' => 'createtaxonomysub']); ?>
            
          </form>
    <?php
    }
}

new WC_Wottica_Admin_Taxonomy();
