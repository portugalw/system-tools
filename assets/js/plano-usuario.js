document.addEventListener('DOMContentLoaded', () => {

    const modalEl = document.getElementById('lista-detalhes-modal');
    const modal = new bootstrap.Modal(modalEl);

    let currentUserId = null;

    const el = id => document.getElementById(id);

    document.querySelectorAll('.btn-view-client').forEach(btn => {
        btn.addEventListener('click', async () => {
            currentUserId = btn.dataset.userId;
            el('modalUserName').innerText = btn.dataset.userName;

            toggleLoading(true);
            modal.show();

            try {
                const result = await fetchJson('get_client_details', { user_id: currentUserId });
                renderHeader(result.data);
            } catch (e) {
                el('modalUserName').innerText = 'Erro ao carregar';
            }

            try {
               clearHistory();
               const result = await fetchJson('get_transactions', { user_id: currentUserId });
               renderHistory(result.data);

            } catch (e) {
                el('modalUserName').innerText = 'Erro ao carregar';
            }
        });
    });
   
    function fetchJson(action, params) {
        const query = new URLSearchParams({
            action,
            ...params,
            _wpnonce: ST_AJAX.nonce
        });

        return fetch(`${ST_AJAX.url}?${query}`).then(r => r.json());
    }

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
                    <td>${txn.note || '-'}</td>
                    <td class="text-end ${txn.amount >= 0 ? 'text-success' : 'text-danger'}">
                        ${txn.amount}
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
});
