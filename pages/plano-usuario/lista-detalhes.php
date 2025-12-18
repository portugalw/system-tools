<?php

error_log('MODAL FILE CARREGADO');

if (!defined('ABSPATH')) exit;

global $wpdb;

$tb_users   = $wpdb->prefix . 'users';
$tb_balance = $wpdb->prefix . 'st_points_balance';

$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$sql = "
    SELECT u.ID, u.display_name, u.user_email, COALESCE(b.available_points, 0) as points
    FROM $tb_users u
    LEFT JOIN $tb_balance b ON b.user_id = u.ID
";

if ($search) {
   $sql .= $wpdb->prepare(
      " WHERE u.display_name LIKE %s OR u.user_email LIKE %s",
      "%$search%",
      "%$search%"
   );
}

$sql .= " ORDER BY u.display_name ASC LIMIT 20";

$users = $wpdb->get_results($sql);
?>

<div class="st-admin-wrapper">
   <header class="st-admin-header">
      <h2><i class="fas fa-users-cog"></i> Gestão de Pontos</h2>
      <p>Administração de clientes e saldos</p>
   </header>

   <div class="card p-4">
      <form method="get" class="mb-4">
         <input type="hidden" name="page" value="st-users">
         <div class="input-group input-group-lg">
            <input type="text"
               name="s"
               class="form-control"
               placeholder="Pesquisar por nome ou e-mail"
               value="<?= esc_attr($search) ?>">
            <button class="btn btn-primary">Buscar</button>
         </div>
      </form>

      <table class="table table-hover align-middle">
         <thead>
            <tr>
               <th>Cliente</th>
               <th>Plano</th>
               <th class="text-center">Pontos</th>
               <th class="text-end">Ações</th>
            </tr>
         </thead>
         <tbody>
            <?php if ($users): foreach ($users as $user): ?>
                  <tr>
                     <td>
                        <strong><?= esc_html($user->display_name) ?></strong><br>
                        <small class="text-muted"><?= esc_html($user->user_email) ?></small>
                     </td>
                     <td><?= $user->points > 5000 ? 'Platinum' : ($user->points > 1000 ? 'Gold' : 'Standard') ?></td>
                     <td class="text-center"><?= number_format($user->points, 0, ',', '.') ?></td>
                     <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm btn-view-client"
                           data-user-id="<?= $user->ID ?>"
                           data-user-name="<?= esc_attr($user->display_name) ?>">
                           Detalhes
                        </button>
                     </td>
                  </tr>
               <?php endforeach;
            else: ?>
               <tr>
                  <td colspan="4" class="text-center text-muted">Nenhum usuário encontrado</td>
               </tr>
            <?php endif; ?>
         </tbody>
      </table>
   </div>
</div>

<?php include __DIR__ . '\lista-detalhes-modal.php'; ?>