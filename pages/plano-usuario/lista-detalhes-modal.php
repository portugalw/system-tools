<?php

/**
 * Partial: Modal de Detalhes do Cliente
 */
if (!defined('ABSPATH')) exit;
?>

<div class="modal fade" id="lista-detalhes-modal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content shadow-xl">

         <input type="hidden" id="id-usuario">
         <!-- HEADER -->
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fw-bold">
               <i class="fas fa-user-circle me-2"></i>
               <span>Detalhes do Usuário - </span>
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
               <div class="card g-3 mb-4 border-0 shadow-sm">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-6">
                           <h5 class="fw-bold m-0">
                              <i cl5ass="fas fa-history me-2"></i>
                              Plano Atual: <span id="nomePlanoAtual">PLANO TALL</span>
                           </h5>
                           <div class="mt-2">

                              <h6>Membro desde: <span class="badge bg-secondary">DATA TAL</span></h6>
                              <h6>Data do plano atual: <span class="badge bg-secondary">DATA TAL</span> Expira em: <span class="badge bg-secondary">DATA TAL</span></h6>
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="stat-box">
                              <div class="stat-value" id="viewBalance">0</div>
                              <div class="stat-label">Saldo Atual</div>
                           </div>
                        </div>

                        <div class="col-md-3">
                           <div class="stat-box border-warning">
                              <div class="stat-value text-warning" id="viewExpiring">0</div>
                              <div class="stat-label">
                                 Expiram em <span id="viewExpiringDate">-</span>
                              </div>
                           </div>
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
                                    <th style="width: 190px">Data</th>
                                    <th>Tipo</th>
                                    <th>Origem</th>
                                    <th style="width: 370px">Obs</th>
                                    <th class="text-end">Valor</th>
                                 </tr>
                              </thead>
                              <tbody id="historyTableBody"></tbody>
                           </table>
                        </div>
                     </div>

                  </div>
               </div>



               <!-- HISTÓRICO -->
               <div class="card border-0 shadow-sm">
                  <div class="card-body">

                     <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                           <h2 class="accordion-header" id="headingOne">
                              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                 Adicionar Pontos
                              </button>
                           </h2>
                           <div id="collapseOne" class="accordion-collapse collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                              <div class="accordion-body">

                                 <div class="mb-3">
                                    <div class="row">
                                       <div class="col">
                                          <label for="quantidade-add" class="form-label">Quantidade de Pontos:</label>
                                          <input type="number" class="form-control " id="quantidade-add" value="10">
                                          <div class="mt-1">
                                             <label for="dias-expirar" class="form-label">Quantidade de Dias para expirar:</label>
                                             <input type="number" class="form-control " id="dias-expirar" value="30">
                                          </div>
                                       </div>
                                       <div class="col">
                                          <label for="justificativa-add" class="form-label">Justificativa:</label>
                                          <textarea class="form-control" id="justificativa-add" rows="2"></textarea>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="mb-3">

                                 </div>
                                 <button type="submit" id="btnAddPontos" class="btn btn-primary">Adicionar Pontos</button>

                              </div>
                           </div>
                        </div>
                        <div class="accordion-item">
                           <h2 class="accordion-header" id="headingTwo">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                 Expirar Pontos
                              </button>
                           </h2>
                           <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                              <div class="accordion-body">
                                 <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
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