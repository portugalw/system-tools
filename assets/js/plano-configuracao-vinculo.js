jQuery(document).ready(function($) {
    
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
        
        // Checkbox logic
        $('#conf_active').prop('checked', active == 1);

        // Abre o Modal (Bootstrap 5)
        const modalEl = document.getElementById('modalPlanConfig');
        const modal = new bootstrap.Modal(modalEl);
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
        formData.append('action', 'st_save_plan_config'); // Ação do PHP
        formData.append('nonce', ST_AJAX.nonce); // Certifique-se de ter o nonce global

        // Fetch API ou $.ajax
        $.ajax({
            url: ajaxurl, // Variável global do WP Admin
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Configuração salva com sucesso!');
                    window.location.reload(); // Recarrega para atualizar a tabela
                } else {
                    alert('Erro: ' + (res.data || 'Erro desconhecido'));
                }
            },
            error: function() {
                alert('Erro de comunicação com o servidor.');
            },
            complete: function() {
                btnSubmit.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
});