

<?php

namespace SystemToolsHelpInfanciaPoints\Core\BackgroundService;

use SystemToolsHelpInfancia\Core\Services\PointsService;

class PointsExpiredBackgroundService
{

   function __construct() {}
   function register()
   {
      if (!wp_next_scheduled('st_points_check_expiration')) {
         wp_schedule_event(time(), 'daily', 'st_points_check_expiration');
      }

      add_action('st_points_check_expiration', function () {
         $service = new PointsService($GLOBALS['wpdb']);
         $service->markBatchExpired($GLOBALS['wpdb']);
      });
   }
}
