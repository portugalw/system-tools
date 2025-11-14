<?php

namespace SystemToolsHelpInfancia\Core\Repositories;


class PointsBatchRepository
{

   private $wpdb;
   private $table;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->table = $wpdb->prefix . 'st_points_batches';
   }

   function create($user_id, $event_id, $points, $expires_at, $metadata)
   {
      // Insert batch
      return $this->wpdb->insert("{ $this->table}", [
         'user_id' => $user_id,
         'origin_event_id' => $event_id,
         'points_total' => $points,
         'points_remaining' => $points,
         'expires_at' => $expires_at,
         'metadata' => $metadata
      ], ['%d', '%s', '%d', '%d', '%s', '%s']);
   }
}
