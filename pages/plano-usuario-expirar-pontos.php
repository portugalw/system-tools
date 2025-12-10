<?php

use SystemToolsHelpInfancia\Core\Services\EventStoreService;

if (!defined('ABSPATH')) exit;

global $wpdb;

$status = null;
$message = "";

// Se recebeu post, processa na própria página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expire_points_form'])) {

   check_admin_referer('expire_points_action');

   $user_id = intval($_POST['user_id'] ?? 0);
   $expire_date = sanitize_text_field($_POST['expire_date'] ?? '');

   if ($user_id <= 0 || empty($expire_date)) {
      $status = 'error';
      $message = "Campos obrigatórios não preenchidos.";
   } else {

      try {
         $service = new EventStoreService($wpdb);
         $results = $service->handle_expire_points($user_id,  $expire_date);

         foreach ($results as $result) {
            echo 'dsad';
            echo $result;
            if ($result['success']) {
               $status = 'success';
               $message = $result['message'] ?? "Expiração processada com sucesso!";
            } else {
               $status = 'error';
               $message = $result['message'] ?? "Falha ao expirar os pontos.";
            }
         }
      } catch (\Throwable $e) {
         echo $e->getMessage();
         $status = 'error';
         $message = "Erro ao expirar pontos: " . $e->getMessage();
         echo $message;
         error_log("[ExpirePoints] " . $e->getMessage());
      }
   }
}
?>

<div class="wrap">
   <h1>Expirar Pontos do Usuário</h1>

   <?php if ($status === 'success'): ?>
      <div class="notice notice-success is-dismissible">
         <p><?php foreach ($results as $result) {
               echo esc_html($message);
            } ?></p>
      </div>
   <?php elseif ($status === 'error'): ?>
      <div class="notice notice-error is-dismissible">
         <p><?php foreach ($results as $result) {
               echo esc_html($message);
            } ?></p>
      </div>
   <?php endif; ?>

   <form method="POST" action="">
      <?php wp_nonce_field('expire_points_action'); ?>
      <input type="hidden" name="expire_points_form" value="1">

      <table class="form-table">
         <tr>
            <th><label for="user_id">User ID</label></th>
            <td>
               <input type="number" name="user_id" id="user_id" required class="regular-text" />
            </td>
         </tr>

         <tr>
            <th><label for="expire_date">Expirar pontos até a data</label></th>
            <td>
               <input type="text" name="expire_date" id="expire_date" required class="regular-text" />
            </td>
         </tr>
      </table>

      <p>
         <button type="submit" class="button button-primary">Processar Expiração</button>
      </p>
   </form>
</div>