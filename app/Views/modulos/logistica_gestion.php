<?= $this->extend('layouts/main') ?>

<?php
$envios         = $envios ?? [];
$transportistas = $transportistas ?? [];
$embarques      = $embarques ?? [];
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .btn-icon{ padding:.25rem .45rem; border-width:2px; }
    /* Botonera tipo “pastillas” separadas */
    div.dt-buttons{
        display:flex !important;
        gap:.65rem;
        flex-wrap:wrap;
        background:transparent !important;
        border:0 !important;
        border-radius:0 !important;
        box-shadow:none !important;
        padding:0 !important;
    }
    div.dt-buttons > .btn:not(:first-child){ margin-left:0 !important; }
    div.dt-buttons > .btn{ border-radius:.65rem !important; padding:.45rem 1rem !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Título + Agregar -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Gestión de Envíos</h1>
        <span class="badge bg-info text-dark">Tracking</span>
    </div>
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#agregarModal">
        <i class="bi bi-plus-circle me-1"></i> Agregar
    </button>
</div>

<?php if (session()->getFlashdata('ok')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('ok')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ===== MODAL: Agregar envío ===== -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarModalLabel">Registrar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAgregar" method="post" action="<?= site_url('modulo3/envios/crear') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Embarque (folio)</label>
                        <select class="form-select" name="embarqueId">
                            <option value="">— Selecciona —</option>
                            <?php foreach($embarques as $e): ?>
                                <option value="<?= (int)$e['id'] ?>"><?= esc($e['folio']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Empresa transporte</label>
                        <select class="form-select" name="transportistaId" required>
                            <option value="">— Selecciona —</option>
                            <?php foreach($transportistas as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= esc($t['nombre']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Número de guía</label>
                        <input class="form-control" name="numeroGuia" placeholder="JD0148899001" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">URL de seguimiento</label>
                        <input class="form-control" name="urlSeguimiento" placeholder="https://...">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha de salida</label>
                            <input type="date" class="form-control" name="fechaSalida">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="">— Selecciona —</option>
                                <option value="En tránsito">En tránsito</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Retrasado">Retrasado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL: Ver envío ===== -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verModalLabel">Detalle de envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-4">Embarque</dt><dd class="col-8" id="v-embarque">-</dd>
                    <dt class="col-4">Transportista</dt><dd class="col-8" id="v-transportista">-</dd>
                    <dt class="col-4">Núm. guía</dt><dd class="col-8" id="v-guia">-</dd>
                    <dt class="col-4">Fecha salida</dt><dd class="col-8" id="v-fecha">-</dd>
                    <dt class="col-4">Estado</dt><dd class="col-8" id="v-estado">-</dd>
                    <dt class="col-4">Seguimiento</dt>
                    <dd class="col-8" id="v-url"><a href="#" target="_blank" id="v-url-a">—</a></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Editar envío ===== -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditar" method="post" action="#">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Embarque (folio)</label>
                        <select class="form-select" name="embarqueId" id="e-embarqueId">
                            <option value="">— Selecciona —</option>
                            <?php foreach($embarques as $e): ?>
                                <option value="<?= (int)$e['id'] ?>"><?= esc($e['folio']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Empresa transporte</label>
                        <select class="form-select" name="transportistaId" id="e-transportistaId">
                            <option value="">— Selecciona —</option>
                            <?php foreach($transportistas as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= esc($t['nombre']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Número de guía</label>
                        <input class="form-control" name="numeroGuia" id="e-numeroGuia">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">URL de seguimiento</label>
                        <input class="form-control" name="urlSeguimiento" id="e-urlSeguimiento">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha de salida</label>
                            <input type="date" class="form-control" name="fechaSalida" id="e-fechaSalida">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="e-estado">
                                <option value="">— Selecciona —</option>
                                <option value="En tránsito">En tránsito</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Retrasado">Retrasado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Tabla ===== -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Envíos</strong></div>
    <div class="card-body p-0">
        <table id="tablaEnvios" class="table table-striped table-bordered m-0 align-middle">
            <thead class="table-primary">
            <tr>
                <th class="text-center">Fecha</th>
                <th class="text-center">Empresa</th>
                <th class="text-center">Guía</th>
                <th class="text-center">Embarque</th>
                <th class="text-center">Estado</th>
                <th class="text-center" style="width:210px;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($envios as $r): ?>
                <tr>
                    <td class="text-center"><?= esc($r['fechaSalida'] ?? $r['fechaEmbarque'] ?? '') ?></td>
                    <td class="text-center"><?= esc($r['transportista'] ?? '') ?></td>
                    <td class="text-center"><?= esc($r['numeroGuia'] ?? '') ?></td>
                    <td class="text-center"><?= esc($r['embarque'] ?? '') ?></td>
                    <td class="text-center">
                        <?php $estado = $r['estado'] ?? ($r['estatusEmbarque'] ?? ''); ?>
                        <span class="badge <?= $estado==='Entregado' ? 'bg-success' : 'bg-info text-dark' ?>">
              <?= esc($estado ?: '—') ?>
            </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-info btn-sm btn-icon btn-ver" data-id="<?= (int)$r['id'] ?>" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-outline-primary btn-sm btn-icon btn-editar" data-id="<?= (int)$r['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></button>

                            <?php if (!empty($r['urlSeguimiento'])): ?>
                                <a class="btn btn-outline-secondary btn-sm btn-icon" href="<?= esc($r['urlSeguimiento']) ?>" target="_blank" title="Abrir tracking">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            <?php endif; ?>

                            <form method="post" action="<?= site_url('modulo3/envios/'.(int)$r['id'].'/eliminar') ?>" class="d-inline ms-1"
                                  onsubmit="return confirm('¿Eliminar envío?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-danger btn-sm btn-icon" type="submit" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    (function(){
        // Quitar btn-group por defecto para que no quede barra gris
        $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons';

        const langES = {
            sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_ registros",
            sZeroRecords:"No se encontraron resultados", sEmptyTable:"Ningún dato disponible",
            sInfo:"Filas: _TOTAL_", sInfoEmpty:"Filas: 0", sInfoFiltered:"(de _MAX_)",
            sSearch:"Buscar:", sLoadingRecords:"Cargando...",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
        };

        const tieneDatosEnvios = $('#tablaEnvios tbody tr').filter(function(){
            return !$(this).find('td[colspan]').length;
        }).length > 0;

        if (tieneDatosEnvios) {
            $('#tablaEnvios').DataTable({
                language: langES,
                dom: "<'row px-3 pt-3'<'col-sm-6'B><'col-sm-6'f>>" + "t" +
                    "<'row p-3'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [
                    { extend:'copy',  text:'Copy',     className:'btn btn-secondary' },
                    { extend:'csv',   text:'CSV',      className:'btn btn-secondary' },
                    { extend:'excel', text:'Excel',    className:'btn btn-secondary' },
                    { extend:'pdf',   text:'PDF',      className:'btn btn-secondary' },
                    { extend:'print', text:'Print',    className:'btn btn-secondary' }
                ],
                pageLength: 10,
                columnDefs: [{ targets: -1, orderable:false, searchable:false }]
            });
        }

        const base = "<?= site_url('modulo3/envios') ?>";

        // Ver
        document.querySelectorAll('.btn-ver').forEach(b=>{
            b.addEventListener('click', async ()=>{
                const id = b.dataset.id;
                const r  = await fetch(`${base}/${id}/json`);
                if(!r.ok) return alert('No se pudo cargar el envío');
                const d  = await r.json();
                document.getElementById('v-embarque').textContent     = d.embarqueFolio ?? '—';
                document.getElementById('v-transportista').textContent= d.transportista ?? '—';
                document.getElementById('v-guia').textContent         = d.numeroGuia ?? '—';
                document.getElementById('v-fecha').textContent        = d.fechaSalida ?? '—';
                document.getElementById('v-estado').textContent       = d.estado ?? '—';
                const a = document.getElementById('v-url-a');
                a.textContent = d.urlSeguimiento ? 'Abrir seguimiento' : '—';
                a.href        = d.urlSeguimiento || '#';
                new bootstrap.Modal(document.getElementById('verModal')).show();
            });
        });

        // Editar
        document.querySelectorAll('.btn-editar').forEach(b=>{
            b.addEventListener('click', async ()=>{
                const id = b.dataset.id;
                const r  = await fetch(`${base}/${id}/json`);
                if(!r.ok) return alert('No se pudo cargar el envío');
                const d  = await r.json();

                document.getElementById('e-embarqueId').value      = d.embarqueId ?? '';
                document.getElementById('e-transportistaId').value = d.transportistaId ?? '';
                document.getElementById('e-numeroGuia').value      = d.numeroGuia ?? '';
                document.getElementById('e-urlSeguimiento').value  = d.urlSeguimiento ?? '';
                document.getElementById('e-fechaSalida').value     = (d.fechaSalida ?? '').substring(0,10);
                document.getElementById('e-estado').value          = d.estado ?? '';

                const f = document.getElementById('formEditar');
                f.action = `${base}/${id}/editar`;
                new bootstrap.Modal(document.getElementById('editarModal')).show();
            });
        });

        // Focus al abrir "Agregar"
        document.getElementById('agregarModal')?.addEventListener('shown.bs.modal', ()=>{
            document.querySelector('#formAgregar select[name="transportistaId"]')?.focus();
        });
    })();
</script>
<?= $this->endSection() ?>
