<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Gestión de Cortes</h4>
            <small class="text-muted">Control de consumo de tela y tallas</small>
        </div>
        <a href="<?= base_url('modulo3/cortes/nuevo') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Corte
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Corte</th>
                            <th>Estilo</th>
                            <th>Prenda</th>
                            <th>Cliente</th>
                            <th>Fecha Entrada</th>
                            <th>Total Prendas</th>
                            <th>Tela Usada (m)</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cortes)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No hay cortes registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cortes as $corte): ?>
                                <tr>
                                    <td class="fw-bold"><?= esc($corte['numero_corte']) ?></td>
                                    <td><?= esc($corte['estilo']) ?></td>
                                    <td><?= esc($corte['prenda']) ?></td>
                                    <td><?= esc($corte['cliente']) ?></td>
                                    <td><?= $corte['fecha_entrada'] ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $corte['total_prendas'] ?></span></td>
                                    <td><?= $corte['total_tela_usada'] ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('modulo3/cortes/editar/' . $corte['id']) ?>"
                                            class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>