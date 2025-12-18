<?php

/**
 * Partial: Modal de Detalhes do Cliente
 */
if (!defined('ABSPATH')) exit;
?>

<div class="modal fade" id="lista-detalhes-modal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content shadow-xl">

         <!-- HEADER -->
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fw-bold">
               <i class="fas fa-user-circle me-2"></i>
               <span id="modalUserName">Carregando...</span>
            </h5>
            <button type="button"
               class="btn-close btn-close-white"
               data-bs-dismiss="modal"
               aria-label="Fechar"></button>
         </div>

         <!-- BODY -->
         <div class="modal-body bg-light p-4">

            <!-- LOADING -->
            <div id="modalLoading" class="text-center py-5">
               <div class="spinner-border text-primary"></div>
               <p class="mt-2 text-muted">Carregando dados…</p>
            </div>

            <!-- CONTENT -->
            <div id="modalContent" class="d-none">

               <!-- STATS -->
               <div class="row g-3 mb-4">
                  <div class="col-md-6">
                     <div class="stat-box">
                        <div class="stat-value" id="viewBalance">0</div>
                        <div class="stat-label">Saldo Atual</div>
                     </div>
                  </div>

                  <div class="col-md-6">
                     <div class="stat-box border-warning">
                        <div class="stat-value text-warning" id="viewExpiring">0</div>
                        <div class="stat-label">
                           Expiram em <span id="viewExpiringDate">-</span>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- HISTÓRICO -->
               <div class="card border-0 shadow-sm">
                  <div class="card-body">

                     <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0">
                           <i class="fas fa-history me-2"></i>
                           Histórico de Transações
                        </h6>
                     </div>

                     <div id="historyContainer">
                        <div class="table-responsive" style="max-height: 300px">
                           <table class="table table-sm table-striped">
                              <thead>
                                 <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Obs</th>
                                    <th class="text-end">Valor</th>
                                 </tr>
                              </thead>
                              <tbody id="historyTableBody"></tbody>
                           </table>
                        </div>
                     </div>

                  </div>
               </div>

            </div>
         </div>

         <!-- FOOTER -->
         <div class="modal-footer bg-white">
            <button type="button"
               class="btn btn-secondary"
               data-bs-dismiss="modal">
               Fechar
            </button>

            <button type="button" class="btn btn-primary">
               <i class="fas fa-pen me-1"></i>
               Ajustar Saldo
            </button>
         </div>

      </div>
   </div>
</div>