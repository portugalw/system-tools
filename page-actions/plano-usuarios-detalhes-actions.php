<?php

if (!defined('ABSPATH')) exit;

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

//add_action('wp_ajax_nopriv_get_client_details', 'st_get_client_details');
add_action('wp_ajax_get_client_details', 'st_get_client_details');

add_action('wp_ajax_get_transactions', 'st_get_transactions');


add_action('wp_ajax_nopriv_get_transactions_from_logged_user', 'st_get_transactions_from_logged_user');
//add_action('wp_ajax_nopriv_get_transactions', 'st_get_transactions');


add_action('wp_ajax_st_update_points', 'st_update_points');

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

function st_get_transactions_from_logged_user()
{

   $rows = getTransactions(get_current_user_id());

   wp_send_json_success($rows);
}


function st_get_transactions()
{

   if (!current_user_can('manage_options')) {
      wp_send_json_error('Sem permissão', 403);
   }

   check_ajax_referer('st_ajax_nonce');



   $user_id = intval($_GET['user_id'] ?? 0);
   if (!$user_id) {
      wp_send_json_error('Usuário inválido', 400);
   }

   $rows = getTransactions($user_id);

   wp_send_json_success($rows);
}

function getTransactions($user_id)
{
   global $wpdb;
   $tb_transactions = $wpdb->prefix . 'st_points_transactions';
   $tb_event_store = $wpdb->prefix . 'st_event_store';

   $rows = $wpdb->get_results(
      $wpdb->prepare("
            SELECT t.txn_id, t.user_id, t.type, t.note, t.related_resource, t.created_at, t.amount
            FROM $tb_transactions t
            WHERE user_id = %d
            ORDER BY created_at DESC
            LIMIT 50
        ", $user_id)
   );
}


function st_update_points()
{

   if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Sem permissão'], 403);
   }

   check_ajax_referer('st_ajax_nonce');

   global $wpdb;

   $user_id  = intval($_POST['user_id'] ?? 0);
   $amount   = intval($_POST['amount'] ?? 0);
   $days_expire = intval($_POST['days_expire'] ?? 30);
   $note     = sanitize_textarea_field($_POST['note'] ?? '');
   $operation = sanitize_text_field($_POST['operation'] ?? '');

   if (!$user_id || $amount <= 0 || empty($note)) {
      wp_send_json_error(['message' => 'Dados inválidos.']);
   }

   if (!in_array($operation, ['add', 'remove'], true)) {
      wp_send_json_error(['message' => 'Operação inválida.']);
   }

   if ($operation === 'remove') {
      $amount = -abs($amount);
   }

   try {
      $service = new EventStoreService($wpdb);

      if ($operation === 'add') {
         $result = $service->handle_add_points_admin(
            $user_id,
            $amount,
            $days_expire,
            $note
         );
      } else {
         $result = $service->handle_expire_points($user_id, null);
      }

      if (!is_array($result) || !isset($result['success'])) {
         error_log('[st_update_points] Resposta inválida do service');
         wp_send_json_error([
            'message' => 'Resposta inválida do servidor.'
         ]);
      }

      if ($result['success'] === true) {
         wp_send_json_success([
            'message' => $result['message'] ?? 'Pontos atualizados com sucesso.'
         ]);
      }

      wp_send_json_error([
         'message' => $result['message'] ?? 'Falha ao atualizar pontos.'
      ]);
   } catch (Throwable $t) {
      error_log('[st_update_points] Erro: ' . $t->getMessage());

      wp_send_json_error([
         'message' => 'Erro inesperado: ' . $t->getMessage()
      ]);
   }
}
