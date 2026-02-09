<?php

namespace SystemToolsHelpInfancia\Core\Services;

use SystemToolsHelpInfancia\Core\Repositories\PlanConfigRepository;

if (!defined('ABSPATH')) exit;

class PlanConfigService
{

   private $wpdb;

   public function __construct(\wpdb $wpdb)

   {
      $this->wpdb = $wpdb;
   }

   public function getPlanConfigDetailsByArmPlanId($plan_arm_id)
   {
      $planConfigRepository = new PlanConfigRepository($this->wpdb);

      return $planConfigRepository->getPlanConfigDetailsByArmPlanId($plan_arm_id);
   }

   public function save($plan_arm_id, $points, $days_expire, $is_active): array
   {
      $user_id = get_current_user_id();
      $planConfigRepository = new PlanConfigRepository($this->wpdb);

      $planConfig =  $planConfigRepository->getPlanConfigByArmPlanId($plan_arm_id);

      try {
         if (count($planConfig) > 0) {
            $result =  $planConfigRepository->update($plan_arm_id, $points, $days_expire, $is_active);
         } else {
            $result =  $planConfigRepository->create($plan_arm_id, $points, $days_expire, $is_active,  $user_id);
         }

         if ($result) {
            return [
               'success' => true,
               'message' => "Sucesso ao salvar!"
            ];
         } else {
            return [
               'success' => false,
               'message' => "Erro ao salvar."
            ];
         }
      } catch (\Throwable $t) {
         // Se falhar a projeção => rollback
         $msg = $t->getMessage();
         echo 'Falha ao aplicar projeção:';
         error_log("[Projection] Falha ao aplicar projeção: {$msg}");

         return [
            'success' => false,
            'message' => "Erro ao aplicar projeção: {$msg}"
         ];
      }
   }
}
