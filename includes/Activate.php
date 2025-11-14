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


      /**POINTS EVENT TABLE **/
      $tabela_st_event_store =  $wpdb->prefix . 'st_event_store';
      $sql_tabela_log_event =
         "CREATE TABLE $tabela_st_event_store (
         id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
         event_id CHAR(36) NOT NULL, -- UUID v4
         aggregate_type VARCHAR(50) NOT NULL, -- e.g. 'user_points'
         aggregate_id BIGINT UNSIGNED NOT NULL, -- user_id
         event_type VARCHAR(100) NOT NULL, -- e.g. 'PointsCredited', 'PointsConsumed'
         payload JSON NOT NULL,
         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
         created_user_id BIGINT UNSIGNED NOT NULL,
         metadata JSON NULL, -- { command_id, actor_id, ip, source, reason }
         version INT NOT NULL DEFAULT 1, -- aggregate version at append time
         UNIQUE KEY uq_event_eventid (event_id),
         KEY idx_aggregate (aggregate_type, aggregate_id),
         KEY idx_created_at (created_at)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

      dbDelta($sql_tabela_log_event);

      $tabela_st_points_balance =  $wpdb->prefix . 'st_points_balance';
      $sql_tabela_points_balance =
         "CREATE TABLE $tabela_st_points_balance (
            user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
            available_points INT NOT NULL DEFAULT 0,
            reserved_points INT NOT NULL DEFAULT 0, -- se necessário (ex: reservas temporárias)
            total_earned INT NOT NULL DEFAULT 0,
            total_spent INT NOT NULL DEFAULT 0,
            total_expired INT NOT NULL DEFAULT 0,
            last_event_id CHAR(36) NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

      dbDelta($sql_tabela_points_balance);

      $tabela_st_points_batches =  $wpdb->prefix . 'st_points_batches';
      $sql_tabela_points_batches =
         "CREATE TABLE $tabela_st_points_batches (
         batch_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
         user_id BIGINT UNSIGNED NOT NULL,
         origin_event_id CHAR(36) NOT NULL, -- id do evento que criou este batch
         points_total INT NOT NULL,
         points_remaining INT NOT NULL,
         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
         expires_at DATETIME NULL,
         created_user_id BIGINT UNSIGNED NOT NULL,
         status ENUM('active','expired','consumed','adjusted') NOT NULL DEFAULT 'active',
         metadata JSON NULL,
         KEY idx_user_expires (user_id, expires_at),
         KEY idx_user_status (user_id, status)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

      dbDelta($sql_tabela_points_batches);

      $tabela_st_points_transactions =  $wpdb->prefix . 'st_points_transactions';
      $sql_tabela_points_transactions =
         "CREATE TABLE $tabela_st_points_transactions (
         txn_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
         user_id BIGINT UNSIGNED NOT NULL,
         event_id CHAR(36) NOT NULL, -- referência ao event_store
         type VARCHAR(50) NOT NULL, -- 'credit','consume','expire','admin_adjust','compensation'
         amount INT NOT NULL, -- positivo para créditos, negativo para débitos
         balance_after INT NOT NULL, -- auxílio para auditoria
         related_resource VARCHAR(255) NULL, -- ex: order_id, usage_id
         note TEXT NULL,
         metadata JSON NULL,
         batch_afected JSON NULL,
         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
         created_user_id BIGINT UNSIGNED NOT NULL,
         KEY idx_user_created (user_id, created_at),
         KEY idx_event (event_id)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

      dbDelta($sql_tabela_points_transactions);


      $tabela_st_audit_logs =  $wpdb->prefix . 'st_audit_logs';
      $sql_tabela_st_audit_logs =
         "CREATE TABLE $tabela_st_audit_logs (
         id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
         actor_id BIGINT UNSIGNED NULL,
         action VARCHAR(100) NOT NULL,
         target_type VARCHAR(50) NULL,
         target_id BIGINT UNSIGNED NULL,
         details JSON NULL,
         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
         created_user_id BIGINT UNSIGNED NOT NULL,
         KEY idx_actor (actor_id)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

      dbDelta($sql_tabela_st_audit_logs);
   }
}
