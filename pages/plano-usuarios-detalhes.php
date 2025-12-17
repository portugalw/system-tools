<?php

/**
 * Template Name: Painel Admin Pontos
 * Description: Protótipo de gestão de pontos de clientes (Event Sourcing Projection View)
 */

// Garantir que estamos no WP
if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly
}

global $wpdb;

// --- CONFIGURAÇÃO DAS TABELAS ---
$tb_balance      = $wpdb->prefix . 'st_points_balance';
$tb_batches      = $wpdb->prefix . 'st_points_batches';
$tb_transactions = $wpdb->prefix . 'st_points_transactions';
$tb_users        = $wpdb->prefix . 'users';

// --- HANDLER AJAX (Processamento das requisições do Modal) ---
if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'get_client_details') {
   header('Content-Type: application/json');

   $user_id = intval($_GET['user_id']);

   // 1. Buscar Saldo Atual
   $balance = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tb_balance WHERE user_id = %d", $user_id));

   // 2. Buscar Pontos a Expirar (Próximos a vencer)
   // Lógica: Batches ativos, com saldo > 0 e data futura
   $expiring = $wpdb->get_row($wpdb->prepare("
        SELECT 
            SUM(points_remaining) as total_amount,
            MIN(expires_at) as next_date
        FROM $tb_batches 
        WHERE user_id = %d 
          AND status = 'active' 
          AND points_remaining > 0 
          AND expires_at >= NOW()
    ", $user_id));

   // Retorna JSON
   echo json_encode([
      'balance' => $balance ? $balance->available_points : 0,
      'total_earned' => $balance ? $balance->total_earned : 0,
      'expiring_amount' => $expiring->total_amount ?? 0,
      'expiring_date' => $expiring->next_date ? date('d/m/Y', strtotime($expiring->next_date)) : '-',
   ]);
   exit;
}

if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'get_transactions') {
   header('Content-Type: application/json');
   $user_id = intval($_GET['user_id']);

   // Buscar histórico (Limitado aos últimos 50 para performance)
   $transactions = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM $tb_transactions 
        WHERE user_id = %d 
        ORDER BY created_at DESC 
        LIMIT 50
    ", $user_id));

   echo json_encode($transactions);
   exit;
}

// --- LOGICA DA LISTAGEM PRINCIPAL ---
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$sql_users = "
    SELECT u.ID, u.display_name, u.user_email, b.available_points 
    FROM $tb_users u
    LEFT JOIN $tb_balance b ON u.ID = b.user_id
";

if (!empty($search)) {
   $sql_users .= " WHERE u.display_name LIKE '%" . esc_sql($search) . "%' OR u.user_email LIKE '%" . esc_sql($search) . "%'";
}

$sql_users .= " ORDER BY u.display_name ASC LIMIT 20";
$users_list = $wpdb->get_results($sql_users);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Administração de Pontos</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

   <style>
      body {
         font-family: 'Roboto', sans-serif;
         background-color: #f0f2f5;
         color: #333;
      }

      /* Material Cards */
      .card {
         border: none;
         border-radius: 12px;
         box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
         background: #fff;
         transition: transform 0.2s;
         min-width: 100%;
      }

      /* Header Style */
      .admin-header {
         background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%);
         color: white;
         padding: 40px 0;
         margin-bottom: -40px;
         padding-bottom: 80px;
         border-bottom-left-radius: 30px;
         border-bottom-right-radius: 30px;
      }

      /* Table Styling */
      .table-custom th {
         font-weight: 600;
         color: #555;
         border-bottom: 2px solid #e0e0e0;
         background-color: #fff;
      }

      .table-custom td {
         vertical-align: middle;
         padding: 15px;
      }

      .user-avatar {
         width: 40px;
         height: 40px;
         background-color: #E3F2FD;
         color: #1565C0;
         border-radius: 50%;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         font-weight: bold;
         margin-right: 10px;
      }

      /* Badge Styles */
      .badge-points {
         background-color: #E8F5E9;
         color: #2E7D32;
         font-size: 0.9rem;
         padding: 8px 12px;
         border-radius: 20px;
      }

      .badge-plan {
         background-color: #E3F2FD;
         color: #1565C0;
         border: 1px solid #BBDEFB;
      }

      /* Modal Customization */
      .modal-header {
         background-color: #1565C0;
         color: white;
         border-top-left-radius: 12px;
         border-top-right-radius: 12px;
      }

      .modal-content {
         border-radius: 12px;
         border: none;
      }

      .close-white {
         filter: invert(1) grayscale(100%) brightness(200%);
      }

      /* Stats Box inside Modal */
      .stat-box {
         background: #F8F9FA;
         border-radius: 10px;
         padding: 20px;
         text-align: center;
         border-left: 4px solid #1565C0;
      }

      .stat-value {
         font-size: 2rem;
         font-weight: 700;
         color: #1565C0;
      }

      .stat-label {
         font-size: 0.85rem;
         color: #666;
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      /* Animation */
      .fade-in {
         animation: fadeIn 0.5s ease-in-out;
      }

      @keyframes fadeIn {
         from {
            opacity: 0;
            transform: translateY(10px);
         }

         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
   </style>
</head>

<body>

   <div class="admin-header">
      <div class="container">
         <h2 class="fw-bold"><i class="fas fa-users-cog me-2"></i> Gestão de Pontos</h2>
         <p class="opacity-75">Administração de Saldos, Planos e Histórico de Transações</p>
      </div>
   </div>

   <div class="container" style="margin-top: -50px;">
      <div class="card p-4">

         <form method="GET" class="mb-4">
            <div class="input-group input-group-lg shadow-sm">
               <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
               <input type="text" name="s" class="form-control border-start-0" placeholder="Pesquisar por nome ou e-mail..." value="<?php echo esc_attr($search); ?>">
               <button class="btn btn-primary px-4" type="submit">Filtrar</button>
            </div>
         </form>

         <div class="table-responsive">
            <table class="table table-hover table-custom align-middle">
               <thead>
                  <tr>
                     <th>Cliente</th>
                     <th>Plano Atual</th>
                     <th class="text-center">Pontos Disponíveis</th>
                     <th class="text-end">Ações</th>
                  </tr>
               </thead>
               <tbody>
                  <?php if ($users_list): foreach ($users_list as $user):
                        $initials = strtoupper(substr($user->display_name, 0, 1));
                        $points = $user->available_points ? number_format($user->available_points, 0, ',', '.') : '0';

                        // Mock de Plano (já que não há tabela de planos no esquema fornecido)
                        $plano = 'Standard';
                        if ($points > 1000) $plano = 'Gold';
                        if ($points > 5000) $plano = 'Platinum';
                  ?>
                        <tr>
                           <td>
                              <div class="d-flex align-items-center">
                                 <div class="user-avatar shadow-sm"><?php echo $initials; ?></div>
                                 <div>
                                    <div class="fw-bold text-dark"><?php echo esc_html($user->display_name); ?></div>
                                    <small class="text-muted"><?php echo esc_html($user->user_email); ?></small>
                                 </div>
                              </div>
                           </td>
                           <td><span class="badge badge-plan rounded-pill"><?php echo $plano; ?></span></td>
                           <td class="text-center">
                              <span class="badge-points fw-bold"><?php echo $points; ?> pts</span>
                           </td>
                           <td class="text-end">
                              <button class="btn btn-outline-primary btn-sm rounded-pill px-3 btn-view-client"
                                 data-userid="<?php echo $user->ID; ?>"
                                 data-username="<?php echo esc_attr($user->display_name); ?>">
                                 <i class="fas fa-eye me-1"></i> Detalhes
                              </button>
                           </td>
                        </tr>
                     <?php endforeach;
                  else: ?>
                     <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Nenhum usuário encontrado.</td>
                     </tr>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
         <div class="modal-content shadow-lg">
            <div class="modal-header">
               <h5 class="modal-title fw-bold"><i class="fas fa-user-circle me-2"></i> <span id="modalUserName">Carregando...</span></h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">

               <div id="modalLoading" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                  <p class="mt-2 text-muted">Buscando dados na Event Store...</p>
               </div>

               <div id="modalContent" class="d-none fade-in">

                  <div class="row g-3 mb-4">
                     <div class="col-md-6">
                        <div class="stat-box shadow-sm">
                           <div class="stat-value" id="viewBalance">0</div>
                           <div class="stat-label">Saldo Atual</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="stat-box shadow-sm" style="border-left-color: #F57C00;">
                           <div class="stat-value text-warning" style="color: #F57C00 !important;" id="viewExpiring">0</div>
                           <div class="stat-label">Expiram em <span id="viewExpiringDate">-</span></div>
                        </div>
                     </div>
                  </div>

                  <div class="card border-0 shadow-sm">
                     <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                           <h6 class="fw-bold m-0 text-primary"><i class="fas fa-history me-2"></i> Histórico de Transações</h6>
                           <button id="btnLoadHistory" class="btn btn-sm btn-light text-primary fw-bold">
                              <i class="fas fa-chevron-down me-1"></i> Consultar Extrato
                           </button>
                        </div>

                        <div id="historyContainer" class="d-none mt-3">
                           <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                              <table class="table table-sm table-striped" style="font-size: 0.9rem;">
                                 <thead class="table-light sticky-top">
                                    <tr>
                                       <th>Data</th>
                                       <th>Tipo</th>
                                       <th>Obs</th>
                                       <th class="text-end">Valor</th>
                                    </tr>
                                 </thead>
                                 <tbody id="historyTableBody">
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>

               </div>
            </div>
            <div class="modal-footer bg-white">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
               <button type="button" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Ajustar Saldo</button>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const clientModal = new bootstrap.Modal(document.getElementById('clientModal'));
         const modalUserName = document.getElementById('modalUserName');
         const modalLoading = document.getElementById('modalLoading');
         const modalContent = document.getElementById('modalContent');
         const historyContainer = document.getElementById('historyContainer');
         const btnLoadHistory = document.getElementById('btnLoadHistory');

         let currentUserId = 0;

         // Click on "Detalhes"
         document.querySelectorAll('.btn-view-client').forEach(btn => {
            btn.addEventListener('click', function() {
               currentUserId = this.getAttribute('data-userid');
               const userName = this.getAttribute('data-username');

               // Reset UI
               modalUserName.innerText = userName;
               modalLoading.classList.remove('d-none');
               modalContent.classList.add('d-none');
               historyContainer.classList.add('d-none');
               btnLoadHistory.innerHTML = '<i class="fas fa-chevron-down me-1"></i> Consultar Extrato';
               document.getElementById('historyTableBody').innerHTML = '';

               clientModal.show();

               // Fetch Header Data
               fetch(`?ajax_action=get_client_details&user_id=${currentUserId}`)
                  .then(response => response.json())
                  .then(data => {
                     document.getElementById('viewBalance').innerText = new Intl.NumberFormat('pt-BR').format(data.balance);
                     document.getElementById('viewExpiring').innerText = new Intl.NumberFormat('pt-BR').format(data.expiring_amount);
                     document.getElementById('viewExpiringDate').innerText = data.expiring_date;

                     modalLoading.classList.add('d-none');
                     modalContent.classList.remove('d-none');
                  })
                  .catch(err => {
                     console.error(err);
                     modalUserName.innerText = "Erro ao carregar dados";
                  });
            });
         });

         // Click on "Consultar Extrato" inside Modal
         btnLoadHistory.addEventListener('click', function() {
            if (!historyContainer.classList.contains('d-none')) {
               // Toggle Close
               historyContainer.classList.add('d-none');
               this.innerHTML = '<i class="fas fa-chevron-down me-1"></i> Consultar Extrato';
               return;
            }

            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Carregando...';

            fetch(`?ajax_action=get_transactions&user_id=${currentUserId}`)
               .then(response => response.json())
               .then(data => {
                  const tbody = document.getElementById('historyTableBody');
                  tbody.innerHTML = '';

                  if (data.length === 0) {
                     tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhuma transação encontrada.</td></tr>';
                  } else {
                     data.forEach(txn => {
                        const date = new Date(txn.created_at).toLocaleDateString('pt-BR') + ' ' + new Date(txn.created_at).toLocaleTimeString('pt-BR', {
                           hour: '2-digit',
                           minute: '2-digit'
                        });
                        const colorClass = txn.amount >= 0 ? 'text-success' : 'text-danger';
                        const sign = txn.amount >= 0 ? '+' : '';

                        const row = `
                                    <tr>
                                        <td><small>${date}</small></td>
                                        <td><span class="badge bg-light text-dark border">${txn.type}</span></td>
                                        <td><small class="text-muted text-truncate" style="max-width: 150px; display:block;">${txn.note || '-'}</small></td>
                                        <td class="text-end fw-bold ${colorClass}">${sign}${txn.amount}</td>
                                    </tr>
                                `;
                        tbody.innerHTML += row;
                     });
                  }

                  historyContainer.classList.remove('d-none');
                  this.innerHTML = '<i class="fas fa-chevron-up me-1"></i> Ocultar Extrato';
               });
         });
      });
   </script>
</body>

</html>