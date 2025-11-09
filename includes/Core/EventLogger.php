<?php


namespace SystemToolsHelpInfancia\Core;

class EventLogger
{
   function __construct() {}

   static function log($event, $description, $origin)
   {
      global $wpdb;

      $nome_tabela = $wpdb->prefix . 'log_event';

      $wpdb->insert(
         $nome_tabela,
         [
            'event' => $event,
            'description' => $description,
            'origin' => $origin
         ],
         [
            '%s', // Tipo para "event"
            '%s', // Tipo para "description"
            '%s'  // Tipo para "origin"
         ]
      );
   }
}
