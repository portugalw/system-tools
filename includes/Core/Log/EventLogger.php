<?php

namespace SystemToolsHelpInfancia\Core\Log;

use SystemToolsHelpInfancia\Core\Repositories\EventLoggerRepository;

if (!defined('ABSPATH')) exit;

class EventLogger
{


   public static function LogError($event, $description, $origin, $customer_email = '')
   {
      global $wpdb;
      $log = new EventLoggerRepository($wpdb);

      return $log->log($event, $description, $origin, 'ERROR', $customer_email);
   }

   public static function LogInfo($event, $description, $origin, $customer_email = '')
   {
      global $wpdb;
      $log = new EventLoggerRepository($wpdb);

      return $log->log($event, $description, $origin, 'INFO', $customer_email);
   }

   public static function LogWarn($event, $description, $origin, $customer_email = '')
   {
      global $wpdb;
      $log = new EventLoggerRepository($wpdb);

      return $log->log($event, $description, $origin, 'WARN', $customer_email);
   }
}
