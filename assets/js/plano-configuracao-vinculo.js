jQuery(document).ready(function($) {
    

       const el = id => document.getElementById(id);

        // Abre o Modal (Bootstrap 5)
        const modalEl = document.getElementById('modalPlanConfig');
        const modal = new bootstrap.Modal(modalEl);

    // 1. Abrir Modal e Preencher Dados
    $('.btn-edit-plan').on('click', function() {
        const btn = $(this);
        
        // Pega dados dos data-attributes do botão
        const id = btn.data('id');
        const name = btn.data('name');
        const points = btn.data('points');
        const days = btn.data('days');
        const active = btn.data('active');

        // Preenche o Modal
        $('#plan_id').val(id);
        $('#modalPlanName').text(name);
        $('#conf_points').val(points);
        $('#conf_days').val(days);

        //actions inputs
        $('#conf_points').on('input', mascaraInteiros);
        $('#conf_days').on('input', mascaraInteiros);
        
        // Checkbox logic
        $('#conf_active').prop('checked', active == 1);

       
        modal.show();
    });

    // 2. Salvar Dados (Submit)
    $('#formPlanConfig').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const spinner = btnSubmit.find('.spinner-border');

        // UI Loading
        btnSubmit.prop('disabled', true);
        spinner.removeClass('d-none');

        // Prepara dados
        const formData = new FormData(this);
        
        
        formData.append('is_active', $('#conf_active').is(':checked') ? 1 : 0);
        formData.append('action', 'st_save_plan_config'); // Ação do PHP
        formData.append('_wpnonce', ST_AJAX.nonce);

        console.log(el('conf_active').value);

        // Fetch API ou $.ajax


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
        
                btnSubmit.prop('disabled', false);
                spinner.addClass('d-none');
                if (!res.success) {
                    alert(res.data?.message || 'Erro ao atualizar pontos.');
                    return;
                }
                
                atualizarLinhaPlano($('#plan_id').val());
                modal.hide();
                // 3) Mostrar feedback (opcional)
               // toastr.success("Configuração atualizada com sucesso!");

            })
            .catch(error => {
            console.error(error);
            alert('Erro de comunicação com o servidor.');
            });
      
    });

    async function buscarDadosDetalhadosDoPlano(armPlanId){
         

      return await window.ST.fetchJson('st_get_plan_config_details', { armPlanId: armPlanId });

       /* fetch(ajaxurl, {
            method: 'POST',
            body: {armPlanId}
            })
            .then(res => {
                console.log('HTTP status:', res.status);
                return res.text(); // ⬅️ IMPORTANTE para debug
            })
            .then(text => {
                console.log('Resposta bruta:', text); // 👀 veja isso no console
                const res = JSON.parse(text); // força o erro aparecer aqui
            })
            .catch(error => {
                console.error(error);
                alert('Erro ao buscar dados do plano ' + armPlanId);
            });*/
    
    }


   async  function atualizarLinhaPlano(armPlanId) {

            const result = await buscarDadosDetalhadosDoPlano(armPlanId);
            const data = result.data;

            console.log(data);

            // Seleciona a TR pela ID do plano
            const linha = $(`tr[data-plan-id="${data.plan_id}"]`);

            console.log(linha);

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