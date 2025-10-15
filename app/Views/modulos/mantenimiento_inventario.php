<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Encabezado con botón Agregar al lado derecho -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Inventario de Maquinaria</h1>
        <span class="badge bg-secondary">Mantenimiento</span>
    </div>

    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#maqModal">
        <i class="bi bi-plus-circle me-1"></i> Agregar máquina
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- MODAL: Agregar / Registrar máquina (centrado) -->
<div class="modal fade" id="maqModal" tabindex="-1" aria-labelledby="maqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-dark" id="maqModalLabel">Registro de máquina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form class="row g-3" method="post" action="<?= base_url('modulo3/maquinaria/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label fw-semibold text-dark">Código</label>
                            <input id="codigo" name="codigo" class="form-control" required
                                   value="<?= esc(old('codigo')) ?>" placeholder="MC-0007">
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label fw-semibold text-dark">Modelo</label>
                            <input id="modelo" name="modelo" class="form-control" required
                                   value="<?= esc(old('modelo')) ?>" placeholder="Juki DDL-8700">
                        </div>

                        <div class="col-md-4">
                            <label for="fabricante" class="form-label fw-semibold text-dark">Fabricante</label>
                            <input id="fabricante" name="fabricante" class="form-control"
                                   value="<?= esc(old('fabricante')) ?>" placeholder="Juki / Brother / Siruba ...">
                        </div>

                        <div class="col-md-4">
                            <label for="serie" class="form-label fw-semibold text-dark">Serie</label>
                            <input id="serie" name="serie" class="form-control"
                                   value="<?= esc(old('serie')) ?>" placeholder="SER12345">
                        </div>

                        <div class="col-md-4">
                            <label for="fechaCompra" class="form-label fw-semibold text-dark">Fecha de compra</label>
                            <input id="fechaCompra" type="date" name="fechaCompra" class="form-control"
                                   value="<?= esc(old('fechaCompra')) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="ubicacion" class="form-label fw-semibold text-dark">Ubicación</label>
                            <!-- FIX: se corrigió el value mal cerrado -->
                            <input id="ubicacion" name="ubicacion" class="form-control"
                                   value="<?= esc(old('ubicacion')) ?>" placeholder="Línea 2">
                        </div>

                        <div class="col-md-4">
                            <label for="activa" class="form-label fw-semibold text-dark">Estado</label>
                            <select id="activa" name="activa" class="form-select">
                                <?php $opt = old('activa') ?: 'Operativa'; ?>
                                <option value="Operativa"     <?= $opt==='Operativa'?'selected':'' ?>>Operativa</option>
                                <option value="En reparación" <?= $opt==='En reparación'?'selected':'' ?>>En reparación</option>
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

<!-- MODAL: Ver / Más información (solo lectura, sin botón Editar) -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="verModalLabel">Detalles de la máquina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Código</dt>       <dd class="col-sm-9" id="v-codigo">-</dd>
                    <dt class="col-sm-3">Modelo</dt>       <dd class="col-sm-9" id="v-modelo">-</dd>
                    <dt class="col-sm-3">Fabricante</dt>   <dd class="col-sm-9" id="v-fabricante">-</dd>
                    <dt class="col-sm-3">Serie</dt>        <dd class="col-sm-9" id="v-serie">-</dd>
                    <dt class="col-sm-3">Compra</dt>       <dd class="col-sm-9" id="v-compra">-</dd>
                    <dt class="col-sm-3">Ubicación</dt>    <dd class="col-sm-9" id="v-ubicacion">-</dd>
                    <dt class="col-sm-3">Estado</dt>       <dd class="col-sm-9" id="v-estado"><span class="badge bg-success">Operativa</span></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Listado</strong></div>
    <div class="card-body table-responsive">
        <table id="tablaMaquinaria" class="table table-striped align-middle mb-0">
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
                    $compra = '';
                    if (!empty($m['compra'])) {
                        $ts = strtotime($m['compra']);
                        if ($ts) { $compra = date('Y-m-d', $ts); }
                    }
                    $estado       = $m['estado'] ?? 'Operativa';
                    $esOperativa  = ($estado === 'Operativa');
                    $badgeClass   = $esOperativa ? 'bg-success' : 'bg-warning text-dark';

                    $id         = $m['id']        ?? null;
                    $codigo     = $m['cod']       ?? '';
                    $modelo     = $m['modelo']    ?? '';
                    $fabricante = $m['fabricante']?? '';
                    $serie      = $m['serie']     ?? '';
                    $ubicacion  = $m['ubic']      ?? '';
                    ?>
                    <tr>
                        <td><?= esc($codigo) ?></td>
                        <td><?= esc($modelo) ?></td>
                        <td><?= esc($fabricante) ?></td>
                        <td><?= esc($serie) ?></td>
                        <td><?= esc($compra) ?></td>
                        <td><?= esc($ubicacion) ?></td>
                        <td><span class="badge <?= esc($badgeClass,'attr') ?>"><?= esc($estado) ?></span></td>
                        <td class="text-end">
                            <div class="btn-group" role="group" aria-label="Acciones">
                                <!-- Ver -->
                                <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#verModal"
                                        data-id="<?= esc($id) ?>"
                                        data-codigo="<?= esc($codigo, 'attr') ?>"
                                        data-modelo="<?= esc($modelo, 'attr') ?>"
                                        data-fabricante="<?= esc($fabricante, 'attr') ?>"
                                        data-serie="<?= esc($serie, 'attr') ?>"
                                        data-compra="<?= esc($compra, 'attr') ?>"
                                        data-ubicacion="<?= esc($ubicacion, 'attr') ?>"
                                        data-estado="<?= esc($estado, 'attr') ?>"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                </button>

                                <!-- Editar -->
                                <a class="btn btn-sm btn-outline-primary <?= $id ? '' : 'disabled' ?>"
                                   href="<?= $id ? base_url('modulo3/maquinaria/editar/'.$id) : 'javascript:void(0)' ?>">
                                    <i class="bi bi-pencil me-1"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No hay máquinas registradas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons (exportación) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- ===== Separación precisa de botones (global) ===== -->
<script>
    // Evita 'btn-group' y usa utilidades de Bootstrap con gap
    $.fn.dataTable.Buttons.defaults.dom.container.className =
        'dt-buttons d-inline-flex flex-wrap gap-2';
</script>

<script>
    (function () {
        // Rellena fecha por defecto al abrir el modal de "Agregar"
        const addModal = document.getElementById('maqModal');
        if (addModal) {
            addModal.addEventListener('show.bs.modal', () => {
                const input = document.getElementById('fechaCompra');
                if (input && !input.value) {
                    const d = new Date(), pad = n => String(n).padStart(2,'0');
                    input.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
                }
            });
        }

        // Pinta datos en el modal de "Ver"
        const verModal = document.getElementById('verModal');
        if (verModal) {
            verModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;

                const q = (id) => this.querySelector(id);

                q('#v-codigo').textContent     = btn.getAttribute('data-codigo')     || '-';
                q('#v-modelo').textContent     = btn.getAttribute('data-modelo')     || '-';
                q('#v-fabricante').textContent = btn.getAttribute('data-fabricante') || '-';
                q('#v-serie').textContent      = btn.getAttribute('data-serie')      || '-';
                q('#v-compra').textContent     = btn.getAttribute('data-compra')     || '-';
                q('#v-ubicacion').textContent  = btn.getAttribute('data-ubicacion')  || '-';

                const estado = btn.getAttribute('data-estado') || 'Operativa';
                const vEstado = q('#v-estado');
                vEstado.innerHTML = '';
                const span = document.createElement('span');
                span.className = 'badge ' + (estado === 'Operativa' ? 'bg-success' : 'bg-warning text-dark');
                span.textContent = estado;
                vEstado.appendChild(span);
            });
        }

        // ===== DataTable de maquinaria con botones separados =====
        const langES = {
            sProcessing:"Procesando...",
            sLengthMenu:"Mostrar _MENU_ registros",
            sZeroRecords:"No se encontraron resultados",
            sEmptyTable:"Sin datos",
            sInfo:"Mostrando _START_–_END_ de _TOTAL_",
            sInfoEmpty:"Mostrando 0–0 de 0",
            sInfoFiltered:"(filtrado de _MAX_)",
            sSearch:"Buscar:",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copiar" }
        };

        const hoy = new Date().toISOString().slice(0,10);

        $('#tablaMaquinaria').DataTable({
            language: langES,
            columnDefs: [
                { targets: -1, orderable:false, searchable:false } // Acciones
            ],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copy',  text:'Copy',  exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'csv',   text:'CSV',   filename:'maquinaria_'+hoy, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'excel', text:'Excel', filename:'maquinaria_'+hoy, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'pdf',   text:'PDF',   filename:'maquinaria_'+hoy, title:'Inventario de Maquinaria',
                    orientation:'landscape', pageSize:'A4', exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'print', text:'Print', exportOptions:{ columns: ':not(:last-child)' } }
            ]
        });
    })();
</script>
<?= $this->endSection() ?>
