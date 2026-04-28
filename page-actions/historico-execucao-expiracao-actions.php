<?php

if (!defined('ABSPATH')) exit;


add_action('wp_ajax_st_get_run_details', 'st_get_run_details');




function st_get_run_details()
{

   global $wpdb;

   $run_id = intval($_GET['run_id']);

   $table_runs = $wpdb->prefix . 'st_points_expiration_runs';
   $table_logs = $wpdb->prefix . 'st_points_expiration_logs';

   // 🔹 dados do run
   $run = $wpdb->get_row($wpdb->prepare("
        SELECT *
        FROM $table_runs
        WHERE run_id = %d
    ", $run_id));

   // 🔹 logs
   $logs = $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM $table_logs
        WHERE run_id = %d
        ORDER BY created_at DESC
        LIMIT 200
    ", $run_id));

   if (!$run) {
      echo "<p>Execução não encontrada</p>";
      wp_die();
   }

?>

   <h5>Resumo</h5>
   <ul>
      <li><strong>Status:</strong> <?= esc_html($run->status) ?></li>
      <li><strong>Total:</strong> <?= $run->total_records ?></li>
      <li><strong>Sucesso:</strong> <?= $run->success_count ?></li>
      <li><strong>Erro:</strong> <?= $run->error_count ?></li>
      <li><strong>Início:</strong> <?= $run->started_at ?></li>
      <li><strong>Fim:</strong> <?= $run->finished_at ?></li>
   </ul>

   <h5 class="mt-4">Logs</h5>

   <table class="table table-sm table-striped">
      <thead>
         <tr>
            <th>Batch</th>
            <th>User</th>
            <th>Pontos</th>
            <th>Status</th>
            <th>Mensagem</th>
            <th>Data</th>
         </tr>
      </thead>
      <tbody>
         <?php if ($logs): foreach ($logs as $log): ?>
               <tr>
                  <td><?= $log->batch_id ?></td>
                  <td><?= $log->user_id ?></td>
                  <td><?= $log->points ?></td>
                  <td><?= esc_html($log->status) ?></td>
                  <td><?= esc_html($log->message) ?></td>
                  <td><?= $log->created_at ?></td>
               </tr>
            <?php endforeach;
         else: ?>
            <tr>
               <td colspan="6">Nenhum log encontrado</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>

<?php

   wp_die();
};
