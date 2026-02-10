jQuery(function ($) {

    /* --- Troca de abas --- */
    $("#extratoTabs button").on("click", function () {
        let tab = $(this).data("tab");

        $("#extratoTabs button").removeClass("active");
        $(this).addClass("active");

        $(".tab-pane").hide();
        $("#tab-" + tab).show();

        if (tab === "extrato") {
            $("#filtros-extrato").show();
            carregarExtrato(1);
        } else {
            $("#filtros-extrato").hide();
            carregarExpirar();
        }
    });


    /* --- Carregar extrato via AJAX --- */
    async function carregarExtrato(page = 1) {


      const res = await window.ST.fetchJson('get_transactions_from_logged_user');

      console.log(res);

            if (!res.success) return;

            let html = "";
            res.data.items.forEach(item => {
                html += `
                <tr>
                    <td>${item.data}</td>
                    <td>${item.operacao}</td>
                    <td>${item.parceiro}</td>
                    <td>${item.pontos}</td>
                    <td>${item.obs}</td>
                </tr>`;
            });

            $("#tab-extrato tbody").html(html);
            $("#paginacao-extrato").html(res.data.paginacao);
    }

    carregarExtrato(1);


    /* --- Filtro de operações --- */
    $("#filtro-operacao").on("change", function () {
        carregarExtrato(1);
    });


    /* --- Carregar pontos a expirar --- */
    function carregarExpirar() {

        $.post(ST_EXTRATO.ajaxurl, {
            action: "get_expirar",
            _ajax_nonce: ST_EXTRATO.nonce
        }, function (res) {
            if (!res.success) return;

            let html = "";
            res.data.items.forEach(item => {
                html += `
                <tr>
                    <td>${item.data_expira}</td>
                    <td>${item.quantidade}</td>
                </tr>`;
            });

            $("#tab-expirar tbody").html(html);
        });
    }

});
