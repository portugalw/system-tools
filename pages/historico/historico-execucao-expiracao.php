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
      <div class="mb-3">
         <select id="per-page-select" name="per_page" class="form-select w-auto">
            <option value="25" <?= $per_page == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $per_page == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $per_page == 100 ? 'selected' : '' ?>>100</option>
         </select>
      </div>

      <div id="table-container">
          <?php include __DIR__ . '/table-historico-execucao-expiracao.php'; ?>
      </div>

   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.getElementById('table-container');
    const perPageSelect = document.getElementById('per-page-select');

    function loadTable(paged = 1) {
        const perPage = perPageSelect.value;
        tableContainer.innerHTML = '<p class="text-center text-muted py-3">Carregando...</p>';

        fetch(ajaxurl + '?action=st_get_historico_expiracao_table&paged=' + paged + '&per_page=' + perPage)
            .then(res => res.text())
            .then(html => {
                tableContainer.innerHTML = html;
            })
            .catch(err => {
                tableContainer.innerHTML = '<p class="text-center text-danger py-3">Erro ao carregar dados.</p>';
            });
    }

    perPageSelect.addEventListener('change', function() {
        loadTable(1);
    });

    tableContainer.addEventListener('click', function(e) {
        if (e.target.closest('.pagination-link')) {
            e.preventDefault();
            const link = e.target.closest('.pagination-link');
            const page = link.dataset.page;
            if (page) {
                loadTable(page);
            }
        }
    });
});
</script>


<?php include __DIR__ . '/modal-detalhes-historico-execucao-expiracao.php'; ?>