<?php

if (!defined('ABSPATH')) exit;

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
?>

<div class="st-admin-wrapper">
   <header class="st-admin-header">
      <h2>📊 Execuções de Expiração</h2>
      <p>Monitoramento de jobs de expiração de pontos</p>
   </header>

   <div class="card p-4">

      <!-- 🔹 controle de paginação -->
      <form method="get" class="mb-3">
         <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? '') ?>">

         <select name="per_page" onchange="this.form.submit()">
            <option value="25" <?= $per_page == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $per_page == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
         </select>
      </form>

      <table class="table table-hover align-middle">
         <thead>
            <tr>
               <th>ID</th>
               <th>Status</th>
               <th>Total</th>
               <th>Sucesso</th>
               <th>Erro</th>
               <th>Início</th>
               <th>Fim</th>
               <th>Ação</th>
            </tr>
         </thead>
         <tbody>
            <?php if ($runs): foreach ($runs as $run): ?>
                  <tr>
                     <td><?= $run->run_id ?></td>

                     <td>
                        <span class="badge bg-<?= $run->status === 'finished' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning') ?>"> <?= esc_html($run->status) ?>
                        </span>
                     </td>

                     <td><?= $run->total_records ?></td>
                     <td><?= $run->success_count ?></td>
                     <td><?= $run->error_count ?></td>

                     <td><?= $run->started_at ?></td>
                     <td><?= $run->finished_at ?: '-' ?></td>

                     <td>
                        <button class="btn btn-outline-primary btn-sm btn-view-run"
                           data-run-id="<?= $run->run_id ?>">
                           Detalhes
                        </button>
                     </td>
                  </tr>
               <?php endforeach;
            else: ?>
               <tr>
                  <td colspan="8" class="text-center text-muted">
                     Nenhuma execução encontrada
                  </td>
               </tr>
            <?php endif; ?>
         </tbody>
      </table>

      <!-- 🔹 paginação -->
      <div class="mt-3">
         <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline-secondary' ?>"
               href="?page=<?= esc_attr($_GET['page']) ?>&paged=<?= $i ?>&per_page=<?= $per_page ?>">
               <?= $i ?>
            </a>
         <?php endfor; ?>
      </div>

   </div>
</div>

<?php include __DIR__ . '/modal-detalhes-historico-execucao-expiracao.php'; ?>