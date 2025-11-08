<?php

namespace SystemToolsHelpInfancia;

use SystemToolsHelpInfancia\Public\ApiUser;
use SystemToolsHelpInfancia\Activate;
use SystemToolsHelpInfancia\Deactivate;

defined('ABSPATH') || exit;

define('ST_PLUGIN_STYLE_CSS', untrailingslashit(plugins_url('/assets/system-tools-style.css', ST_PLUGIN_FILE)));
define('ST_PLUGIN_SCRIPT_JS', untrailingslashit(plugins_url('/assets/system-tools-script.js', ST_PLUGIN_FILE)));


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
      wp_enqueue_style('mypluginstyle', ST_PLUGIN_STYLE_CSS);
      wp_enqueue_script('mypluginscript', ST_PLUGIN_SCRIPT_JS);
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
      add_menu_page('Help Infância Tool', 'Help Infância Tool', 'manage_options', 'system_tools', array($this, 'admin_page_index_callback'), 'dashicons-store', 110);

      add_submenu_page(
         'system_tools',
         'Template de Email',
         'Template de Email',
         'manage_options',
         'mpg-template-email',
         array($this, 'render_template_email_page') // Callback
      );
      add_submenu_page(
         'system_tools',
         'Template de Email',
         'Template de Email',
         'manage_options',
         'mpg-template-email',
         array($this, 'render_template_email_page') // Callback
      );
   }

   function admin_page_index_callback()
   {
      require_once ST_PAGE_ADMIN_INDEX;
   }
   function render_template_email_page()
   {
      require_once ST_PAGE_ADMIN_CADASTRO_TEMPLATE_EMAIL;
   }

   function custom_post_type()
   {
      //register_post_type('book', ['public' => true, 'label' => 'BOOKS']);
   }
}
