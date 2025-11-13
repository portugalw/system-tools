<?php

namespace SystemToolsHelpInfancia\Core\Services;

use SystemToolsHelpInfancia\Core\Repositories\EventStoreRepository;

if (!defined('ABSPATH')) exit;

class EventStoreService
{
   private $wpdb;
   private $eventStoreRepository;

   public function __construct(\wpdb $wpdb)
   {
      $this->wpdb = $wpdb;
      $this->eventStoreRepository = new EventStoreRepository($wpdb);
   }

   /**
    * Append event atomically and apply projection synchronously.
    * Returns true if inserted, false if duplicate.
    */
   public function append(array $event): bool
   {
      // Use $wpdb->prepare for safety and wpdb->query for transactions
      $this->wpdb->query('START TRANSACTION');

      $res = $this->eventStoreRepository->create($event);

      if ($res === false) {
         // Duplicate or DB error
         $error = $this->wpdb->last_error;
         $this->wpdb->query('ROLLBACK');
         // If duplicate command_id or event_id, treat as idempotent
         if (stripos($error, 'Duplicate') !== false) {
            return false;
         }
         throw new \RuntimeException("EventStore append failed: {$error}");
      }

      // Apply to projections synchronously (ProjectionHandler may also be async in bigger systems)
      PointsService::apply($this->wpdb, $event);

      $this->wpdb->query('COMMIT');
      return true;
   }
}
