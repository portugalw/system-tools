<?php

if (!defined('ABSPATH')) exit;

use SystemToolsHelpInfancia\Core\Services\PlanConfigService;

add_action('wp_ajax_st_save_plan_config', 'st_save_plan_config');
add_action('wp_ajax_st_get_plan_config_details', 'st_get_plan_config_details');


function st_save_plan_config()
{

   if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Sem permissão'], 403);
   }

   check_ajax_referer('st_ajax_nonce');

   global $wpdb;

   $points   = intval($_POST['points'] ?? 1);
   $days_expire = intval($_POST['days_expire'] ?? 30);
   $plan_arm_id = intval($_POST['plan_id']);
   $is_active  = intval($_POST['is_active']);


   if ($points <= 0) {
      wp_send_json_error(['message' => 'Dados inválidos. Conceder ao menos 1 ponto.']);
   }

   if ($days_expire <= 0) {
      wp_send_json_error(['message' => 'Dados inválidos. Conceder ao menos 1 dia']);
   }

   try {
      $service = new PlanConfigService($wpdb);

      $result = $service->save(
         $plan_arm_id,
         $points,
         $days_expire,
         $is_active
      );


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



function st_get_plan_config_details()
{



   // 🔐 Segurança
   if (!current_user_can('manage_options')) {
      wp_send_json_error('Sem permissão', 403);
   }

   // 🔐 Valida nonce
   check_ajax_referer('st_ajax_nonce');
   //
   global $wpdb;

   $plan_arm_id = intval($_GET['armPlanId']);
   if (!$plan_arm_id) {
      wp_send_json_error('Plano do ARM inválido', 400);
   }

   $service = new PlanConfigService($wpdb);

   $result = $service->getPlanConfigDetailsByArmPlanId($plan_arm_id);

   wp_send_json_success([
      'plan_id'               => (int) $result->plan_id,
      'plan_name'             => $result->plan_name,
      'points'                => (int) $result->points,
      'days_expire'           => (int) $result->days_expire,
      'is_active'             => (int) $result->is_active,
      'updated_at'            => $result->updated_at,
      'updated_at_formatado'  => date('d/m/Y H:i', strtotime($result->updated_at)),
   ]);
}
