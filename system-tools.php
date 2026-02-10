<?php

/**
 * Plugin Name:     System Tools - Help Infância
 * Plugin URI:      HELP INFANCIA
 * Description:     HELP INFANCIA
 * Author:          Washington Portugal
 * Author URI:      YOUR SITE HERE
 * Text Domain:     system-tools-hi
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         System_Tools
 */

use function Avifinfo\read;

use SystemToolsHelpInfancia\Plugin;

// Your code starts here.

defined('ABSPATH') || die('Adiós, cracker!');

define('ST_PLUGIN_FILE', __FILE__);
define('ST_PLUGIN_NAME', plugin_basename(__FILE__));
define('ST_PLUGIN_PATH', untrailingslashit(plugin_dir_path(ST_PLUGIN_FILE)));
define('ST_PLUGIN_URL', untrailingslashit(plugin_dir_url(ST_PLUGIN_FILE)));

//PAGES
$pagesPrefixFolder = ST_PLUGIN_PATH . '/pages';
define('ST_PAGE_ADMIN_INDEX', $pagesPrefixFolder  . '/admin.php');
define('ST_PAGE_ADMIN_CADASTRO_TEMPLATE_EMAIL', $pagesPrefixFolder  . '/cadastro-template-email.php');
define('ST_PAGE_ADMIN_CADASTRO_PLANO_USUARIO', $pagesPrefixFolder  . '/plano-usuario/cadastro.php');
define('ST_PAGE_ADMIN_DEBITO_PONTOS_USUARIO', $pagesPrefixFolder  . '/plano-usuario/debito-pontos.php');
define('ST_PAGE_ADMIN_EXPIRAR_PONTOS_USUARIO', $pagesPrefixFolder  . '/plano-usuario/expirar-pontos.php');
define('ST_PAGE_ADMIN_PLANO_USUARIOS_DETALHES', $pagesPrefixFolder  . '/plano-usuario/lista-detalhes.php');
define('ST_PAGE_ADMIN_PLANO_CONFIGURACAO_VINCULO', $pagesPrefixFolder  . '/plano-configuracao/plano-configuracao-vinculo.php');


define('ST_PAGE_ADMIN_EVENT_LOG', $pagesPrefixFolder  . '/event-log-view.php');
define('ST_PAGE_ADMIN_REQUEST_LOG', $pagesPrefixFolder  . '/request-log-view.php');

define('ST_PAGE_PUBLIC_PLANO_USUARIO_EXTRATO', $pagesPrefixFolder  . '/public/plano-usuario/extrato.php');

$pagesActionPrefixFolder = ST_PLUGIN_PATH . '/page-actions';
define('ST_PAGE_ACTION_ADMIN_PLANO_USUARIOS_DETALHES', $pagesActionPrefixFolder  . '/plano-usuarios-detalhes-actions.php');
define('ST_PAGE_ACTION_ADMIN_PLANO_CONFIGURACAO', $pagesActionPrefixFolder  . '/plano-configuracao-vinculo-actions.php');

define('ST_PAGE_ACTION_PUBLIC_PLANO_USUARIO_EXTRATO', $pagesActionPrefixFolder  . '/public/plano-usuario-extrato-actions.php');


$shortCodesPrefixFolder = ST_PLUGIN_PATH . '/includes/short-codes';
define('ST_SHORT_CODE_PUBLIC_PLANO_USUARIO_EXTRATO', $shortCodesPrefixFolder  . '/public/short-code-extrato-perfil-usuario.php');
define('ST_SHORT_CODE_PUBLIC_EMBED_FORMULARIO_HELP_INFANCIA', $shortCodesPrefixFolder  . '/public/short-code-formulario-help-infancia.php');

require_once ST_PAGE_ACTION_ADMIN_PLANO_USUARIOS_DETALHES;
require_once ST_PAGE_ACTION_ADMIN_PLANO_CONFIGURACAO;
require_once ST_PAGE_ACTION_PUBLIC_PLANO_USUARIO_EXTRATO;

require_once ST_SHORT_CODE_PUBLIC_PLANO_USUARIO_EXTRATO;
require_once ST_SHORT_CODE_PUBLIC_EMBED_FORMULARIO_HELP_INFANCIA;


require_once ST_PLUGIN_PATH . '/includes/Plugin.php';
require_once ST_PLUGIN_PATH . '/includes/Activate.php';
require_once ST_PLUGIN_PATH . '/includes/Deactivate.php';





//External

if (file_exists(MEMBERSHIPLITE_DIR . '/core/classes/class.arm_members.php')) {
   require_once MEMBERSHIPLITE_DIR . '/core/classes/class.arm_members.php';
}


if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
   require_once dirname(__FILE__) . '/vendor/autoload.php';
}

if (class_exists(Plugin::class)) {



   function STHI(): ?Plugin
   {
      return Plugin::getInstance();
   }
   STHI()->register();
}

add_action('plugins_loaded', array(STHI(), 'init'));

//ativação do plugin
register_activation_hook(ST_PLUGIN_FILE, array(STHI(), 'activate'));
//desativação do plugin
register_deactivation_hook(ST_PLUGIN_FILE, array(STHI(), 'deactivate'));
