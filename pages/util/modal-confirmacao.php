<?php

/**
 * Partial: Modal de Detalhes do Cliente
 */
if (!defined('ABSPATH')) exit;
?>

<div class="modal fade" id="confirmActionModal" tabindex="-1">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

         <div class="modal-header bg-warning">
            <h5 class="modal-title fw-bold">Confirmação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <div class="modal-body">
            <p id="confirmMessage" class="mb-0"></p>
         </div>

         <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="btnConfirmAction">
               Confirmar
            </button>
         </div>

      </div>
   </div>
</div>