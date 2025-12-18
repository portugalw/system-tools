<?php

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

if (!defined('ABSPATH')) exit;

global $wpdb;

$status  = null;
$message = null;

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debit_plan_form'])) {

   check_admin_referer('debit_plan_action');

   // Sanitização
   $user_id = intval($_POST['user_id'] ?? 0);
   $plan_id = intval($_POST['plan_id'] ?? 0);
   $points  = intval($_POST['points'] ?? 0);

   // Validações básicas
   if ($user_id <= 0 || $plan_id <= 0 || $points <= 0) {
      $status  = 'error';
      $message = 'Dados inválidos. Verifique User ID, Plan ID e Pontos.';
   } else {

      try {
         $service = new EventStoreService($wpdb);

         // handle_consume_plan deve retornar:
         // ['success'=>bool, 'message'=>string]
         $result = $service->handle_consume_plan($user_id, $plan_id, $points);

         if (is_array($result) && isset($result['success'])) {

            if ($result['success'] === true) {
               $status  = 'success';
               $message = $result['message'] ?? 'Débito registrado com sucesso!';
            } else {
               $status  = 'error';
               $message = $result['message'] ?? 'Não foi possível processar o débito.';
            }
         } else {
            $status  = 'error';
            $message = 'Erro inesperado na resposta do sistema.';
            error_log('[debit_plan] Resposta inesperada de handle_consume_plan');
         }
      } catch (Throwable $e) {

         $status  = 'error';
         $message = 'Erro crítico ao processar o débito: ' . $e->getMessage();

         // log seguro para debug
         error_log('[debit_plan] EXCEPTION: ' . $e->getMessage());
      }
   }
}
?>

<div class="wrap">
   <h1>Registrar Débito de Pontos</h1>

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
      <?php wp_nonce_field('debit_plan_action'); ?>
      <input type="hidden" name="debit_plan_form" value="1">

      <table class="form-table">

         <tr>
            <th><label for="user_id">User ID</label></th>
            <td><input type="number" id="user_id" name="user_id" required /></td>
         </tr>

         <tr>
            <th><label for="plan_id">Plan ID</label></th>
            <td><input type="number" id="plan_id" name="plan_id" required /></td>
         </tr>

         <tr>
            <th><label for="points">Pontos</label></th>
            <td><input type="number" id="points" name="points" required min="1" /></td>
         </tr>

      </table>

      <p><button type="submit" class="button button-primary">Salvar</button></p>
   </form>
</div>