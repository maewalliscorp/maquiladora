<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .dt-buttons.btn-group .btn{
        margin-left:0!important;margin-right:.5rem;border-radius:.375rem!important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right:0; }
    .tabla-acciones-centradas th:last-child,
    .tabla-acciones-centradas td:last-child{
        text-align:center!important;white-space:nowrap;
    }
    .tabla-acciones-centradas td:last-child .acciones-wrap{
        display:inline-flex;align-items:center;gap:.5rem;
    }
    .tabla-acciones-centradas td:last-child .btn{
        padding:.25rem .45rem;border-radius:.5rem;line-height:1;
    }
</style>
<?= $this->endSection() ?>

<?php
$tableId   = $tableId   ?? 'tablaMtto';
$columns   = $columns   ?? ['Folio','Apertura','Máquina','Tipo','Estatus','Descripción','Cierre','Horas','Acciones'];
$rows      = is_array($rows ?? null) ? $rows : [];
$maquinas  = $maquinas  ?? [];
$empleados = $empleados ?? [];
?>

<?= $this->section('content') ?>

<!-- Encabezado + Acciones -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Mantenimiento Correctivo</h1>
        <span class="badge bg-danger">Averías</span>
    </div>
    <div class="d-flex gap-2">
        <!-- Botón global: Historial por máquina -->
        <button class="btn btn-outline-secondary"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalHistorialMaquina">
            <i class="bi bi-clock-history me-1"></i> Historial por máquina
        </button>

        <!-- Agregar OT -->
        <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalMtto">
            <i class="bi bi-plus-circle me-1"></i> Agregar
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ====================== MODAL CREAR ====================== -->
<div class="modal fade" id="modalMtto" tabindex="-1" aria-labelledby="modalMttoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="modalMttoLabel">Registrar orden de mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form class="row g-3" method="post" action="<?= site_url('mantenimiento/correctivo/crear') ?>" id="formCrear">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Fecha apertura *</label>
                            <input name="fechaApertura" id="f-fechaApertura" type="datetime-local" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Máquina *</label>
                            <select name="maquinaId" class="form-select" required>
                                <option value="" disabled selected>— Selecciona máquina —</option>
                                <?php foreach ($maquinas as $m): ?>
                                    <?php
                                    $codigo = $m['codigo'] ?? $m['clave'] ?? $m['serie'] ?? $m['modelo'] ?? $m['id'];
                                    $nombre = $m['nombre'] ?? $m['descripcion'] ?? $m['modelo'] ?? $m['serie'] ?? 'Sin nombre';
                                    ?>
                                    <option value="<?= esc($m['id'],'attr') ?>">
                                        [<?= esc($codigo) ?>] <?= esc($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Responsable</label>
                            <select name="responsableId" class="form-select">
                                <option value="">— Sin responsable —</option>
                                <?php foreach ($empleados as $e): ?>
                                    <?php
                                    $noEmp = $e['noEmpleado'] ?? $e['numeroEmpleado'] ?? $e['id'];
                                    $nom   = $e['nombre'] ?? $e['nombres'] ?? '';
                                    $ape   = $e['apellido'] ?? $e['apellidos'] ?? '';
                                    $full  = trim($nom.' '.$ape);
                                    ?>
                                    <option value="<?= esc($e['id'],'attr') ?>">
                                        [<?= esc($noEmp) ?>] <?= esc($full ?: 'Empleado') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
            <table id="<?= esc($tableId) ?>" class="table table-striped table-bordered align-middle tabla-acciones-centradas">
                <thead class="table-primary">
                <tr><?php foreach ($columns as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $estado   = $r['Estatus'] ?? '';
                    $cls      = ($estado === 'Cerrado') ? 'bg-success'
                            : (($estado === 'En reparación') ? 'bg-warning text-dark' : 'bg-danger');

                    $folio       = $r['Folio']        ?? '';
                    $apertura    = $r['Apertura']     ?? '';
                    $maquina     = $r['Maquina']      ?? '';
                    $maquinaId   = $r['MaquinaId']    ?? '';
                    $respId      = $r['ResponsableId']?? '';
                    $tipo        = $r['Tipo']         ?? '';
                    $descripcion = $r['Descripcion']  ?? '';
                    $cierre      = $r['Cierre']       ?? '';
                    $horas       = (string)($r['Horas'] ?? '0');
                    ?>
                    <tr>
                        <td><?= esc($folio) ?></td>
                        <td><?= esc($apertura) ?></td>
                        <td><?= esc($maquina) ?></td>
                        <td><?= esc($tipo) ?></td>
                        <td><span class="badge <?= esc($cls,'attr') ?>"><?= esc($estado) ?></span></td>
                        <td class="text-start"><?= esc($descripcion) ?></td>
                        <td><?= esc($cierre ?: '-') ?></td>
                        <td><?= number_format((float)$horas, 2) ?></td>
                        <td class="text-center">
                            <div class="acciones-wrap">
                                <!-- Ver -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-info btn-ver"
                                        title="Ver"
                                        data-bs-toggle="modal" data-bs-target="#modalVista"
                                        data-id="<?= esc($folio, 'attr') ?>"
                                        data-apertura="<?= esc($apertura, 'attr') ?>"
                                        data-maquina="<?= esc($maquina, 'attr') ?>"
                                        data-maquinaid="<?= esc($maquinaId, 'attr') ?>"
                                        data-responsableid="<?= esc($respId, 'attr') ?>"
                                        data-tipo="<?= esc($tipo, 'attr') ?>"
                                        data-estatus="<?= esc($estado, 'attr') ?>"
                                        data-descripcion="<?= esc($descripcion, 'attr') ?>"
                                        data-cierre="<?= esc($cierre, 'attr') ?>"
                                        data-horas="<?= esc($horas, 'attr') ?>">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Historial por máquina (prefija el select del modal) -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary btn-hist"
                                        title="Historial por máquina"
                                        data-bs-toggle="modal" data-bs-target="#modalHistorialMaquina"
                                        data-maquinaid="<?= esc($maquinaId, 'attr') ?>">
                                    <i class="bi bi-clock-history"></i>
                                </button>

                                <!-- Editar -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-editar"
                                        title="Editar"
                                        data-id="<?= esc($folio, 'attr') ?>"
                                        data-apertura="<?= esc($apertura, 'attr') ?>"
                                        data-maquinaid="<?= esc($maquinaId, 'attr') ?>"
                                        data-responsableid="<?= esc($respId, 'attr') ?>"
                                        data-tipo="<?= esc($tipo, 'attr') ?>"
                                        data-estatus="<?= esc($estado, 'attr') ?>"
                                        data-descripcion="<?= esc($descripcion, 'attr') ?>"
                                        data-cierre="<?= esc($cierre, 'attr') ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <!-- Eliminar -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-eliminar"
                                        title="Eliminar"
                                        data-id="<?= esc($folio, 'attr') ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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
                    <dt class="col-sm-3 fw-semibold text-dark">Responsable (ID)</dt><dd class="col-sm-9" id="v-responsable"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Tipo</dt>         <dd class="col-sm-9" id="v-tipo"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Estatus</dt>      <dd class="col-sm-9" id="v-estatus"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>  <dd class="col-sm-9" id="v-descripcion"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Cierre</dt>       <dd class="col-sm-9" id="v-cierre"></dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Horas</dt>        <dd class="col-sm-9" id="v-horas"></dd>
                </dl>
            </div>
            <div class="modal-footer">
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
                            <label class="form-label fw-semibold text-dark">Máquina *</label>
                            <select name="maquinaId" id="e-maquinaId" class="form-select" required>
                                <option value="">— Selecciona máquina —</option>
                                <?php foreach ($maquinas as $m): ?>
                                    <?php
                                    $codigo = $m['codigo'] ?? $m['clave'] ?? $m['serie'] ?? $m['modelo'] ?? $m['id'];
                                    $nombre = $m['nombre'] ?? $m['descripcion'] ?? $m['modelo'] ?? $m['serie'] ?? 'Sin nombre';
                                    ?>
                                    <option value="<?= esc($m['id'],'attr') ?>">
                                        [<?= esc($codigo) ?>] <?= esc($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Responsable</label>
                            <select name="responsableId" id="e-responsableId" class="form-select">
                                <option value="">— Sin responsable —</option>
                                <?php foreach ($empleados as $e): ?>
                                    <?php
                                    $noEmp = $e['noEmpleado'] ?? $e['numeroEmpleado'] ?? $e['id'];
                                    $nom   = $e['nombre'] ?? $e['nombres'] ?? '';
                                    $ape   = $e['apellido'] ?? $e['apellidos'] ?? '';
                                    $full  = trim($nom.' '.$ape);
                                    ?>
                                    <option value="<?= esc($e['id'],'attr') ?>">
                                        [<?= esc($noEmp) ?>] <?= esc($full ?: 'Empleado') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

<!-- ====================== MODAL ELIMINAR (legacy) ====================== -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="modalEliminarLabel">Eliminar orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEliminar" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="mb-0">
                        Esta acción es <strong>permanente</strong>.<br>
                        ¿Deseas eliminar la orden de mantenimiento <strong>#<span id="del-id"></span></strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" type="submit">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ====================== MODAL · HISTORIAL POR MÁQUINA ====================== -->
<div class="modal fade" id="modalHistorialMaquina" tabindex="-1" aria-labelledby="hmLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark">
                    Historial por máquina
                    <span class="badge bg-light text-dark ms-2" id="hm-badge">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Máquina</label>
                        <select id="hm-maquinaId" class="form-select">
                            <option value="">— Selecciona máquina —</option>
                            <?php foreach ($maquinas as $m): ?>
                                <?php
                                $codigo = $m['codigo'] ?? $m['clave'] ?? $m['serie'] ?? $m['modelo'] ?? $m['id'];
                                $nombre = $m['nombre'] ?? $m['descripcion'] ?? $m['modelo'] ?? $m['serie'] ?? 'Sin nombre';
                                ?>
                                <option value="<?= esc($m['id'],'attr') ?>">[<?= esc($codigo) ?>] <?= esc($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="hm-cargar">
                            <i class="bi bi-arrow-repeat me-1"></i> Cargar historial
                        </button>
                    </div>
                </div>

                <hr class="my-3">

                <div class="table-responsive">
                    <table id="tablaHistorialMaquina" class="table table-striped table-bordered align-middle w-100">
                        <thead class="table-primary">
                        <tr>
                            <th>Folio</th>
                            <th>Apertura</th>
                            <th>Tipo</th>
                            <th>Estatus</th>
                            <th>Descripción</th>
                            <th>Cierre</th>
                            <th>Horas</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    (function(){
        const tableSelector = '#<?= esc($tableId) ?>';

        // ===== Helpers SweetAlert2 =====
        const confirmAsync = (title, text, icon='warning', confirmText='Sí, continuar')=>{
            return Swal.fire({
                title, text, icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then(r=>r.isConfirmed);
        };
        const toast = (text, icon='success')=>{
            return Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 1700, timerProgressBar: true, icon, title: text
            });
        };
        const loading = (title='Procesando...')=>{
            Swal.fire({title, allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
        };

        // ===== DataTables (principal) =====
        const langES = {
            sEmptyTable:"Sin datos", sZeroRecords:"No se encontraron resultados",
            sInfo:"Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty:"Mostrando 0–0 de 0",
            sInfoFiltered:"(filtrado de _MAX_)", sSearch:"Buscar:",
            oPaginate:{sFirst:"Primero",sLast:"Último",sNext:"Siguiente",sPrevious:"Anterior"}
        };

        const fecha = new Date().toISOString().slice(0,10);
        const fileName = 'mantenimiento_correctivo_' + fecha;

        $(tableSelector).DataTable({
            language: langES,
            columnDefs: [
                { targets: -1, orderable:false, searchable:false, className: 'text-center' }
            ],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copy',  text:'Copy',  exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'csv',   text:'CSV',   filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'excel', text:'Excel', filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'pdf',   text:'PDF',   filename:fileName, title:fileName,
                    orientation:'landscape', pageSize:'A4',
                    exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'print', text:'Print', exportOptions:{ columns: ':not(:last-child)' } }
            ]
        });

        // ====== Prefills ======
        const crear = document.getElementById('modalMtto');
        if (crear) {
            crear.addEventListener('show.bs.modal', () => {
                const input = document.getElementById('f-fechaApertura');
                const pad = n => String(n).padStart(2,'0');
                const d = new Date();
                const val = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
                if (input && !input.value) input.value = val;
            });
        }

        function toLocalInputValue(dt) {
            if (!dt) return '';
            if (dt.includes('T')) return dt.slice(0,16);
            return dt.replace(' ', 'T').slice(0,16);
        }

        // VER
        document.querySelectorAll('.btn-ver').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const d = btn.dataset;
                document.getElementById('v-folio').textContent       = d.id || '';
                document.getElementById('v-apertura').textContent    = d.apertura || '';
                document.getElementById('v-maquina').textContent     = d.maquina || '';
                document.getElementById('v-responsable').textContent = d.responsableid || '(sin responsable)';
                document.getElementById('v-tipo').textContent        = d.tipo || '';
                document.getElementById('v-estatus').textContent     = d.estatus || '';
                document.getElementById('v-descripcion').textContent = d.descripcion || '';
                document.getElementById('v-cierre').textContent      = d.cierre || '-';
                document.getElementById('v-horas').textContent       = (d.horas ? parseFloat(d.horas).toFixed(2) : '0.00');
            });
        });

        // EDITAR
        document.querySelectorAll('.btn-editar').forEach(btn=>{
            btn.addEventListener('click', async (ev)=>{
                ev.preventDefault();
                const d = btn.dataset;

                if (!(await confirmAsync('¿Editar orden #'+ (d.id||'') +'?', 'Entrarás al modo edición.', 'question', 'Editar')))
                    return;

                const form = document.getElementById('formEditar');
                form.action = '<?= site_url("mantenimiento/correctivo/actualizar") ?>' + '/' + (d.id || '');

                document.getElementById('e-id').value            = d.id || '';
                document.getElementById('e-apertura').value      = toLocalInputValue(d.apertura || '');
                document.getElementById('e-maquinaId').value     = d.maquinaid || '';
                document.getElementById('e-responsableId').value = d.responsableid || '';
                document.getElementById('e-tipo').value          = d.tipo || 'Correctivo';
                document.getElementById('e-estatus').value       = d.estatus || 'Abierta';
                document.getElementById('e-descripcion').value   = d.descripcion || '';
                document.getElementById('e-cierre').value        = toLocalInputValue(d.cierre || '');

                const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                modal.show();
            });
        });

        // ELIMINAR
        document.querySelectorAll('.btn-eliminar').forEach(btn=>{
            btn.addEventListener('click', async (ev)=>{
                ev.preventDefault();
                const id = btn.dataset.id || '';

                if (!(await confirmAsync('¿Eliminar orden #'+ id +'?', 'Esta acción no se puede deshacer.', 'warning', 'Sí, eliminar')))
                    return;

                const form = document.getElementById('formEliminar');
                form.action = '<?= site_url("mantenimiento/correctivo/eliminar") ?>' + '/' + id;

                loading('Eliminando...');
                form.submit();
            });
        });

        // ====== Submits con confirmación ======
        const formCrear = document.getElementById('formCrear');
        if (formCrear){
            formCrear.addEventListener('submit', async (e)=>{
                e.preventDefault();
                if (await confirmAsync('¿Guardar orden?', 'Se registrará una nueva orden de mantenimiento.', 'question', 'Guardar')) {
                    loading('Guardando...');
                    formCrear.submit();
                }
            });
        }

        const formEditar = document.getElementById('formEditar');
        if (formEditar){
            formEditar.addEventListener('submit', async (e)=>{
                e.preventDefault();
                if (await confirmAsync('¿Guardar cambios?', 'Se actualizará la orden seleccionada.', 'question', 'Guardar')) {
                    loading('Actualizando...');
                    formEditar.submit();
                }
            });
        }

        // ====== Flash con SweetAlert ======
        <?php if (session()->getFlashdata('success')): ?>
        toast('<?= esc(session()->getFlashdata('success')) ?>', 'success');
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        Swal.fire('Error', '<?= esc(session()->getFlashdata('error')) ?>', 'error');
        <?php endif; ?>

        /* =================================================================
         *  HISTORIAL POR MÁQUINA (modal con DataTable que NO se re-inicializa)
         * ================================================================= */
        const HIST_URL = '<?= site_url('mantenimiento/correctivo/historial/maquina') ?>';
        const modalEl   = document.getElementById('modalHistorialMaquina');
        const selectEl  = document.getElementById('hm-maquinaId');
        const badgeEl   = document.getElementById('hm-badge');
        const btnCargar = document.getElementById('hm-cargar');

        let dtHist = null;

        function ensureDt(){
            if (dtHist) return dtHist;
            dtHist = $('#tablaHistorialMaquina').DataTable({
                language: langES,
                data: [],
                columns: [
                    { data: 'Folio'       },
                    { data: 'Apertura'    },
                    { data: 'Tipo'        },
                    { data: 'Estatus'     },
                    { data: 'Descripcion' },
                    { data: 'Cierre'      },
                    { data: 'Horas',
                        render: d => (d!=null && d!=='') ? parseFloat(d).toFixed(2) : '0.00',
                        className:'text-end'
                    }
                ],
                order: [[1,'desc']],
                destroy: false
            });
            return dtHist;
        }

        function actualizarBadge(){
            const opt = selectEl.options[selectEl.selectedIndex];
            const etiqueta = opt ? (opt.text.match(/\[(.*?)\]/)?.[1] || opt.text) : '—';
            badgeEl.textContent = etiqueta || '—';
        }

        async function cargarHistorial(maquinaId){
            if (!maquinaId) { ensureDt().clear().draw(); actualizarBadge(); return; }
            actualizarBadge();
            try{
                const res  = await fetch(`${HIST_URL}/${maquinaId}`, { headers:{'Accept':'application/json'} });
                const json = await res.json();
                const list = Array.isArray(json) ? json : (json.data || []);
                const rows = list.map(r => ({
                    Folio:       r.Folio ?? r.id ?? '',
                    Apertura:    r.Apertura ?? r.fechaApertura ?? '',
                    Tipo:        r.Tipo ?? r.tipo ?? '',
                    Estatus:     r.Estatus ?? r.estatus ?? '',
                    Descripcion: r.Descripcion ?? r.descripcion ?? '',
                    Cierre:      (r.Cierre ?? r.fechaCierre ?? '') || '-',
                    Horas:       r.Horas ?? r.tiempoHoras ?? 0
                }));
                const dt = ensureDt();
                dt.clear().rows.add(rows).draw();
            }catch(e){
                ensureDt().clear().draw();
                console.error('Historial error:', e);
            }
        }

        modalEl.addEventListener('show.bs.modal', (ev)=>{
            const trg = ev.relatedTarget;
            if (trg && trg.dataset && trg.dataset.maquinaid){
                selectEl.value = trg.dataset.maquinaid;
            }
            ensureDt();
            if (selectEl.value) cargarHistorial(selectEl.value);
            else actualizarBadge();
        });

        btnCargar.addEventListener('click', ()=> cargarHistorial(selectEl.value));
        selectEl.addEventListener('change', ()=> cargarHistorial(selectEl.value));

        document.addEventListener('click', (e)=>{
            const btn = e.target.closest('.btn-hist');
            if (!btn) return;
            // Nada más; el show.bs.modal hará el prefijo por data-maquinaid
        });
    })();
</script>
<?= $this->endSection() ?>
