<?php if (!defined('ABSPATH')) exit; ?>

<table class="table table-hover align-middle">
    <thead>
    <tr>
        <th>ID</th>
        <th>Status</th>
        <th>Total</th>
        <th>Sucesso</th>
        <th>Erro</th>
        <th>Início</th>
        <th>Fim</th>
        <th>Ação</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($runs): foreach ($runs as $run): ?>
            <tr>
                <td><?= $run->run_id ?></td>

                <td>
                <span class="badge bg-<?= $run->status === 'finished' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning') ?>"> <?= esc_html($run->status) ?>
                </span>
                </td>

                <td><?= $run->total_records ?></td>
                <td><?= $run->success_count ?></td>
                <td><?= $run->error_count ?></td>

                <td><?= $run->started_at ?></td>
                <td><?= $run->finished_at ?: '-' ?></td>

                <td>
                <button class="btn btn-outline-primary btn-sm btn-view-run"
                    data-run-id="<?= $run->run_id ?>">
                    Detalhes
                </button>
                </td>
            </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td colspan="8" class="text-center text-muted">
                Nenhuma execução encontrada
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<!-- 🔹 paginação -->
<div class="mt-3">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline-secondary' ?> pagination-link"
        href="#" data-page="<?= $i ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
