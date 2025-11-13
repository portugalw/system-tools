<?php

namespace SystemToolsHelpInfancia\Core\Factory;


if (!defined('ABSPATH')) exit;

use SystemToolsHelpInfancia\Util;

class EventFactory
{
   public static function create(
      string $aggregateType,
      int $aggregateId,
      string $eventType,
      array $payload,
      array $metadata = [],
      ?string $commandId = null,
      int $version = 1
   ): array {
      $eventId =  Util::generateUuidV4();

      if (!$commandId) {
         $commandId = Util::generateUuidV4();
      }

      $metadata = array_merge([
         'created_by' => $metadata['created_by'] ?? 'administrator',
         'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli'
      ], $metadata);

      return [
         'event_id' => $eventId,
         'aggregate_type' => $aggregateType,
         'aggregate_id' => $aggregateId,
         'event_type' => $eventType,
         'payload' => $payload,
         'metadata' => $metadata,
         'command_id' => $commandId,
         'version' => $version,
         'created_at' => date('Y-m-d H:i:s')
      ];
   }
}
