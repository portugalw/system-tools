<?php

namespace SystemToolsHelpInfancia\Public\WebHooks;

class WebHookFormularioHelpInfanciaOnSubmit
{
   public function __construct()
   {
      add_action(
         'rest_api_init',
         function () {
            register_rest_route('helpinfancia-zohoform-webhook/v1', '/update-user-points', array(
               'methods' => 'POST',
               'callback' => 'update_user_points',
               'permission_callback' => '__return_true',
            ));
         }
      );
   }
   function update_user_points($request)
   {
      $parameters = $request->get_json_params();

      request_logger_handle_request($request);

      $email = sanitize_email($parameters['email']);
      $userid = sanitize_text_field($parameters['userid']);
      $formid = sanitize_text_field($parameters['formid']);
      $password = 'helpinfancia';
      $membership_id = 3; // ID do plano no ARMember 24 horas
      $user_id = false;



      return array(
         'success' => true,
         'message' => 'Usuário criado com sucesso.',
         'user_id' => $user_id,
      );
   }
}


https://helpinfancia.com.br/help/wp-json/helpinfancia-zohoform-webhook/v1/update-user-points