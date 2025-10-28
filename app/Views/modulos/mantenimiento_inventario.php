<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Botones de exportación separados */
    .dt-buttons.btn-group .btn{ margin-right:.5rem;border-radius:.375rem!important }
    .dt-buttons.btn-group .btn:last-child{ margin-right:0 }

    /* Acciones centradas */
    #tablaMaquinaria.tabla-acciones-centradas th:last-child,
    #tablaMaquinaria.tabla-acciones-centradas td:last-child{ text-align:center!important;white-space:nowrap }

    /* Botones de acciones como pastillas separadas */
    #tablaMaquinaria.tabla-acciones-centradas td:last-child .acciones-wrap{
        display:inline-flex;align-items:center;gap:.5rem
    }
    #tablaMaquinaria.tabla-acciones-centradas td:last-child .acciones-wrap .btn{
        padding:.25rem .45rem;border-radius:.5rem;line-height:1
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Encabezado -->
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
    <div class="alert alert-success mb-3 d-none" id="flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3 d-none" id="flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ================= Modal: Alta con Catálogo / Manual ================= -->
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

                    <!-- Toggle de modo -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="btn-group" role="group" aria-label="Modo de captura">
                            <button type="button" class="btn btn-outline-primary active" id="btnModoCatalogo">
                                Seleccionar de BD
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnModoManual">
                                Agregar nuevo equipo
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Código con consecutivo sugerido -->
                        <div class="col-md-4">
                            <label for="codigo" class="form-label fw-semibold text-dark">Código</label>
                            <div class="input-group">
                                <input id="codigo" name="codigo" class="form-control"
                                       value="<?= esc(old('codigo') ?: ($sigCodigo ?? 'MC-0001')) ?>"
                                       placeholder="MC-0007">
                                <button class="btn btn-outline-secondary" type="button" id="btnAutonum" title="Generar siguiente">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            <div class="form-text">Puedes modificarlo; si lo dejas vacío, se asigna el siguiente disponible.</div>
                        </div>

                        <!-- ======== MODO CATÁLOGO (SPINNERS) ======== -->
                        <div id="secCatalogo" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Modelo</label>
                                <select id="modeloSelect" name="modelo" class="form-select">
                                    <option value="">— Selecciona —</option>
                                    <?php foreach (($modelos ?? []) as $opt): ?>
                                        <option value="<?= esc($opt,'attr') ?>" <?= (old('modelo')===$opt?'selected':'') ?>>
                                            <?= esc($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Fabricante</label>
                                <select id="fabricanteSelect" name="fabricante" class="form-select">
                                    <option value="">— Selecciona —</option>
                                    <?php foreach (($fabricantes ?? []) as $opt): ?>
                                        <option value="<?= esc($opt,'attr') ?>" <?= (old('fabricante')===$opt?'selected':'') ?>>
                                            <?= esc($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Ubicación</label>
                                <select id="ubicacionSelect" name="ubicacion" class="form-select" <?= empty($ubicaciones)?'disabled':'' ?>>
                                    <option value="">— Selecciona —</option>
                                    <?php foreach (($ubicaciones ?? []) as $opt): ?>
                                        <option value="<?= esc($opt,'attr') ?>" <?= (old('ubicacion')===$opt?'selected':'') ?>>
                                            <?= esc($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Serie</label>
                                <select id="serieSelect" name="serie" class="form-select" <?= empty($series)?'disabled':'' ?>>
                                    <option value="">— Selecciona —</option>
                                    <?php foreach (($series ?? []) as $opt): ?>
                                        <option value="<?= esc($opt,'attr') ?>" <?= (old('serie')===$opt?'selected':'') ?>>
                                            <?= esc($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Si la serie ya existe en la BD, selecciónala aquí.</div>
                            </div>
                        </div>

                        <!-- ======== MODO MANUAL (CAMPOS TEXTO) ======== -->
                        <div id="secManual" class="row g-3 d-none">
                            <div class="col-md-4">
                                <label for="modeloInput" class="form-label fw-semibold text-dark">Modelo</label>
                                <input id="modeloInput" name="modelo" class="form-control"
                                       value="<?= esc(old('modelo')) ?>" placeholder="Juki DDL-8700" disabled>
                            </div>

                            <div class="col-md-4">
                                <label for="fabricanteInput" class="form-label fw-semibold text-dark">Fabricante</label>
                                <input id="fabricanteInput" name="fabricante" class="form-control"
                                       value="<?= esc(old('fabricante')) ?>" placeholder="Juki / Brother / Siruba ..." disabled>
                            </div>

                            <div class="col-md-4">
                                <label for="ubicacionInput" class="form-label fw-semibold text-dark">Ubicación</label>
                                <input id="ubicacionInput" name="ubicacion" class="form-control"
                                       value="<?= esc(old('ubicacion')) ?>" placeholder="Línea 2" disabled>
                            </div>

                            <div class="col-md-4">
                                <label for="serieInput" class="form-label fw-semibold text-dark">Serie</label>
                                <input id="serieInput" name="serie" class="form-control"
                                       value="<?= esc(old('serie')) ?>" placeholder="SER12345" disabled>
                            </div>
                        </div>

                        <!-- Comunes -->
                        <div class="col-md-4">
                            <label for="fechaCompra" class="form-label fw-semibold text-dark">Fecha de compra</label>
                            <input id="fechaCompra" type="date" name="fechaCompra" class="form-control"
                                   value="<?= esc(old('fechaCompra')) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="activa" class="form-label fw-semibold text-dark">Estado</label>
                            <?php $optEstado = old('activa') ?: 'Operativa'; ?>
                            <select id="activa" name="activa" class="form-select">
                                <option value="Operativa"     <?= $optEstado==='Operativa'?'selected':'' ?>>Operativa</option>
                                <option value="En reparación" <?= $optEstado==='En reparación'?'selected':'' ?>>En reparación</option>
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

<!-- ================= Modal: Ver ================= -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="verModalLabel">Detalles de la máquina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Código</dt>     <dd class="col-sm-9" id="v-codigo">-</dd>
                    <dt class="col-sm-3">Modelo</dt>     <dd class="col-sm-9" id="v-modelo">-</dd>
                    <dt class="col-sm-3">Fabricante</dt> <dd class="col-sm-9" id="v-fabricante">-</dd>
                    <dt class="col-sm-3">Serie</dt>      <dd class="col-sm-9" id="v-serie">-</dd>
                    <dt class="col-sm-3">Compra</dt>     <dd class="col-sm-9" id="v-compra">-</dd>
                    <dt class="col-sm-3">Ubicación</dt>  <dd class="col-sm-9" id="v-ubicacion">-</dd>
                    <dt class="col-sm-3">Estado</dt>     <dd class="col-sm-9" id="v-estado"><span class="badge bg-success">Operativa</span></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= Modal: Editar (MEDIO) ================= -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="editModalLabel">Editar máquina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="frmEdit" method="post" action="#">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="e-id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" id="e-codigo" placeholder="MC-0001">
                            <div class="form-text">Si lo dejas vacío, se asignará el siguiente disponible.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo" id="e-modelo" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fabricante</label>
                            <input type="text" class="form-control" name="fabricante" id="e-fabricante">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Serie</label>
                            <input type="text" class="form-control" name="serie" id="e-serie">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fecha de compra</label>
                            <input type="date" class="form-control" name="fechaCompra" id="e-compra">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion" id="e-ubicacion">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="activa" id="e-estado">
                                <option value="1">Operativa</option>
                                <option value="0">En reparación</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="button" id="btnEditSubmit">
                        <i class="bi bi-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= Tabla ================= -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Listado</strong></div>
    <div class="card-body table-responsive">
        <table id="tablaMaquinaria" class="table table-striped align-middle mb-0 tabla-acciones-centradas">
            <thead class="table-primary">
            <tr>
                <th>Código</th>
                <th>Modelo</th>
                <th>Fabricante</th>
                <th>Serie</th>
                <th>Compra</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($maq) && is_array($maq)): ?>
                <?php foreach ($maq as $m): ?>
                    <?php
                    $compra = '';
                    if (!empty($m['compra'])) {
                        $ts = strtotime($m['compra']); if ($ts) { $compra = date('Y-m-d', $ts); }
                    }
                    $estado     = $m['estado'] ?? 'Operativa';
                    $badgeClass = ($estado === 'Operativa') ? 'bg-success' : 'bg-warning text-dark';

                    $id         = $m['id'] ?? null;
                    $codigo     = $m['cod'] ?? '';
                    $modelo     = $m['modelo'] ?? '';
                    $fabricante = $m['fabricante'] ?? '';
                    $serie      = $m['serie'] ?? '';
                    $ubicacion  = $m['ubic'] ?? '';
                    ?>
                    <tr>
                        <td><?= esc($codigo) ?></td>
                        <td><?= esc($modelo) ?></td>
                        <td><?= esc($fabricante) ?></td>
                        <td><?= esc($serie) ?></td>
                        <td><?= esc($compra) ?></td>
                        <td><?= esc($ubicacion) ?></td>
                        <td><span class="badge <?= esc($badgeClass,'attr') ?>"><?= esc($estado) ?></span></td>
                        <td>
                            <div class="acciones-wrap">
                                <!-- Ver -->
                                <button type="button" class="btn btn-sm btn-outline-info btn-ver" title="Ver"
                                        data-bs-toggle="modal" data-bs-target="#verModal"
                                        data-id="<?= esc($id) ?>"
                                        data-codigo="<?= esc($codigo, 'attr') ?>"
                                        data-modelo="<?= esc($modelo, 'attr') ?>"
                                        data-fabricante="<?= esc($fabricante, 'attr') ?>"
                                        data-serie="<?= esc($serie, 'attr') ?>"
                                        data-compra="<?= esc($compra, 'attr') ?>"
                                        data-ubicacion="<?= esc($ubicacion, 'attr') ?>"
                                        data-estado="<?= esc($estado, 'attr') ?>">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Editar (modal medio en la misma vista) -->
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-open" title="Editar"
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?= esc($id) ?>"
                                        data-codigo="<?= esc($codigo, 'attr') ?>"
                                        data-modelo="<?= esc($modelo, 'attr') ?>"
                                        data-fabricante="<?= esc($fabricante, 'attr') ?>"
                                        data-serie="<?= esc($serie, 'attr') ?>"
                                        data-compra="<?= esc($compra, 'attr') ?>"
                                        data-ubicacion="<?= esc($ubicacion, 'attr') ?>"
                                        data-activa="<?= ($estado==='Operativa'?1:0) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <!-- Eliminar -->
                                <form action="<?= $id ? base_url('modulo3/maquinaria/eliminar/'.$id) : '#' ?>" method="post" class="d-inline frm-del">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-del" title="Eliminar"
                                            <?= $id ? '' : 'disabled' ?> data-name="<?= esc($codigo) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Contenedor de botones de exportación
    $.fn.dataTable.Buttons.defaults.dom.container.className =
        'dt-buttons d-inline-flex flex-wrap gap-2';

    (function () {
        // ===== Prefill fecha y establecer modo por defecto =====
        const addModal = document.getElementById('maqModal');
        function setModo(modo){
            const btnCat = document.getElementById('btnModoCatalogo');
            const btnMan = document.getElementById('btnModoManual');
            const secCat = document.getElementById('secCatalogo');
            const secMan = document.getElementById('secManual');

            if(modo==='catalogo'){
                btnCat.classList.add('active'); btnMan.classList.remove('active');
                secCat.classList.remove('d-none'); secMan.classList.add('d-none');
                secCat.querySelectorAll('select').forEach(el=>el.disabled=false);
                secMan.querySelectorAll('input').forEach(el=>el.disabled=true);
            }else{
                btnMan.classList.add('active'); btnCat.classList.remove('active');
                secMan.classList.remove('d-none'); secCat.classList.add('d-none');
                secMan.querySelectorAll('input').forEach(el=>el.disabled=false);
                secCat.querySelectorAll('select').forEach(el=>el.disabled=true);
            }
        }

        if (addModal) {
            addModal.addEventListener('show.bs.modal', () => {
                // Fecha
                const f = document.getElementById('fechaCompra');
                if (f && !f.value) {
                    const d=new Date(), pad=n=>String(n).padStart(2,'0');
                    f.value = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
                }
                // Modo por defecto: catálogo
                setModo('catalogo');
            });
        }

        // Toggle modo
        document.getElementById('btnModoCatalogo')?.addEventListener('click', ()=>setModo('catalogo'));
        document.getElementById('btnModoManual')?.addEventListener('click',   ()=>setModo('manual'));

        // Botón de consecutivo (deja vacío para que el backend asigne el siguiente)
        document.getElementById('btnAutonum')?.addEventListener('click', function(){
            const input = document.getElementById('codigo');
            if (input) input.value = '';
        });

        // Modal Ver → pintar datos
        const verModal = document.getElementById('verModal');
        if (verModal) {
            verModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget; if (!btn) return;
                const q = (id) => this.querySelector(id);

                q('#v-codigo').textContent     = btn.getAttribute('data-codigo')     || '-';
                q('#v-modelo').textContent     = btn.getAttribute('data-modelo')     || '-';
                q('#v-fabricante').textContent = btn.getAttribute('data-fabricante') || '-';
                q('#v-serie').textContent      = btn.getAttribute('data-serie')      || '-';
                q('#v-compra').textContent     = btn.getAttribute('data-compra')     || '-';
                q('#v-ubicacion').textContent  = btn.getAttribute('data-ubicacion')  || '-';

                const estado = btn.getAttribute('data-estado') || 'Operativa';
                const vEstado = q('#v-estado'); vEstado.innerHTML = '';
                const span = document.createElement('span');
                span.className = 'badge ' + (estado === 'Operativa' ? 'bg-success' : 'bg-warning text-dark');
                span.textContent = estado;
                vEstado.appendChild(span);
            });
        }

        // Modal Editar (MEDIO) → prefill + acción
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget; if (!btn) return;

                const id    = btn.getAttribute('data-id');
                const form  = document.getElementById('frmEdit');
                form.action = "<?= base_url('modulo3/maquinaria/actualizar') ?>/" + id;

                // Prefill
                document.getElementById('e-id').value         = id;
                document.getElementById('e-codigo').value     = btn.getAttribute('data-codigo') || '';
                document.getElementById('e-modelo').value     = btn.getAttribute('data-modelo') || '';
                document.getElementById('e-fabricante').value = btn.getAttribute('data-fabricante') || '';
                document.getElementById('e-serie').value      = btn.getAttribute('data-serie') || '';
                document.getElementById('e-compra').value     = btn.getAttribute('data-compra') || '';
                document.getElementById('e-ubicacion').value  = btn.getAttribute('data-ubicacion') || '';
                document.getElementById('e-estado').value     = btn.getAttribute('data-activa') === '1' ? '1' : '0';
            });

            // Confirmación SweetAlert antes de enviar
            document.getElementById('btnEditSubmit')?.addEventListener('click', function(){
                Swal.fire({
                    title: "¿Guardar cambios?",
                    text: "Se actualizará la información de la máquina.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Sí, guardar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('frmEdit').submit();
                    }
                });
            });
        }

        // DataTable
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
                { targets: -1, orderable:false, searchable:false, className:'text-center' } // Acciones
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

        // ===== Eliminar con SweetAlert2 (como tu ejemplo) =====
        document.querySelectorAll('.btn-del').forEach(btn=>{
            btn.addEventListener('click', function(){
                const name = this.getAttribute('data-name') || 'la máquina';
                const form = this.closest('form');
                Swal.fire({
                    title: "¿Estás seguro?",
                    text: "No podrás revertir esta acción. Se eliminará " + name + ".",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // ===== Flash messages con SweetAlert2 =====
        const ok = document.getElementById('flash-success');
        const er = document.getElementById('flash-error');
        if (ok) Swal.fire({ icon:'success', title:'Listo', text: ok.textContent.trim() });
        if (er) Swal.fire({ icon:'error',   title:'Error', text: er.textContent.trim() });
    })();
</script>
<?= $this->endSection() ?>
