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


      return $this->appendEvent($event);
   }

   function handle_consume_plan($user_id, $plan_id,  $points)
   {

      $payload = ['user_id' => $user_id, 'plan_id' => $plan_id, 'points' => $points, 'source' => 'consumo_help_infancia'];
      $meta = ['actor_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR']];

      $event = EventFactory::create(
         'UserPoints',
         $user_id,
         'AdminDeductedPoints',
         $payload,
         $meta
      );

      return  $this->appendEvent($event);;
   }

   /**
    * Append event atomically and apply projection synchronously.
    * Returns true if inserted, false if duplicate.
    */
   public function appendEvent($event)
   {
      try {

         // -----------------------------------
         // INÍCIO DA TRANSAÇÃO
         // -----------------------------------
         $this->wpdb->query('START TRANSACTION');

         // Tenta gravar o evento no Event Store
         $res = $this->eventStoreRepository->create($event);

         if ($res === false) {
            $error = $this->wpdb->last_error;

            // Log do erro no EventStore
            error_log("[EventStore] Falha ao criar evento: {$error}");

            // Se for duplicidade => idempotência
            if (stripos($error, 'Duplicate') !== false) {
               $this->wpdb->query('ROLLBACK');

               return [
                  'success' => true,
                  'message' => 'Operação idempotente: evento já havia sido processado.'
               ];
            }

            // Outro tipo de erro => rollback e erro
            $this->wpdb->query('ROLLBACK');

            return [
               'success' => false,
               'message' => "Erro ao gravar evento no EventStore: {$error}"
            ];
         }

         // -----------------------------------
         // APLICA PROJEÇÕES (síncrono)
         // -----------------------------------
         try {
            PointsService::apply($this->wpdb, $event);
         } catch (\Throwable $t) {
            // Se falhar a projeção => rollback
            $this->wpdb->query('ROLLBACK');

            $msg = $t->getMessage();
            error_log("[Projection] Falha ao aplicar projeção: {$msg}");

            return [
               'success' => false,
               'message' => "Erro ao aplicar projeção: {$msg}"
            ];
         }

         // -----------------------------------
         // SUCESSO TOTAL
         // -----------------------------------
         $this->wpdb->query('COMMIT');

         return [
            'success' => true,
            'message' => 'Evento processado com sucesso.'
         ];
      } catch (\Throwable $e) {

         // -----------------------------------
         // FAIL-SAFE → GARANTE ROLLBACK
         // -----------------------------------
         try {
            $this->wpdb->query('ROLLBACK');
         } catch (\Throwable $ignored) {
            // ignora erro de rollback — loga somente
            error_log("[EventStore] Falha ao executar rollback: {$ignored->getMessage()}");
         }

         $msg = $e->getMessage();
         error_log("[EventStore] Erro crítico: {$msg}");

         return [
            'success' => false,
            'message' => "Erro inesperado: {$msg}"
         ];
      }
   }
}
