<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inventario de Maquinaria</h1>
    <span class="badge bg-secondary">Mantenimiento</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Registro de máquina</strong></div>
    <div class="card-body">
        <!-- Opción A: rutas dentro de modulo3 -->
        <form class="row g-3" method="post" action="<?= base_url('modulo3/maquinaria/guardar') ?>">
            <?= csrf_field() ?>

            <div class="col-md-4">
                <label for="codigo" class="form-label">Código</label>
                <input id="codigo" name="codigo" class="form-control" required
                       value="<?= esc(old('codigo')) ?>" placeholder="MC-0007">
            </div>

            <div class="col-md-4">
                <label for="modelo" class="form-label">Modelo</label>
                <input id="modelo" name="modelo" class="form-control" required
                       value="<?= esc(old('modelo')) ?>" placeholder="Juki DDL-8700">
            </div>

            <div class="col-md-4">
                <label for="fabricante" class="form-label">Fabricante</label>
                <input id="fabricante" name="fabricante" class="form-control"
                       value="<?= esc(old('fabricante')) ?>" placeholder="Juki / Brother / Siruba ...">
            </div>

            <div class="col-md-4">
                <label for="serie" class="form-label">Serie</label>
                <input id="serie" name="serie" class="form-control"
                       value="<?= esc(old('serie')) ?>" placeholder="SER12345">
            </div>

            <div class="col-md-4">
                <label for="fechaCompra" class="form-label">Fecha de compra</label>
                <input id="fechaCompra" type="date" name="fechaCompra" class="form-control"
                       value="<?= esc(old('fechaCompra')) ?>">
            </div>

            <div class="col-md-4">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input id="ubicacion" name="ubicacion" class="form-control"
                       value="<?= esc(old('ubicacion')) ?>" placeholder="Línea 2">
            </div>

            <div class="col-md-4">
                <label for="activa" class="form-label">Estado</label>
                <select id="activa" name="activa" class="form-select">
                    <?php $opt = old('activa') ?: 'Operativa'; ?>
                    <option value="Operativa"     <?= $opt==='Operativa'?'selected':'' ?>>Operativa</option>
                    <option value="En reparación" <?= $opt==='En reparación'?'selected':'' ?>>En reparación</option>
                </select>
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><strong>Listado</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-primary">
            <tr>
                <th>Código</th>
                <th>Modelo</th>
                <th>Fabricante</th>
                <th>Serie</th>
                <th>Compra</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($maq) && is_array($maq)): ?>
                <?php foreach ($maq as $m): ?>
                    <?php
                    // Normaliza fecha segura
                    $compra = '';
                    if (!empty($m['compra'])) {
                        $ts = strtotime($m['compra']);
                        if ($ts) { $compra = date('Y-m-d', $ts); }
                    }
                    $esOperativa = (isset($m['estado']) && $m['estado'] === 'Operativa');
                    $badgeClass  = $esOperativa ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                    <tr>
                        <td><?= esc($m['cod'] ?? '') ?></td>
                        <td><?= esc($m['modelo'] ?? '') ?></td>
                        <td><?= esc($m['fabricante'] ?? '') ?></td>
                        <td><?= esc($m['serie'] ?? '') ?></td>
                        <td><?= esc($compra) ?></td>
                        <td><?= esc($m['ubic'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= esc($badgeClass, 'attr') ?>">
                                <?= esc($m['estado'] ?? 'Operativa') ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php $id = $m['id'] ?? null; ?>
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= $id ? base_url('modulo3/maquinaria/editar/'.$id) : 'javascript:void(0)' ?>">
                                Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No hay máquinas registradas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
