<?php

namespace SystemToolsHelpInfancia\Core\Services;

use SystemToolsHelpInfancia\Core\Repositories\PointsBatchRepository;

if (!defined('ABSPATH')) exit;

class PointsService
{

   private $wpdb;
   private $tableBatches;
   private $tableEvents;
   private $pointsBatchRepository;

   public function __construct(\wpdb $wpdb)

   {
      $this->wpdb = $wpdb;
      $this->tableBatches = $wpdb->prefix . 'st_points_batches';
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
      $result = $wpdb->insert("{$prefix}st_points_batches", [
         'user_id' => $user_id,
         'origin_event_id' => $event['event_id'],
         'points_total' => $points,
         'points_remaining' => $points,
         'expires_at' => $expires_at,
         'metadata' => wp_json_encode($payload)
      ], ['%d', '%s', '%d', '%d', '%s', '%s']);

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }

      // Update balance (upsert)
      $result = $wpdb->query($wpdb->prepare("
            INSERT INTO {$prefix}st_points_balance (user_id, available_points, total_earned, last_event_id)
            VALUES (%d, %d, %d, %s)
            ON DUPLICATE KEY UPDATE
               available_points = available_points + VALUES(available_points),
               total_earned = total_earned + VALUES(total_earned),
               last_event_id = VALUES(last_event_id)
        ", $user_id, $points, $points, $event['event_id']));

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }

      // Insert transaction
      $balance_after = self::getUserBalance($wpdb, $user_id);

      $result = $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event['event_id'],
         'type' => 'credit',
         'amount' => $points,
         'balance_after' => $balance_after,
         'note' => $payload['description'] ?? null,
         'related_resource' => $payload['source'] ?? null
      ], ['%d', '%s', '%s', '%d', '%d', '%s', '%s']);

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }
   }

   private static function applyConsume(\wpdb $wpdb, array $event)
   {
      $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $event_id = $event['event_id'];
      $points_to_consume = (int)($payload['points'] ?? 0);

      $balance = self::getUserBalance($wpdb, $user_id);

      if ($balance < $points_to_consume) {
         throw new \Exception("Saldo insuficiente: Pontos para consumir: $points_to_consume ponto(s). Saldo: $balance ponto(s)");
      }
      // 2. Select batches ordered by expires_at ASC (earliest expire first), then created_at
      $queryBatches = "
        SELECT batch_id, points_remaining
        FROM {$wpdb->prefix}st_points_batches
        WHERE user_id = %d
          AND points_remaining > 0
          AND status = 'active'
        ORDER BY 
            COALESCE(expires_at, '9999-12-31') ASC,
            created_at ASC
        FOR UPDATE
    ";
      $batches = $wpdb->get_results($wpdb->prepare($queryBatches, $user_id), ARRAY_A);

      $allocations = [];
      $remaining = $points_to_consume;
      foreach ($batches as $b) {
         if ($remaining <= 0) break;
         $take = min($b['points_remaining'], $remaining);
         $batch_id = $b['batch_id'];
         // decrement batch
         $result =  $wpdb->query($wpdb->prepare("
                UPDATE {$prefix}st_points_batches
                SET points_remaining = GREATEST(points_remaining - %d, 0)
                WHERE batch_id = %d
            ", $take, $batch_id));

         if ($result === false) {
            throw new \Exception("Erro SQL: {$wpdb->last_error}");
         }

         $allocations[] = ['batch_id' => $batch_id, 'points' => $take];
         $remaining -= $take;
      }
      if ($remaining > 0) {
         throw new \Exception("Erro de alocação: não foi possível alocar todos os pontos. Pontos Restantes: $remaining");
      }


      // Update balance
      $result =  $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_balance
            SET available_points = available_points - %d,
                total_spent = total_spent + %d,
                last_event_id = %s
            WHERE user_id = %d
        ", $points_to_consume, $points_to_consume, $event['event_id'], $user_id));
      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }

      $balance_after = self::getUserBalance($wpdb, $user_id);
      // Insert transaction
      $result =  $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event_id,
         'type' => 'consume',
         'amount' => -$points_to_consume,
         'balance_after' => $balance_after,
         'related_resource' => $payload['usage_id'] ?? null,
         'batch_afected' =>  wp_json_encode($allocations),
      ], ['%d', '%s', '%s', '%d', '%d', '%s', '%s']);

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }
   }

   private static function applyExpire(\wpdb $wpdb, array $event)
   {
      $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $batch_id = (int)$payload['batch_id'];
      $expired_points = (int)$payload['expired_points'];
      $allocation = ['batch_id' => $batch_id, 'expired_points' => $expired_points];
      $source = (int)$payload['source'];

      if ($expired_points <= 0) {
         // Still mark batch expired if needed
         $wpdb->query($wpdb->prepare("UPDATE {$prefix}st_points_batches SET status='expired' WHERE batch_id=%d", $batch_id));
         return;
      }

      // Update batch (zero remaining, mark expired)
      $result = $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_batches
            SET points_remaining = 0,
                status = 'expired'
            WHERE batch_id = %d
        ", $batch_id));

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }

      // Update balance
      $result = $wpdb->query($wpdb->prepare("
            UPDATE {$prefix}st_points_balance
            SET available_points = GREATEST(available_points - %d, 0),
                total_expired = total_expired + %d,
                last_event_id = %s
            WHERE user_id = %d
        ", $expired_points, $expired_points, $event['event_id'], $user_id));

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }

      $balance_after = self::getUserBalance($wpdb, $user_id);

      $result = $wpdb->insert("{$prefix}st_points_transactions", [
         'user_id' => $user_id,
         'event_id' => $event['event_id'],
         'type' => 'expire',
         'amount' => -$expired_points,
         'balance_after' => $balance_after,
         'related_resource' =>  $source,
         'batch_afected' => wp_json_encode($allocation),
         'metadata' => wp_json_encode($allocation),
      ], ['%d', '%s', '%s', '%d', '%d', '%s', '%s']);

      if ($result === false) {
         throw new \Exception("Erro SQL: {$wpdb->last_error}");
      }
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
      //echo $res->available_points;
      return $res ? (int)$res->available_points : 0;
   }


   /* $prefix = $wpdb->prefix;
      $payload = $event['payload'];
      $user_id = (int)$event['aggregate_id'];
      $batch_id = (int)$payload['batch_id'];
      $expired_points = (int)$payload['expired_points'];
      $allocation = ['batch_id' => $batch_id, 'expired_points' => $expired_points];
      $source = (int)$payload['source'];*/

   public function markBatchExpired(): void
   {
      global $wpdb;

      $limit = 30;

      // 🔹 Cria execução (run)
      $wpdb->insert("{$wpdb->prefix}st_points_expiration_runs", [
         'started_at' => current_time('mysql'),
         'status' => 'running'
      ]);

      $run_id = $wpdb->insert_id;

      $total = 0;
      $success = 0;
      $error = 0;

      try {

         do {
            $batches = $wpdb->get_results($wpdb->prepare("
                SELECT batch_id, user_id, points_remaining
                FROM {$this->tableBatches}
                WHERE expires_at <= NOW()
                  AND status = 'active'
                LIMIT %d
            ", $limit));

            if (empty($batches)) {
               break;
            }

            foreach ($batches as $batch) {

               $total++;

               try {

                  // 🔹 Idempotência
                  if ((int)$batch->points_remaining <= 0) {
                     $this->logExpiration($run_id, $batch, 'skipped', 'Batch sem pontos');
                     continue;
                  }

                  // 🔹 Monta evento esperado pelo applyExpire
                  $event = [
                     'event_id' => uniqid('expire_', true),
                     'event_type' => 'PointsExpired',
                     'aggregate_id' => (int)$batch->user_id,
                     'payload' => [
                        'batch_id'       => (int)$batch->batch_id,
                        'expired_points' => (int)$batch->points_remaining,
                        'source'         => 'cron-expire'
                     ]
                  ];

                  // 🔥 Chamada direta (sem event store)
                  self::apply($wpdb, $event);

                  $success++;

                  $this->logExpiration(
                     $run_id,
                     $batch,
                     'success',
                     'Expiração aplicada via applyExpire'
                  );
               } catch (\Throwable $e) {

                  $error++;

                  $this->logExpiration(
                     $run_id,
                     $batch,
                     'error',
                     $e->getMessage(),
                     $e->getTraceAsString()
                  );

                  continue;
               }
            }
         } while (count($batches) === $limit);

         // 🔹 Finaliza execução
         $wpdb->update("{$wpdb->prefix}st_points_expiration_runs", [
            'finished_at' => current_time('mysql'),
            'total_records' => $total,
            'success_count' => $success,
            'error_count' => $error,
            'status' => 'finished'
         ], ['run_id' => $run_id]);
      } catch (\Throwable $e) {

         $wpdb->update("{$wpdb->prefix}st_points_expiration_runs", [
            'finished_at' => current_time('mysql'),
            'status' => 'failed',
            'error_message' => $e->getMessage()
         ], ['run_id' => $run_id]);

         error_log('[PointsExpiration] ERRO CRÍTICO: ' . $e->getMessage());
      }
   }

   private function logExpiration(
      int $run_id,
      $batch,
      string $status,
      string $message,
      string $stack = ''
   ): void {

      $this->wpdb->insert(
         "{$this->wpdb->prefix}st_points_expiration_logs",
         [
            'run_id' => $run_id,
            'batch_id' => (int)$batch->batch_id,
            'user_id' => (int)$batch->user_id,
            'points' => (int)$batch->points_remaining,
            'status' => $status,
            'message' => $message,
            'error_stack' => $stack,
            'created_at' => current_time('mysql')
         ]
      );
   }
}
