document.addEventListener('DOMContentLoaded', () => {

    const modalEl = document.getElementById('lista-detalhes-modal');
    const modal = new bootstrap.Modal(modalEl);
    
    const confirmModalEl = document.getElementById('confirmActionModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    const confirmMessage = document.getElementById('confirmMessage');
    const btnConfirmAction = document.getElementById('btnConfirmAction');

    let pendingAction = null;

    let currentUserId = null;

    const el = id => document.getElementById(id);


     async function loadTransactions() {
                clearHistory();
                const result = await window.ST.fetchJson('get_transactions', { user_id: currentUserId });
                renderHistory(result.data);
            }


     async function loadCustomerDetails() {
                const result = await window.ST.fetchJson('get_client_details', { user_id: currentUserId });
                renderHeader(result.data);
            }

    document.querySelectorAll('.btn-view-client').forEach(btn => {
        btn.addEventListener('click', async () => {
            currentUserId = btn.dataset.userId;
      
            el('modalUserName').innerText = btn.dataset.userName + ' ID:' + currentUserId;
            el('id-usuario').value = currentUserId;

            toggleLoading(true);
            modal.show();
            el('justificativa-add').focus();

            try {
                await loadCustomerDetails();
            } catch (e) {
                el('modalUserName').innerText = 'Erro ao carregar';
            }

            try {
               await loadTransactions();

            } catch (e) {
                el('modalUserName').innerText = 'Erro ao carregar';
            }

           

           
        });
    });

 
    function toggleLoading(show) {
        el('modalLoading').classList.toggle('d-none', !show);
        el('modalContent').classList.toggle('d-none', show);
    }

    function renderHeader(data) {

        el('viewBalance').innerText = data.balance;
        el('viewExpiring').innerText = data.expiring_amount;
        el('viewExpiringDate').innerText = data.expiring_date;
        toggleLoading(false);
    }

   function clearHistory() {
      const tbody = el('historyTableBody');
        tbody.innerHTML = '<tr><td colspan="4">Carregando...</td></tr>';
   }

    function renderHistory(rows) {
        const tbody = el('historyTableBody');
        tbody.innerHTML = '';

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4">Sem transações</td></tr>';
            return;
        }

        rows.forEach(txn => {
            tbody.innerHTML += `
                <tr>
                    <td>${formatDate(txn.created_at)}</td>
                    <td>${txn.type}</td>
                    <td>${txn.related_resource}</td>
                    <td>${txn.note || '-'}</td>
                    <td class="text-end ${txn.amount >= 0 ? 'text-success' : 'text-danger'}">
                       <strong> ${txn.amount} </strong>
                    </td>
                </tr>`;
        });
    }

    function toggleHistory(show) {
        el('historyContainer').classList.toggle('d-none', !show);
    }

    function formatDate(date) {
        return new Date(date).toLocaleString('pt-BR');
    }

    /*ITENS DO MODAL*/ 

    
  function validateFields(amount, note) {
    if (!amount || amount <= 0) {
      alert('Informe uma quantidade válida de pontos.');
      return false;
    }
    if (!note || note.trim().length < 5) {
      alert('Informe uma justificativa com pelo menos 5 caracteres.');
      return false;
    }
    return true;
  }

  function openConfirmModal(message, callback) {
    confirmMessage.textContent = message;
    pendingAction = callback;
    confirmModal.show();
  }

  btnConfirmAction.addEventListener('click', () => {
    if (typeof pendingAction === 'function') {
      pendingAction();
    }
    confirmModal.hide();
  });

  // =============================
  // ADICIONAR PONTOS
  // =============================
  el('btnAddPontos').addEventListener('click', () => {

    const amount = el('quantidade-add').value;
    const note = el('justificativa-add').value;
    const userId = el('id-usuario').value;

    if (!validateFields(amount, note)) return;

    openConfirmModal(
      `Deseja adicionar ${amount} pontos para este usuário?`,
      () => submitPoints(userId, amount, note, 'add')
    );
  });

  // =============================
  // SUBMISSÃO AJAX
  // =============================
  function submitPoints(userId, amount, note, operation) {


    const formData = new FormData();
    formData.append('action', 'st_update_points');
    formData.append('user_id', userId);
    formData.append('amount', amount);
    formData.append('note', note);
    formData.append('operation', operation);
    formData.append('_wpnonce', ST_AJAX.nonce);


    fetch(ajaxurl, {
      method: 'POST',
      body: formData
    })
    .then(res => {
      console.log('HTTP status:', res.status);
      return res.text(); // ⬅️ IMPORTANTE para debug
    })
    .then(text => {
      console.log('Resposta bruta:', text); // 👀 veja isso no console
      const res = JSON.parse(text); // força o erro aparecer aqui
  
      if (!res.success) {
        alert(res.data?.message || 'Erro ao atualizar pontos.');
        return;
      }
      
      const mainModal = bootstrap.Modal.getInstance(
        document.getElementById('lista-detalhes-modal')
      );
      //mainModal.hide();
      //window.location.reload(); // TODO atualizar vi ajaxa e usar told alerts
      loadTransactions();
      loadCustomerDetails();
      
    })
    .catch(error => {
      console.error(error);
      alert('Erro de comunicação com o servidor.');
    });
  }
});
