<?php

namespace SystemToolsHelpInfanciaTemplates;

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'request_logs';

// Capturar o termo de pesquisa
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Montar query com filtro
$query = "SELECT * FROM $table_name";
if (!empty($search)) {
    $query .= $wpdb->prepare(" WHERE request_body LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}
$query .= " ORDER BY timestamp DESC";

$logs = $wpdb->get_results($query);
?>


<div class="wrap logs-admin-page">
    <h1 class="page-title">📘 Logs dos Requests</h1>
    <p class="page-subtitle">Visualize, filtre e analise os registros do sistema.</p>

    <!-- Formulário de Filtro -->
    <form method="get" id="filter-form">
        <input type="hidden" name="page" value="<?= esc_attr($_GET['page']); ?>">

        <div class="filter-fields">

            <label for="search">Pesquisar:</label>
            <input
                type="text"
                name="search"
                id="search"
                value="<?= esc_attr($search); ?>"
                placeholder="Digite um termo para filtrar...">
            <button type="submit" class="button button-primary">🔍 Filtrar</button>
            <button type="button" class="button button-secondary" id="clear-filters">🧹 Limpar</button>
        </div>
    </form>

    <!-- Quantidade de registros -->
    <p class="record-count">Exibindo <?= count($logs); ?> registros.</p>

    <!-- Tabela -->
    <table id="logs-table" class="widefat fixed log-table">
        <thead>
            <tr>
                <th style="width: 40px;">ID</th>
                <th style="width: 160px;">Data/Hora</th>
                <th>Corpo da Requisição</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= esc_html($log->id); ?></td>
                        <td><?= esc_html($log->timestamp); ?></td>
                        <td>
                            <pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 150px; overflow-y: auto;">
<?= esc_html($log->request_body); ?>
                            </pre>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nenhum log encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- JavaScript para limpar o campo -->
<script>
    document.getElementById('clear-filters').addEventListener('click', function() {
        document.getElementById('search').value = '';
        document.getElementById('filter-form').submit();
    });
</script>