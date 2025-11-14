<?php

namespace SystemToolsHelpInfancia\Core\Services;

use SystemToolsHelpInfancia\Core\Repositories\EventStoreRepository;
use SystemToolsHelpInfancia\Core\Repositories\PointsBatchRepository;

if (!defined('ABSPATH')) exit;

class PointsService
{

   private $wpdb;
   private $tableBatches;
   private $tableProjection;
   private $tableEvents;
   private $pointsBatchRepository;

   public function __construct(\wpdb $wpdb)

   {
      $this->wpdb = $wpdb;
      $this->tableBatches = $wpdb->prefix . 'st_points_batches';
      $this->tableProjection = $wpdb->prefix . 'st_points_projection';
      $this->tableEvents = $wpdb->prefix . 'st_points_events';
   }

   public static function apply(\wpdb $wpdb, array $event): void
   {
      $pointsBatchRepository = new PointsBatchRepository($wpdb);


      switch ($event['event_type']) {
         case 'PlanPurchased':
         case 'PointsCredited':
         case 'AdminGrantedPoints':
            self::applyCredit($wpdb, $event);
            break;

         case 'PointsConsumed':
         case 'AdminDeductedPoints':
            self::applyConsume($wpdb, $event);
            break;

         case 'PointsExpired':
            self::applyExpire($wpdb, $event);
            break;

         case 'PointsCompensated':
            self::applyCompensate($wpdb, $event);
            break;

         default:
            // Unknown event -> ignore or log
            break;
      }
   }

   private static function applyCredit(\wpdb $wpdb, array $event)
   {
      $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $points = (int)($payload['points'] ?? 0);
      $expires_at = isset($payload['expires_at']) ? $payload['expires_at'] : null;

      if ($points <= 0) return;

      // Insert batch
      $wpdb->insert("{$prefix}st_points_batches", [
         'user_id' => $user_id,
         'origin_event_id' => $event['event_id'],
         'points_total' => $points,
         'points_remaining' => $points,
         'expires_at' => $expires_at,
         'metadata' => wp_json_encode($payload)
      ], ['%d', '%s', '%d', '%d', '%s', '%s']);

      // Update balance (upsert)
      $wpdb->query($wpdb->prepare("
            INSERT INTO {$prefix}st_points_balance (user_id, available_points, total_earned, last_event_id)
            VALUES (%d, %d, %d, %s)
            ON DUPLICATE KEY UPDATE
               available_points = available_points + VALUES(available_points),
               total_earned = total_earned + VALUES(total_earned),
               last_event_id = VALUES(last_event_id)
        ", $user_id, $points, $points, $event['event_id']));

      // Insert transaction
      $balance_after = self::getUserBalance($wpdb, $user_id);
      $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event['event_id'],
         'type' => 'credit',
         'amount' => $points,
         'balance_after' => $balance_after,
         'related_resource' => $payload['reference'] ?? null
      ], ['%d', '%s', '%s', '%d', '%d', '%s']);
   }

   private static function applyConsume(\wpdb $wpdb, array $event)
   {
      $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $points = (int)($payload['points'] ?? 0);
      $alloc = $payload['allocation'] ?? [];

      if ($points <= 0) return;

      // For each allocation, reduce points_remaining in batch
      foreach ($alloc as $a) {
         $batch_id = (int)$a['batch_id'];
         $qty = (int)$a['points'];
         if ($qty <= 0) continue;
         $wpdb->query($wpdb->prepare("
                UPDATE {$prefix}st_points_batches
                SET points_remaining = GREATEST(points_remaining - %d, 0),
                    points_total = points_total
                WHERE batch_id = %d
            ", $qty, $batch_id));
      }

      // Update balance
      $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_balance
            SET available_points = available_points - %d,
                total_spent = total_spent + %d,
                last_event_id = %s
            WHERE user_id = %d
        ", $points, $points, $event['event_id'], $user_id));

      $balance_after = self::getUserBalance($wpdb, $user_id);
      // Insert transaction
      $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event['event_id'],
         'type' => 'consume',
         'amount' => -$points,
         'balance_after' => $balance_after,
         'related_resource' => $payload['usage_id'] ?? null
      ], ['%d', '%s', '%s', '%d', '%d', '%s']);
   }

   private static function applyExpire(\wpdb $wpdb, array $event)
   {
      $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $batch_id = (int)$payload['batch_id'];
      $expired_points = (int)$payload['expired_points'];

      if ($expired_points <= 0) {
         // Still mark batch expired if needed
         $wpdb->query($wpdb->prepare("UPDATE {$prefix}points_batches SET status='expired' WHERE batch_id=%d", $batch_id));
         return;
      }

      // Update batch (zero remaining, mark expired)
      $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_batches
            SET points_remaining = 0,
                status = 'expired'
            WHERE batch_id = %d
        ", $batch_id));

      // Update balance
      $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_balance
            SET available_points = GREATEST(available_points - %d, 0),
                total_expired = total_expired + %d,
                last_event_id = %s
            WHERE user_id = %d
        ", $expired_points, $expired_points, $event['event_id'], $user_id));

      $balance_after = self::getUserBalance($wpdb, $user_id);

      // Insert transaction
      $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event['event_id'],
         'type' => 'expire',
         'amount' => -$expired_points,
         'balance_after' => $balance_after,
         'related_resource' => $batch_id
      ], ['%d', '%s', '%s', '%d', '%d', '%d']);
   }

   private static function applyCompensate(\wpdb $wpdb, array $event)
   {
      // Simple implementation: credit or adjust projections according to payload
      $payload = $event['payload'];
      // e.g. payload = ['user_id'=>.., 'points'=>.., 'reason'=>..]
      // For brevity, call applyCredit with PointsCredited-like payload
      $event['event_type'] = 'PointsCredited';
      self::applyCredit($wpdb, $event);
   }

   public static function getUserBalance(\wpdb $wpdb, int $user_id): int
   {
      $prefix = $wpdb->prefix;
      $res = $wpdb->get_row($wpdb->prepare("SELECT available_points FROM {$prefix}st_points_balance WHERE user_id = %d", $user_id));
      return $res ? (int)$res->available_points : 0;
   }

   private static function getUserBalanceAlias(\wpdb $wpdb, int $user_id)
   {
      return self::getUserBalance($wpdb, $user_id);
   }


   public function markBatchExpired(\wpdb $wpdb): void
   {
      $expiredBatches = $wpdb->get_results(
         "SELECT * FROM $this->tableBatches 
             WHERE expires_at <= NOW() AND status = 'active'"
      );

      foreach ($expiredBatches as $batch) {
         $wpdb->update(
            $this->tableBatches,
            ['status' => 'expired'],
            ['id' => $batch->id]
         );

         $this->recordEvent($batch->user_id, 'POINTS_EXPIRED', [
            'batch_id' => $batch->id,
            'expired_amount' => ($batch->amount - $batch->used)
         ]);

         $this->recalculateProjection($batch->user_id);
      }
   }

   /**
    * Recalcula o total e disponível do usuário (projeção).
    */
   private function recalculateProjection(int $user_id): void
   {
      $result = $this->wpdb->get_row(
         $this->wpdb->prepare(
            "SELECT 
                    SUM(amount) AS total,
                    SUM(amount - used) AS available
                 FROM $this->tableBatches
                 WHERE user_id = %d AND status = 'active'",
            $user_id
         )
      );

      $total = $result->total ?? 0;
      $available = $result->available ?? 0;

      $exists = $this->wpdb->get_var(
         $this->wpdb->prepare(
            "SELECT COUNT(*) FROM $this->tableProjection WHERE user_id = %d",
            $user_id
         )
      );

      if ($exists) {
         $this->wpdb->update(
            $this->tableProjection,
            [
               'total_points' => $total,
               'available_points' => $available,
               'last_updated' => current_time('mysql')
            ],
            ['user_id' => $user_id]
         );
      } else {
         $this->wpdb->insert(
            $this->tableProjection,
            [
               'user_id' => $user_id,
               'total_points' => $total,
               'available_points' => $available,
               'last_updated' => current_time('mysql')
            ]
         );
      }
   }

   /**
    * Registra evento no Event Store.
    */
   private function recordEvent(int $user_id, string $type, array $data): void
   {
      $this->wpdb->insert(
         $this->tableEvents,
         [
            'user_id' => $user_id,
            'event_type' => $type,
            'data_json' => wp_json_encode($data),
            'created_at' => current_time('mysql')
         ]
      );
   }
}
