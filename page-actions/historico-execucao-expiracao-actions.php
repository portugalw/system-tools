<?php

if (!defined('ABSPATH')) exit;


add_action('wp_ajax_st_get_run_details', 'st_get_run_details');
add_action('wp_ajax_st_get_historico_expiracao_table', 'st_get_historico_expiracao_table');

function st_get_historico_expiracao_table() {
   global $wpdb;

   $table_runs = $wpdb->prefix . 'st_points_expiration_runs';

   // 🔹 paginação
   $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;
   $per_page = in_array($per_page, [25, 50, 100]) ? $per_page : 25;

   $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
   $offset = ($page - 1) * $per_page;

   // 🔹 total
   $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_runs");

   // 🔹 dados
   $runs = $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM $table_runs
        ORDER BY started_at DESC
        LIMIT %d OFFSET %d
    ", $per_page, $offset));

   // 🔹 total páginas
   $total_pages = ceil($total / $per_page);

   // render HTML
   include plugin_dir_path(__DIR__) . 'pages/historico/table-historico-execucao-expiracao.php';

   wp_die();
}
function st_get_run_details()
{

   global $wpdb;

   $run_id = intval($_GET['run_id']);

   $table_runs = $wpdb->prefix . 'st_points_expiration_runs';
   $table_logs = $wpdb->prefix . 'st_points_expiration_logs';
   $table_users = $wpdb->prefix . 'users';

   // 🔹 dados do run
   $run = $wpdb->get_row($wpdb->prepare("
        SELECT *
        FROM $table_runs
        WHERE run_id = %d
    ", $run_id));

   // 🔹 logs
   $logs = $wpdb->get_results($wpdb->prepare("
        SELECT l.*, u.display_name, u.user_email
        FROM $table_logs l
        JOIN $table_users u ON u.ID = l.user_id 
        WHERE l.run_id = %d
        ORDER BY l.created_at DESC
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
            <th>E-mail</th>
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
                  <td><?= $log->display_name ?></td>
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
