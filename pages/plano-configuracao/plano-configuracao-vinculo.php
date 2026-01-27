<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// Nomes das tabelas
$tb_arm_plans = $wpdb->prefix . 'arm_subscription_plans'; // Ajuste se o prefixo for diferente
$tb_config    = $wpdb->prefix . 'st_plans_config';

// Busca e Filtros
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Query Principal com LEFT JOIN
$sql = "
    SELECT 
        p.arm_subscription_plan_id as plan_id,
        p.arm_subscription_plan_name as plan_name,
        c.points,
        c.days_expire,
        c.is_active,
        c.updated_at
    FROM $tb_arm_plans p
    LEFT JOIN $tb_config c ON p.arm_subscription_plan_id = c.arm_subscription_plan_id
";

if ($search) {
   $sql .= $wpdb->prepare(" WHERE p.arm_subscription_plan_name LIKE %s", "%$search%");
}

$sql .= " ORDER BY p.arm_subscription_plan_name ASC";

$plans = $wpdb->get_results($sql);
?>

<div class="st-admin-wrapper">
   <header class="st-admin-header mb-4">
      <h2><i class="fas fa-list-alt"></i> Configuração de Planos</h2>
      <p>Defina a pontuação e validade para cada plano do ARMember.</p>
   </header>

   <div class="card p-4 shadow-sm">
      <form method="get" class="mb-4">
         <input type="hidden" name="page" value="st-plans-config">
         <div class="input-group input-group-lg">
            <input type="text"
               name="s"
               class="form-control"
               placeholder="Pesquisar por nome do plano..."
               value="<?= esc_attr($search) ?>">
            <button class="btn btn-primary">Buscar</button>
         </div>
      </form>

      <div class="table-responsive">
         <table class="table table-hover align-middle">
            <thead class="table-light">
               <tr>
                  <th>ID</th>
                  <th>Nome do Plano</th>
                  <th class="text-center">Pontos</th>
                  <th class="text-center">Expira em (Dias)</th>
                  <th class="text-center">Status Config.</th>
                  <th>Última Edição</th>
                  <th class="text-end">Ações</th>
               </tr>
            </thead>
            <tbody>
               <?php if ($plans): foreach ($plans as $plan):
                     // Tratamento de dados nulos (caso não tenha config)
                     $has_config = !is_null($plan->points);
                     $points = $has_config ? $plan->points : 0;
                     $days   = $has_config ? $plan->days_expire : 30;
                     $active = $has_config ? (int)$plan->is_active : 1; // Padrão ativo se for criar novo
               ?>
                     <tr>
                        <td>#<?= $plan->plan_id ?></td>
                        <td>
                           <strong><?= esc_html($plan->plan_name) ?></strong>
                        </td>
                        <td class="text-center">
                           <?php if ($has_config): ?>
                              <span class="badge bg-info text-dark"><?= $points ?> pts</span>
                           <?php else: ?>
                              <span class="text-muted">-</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <?php if ($has_config): ?>
                              <?= $days ?> dias
                           <?php else: ?>
                              <span class="text-muted">-</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <?php if (!$has_config): ?>
                              <span class="badge bg-secondary">Sem Config</span>
                           <?php elseif ($active): ?>
                              <span class="badge bg-success">Ativado</span>
                           <?php else: ?>
                              <span class="badge bg-danger">Desativado</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                           <?= $plan->updated_at ? date('d/m/Y H:i', strtotime($plan->updated_at)) : '-' ?>
                        </td>
                        <td class="text-end">
                           <button class="btn btn-outline-primary btn-sm btn-edit-plan"
                              data-id="<?= $plan->plan_id ?>"
                              data-name="<?= esc_attr($plan->plan_name) ?>"
                              data-points="<?= $points ?>"
                              data-days="<?= $days ?>"
                              data-active="<?= $active ?>">
                              <i class="fas fa-edit"></i> Editar
                           </button>
                        </td>
                     </tr>
                  <?php endforeach;
               else: ?>
                  <tr>
                     <td colspan="7" class="text-center text-muted py-4">Nenhum plano encontrado.</td>
                  </tr>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<?php include __DIR__ . '/modal-plano-configuracao-vinculo.php'; ?>
<script src="<?= plugin_dir_url(__FILE__) . 'js/plano-configuracao-vinculo.js' ?>"></script>