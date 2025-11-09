<?php

namespace SystemToolsHelpInfanciaTemplates;


// Impedir acesso direto
if (! defined('ABSPATH')) {
    exit;
}
global $wpdb;
$table_name = $wpdb->prefix . 'request_logs';

// Obter os registros
$logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY timestamp DESC");

?>
<div class="wrap">


    <h1>Logs de Requests</h1>


    <table id="logs-table" class="wp-list-table widefat fixed striped" style="width:100%">
        <thead>
            <tr>
                <th style="width: 30px;">ID</th>
                <th style="width: 120px;">Data/Hora</th>
                <th>Corpo da Requisição</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($logs) :
                foreach ($logs as $log) :
            ?>
                    <tr>
                        <td><?= esc_html($log->id); ?></td>
                        <td><?= esc_html($log->timestamp); ?></td>
                        <td>
                            <pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 150px; overflow-y: auto;">
                                    <?= esc_html($log->request_body); ?>
                                </pre>
                        </td>
                    </tr>
                <?php
                endforeach;
            else :
                ?>
                <tr>
                    <td colspan="3">Nenhum log encontrado.</td>
                </tr>
            <?php
            endif;
            ?>
        </tbody>
    </table>

</div>