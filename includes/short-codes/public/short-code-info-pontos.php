<?php

if (!defined('ABSPATH')) exit;



add_shortcode('st_info_pontos', function ($atts) {
   if (!is_user_logged_in()) {
      return 'is_user_logged_in';
   }

   $user_id = get_current_user_id();
   global $wpdb;

   // Busca o valor direto da coluna e tabela do ARMember
   $coluna_serializada = $wpdb->get_var($wpdb->prepare("
      SELECT arm_user_plan_ids 
      FROM {$wpdb->prefix}arm_members 
      WHERE arm_user_id = %d 
   ", $user_id));

   // Transforma de String para Array do PHP
   $planos_array = maybe_unserialize($coluna_serializada);

   $id_do_plano = null;

   if (is_array($planos_array) && !empty($planos_array)) {
      $id_do_plano = $planos_array[0]; 
   }

   if (empty($id_do_plano)) {
      return ''; // Não possui plano
   }

   $plan_ids = [$id_do_plano];

   $tb_config = $wpdb->prefix . 'st_plans_config';
   $tb_arm_plans = $wpdb->prefix . 'arm_subscription_plans';
   $tb_balance = $wpdb->prefix . 'st_points_balance';

   $plan_encontrado = null;
   $plan_name = '';

   // Verifica se algum dos planos do usuário tem configuração de pontos na st_plans_config e se está ativo
   foreach ($plan_ids as $pid) {
      $config = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, p.arm_subscription_plan_name as plan_name 
            FROM $tb_config c
            INNER JOIN $tb_arm_plans p ON p.arm_subscription_plan_id = c.arm_subscription_plan_id
            WHERE c.arm_subscription_plan_id = %d AND c.is_active = 1
        ", $pid));

      if ($config) {
         $plan_encontrado = $config;
         $plan_name = $config->plan_name;
         break;
      }
   }

   // Se não encontrou configuração ativa, não exibe nada
   if (!$plan_encontrado) {
      return '';
   }

   // Busca a quantidade de pontos do usuário
   $points = $wpdb->get_var($wpdb->prepare("SELECT available_points FROM $tb_balance WHERE user_id = %d", $user_id));
   $points = $points ? (int)$points : 0;

   // Renderiza o HTML
   ob_start();
?>
   <div class="st-info-pontos-container" style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
      <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
         <!-- <h4 style="margin: 0;">Plano Atual: <strong><?php echo esc_html($plan_name); ?></strong></h4> -->
         <p style="font-size: 1.1em; margin: 0;">Saldo: <strong><?php echo number_format($points, 0, ',', '.'); ?> pts</strong></p>
      </div>
      <a href="<?php echo esc_url(site_url('/extrato/')); ?>" class="extrato-link" style="color: #007bff; text-decoration: underline; font-weight: bold;">Acessar Meu Extrato</a>
   </div>
<?php
   return ob_get_clean();
});
