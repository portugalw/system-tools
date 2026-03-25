<div class="st-extrato">

   <h2 class="st-title">Extrato</h2>

   <!-- CARDS SUPERIORES -->
   <div class="st-cards">

      <div class="st-card">
         <div class="st-card-label">Total de pontos</div>
         <div class="st-card-value" id="viewBalance">-</div>

      </div>

      <div class="st-card">
         <div class="st-card-label">Pontos a expirar em 31 dias</div>
         <div class="st-card-value" id="viewExpiring">-</div>
         <div class="st-card-sub">
            (<strong id="viewNextExpiringPoints"> 0</strong> pts em <strong id="viewExpiringDate">0</strong> )
         </div>

      </div>




   </div>

   <!-- ABAS -->
   <ul class="nav nav-tabs">
      <li class="nav-item">
         <a class="nav-link active btn-tab" data-tab="mov" id='btn-tab-mov'>Movimentações</a>
      </li>
      <li class="nav-item">
         <a class="nav-link btn-tab" data-tab="exp" id='btn-tab-exp'>Pontos a Expirar</a>
      </li>
   </ul>

   <!-- FILTRO (SÓ NA ABA MOVIMENTAÇÕES) 
   <div id="st-filtros-mov" class="st-filters">
      <select id="st-filtro-tipo">
         <option value="todos">Tudo</option>
         <option value="recebidos">Pontos Recebidos</option>
         <option value="gastos">Pontos Gastos</option>
         <option value="expirados">Pontos Expirados</option>
      </select>
   </div>-->

   <!-- TABELA: MOVIMENTAÇÕES -->
   <div id="st-tab-mov" class="st-table-wrapper tab-active tab">

      <table class="st-table" id="table-movimentacoes">
         <thead>
            <tr>
               <th>Data</th>
               <th>Operação</th>
               <th>Origem</th>
               <th>Pontos</th>
               <th>Observações</th>
            </tr>
         </thead>
         <tbody id="extractTableBody">
            <tr>
               <td colspan="2">Carregando…</td>
            </tr>
         </tbody>
      </table>
      <div id="st-paginacao-mov"></div>
   </div>

   <!-- TABELA: PONTOS A EXPIRAR -->
   <div id="st-tab-exp" class="st-table-wrapper tab" style="display:none;">
      <table class="st-table">
         <thead>
            <tr>
               <th>Data de Crédito</th>
               <th>Quantidade</th>
               <th>Data de expiração</th>
               <th>Quantidade a expirar</th>
            </tr>
         </thead>
         <tbody id="expirationTableBody">
            <tr>
               <td colspan="2">Carregando…</td>
            </tr>
         </tbody>
      </table>
   </div>

</div>