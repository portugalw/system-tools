<?php

namespace SystemToolsHelpInfancia\Public;


use SystemToolsHelpInfancia\Core\EventLogger;
use SystemToolsHelpInfancia\Core\RequestLogger;
use SystemToolsHelpInfancia\Adapters\SystemeAdapter;
use SystemToolsHelpInfancia\Util;

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

      RequestLogger::log($request);

      $email = sanitize_email($parameters['cus_email']);
      $username = sanitize_text_field($parameters['cus_email']);
      $name = sanitize_text_field($parameters['cus_name']);
      $phone_number = Util::formatPhone(sanitize_text_field($parameters['cus_cel']));
      $trans_cod_eduzz = sanitize_text_field($parameters['trans_cod']);
      $origin = sanitize_text_field($parameters['origin']);
      $password = 'helpinfancia';
      $dadosAluno = Util::concatenaDados($email, $phone_number, $name, $membership_id);
      $msgUserSuccess = 'Usuário criado com sucesso!';

      EventLogger::log($tipo_evento, $dadosAluno, $origin);

      if ($tipo_evento != 'invoice_paid') {

         return array(
            'success' => true,
            'message' => 'Evento diferente de invoice_paid',
         );
      }

      $user_id = false;
      if (!email_exists($email)) {
         // Criar o usuário no WordPress
         $user_id = wp_create_user($username, $password, $email);
         // Opcional: Adicionar meta ou função customizada ao usuário
         wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $name,
            'role' => 'subscriber',
         ));
         EventLogger::log('cria-usuario-sucesso', $dadosAluno, $origin);
      } else {
         $user = get_user_by('email', $email);
         $user_id = $user->ID;
         EventLogger::log('busca-usuario-existente-sucesso', $dadosAluno, $origin);
         $msgUserSuccess = 'Usuário atualizado com sucesso!';
      }

      if (is_wp_error($user_id)) {
         EventLogger::log('busca-cria-usuario-erro', $dadosAluno . " ID: " . $user_id, $origin);
         return new \WP_Error('user_creation_failed', 'Erro ao criar usuário.', array('status' => 500));
      }

      add_user_meta($user_id, 'trans_cod_eduzz', $trans_cod_eduzz); // adiciona o id da transação da compra no usuario

      // Adicionar o usuário ao ARMember
      if (class_exists('ARM_Members_Lite')) {
         $armember = new \ARM_Members_Lite();
         $armember->arm_add_user_to_armember_func($user_id, 0, $membership_id);
         EventLogger::log('add-armember-sucesso', $dadosAluno . " ID: " . $user_id, $origin);
      }

      // Enviar e-mail ao usuário com seu plano
      global $wpdb, $arm_email_settings, $ARMemberLite, $arm_global_settings;
      $arm_global_settings = new \ARM_global_settings_Lite();
      $retorno = $arm_global_settings->arm_mailer($arm_email_settings->templates->on_menual_activation, $user_id);
      $retorno = $arm_global_settings->arm_mailer($arm_email_settings->templates->change_password_user, $user_id);
      EventLogger::log('email-usuario-novo-sucesso', $dadosAluno . " ID: " . $user_id, $origin);

      if ($origin == 'MANUAL_PLANILHA_INTEGRACAO') {
         EventLogger::log('cadastro-via-planilha-usuario-novo-sucesso', $dadosAluno . " ID: " . $user_id, $origin);
      } else {
         SystemeAdapter::enviarDadosSysteme($email, $phone_number, $name, $membership_id, $origin);
      }

      update_user_meta($user_id, 'description', $phone_number);


      return wp_send_json_success(array(
         'success' => true,
         'message' => $msgUserSuccess,
         'user_id' => $user_id,
         'retorno' => $retorno
      ));
   }
}
