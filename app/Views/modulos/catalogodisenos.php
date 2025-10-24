<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Separar los botones de DataTables (corrige el btn-group pegado) */
    .dt-buttons.btn-group .btn{
        margin-left: 0 !important;
        margin-right: .5rem;
        border-radius: .375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right: 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="me-3">CATÁLOGO DE DISEÑOS</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoDisenoModal">
        <i class="bi bi-plus-circle"></i> NUEVO DISEÑO
    </button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <table id="tablaDisenos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>NOMBRE</th>
                <th>DESCRIPCIÓN</th>
                <th>VERSIÓN</th>
                <th>PRECIO UNIDAD</th>
                <th>MATERIALES</th>
                <th>ACCIONES</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($disenos)): ?>
                <?php foreach ($disenos as $d): ?>
                    <tr>
                        <td><?= esc($d['id']) ?></td>
                        <td><strong><?= esc($d['nombre']) ?></strong></td>
                        <td class="text-start"><?= esc($d['descripcion']) ?></td>
                        <td><?= esc($d['version']) ?></td>
                        <td><?= isset($d['precio_unidad']) && $d['precio_unidad'] !== '' ? esc($d['precio_unidad']) : '-' ?></td>
                        <td class="text-start">
                            <?php if (!empty($d['materiales'])): ?>
                                <ul class="material-list">
                                    <?php foreach ($d['materiales'] as $m): ?>
                                        <li><?= esc($m) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <em>Sin materiales</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-ver-modal"
                                    data-id="<?= (int)$d['id'] ?>"
                                    data-nombre="<?= esc($d['nombre']) ?>"
                                    data-descripcion="<?= esc($d['descripcion']) ?>"
                                    data-version="<?= esc($d['version']) ?>"
                                    data-materiales='<?= esc(!empty($d['materiales']) ? implode(", ", $d['materiales']) : "Sin materiales") ?>'
                                    data-imagen="<?= isset($d['imagen']) ? esc($d['imagen']) : '' ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#disenoModal">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary me-1 btn-editar-modal"
                                    title="Editar"
                                    data-id="<?= (int)$d['id'] ?>"
                                    data-codigo="<?= isset($d['codigo']) ? esc($d['codigo']) : '' ?>"
                                    data-nombre="<?= esc($d['nombre']) ?>"
                                    data-descripcion="<?= esc($d['descripcion']) ?>"
                                    data-version="<?= esc($d['version']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarDisenoModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar"
                                    data-id="<?= (int)$d['id'] ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Bootstrap: Nuevo Diseño -->
<div class="modal fade" id="nuevoDisenoModal" tabindex="-1" aria-labelledby="nuevoDisenoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoDisenoLabel">Nuevo diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoDiseno">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre<span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versión<span class="text-danger">*</span></label>
                            <input type="text" name="version" class="form-control" placeholder="v1.0" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <input type="text" name="notas" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio por unidad</label>
                            <input type="number" name="precio_unidad" class="form-control" min="0" step="0.01" placeholder="0.00" inputmode="decimal" />
                        </div>
                        <!-- Catálogos: sexo, talla, tipo corte, tipo ropa -->
                        <div class="col-md-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="idSexoFK" id="selSexo" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="idTallasFK" id="selTalla" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de corte</label>
                            <select class="form-select" name="idTipoCorteFK" id="selTipoCorte" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de ropa</label>
                            <select class="form-select" name="idTipoRopaFK" id="selTipoRopa" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Archivo CAD (subir cualquier formato)</label>
                            <input type="file" name="archivoCadFile" class="form-control" />
                            <small class="text-muted">Opcional: si no subes archivo, puedes poner URL manual.</small>
                            <input type="text" name="archivoCadUrl" class="form-control mt-1" placeholder="/archivos/cad/archivo.dxf" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Archivo Patrón (subir cualquier formato)</label>
                            <input type="file" name="archivoPatronFile" class="form-control" />
                            <small class="text-muted">Opcional: si no subes archivo, puedes poner URL manual.</small>
                            <input type="text" name="archivoPatronUrl" class="form-control mt-1" placeholder="/archivos/patron/archivo.pdf" />
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="aprobadoCheck" name="aprobado">
                                <label class="form-check-label" for="aprobadoCheck">Aprobado</label>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Materiales (desde artículos)</label>
                            <input type="text" id="buscarArticulo" class="form-control mb-2" placeholder="Buscar por nombre, unidad o SKU..." />
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="fw-semibold small mb-1">Disponibles</div>
                                    <div id="listaDisponibles" class="border rounded p-2" style="max-height: 220px; overflow:auto;"></div>
                                </div>
                            </div>
                            <small class="text-muted">Marca los materiales; aparecerán abajo para capturar cantidades.</small>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th style="width:5%"></th>
                                        <th style="width:55%">Artículo</th>
                                        <th style="width:20%">Cantidad por unidad</th>
                                        <th style="width:20%">Merma % (opc)</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tblMaterialesBody">
                                    <tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="nuevoDisenoAlert" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarDiseno" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Editar Diseño -->
<div class="modal fade" id="editarDisenoModal" tabindex="-1" aria-labelledby="editarDisenoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarDisenoLabel">Editar diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarDiseno">
                    <input type="hidden" name="id" id="e-id" />
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" id="e-codigo" class="form-control" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre<span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="e-nombre" class="form-control" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="e-descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versión<span class="text-danger">*</span></label>
                            <input type="text" name="version" id="e-version" class="form-control" placeholder="v1.0" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="e-fecha" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <input type="text" name="notas" id="e-notas" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="idSexoFK" id="e-selSexo" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="idTallasFK" id="e-selTalla" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de corte</label>
                            <select class="form-select" name="idTipoCorteFK" id="e-selTipoCorte" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de ropa</label>
                            <select class="form-select" name="idTipoRopaFK" id="e-selTipoRopa" disabled>
                                <option value="">Cargando…</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio por unidad</label>
                            <input type="number" name="precio_unidad" id="e-precio_unidad" class="form-control" min="0" step="0.01" placeholder="0.00" inputmode="decimal" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Archivo CAD (subir cualquier formato)</label>
                            <input type="file" name="archivoCadFile" class="form-control" />
                            <small class="text-muted">Opcional. Vacío conserva el actual.</small>
                            <input type="text" name="archivoCadUrl" id="e-archivoCadUrl" class="form-control mt-1" placeholder="/archivos/cad/archivo.dxf" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Archivo Patrón (subir cualquier formato)</label>
                            <input type="file" name="archivoPatronFile" class="form-control" />
                            <small class="text-muted">Opcional. Vacío conserva el actual.</small>
                            <input type="text" name="archivoPatronUrl" id="e-archivoPatronUrl" class="form-control mt-1" placeholder="/archivos/patron/archivo.pdf" />
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="e-aprobadoCheck" name="aprobado">
                                <label class="form-check-label" for="e-aprobadoCheck">Aprobado</label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div id="e-materialesSummary" class="alert alert-secondary py-2 d-none"></div>
                        </div>

                        <div class="col-12 mt-2">
                            <label class="form-label">Materiales (agregar/quitar)</label>
                            <input type="text" id="e-buscarArticulo" class="form-control mb-2" placeholder="Buscar por nombre, unidad o SKU..." />
                            <div class="row g-2">
                                <div class="col-12">
                                    <div id="e-listaDisponibles" class="border rounded p-2" style="max-height: 220px; overflow:auto;"></div>
                                </div>
                            </div>
                            <small class="text-muted">Marca para agregar; aparecerán abajo para capturar cantidades.</small>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive mt-2">
                                <table class="table table-sm align-middle">
                                    <thead>
                                    <tr>
                                        <th style="width:5%"></th>
                                        <th style="width:55%">Artículo</th>
                                        <th style="width:20%">Cantidad por unidad</th>
                                        <th style="width:20%">Merma % (opc)</th>
                                    </tr>
                                    </thead>
                                    <tbody id="e-tblMaterialesBody">
                                    <tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="editarDisenoAlert" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnActualizarDiseno" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Detalles del diseño -->
<div class="modal fade" id="disenoModal" tabindex="-1" aria-labelledby="disenoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="disenoModalLabel">Detalles del diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <div class="text-center mb-3 text-dark" id="m-imagen-wrap" style="display:none;">
                    <img id="m-imagen" src="" alt="Imagen del diseño" class="img-fluid rounded border text-dark" />
                </div>
                <dl class="row mb-0 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">ID</dt>
                    <dd class="col-sm-9 text-dark" id="m-id">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Código</dt>
                    <dd class="col-sm-9 text-dark" id="m-codigo">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                    <dd class="col-sm-9 text-dark" id="m-nombre">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>
                    <dd class="col-sm-9 text-dark" id="m-descripcion">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Versión</dt>
                    <dd class="col-sm-9 text-dark" id="m-version">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Fecha versión</dt>
                    <dd class="col-sm-9 text-dark" id="m-fecha">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Notas</dt>
                    <dd class="col-sm-9 text-dark" id="m-notas">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Precio unidad</dt>
                    <dd class="col-sm-9 text-dark" id="m-precio">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Materiales</dt>
                    <dd class="col-sm-9 text-dark" id="m-materiales">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Vista previa CAD</dt>
                    <dd class="col-sm-9 text-dark" id="m-cad">
                        <div id="m-cad-view" style="display:none;">
                            <img id="m-cad-img" src="" alt="CAD" class="img-fluid rounded border mb-2" style="max-height:300px; display:none;" />
                            <object id="m-cad-pdf" data="" type="application/pdf" width="100%" height="320" style="display:none;">
                                <div class="text-muted">No se pudo mostrar el PDF.</div>
                            </object>
                            <div id="m-cad-fallback" class="text-muted" style="display:none;"></div>
                        </div>
                        <div id="m-cad-empty">-</div>
                    </dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Vista previa Patrón</dt>
                    <dd class="col-sm-9 text-dark" id="m-patron">
                        <div id="m-patron-view" style="display:none;">
                            <img id="m-patron-img" src="" alt="Patrón" class="img-fluid rounded border mb-2" style="max-height:300px; display:none;" />
                            <object id="m-patron-pdf" data="" type="application/pdf" width="100%" height="320" style="display:none;">
                                <div class="text-muted">No se pudo mostrar el PDF.</div>
                            </object>
                            <div id="m-patron-fallback" class="text-muted" style="display:none;"></div>
                        </div>
                        <div id="m-patron-empty">-</div>
                    </dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Aprobado</dt>
                    <dd class="col-sm-9 text-dark" id="m-aprobado">-</dd>
                </dl>

                <!-- Carrusel de vistas previas -->
                <div id="m-carousel" class="carousel slide mt-3" data-bs-ride="carousel" style="display:none;">
                    <div class="carousel-indicators" id="m-carousel-indicators"></div>
                    <div class="carousel-inner" id="m-carousel-inner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#m-carousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#m-carousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- JS Bootstrap + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Export helpers (Buttons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Visores DXF (si no cargan, se hace fallback) -->
<script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
<script src="https://unpkg.com/dxf-parser@4.11.5/dist/dxf-parser.min.js"></script>
<script src="https://unpkg.com/three-dxf@1.0.0/dist/three-dxf.js"></script>

<script>
    // Helper: renderizar DXF si hay librerías disponibles
    function renderDXF(containerId, url) {
        const el = document.getElementById(containerId);
        if (!el) return;
        try {
            if (window.THREE && window.DXF && typeof window.DXF.Viewer === 'function') {
                new window.DXF.Viewer(el, url);
                return;
            }
            if (window.THREE && window.DxfViewer) {
                const viewer = new window.DxfViewer(el, { autoResize: true, clearColor: 0xffffff });
                if (typeof viewer.load === 'function') viewer.load(url);
                return;
            }
            if (window.DxfParser) {
                fetch(url).then(r => r.text()).then(txt => {
                    try {
                        const parser = new window.DxfParser();
                        parser.parseSync(txt);
                        el.innerHTML = '<div class="p-3 text-muted">DXF cargado (se requieren librerías de render para ver la geometría).</div>';
                    } catch (e) {
                        el.innerHTML = '<div class="p-3 text-muted">No fue posible interpretar el DXF.</div>';
                    }
                }).catch(() => {
                    el.innerHTML = '<div class="p-3 text-muted">No fue posible descargar el DXF.</div>';
                });
            }
            el.innerHTML = 'Vista previa DXF no disponible en este navegador.';
        } catch (e) {
            el.innerHTML = 'Error cargando DXF.';
        }
    }

    // ====== Lógica existente (editar/nuevo) — se conserva ======
    let eSeleccionados = new Set();

    function e_labelArticulo(a){
        return (a.nombre || ('ID '+a.id)) + (a.unidadMedida ? ' ('+a.unidadMedida+')' : '') + (a.sku ? ' • ' + a.sku : '');
    }
    function e_pintarDisponibles(filtro = ''){
        const $disp = $('#e-listaDisponibles');
        $disp.empty();
        const idsSeleccionados = eSeleccionados;
        const q = (filtro||'').toLowerCase();
        let count = 0;
        (articulosCache||[]).forEach(a => {
            if (idsSeleccionados.has(String(a.id))) return;
            const hay = [a.nombre, a.unidadMedida, a.sku].filter(Boolean).join(' ').toLowerCase();
            if (q && !hay.includes(q)) return;
            const item = '<label class="form-check d-flex align-items-center gap-2 mb-1">'
                + '<input class="form-check-input e-chk-disp" type="checkbox" value="'+a.id+'" />'
                + '<span>'+e_labelArticulo(a)+'</span>'
                + '</label>';
            $disp.append(item);
            count++;
        });
        if (count === 0) $disp.html('<div class="text-muted px-2">Sin coincidencias</div>');
    }
    function e_renderMateriales(){
        const ids = Array.from(eSeleccionados);
        const $tb = $('#e-tblMaterialesBody');
        $tb.empty();
        if (ids.length === 0){
            $tb.append('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
            return;
        }
        ids.forEach(id => {
            const art = (articulosCache||[]).find(a => String(a.id)===String(id));
            const nombre = art ? e_labelArticulo(art) : ('ID '+id);
            const row = `
                    <tr data-id="${id}">
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger e-btn-del-row" title="Quitar"><i class="bi bi-x"></i></button></td>
                        <td>${nombre}</td>
                        <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm e-inp-cant" placeholder="0" /></td>
                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm e-inp-merma" placeholder="0" /></td>
                    </tr>`;
            $tb.append(row);
        });
    }
    $(document).on('click', '.btn-editar-modal', function(){
        const id = $(this).data('id');
        eSeleccionados = new Set();
        $('#formEditarDiseno')[0].reset();
        $('#e-id').val(id);
        $('#e-materialesSummary').addClass('d-none').empty();
        $('#e-tblMaterialesBody').html('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        const modalEl = document.getElementById('editarDisenoModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
        const $btn = $(this);
        if ($btn.data('codigo') !== undefined) $('#e-codigo').val($btn.data('codigo'));
        if ($btn.data('nombre') !== undefined) $('#e-nombre').val($btn.data('nombre'));
        if ($btn.data('descripcion') !== undefined) $('#e-descripcion').val($btn.data('descripcion'));
        if ($btn.data('version') !== undefined) $('#e-version').val($btn.data('version'));
        if (articulosCache === null) { cargarArticulos(); }
        e_pintarDisponibles();
        // Cargar catálogos para edición
        const q1 = cargarCatalogo('<?= base_url('modulo2/catalogos/sexo') ?>' + '?_=' + Date.now(), '#e-selSexo');
        const q2 = cargarCatalogo('<?= base_url('modulo2/catalogos/tallas') ?>' + '?_=' + Date.now(), '#e-selTalla');
        const q3 = cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-corte') ?>' + '?_=' + Date.now(), '#e-selTipoCorte');
        const q4 = cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-ropa') ?>' + '?_=' + Date.now(), '#e-selTipoRopa');
        $.getJSON('<?= base_url('modulo2/diseno') ?>/' + id + '/json')
            .done(function (data) {
                $('#e-codigo').val(data.codigo||'');
                $('#e-nombre').val(data.nombre||'');
                $('#e-descripcion').val(data.descripcion||'');
                $('#e-version').val(data.version||'');
                if (data.fecha) { $('#e-fecha').val(String(data.fecha).slice(0,10)); }
                $('#e-notas').val(data.notas||'');
                if (data.precio_unidad !== undefined && data.precio_unidad !== null) { $('#e-precio_unidad').val(data.precio_unidad); }
                $('#e-archivoCadUrl').val(data.archivoCadUrl||'');
                $('#e-archivoPatronUrl').val(data.archivoPatronUrl||'');
                const apr = data.aprobado;
                $('#e-aprobadoCheck').prop('checked', apr === 1 || apr === true || apr === '1');
                // Setear FKs cuando carguen los catálogos
                $.when(q1, q2, q3, q4).always(function(){
                    if (data.idSexoFK !== undefined && data.idSexoFK !== null) $('#e-selSexo').val(String(data.idSexoFK));
                    if (data.IdTallasFK !== undefined && data.IdTallasFK !== null) $('#e-selTalla').val(String(data.IdTallasFK));
                    if (data.idTipoCorteFK !== undefined && data.idTipoCorteFK !== null) $('#e-selTipoCorte').val(String(data.idTipoCorteFK));
                    if (data.idTipoRopaFK !== undefined && data.idTipoRopaFK !== null) $('#e-selTipoRopa').val(String(data.idTipoRopaFK));
                });

                const mats = data.materiales || [];
                if (mats.length > 0) {
                    let html = '<div class="small"><strong>Materiales actuales:</strong><ul class="mb-0">';
                    mats.forEach(m => {
                        if (typeof m === 'string') html += '<li>'+m+'</li>';
                        else html += '<li>'+(m.nombre||'Material') + (m.cantidad? (' x '+m.cantidad):'') + (m.merma? (' • merma '+m.merma+'%'):'') + '</li>';
                    });
                    html += '</ul></div>';
                    $('#e-materialesSummary').removeClass('d-none').html(html);
                }

                const det = data.materialesDet || [];
                if (det.length > 0) {
                    $('#e-materialesSummary').addClass('d-none').empty();
                    eSeleccionados = new Set(det.map(r => String(r.articuloId)));
                    e_renderMateriales();
                    det.forEach(r => {
                        const tr = $('#e-tblMaterialesBody tr[data-id="'+String(r.articuloId)+'"]');
                        if (tr.length) {
                            if (r.cantidadPorUnidad !== undefined && r.cantidadPorUnidad !== null) {
                                tr.find('.e-inp-cant').val(r.cantidadPorUnidad);
                            }
                            if (r.mermaPct !== undefined && r.mermaPct !== null) {
                                tr.find('.e-inp-merma').val(r.mermaPct);
                            }
                        }
                    });
                    const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
                    e_pintarDisponibles(q);
                }
            })
            .fail(function(xhr){
                $('#editarDisenoAlert').removeClass('d-none').text('No fue posible cargar los datos');
            });
    });
    $(document).on('input', '#e-buscarArticulo', function(){
        const q = ($(this).val()||'').toString().toLowerCase().trim();
        e_pintarDisponibles(q);
    });
    $(document).on('change', '#e-listaDisponibles .e-chk-disp', function(){
        const id = String(this.value);
        if (this.checked) eSeleccionados.add(id); else eSeleccionados.delete(id);
        e_renderMateriales();
        if (!this.checked) {
            const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
            e_pintarDisponibles(q);
        }
    });
    $(document).on('click', '.e-btn-del-row', function(){
        const $tr = $(this).closest('tr');
        const id = String($tr.data('id'));
        $tr.remove();
        eSeleccionados.delete(id);
        const q = ($('#e-buscarArticulo').val()||'').toString().toLowerCase().trim();
        e_pintarDisponibles(q);
        if ($('#e-tblMaterialesBody tr[data-id]').length === 0) {
            $('#e-tblMaterialesBody').html('<tr class="text-muted" id="e-rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        }
    });
    $('#btnActualizarDiseno').on('click', function(){
        const id = $('#e-id').val();
        const $alert = $('#editarDisenoAlert');
        $alert.addClass('d-none').text('');
        const formEl = document.getElementById('formEditarDiseno');
        const fd = new FormData(formEl);
        const materials = [];
        $('#e-tblMaterialesBody tr').each(function(){
            const idm = $(this).data('id');
            if (!idm) return;
            const cant = parseFloat($(this).find('.e-inp-cant').val() || '0');
            const merma = $(this).find('.e-inp-merma').val();
            const mermaNum = merma === '' ? null : parseFloat(merma);
            materials.push({ articuloId: parseInt(idm,10), cantidadPorUnidad: isNaN(cant)?0:cant, mermaPct: (mermaNum===null||isNaN(mermaNum))?null:mermaNum });
        });
        if (materials.length > 0) fd.append('materials', JSON.stringify(materials));
        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizará el diseño.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('modulo2/actualizar') ?>/'+id,
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
            }).done(function(resp){
                if (resp && (resp.ok || resp.success)) {
                    Swal.fire({ title:'¡Guardado!', text:'Cambios aplicados.', icon:'success' }).then(()=>{
                        const modalEl = document.getElementById('editarDisenoModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        window.location.reload();
                    });
                } else {
                    const msg = resp && (resp.message||resp.error) ? (resp.message||resp.error) : 'No se pudo actualizar.';
                    Swal.fire({ title:'Error', text: msg, icon:'error' });
                }
            }).fail(function(xhr){
                let msg = 'Error al actualizar.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ title:'Error', text: msg, icon:'error' });
            });
        });
    });

    // ====== Nuevo diseño ======
    let articulosCache = null;
    const seleccionados = new Set();
    const renderMateriales = () => {
        const selected = Array.from(seleccionados);
        const $tb = $('#tblMaterialesBody');
        $tb.empty();
        if (selected.length === 0) {
            $tb.append('<tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
            return;
        }
        selected.forEach(id => {
            const art = (articulosCache || []).find(a => String(a.id) === String(id));
            const nombre = art ? (art.nombre + (art.unidadMedida ? ' ('+art.unidadMedida+')' : '')) : ('ID ' + id);
            const row = `
                    <tr data-id="${id}">
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del-row" title="Quitar"><i class="bi bi-x"></i></button></td>
                        <td>${nombre}</td>
                        <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm inp-cant" placeholder="0" /></td>
                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm inp-merma" placeholder="0" /></td>
                    </tr>`;
            $tb.append(row);
        });
    };
    function cargarArticulos(){
        const url = '<?= base_url('modulo2/articulos/json') ?>' + '?_=' + Date.now();
        const $disp = $('#listaDisponibles');
        $disp.html('<div class="text-muted px-2">Cargando artículos…</div>');
        return $.getJSON(url)
            .done(function(resp){
                articulosCache = (resp && resp.items) ? resp.items : [];
                pintarDisponibles();
            })
            .fail(function(xhr){
                console.error('Error cargando artículos', xhr && xhr.status, xhr && xhr.responseText);
                $('#listaDisponibles').html('<div class="text-danger px-2">Error cargando artículos</div>');
            });
    }
    function labelArticulo(a){
        return (a.nombre || ('ID '+a.id)) + (a.unidadMedida ? ' ('+a.unidadMedida+')' : '') + (a.sku ? ' • ' + a.sku : '');
    }
    function pintarDisponibles(filtro = ''){
        const $disp = $('#listaDisponibles');
        $disp.empty();
        const idsSeleccionados = seleccionados;
        const q = (filtro||'').toLowerCase();
        let count = 0;
        (articulosCache||[]).forEach(a => {
            if (idsSeleccionados.has(String(a.id))) return;
            const hay = [a.nombre, a.unidadMedida, a.sku].filter(Boolean).join(' ').toLowerCase();
            if (q && !hay.includes(q)) return;
            const item = '<label class="form-check d-flex align-items-center gap-2 mb-1">'
                + '<input class="form-check-input chk-disp" type="checkbox" value="'+a.id+'" />'
                + '<span>'+labelArticulo(a)+'</span>'
                + '</label>';
            $disp.append(item);
            count++;
        });
        if (count === 0) { $disp.html('<div class="text-muted px-2">Sin coincidencias</div>'); }
    }
    function initCatalogos(){
        // Cargar catálogos en paralelo con cache-busting
        cargarCatalogo('<?= base_url('modulo2/catalogos/sexo') ?>' + '?_=' + Date.now(), '#selSexo');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tallas') ?>' + '?_=' + Date.now(), '#selTalla');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-corte') ?>' + '?_=' + Date.now(), '#selTipoCorte');
        cargarCatalogo('<?= base_url('modulo2/catalogos/tipo-ropa') ?>' + '?_=' + Date.now(), '#selTipoRopa');
    }
    $('#nuevoDisenoModal').on('show.bs.modal shown.bs.modal', function(){
        if (articulosCache === null) { cargarArticulos(); }
        initCatalogos();
    });
    $(document).on('input', '#buscarArticulo', function(){
        const q = ($(this).val() || '').toString().toLowerCase().trim();
        pintarDisponibles(q);
    });
    $(document).on('change', '#listaDisponibles .chk-disp', function(){
        const id = String(this.value);
        if (this.checked) seleccionados.add(id); else seleccionados.delete(id);
        renderMateriales();
        if (!this.checked) {
            const q = ($('#buscarArticulo').val()||'').toString().toLowerCase().trim();
            pintarDisponibles(q);
        }
    });
    $(document).on('click', '.btn-del-row', function(){
        const $tr = $(this).closest('tr');
        const id = String($tr.data('id'));
        $tr.remove();
        const $sel = $('#listaSeleccionados');
        const opt = $sel.find('option[value="'+id+'"]');
        if (opt.length) { opt.remove(); }
        pintarDisponibles($('#buscarArticulo').val()||'');
        if ($('#tblMaterialesBody tr[data-id]').length === 0) {
            $('#tblMaterialesBody').html('<tr class="text-muted" id="rowMaterialesEmpty"><td colspan="4">Sin materiales seleccionados</td></tr>');
        }
    });
    $('#btnGuardarDiseno').on('click', function(){
        const $alert = $('#nuevoDisenoAlert');
        $alert.addClass('d-none').text('');
        const formEl = document.getElementById('formNuevoDiseno');
        const fd = new FormData(formEl);
        const materials = [];
        $('#tblMaterialesBody tr').each(function(){
            const id = $(this).data('id');
            if (!id) return;
            const cant = parseFloat($(this).find('.inp-cant').val() || '0');
            const merma = $(this).find('.inp-merma').val();
            const mermaNum = merma === '' ? null : parseFloat(merma);
            materials.push({ articuloId: parseInt(id,10), cantidadPorUnidad: isNaN(cant)?0:cant, mermaPct: (mermaNum===null||isNaN(mermaNum))?null:mermaNum });
        });
        if (materials.length > 0) fd.append('materials', JSON.stringify(materials));
        Swal.fire({
            title: '¿Guardar diseño?',
            text: 'Se creará un nuevo diseño.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('modulo2/disenos/crear') ?>',
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
            }).done(function(resp){
                if (resp && resp.ok) {
                    Swal.fire({ title:'¡Guardado!', text:'Diseño creado correctamente.', icon:'success' }).then(()=>{
                        const modalEl = document.getElementById('nuevoDisenoModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        window.location.reload();
                    });
                } else {
                    const msg = resp && resp.message ? resp.message : 'No se pudo guardar.';
                    Swal.fire({ title:'Error', text: msg, icon:'error' });
                }
            }).fail(function(xhr){
                let msg = 'Error al guardar.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ title:'Error', text: msg, icon:'error' });
            });
        });
    });

    // ==== Helpers: cargar catálogos con spinner ====
    function cargarCatalogo(url, selectSel){
        const $sel = $(selectSel);
        $sel.prop('disabled', true).html('<option value="">Cargando…</option>');
        return $.getJSON(url)
            .done(function(resp){
                const items = (resp && resp.items) ? resp.items : [];
                if (!items.length){
                    $sel.html('<option value="">Sin datos</option>');
                } else {
                    const opts = ['<option value="">Seleccione…</option>'];
                    items.forEach(function(it){
                        const id = it.id ?? it.ID ?? it.Id;
                        const nombre = (it.nombre || it.name || ('ID '+id));
                        opts.push('<option value="'+id+'">'+nombre+'</option>');
                    });
                    $sel.html(opts.join(''));
                }
            })
            .fail(function(){ $sel.html('<option value="">Error al cargar</option>'); })
            .always(function(){ $sel.prop('disabled', false); });
    }

    // ====== DataTable con Buttons (izquierda) ======
    $(document).ready(function () {
        // Fallback: por si no se dispara el evento del modal
        if (document.getElementById('nuevoDisenoModal')) {
            try { initCatalogos(); } catch(e) { console.warn('initCatalogos error', e); }
            try { if (articulosCache === null) cargarArticulos(); } catch(e) { console.warn('cargarArticulos error', e); }
        }
        // Español + layout con botones a la izquierda y buscador a la derecha
        const langES = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_",
            "sInfoEmpty":      "Mostrando 0 a 0 de 0",
            "sInfoFiltered":   "(filtrado de _MAX_)",
            "sSearch":         "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "buttons": { "copy": "Copiar", "colvis": "Columnas" }
        };
        const fecha = new Date().toISOString().slice(0,10);
        const fileName = 'catalogo_disenos_' + fecha;

        $('#tablaDisenos').DataTable({
            language: langES,
            columnDefs: [{ targets: -1, orderable:false, searchable:false }], // ACCIONES
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

        // Botones de acción (editar/eliminar) existentes
        $('.btn-accion').on('click', function() {
            const action = $(this).attr('title');
            const id = $(this).data('id');
            if (action === 'Editar') {
                window.location.href = '<?= base_url('modulo2/editardiseno/') ?>' + id;
            } else if (action === 'Eliminar') {
                Swal.fire({
                    title: '¿Eliminar diseño?',
                    text: 'No podrás revertir esta acción.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result)=>{
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '<?= base_url('modulo2/disenos/eliminar') ?>/'+id,
                        method: 'POST',
                    }).done(function(resp){
                        if (resp && resp.ok) {
                            Swal.fire({ title:'Eliminado', text: 'Diseño eliminado correctamente', icon:'success' }).then(()=>{
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ title:'Error', text: (resp && resp.message) ? resp.message : 'No se pudo eliminar', icon:'error' });
                        }
                    }).fail(function(xhr){
                        let msg = 'Error al eliminar';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ title:'Error', text: msg, icon:'error' });
                    });
                });
            }
        });

        // Modal Detalles (AJAX)
        $(document).on('click', '.btn-ver-modal', function () {
            const id = $(this).data('id');
            $('#m-id,#m-codigo,#m-nombre,#m-version,#m-fecha,#m-notas,#m-aprobado').text('-');
            $('#m-descripcion').text('Cargando...');
            $('#m-materiales').text('-');
            const $ci = $('#m-carousel-inner'); const $ind = $('#m-carousel-indicators');
            $ci.empty(); $ind.empty(); $('#m-carousel').hide();
            $('#m-imagen').attr('src', ''); $('#m-imagen-wrap').hide(); $('#m-editar').attr('href', '#');

            $.getJSON('<?= base_url('modulo2/diseno') ?>/' + id + '/json')
                .done(function (data) {
                    $('#m-id').text(data.id || id);
                    $('#m-codigo').text(data.codigo || '-');
                    $('#m-nombre').text(data.nombre || '-');
                    $('#m-descripcion').text(data.descripcion || '-');
                    $('#m-version').text(data.version || '-');
                    $('#m-fecha').text(data.fecha ? (new Date(data.fecha)).toISOString().slice(0,10) : '-');
                    $('#m-notas').text(data.notas || '-');
                    $('#m-precio').text((data.precio_unidad !== undefined && data.precio_unidad !== null && data.precio_unidad !== '') ? data.precio_unidad : '-');
                    $('#m-materiales').text((data.materiales || []).join(', '));

                    const isImage = (u) => /\.(png|jpg|jpeg|gif|bmp|webp|svg)$/i.test(u || '');
                    const isPdf   = (u) => /\.(pdf)$/i.test(u || '');
                    const isDxf   = (u) => /\.(dxf)$/i.test(u || '');
                    let slides = [];

                    if (data.imagenUrl && isImage(data.imagenUrl)) {
                        slides.push({title: 'Imagen',
                            html: '<div class="text-center"><img src="'+data.imagenUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="Imagen"/></div>'});
                    }
                    if (data.archivoCadUrl) {
                        if (isImage(data.archivoCadUrl)) {
                            slides.push({title:'CAD (imagen)', html:'<div class="text-center"><img src="'+data.archivoCadUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="CAD"/></div>'});
                        } else if (isPdf(data.archivoCadUrl)) {
                            slides.push({title:'CAD (PDF)', html:'<object data="'+data.archivoCadUrl+'" type="application/pdf" width="100%" height="450"><div class="text-muted p-3">No se pudo mostrar el PDF CAD.</div></object>'});
                        } else if (isDxf(data.archivoCadUrl)) {
                            const dxfId = 'dxf-cad-'+Date.now();
                            slides.push({title:'CAD (DXF)', html:'<div id="'+dxfId+'" style="height:450px; background:#f8f9fa;" class="rounded border d-flex align-items-center justify-content-center">Cargando DXF…</div>', afterMount: function(){ renderDXF(dxfId, data.archivoCadUrl); }});
                        } else {
                            slides.push({title:'CAD', html:'<div class="p-3 text-muted">Archivo CAD: '+ data.archivoCadUrl +'</div>'});
                        }
                    }
                    if (data.archivoPatronUrl) {
                        if (isImage(data.archivoPatronUrl)) {
                            slides.push({title:'Patrón (imagen)', html:'<div class="text-center"><img src="'+data.archivoPatronUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="Patrón"/></div>'});
                        } else if (isPdf(data.archivoPatronUrl)) {
                            slides.push({title:'Patrón (PDF)', html:'<object data="'+data.archivoPatronUrl+'" type="application/pdf" width="100%" height="450"><div class="text-muted p-3">No se pudo mostrar el PDF Patrón.</div></object>'});
                        } else if (isDxf(data.archivoPatronUrl)) {
                            const dxfId2 = 'dxf-patron-'+Date.now();
                            slides.push({title:'Patrón (DXF)', html:'<div id="'+dxfId2+'" style="height:450px; background:#f8f9fa;" class="rounded border d-flex align-items-center justify-content-center">Cargando DXF…</div>', afterMount: function(){ renderDXF(dxfId2, data.archivoPatronUrl); }});
                        } else {
                            slides.push({title:'Patrón', html:'<div class="p-3 text-muted">Archivo Patrón: '+ data.archivoPatronUrl +'</div>'});
                        }
                    }

                    if (slides.length > 0) {
                        slides.forEach((s, idx) => {
                            const active = idx === 0 ? ' active' : '';
                            $ci.append('<div class="carousel-item'+active+'">'+ s.html +'</div>');
                            $ind.append('<button type="button" data-bs-target="#m-carousel" data-bs-slide-to="'+idx+'" '+(idx===0?'class="active" aria-current="true"':'')+' aria-label="'+(s.title||('Slide '+(idx+1)))+'"></button>');
                        });
                        $('#m-carousel').show();
                        setTimeout(() => { slides.forEach(s => { if (typeof s.afterMount === 'function') s.afterMount(); }); }, 50);
                    }
                    const apr = data.aprobado;
                    $('#m-aprobado').text(apr === 1 || apr === true || apr === '1' ? 'Sí' : (apr === 0 || apr === false || apr === '0' ? 'No' : '-'));
                    $('#m-editar').attr('href', '<?= base_url('modulo2/editardiseno/') ?>' + id);

                    if (data.imagenUrl) { $('#m-imagen').attr('src', data.imagenUrl); $('#m-imagen-wrap').show(); }
                })
                .fail(function () {
                    $('#m-descripcion').text('No fue posible cargar los datos');
                });
        });
    });
</script>
<?= $this->endSection() ?>
