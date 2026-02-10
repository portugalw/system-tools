<?php

add_action('wp_ajax_get_extrato', 'st_ajax_get_extrato');
add_action('wp_ajax_nopriv_get_extrato', 'st_ajax_get_extrato');

function st_ajax_get_extrato()
{

   check_ajax_referer('extrato_nonce');

   $page = intval($_POST['page'] ?? 1);
   $operacao = sanitize_text_field($_POST['operacao'] ?? 'todos');

   $items = st_obter_extrato($page, $operacao);

   wp_send_json_success([
      'items' => $items,
      'paginacao' => st_gerar_paginacao($page, 10)
   ]);
}

add_action('wp_ajax_get_expirar', 'st_ajax_get_expirar');
add_action('wp_ajax_nopriv_get_expirar', 'st_ajax_get_expirar');

function st_ajax_get_expirar()
{

   check_ajax_referer('extrato_nonce');

   $items = st_obter_expirar();

   wp_send_json_success([
      'items' => $items
   ]);
}

function st_obter_extrato($page = 1, $operacao = 'todos')
{
   global $wpdb;

   $limit  = 10;
   $offset = ($page - 1) * $limit;

   $table = $wpdb->prefix . 'st_points_history';

   $where = "WHERE user_id = " . get_current_user_id();

   if ($operacao !== 'todos') {
      $where .= $wpdb->prepare(" AND operacao = %s", $operacao);
   }

   // Query principal
   $sql = $wpdb->prepare("
        SELECT data, operacao, parceiro, pontos, observacao 
        FROM $table
        $where
        ORDER BY data DESC
        LIMIT %d OFFSET %d
    ", $limit, $offset);

   $rows = $wpdb->get_results($sql);

   $items = [];

   foreach ($rows as $r) {
      $items[] = [
         'data'      => date('d/m/Y H:i', strtotime($r->data)),
         'operacao'  => ucfirst($r->operacao),
         'parceiro'  => $r->parceiro ?: '-',
         'pontos'    => intval($r->pontos),
         'obs'       => $r->observacao ?: '-'
      ];
   }

   return $items;
}


function st_obter_expirar()
{
   global $wpdb;

   $table = $wpdb->prefix . 'st_points_expiring';

   $sql = $wpdb->prepare("
        SELECT data_expira, quantidade 
        FROM $table
        WHERE user_id = %d
        ORDER BY data_expira ASC
    ", get_current_user_id());

   $rows = $wpdb->get_results($sql);
   $items = [];

   foreach ($rows as $r) {
      $items[] = [
         'data_expira' => date('d/m/Y', strtotime($r->data_expira)),
         'quantidade'  => intval($r->quantidade)
      ];
   }

   return $items;
}


function st_gerar_paginacao($page, $total_pages)
{

   if ($total_pages <= 1) return '';

   $html = '<nav><ul class="pagination justify-content-center">';

   // Botão anterior
   if ($page > 1) {
      $html .= '<li class="page-item">
                    <a href="#" data-page="' . ($page - 1) . '" class="page-link">«</a>
                  </li>';
   }

   // Páginas numeradas
   for ($i = 1; $i <= $total_pages; $i++) {
      $active = ($i == $page) ? 'active' : '';
      $html .= '<li class="page-item ' . $active . '">
                    <a href="#" data-page="' . $i . '" class="page-link">' . $i . '</a>
                  </li>';
   }

   // Botão próximo
   if ($page < $total_pages) {
      $html .= '<li class="page-item">
                    <a href="#" data-page="' . ($page + 1) . '" class="page-link">»</a>
                  </li>';
   }

   $html .= '</ul></nav>';

   return $html;
}
