<?php
// app/Views/modulo3/logistica_preparacion.php
?>

<?= $this->extend('layouts/main') ?>

<?php
$embarque = $embarque ?? [];
$clientes = $clientes ?? [];
$ordenes  = $ordenes  ?? [];
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .btn-icon{ padding:.25rem .45rem; border-width:2px; }

    /* Botonera tipo pastillas separadas */
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

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Preparación de Envíos</h1>
        <span class="badge bg-primary">Packing</span>
    </div>
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#embarqueModal">
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

<!-- ===== RESUMEN EMBARQUE ACTUAL ===== -->
<?php if (! empty($embarque['id'])): ?>
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <strong>Embarque abierto #<?= (int)$embarque['id'] ?></strong>
        </div>
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Folio</small>
                    <span><?= esc($embarque['folio'] ?? '-') ?></span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Destino</small>
                    <span>
                        <?php
                        $nombreCliente = '';
                        if (!empty($embarque['clienteId']) && !empty($clientes)) {
                            foreach ($clientes as $cli) {
                                if ((int)$cli['id'] === (int)$embarque['clienteId']) {
                                    $nombreCliente = $cli['nombre'];
                                    break;
                                }
                            }
                        }
                        echo esc($nombreCliente ?: 'Sin cliente asignado');
                        ?>
                    </span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Cajas</small>
                    <span><?= esc($embarque['cajas'] ?? 0) ?></span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Peso total (kg)</small>
                    <span><?= esc($embarque['pesoTotal'] ?? 0) ?></span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Volumen (m³)</small>
                    <span><?= esc($embarque['volumen'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        No hay embarque abierto. Usa el botón <strong>Agregar</strong> para crear uno y poder asignar pedidos.
    </div>
<?php endif; ?>

<!-- ===== MODAL: Crear/usar embarque ===== -->
<div class="modal fade" id="embarqueModal" tabindex="-1" aria-labelledby="embarqueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="embarqueModalLabel">Generar lista de empaque / etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEmbarque" class="row g-3" method="post" action="<?= site_url('modulo3/embarques/crear') ?>">
                    <?= csrf_field() ?>
                    <div class="col-md-6">
                        <label class="form-label">Pedido (Folio de embarque)</label>
                        <input type="text" name="folio" class="form-control" placeholder="PED-2025-0045"
                               value="<?= esc($embarque['folio'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Destino (Cliente)</label>
                        <select name="clienteId" class="form-select">
                            <option value="">— Selecciona —</option>
                            <?php foreach ($clientes as $cli): ?>
                                <option value="<?= (int)$cli['id'] ?>"
                                        <?= isset($embarque['clienteId']) && (int)$embarque['clienteId'] === (int)$cli['id'] ? 'selected' : '' ?>>
                                    <?= esc($cli['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cajas</label>
                        <input type="number" name="cajas" class="form-control" placeholder="5"
                               value="<?= esc($embarque['cajas'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Peso total (kg)</label>
                        <input type="number" name="pesoTotal" step="0.01" class="form-control" placeholder="48"
                               value="<?= esc($embarque['pesoTotal'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Volumen (m³)</label>
                        <input type="number" name="volumen" step="0.01" class="form-control" placeholder="0.9"
                               value="<?= esc($embarque['volumen'] ?? '') ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <?php if (!empty($embarque['id'])): ?>
                    <a class="btn btn-outline-secondary" href="<?= site_url('modulo3/embarques/'.$embarque['id'].'/packing-list') ?>">Generar Packing List</a>
                    <a class="btn btn-outline-secondary" href="<?= site_url('modulo3/embarques/'.$embarque['id'].'/etiquetas') ?>">Etiquetas</a>
                <?php else: ?>
                    <button class="btn btn-outline-secondary" type="button" disabled>Generar Packing List</button>
                    <button class="btn btn-outline-secondary" type="button" disabled>Etiquetas</button>
                <?php endif; ?>
                <button type="submit" form="formEmbarque" class="btn btn-primary">Generar / Usar embarque</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Ver ===== -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verModalLabel">Detalle de pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Folio</dt><dd class="col-sm-8" id="v-folio">-</dd>
                    <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8" id="v-fecha">-</dd>
                    <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8" id="v-cliente">-</dd>
                    <dt class="col-sm-4">Estatus</dt><dd class="col-sm-8" id="v-estatus">-</dd>
                    <dt class="col-sm-4">Moneda</dt><dd class="col-sm-8" id="v-moneda">-</dd>
                    <dt class="col-sm-4">Total</dt><dd class="col-sm-8" id="v-total">-</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Editar ===== -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditar" method="post" action="#">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Folio</label>
                        <input type="text" class="form-control" name="folio" id="e-folio">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" id="e-fecha">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Destino (Cliente)</label>
                            <select class="form-select" name="clienteId" id="e-clienteId">
                                <option value="">— Selecciona —</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= (int)$cli['id'] ?>"><?= esc($cli['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="estatus" id="e-estatus">
                                <option value="abierto">abierto</option>
                                <option value="aprobada">aprobada</option>
                                <option value="cerrado">cerrado</option>
                                <option value="cancelada">cancelada</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">OP</label>
                            <input type="text" class="form-control" name="op" id="e-op" placeholder="OP-0011">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Cajas</label>
                            <input type="number" class="form-control" name="cajas" id="e-cajas" min="0" step="1">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" class="form-control" name="peso" id="e-peso" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Moneda</label>
                            <input type="text" class="form-control" name="moneda" id="e-moneda" placeholder="MXN">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Total</label>
                            <input type="number" step="0.01" class="form-control" name="total" id="e-total">
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
    <div class="card-header"><strong>Consolidación de pedidos</strong></div>
    <div class="card-body p-0">
        <table id="tablaConsolida" class="table table-striped table-bordered m-0 align-middle">
            <thead class="table-primary">
            <tr>
                <th class="text-center">Pedido</th>
                <th class="text-center">OP</th>
                <th class="text-center">Cajas</th>
                <th class="text-center">Peso</th>
                <th class="text-center">Destino</th>
                <th class="text-center" style="width:190px;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($ordenes)): ?>
                <tr><td colspan="6" class="text-center text-muted">No hay pedidos pendientes</td></tr>
            <?php else: foreach ($ordenes as $r): ?>
                <tr>
                    <td><?= esc($r['pedido']) ?></td>
                    <td><?= esc($r['op'] ?? '') ?></td>
                    <td><?= esc($r['cajas'] ?? '') ?></td>
                    <td><?= esc($r['peso'] ?? '') ?></td>
                    <td><?= esc($r['clienteNombre'] ?? '-') ?></td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm btn-icon btn-ver" data-id="<?= (int)$r['id'] ?>" title="Ver">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-icon btn-editar" data-id="<?= (int)$r['id'] ?>" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if (!empty($embarque['id'])): ?>
                                <form method="post" action="<?= site_url('modulo3/embarques/'.$embarque['id'].'/agregar-orden') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="ordenId" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-outline-success btn-sm btn-icon" type="submit" title="Agregar al envío">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
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
        const baseOrden = "<?= site_url('modulo3/ordenes') ?>";

        // Quita el contenedor btn-group por defecto
        $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons';

        const langES = {
            sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_ registros",
            sZeroRecords:"No se encontraron resultados", sEmptyTable:"Ningún dato disponible",
            sInfo:"Filas: _TOTAL_", sInfoEmpty:"Filas: 0", sInfoFiltered:"(de _MAX_)",
            sSearch:"Buscar:", sLoadingRecords:"Cargando...",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
        };

        const tieneDatosConsolida = $('#tablaConsolida tbody tr').filter(function(){
            return !$(this).find('td[colspan]').length;
        }).length > 0;

        if (tieneDatosConsolida) {
            $('#tablaConsolida').DataTable({
                language: langES,
                dom: "<'row px-3 pt-3'<'col-sm-6'B><'col-sm-6'f>>" + "t" + "<'row p-3'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [
                    { extend: 'copy',  text: 'Copy',     className: 'btn btn-secondary' },
                    { extend: 'csv',   text: 'CSV',      className: 'btn btn-secondary' },
                    { extend: 'excel', text: 'Excel',    className: 'btn btn-secondary' },
                    { extend: 'pdf',   text: 'PDF',      className: 'btn btn-secondary' },
                    { extend: 'print', text: 'Print',    className: 'btn btn-secondary' }
                ],
                pageLength: 10,
                columnDefs: [{ targets: -1, orderable: false, searchable: false }]
            });
        }

        // Ver
        document.querySelectorAll('.btn-ver').forEach(btn=>{
            btn.addEventListener('click', async ()=>{
                const id = btn.dataset.id;
                const res = await fetch(`${baseOrden}/${id}/json`);
                if(!res.ok) return alert('No se pudo cargar el pedido');
                const d = await res.json();
                document.getElementById('v-folio').textContent   = d.folio ?? '-';
                document.getElementById('v-fecha').textContent   = d.fecha ?? '-';
                document.getElementById('v-cliente').textContent = d.cliente ?? '-';
                document.getElementById('v-estatus').textContent = d.estatus ?? '-';
                document.getElementById('v-moneda').textContent  = d.moneda ?? '-';
                document.getElementById('v-total').textContent   = d.total ?? '-';
                new bootstrap.Modal(document.getElementById('verModal')).show();
            });
        });

        // Editar
        document.querySelectorAll('.btn-editar').forEach(btn=>{
            btn.addEventListener('click', async ()=>{
                const id = btn.dataset.id;
                const res = await fetch(`${baseOrden}/${id}/json`);
                if(!res.ok) return alert('No se pudo cargar el pedido');
                const d = await res.json();

                document.getElementById('e-folio').value   = d.folio ?? '';
                document.getElementById('e-fecha').value   = (d.fecha ?? '').substring(0,10);
                document.getElementById('e-estatus').value = d.estatus ?? 'abierto';
                document.getElementById('e-moneda').value  = d.moneda ?? 'MXN';
                document.getElementById('e-total').value   = d.total ?? '';

                document.getElementById('e-clienteId').value = d.clienteId ?? '';
                document.getElementById('e-op').value        = d.op ?? '';
                document.getElementById('e-cajas').value     = d.cajas ?? '';
                document.getElementById('e-peso').value      = d.peso ?? '';

                const form = document.getElementById('formEditar');
                form.action = `${baseOrden}/${id}/editar`;
                new bootstrap.Modal(document.getElementById('editarModal')).show();
            });
        });

        // Focus en modal Agregar
        document.getElementById('embarqueModal')?.addEventListener('shown.bs.modal', ()=>{
            document.querySelector('#formEmbarque input[name="folio"]')?.focus();
        });
    })();
</script>
<?= $this->endSection() ?>
