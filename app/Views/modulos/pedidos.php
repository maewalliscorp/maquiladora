<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Pedidos</h1>
    <span class="badge bg-primary">Módulo 1</span>
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
                    <dt class="col-sm-3 fw-semibold text-dark">Teléfono</dt>
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
                <a id="p-editar" href="#" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a id="p-doc" href="#" class="btn btn-outline-secondary" target="_blank" style="display:none;">
                    <i class="bi bi-file-earmark-text"></i> Documento
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
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
                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('modulo1/editar/' . (int)$p['id']) ?>">
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
                    // Fallback: si no hay diseno pero viene el arreglo disenos, tomar el último
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
    });
</script>

<?= $this->endSection() ?>