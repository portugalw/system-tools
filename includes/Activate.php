<?php

namespace SystemToolsHelpInfancia;

defined('ABSPATH') || exit;

class Activate
{
   protected function __construct() {}

   public static function activate()
   {
      flush_rewrite_rules();
      echo "Ativei o Plugin";
   }
}
