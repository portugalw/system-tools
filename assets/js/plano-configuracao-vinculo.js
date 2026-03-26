jQuery(document).ready(function ($) {

    const el = id => document.getElementById(id);

    // 🔥 NÃO inicializa aqui ainda
    let modal = null;

    // 1. Abrir Modal e Preencher Dados
    $('.btn-edit-plan').on('click', function () {
        const btn = $(this);

        const modalEl = el('modalPlanConfig');

        // ✅ garante que existe antes de usar
        if (!modalEl) {
            console.error('Modal #modalPlanConfig não encontrado');
            return;
        }

        // ✅ inicializa UMA vez só
        if (!modal) {
            modal = new bootstrap.Modal(modalEl, {
                backdrop: true
            });
        }

        // Pega dados
        const id = btn.data('id');
        const name = btn.data('name');
        const points = btn.data('points');
        const days = btn.data('days');
        const active = btn.data('active');

        // Preenche
        $('#plan_id').val(id);
        $('#modalPlanName').text(name);
        $('#conf_points').val(points);
        $('#conf_days').val(days);

        // ⚠️ evita registrar evento múltiplas vezes
        $('#conf_points').off('input').on('input', mascaraInteiros);
        $('#conf_days').off('input').on('input', mascaraInteiros);

        $('#conf_active').prop('checked', active == 1);

        modal.show();
    });

    // 2. Submit
    $('#formPlanConfig').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const spinner = btnSubmit.find('.spinner-border');

        btnSubmit.prop('disabled', true);
        spinner.removeClass('d-none');

        const formData = new FormData(this);

        formData.append('is_active', $('#conf_active').is(':checked') ? 1 : 0);
        formData.append('action', 'st_save_plan_config');
        formData.append('_wpnonce', ST_AJAX.nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            console.log('Resposta bruta:', text);

            let res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                console.error('JSON inválido:', text);
                throw e;
            }

            btnSubmit.prop('disabled', false);
            spinner.addClass('d-none');

            if (!res.success) {
                alert(res.data?.message || 'Erro ao atualizar.');
                return;
            }

            atualizarLinhaPlano($('#plan_id').val());

            if (modal) modal.hide();
        })
        .catch(error => {
            console.error(error);
            alert('Erro de comunicação com o servidor.');
            btnSubmit.prop('disabled', false);
            spinner.addClass('d-none');
        });
    });

    async function buscarDadosDetalhadosDoPlano(armPlanId) {
        return await window.ST.fetchJson('st_get_plan_config_details', {
            armPlanId: armPlanId
        });
    }

    async function atualizarLinhaPlano(armPlanId) {
        const result = await buscarDadosDetalhadosDoPlano(armPlanId);
        const data = result.data;

        const linha = $(`tr[data-plan-id="${data.plan_id}"]`);

        linha.find('.col-points').html(
            `<span class="badge bg-info text-dark">${data.points} pts</span>`
        );

        linha.find('.col-days').text(`${data.days_expire} dias`);

        linha.find('.col-status').html(
            data.is_active == 1
                ? `<span class="badge bg-success">Ativado</span>`
                : `<span class="badge bg-danger">Desativado</span>`
        );

        linha.find('.col-updated').text(data.updated_at_formatado);
    }
});