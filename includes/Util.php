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

   public static function generateUuidV4(): string
   {
      if (function_exists('ramsey_uuid')) {
         return \Ramsey\Uuid\Uuid::uuid4()->toString();
      }

      return sprintf(
         '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
         mt_rand(0, 0xffff),
         mt_rand(0, 0xffff),
         mt_rand(0, 0xffff),
         mt_rand(0, 0x0fff) | 0x4000,
         mt_rand(0, 0x3fff) | 0x8000,
         mt_rand(0, 0xffff),
         mt_rand(0, 0xffff),
         mt_rand(0, 0xffff)
      );
   }
}
