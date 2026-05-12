<?php

if (!defined('ABSPATH')) exit;

if (defined('WP_CLI') && WP_CLI) {

   WP_CLI::add_command('points_expire', function () {

      global $wpdb;

      set_time_limit(0);
      ini_set('memory_limit', '512M');

      $lock_key = 'st_points_expire_lock';

     /* if (get_transient($lock_key)) {
         WP_CLI::warning('Já existe um processo em execução.');
         return;
      }*/

      set_transient($lock_key, 1, 60 * 2);

      try {

         $service = new \SystemToolsHelpInfancia\Core\Services\PointsService($wpdb);

         $run_id = $service->markBatchExpired($wpdb);

         WP_CLI::success("Run ID: {$run_id}");
      } catch (\Throwable $e) {

         error_log('[CRON] ' . $e->getMessage());
         WP_CLI::error($e->getMessage());
      } finally {
         delete_transient($lock_key);
      }
   });
}
