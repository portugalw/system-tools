<?php

namespace SystemToolsHelpInfancia\Core\Services;

use SystemToolsHelpInfancia\Core\Factory\EventFactory;
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

   function handle_purchase_plan($user_id, $plan_id)
   {
      $points = 10; // regra
      $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

      $payload = ['user_id' => $user_id, 'plan_id' => $plan_id, 'points' => $points, 'expires_at' => $expires_at, 'source' => 'plan_purchase'];
      $meta = ['actor_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR']];

      $event = EventFactory::create(
         'UserPoints',
         $user_id,
         'AdminGrantedPoints',
         $payload,
         $meta
      );

      $this->appendEvent($event);
      return true;
   }

   /**
    * Append event atomically and apply projection synchronously.
    * Returns true if inserted, false if duplicate.
    */
   public function appendEvent(array $event): bool
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
