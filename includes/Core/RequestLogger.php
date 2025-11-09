<?php


namespace SystemToolsHelpInfancia\Core;

class RequestLogger
{
   function __construct() {}

   static function log($request)
   {
      global $wpdb;

      // Obter o corpo da mensagem
      $request_body = json_encode($request->get_json_params());

      // Inserir no banco de dados
      $table_name = $wpdb->prefix . 'request_logs';
      $wpdb->insert($table_name, [
         'request_body' => $request_body,
      ]);

      // Retornar uma resposta para o cliente
      return rest_ensure_response([
         'success' => true,
         'message' => 'Requisição registrada com sucesso.',
      ]);
   }
}
