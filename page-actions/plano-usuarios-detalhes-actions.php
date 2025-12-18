<?php

if (!defined('ABSPATH')) exit;

//add_action('wp_ajax_nopriv_get_client_details', 'st_get_client_details');
add_action('wp_ajax_get_client_details', 'st_get_client_details');
//add_action('wp_ajax_nopriv_get_transactions', 'st_get_transactions');
add_action('wp_ajax_get_transactions', 'st_get_transactions');

function st_get_client_details()
{

   //wp_send_json_success('OK');

   // 🔐 Segurança
   if (!current_user_can('manage_options')) {
      wp_send_json_error('Sem permissão', 403);
   }

   // 🔐 Valida nonce
   check_ajax_referer('st_ajax_nonce');
   //
   global $wpdb;

   $user_id = intval($_GET['user_id'] ?? 0);
   if (!$user_id) {
      wp_send_json_error('Usuário inválido', 400);
   }

   $tb_balance = $wpdb->prefix . 'st_points_balance';
   $tb_batches = $wpdb->prefix . 'st_points_batches';

   $balance = $wpdb->get_row(
      $wpdb->prepare("SELECT * FROM $tb_balance WHERE user_id = %d", $user_id)
   );

   $expiring = $wpdb->get_row(
      $wpdb->prepare("
            SELECT SUM(points_remaining) AS total, MIN(expires_at) AS next_date
            FROM $tb_batches
            WHERE user_id = %d
              AND status = 'active'
              AND points_remaining > 0
              AND expires_at >= NOW()
        ", $user_id)
   );

   wp_send_json_success([
      'balance'          => (int) ($balance->available_points ?? 0),
      'total_earned'     => (int) ($balance->total_earned ?? 0),
      'expiring_amount'  => (int) ($expiring->total ?? 0),
      'expiring_date'    => $expiring->next_date
         ? date('d/m/Y', strtotime($expiring->next_date))
         : '-',
   ]);
}


function st_get_transactions()
{

   if (!current_user_can('manage_options')) {
      wp_send_json_error('Sem permissão', 403);
   }

   check_ajax_referer('st_ajax_nonce');

   global $wpdb;

   $user_id = intval($_GET['user_id'] ?? 0);
   if (!$user_id) {
      wp_send_json_error('Usuário inválido', 400);
   }

   $tb_transactions = $wpdb->prefix . 'st_points_transactions';

   $rows = $wpdb->get_results(
      $wpdb->prepare("
            SELECT *
            FROM $tb_transactions
            WHERE user_id = %d
            ORDER BY created_at DESC
            LIMIT 50
        ", $user_id)
   );

   wp_send_json_success($rows);
}
