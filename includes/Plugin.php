<?php

namespace SystemToolsHelpInfancia;

use SystemToolsHelpInfancia\Public\ApiUser;
use SystemToolsHelpInfancia\Activate;
use SystemToolsHelpInfancia\Deactivate;

defined('ABSPATH') || exit;

define('ST_PLUGIN_STYLE_CSS', untrailingslashit(plugins_url('/assets/css/system-tools-style.css', ST_PLUGIN_FILE)));
define('ST_PLUGIN_STYLE_ADMIN_LOGS_CSS', untrailingslashit(plugins_url('/assets/css/system-tools-admin-logs.css', ST_PLUGIN_FILE)));
define('ST_PLUGIN_STYLE_PLANO_USUARIO_CSS', untrailingslashit(plugins_url('/assets/css/plano-usuario.css', ST_PLUGIN_FILE)));


define('ST_PLUGIN_SCRIPT_JS', untrailingslashit(plugins_url('/assets/js/system-tools-script.js', ST_PLUGIN_FILE)));
define('ST_PLUGIN_SCRIPT_PLANO_USUARIO_JS', untrailingslashit(plugins_url('/assets/js/plano-usuario.js', ST_PLUGIN_FILE)));
define('ST_PLUGIN_SCRIPT_PLANO_CONFIGURACAO_VINCULO_JS', untrailingslashit(plugins_url('/assets/js/plano-configuracao-vinculo.js', ST_PLUGIN_FILE)));



final class Plugin
{

   private static $_instance = null;
   public $plugin_name;

   protected function __construct()
   {
      add_action('init', array($this, 'custom_post_type'));
      $this->plugin_name = ST_PLUGIN_NAME;
   }

   public function register()
   {
      add_action('admin_enqueue_scripts', array($this, 'enqueue'));
      //add_action('wp_enqueue_scripts', array($this, 'enqueue')); // FRONTEND

      add_action('admin_menu', array($this, 'add_admin_pages'));


      //add_filter("plugin_action_links", array($this, 'settings_link'));
      ///add_filter('plugin_action_links', 'settings_link', 10, 2);

      new ApiUser();
   }

   protected function __clone() {}

   public function __wakeup() {}

   public static function getInstance(): ?Plugin
   {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }

   public function init() {}

   public function activate()
   {
      Activate::activate();
      $this->custom_post_type();
   }
   public function deactivate()
   {
      Deactivate::deactivate();
   }

   public function uninstall()
   {
      echo 'uninstalled';
   }

   function enqueue()
   {

      // Bootstrap CSS
      wp_enqueue_style(
         'bootstrap-css',
         'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
         [],
         '5.3.0'
      );

      // Bootstrap JS (IMPORTANTE!)
      wp_enqueue_script(
         'bootstrap-js',
         'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
         [],
         '5.3.0',
         true
      );


      wp_enqueue_style('mypluginstyle', ST_PLUGIN_STYLE_CSS);
      wp_enqueue_style('stpluginstyleadminlogscss', ST_PLUGIN_STYLE_ADMIN_LOGS_CSS);
      wp_enqueue_script('mypluginscript', ST_PLUGIN_SCRIPT_JS);


      wp_localize_script('mypluginscript', 'ST_AJAX', [
         'url'   => admin_url('admin-ajax.php'),
         'nonce' => wp_create_nonce('st_ajax_nonce')
      ]);


      wp_enqueue_script('st-admin-plano_usuario', ST_PLUGIN_SCRIPT_PLANO_USUARIO_JS,   ['mypluginscript', 'bootstrap-js', 'jquery'],);
      wp_enqueue_script('st-admin-plano_configuracao_vinculo', ST_PLUGIN_SCRIPT_PLANO_CONFIGURACAO_VINCULO_JS,   ['mypluginscript', 'bootstrap-js', 'jquery'],);




      wp_enqueue_style(
         'st-admin-plano_usuario-css',
         ST_PLUGIN_STYLE_PLANO_USUARIO_CSS,
         [],
         '1.0.0'
      );

      wp_enqueue_style(
         'google-roboto',
         'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
         [],
         null
      );
   }

   function settings_link($plugin_actions, $plugin_file)
   {
      echo $plugin_actions;
      echo $plugin_file;
      // $settings_link = '<a href="options-general.php?page=landing_pages_tools">Settings</a>';
      //array_push($links, $settings_link);
   }

   function add_admin_pages()
   {
      add_menu_page('Help Infância Tool', 'Help Infância Tool', 'manage_options', 'system_tools', array($this, 'admin_page_index_callback'), 'dashicons-admin-generic', 110);

      add_submenu_page(
         'system_tools',
         'Log de Eventos',
         'Log de Eventos',
         'manage_options',
         'st-event-log',
         array($this, 'admin_page_event_log_callback') // Callback
      );
      add_submenu_page(
         'system_tools',
         'Request Logs',
         'Request Logs',
         'manage_options',
         'st-request-log',
         array($this, 'admin_page_request_log_callback') // Callback
      );

      add_submenu_page(
         'system_tools',
         'Registrar Compra de Plano',
         'Compra de Plano',
         'manage_options',
         'purchase-plan-screen',
         array($this, 'admin_page_purchase_user_plan_callback'), // Callback
         97,
         'dashicons-tickets-alt'

      );

      add_submenu_page(
         'system_tools',
         'Registrar Débito Pontos',
         'Débito de Pontos',
         'manage_options',
         'debit-plan-screen',
         array($this, 'admin_page_usuario_debito_pontos_callback'), // Callback
         98,
         'dashicons-tickets-alt'
      );

      add_submenu_page(
         'system_tools',
         'Expirar Pontos',
         'Expirar Pontos',
         'manage_options',
         'expire-plan-screen',
         array($this, 'admin_page_usuario_expirar_pontos_callback'), // Callback
         99,
         'dashicons-tickets-alt'
      );

      add_submenu_page(
         'system_tools',
         'Configurar Plano',
         'Configurar Plano',
         'manage_options',
         'plan-config-attach',
         array($this, 'admin_page_plan_config_attach_callback'), // Callback
         39,
         'dashicons-tickets-alt'
      );

      add_submenu_page(
         'system_tools',
         'Lista de Usuários',
         'Lista de Usuários',
         'manage_options',
         'list-users-details',
         array($this, 'admin_page_list_user_plan_details_callback'), // Callback
         40,
         'dashicons-tickets-alt'
      );
   }

   function admin_page_index_callback()
   {
      require_once ST_PAGE_ADMIN_INDEX;
   }

   function admin_page_event_log_callback()
   {
      require_once ST_PAGE_ADMIN_EVENT_LOG;
   }

   function admin_page_request_log_callback()
   {
      require_once ST_PAGE_ADMIN_REQUEST_LOG;
   }

   function admin_page_usuario_debito_pontos_callback()
   {
      require_once ST_PAGE_ADMIN_DEBITO_PONTOS_USUARIO;
   }
   function admin_page_usuario_expirar_pontos_callback()
   {
      require_once ST_PAGE_ADMIN_EXPIRAR_PONTOS_USUARIO;
   }
   function admin_page_purchase_user_plan_callback()
   {
      require_once ST_PAGE_ADMIN_CADASTRO_PLANO_USUARIO;
   }

   function admin_page_list_user_plan_details_callback()
   {
      require_once ST_PAGE_ADMIN_PLANO_USUARIOS_DETALHES;
   }

   function admin_page_plan_config_attach_callback()
   {
      require_once ST_PAGE_ADMIN_PLANO_CONFIGURACAO_VINCULO;
   }





   function custom_post_type()
   {
      //register_post_type('book', ['public' => true, 'label' => 'BOOKS']);
   }
}
