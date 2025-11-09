<?php

namespace SystemToolsHelpInfancia;

class Util
{
   function __construct() {}

   static function concatenaDados($email, $phone, $name, $memberId)
   {
      return  " Nome: " . $name . ", Email: " . $email . ", Telefone: " . $phone . ", Plano: " . $memberId;
   }

   static function formatPhone($phone)
   {
      // Remove tudo que não seja número ou +
      $cleaned = preg_replace('/[^0-9+]/', '', $phone);

      // Garante que o resultado não ultrapasse 15 caracteres
      $cleaned = substr($cleaned, 0, 15);

      return $cleaned;
   }
}
