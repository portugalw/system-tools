<div class="st-extrato">

   <h2 class="st-title">Extrato</h2>

   <!-- CARDS SUPERIORES -->
   <div class="st-cards">

      <div class="st-card">
         <div class="st-card-label">Total de pontos</div>
         <div class="st-card-value" id="st-total-pontos">10</div>
         <div class="st-card-update">
            <span class="dot"></span>
            <span id="st-data-atualizacao"></span>
         </div>
      </div>

      <div class="st-card">
         <div class="st-card-label">Pontos a expirar</div>
         <div class="st-card-value" id="st-expirar-total">45</div>
         <div class="st-card-sub" id="st-expirar-data"></div>
         <button class="st-btn-outline st-btn-expirar">Ver detalhes</button>
      </div>

      <div class="st-card">
         <div class="st-card-label">Pontos Utilizados</div>
         <div class="st-card-value" id="st-receber-total">0</div>
         <div class="st-card-sub" id="st-receber-data"></div>
         <button class="st-btn-outline">Ver detalhes</button>
      </div>

      <div class="st-card-right">
         <button class="st-btn">Transferir pontos</button>
         <button class="st-btn">Trocar pontos</button>
         <button class="st-btn">Comprar pontos</button>
         <button class="st-btn">Assine o clube</button>
      </div>

   </div>

   <!-- ABAS -->
   <div class="st-tabs">
      <button class="st-tab active" data-tab="mov">Movimentações</button>
      <button class="st-tab" data-tab="exp">Pontos a expirar</button>
   </div>

   <!-- FILTRO (SÓ NA ABA MOVIMENTAÇÕES) -->
   <div id="st-filtros-mov" class="st-filters">
      <select id="st-filtro-tipo">
         <option value="todos">Tudo</option>
         <option value="recebidos">Pontos Recebidos</option>
         <option value="gastos">Pontos Gastos</option>
         <option value="expirados">Pontos Expirados</option>
      </select>
   </div>

   <!-- TABELA: MOVIMENTAÇÕES -->
   <div id="st-tab-mov" class="st-table-wrapper">

      <table class="st-table">
         <thead>
            <tr>
               <th>Data</th>
               <th>Operação</th>
               <th>Parceiros</th>
               <th>Pontos</th>
               <th>Observações</th>
            </tr>
         </thead>
         <tbody>

            <tr>
               <td>13/01/2026</td>
               <td>Acúmulo</td>
               <td>Membership Rewards</td>
               <td>
                  <span class="st-pontos positivo">+45</span>
                  <div class="st-mini-btn">Turbinhar meus pontos</div>
                  <div class="st-mini-link">Como funciona</div>
               </td>
               <td>-</td>
            </tr>

            <tr>
               <td>12/12/2025</td>
               <td>Acúmulo</td>
               <td>Membership Rewards</td>
               <td>
                  <span class="st-pontos positivo">+410</span>
                  <div class="st-mini-btn">Turbinar meus pontos</div>
                  <div class="st-mini-link">Como funciona</div>
               </td>
               <td>-</td>
            </tr>

            <tr>
               <td>08/12/2025</td>
               <td>Acúmulo</td>
               <td>AMAZON</td>
               <td>
                  <span class="st-pontos positivo">+45</span>
                  <div class="st-mini-btn">Turbinar meus pontos</div>
                  <div class="st-mini-link">Como funciona</div>
               </td>
               <td>Expira em 08/12/2027</td>
            </tr>

         </tbody>
      </table>
      <div id="st-paginacao-mov"></div>
   </div>

   <!-- TABELA: PONTOS A EXPIRAR -->
   <div id="st-tab-exp" class="st-table-wrapper" style="display:none;">
      <table class="st-table">
         <thead>
            <tr>
               <th>Data de expiração</th>
               <th>Quantidade</th>
            </tr>
         </thead>
         <tbody id="st-tbody-exp">
            <tr>
               <td colspan="2">Carregando…</td>
            </tr>
         </tbody>
      </table>
   </div>

</div>