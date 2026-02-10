<?php


add_shortcode('embed_helpinfancia_form_with_email', 'create_embed_helpinfancia_form_with_email');

// criando embd para renderizar o URL do help infancia do ZOHO com o email do usuario logado
function create_embed_helpinfancia_form_with_email($atts)
{
   // Verifica se o usuário está logado
   if (is_user_logged_in()) {
      // Obtém o usuário atual
      $current_user = wp_get_current_user();
      $email = urlencode($current_user->user_email); // Obtém e escapa o e-mail do usuário

      // Define os atributos do shortcode e a URL padrão
      $atts = shortcode_atts(
         array(
            'url' => '', // URL base a ser passada no shortcode
         ),
         $atts
      );

      // Verifica se a URL foi fornecida 
      if (empty($atts['url'])) {
         return 'Erro: Nenhuma URL fornecida para o embed.';
      }

      // Constrói a URL com o e-mail como parâmetro
      $embed_url = $atts['url'] . '?e=' . $email;

      // Retorna o iframe com a URL gerada
      return '<iframe src="' . esc_url($embed_url) . '" width="100%" height="1100px" frameborder="0"></iframe>';
   } else {
      return 'Você precisa estar logado para visualizar este conteúdo.';
   }
}
