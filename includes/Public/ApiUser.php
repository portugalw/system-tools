<?php

namespace SystemToolsHelpInfancia\Public;

defined('ABSPATH') || exit;




class ApiUser
{

   public function __construct()
   {
      add_action('rest_api_init', array($this, 'register_routes'));
   }


   public function register_routes()
   {
      register_rest_route('meu-plugin/v1', '/receber-usuario', array(
         'methods'             => 'POST',
         'callback'            => array($this, 'handle_user_request'),
         'permission_callback' => '__return_true', // ATENÇÃO: Aberto ao público.
      ));

      register_rest_route('custom-webhook/v2', '/create-user', array(
         'methods' => 'POST',
         'callback' => array($this, 'handle_create_user_request_v2'),
         'permission_callback' => '__return_true',
      ));
   }


   /**
    * Callback da rota da API. Processa os dados do usuário.
    */
   public function handle_create_user_request_v2($request)
   {
      global $wpdb;
      $parameters = $request->get_json_params();
      // Obtém os parâmetros da rota
      $membership_id = $request->get_param('membership_id'); // Pega o parâmetro da rota
      $tipo_evento = $request->get_param('event_name'); // invoice_paid; invoice_opened; invoice_canceled

      if (!$membership_id || !is_numeric($membership_id)) {
         return new \WP_Error('invalid_membership_id', 'ID do plano de assinatura inválido ou ausente.', array('status' => 400));
      }

      $this->request_logger_handle_request($request);

      $email = sanitize_email($parameters['cus_email']);
      $username = sanitize_text_field($parameters['cus_email']);
      $name = sanitize_text_field($parameters['cus_name']);
      $phone_number = sanitize_text_field($parameters['cus_cel']);
      $trans_cod_eduzz = sanitize_text_field($parameters['trans_cod']);
      $origin = sanitize_text_field($parameters['origin']);
      $password = 'helpinfancia';
      $dadosAluno = $this->concatenaDados($email, $phone_number, $name, $membership_id);
      $msgUserSuccess = 'Usuário criado com sucesso!';

      $this->gravar_log($tipo_evento, $dadosAluno);

      if ($tipo_evento != 'invoice_paid') {

         return array(
            'success' => true,
            'message' => 'Evento diferente de invoice_paid',
         );
      }

      $user_id = false;
      if (!email_exists($email)) {
         // Criar o usuário no WordPress
         //gravar_log('cria-usuario-inicio', $dadosAluno);
         $user_id = wp_create_user($username, $password, $email);
         // Opcional: Adicionar meta ou função customizada ao usuário
         wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $name,
            'role' => 'subscriber',
         ));
         $this->gravar_log('cria-usuario-sucesso', $dadosAluno);
         //return new WP_Error('user_exists', 'Usuário já existe.', array('status' => 400));
      } else {
         $user = get_user_by('email', $email);
         $user_id = $user->ID;
         $this->gravar_log('busca-usuario-existente-sucesso', $dadosAluno);
         $msgUserSuccess = 'Usuário atualizado com sucesso!';
      }

      if (is_wp_error($user_id)) {
         $this->gravar_log('busca-cria-usuario-erro', $dadosAluno . " ID: " . $user_id);
         return new \WP_Error('user_creation_failed', 'Erro ao criar usuário.', array('status' => 500));
      }

      add_user_meta($user_id, 'trans_cod_eduzz', $trans_cod_eduzz); // adiciona o id da transação da compra no usuario

      // Adicionar o usuário ao ARMember
      if (class_exists('ARM_Members_Lite')) {
         $armember = new \ARM_Members_Lite();
         $armember->arm_add_user_to_armember_func($user_id, 0, $membership_id);
         $this->gravar_log('add-armember-sucesso', $dadosAluno . " ID: " . $user_id);
      }

      // Enviar e-mail ao usuário com seu plano
      global $wpdb, $arm_email_settings, $ARMemberLite, $arm_global_settings;
      $arm_global_settings = new \ARM_global_settings_Lite();
      $retorno = $arm_global_settings->arm_mailer($arm_email_settings->templates->on_menual_activation, $user_id);
      $this->gravar_log('email-usuario-novo-sucesso', $dadosAluno . " ID: " . $user_id);

      if ($origin == 'MANUAL_PLANILHA_INTEGRACAO') {
         $this->gravar_log('cadastro-via-planilha-usuario-novo-sucesso', $dadosAluno . " ID: " . $user_id);
      } else {
         $this->enviarDadosSysteme($email, $phone_number, $name, $membership_id);
      }
      //verificar_e_atualizar_meta_usuario($user_id, 'description', $phone_number);
      update_user_meta($user_id, 'description', $phone_number);
      //gravar_log('add-usuario-telefone-sucesso', $dadosAluno . " ID: " . $user_id);

      return wp_send_json_success(array(
         'success' => true,
         'message' => 'Usuário criado com sucesso.',
         'user_id' => 0,
         'retorno' => 0
      ));
   }

   function gravar_log($event, $description)
   {
      global $wpdb;

      $nome_tabela = $wpdb->prefix . 'log_event';

      $wpdb->insert(
         $nome_tabela,
         [
            'event' => $event,
            'description' => $description
         ],
         [
            '%s', // Tipo para "event"
            '%s'  // Tipo para "description"
         ]
      );
   }

   function request_logger_handle_request($request)
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


   function concatenaDados($email, $phone, $name, $memberId)
   {
      // Formata os dados concatenados
      $resultado =  " Nome: " . $name . ", Email: " . $email . ", Telefone: " . $phone . ", Plano: " . $memberId;

      // Retorna o resultado
      return $resultado;
   }

   function enviarDadosSysteme($email, $phone, $name, $memberId)
   {
      // Define o endpoint da API
      $url = "https://pv.helpinfancia.com.br/pos-compra-integracao";
      $dadosAluno = $this->concatenaDados($email, $phone, $name, $memberId);

      // Cria o JSON com os dados mapeados
      $data = [
         "optin" => [
            "fields" => [
               "email" => $email,
               "phone_number" => $phone,
               "first_name" => $name
            ],
            "timeZone" => "America/Sao_Paulo",
            "popupId" => null,
            "isDesktop" => true,
            "entityId" => "ec70d02f-0b8f-4fba-bae9-ef10e78af655",
            "checkBoxIds" => []
         ]
      ];

      // Converte os dados para JSON
      $jsonData = json_encode($data);

      // Configura as opções do cURL
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
         'Content-Type: application/json',
         'Content-Length: ' . strlen($jsonData)
      ]);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

      // Executa a requisição
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Verifica se houve erro
      if (curl_errno($ch)) {
         $error = curl_error($ch);
         curl_close($ch);
         $this->gravar_log('enviarDadosSysteme-erro', $dadosAluno);
         return ["success" => false, "error" => $error];
      }

      // Fecha o cURL
      curl_close($ch);
      $this->gravar_log('enviarDadosSysteme-sucesso', $dadosAluno);
      // Retorna a resposta
      return ["success" => $httpCode === 200, "response" => $response];
   }
}
