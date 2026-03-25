<?php

if (!defined('ABSPATH')) exit;

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

//ADMIN

add_action('wp_ajax_get_client_details', 'st_get_client_details');
add_action('wp_ajax_get_transactions', 'st_get_transactions');

// USUARIO LOGADO
add_action('wp_ajax_get_client_details_from_logged_user', 'st_get_client_details_from_logged_user');
add_action('wp_ajax_get_transactions_from_logged_user', 'st_get_transactions_from_logged_user');
add_action('wp_ajax_get_active_batch_points_with_expiration_from_logged_user', 'st_get_active_batch_points_with_expiration_from_logged_user');


add_action('wp_ajax_st_update_points', 'st_update_points');


function st_get_client_details_from_logged_user()
{

   check_ajax_referer('st_ajax_nonce');

   $user_id = get_current_user_id();

   getClientDetails($user_id);
}
function st_get_client_details()
{
   if (!current_user_can('manage_options')) {
      wp_send_json_error('Sem permissão', 403);
   }

   check_ajax_referer('st_ajax_nonce');

   $user_id = intval($_GET['user_id'] ?? 0);

   getClientDetails($user_id);
}

function getClientDetails($user_id)
{
   global $wpdb;
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
            SELECT 
               SUM(points_remaining) AS total,
               MIN(expires_at) AS next_date,
               SUM(CASE 
                        WHEN DATE( expires_at) = (
                           SELECT DATE(MIN(expires_at))
                           FROM $tb_batches
                           WHERE user_id = %d
                              AND status = 'active'
                              AND points_remaining > 0
                              AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 31 DAY)
                        )
                        THEN points_remaining
                        ELSE 0
                  END) AS points_expiring_first
            FROM $tb_batches
            WHERE user_id = %d
            AND status = 'active'
            AND points_remaining > 0
            AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 31 DAY)
        ", $user_id, $user_id)
   );

   wp_send_json_success([
      'balance'          => (int) ($balance->available_points ?? 0),
      'total_earned'     => (int) ($balance->total_earned ?? 0),
      'expiring_amount'  => (int) ($expiring->total ?? 0),
      'points_expiring_first' => $expiring->points_expiring_first,
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

   return $wpdb->get_results(
      $wpdb->prepare("
            SELECT t.txn_id, t.user_id, t.type, t.note, t.related_resource, t.created_at, t.amount
            FROM $tb_transactions t
            WHERE user_id = %d
            ORDER BY created_at DESC
            LIMIT 50
        ", $user_id)
   );
}

function st_get_active_batch_points_with_expiration_from_logged_user()
{

   $rows = getActiveBatchPointsWithExpiration(get_current_user_id());

   wp_send_json_success($rows);
}

function getActiveBatchPointsWithExpiration($user_id)
{
   global $wpdb;
   $tb_points_batches = $wpdb->prefix . 'st_points_batches';

   return $wpdb->get_results(
      $wpdb->prepare("
             SELECT 
         points_total,
         points_remaining,
         created_at,
         expires_at
            FROM $tb_points_batches
            WHERE user_id = %d 
            AND status = 'active'
            AND points_remaining > 0
            order by expires_at asc
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
