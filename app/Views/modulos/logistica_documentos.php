<?= $this->extend('layouts/main') ?>

<?php
$docs      = $docs ?? [];
$embarques = $embarques ?? [];
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .btn-icon{ padding:.25rem .45rem; border-width:2px; }
    div.dt-buttons{
        display:flex !important; gap:.65rem; flex-wrap:wrap;
        background:transparent !important; border:0 !important;
        border-radius:0 !important; box-shadow:none !important; padding:0 !important;
    }
    div.dt-buttons > .btn:not(:first-child){ margin-left:0 !important; }
    div.dt-buttons > .btn{ border-radius:.65rem !important; padding:.45rem 1rem !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- (MIGAS DE PAN ELIMINADAS) -->

<!-- Encabezado con acciones -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Documentos de Embarque</h1>
        <span class="badge bg-secondary">Docs</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('modulo3/embarque/manual') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Agregar (manualmente)
        </a>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#generarModal">
            <i class="bi bi-files me-1"></i> Agregar doc
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#agregarModal">
            <i class="bi bi-plus-circle me-1"></i> Agregar
        </button>
    </div>
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

<!-- MODAL: Generación rápida -->
<div class="modal fade" id="generarModal" tabindex="-1" aria-labelledby="generarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generarModalLabel">Generación de documentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuick" method="post" action="<?= site_url('modulo3/documentos/crear') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Embarque</label>
                        <select class="form-select" name="embarqueId" required>
                            <option value="">— Selecciona —</option>
                            <?php foreach($embarques as $e): ?>
                                <option value="<?= (int)$e['id'] ?>"><?= esc($e['folio'] ?? ('ID '.$e['id'])) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button name="tipo" value="Factura"      class="btn btn-outline-primary" type="submit">Factura de envío</button>
                        <button name="tipo" value="Packing List" class="btn btn-outline-primary" type="submit">Lista de empaque</button>
                        <button name="tipo" value="Etiqueta"     class="btn btn-outline-primary" type="submit">Etiqueta</button>
                        <button name="tipo" value="Aduanas"      class="btn btn-outline-primary" type="submit">Aduanas</button>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
            </form>
        </div></div>
</div>

<!-- MODAL: Agregar -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarModalLabel">Agregar documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAgregar" method="post" action="<?= site_url('modulo3/documentos/crear') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Embarque</label>
                        <select class="form-select" name="embarqueId" required>
                            <option value="">— Selecciona —</option>
                            <?php foreach($embarques as $e): ?>
                                <option value="<?= (int)$e['id'] ?>"><?= esc($e['folio'] ?? ('ID '.$e['id'])) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">— Selecciona —</option>
                                <option>Factura</option>
                                <option>Packing List</option>
                                <option>Etiqueta</option>
                                <option>Aduanas</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Número</label>
                            <input name="numero" class="form-control" placeholder="FAC-2025-0001">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option>Emitida</option>
                                <option>Borrador</option>
                                <option>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">URL PDF (opcional)</label>
                        <input name="urlPdf" class="form-control" placeholder="https://...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Archivo PDF (ruta en /writable/uploads)</label>
                        <input name="archivoPdf" class="form-control" placeholder="facturas/FAC-2025-0001.pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div></div>
</div>

<!-- MODAL: Ver -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verModalLabel">Detalle de documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-4">Tipo</dt><dd class="col-8" id="v-tipo">-</dd>
                    <dt class="col-4">Número</dt><dd class="col-8" id="v-numero">-</dd>
                    <dt class="col-4">Fecha</dt><dd class="col-8" id="v-fecha">-</dd>
                    <dt class="col-4">Estado</dt><dd class="col-8" id="v-estado">-</dd>
                    <dt class="col-4">Embarque</dt><dd class="col-8" id="v-embarque">-</dd>
                    <dt class="col-4">PDF</dt>
                    <dd class="col-8" id="v-pdf">
                        <a id="v-pdf-a" href="#" target="_blank" rel="noopener">—</a>
                    </dd>
                </dl>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div></div>
</div>

<!-- MODAL: Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditar" method="post" action="#">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Embarque</label>
                        <select class="form-select" name="embarqueId" id="e-embarqueId">
                            <option value="">— Selecciona —</option>
                            <?php foreach($embarques as $e): ?>
                                <option value="<?= (int)$e['id'] ?>"><?= esc($e['folio'] ?? ('ID '.$e['id'])) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" id="e-tipo">
                                <option>Factura</option>
                                <option>Packing List</option>
                                <option>Etiqueta</option>
                                <option>Aduanas</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Número</label>
                            <input name="numero" id="e-numero" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" id="e-fecha">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" id="e-estado">
                                <option>Emitida</option>
                                <option>Borrador</option>
                                <option>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">URL PDF</label>
                        <input name="urlPdf" id="e-urlPdf" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Archivo PDF</label>
                        <input name="archivoPdf" id="e-archivoPdf" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div></div>
</div>

<!-- Tabla -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Documentos generados</strong></div>
    <div class="card-body p-0">
        <table id="tablaDocs" class="table table-striped table-bordered m-0 align-middle">
            <thead class="table-primary">
            <tr>
                <th class="text-center">Tipo</th>
                <th class="text-center">Número</th>
                <th class="text-center">Fecha</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Embarque</th>
                <th class="text-center">PDF</th>
                <th class="text-center" style="width:220px;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($docs as $d): ?>
                <?php
                $id    = (int)($d['id'] ?? 0);
                $url   = trim($d['urlPdf'] ?? '');
                $arch  = trim($d['archivoPdf'] ?? '');
                $href  = $url ? $url : ($arch ? site_url('modulo3/documentos/'.$id.'/pdf') : '#');
                $hasPdf = $url || $arch;
                ?>
                <tr>
                    <td class="text-center"><?= esc($d['tipo'] ?? '') ?></td>
                    <td class="text-center"><?= esc($d['numero'] ?? '') ?></td>
                    <td class="text-center"><?= esc($d['fecha'] ?? '') ?></td>
                    <td class="text-center">
              <span class="badge <?= ($d['estado'] ?? '')==='Emitida' ? 'bg-success' : 'bg-secondary' ?>">
                  <?= esc($d['estado'] ?? '—') ?>
              </span>
                    </td>
                    <td class="text-center"><?= esc($d['embarqueFolio'] ?? '') ?></td>
                    <td class="text-center">
                        <?php if ($hasPdf): ?>
                            <a href="<?= $href ?>" target="_blank" rel="noopener">Abrir PDF</a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-info btn-sm btn-icon btn-ver" data-id="<?= $id ?>" title="Ver">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm btn-icon btn-editar" data-id="<?= $id ?>" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a class="btn btn-outline-secondary btn-sm btn-icon <?= $hasPdf ? '' : 'disabled' ?>" title="Descargar/Ver PDF"
                               href="<?= $hasPdf ? $href : '#' ?>" <?= $hasPdf ? 'target="_blank" rel="noopener"' : '' ?>>
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <form method="post" action="<?= site_url('modulo3/documentos/'.$id.'/eliminar') ?>"
                                  class="d-inline ms-1" onsubmit="return confirm('¿Eliminar documento?');">
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
        const base = "<?= site_url('modulo3/documentos') ?>";
        $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons';

        const langES = {
            sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_ registros",
            sZeroRecords:"No se encontraron resultados", sEmptyTable:"Ningún dato disponible",
            sInfo:"Filas: _TOTAL_", sInfoEmpty:"Filas: 0", sInfoFiltered:"(de _MAX_)",
            sSearch:"Buscar:", sLoadingRecords:"Cargando...",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
        };

        $('#tablaDocs').DataTable({
            language: langES,
            dom: "<'row px-3 pt-3'<'col-sm-6'B><'col-sm-6'f>>t<'row p-3'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [
                { extend:'copy',  text:'Copy',  className:'btn btn-secondary' },
                { extend:'csv',   text:'CSV',   className:'btn btn-secondary' },
                { extend:'excel', text:'Excel', className:'btn btn-secondary' },
                { extend:'pdf',   text:'PDF',   className:'btn btn-secondary' },
                { extend:'print', text:'Print', className:'btn btn-secondary' }
            ],
            pageLength: 10,
            columnDefs: [{ targets: -1, orderable:false, searchable:false }]
        });

        // Ver
        document.querySelectorAll('.btn-ver').forEach(btn=>{
            btn.addEventListener('click', async ()=>{
                const id = btn.dataset.id;
                const r  = await fetch(`${base}/${id}/json`);
                if(!r.ok) return alert('No se pudo cargar el documento');
                const d  = await r.json();
                document.getElementById('v-tipo').textContent     = d.tipo ?? '—';
                document.getElementById('v-numero').textContent   = d.numero ?? '—';
                document.getElementById('v-fecha').textContent    = d.fecha ?? '—';
                document.getElementById('v-estado').textContent   = d.estado ?? '—';
                document.getElementById('v-embarque').textContent = d.embarqueFolio ?? '—';
                const a   = document.getElementById('v-pdf-a');
                const href = d.urlPdf ? d.urlPdf : (d.archivoPdf ? `${base}/${id}/pdf` : '#');
                a.textContent = (d.urlPdf || d.archivoPdf) ? 'Abrir PDF' : '—';
                a.href        = href;
                if (href !== '#') { a.setAttribute('target','_blank'); a.setAttribute('rel','noopener'); }
                new bootstrap.Modal(document.getElementById('verModal')).show();
            });
        });

        // Editar
        document.querySelectorAll('.btn-editar').forEach(btn=>{
            btn.addEventListener('click', async ()=>{
                const id = btn.dataset.id;
                const r  = await fetch(`${base}/${id}/json`);
                if(!r.ok) return alert('No se pudo cargar el documento');
                const d  = await r.json();
                document.getElementById('e-embarqueId').value = d.embarqueId ?? '';
                document.getElementById('e-tipo').value       = d.tipo ?? 'Factura';
                document.getElementById('e-numero').value     = d.numero ?? '';
                document.getElementById('e-fecha').value      = (d.fecha ?? '').substring(0,10);
                document.getElementById('e-estado').value     = d.estado ?? 'Emitida';
                document.getElementById('e-urlPdf').value     = d.urlPdf ?? '';
                document.getElementById('e-archivoPdf').value = d.archivoPdf ?? '';
                document.getElementById('formEditar').action  = `${base}/${id}/editar`;
                new bootstrap.Modal(document.getElementById('editarModal')).show();
            });
        });

        // Focus al abrir modales de alta
        document.getElementById('agregarModal')?.addEventListener('shown.bs.modal', ()=>{
            document.querySelector('#formAgregar select[name="embarqueId"]')?.focus();
        });
        document.getElementById('generarModal')?.addEventListener('shown.bs.modal', ()=>{
            document.querySelector('#generarModal select[name="embarqueId"]')?.focus();
        });
    })();
</script>
<?= $this->endSection() ?>
