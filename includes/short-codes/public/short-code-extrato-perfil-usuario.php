<?php
add_shortcode('extrato_pontos', 'st_extrato_pontos_shortcode');

function st_extrato_pontos_shortcode()
{
   ob_start();

   // Caminho até o template
   $template = ST_PAGE_PUBLIC_PLANO_USUARIO_EXTRATO;
   echo $template;
   if (file_exists($template)) {
      include $template;
   } else {
      echo "<p>Erro: template extrato-view.php não encontrado.</p>";
   }

   return ob_get_clean();
}
