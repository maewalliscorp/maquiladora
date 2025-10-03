<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Work in Progress (WIP)</h1>
    <span class="badge bg-primary">Módulo 3</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Menú del Módulo 3 -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-2">
                <a href="<?= base_url('modulo3') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/ordenes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-clipboard-data me-1"></i>Órdenes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/wip') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-gear me-1"></i>WIP
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/incidencias') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-exclamation-triangle me-1"></i>Incidencias
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/reportes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>Reportes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/notificaciones') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-bell me-1"></i>Notificaciones
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><strong>Etapas de Producción</strong></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Etapa</th>
                    <th>Responsable</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th style="width:260px">Progreso</th>
                    <th style="width:220px">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var array $etapas */
                $rows = isset($etapas) && is_array($etapas) ? $etapas : [];
                ?>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" class="py-4 text-muted">No hay etapas en progreso.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $etapa): ?>
                        <?php
                        $prog = (int)($etapa['prog'] ?? 0);
                        $w    = $prog . '%';
                        // color de la barra según avance
                        $barClass = 'bg-danger';
                        if     ($prog >= 67) $barClass = 'bg-primary';
                        elseif ($prog >= 34) $barClass = 'bg-warning';
                        // fechas amigables si vienen en otro formato
                        $fmt = function($d){ $t = strtotime((string)$d); return $t ? date('Y-m-d', $t) : esc((string)$d); };
                        $id  = $etapa['id'] ?? null;
                        ?>
                        <tr>
                            <td><?= esc($etapa['etapa'] ?? '—') ?></td>
                            <td><?= esc($etapa['resp']  ?? '—') ?></td>
                            <td><?= $fmt($etapa['ini'] ?? '') ?></td>
                            <td><?= $fmt($etapa['fin'] ?? '') ?></td>
                            <td>
                                <div class="progress" style="height:20px;">
                                    <div class="progress-bar <?= esc($barClass, 'attr') ?>" role="progressbar"
                                         style="width: <?= esc($w, 'attr') ?>;" aria-valuenow="<?= $prog ?>"
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= $prog ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($id): ?>
                                    <form method="post" action="<?= base_url('modulo3/wip/actualizar/'.$id) ?>" class="d-flex justify-content-center align-items-center gap-2">
                                        <?= csrf_field() ?>
                                        <input type="number" name="avance" min="0" max="100" value="<?= $prog ?>" class="form-control form-control-sm" style="width:90px" />
                                        <button class="btn btn-sm btn-outline-primary">Actualizar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Sin ID</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
