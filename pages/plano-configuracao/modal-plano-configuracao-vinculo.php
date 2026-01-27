<?php if (!defined('ABSPATH')) exit; ?>

<div class="modal fade" id="modalPlanConfig" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg">
         <form id="formPlanConfig">

            <div class="modal-header bg-primary text-white">
               <h5 class="modal-title">
                  <i class="fas fa-cog me-2"></i> Configurar: <span id="modalPlanName">...</span>
               </h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
               <input type="hidden" id="plan_id" name="plan_id">

               <div class="mb-3">
                  <label for="conf_points" class="form-label fw-bold">Pontos a conceder</label>
                  <div class="input-group">
                     <span class="input-group-text"><i class="fas fa-star"></i></span>
                     <input type="number" class="form-control" id="conf_points" name="points" min="0" required>
                  </div>
                  <div class="form-text">Quantidade de pontos ao assinar este plano.</div>
               </div>

               <div class="mb-3">
                  <label for="conf_days" class="form-label fw-bold">Validade (Dias)</label>
                  <div class="input-group">
                     <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                     <input type="number" class="form-control" id="conf_days" name="days_expire" min="1" required>
                  </div>
                  <div class="form-text">Em quantos dias esses pontos expiram.</div>
               </div>

               <div class="mb-3 pt-2 border-top">
                  <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="conf_active" name="is_active" value="1">
                     <label class="form-check-label fw-bold" for="conf_active">Configuração Ativa?</label>
                  </div>
                  <div class="form-text">Se desativado, o plugin ignorará a regra de pontos para este plano.</div>
               </div>
            </div>

            <div class="modal-footer bg-light">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
               <button type="submit" class="btn btn-primary">
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  Salvar Alterações
               </button>
            </div>
         </form>
      </div>
   </div>
</div>