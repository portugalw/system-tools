<?php


namespace SystemToolsHelpInfancia\Core\Repositories;

class EventLogger
{
   private $wpdb;
   private $table;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->table = $wpdb->prefix . 'log_event';
   }

   function log($event, $description, $origin, $customer_email = '')
   {

      $this->wpdb->insert(
         $this->table,
         [
            'event' => $event,
            'description' => $description,
            'origin' => $origin,
            'customer_email' => $customer_email
         ],
         [
            '%s', // Tipo para "event"
            '%s', // Tipo para "description"
            '%s', // Tipo para "customer_email"
            '%s'  // Tipo para "origin"
         ]
      );
   }
}
