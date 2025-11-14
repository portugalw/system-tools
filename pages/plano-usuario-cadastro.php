<?php

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

if (!defined('ABSPATH')) exit;

global $wpdb;

$status = null;

// Se recebeu post, processa na própria página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_plan_form'])) {

   check_admin_referer('purchase_plan_action');

   $user_id = intval($_POST['user_id'] ?? 0);
   $plan_id = intval($_POST['plan_id'] ?? 0);

   $service = new EventStoreService($wpdb);
   $service->handle_purchase_plan($user_id, $plan_id);

   $status = 'success';
}
?>

<div class="wrap">
   <h1>Registrar Compra de Plano</h1>

   <?php if ($status === 'success'): ?>
      <div class="notice notice-success is-dismissible">
         <p>Compra registrada com sucesso!</p>
      </div>
   <?php endif; ?>

   <form method="POST" action="">
      <?php wp_nonce_field('purchase_plan_action'); ?>
      <input type="hidden" name="purchase_plan_form" value="1">

      <label>User ID</label>
      <input type="text" name="user_id" required />

      <label>Plan ID</label>
      <input type="text" name="plan_id" required />

      <button type="submit" class="button button-primary">Salvar</button>
   </form>
</div>