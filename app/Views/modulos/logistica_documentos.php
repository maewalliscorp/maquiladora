<?= $this->extend('layouts/main') ?>

<?php
$docs      = $docs ?? [];
$embarques = $embarques ?? [];

/* ====== ENV ====== */
$supaUrl    = env('SUPABASE_URL')  ?? '';
$supaAnon   = env('SUPABASE_ANON') ?? ''; // clave pública (anon)

$B_DOC_EMB  = env('SUPABASE_BUCKET_DOC_EMBARQUE') ?? 'Doc_Embarque';
$B_ETIQUETA = env('SUPABASE_BUCKET_ETIQUETA')     ?? 'Etiqueta';
$B_FACT     = env('SUPABASE_BUCKET_FACTURAS')     ?? 'Facturas';
$B_PACK     = env('SUPABASE_BUCKET_PACKING')      ?? 'Packing';
$B_ADU      = env('SUPABASE_BUCKET_ADUANAS')      ?? 'Aduanas';
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

<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div class="d-flex align-items-center">
    <h1 class="me-3">Documentos de Embarque</h1>
    <span class="badge bg-secondary">Docs</span>
  </div>
  <div class="d-flex gap-2">
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
          <!-- Navega a UI de facturación -->
          <button id="btnIrFacturar" class="btn btn-outline-primary" type="button">Factura de envío</button>
          <a href="<?= site_url('modulo3/embarque/manual') ?>" class="btn btn-outline-primary">Documento de embarque</a>
          <button name="tipo" value="Etiqueta" class="btn btn-outline-primary" type="submit">Etiqueta</button>
          <button name="tipo" value="Aduanas" class="btn btn-outline-primary" type="submit">Aduanas</button>
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
            <select name="tipo" id="a-tipo" class="form-select" required>
              <option value="">— Selecciona —</option>
              <option>Factura</option>
              <option>Packing List</option>
              <option>Etiqueta</option>
              <option>Aduanas</option>
              <option>Documento Embarque</option>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Número</label>
            <input name="numero" class="form-control" placeholder="FAC-2025-0001">
          </div>
        </div>

        <!-- Lista dinámica Supabase -->
        <div class="mb-2">
          <label class="form-label">Documento guardado (Supabase)</label>
          <div class="d-flex gap-2">
            <select id="a-docListado" class="form-select" disabled>
              <option value="">— Selecciona primero un tipo —</option>
            </select>
            <button class="btn btn-outline-secondary" type="button" id="a-btnRefrescar" title="Refrescar" disabled>
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
          <div class="form-text">Al elegir un tipo, se listan los PDFs del bucket correspondiente.</div>
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

        <!-- Ocultos para llenar con Supabase -->
        <input type="hidden" name="urlPdf" id="a-urlPdf">
        <input type="hidden" name="archivoPdf" id="a-archivoPdf">
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
        <dd class="col-8" id="v-pdf"><a id="v-pdf-a" href="#" target="_blank" rel="noopener">—</a></dd>
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
              <option>Documento Embarque</option>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Número</label>
            <input name="numero" id="e-numero" class="form-control">
          </div>
        </div>

        <!-- Lista dinámica Supabase -->
        <div class="mb-2">
          <label class="form-label">Documento guardado (Supabase)</label>
          <div class="d-flex gap-2">
            <select id="e-docListado" class="form-select" disabled>
              <option value="">— Selecciona primero un tipo —</option>
            </select>
            <button class="btn btn-outline-secondary" type="button" id="e-btnRefrescar" title="Refrescar" disabled>
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
          <div class="form-text">Puedes reemplazar el PDF seleccionando uno del Storage.</div>
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

<!-- Supabase UMD -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2.45.4/dist/umd/supabase.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{

  const base = "<?= site_url('modulo3/documentos') ?>";

  // ✅ Solo toca Buttons.defaults si existe
  if (window.jQuery && $.fn.dataTable && $.fn.dataTable.Buttons && $.fn.dataTable.Buttons.defaults) {
    $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons';
  }

  const langES = {
    sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_ registros",
    sZeroRecords:"No se encontraron resultados", sEmptyTable:"Ningún dato disponible",
    sInfo:"Filas: _TOTAL_", sInfoEmpty:"Filas: 0", sInfoFiltered:"(de _MAX_)",
    sSearch:"Buscar:", sLoadingRecords:"Cargando...",
    oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
    buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
  };

  // ✅ Inicialización tolerante a ausencia de Buttons
  const dtOpts = {
    language: langES,
    dom: "<'row px-3 pt-3'<'col-sm-6'B><'col-sm-6'f>>t<'row p-3'<'col-sm-6'i><'col-sm-6'p>>",
    pageLength: 10,
    columnDefs: [{ targets: -1, orderable:false, searchable:false }]
  };
  if ($.fn.dataTable && $.fn.dataTable.Buttons) {
    dtOpts.buttons = [
      { extend:'copy',  text:'Copy',  className:'btn btn-secondary' },
      { extend:'csv',   text:'CSV',   className:'btn btn-secondary' },
      { extend:'excel', text:'Excel', className:'btn btn-secondary' },
      { extend:'pdf',   text:'PDF',   className:'btn btn-secondary' },
      { extend:'print', text:'Print', className:'btn btn-secondary' }
    ];
  } else {
    dtOpts.dom = "<'row px-3 pt-3'<'col-sm-6'f>>t<'row p-3'<'col-sm-6'i><'col-sm-6'p>>";
  }
  $('#tablaDocs').DataTable(dtOpts);

  // ====== Ver ======
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

  // ====== Editar ======
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
      cargarListaPorTipo('editar');
      new bootstrap.Modal(document.getElementById('editarModal')).show();
    });
  });

  /* ===== Supabase desde .env ===== */
  const SUPABASE_URL  = <?= json_encode($supaUrl) ?>;
  const SUPABASE_ANON = <?= json_encode($supaAnon) ?>;

  if (!SUPABASE_URL || !SUPABASE_ANON) {
    console.error('Faltan SUPABASE_URL / SUPABASE_ANON. Verifica tu .env.');
    return;
  }
  if (!window.supabase || !window.supabase.createClient) {
    console.error('Supabase UMD no cargó.');
    return;
  }

  const sb = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON);
  const tipoToBucket = {
    'Factura'            : <?= json_encode($B_FACT) ?>,
    'Packing List'       : <?= json_encode($B_PACK) ?>,
    'Etiqueta'           : <?= json_encode($B_ETIQUETA) ?>,
    'Aduanas'            : <?= json_encode($B_ADU) ?>,
    'Documento Embarque' : <?= json_encode($B_DOC_EMB) ?>,
  };

  function resetListado($select, $btn, texto) {
    $select.innerHTML = `<option value="">${texto}</option>`;
    $select.disabled = true;
    if ($btn) $btn.disabled = true;
  }

  // Lista robusta (raíz → public/ → 1 carpeta) con fallback a proxy backend
  async function cargarLista(tipo, $select, $btn) {
    const bucket = tipoToBucket[tipo];
    if (!bucket) { resetListado($select, $btn, '— Tipo sin bucket —'); return; }

    $select.disabled = true; if ($btn) $btn.disabled = true;
    $select.innerHTML = `<option value="">Cargando...</option>`;

    async function tryList(path) {
      const res = await sb.storage.from(bucket).list(path, {
        limit: 1000, sortBy: { column:'name', order:'asc' }
      });
      return res;
    }

    let entries = [];
    let { data, error } = await tryList('');

    if ((!data || data.length === 0) && !error) {
      const r2 = await tryList('public');
      if (!r2.error && r2.data?.length) data = r2.data.map(x => ({...x, _prefix:'public/'}));
    }

    if ((!data || data.length === 0) && !error) {
      const r3 = await tryList('');
      const folders = (r3.data || []).filter(x => x.id === null);
      const files   = (r3.data || []).filter(x => x.id !== null);
      if (folders.length === 1 && files.length === 0) {
        const folder = folders[0].name;
        const r4 = await tryList(folder);
        if (!r4.error && r4.data?.length) data = r4.data.map(x => ({...x, _prefix: folder + '/'}));
      }
    }

    if (!error && data?.length) entries = data;

    if (entries.length === 0) {
      try {
        const resp = await fetch("<?= site_url('modulo3/storage/list') ?>", {
          method: 'POST',
          headers: { 'Content-Type':'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ bucket, prefix: '' })
        });
        const json = await resp.json();
        if (Array.isArray(json) && json.length) {
          entries = json.map(it => ({ name: it.name, id: it.id ?? 'x', _prefix: '' })).filter(x=>!!x.name);
        }
      } catch (e) { console.error('[Proxy] error', e); }
    }

    const archivos = (entries || [])
      .filter(it => it.name && typeof it.name === 'string')
      .filter(it => it.name.toLowerCase().endsWith('.pdf'))
      .map(it => ({ name: it.name, path: (it._prefix || '') + it.name }));

    if (!archivos.length) { resetListado($select, $btn, '— Sin PDFs en bucket —'); return; }

    $select.innerHTML = `<option value="">— Selecciona un archivo —</option>`;
    archivos.forEach(f=>{
      const opt = document.createElement('option');
      opt.value = JSON.stringify({ bucket, path: f.path, name: f.name });
      opt.textContent = f.name;
      $select.appendChild(opt);
    });

    $select.disabled = false; if ($btn) $btn.disabled = false;
  }

  // Formularios (Agregar / Editar)
  const $aTipo    = document.getElementById('a-tipo');
  const $aListado = document.getElementById('a-docListado');
  const $aRef     = document.getElementById('a-btnRefrescar');
  const $aUrl     = document.getElementById('a-urlPdf');
  const $aRuta    = document.getElementById('a-archivoPdf');

  const $eTipo    = document.getElementById('e-tipo');
  const $eListado = document.getElementById('e-docListado');
  const $eRef     = document.getElementById('e-btnRefrescar');
  const $eUrl     = document.getElementById('e-urlPdf');
  const $eRuta    = document.getElementById('e-archivoPdf');

  function limpiarDestino($url,$ruta){ if($url) $url.value=''; if($ruta) $ruta.value=''; }

  async function cargarListaPorTipo(form) {
    if (form==='agregar') {
      const tipo = $aTipo?.value?.trim();
      limpiarDestino($aUrl,$aRuta);
      if (!tipo) { resetListado($aListado,$aRef,'— Selecciona primero un tipo —'); return; }
      await cargarLista(tipo,$aListado,$aRef);
    } else {
      const tipo = $eTipo?.value?.trim();
      limpiarDestino($eUrl,$eRuta);
      if (!tipo) { resetListado($eListado,$eRef,'— Selecciona primero un tipo —'); return; }
      await cargarLista(tipo,$eListado,$eRef);
    }
  }

  // Eventos Agregar
  $aTipo?.addEventListener('change', ()=>cargarListaPorTipo('agregar'));
  $aRef?.addEventListener('click', ()=>cargarListaPorTipo('agregar'));
  $aListado?.addEventListener('change', async ()=>{
    if (!$aListado.value) return;
    const sel = JSON.parse($aListado.value);
    const { data: pub } = sb.storage.from(sel.bucket).getPublicUrl(sel.path);
    if ($aUrl)  $aUrl.value  = pub?.publicUrl || '';
    if ($aRuta) $aRuta.value = sel.path;
  });

  // Eventos Editar
  $eTipo?.addEventListener('change', ()=>cargarListaPorTipo('editar'));
  $eRef?.addEventListener('click', ()=>cargarListaPorTipo('editar'));
  $eListado?.addEventListener('change', async ()=>{
    if (!$eListado.value) return;
    const sel = JSON.parse($eListado.value);
    const { data: pub } = sb.storage.from(sel.bucket).getPublicUrl(sel.path);
    if ($eUrl)  $eUrl.value  = pub?.publicUrl || '';
    if ($eRuta) $eRuta.value = sel.path;
  });

  // Ir a UI de facturación
  document.getElementById('btnIrFacturar')?.addEventListener('click', ()=>{
    const sel = document.querySelector('#generarModal select[name="embarqueId"]');
    const id = sel?.value || '';
    if (!id) { alert('Selecciona un embarque'); return; }
    window.location.href = "<?= site_url('logistica/embarque') ?>/"+id+"/facturar/ui";
  });

});
</script>
<?= $this->endSection() ?>
