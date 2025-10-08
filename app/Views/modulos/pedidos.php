<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
        <div class="ms-auto">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#pedidoAddModal">
                <i class="bi bi-person-plus"></i> Agregar Pedido
            </button>
        </div>
    </div>

    <!-- Modal Bootstrap: Agregar pedido (primero datos del cliente) -->
    <div class="modal fade" id="pedidoAddModal" tabindex="-1" aria-labelledby="pedidoAddModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content text-dark">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="pedidoAddModalLabel">Agregar pedido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body text-dark">
              <!-- Paso 1: Cliente -->
              <div class="mb-3">
                <h6 class="mb-2">Cliente</h6>
                <div class="d-flex align-items-center mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pa-nuevo-cliente">
                    <label class="form-check-label ms-2" for="pa-nuevo-cliente">Nuevo cliente</label>
                  </div>
                </div>
                <div id="pa-select-wrap" class="row g-3 align-items-end">
                  <div class="col-md-8">
                    <label class="form-label">Seleccionar cliente</label>
                    <select class="form-select" id="pa-cliente-select">
                      <option value="">Cargando catálogo...</option>
                    </select>
                  </div>
                  <div class="col-md-4 text-end">
                    <div id="pa-cli-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                      <span class="visually-hidden">Cargando...</span>
                    </div>
                  </div>
                </div>
                <div class="row g-3 mt-2">
                  <input type="hidden" id="pa-cliente-id" name="cliente_id" value="">
                  <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="pa-cli-nombre" name="cli_nombre">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="pa-cli-email" name="cli_email">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Telefono</label>
                    <input type="text" class="form-control" id="pa-cli-telefono" name="cli_telefono">
                  </div>
                </div>
              </div>

              <hr>

              <!-- Paso 2: Domicilio principal -->
              <div class="mb-3">
                <h6 class="mb-2">Domicilio</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Calle</label>
                    <input type="text" class="form-control" id="pa-dir-calle" name="cli_calle">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Num. Ext</label>
                    <input type="text" class="form-control" id="pa-dir-numext" name="cli_numext">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Num. Int</label>
                    <input type="text" class="form-control" id="pa-dir-numint" name="cli_numint">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="pa-dir-ciudad" name="cli_ciudad">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <input type="text" class="form-control" id="pa-dir-estado" name="cli_estado">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">País</label>
                    <input type="text" class="form-control" id="pa-dir-pais" name="cli_pais">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">CP</label>
                    <input type="text" class="form-control" id="pa-dir-cp" name="cli_cp">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Resumen</label>
                    <input type="text" class="form-control" id="pa-dir-resumen" name="cli_dir_resumen" readonly>
                  </div>
                </div>
              </div>

              <hr>

              <!-- Paso 3: Clasificación -->
              <div class="mb-2">
                <h6 class="mb-2">Clasificación</h6>
                <div class="row g-2 align-items-end mb-2">
                  <div class="col-md-6">
                    <label class="form-label">Seleccionar clasificación</label>
                    <select class="form-select" id="pa-cla-select">
                      <option value="">Cargando catálogo...</option>
                    </select>
                  </div>
                  <div class="col-auto">
                    <div id="pa-cla-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                      <span class="visually-hidden">Cargando...</span>
                    </div>
                  </div>
                </div>
                <div class="row g-3">
                  <input type="hidden" id="pa-cla-id" name="cla_id" value="">
                  <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="pa-cla-nombre" name="cla_nombre" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="pa-cla-descripcion" name="cla_descripcion" readonly>
                  </div>
                </div>
              </div>

              <hr>

              <!-- Paso 4: Diseño -->
              <div class="mb-2">
                <h6 class="mb-2">Diseño</h6>
                <div class="row g-2 align-items-end mb-2">
                  <div class="col-md-6">
                    <label class="form-label">Seleccionar diseño</label>
                    <select class="form-select" id="pa-dis-select">
                      <option value="">Seleccionar...</option>
                    </select>
                  </div>
                  <div class="col-auto">
                    <div id="pa-dis-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                      <span class="visually-hidden">Cargando...</span>
                    </div>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="pa-dis-nombre" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Versión</label>
                    <input type="text" class="form-control" id="pa-dis-version" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Fecha versión</label>
                    <input type="text" class="form-control" id="pa-dis-fecha" readonly>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="pa-dis-descripcion" rows="2" readonly></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" id="pa-dis-notas" rows="2" readonly></textarea>
                  </div>
                </div>

                <div class="row g-3 mt-2">
                  <div class="col-md-7">
                    <label class="form-label">Imágenes</label>
                    <div id="pa-dis-carousel-wrap" style="display:none;">
                      <div id="pa-dis-carousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="pa-dis-carousel-inner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#pa-dis-carousel" data-bs-slide="prev">
                          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                          <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#pa-dis-carousel" data-bs-slide="next">
                          <span class="carousel-control-next-icon" aria-hidden="true"></span>
                          <span class="visually-hidden">Siguiente</span>
                        </button>
                      </div>
                    </div>
                    <div id="pa-dis-noimg" class="text-muted" style="display:none;">Sin imágenes disponibles</div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">Lista de materiales</label>
                    <ul class="list-group" id="pa-dis-materiales"></ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" id="pa-continuar">Continuar</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

<!-- Modal Bootstrap: Detalles del pedido -->
<div class="modal fade" id="pedidoModal" tabindex="-1" aria-labelledby="pedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="pedidoModalLabel">Detalle del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <!-- Datos generales del pedido -->
                <dl class="row mb-3 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">ID</dt>
                    <dd class="col-sm-9 text-dark" id="p-id">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Empresa</dt>
                    <dd class="col-sm-9 text-dark" id="p-empresa">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Folio</dt>
                    <dd class="col-sm-9 text-dark" id="p-folio">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Fecha</dt>
                    <dd class="col-sm-9 text-dark" id="p-fecha">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Estatus</dt>
                    <dd class="col-sm-9 text-dark" id="p-estatus">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Moneda</dt>
                    <dd class="col-sm-9 text-dark" id="p-moneda">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Total</dt>
                    <dd class="col-sm-9 text-dark" id="p-total">-</dd>
                </dl>

                <!-- Cliente -->
                <h6 class="mb-2">Cliente</h6>
                <dl class="row mb-3 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                    <dd class="col-sm-9 text-dark" id="p-cli-nombre">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Email</dt>
                    <dd class="col-sm-9 text-dark" id="p-cli-email">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Telefono</dt>
                    <dd class="col-sm-9 text-dark" id="p-cli-telefono">-</dd>
                </dl>

                <!-- Domicilio -->
                <h6 class="mb-2">Domicilio</h6>
                <dl class="row mb-3 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">Calle</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-calle">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Num. Ext</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-numext">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Num. Int</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-numint">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Ciudad</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-ciudad">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Estado</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-estado">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">CP</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-cp">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">País</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-pais">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Resumen</dt>
                    <dd class="col-sm-9 text-dark" id="p-dir-resumen">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Clasificación</dt>
                    <dd class="col-sm-9 text-dark" id="p-cli-clasificacion">-</dd>
                </dl>

                <!-- Diseño relacionado -->
                <h6 class="mt-4 mb-2">Diseño relacionado</h6>
                <dl class="row mb-3 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">Código</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-codigo">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-nombre">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-descripcion">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Versión</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-version">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Fecha versión</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-version-fecha">-</dd>
                    <dt class="col-sm-3 fw-semibold text-dark">Aprobado</dt>
                    <dd class="col-sm-9 text-dark" id="p-dis-version-aprobado">-</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <a id="p-doc" href="#" class="btn btn-outline-secondary" target="_blank" style="display:none;">
                    <i class="bi bi-file-earmark-text"></i> Documento
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap: Editar pedido (incluido dentro del content para evitar redirección) -->
<div class="modal fade" id="pedidoEditModal" tabindex="-1" aria-labelledby="pedidoEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title text-dark" id="pedidoEditModalLabel">Editar pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formPedidoEditar" action="<?= base_url('modulo1/editar') ?>" method="POST">
        <div class="modal-body text-dark">
          <input type="hidden" name="id" id="pe-id" value="">

          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Folio</label>
              <input type="text" class="form-control" name="folio" id="pe-folio">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fecha</label>
              <input type="date" class="form-control" name="fecha" id="pe-fecha">
            </div>
            <div class="col-md-3">
              <label class="form-label">Estatus</label>
              <select class="form-select" name="estatus" id="pe-estatus">
                <option value="Pendiente">Pendiente</option>
                <option value="En proceso">En proceso</option>
                <option value="Completado">Completado</option>
                <option value="Cancelado">Cancelado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Moneda</label>
              <input type="text" class="form-control" name="moneda" id="pe-moneda">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Total</label>
              <input type="number" step="0.01" class="form-control" name="total" id="pe-total">
            </div>
          </div>
          <hr>
          <!-- Secciones editables: Cliente, Domicilio y Diseño -->
          <div class="mt-4">
            <h6 class="mb-2">Cliente</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" id="pe-cli-nombre" name="cli_nombre">
              </div>
              <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="pe-cli-email" name="cli_email">
              </div>
              <div class="col-md-4">
                <label class="form-label">Telefono</label>
                <input type="text" class="form-control" id="pe-cli-telefono" name="cli_telefono">
              </div>
            </div>

            <h6 class="mb-2">Domicilio</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Calle</label>
                <input type="text" class="form-control" id="pe-dir-calle" name="cli_calle">
              </div>
              <div class="col-md-2">
                <label class="form-label">Num. Ext</label>
                <input type="text" class="form-control" id="pe-dir-numext" name="cli_numext">
              </div>
              <div class="col-md-2">
                <label class="form-label">Num. Int</label>
                <input type="text" class="form-control" id="pe-dir-numint" name="cli_numint">
              </div>
              <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="pe-dir-ciudad" name="cli_ciudad">
              </div>
              <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" class="form-control" id="pe-dir-estado" name="cli_estado">
              </div>
              <div class="col-md-2">
                <label class="form-label">País</label>
                <input type="text" class="form-control" id="pe-dir-pais" name="cli_pais">
              </div>
              <div class="col-md-2">
                <label class="form-label">CP</label>
                <input type="text" class="form-control" id="pe-dir-cp" name="cli_cp">
              </div>
              <div class="col-12">
                <label class="form-label">Resumen</label>
                <input type="text" class="form-control" id="pe-dir-resumen" name="cli_dir_resumen" readonly>
              </div>
            </div>

            <h6 class="mt-2 mb-2">Diseño relacionado</h6>
            <div class="row g-2 align-items-center mb-2">
              <div class="col-md-6">
                <label class="form-label">Seleccionar modelo/diseño</label>
                <select class="form-select" id="pe-dis-select">
                  <option value="">Seleccionar...</option>
                </select>
              </div>
              <div class="col-auto">
                <div id="pe-dis-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                  <span class="visually-hidden">Cargando...</span>
                </div>
              </div>
            </div>
            <div class="row g-2 align-items-center mb-2">
            <div class="col-auto">
              <div id="pe-dis-loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display:none;">
                <span class="visually-hidden">Cargando...</span>
              </div>
            </div>
          </div>
          <div class="row g-3 mb-0">
              <div class="col-md-3">
                <label class="form-label">Catalogo</label>
                <input type="text" class="form-control" id="pe-dis-codigo" name="dis_codigo">
              </div>
              <div class="col-md-5">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" id="pe-dis-nombre" name="dis_nombre">
              </div>
              <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" id="pe-dis-descripcion" name="dis_descripcion" rows="2"></textarea>
              </div>
              <div class="col-md-3">
                <label class="form-label">Versión</label>
                <input type="text" class="form-control" id="pe-dis-version" name="dis_version">
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha versión</label>
                <input type="date" class="form-control" id="pe-dis-version-fecha" name="dis_version_fecha">
              </div>
              <div class="col-md-3">
                <label class="form-label">Aprobado</label>
                <select class="form-select" id="pe-dis-version-aprobado" name="dis_version_aprobado">
                  <option value="">-</option>
                  <option value="1">Sí</option>
                  <option value="0">No</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Pedidos</strong>
    </div>
    <div class="card-body">
        <table id="tablaPedidos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Empresa</th>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Estatus</th>
                <th>Moneda</th>
                <th>Total</th>
                <th>Ver</th>
                <th>Editar</th>
                <th>Documento</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= esc($p['id']) ?></td>
                        <td><?= esc($p['empresa'] ?? '-') ?></td>
                        <td><?= esc($p['folio'] ?? '-') ?></td>
                        <td><?= isset($p['fecha']) ? esc(date('Y-m-d', strtotime($p['fecha']))) : '-' ?></td>
                        <td><?= esc($p['estatus'] ?? '-') ?></td>
                        <td><?= esc($p['moneda'] ?? '-') ?></td>
                        <td><?= isset($p['total']) ? number_format((float)$p['total'], 2) : '0.00' ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-ver-pedido"
                                    data-id="<?= (int)$p['id'] ?>" data-bs-toggle="modal" data-bs-target="#pedidoModal">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('modulo1/editar/' . (int)$p['id']) ?>" role="button" onclick="return false;">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                        <td>
                            <?php $docUrl = $p['documento_url'] ?? null; ?>
                            <?php if ($docUrl): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= esc($docUrl) ?>">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- JS Bootstrap + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tablaPedidos').DataTable({
            language: {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sSearch":         "Buscar:",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                }
            }
        });

        // Manejar selección de diseño
        $(document).on('change', '#pa-dis-select', function(){
          const $opt = $(this).find('option:selected');
          const id = $opt.data('id');
          const key = $(this).val();
          // Limpiar UI
          const $carWrap = $('#pa-dis-carousel-wrap');
          const $carInner = $('#pa-dis-carousel-inner');
          const $noimg = $('#pa-dis-noimg');
          const $matList = $('#pa-dis-materiales');
          $carInner.empty();
          $carWrap.hide();
          $noimg.hide();
          $matList.empty();
          $('#pa-dis-nombre, #pa-dis-version, #pa-dis-fecha, #pa-dis-descripcion, #pa-dis-notas').val('');

          if (!id) { return; }
          $('#pa-dis-loading').show();
          const url = '<?= base_url('modulo2/diseno') ?>/' + id + '/json?t=' + Date.now();
          $.getJSON(url)
            .done(function(data){
              // Campos básicos
              const nombre = pick(data, ['nombre','Nombre'], '');
              const descripcion = pick(data, ['descripcion','Descripcion'], '');
              let verObj = data && typeof data.version === 'object' ? data.version : (data.ultima_version || null);
              const version = verObj ? (verObj.version ?? verObj.ver ?? '') : (data.version ?? '');
              const fecha = verObj ? (verObj.fecha ?? '') : (data.fecha ?? '');
              const notas = verObj ? (verObj.notas ?? '') : (data.notas ?? '');
              $('#pa-dis-nombre').val(nombre || '');
              $('#pa-dis-descripcion').val(descripcion || '');
              $('#pa-dis-version').val(version || '');
              if (fecha){
                const d = new Date(fecha);
                $('#pa-dis-fecha').val(isNaN(d) ? String(fecha).slice(0,10) : d.toISOString().slice(0,10));
              } else {
                $('#pa-dis-fecha').val('');
              }
              $('#pa-dis-notas').val(notas || '');

              // Imágenes (tolerante)
              let imgs = [];
              const tryArrays = [
                data?.imagenes, data?.images, data?.fotos,
                verObj?.imagenes, verObj?.images, verObj?.fotos
              ];
              for (let i=0;i<tryArrays.length;i++){
                if (Array.isArray(tryArrays[i]) && tryArrays[i].length){ imgs = tryArrays[i]; break; }
              }
              if (imgs.length){
                imgs.forEach(function(it, idx){
                  const url = typeof it === 'string' ? it : (it.url || it.src || '');
                  if (!url) return;
                  const active = idx === 0 ? ' active' : '';
                  $carInner.append(
                    '<div class="carousel-item'+active+'">\n' +
                    '  <img class="d-block w-100" src="'+ url +'" alt="imagen '+ (idx+1) +'">\n' +
                    '</div>'
                  );
                });
                $carWrap.show();
              } else {
                $noimg.show();
              }

              // Materiales (tolerante)
              let mats = [];
              const tryMats = [data?.materiales, data?.lista_materiales, verObj?.materiales];
              for (let i=0;i<tryMats.length;i++){
                const src = tryMats[i];
                if (!src) continue;
                if (Array.isArray(src) && src.length){ mats = src; break; }
                if (typeof src === 'string'){
                  // Separar por ||, |, ;, coma o saltos de línea
                  const arr = src.split(/[|]{2,}|[|,;\n]+/).map(s=>s.trim()).filter(Boolean);
                  if (arr.length){ mats = arr; break; }
                }
              }
              if (mats.length){
                mats.forEach(function(m){
                  if (typeof m === 'string') {
                    // Intentar parsear formato "Nombre x Cantidad"
                    const parts = m.split(/\s+x\s+/i);
                    const nombre = (parts[0] || m).trim();
                    const cant = (parts[1] || '').trim();
                    const extra = cant ? ('<span class="badge bg-light text-dark">Cant: ' + cant + '</span>') : '';
                    if (nombre) {
                      $('#pa-dis-materiales').append('<li class="list-group-item d-flex justify-content-between align-items-center">'+ nombre + extra +'</li>');
                    }
                  } else if (m && typeof m === 'object') {
                    let nombre = pick(m, ['nombre','articulo','articuloNombre','descripcion'], '');
                    const artId = pick(m, ['articuloId','articulo_id','articuloID'], '');
                    if (!nombre) { nombre = artId ? ('Art ' + artId) : ''; }
                    const cant = pick(m, ['cantidad','cantidadPorUnidad','qty','cantidad_por_unidad'], '');
                    const merma = pick(m, ['merma','mermaPct','merma_pct'], '');
                    const extra = [cant ? ('Cant: ' + cant) : null, merma ? ('Merma: ' + merma) : null].filter(Boolean).join(' · ');
                    if (nombre || extra){
                      $('#pa-dis-materiales').append('<li class="list-group-item d-flex justify-content-between align-items-center">'+ (nombre||'Material') + (extra? ('<span class="badge bg-light text-dark">'+ extra +'</span>'):'') +'</li>');
                    }
                  }
                });
              }
            })
            .always(function(){ $('#pa-dis-loading').hide(); });
        });

        // Cargar catálogo de clientes al abrir el modal Agregar
        let paClientesCache = null;
        let paClasifCache = null;
        let paNuevoCliente = false;
        let paClientesMap = {};
        // Normalizador: toma la primera clave disponible del objeto
        function pick(obj, keys, def=''){
            if (!obj) return def;
            for (let i=0;i<keys.length;i++){
                const k = keys[i];
                if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null) return obj[k];
            }
            return def;
        }
        function paFillFromCliente(cli){
            if (!cli) {
                // Limpiar todos los campos cuando no hay cliente seleccionado
                $('#pa-cliente-id').val('');
                $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
                $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
                $('#pa-cla-nombre, #pa-cla-descripcion').val('');
                return;
            }
            $('#pa-cliente-id').val(cli.id ?? '');
            $('#pa-cli-nombre').val(cli.nombre || '');
            $('#pa-cli-email').val(cli.email || '');
            $('#pa-cli-telefono').val(cli.telefono || '');
            const d0 = cli.direccion_detalle || cli.direccion || {};
            const d = {
                calle:  pick(d0, ['calle','Calle']),
                numExt: pick(d0, ['numExt','num_ext','numext','numeroExterior','numero_exterior','NumeroExterior']),
                numInt: pick(d0, ['numInt','num_int','numint','numeroInterior','numero_interior','NumeroInterior']),
                ciudad: pick(d0, ['ciudad','Ciudad']),
                estado: pick(d0, ['estado','Estado','provincia','Provincia']),
                cp:     pick(d0, ['cp','CP','codigo_postal','codigoPostal','CodigoPostal','zip','ZIP']),
                pais:   pick(d0, ['pais','País','Pais','country','Country'])
            };
            $('#pa-dir-calle').val(d.calle || '');
            $('#pa-dir-numext').val(d.numExt || '');
            $('#pa-dir-numint').val(d.numInt || '');
            $('#pa-dir-ciudad').val(d.ciudad || '');
            $('#pa-dir-estado').val(d.estado || '');
            $('#pa-dir-cp').val(d.cp || '');
            $('#pa-dir-pais').val(d.pais || '');
            const resumen = [d.calle, d.numExt ? ('#'+d.numExt):null, d.numInt?('Int '+d.numInt):null, d.ciudad, d.estado, d.pais, d.cp?('CP '+d.cp):null]
                .filter(Boolean).join(', ');
            $('#pa-dir-resumen').val(resumen || '');
            const cla = cli.clasificacion || {};
            $('#pa-cla-nombre').val(cla.nombre || '');
            $('#pa-cla-descripcion').val(cla.descripcion || '');
        }

        function paToggleNuevoCliente(on){
            paNuevoCliente = !!on;
            if (paNuevoCliente){
                $('#pa-cliente-select').closest('.col-md-8').hide();
                // limpiar para captura
                $('#pa-cliente-id').val('');
                $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
                $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
                $('#pa-cla-nombre, #pa-cla-descripcion').val('');
            } else {
                $('#pa-cliente-select').closest('.col-md-8').show();
            }
        }

        $('#pedidoAddModal').on('show.bs.modal', function(){
            // limpiar
            $('#pa-cliente-select').empty().append('<option value="">Cargando catálogo...</option>');
            $('#pa-cli-nombre, #pa-cli-email, #pa-cli-telefono').val('');
            $('#pa-dir-calle, #pa-dir-numext, #pa-dir-numint, #pa-dir-ciudad, #pa-dir-estado, #pa-dir-cp, #pa-dir-pais, #pa-dir-resumen').val('');
            $('#pa-cla-nombre, #pa-cla-descripcion').val('');
            $('#pa-cliente-id').val('');
            $('#pa-nuevo-cliente').prop('checked', false);
            paToggleNuevoCliente(false);
            // invalidar cache local para asegurar datos frescos
            paClientesCache = null;

            // Preparar UI de diseños
            const $selDis = $('#pa-dis-select');
            const $spinDis = $('#pa-dis-loading');
            const $carWrap = $('#pa-dis-carousel-wrap');
            const $carInner = $('#pa-dis-carousel-inner');
            const $noimg = $('#pa-dis-noimg');
            const $matList = $('#pa-dis-materiales');
            $selDis.empty().append('<option value="">Seleccionar...</option>');
            $carInner.empty();
            $carWrap.hide();
            $noimg.hide();
            $matList.empty();
            $('#pa-dis-nombre, #pa-dis-version, #pa-dis-fecha, #pa-dis-descripcion, #pa-dis-notas').val('');

            // Cargar catálogo de diseños
            $spinDis.show();
            $.getJSON('<?= base_url('modulo2/disenos/json') ?>' + '?t=' + Date.now())
              .done(function(arr){
                const list = Array.isArray(arr) ? arr : [];
                list.forEach(function(it, idx){
                  const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : ('idx_'+idx));
                  const label = [it.codigo||'', it.nombre||''].filter(Boolean).join(' — ');
                  $selDis.append('<option value="'+ key +'" data-id="'+ (it.id ?? '') +'">'+ label +'</option>');
                });
              })
              .always(function(){ $spinDis.hide(); });

            const cargar = function(list){
                const $sel = $('#pa-cliente-select');
                $sel.empty().append('<option value="">Seleccionar...</option>');
                const keys = [];
                paClientesMap = {};
                list.forEach(function(c, idx){
                    let key = (c.id != null) ? String(c.id) : (c.codigo ? String(c.codigo) : '');
                    if (!key) { key = 'idx_' + idx; }
                    keys.push(key);
                    paClientesMap[key] = c;
                    const label = [c.nombre||'', c.email||''].filter(Boolean).join(' — ');
                    $sel.append('<option value="'+ key +'">'+ label +'</option>');
                });
                // No seleccionar automáticamente para evitar confusión; limpiar campos
                $sel.val('');
                paFillFromCliente(null);
            };

            $('#pa-cli-loading').show();
            const urlClientes = '<?= base_url('modulo1/clientes/json') ?>' + '?t=' + Date.now();
            $.getJSON(urlClientes)
                .done(function(arr){
                    paClientesCache = Array.isArray(arr) ? arr : [];
                    cargar(paClientesCache);
                })
                .always(function(){ $('#pa-cli-loading').hide(); });
        });

        $(document).on('change', '#pa-cliente-select', function(){
            const val = $(this).val();
            if (!paClientesCache) return;
            if (!val) { paFillFromCliente(null); return; }
            let cli = (val && paClientesMap && paClientesMap[val]) ? paClientesMap[val] : null;
            if (!cli && val) {
                // Fallback por id/codigo si existiera
                cli = paClientesCache.find(function(c){
                    const idMatch = String(c.id ?? '') === String(val);
                    const codMatch = String(c.codigo ?? '') === String(val);
                    return idMatch || codMatch;
                }) || null;
            }
            paFillFromCliente(cli);
        });

        $(document).on('change', '#pa-nuevo-cliente', function(){
            paToggleNuevoCliente($(this).is(':checked'));
        });

        // Botón continuar (cerrar y abrir modal de edición existente)
        $(document).on('click', '#pa-continuar', function(){
            // Por ahora solo cerramos este modal; integración de guardado vendrá después
            $('#pedidoAddModal').modal('hide');
        });

        // Abrir y poblar el modal de pedido (AJAX JSON)
        $(document).on('click', '.btn-ver-pedido', function () {
            const id = $(this).data('id');

            $('#p-id,#p-empresa,#p-folio,#p-fecha,#p-estatus,#p-moneda,#p-total').text('...');
            $('#p-cli-codigo,#p-cli-nombre,#p-cli-email,#p-cli-telefono,#p-cli-clasificacion').text('...');
            $('#p-dir-calle,#p-dir-numext,#p-dir-numint,#p-dir-ciudad,#p-dir-estado,#p-dir-cp,#p-dir-pais,#p-dir-resumen').text('...');
            // Limpiar bloque de diseño relacionado
            $('#p-dis-codigo,#p-dis-nombre,#p-dis-descripcion,#p-dis-version,#p-dis-version-fecha,#p-dis-version-aprobado').text('...');
            $('#p-editar').attr('href', '#');
            $('#p-doc').hide().attr('href','#');

            const url = '<?= base_url('modulo1/pedido') ?>/' + id + '/json';

            $.getJSON(url)
                .done(function (data) {
                    $('#p-id').text(data.id || id);
                    $('#p-empresa').text(data.empresa || '-');
                    $('#p-folio').text(data.folio || '-');
                    $('#p-fecha').text(data.fecha || '-');
                    $('#p-estatus').text(data.estatus || '-');
                    $('#p-moneda').text(data.moneda || '-');
                    $('#p-total').text(data.total || '0.00');
                    $('#p-editar').attr('href', '<?= base_url('modulo1/editar/') ?>' + id);

                    // Cliente
                    const cli = data.cliente || {};
                    $('#p-cli-codigo').text(cli.codigo || '-');
                    $('#p-cli-nombre').text(cli.nombre || '-');
                    $('#p-cli-email').text(cli.email || '-');
                    $('#p-cli-telefono').text(cli.telefono || '-');
                    const dir = (cli.direccion_detalle || {});
                    $('#p-dir-calle').text(dir.calle || '-');
                    $('#p-dir-numext').text(dir.numExt || '-');
                    $('#p-dir-numint').text(dir.numInt || '-');
                    $('#p-dir-ciudad').text(dir.ciudad || '-');
                    $('#p-dir-estado').text(dir.estado || '-');
                    $('#p-dir-cp').text(dir.cp || '-');
                    $('#p-dir-pais').text(dir.pais || '-');
                    const resumen = [dir.calle, dir.numExt ? ('#' + dir.numExt) : null, dir.numInt ? ('Int ' + dir.numInt) : null, dir.ciudad, dir.estado, dir.pais, dir.cp ? ('CP ' + dir.cp) : null]
                        .filter(Boolean).join(', ');
                    $('#p-dir-resumen').text(resumen || '-');
                    const cla = (cli.clasificacion || {});
                    const claTxt = (cla.nombre ? cla.nombre : '-') + (cla.descripcion ? (' · ' + cla.descripcion) : '');
                    $('#p-cli-clasificacion').text(claTxt);

                    if (data.documento_url) {
                        $('#p-doc').attr('href', data.documento_url).show();
                    }

                    // Diseño relacionado
                    let dis = data.diseno || null;
                    // Fallback: si no hay diseño pero viene el arreglo diseños, tomar el último
                    if (!dis && Array.isArray(data.disenos) && data.disenos.length > 0) {
                        dis = data.disenos[data.disenos.length - 1];
                    }
                    $('#p-dis-codigo').text(dis?.codigo || '-');
                    $('#p-dis-nombre').text(dis?.nombre || '-');
                    $('#p-dis-descripcion').text(dis?.descripcion || '-');
                    // La versión puede venir anidada (dis.version = { version, fecha, aprobado, ... })
                    // o plana junto con el diseño (dis.version, dis.fecha, dis.aprobado)
                    let ver = dis && (dis.version && typeof dis.version === 'object' ? dis.version : null);
                    const vNum = ver?.version ?? dis?.version ?? null;
                    const vFechaRaw = ver?.fecha ?? dis?.fecha ?? null;
                    const vAprob = ver?.aprobado ?? dis?.aprobado ?? null;
                    $('#p-dis-version').text(vNum ?? '-');
                    if (vFechaRaw) {
                        const d = new Date(vFechaRaw);
                        $('#p-dis-version-fecha').text(isNaN(d) ? String(vFechaRaw).slice(0,10) : d.toISOString().slice(0,10));
                    } else {
                        $('#p-dis-version-fecha').text('-');
                    }
                    $('#p-dis-version-aprobado').text((vAprob === 1 || vAprob === true || vAprob === '1') ? 'Sí' : (vAprob === 0 || vAprob === false || vAprob === '0' ? 'No' : '-'));
                })
                .fail(function () {
                    $('#p-empresa').text('No fue posible cargar los datos');
                    console.error('Error cargando', url, arguments);
                    $('#p-dis-codigo,#p-dis-nombre,#p-dis-descripcion,#p-dis-version,#p-dis-version-fecha,#p-dis-version-aprobado').text('-');
                });
        });

        // Cargar datos en el modal de edición
        function cargarPedidoEnModal(id){
            const url = '<?= base_url('modulo1/pedido') ?>/' + id + '/json';
            // limpiar
            $('#pe-id').val(id);
            $('#pe-folio, #pe-fecha, #pe-estatus, #pe-moneda, #pe-total, #pe-progreso, #pe-descripcion, #pe-cantidad, #pe-fechaentrega, #pe-modelo, #pe-tallas, #pe-color, #pe-materiales, #pe-especificaciones').val('');
            // limpiar bloques informativos
            $('#pe-empresa, #pe-dir, #pe-dis').text('-');
            $('#pe-cli-nombre, #pe-cli-email, #pe-cli-telefono').val('');
            $('#pe-dir-calle, #pe-dir-numext, #pe-dir-numint, #pe-dir-ciudad, #pe-dir-estado, #pe-dir-cp, #pe-dir-pais, #pe-dir-resumen').val('');
            $('#pe-dis-codigo, #pe-dis-nombre, #pe-dis-descripcion, #pe-dis-version, #pe-dis-version-fecha').val('');
            $('#pe-dis-version-aprobado').val('');
            // limpiar selector de diseños
            const $selDis = $('#pe-dis-select');
            if ($selDis.length){
              $selDis.empty().append('<option value="">Seleccionar...</option>');
            }
            $('#pe-dis-loading').hide();

            // mostrar spinner de diseño
            $('#pe-dis-loading').show();

            $.getJSON(url).done(function(data){
                $('#pe-folio').val(data.folio || '');
                $('#pe-fecha').val(data.fecha || '');
                $('#pe-estatus').val(data.estatus || 'Pendiente');
                $('#pe-moneda').val(data.moneda || '');
                const total = (data.total||'').toString().replace(/,/g,'');
                $('#pe-total').val(total || '');
                $('#pe-progreso').val(data.progreso || '');
                $('#pe-descripcion').val(data.descripcion || '');
                $('#pe-cantidad').val(data.cantidad || '');
                $('#pe-fechaentrega').val(data.fecha_entrega || '');
                $('#pe-modelo').val(data.modelo || '');
                $('#pe-tallas').val(data.tallas || '');
                $('#pe-color').val(data.color || '');
                $('#pe-materiales').val(data.materiales || '');
                $('#pe-especificaciones').val(data.especificaciones || '');

                const cli = data.cliente || {};
                $('#pe-empresa').text(cli.nombre || (data.empresa||'-'));
                $('#pe-cli-nombre').val(cli.nombre || '');
                $('#pe-cli-email').val(cli.email || '');
                $('#pe-cli-telefono').val(cli.telefono || '');
                const d0 = cli.direccion_detalle || cli.direccion || {};
                const d = {
                  calle:  pick(d0, ['calle','Calle']),
                  numExt: pick(d0, ['numExt','num_ext','numext','numeroExterior','numero_exterior','NumeroExterior']),
                  numInt: pick(d0, ['numInt','num_int','numint','numeroInterior','numero_interior','NumeroInterior']),
                  ciudad: pick(d0, ['ciudad','Ciudad']),
                  estado: pick(d0, ['estado','Estado','provincia','Provincia']),
                  cp:     pick(d0, ['cp','CP','codigo_postal','codigoPostal','CodigoPostal','zip','ZIP']),
                  pais:   pick(d0, ['pais','País','Pais','country','Country'])
                };
                $('#pe-dir-calle').val(d.calle || '');
                $('#pe-dir-numext').val(d.numExt || '');
                $('#pe-dir-numint').val(d.numInt || '');
                $('#pe-dir-ciudad').val(d.ciudad || '');
                $('#pe-dir-estado').val(d.estado || '');
                $('#pe-dir-cp').val(d.cp || '');
                $('#pe-dir-pais').val(d.pais || '');
                const dirTxt = [d.calle, d.numExt ? ('#'+d.numExt) : null, d.numInt ? ('Int '+d.numInt) : null, d.ciudad, d.estado, d.pais, d.cp ? ('CP '+d.cp) : null]
                    .filter(Boolean).join(', ');
                $('#pe-dir-resumen').val(dirTxt || '');
                let dis = data.diseno || null;
                // Rellenar selector con lista de diseños, si existe
                const lista = Array.isArray(data.disenos) ? data.disenos : [];
                const mapByKey = {};
                if ($selDis.length){
                  $selDis.empty().append('<option value="">Seleccionar...</option>');
                  lista.forEach((it, idx) => {
                    const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : String(idx));
                    mapByKey[key] = it;
                    const label = [it.codigo||'', it.nombre||''].filter(Boolean).join(' — ');
                    $selDis.append(`<option value="${key}">${label}</option>`);
                  });
                }

                // Fallback: si la lista viene vacía o trae solo 1 (suele ser el actual), cargar catálogo completo
                if ($selDis.length && (!lista || lista.length <= 1)) {
                  $('#pe-dis-loading').show();
                  $.getJSON('<?= base_url('modulo2/disenos/json') ?>')
                    .done(function(cat){
                      const arr = Array.isArray(cat) ? cat : [];
                      $selDis.empty().append('<option value="">Seleccionar...</option>');
                      for (let i = 0; i < arr.length; i++) {
                        const it = arr[i] || {};
                        const key = (it.id != null) ? String(it.id) : (it.codigo ? String(it.codigo) : String(i));
                        mapByKey[key] = it;
                        const label = [it.codigo || '', it.nombre || ''].filter(Boolean).join(' — ');
                        $selDis.append('<option value="' + key + '\">' + label + '</option>');
                      }

                      // Mantener selección actual si venía en data.diseno
                      if (dis) {
                        const selKey = (dis.id != null) ? String(dis.id) : (dis.codigo ? String(dis.codigo) : '');
                        if (selKey && mapByKey[selKey]) $selDis.val(selKey);
                      }

                      // Si no hay selección aún, elegir el primero
                      if (!$selDis.val() && arr.length) {
                        const firstKey = (arr[0].id != null) ? String(arr[0].id) : (arr[0].codigo ? String(arr[0].codigo) : '0');
                        $selDis.val(firstKey);
                        dis = mapByKey[firstKey];
                      }

                      // Rellenar campos con la selección final
                      fillDesignFields(dis || null);
                    })
                    .always(function(){
                      $('#pe-dis-loading').hide();
                    });
                }

                function fillDesignFields(dx){
                  $('#pe-dis').text(dx ? ((dx.codigo||'') + ' ' + (dx.nombre||'')) : '-');
                  $('#pe-dis-codigo').val(dx?.codigo || '');
                  $('#pe-dis-nombre').val(dx?.nombre || '');
                  $('#pe-dis-descripcion').val(dx?.descripcion || '');
                  let ver = dx && (dx.version && typeof dx.version === 'object' ? dx.version : null);
                  const vNum = ver?.version ?? dx?.version ?? null;
                  const vFechaRaw = ver?.fecha ?? dx?.fecha ?? null;
                  const vAprob = ver?.aprobado ?? dx?.aprobado ?? null;
                  $('#pe-dis-version').val(vNum ?? '');
                  if (vFechaRaw) {
                      const dt = new Date(vFechaRaw);
                      $('#pe-dis-version-fecha').val(isNaN(dt) ? String(vFechaRaw).slice(0,10) : dt.toISOString().slice(0,10));
                  } else {
                      $('#pe-dis-version-fecha').val('');
                  }
                  $('#pe-dis-version-aprobado').val((vAprob === 1 || vAprob === true || vAprob === '1') ? '1' : (vAprob === 0 || vAprob === false || vAprob === '0' ? '0' : ''));
                }

                // Seleccionar por defecto el actual si viene y existe en la lista
                if ($selDis.length){
                  let selectedKey = '';
                  if (dis){
                    selectedKey = (dis.id != null) ? String(dis.id) : (dis.codigo ? String(dis.codigo) : '');
                    $selDis.val(selectedKey);
                  }
                  // Si no hay diseno actual pero hay lista, seleccionar el primero
                  if (!$selDis.val() && lista.length){
                    const firstKey = (lista[0].id != null) ? String(lista[0].id) : (lista[0].codigo ? String(lista[0].codigo) : '0');
                    $selDis.val(firstKey);
                    dis = mapByKey[firstKey];
                  }

                  // Rellenar campos con la selección
                  fillDesignFields(dis || null);

                  // Manejar cambios de selección
                  $selDis.off('change').on('change', function(){
                    const key = $(this).val();
                    const dx = mapByKey[key] || null;
                    fillDesignFields(dx);
                  });
                } else {
                  // Sin selector, solo rellenar con el actual
                  fillDesignFields(dis || null);
                }
                
                $('#pe-dis-loading').hide();
            }).fail(function(){
                console.error('No fue posible cargar el detalle del pedido', id);
                $('#pe-dis-loading').hide();
            });
        }

        // Interceptar clic al lápiz en la tabla (no navegar, abrir modal)
        $(document).on('click', 'a[href*="/modulo1/editar/"]', function (e) {
            const href = $(this).attr('href') || '';
            const m = href.match(/\/modulo1\/editar\/(\d+)/);
            if (m) {
                e.preventDefault();
                const id = parseInt(m[1]);
                cargarPedidoEnModal(id);
                $('#pedidoEditModal').modal('show');
            }
        });

        // Interceptar botón Editar dentro del modal de Ver (si existe)
        $(document).on('click', '#p-editar', function (e) {
            e.preventDefault();
            const id = parseInt(($('#p-id').text()||'').trim()) || null;
            if (id) {
                cargarPedidoEnModal(id);
                $('#pedidoEditModal').modal('show');
            }
        });

        // Submit del formulario de edición por AJAX (sin redirección)
        $(document).on('submit', '#formPedidoEditar', function (e) {
            e.preventDefault();
            const $form = $(this);
            const url = $form.attr('action');
            const fd = new FormData(this);
            if (fd.has('total')) {
                const t = (fd.get('total')||'').toString().replace(/,/g,'');
                fd.set('total', t);
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(resp){
                    if (resp && resp.success) {
                        $('#pedidoEditModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error al actualizar: ' + (resp && resp.message ? resp.message : 'Error desconocido'));
                    }
                },
                error: function(){
                    alert('Error de conexión al actualizar el pedido');
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>
<!-- Modal Bootstrap: Editar pedido -->
<div class="modal fade" id="pedidoEditModal" tabindex="-1" aria-labelledby="pedidoEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title text-dark" id="pedidoEditModalLabel">Editar pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formPedidoEditar" action="<?= base_url('modulo1/editar') ?>" method="POST">
        <div class="modal-body text-dark">
          <input type="hidden" name="id" id="pe-id" value="">

          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Folio</label>
              <input type="text" class="form-control" name="folio" id="pe-folio">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fecha</label>
              <input type="date" class="form-control" name="fecha" id="pe-fecha">
            </div>
            <div class="col-md-3">
              <label class="form-label">Estatus</label>
              <select class="form-select" name="estatus" id="pe-estatus">
                <option value="Pendiente">Pendiente</option>
                <option value="En proceso">En proceso</option>
                <option value="Completado">Completado</option>
                <option value="Cancelado">Cancelado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Moneda</label>
              <input type="text" class="form-control" name="moneda" id="pe-moneda">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Total</label>
              <input type="number" step="0.01" class="form-control" name="total" id="pe-total">
            </div>
            <div class="col-md-6">
              <label class="form-label">Progreso (%)</label>
              <input type="number" min="0" max="100" class="form-control" name="progreso" id="pe-progreso">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripciï¿½n</label>
            <textarea class="form-control" name="descripcion" id="pe-descripcion" rows="2"></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Cantidad</label>
              <input type="number" class="form-control" name="cantidad" id="pe-cantidad">
            </div>
            <div class="col-md-4">
              <label class="form-label">Fecha entrega</label>
              <input type="date" class="form-control" name="fecha_entrega" id="pe-fechaentrega">
            </div>
            <div class="col-md-4">
              <label class="form-label">Modelo</label>
              <select class="form-select" name="modelo" id="pe-modelo">
                <option value="">Seleccionar...</option>
                <option value="MODELO 1">MODELO 1</option>
                <option value="MODELO 2">MODELO 2</option>
                <option value="MODELO 3">MODELO 3</option>
                <option value="OTRO">OTRO</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-0">
            <div class="col-md-4">
              <label class="form-label">Tallas</label>
              <input type="text" class="form-control" name="tallas" id="pe-tallas" placeholder="S,M,L,XL">
            </div>
            <div class="col-md-4">
              <label class="form-label">Color</label>
              <input type="text" class="form-control" name="color" id="pe-color">
            </div>
            <div class="col-md-4">
              <label class="form-label">Materiales</label>
              <input type="text" class="form-control" name="materiales" id="pe-materiales">
            </div>
          </div>

          <div class="mt-2">
            <label class="form-label">Especificaciones</label>
            <textarea class="form-control" name="especificaciones" id="pe-especificaciones" rows="2"></textarea>
          </div>

          <hr>
          <div class="small text-muted">
            <strong>Cliente</strong>: <span id="pe-empresa">-</span>
            &nbsp;|&nbsp;
            <strong>Direcciï¿½n</strong>: <span id="pe-dir">-</span>
            &nbsp;|&nbsp;
            <strong>Diseï¿½o</strong>: <span id="pe-dis">-</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  function cargarPedidoEnModal(id){
    const url = '<?= base_url('modulo1/pedido') ?>/' + id + '/json';
    // limpiar
    document.getElementById('pe-id').value = id;
    ['folio','fecha','estatus','moneda','total','progreso','descripcion','cantidad','fechaentrega','modelo','tallas','color','materiales','especificaciones']
      .forEach(k=>{
        const el = document.getElementById('pe-' + k);
        if(el){ if(el.tagName === 'SELECT' || el.tagName === 'INPUT'){ el.value=''; } else { el.textContent=''; } }
      });
    document.getElementById('pe-empresa').textContent='-';
    document.getElementById('pe-dir').textContent='-';
    document.getElementById('pe-dis').textContent='-';

    fetch(url).then(r=>r.json()).then(data=>{
      document.getElementById('pe-folio').value = data.folio || '';
      document.getElementById('pe-fecha').value = data.fecha || '';
      document.getElementById('pe-estatus').value = data.estatus || 'Pendiente';
      document.getElementById('pe-moneda').value = data.moneda || '';
      const total = (data.total||'').toString().replace(/,/g,'');
      document.getElementById('pe-total').value = total || '';
      document.getElementById('pe-progreso').value = data.progreso || '';

      document.getElementById('pe-descripcion').value = data.descripcion || '';
      document.getElementById('pe-cantidad').value = data.cantidad || '';
      document.getElementById('pe-fechaentrega').value = data.fecha_entrega || '';
      document.getElementById('pe-modelo').value = data.modelo || '';
      document.getElementById('pe-tallas').value = data.tallas || '';
      document.getElementById('pe-color').value = data.color || '';
      document.getElementById('pe-materiales').value = data.materiales || '';
      document.getElementById('pe-especificaciones').value = data.especificaciones || '';

      const cli = data.cliente || {};
      document.getElementById('pe-empresa').textContent = cli.nombre || (data.empresa||'-');
      const d = cli.direccion_detalle || {};
      const dirTxt = [d.calle, d.numExt, d.numInt, d.ciudad, d.estado, d.pais, d.cp].filter(Boolean).join(', ');
      document.getElementById('pe-dir').textContent = dirTxt || '-';
      const dis = data.diseno || null;
      document.getElementById('pe-dis').textContent = dis ? ((dis.codigo||'') + ' ' + (dis.nombre||'')) : '-';
    }).catch(()=>{
      console.error('No fue posible cargar el detalle del pedido', id);
    });
  }

  // Interceptar botï¿½n Editar del modal de Ver
  document.addEventListener('click', function(ev){
    const btn = ev.target.closest('#p-editar');
    if(btn){
      ev.preventDefault();
      // Tomar ID que ya estï¿½ en el modal de ver
      const idTxt = document.getElementById('p-id')?.textContent?.trim() || '';
      const id = parseInt(idTxt) || null;
      if(id){
        cargarPedidoEnModal(id);
        const modalEl = document.getElementById('pedidoEditModal');
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    }
  });

  // Interceptar clic al lï¿½piz en la tabla (href actual redirige, lo transformamos en modal)
  document.addEventListener('click', function(ev){
    const a = ev.target.closest('a.btn.btn-sm.btn-outline-primary');
    if(!a) return;
    const href = a.getAttribute('href')||'';
    if(href.includes('/modulo1/editar/')){
      ev.preventDefault();
      const match = href.match(/\/modulo1\/editar\/(\d+)/);
      const id = match ? parseInt(match[1]) : null;
      if(id){
        cargarPedidoEnModal(id);
        const modalEl = document.getElementById('pedidoEditModal');
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    }
  });

  // Interceptar submit del formulario de edición para evitar redirección
  document.addEventListener('submit', function(ev){
    const form = ev.target.closest('#formPedidoEditar');
    if(!form) return;
    ev.preventDefault();
    const url = form.getAttribute('action');
    const fd = new FormData(form);
    // Asegurar que total vaya numérico sin comas
    if (fd.has('total')) {
      const t = (fd.get('total')||'').toString().replace(/,/g,'');
      fd.set('total', t);
    }
    fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(json => {
        if (json && json.success) {
          // Cerrar modal y recargar para ver cambios
          const modalEl = document.getElementById('pedidoEditModal');
          const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          inst.hide();
          location.reload();
        } else {
          alert('Error al actualizar: ' + (json && json.message ? json.message : 'Error desconocido'));
        }
      })
      .catch(() => {
        alert('Error de conexión al actualizar el pedido');
      });
  });
})();
</script>

