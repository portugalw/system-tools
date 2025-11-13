<?php

namespace SystemToolsHelpInfancia;

defined('ABSPATH') || exit;

class Activate
{
   protected function __construct() {}

   public static function activate()
   {
      self::install();
      flush_rewrite_rules();
      echo "Ativei o Plugin";
   }


   private static function install()
   {

      global $wpdb;


      $tabela_request_logs = $wpdb->prefix . 'request_logs';
      $tabela_log_event = $wpdb->prefix . 'log_event';

      // Pega o conjunto de caracteres padrão do banco de dados
      $charset_collate = $wpdb->get_charset_collate();

      // Carrega o arquivo 'upgrade.php' para ter acesso à função dbDelta()
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      $sql_tabela_request_logs = "CREATE TABLE $tabela_request_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT current_timestamp() NOT NULL,
        request_body longtext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

      dbDelta($sql_tabela_request_logs);

      // --- SQL para Tabela 2 ---
      // (Defina suas colunas aqui)
      $sql_tabela_log_event = "CREATE TABLE $tabela_log_event (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        date datetime DEFAULT current_timestamp() NOT NULL,
        event longtext NOT NULL,
        description longtext NOT NULL,
        customer_email varchar(150) NULL,
        origin varchar(400) NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

      // Executa o dbDelta para a Tabela 2
      dbDelta($sql_tabela_log_event);
   }
}
