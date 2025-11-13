<?php


namespace SystemToolsHelpInfancia\Core\Repositories;

class EventStoreRepository
{

   private $wpdb;
   private $table;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->table = $wpdb->prefix . 'st_event_store';
   }


   public function create($event)
   {
      $sql = $this->wpdb->prepare(
         "
            INSERT INTO {$this->table}
            (event_id, aggregate_type, aggregate_id, event_type, payload, metadata, command_id, version, created_at)
            VALUES (%s, %s, %d, %s, %s, %s, %s, %d, %s)
        ",
         $event['event_id'],
         $event['aggregate_type'],
         $event['aggregate_id'],
         $event['event_type'],
         wp_json_encode($event['payload']),
         wp_json_encode($event['metadata']),
         $event['command_id'],
         $event['version'],
         $event['created_at']
      );

      return $this->wpdb->query($sql);
   }
}
