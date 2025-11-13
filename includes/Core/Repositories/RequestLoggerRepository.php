<?php


namespace SystemToolsHelpInfancia\Core\Repositories;

class RequestLogger
{
   private $wpdb;
   private $table;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->table = $wpdb->prefix . 'request_logs';
   }


   public function log($request)
   {
      // Obter o corpo da mensagem
      $request_body = json_encode($request->get_json_params());

      // Inserir no banco de dados
      return $this->wpdb->insert($this->table, [
         'request_body' => $request_body,
      ]);

      // Retornar uma resposta para o cliente
      return rest_ensure_response([
         'success' => true,
         'message' => 'Requisição registrada com sucesso.',
      ]);
   }
}
