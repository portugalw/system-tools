<?php
// -----------------------------------------------------------------
// 1. BLOCO DE LÓGICA PHP (Processamento e busca de dados)
// -----------------------------------------------------------------
global $wpdb;

// Nome da tabela
$nome_tabela = $wpdb->prefix . 'log_event';

// Campos de filtro
$search_query = isset($_GET['logsearch']) ? sanitize_text_field($_GET['logsearch']) : '';
$evento       = isset($_GET['evento']) ? sanitize_text_field($_GET['evento']) : '';
$origem       = isset($_GET['origem']) ? sanitize_text_field($_GET['origem']) : '';
$descricao        = isset($_GET['descricao']) ? sanitize_text_field($_GET['descricao']) : '';

// Monta consulta base
$query = "SELECT * FROM $nome_tabela WHERE 1=1";

// Filtro de pesquisa geral
if (!empty($search_query)) {
   $query .= $wpdb->prepare(
      " AND (event LIKE %s OR description LIKE %s OR origin LIKE %s OR description LIKE %s)",
      "%$search_query%",
      "%$search_query%",
      "%$search_query%",
      "%$search_query%"
   );
}

// Filtros específicos
if (!empty($evento)) {
   $query .= $wpdb->prepare(" AND event LIKE %s", "%$evento%");
}
if (!empty($origem)) {
   $query .= $wpdb->prepare(" AND origin LIKE %s", "%$origem%");
}
if (!empty($descricao)) {
   $query .= $wpdb->prepare(" AND description LIKE %s", "%$descricao%");
}

// Ordena por mais recente
$query .= " ORDER BY id DESC";

// Executa query
$logs = $wpdb->get_results($query);
$total_logs = count($logs);

// -----------------------------------------------------------------
// 2. INCLUI CSS PERSONALIZADO
// -----------------------------------------------------------------

?>

<div class="wrap logs-admin-page">
   <h1 class="page-title">📘 Logs dos Eventos</h1>
   <p class="page-subtitle">Visualize, filtre e analise os registros do sistema.</p>

   <!-- Formulário de Filtros -->
   <form id="logFilterForm" method="get" action="" class="filter-form">
      <input type="hidden" name="page" value="st-event-log">

      <div class="filter-fields">
         <input type="text" name="logsearch" value="<?= esc_attr($search_query); ?>" placeholder="Pesquisar logs...">
         <input type="text" name="evento" value="<?= esc_attr($evento); ?>" placeholder="Evento">
         <input type="text" name="origem" value="<?= esc_attr($origem); ?>" placeholder="Origem">
         <input type="text" name="descricao" value="<?= esc_attr($descricao); ?>" placeholder="Descrição">

         <input type="submit" value="🔍 Filtrar" class="button button-primary">
         <button type="button" id="clearFiltersBtn" class="button button-secondary">🧹 Limpar</button>
      </div>
   </form>

   <p class="log-count">
      <?= $total_logs; ?> registro(s) encontrado(s)
   </p>

   <!-- Tabela -->
   <table class="widefat fixed log-table">
      <thead>
         <tr>
            <th width="5%">ID</th>
            <th width="10%">Data</th>
            <th width="20%">Evento</th>
            <th width="15%">Origem</th>
            <th width="20%">E-mail</th>
            <th>Descrição</th>
         </tr>
      </thead>
      <tbody>
         <?php if ($logs): ?>
            <?php foreach ($logs as $log): ?>
               <tr>
                  <td><strong><?= esc_html($log->id); ?></strong></td>
                  <td><?= esc_html($log->date); ?></td>
                  <td><?= esc_html($log->event); ?></td>
                  <td><?= esc_html($log->origin); ?></td>
                  <td><?= esc_html($log->customer_email); ?></td>
                  <td><?= esc_html($log->description); ?></td>
               </tr>
            <?php endforeach; ?>
         <?php else: ?>
            <tr>
               <td colspan="6" class="no-results">Nenhum log encontrado.</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>
</div>

<!-- Script para limpar e submeter -->
<script>
   document.getElementById('clearFiltersBtn').addEventListener('click', function() {
      const form = document.getElementById('logFilterForm');
      const inputs = form.querySelectorAll('input[type="text"]');
      inputs.forEach(input => input.value = '');
      form.submit();
   });
</script>