<?php /** app/Views/modulos/logistica_documentos.php */ ?>
<?= $this->extend('layouts/main') ?>

<?php
$docs       = $docs       ?? [];
$embarques  = $embarques  ?? [];
$req        = \Config\Services::request(); // <- usar request fuera de helpers de View
function cur($n){ return '$'.number_format((float)$n, 2); }
?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .page-title { font-weight: 800; }
    .card-shadow { box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .chip { display:inline-block; padding:.2rem .6rem; border-radius:999px; background:#eef4ff; color:#0b5ed7; font-weight:600; }
    .table-tight th, .table-tight td { padding:.55rem .7rem; vertical-align: middle; }
    .muted { color:#6c757d; }
    .actions .btn { --bs-btn-padding-y:.25rem; --bs-btn-padding-x:.5rem; }
    @media (min-width:1200px){ .container-xl{ max-width:1240px; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-xl my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 page-title m-0">Documentos de Embarque</h1>
        <div class="d-flex gap-2">

            <!-- Botón: Agregar (manualmente) -> lleva a la vista manual -->
            <a href="<?= site_url('modulo3/embarque/manual') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Agregar (manualmente)
            </a>

            <!-- Botón: Crear registro rápido en doc_embarque (abre modal) -->
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mdlNuevoDoc">
                <i class="bi bi-file-earmark-plus"></i> Nuevo registro
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('warn')): ?>
        <div class="alert alert-warning"><?= esc(session()->getFlashdata('warn')) ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('ok')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('ok')) ?></div>
    <?php endif ?>

    <div class="card card-shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-tight align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Embarque</th>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Archivo/URL</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($docs)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Sin documentos</td></tr>
                    <?php else: foreach ($docs as $d): ?>
                        <?php
                        $id     = $d['id']              ?? null;
                        $folio  = $d['embarqueFolio']   ?? ($d['embarque'] ?? null);
                        $tipo   = $d['tipo']            ?? '';
                        $num    = $d['numero']          ?? '';
                        $fecha  = $d['fecha']           ?? '';
                        $estado = $d['estado']          ?? '';
                        $ruta   = $d['archivoRuta']     ?? '';
                        $urlPdf = $d['urlPdf']          ?? '';
                        $arch   = $d['archivoPdf']      ?? '';
                        $canDesc = !empty($urlPdf) || !empty($ruta) || !empty($arch);
                        ?>
                        <tr>
                            <td><?= esc($id) ?></td>
                            <td>
                                <?php if ($folio): ?>
                                    <span class="chip"><?= esc($folio) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($tipo ?: '—') ?></td>
                            <td class="fw-semibold"><?= esc($num ?: '—') ?></td>
                            <td class="text-nowrap"><?= esc($fecha ?: '—') ?></td>
                            <td>
                                <?php if ($estado): ?>
                                    <span class="badge text-bg-light border"><?= esc($estado) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($canDesc): ?>
                                    <a class="link-primary" href="<?= site_url('modulo3/documentos/'.$id.'/pdf') ?>">
                                        <i class="bi bi-file-earmark-pdf"></i> Abrir/Descargar
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No adjunto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end actions">
                                <!-- Editar (abre modal) -->
                                <button
                                        class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#mdlEditarDoc"
                                        data-id="<?= esc($id) ?>"
                                        data-tipo="<?= esc($tipo) ?>"
                                        data-numero="<?= esc($num) ?>"
                                        data-fecha="<?= esc($fecha) ?>"
                                        data-estado="<?= esc($estado) ?>"
                                        data-embarqueid="<?= esc($d['embarqueId'] ?? '') ?>"
                                        data-archivoruta="<?= esc($ruta) ?>"
                                        data-urlpdf="<?= esc($urlPdf) ?>"
                                        data-archivopdf="<?= esc($arch) ?>"
                                >
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <!-- Eliminar -->
                                <form class="d-inline" method="post" action="<?= site_url('modulo3/documentos/'.(int)$id.'/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar documento #<?= (int)$id ?>?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: Nuevo Documento ===== -->
<div class="modal fade" id="mdlNuevoDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= site_url('modulo3/documentos/crear') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Embarque</label>
                            <select name="embarqueId" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($embarques as $e): ?>
                                    <option value="<?= (int)($e['id'] ?? 0) ?>">
                                        <?= esc($e['folio'] ?? ('ID '.$e['id'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <input class="form-control" name="tipo" placeholder="Ej. Carta Porte, Guía, etc.">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número</label>
                            <input class="form-control" name="numero">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input class="form-control" name="estado" placeholder="Ej. vigente">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">URL PDF</label>
                            <input class="form-control" name="urlPdf" placeholder="https://...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archivo (ruta interna)</label>
                            <input class="form-control" name="archivoPdf" placeholder="/uploads/mi-archivo.pdf">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ArchivoRuta (alterno)</label>
                            <input class="form-control" name="archivoRuta" placeholder="/ruta/o/url/alternativa.pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Modal: Editar Documento ===== -->
<div class="modal fade" id="mdlEditarDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" id="frmEditarDoc">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Editar documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ed-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Embarque</label>
                            <select name="embarqueId" id="ed-embarqueId" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($embarques as $e): ?>
                                    <option value="<?= (int)($e['id'] ?? 0) ?>">
                                        <?= esc($e['folio'] ?? ('ID '.$e['id'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <input class="form-control" id="ed-tipo" name="tipo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número</label>
                            <input class="form-control" id="ed-numero" name="numero">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="ed-fecha" name="fecha">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input class="form-control" id="ed-estado" name="estado">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">URL PDF</label>
                            <input class="form-control" id="ed-urlPdf" name="urlPdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archivo (ruta interna)</label>
                            <input class="form-control" id="ed-archivoPdf" name="archivoPdf">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ArchivoRuta (alterno)</label>
                            <input class="form-control" id="ed-archivoRuta" name="archivoRuta">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
        // Rellena modal Editar con data-* del botón
        const mdl = document.getElementById('mdlEditarDoc');
        mdl?.addEventListener('show.bs.modal', (ev)=>{
            const btn = ev.relatedTarget;
            if(!btn) return;
            const id   = btn.getAttribute('data-id');
            const frm  = document.getElementById('frmEditarDoc');
            frm.action = '<?= site_url('modulo3/documentos') ?>/' + id + '/editar';

            document.getElementById('ed-id').value = id;
            document.getElementById('ed-tipo').value = btn.getAttribute('data-tipo') || '';
            document.getElementById('ed-numero').value = btn.getAttribute('data-numero') || '';
            document.getElementById('ed-fecha').value = btn.getAttribute('data-fecha') || '';
            document.getElementById('ed-estado').value = btn.getAttribute('data-estado') || '';
            document.getElementById('ed-embarqueId').value = btn.getAttribute('data-embarqueid') || '';
            document.getElementById('ed-archivoRuta').value = btn.getAttribute('data-archivoruta') || '';
            document.getElementById('ed-urlPdf').value = btn.getAttribute('data-urlpdf') || '';
            document.getElementById('ed-archivoPdf').value = btn.getAttribute('data-archivopdf') || '';
        });

        // (Opcional) Si la página vino con __payload vía POST, validamos JSON (sin usar $this->request)
        <?php if ($req && $req->getPost('__payload')): ?>
        try {
            const _payload = JSON.parse(<?= json_encode($req->getPost('__payload')) ?>);
            // console.log('payload recibido', _payload);
        } catch (_) {}
        <?php endif; ?>
    })();
</script>
<?= $this->endSection() ?>
