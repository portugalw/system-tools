<?php

namespace SystemToolsHelpInfancia\Adapters;

use SystemToolsHelpInfancia\Util;
use SystemToolsHelpInfancia\Core\EventLogger;


class SystemeAdapter
{
   function __construct() {}

   static function enviarDadosSysteme($email, $phone, $name, $memberId, $origin)
   {

      $url = "https://pv.helpinfancia.com.br/pos-compra-integracao";
      $entityId = "ec70d02f-0b8f-4fba-bae9-ef10e78af655";
      $dadosAluno = Util::concatenaDados($email, $phone, $name, $memberId);

      // Cria o JSON com os dados mapeados
      $data = [
         "optin" => [
            "fields" => [
               "email" => $email,
               "first_name" => $name
            ],
            "timeZone" => "America/Sao_Paulo",
            "popupId" => null,
            "isDesktop" => true,
            "entityId" => $entityId,
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
         EventLogger::log('enviarDadosSysteme-erro', $dadosAluno . $error, $origin);
         return ["success" => false, "error" => $error];
      }

      // Fecha o cURL
      curl_close($ch);
      EventLogger::log('enviarDadosSysteme-sucesso', $dadosAluno, $origin);
      // Retorna a resposta
      return ["success" => $httpCode === 200, "response" => $response];
   }
}
