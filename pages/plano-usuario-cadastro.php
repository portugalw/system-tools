<?php

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

if (!defined('ABSPATH')) exit;

global $wpdb;

$status  = null;
$message = null;

// Se enviou o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_plan_form'])) {

   check_admin_referer('purchase_plan_action');

   // Sanitização
   $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
   $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;

   // Validações simples de formulário
   if ($user_id <= 0 || $plan_id <= 0) {
      $status  = 'error';
      $message = 'ID de usuário ou plano inválido.';
   } else {

      try {
         $service = new EventStoreService($wpdb);
         $result  = $service->handle_purchase_plan($user_id, $plan_id);

         // Resultado padronizado: ['success'=>bool, 'message'=>string]
         if (is_array($result) && isset($result['success'])) {

            if ($result['success'] === true) {
               $status  = 'success';
               $message = $result['message'] ?? 'Compra registrada com sucesso!';
            } else {
               $status  = 'error';
               $message = $result['message'] ?? 'Falha ao registrar a compra.';
            }
         } else {
            $status  = 'error';
            $message = 'A resposta do servidor não é válida.';
            error_log('[purchase_plan] Resposta inesperada do handle_purchase_plan');
         }
      } catch (Throwable $t) {
         $status  = 'error';
         $message = 'Erro inesperado: ' . $t->getMessage();

         // Log detalhado para debugging
         error_log('[purchase_plan] Erro crítico ao processar compra: ' . $t->getMessage());
      }
   }
}
?>

<div class="wrap">
   <h1>Registrar Compra de Plano</h1>

   <!-- ALERTAS -->
   <?php if ($status === 'success'): ?>
      <div class="notice notice-success is-dismissible">
         <p><?php echo esc_html($message); ?></p>
      </div>
   <?php elseif ($status === 'error'): ?>
      <div class="notice notice-error is-dismissible">
         <p><?php echo esc_html($message); ?></p>
      </div>
   <?php endif; ?>

   <form method="POST" action="">
      <?php wp_nonce_field('purchase_plan_action'); ?>
      <input type="hidden" name="purchase_plan_form" value="1">

      <table class="form-table">
         <tr>
            <th><label for="user_id">User ID</label></th>
            <td><input type="number" id="user_id" name="user_id" required /></td>
         </tr>

         <tr>
            <th><label for="plan_id">Plan ID</label></th>
            <td><input type="number" id="plan_id" name="plan_id" required /></td>
         </tr>
      </table>

      <p>
         <button type="submit" class="button button-primary">Salvar</button>
      </p>
   </form>
</div>