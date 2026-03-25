<?php

namespace SystemToolsHelpInfancia\Core\Log;

use SystemToolsHelpInfancia\Core\Repositories\RequestLoggerRepository;

if (!defined('ABSPATH')) exit;

class RequestLogger
{
   public static function Log($request)
   {
      global $wpdb;
      $log = new RequestLoggerRepository($wpdb);

      return $log->log($request);
   }
}
