<?php

namespace SystemToolsHelpInfancia\Core\Repositories;


class PlanConfigRepository
{

   private $wpdb;
   private $table;
   private $tableArm;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->table = $wpdb->prefix . 'st_plans_config';
      $this->tableArm = $wpdb->prefix . 'arm_subscription_plans';
   }

   function create($arm_plan_id, $points, $days_expire, $is_active, $created_user_id)
   {

      $result =  $this->wpdb->insert($this->table, [
         'created_user_id' => $created_user_id,
         'arm_subscription_plan_id' => $arm_plan_id,
         'points' => $points,
         'days_expire' => $days_expire,
         'is_active' => $is_active,
      ], ['%d', '%d', '%d', '%d', '%d']);




      if ($result === false) {
         throw new \Exception("Erro SQL: {$this->wpdb->last_error}");
      }

      return $result;
   }

   function update($arm_plan_id, $points, $days_expire, $is_active)
   {

      $result = $this->wpdb->update(
         $this->table,
         [
            'points' => $points,
            'days_expire' => $days_expire,
            'is_active' => $is_active,
         ],
         ['arm_subscription_plan_id' => $arm_plan_id],
         ['%d', '%d', '%d']
      );

      if ($result === false) {
         throw new \Exception("Erro SQL: {$this->wpdb->last_error}");
      }

      return $result;
   }

   function getPlanConfigByArmPlanId($arm_plan_id)
   {
      $query = "SELECT * FROM $this->table WHERE arm_subscription_plan_id = $arm_plan_id";

      return $this->wpdb->get_results($query);
   }

   function getPlanConfigDetailsByArmPlanId($arm_plan_id)
   {

      $query = "SELECT 
        p.arm_subscription_plan_id as plan_id,
        p.arm_subscription_plan_name as plan_name,
        c.points,
        c.days_expire,
        c.is_active,
        c.updated_at
       FROM $this->tableArm p
       LEFT JOIN $this->table c ON p.arm_subscription_plan_id = c.arm_subscription_plan_id WHERE c.arm_subscription_plan_id = $arm_plan_id";

      return $this->wpdb->get_row($query);
   }
}
