<?= $this->extend('layouts/main') ?>

<!-- DataTables -->
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .table td, .table th{ padding:.85rem .8rem; }
    .table.tbl-head thead th{
        background:#e6f0fb;color:#0b1720;font-weight:700;vertical-align:middle;border-color:#cfddec;position:relative;
    }
    .table.tbl-head thead th:not(:last-child){ box-shadow: inset -1px 0 0 #cfddec; }
    .table.tbl-head tbody td{ background:#f9fbfe; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3 mb-0">Desperdicios & Reprocesos</h1>
        <span class="badge bg-danger">Calidad</span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDesecho">
            <i class="bi bi-plus-lg me-1"></i>Agregar RD
        </button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalReproceso">
            <i class="bi bi-plus-lg me-1"></i>Agregar UP
        </button>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">

    <!-- ====== DESECHOS (fila completa) ====== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Registro de desecho</strong>
                <!-- (Se quitó el botón Agregar del header) -->
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaDesechos" class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>OP</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($desp)): foreach($desp as $x): ?>
                            <tr>
                                <td><?= esc($x['fecha']) ?></td>
                                <td><?= esc($x['op']) ?></td>
                                <td><?= esc($x['cantidad']) ?></td>
                                <td><?= esc($x['observaciones']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info ver-desecho"
                                                data-id="<?= (int)$x['id'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalVistaDesecho"
                                                title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-desecho"
                                                data-id="<?= (int)$x['id'] ?>"
                                                data-fecha="<?= esc($x['fecha']) ?>"
                                                data-op="<?= esc($x['op']) ?>"
                                                data-cantidad="<?= esc($x['cantidad']) ?>"
                                                data-motivo="<?= esc($x['observaciones']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalDesecho"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== REPROCESOS (otra fila completa) ====== -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Unidades en reproceso</strong>
                <!-- (Se quitó el botón Agregar del header) -->
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaReprocesos" class="table table-striped table-bordered align-middle text-center mb-0 tbl-head">
                        <thead>
                        <tr>
                            <th>OP</th>
                            <th>Tarea</th>
                            <th>Pend.</th>
                            <th>ETA</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($rep)): foreach($rep as $r): ?>
                            <tr>
                                <td><?= esc($r['op']) ?></td>
                                <td><?= esc($r['tarea']) ?></td>
                                <td><?= (int)$r['pendientes'] ?></td>
                                <td><?= esc($r['eta']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info ver-rep"
                                                data-id="<?= (int)$r['id'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalVistaReproceso"
                                                title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-rep"
                                                data-id="<?= (int)$r['id'] ?>"
                                                data-op="<?= esc($r['op']) ?>"
                                                data-tarea="<?= esc($r['tarea']) ?>"
                                                data-pendientes="<?= (int)$r['pendientes'] ?>"
                                                data-eta="<?= esc($r['eta']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalReproceso"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===== Modales ===== -->

<!-- Agregar/Editar Desecho -->
<div class="modal fade" id="modalDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Desecho</h5>
                <div class="ms-auto">
                    <!-- botones contextuales (opcionales) -->
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formDesecho" action="<?= site_url('calidad/desperdicios/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="d-fecha" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">OP</label>
                            <input class="form-control" name="op" id="d-op" placeholder="1001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" class="form-control" name="cantidad" id="d-cantidad" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo (observaciones)</label>
                            <input class="form-control" name="motivo" id="d-motivo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Desecho -->
<div class="modal fade" id="modalVistaDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Detalle del desecho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9" id="vd-fecha">-</dd>
                    <dt class="col-sm-3">OP</dt><dd class="col-sm-9" id="vd-op">-</dd>
                    <dt class="col-sm-3">Cantidad</dt><dd class="col-sm-9" id="vd-cantidad">-</dd>
                    <dt class="col-sm-3">Motivo</dt><dd class="col-sm-9" id="vd-motivo">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Agregar/Editar Reproceso -->
<div class="modal fade" id="modalReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Reproceso</h5>
                <div class="ms-auto"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formReproceso" action="<?= site_url('calidad/reprocesos/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">OP</label>
                            <input class="form-control" name="op" id="r-op" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Tarea</label>
                            <input class="form-control" name="tarea" id="r-tarea" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendientes</label>
                            <input type="number" min="0" class="form-control" name="pendientes" id="r-pendientes" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ETA</label>
                            <input type="date" class="form-control" name="eta" id="r-eta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="r-estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="terminado">Terminado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Reproceso -->
<div class="modal fade" id="modalVistaReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Detalle del reproceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">OP</dt><dd class="col-sm-9" id="vr-op">-</dd>
                    <dt class="col-sm-3">Tarea</dt><dd class="col-sm-9" id="vr-tarea">-</dd>
                    <dt class="col-sm-3">Pendientes</dt><dd class="col-sm-9" id="vr-pendientes">-</dd>
                    <dt class="col-sm-3">ETA</dt><dd class="col-sm-9" id="vr-eta">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function () {
        const langES = {
            sProcessing:"Procesando...",
            sLengthMenu:"Mostrar _MENU_",
            sZeroRecords:"No se encontraron resultados",
            sEmptyTable:"Sin datos",
            sInfo:"Mostrando _START_–_END_ de _TOTAL_",
            sInfoEmpty:"Mostrando 0–0 de 0",
            sInfoFiltered:"(filtrado de _MAX_)",
            sSearch:"Buscar:",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" }
        };

        $('#tablaDesechos').DataTable({
            language: langES,
            columnDefs: [{ orderable:false, searchable:false, targets:[4] }]
        });

        $('#tablaReprocesos').DataTable({
            language: langES,
            columnDefs: [{ orderable:false, searchable:false, targets:[4] }]
        });

        // Ver: Desecho
        $(document).on('click', '.ver-desecho', async function(){
            const id = this.dataset.id;
            const r  = await (await fetch('<?= site_url('calidad/desperdicios') ?>/'+id)).json();
            document.getElementById('vd-fecha').textContent     = r.fecha || '-';
            document.getElementById('vd-op').textContent        = r.op || '-';
            document.getElementById('vd-cantidad').textContent  = r.cantidad || '-';
            document.getElementById('vd-motivo').textContent    = r.observaciones || '-';
        });

        // Ver: Reproceso
        $(document).on('click', '.ver-rep', async function(){
            const id = this.dataset.id;
            const r  = await (await fetch('<?= site_url('calidad/reprocesos') ?>/'+id)).json();
            document.getElementById('vr-op').textContent         = r.op || '-';
            document.getElementById('vr-tarea').textContent      = r.tarea || '-';
            document.getElementById('vr-pendientes').textContent = (r.cantidad ?? r.pendientes) || '-';
            document.getElementById('vr-eta').textContent        = (r.fecha ?? r.eta) || '-';
        });

        // Editar: Desecho
        $(document).on('click', '.edit-desecho', function(){
            const f = document.getElementById('formDesecho');
            f.action = '<?= site_url('calidad/desperdicios') ?>/'+this.dataset.id+'/editar';
            document.getElementById('d-fecha').value    = this.dataset.fecha || '';
            document.getElementById('d-op').value       = this.dataset.op || '';
            document.getElementById('d-cantidad').value = this.dataset.cantidad || '';
            document.getElementById('d-motivo').value   = this.dataset.motivo || '';
        });
        document.getElementById('modalDesecho').addEventListener('show.bs.modal', e=>{
            if (!e.relatedTarget.classList.contains('edit-desecho')) {
                const f = document.getElementById('formDesecho');
                f.action = '<?= site_url('calidad/desperdicios/guardar') ?>';
                ['d-fecha','d-op','d-cantidad','d-motivo'].forEach(id=>document.getElementById(id).value='');
            }
        });

        // Editar: Reproceso
        $(document).on('click', '.edit-rep', function(){
            const f = document.getElementById('formReproceso');
            f.action = '<?= site_url('calidad/reprocesos') ?>/'+this.dataset.id+'/editar';
            document.getElementById('r-op').value         = this.dataset.op || '';
            document.getElementById('r-tarea').value      = this.dataset.tarea || '';
            document.getElementById('r-pendientes').value = this.dataset.pendientes || '';
            document.getElementById('r-eta').value        = this.dataset.eta || '';
            document.getElementById('r-estado').value     = 'pendiente';
        });
        document.getElementById('modalReproceso').addEventListener('show.bs.modal', e=>{
            if (!e.relatedTarget.classList.contains('edit-rep')) {
                const f = document.getElementById('formReproceso');
                f.action = '<?= site_url('calidad/reprocesos/guardar') ?>';
                ['r-op','r-tarea','r-pendientes','r-eta'].forEach(id=>document.getElementById(id).value='');
                document.getElementById('r-estado').value = 'pendiente';
            }
        });
    });
</script>
<?= $this->endSection() ?>
