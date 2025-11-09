<?php
// -----------------------------------------------------------------
// 1. BLOCO DE LÓGICA PHP (Processamento e busca de dados)
// -----------------------------------------------------------------
global $wpdb;

// Nome da tabela
$nome_tabela = $wpdb->prefix . 'log_event';

// Processa o formulário de pesquisa
$search_query = isset($_GET['logsearch']) ? sanitize_text_field($_GET['logsearch']) : '';

// Consulta os logs (inclui filtro se houver busca)
if (!empty($search_query)) {
   $logs = $wpdb->get_results(
      $wpdb->prepare(
         "SELECT * FROM $nome_tabela 
                 WHERE event LIKE %s OR description LIKE %s OR origin LIKE %s
                 ORDER BY ID DESC",
         '%' . $search_query . '%', // O %s será substituído por isso
         '%' . $search_query . '%', // O %s será substituído por isso
         '%' . $search_query . '%'  // O %s será substituído por isso
      )
   );
} else {
   // Busca todos os logs se não houver pesquisa
   $logs = $wpdb->get_results("SELECT * FROM $nome_tabela ORDER BY ID DESC");
}

// -----------------------------------------------------------------
// 2. BLOCO DE TEMPLATE HTML (Renderização)
// Fechamos o PHP para escrever HTML puro.
// -----------------------------------------------------------------
?>

<div class="wrap">
   <h1>Logs do Sistema</h1>

   <form method="get" action="">
      <input type="hidden" name="page" value="st-event-log">
      <p>
         <input type="text"
            name="logsearch"
            value="<?= esc_attr($search_query); ?>"
            placeholder="Pesquisar logs..."
            style="width: 300px; padding: 5px;">

         <input type="submit" value="Pesquisar" class="button button-primary">
      </p>
   </form>

   <table class="widefat fixed" cellspacing="0">
      <thead>
         <tr>
            <th width="5%">ID</th>
            <th width="10%">Data</th>
            <th width="20%">Evento</th>
            <th width="15%">Origem</th>
            <th>Descrição</th>
         </tr>
      </thead>
      <tbody>
         <?php
         // Reabrimos o PHP para usar lógica (o loop)
         if ($logs) :
            // Usamos a sintaxe de loop : e endforeach; que é mais limpa em templates
            foreach ($logs as $log) :
               // Fechamos o PHP de novo para escrever o HTML da linha
         ?>
               <tr>
                  <td><?= esc_html($log->id); ?></td>
                  <td><?= esc_html($log->date); ?></td>
                  <td><?= esc_html($log->event); ?></td>
                  <td><?= esc_html($log->origin); ?></td>
                  <td><?= esc_html($log->description); ?></td>
               </tr>
            <?php
            // Reabrimos o PHP para fechar o loop
            endforeach;
         else :
            // Caso não haja logs
            ?>
            <tr>
               <td colspan="5">Nenhum log encontrado.</td>
            </tr>
         <?php
         // Fechamos o if/else
         endif;
         ?>
      </tbody>
   </table>

</div>