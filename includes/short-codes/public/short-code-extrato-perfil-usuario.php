<?php

add_shortcode('extrato_pontos', 'st_extrato_pontos_shortcode');

function st_extrato_pontos_shortcode()
{

   wp_enqueue_style(
      'stpluginstylepublicplanousuarioextratocss',
      ST_PLUGIN_STYLE_PUBLIC_PLANO_USUARIO_EXTRATO_CSS,
      [],
      filemtime(ST_PLUGIN_STYLE_PUBLIC_PLANO_USUARIO_EXTRATO_CSS)
   );


   wp_enqueue_script(
      'st-public-plano-usuario-extrato-js',
      ST_PLUGIN_SCRIPT_PUBLIC_PLANO_USUARIO_EXTRATO_JS,
      ['jquery', 'mypluginscript', 'bootstrap-js'],
      filemtime(ST_PLUGIN_SCRIPT_PUBLIC_PLANO_USUARIO_EXTRATO_JS),
      true
   );

   wp_localize_script('st-public-plano-usuario-extrato-js', 'ST_EXTRATO', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('st_ajax_nonce'),
   ]);


   ob_start();

   $template = ST_PAGE_PUBLIC_PLANO_USUARIO_EXTRATO;

   if (file_exists($template)) {
      include $template;
   } else {
      echo "<p>Erro: Template não encontrado em <strong>$template</strong>.</p>";
   }

   return ob_get_clean();
}
