

jQuery(function ($) {


    /* --- Troca de abas --- */
    $(".btn-tab").on("click", function () {
        let tab = $(this).data("tab");

        $(".btn-tab").removeClass("active");
        $(this).addClass("active");

        $(".tab").hide();
        $("#st-tab-" + tab).show();

      /*  if (tab === "mov") {
            $("#st-filtros-mov").show();
        
        } else {
            $("#st-filtros-mov").hide();          
        }*/
    });

     async function carregarDetalhesDoUsuario() {
                const result = await window.ST.fetchJson('get_client_details_from_logged_user');
                renderHeader(result.data);
     }

     async function carregarExtratoPontosAExpirar() {
               const result = await window.ST.fetchJson('get_active_batch_points_with_expiration_from_logged_user');
               renderExpirationExtract(result.data);
     }

    /* --- Carregar extrato via AJAX --- */
    async function carregarExtrato(page = 1) {

        const res = await window.ST.fetchJson('get_transactions_from_logged_user');

        if (!res.success) return;

        let rows = res.data;

        renderExtract(rows);

        $("#paginacao-extrato").html(res.data.paginacao);
    }

     function renderExtract(rows) {
        const tbody = el('extractTableBody');
        tbody.innerHTML = '';

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4">Sem transações</td></tr>';
            return;
        }

        rows.forEach(item => {
            tbody.innerHTML += `
                 <tr>
                    <td>${formatDate(item.created_at)}</td>
                    <td>${I18n.t(item.type)}</td>
                    <td>${I18n.t(item.related_resource)}</td>
                    <td>
                        <span class="st-pontos ${item.type == 'credit' ? 'text-success' : 'text-danger'}">${item.amount}</span>
                    
                    </td>
                    <td>${item.note || '-'}</td>
                </tr>`;
        });
    }

     function renderExpirationExtract(rows) {
        const tbody = el('expirationTableBody');
        tbody.innerHTML = '';

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4">Sem transações</td></tr>';
            return;
        }

        rows.forEach(item => {
            tbody.innerHTML += `
                 <tr>
                    <td>${formatDate(item.created_at)}</td>
                    <td><span class="st-pontos ">${item.points_total}</span></td>
                    <td>${formatDate(item.expires_at)}</td>
                    <td><span class="st-pontos ">${item.points_remaining}</span></td>
                </tr>`;
        });
    }

    function renderHeader(data) {
        console.log(data);
        el('viewBalance').innerText = data.balance;
        el('viewExpiring').innerText = data.expiring_amount;
        el('viewExpiringDate').innerText = data.expiring_date;
        el('viewNextExpiringPoints').innerText = data.points_expiring_first;
        
        toggleLoading(false);
    }

    carregarDetalhesDoUsuario();
    carregarExtrato(1);
    carregarExtratoPontosAExpirar();



    /* --- Filtro de operações --- */
    $("#filtro-operacao").on("change", function () {
        carregarExtrato(1);
    });

});
