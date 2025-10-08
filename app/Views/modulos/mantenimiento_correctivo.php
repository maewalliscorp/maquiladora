<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?php
$tableId = $tableId ?? 'tablaMtto';
$columns = $columns ?? ['Folio','Apertura','Máquina','Tipo','Estatus','Descripción','Cierre','Horas','Vista']; // Vista al final
$rows    = is_array($rows ?? null) ? $rows : [];
?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-3">
    <h1 class="me-3">Mantenimiento Correctivo</h1>
    <span class="badge bg-danger">Averías</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Botón Agregar -> Modal crear -->
<div class="mb-3">
    <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalMtto">
        <i class="bi bi-plus-circle me-1"></i> Agregar
    </button>
</div>

<!-- ====================== MODAL CREAR (centrado) ====================== -->
<div class="modal fade" id="modalMtto" tabindex="-1" aria-labelledby="modalMttoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="modalMttoLabel">Registrar orden de mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form class="row g-3" method="post" action="<?= site_url('mantenimiento/correctivo/crear') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Fecha apertura *</label>
                            <input name="fechaApertura" id="f-fechaApertura" type="datetime-local" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Máquina ID *</label>
                            <input name="maquinaId" type="number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Responsable ID</label>
                            <input name="responsableId" type="number" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Tipo *</label>
                            <input name="tipo" class="form-control" value="Correctivo" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Estatus *</label>
                            <select name="estatus" class="form-select" required>
                                <option selected>Abierta</option>
                                <option>En reparación</option>
                                <option>Cerrado</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-dark">Descripción</label>
                            <input name="descripcion" class="form-control" placeholder="Motor detenido / Correa rota">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Fecha cierre</label>
                            <input name="fechaCierre" type="datetime-local" class="form-control">
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12"><span class="text-muted">Detalle inicial (opcional)</span></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Acción</label>
                            <input name="d_accion" class="form-control" placeholder="Cambio de polea">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark">Repuestos usados</label>
                            <input name="d_repuestos" class="form-control" placeholder="Polea A-32, Correa B-45">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Tiempo (hrs)</label>
                            <input name="d_horas" type="number" step="0.25" min="0" class="form-control" placeholder="1.5">
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

<!-- ====================== TABLA ====================== -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Historial por máquina</strong>
        <span class="text-muted small">Filas: <?= count($rows) ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="<?= esc($tableId) ?>" class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                <tr><?php foreach ($columns as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $estado = $r['Estatus'] ?? '';
                    $cls = ($estado === 'Cerrado') ? 'bg-success'
                            : (($estado === 'En reparación') ? 'bg-warning text-dark' : 'bg-danger');
                    ?>
                    <tr>
                        <!-- Campos en el orden del header, excepto Vista que va al final -->
                        <td><?= esc($r['Folio'] ?? '') ?></td>
                        <td><?= esc($r['Apertura'] ?? '') ?></td>
                        <td><?= esc($r['Maquina'] ?? '') ?></td>
                        <td><?= esc($r['Tipo'] ?? '') ?></td>
                        <td><span class="badge <?= esc($cls,'attr') ?>"><?= esc($estado) ?></span></td>
                        <td class="text-start"><?= esc($r['Descripcion'] ?? '') ?></td>
                        <td><?= esc($r['Cierre'] ?? '-') ?></td>
                        <td><?= number_format((float)($r['Horas'] ?? 0), 2) ?></td>

                        <!-- ▶︎ Última columna: Vista -->
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-info btn-vista"
                                    data-bs-toggle="modal" data-bs-target="#modalVista"
                                    data-id="<?= esc($r['Folio'] ?? '', 'attr') ?>"
                                    data-apertura="<?= esc($r['Apertura'] ?? '', 'attr') ?>"
                                    data-maquina="<?= esc($r['Maquina'] ?? '', 'attr') ?>"
                                    data-tipo="<?= esc($r['Tipo'] ?? '', 'attr') ?>"
                                    data-estatus="<?= esc($estado, 'attr') ?>"
                                    data-descripcion="<?= esc($r['Descripcion'] ?? '', 'attr') ?>"
                                    data-cierre="<?= esc($r['Cierre'] ?? '', 'attr') ?>"
                                    data-horas="<?= esc((string)($r['Horas'] ?? '0'), 'attr') ?>">
                                <i class="bi bi-eye"></i> Vista
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ====================== MODAL VISTA ====================== -->
<div class="modal fade" id="modalVista" tabindex="-1" aria-labelledby="modalVistaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="modalVistaLabel">Detalle de la orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 fw-semibold text-dark">Folio</dt>        <dd class="col-sm-9" id="v-folio"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Apertura</dt>     <dd class="col-sm-9" id="v-apertura"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Máquina</dt>      <dd class="col-sm-9" id="v-maquina"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Tipo</dt>         <dd class="col-sm-9" id="v-tipo"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Estatus</dt>      <dd class="col-sm-9" id="v-estatus"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>  <dd class="col-sm-9" id="v-descripcion"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Cierre</dt>       <dd class="col-sm-9" id="v-cierre"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Horas</dt>        <dd class="col-sm-9" id="v-horas"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btnAbrirEditar" data-bs-target="#modalEditar" data-bs-toggle="modal">
                    <i class="bi bi-pencil"></i> Editar
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ====================== MODAL EDITAR ====================== -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditarLabel">Editar orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formEditar" class="row g-3" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="e-id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Fecha apertura *</label>
                            <input name="fechaApertura" id="e-apertura" type="datetime-local" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Máquina ID *</label>
                            <input name="maquinaId" id="e-maquinaId" type="number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Responsable ID</label>
                            <input name="responsableId" id="e-responsableId" type="number" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Tipo *</label>
                            <input name="tipo" id="e-tipo" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Estatus *</label>
                            <select name="estatus" id="e-estatus" class="form-select" required>
                                <option>Abierta</option>
                                <option>En reparación</option>
                                <option>Cerrado</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-dark">Descripción</label>
                            <input name="descripcion" id="e-descripcion" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Fecha cierre</label>
                            <input name="fechaCierre" id="e-cierre" type="datetime-local" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    (function(){
        // DataTables: desactivar ordenar/buscar solo en la ÚLTIMA columna (Vista)
        $('#<?= esc($tableId) ?>').DataTable({
            language:{
                sEmptyTable:"Sin datos", sZeroRecords:"No se encontraron resultados",
                sInfo:"Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty:"Mostrando 0–0 de 0",
                sInfoFiltered:"(filtrado de _MAX_)", sSearch:"Buscar:",
                oPaginate:{sFirst:"Primero",sLast:"Último",sNext:"Siguiente",sPrevious:"Anterior"}
            },
            columnDefs: [{ targets: -1, orderable:false, searchable:false }]
        });

        // Autollenar fecha apertura al abrir "Crear"
        document.getElementById('modalMtto').addEventListener('show.bs.modal', () => {
            const input = document.getElementById('f-fechaApertura');
            const pad = n => String(n).padStart(2,'0');
            const d = new Date();
            const val = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+
                'T'+pad(d.getHours())+':'+pad(d.getMinutes());
            if (!input.value) input.value = val;
        });

        // Utilidad para normalizar datetime-local
        function toLocalInputValue(dt) {
            if (!dt) return '';
            if (dt.includes('T')) return dt.slice(0,16);
            return dt.replace(' ', 'T').slice(0,16);
        }

        // Cargar datos al modal Vista
        let currentRowData = null;
        document.querySelectorAll('.btn-vista').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const d = btn.dataset;
                currentRowData = { ...d };

                document.getElementById('v-folio').textContent       = d.id || '';
                document.getElementById('v-apertura').textContent    = d.apertura || '';
                document.getElementById('v-maquina').textContent     = d.maquina || '';
                document.getElementById('v-tipo').textContent        = d.tipo || '';
                document.getElementById('v-estatus').textContent     = d.estatus || '';
                document.getElementById('v-descripcion').textContent = d.descripcion || '';
                document.getElementById('v-cierre').textContent      = d.cierre || '-';
                document.getElementById('v-horas').textContent       = (d.horas ? parseFloat(d.horas).toFixed(2) : '0.00');
            });
        });

        // Pasar datos al modal Editar
        document.getElementById('btnAbrirEditar').addEventListener('click', ()=>{
            if (!currentRowData) return;
            const d = currentRowData;

            const form = document.getElementById('formEditar');
            form.action = '<?= site_url("mantenimiento/correctivo/actualizar") ?>' + '/' + (d.id || '');

            document.getElementById('e-id').value          = d.id || '';
            document.getElementById('e-apertura').value    = toLocalInputValue(d.apertura || '');
            // si "Maquina" es código (MC-001), aquí deberías pasar también data-maquinaid en el botón:
            document.getElementById('e-maquinaId').value   = (!isNaN(d.maquina) ? d.maquina : '');
            document.getElementById('e-responsableId').value = '';
            document.getElementById('e-tipo').value        = d.tipo || 'Correctivo';
            document.getElementById('e-estatus').value     = d.estatus || 'Abierta';
            document.getElementById('e-descripcion').value = d.descripcion || '';
            document.getElementById('e-cierre').value      = toLocalInputValue(d.cierre || '');
        });
    })();
</script>
<?= $this->endSection() ?>
