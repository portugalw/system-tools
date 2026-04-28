<?php if (!defined('ABSPATH')) exit; ?>

<div class="modal fade" id="runDetailsModal" tabindex="-1">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">

         <div class="modal-header">
            <h5 class="modal-title">Detalhes da Execução</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <div class="modal-body">
            <div id="run-details-content">
               <p>Carregando...</p>
            </div>
         </div>

      </div>
   </div>
</div>

<script>
   document.querySelectorAll('.btn-view-run').forEach(btn => {
      btn.addEventListener('click', function() {

         const runId = this.dataset.runId;

         fetch(ajaxurl + '?action=st_get_run_details&run_id=' + runId)
            .then(res => res.text())
            .then(html => {
               document.getElementById('run-details-content').innerHTML = html;

               const modal = new bootstrap.Modal(document.getElementById('runDetailsModal'));
               modal.show();
            });
      });
   });
</script>