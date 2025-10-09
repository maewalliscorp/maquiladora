<?= $this->extend('layouts/main') ?>

<!-- ====== CSS: solo DataTables (Bootstrap lo carga el layout) ====== -->
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3"><?= esc($title ?? 'Trabajo en Proceso') ?></h1>
    <span class="badge bg-primary">WIP</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Diseños</strong>
    </div>

    <div class="card-body">
        <table id="tablaWip" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Núm. Cliente</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Ver</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= esc($r['Ide']) ?></td>
                        <td><?= esc($r['numeroCliente']) ?></td>
                        <td><?= esc($r['CodigoDiseno']) ?></td>
                        <td><?= esc($r['NombreDiseno']) ?></td>
                        <td><?= esc($r['DescripcionDiseno']) ?></td>
                        <td>
                            <button
                                    type="button"
                                    class="btn btn-sm btn-outline-info ver-diseno"
                                    data-bs-toggle="modal"
                                    data-bs-target="#disenoModal"
                                    data-id="<?= esc($r['Ide']) ?>"
                                    data-numcliente="<?= esc($r['numeroCliente']) ?>"
                                    data-codigo="<?= esc($r['CodigoDiseno']) ?>"
                                    data-nombre="<?= esc($r['NombreDiseno']) ?>"
                                    data-descripcion="<?= esc($r['DescripcionDiseno']) ?>"
                                    title="Ver detalle"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ====== Modal: Detalles del diseño (centrado) ====== -->
<div class="modal fade" id="disenoModal" tabindex="-1" aria-labelledby="disenoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="disenoModalLabel">Detalle del diseño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-dark">
                <dl class="row mb-3 text-dark">
                    <dt class="col-sm-3 fw-semibold text-dark">ID</dt>
                    <dd class="col-sm-9 text-dark" id="d-id">-</dd>

                    <dt class="col-sm-3 fw-semibold text-dark">Núm. Cliente</dt>
                    <dd class="col-sm-9 text-dark" id="d-numcliente">-</dd>

                    <dt class="col-sm-3 fw-semibold text-dark">Código</dt>
                    <dd class="col-sm-9 text-dark" id="d-codigo">-</dd>

                    <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                    <dd class="col-sm-9 text-dark" id="d-nombre">-</dd>

                    <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>
                    <dd class="col-sm-9 text-dark" id="d-descripcion">-</dd>
                </dl>
            </div>

            <div class="modal-footer">
                <a id="d-editar" href="#" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<!-- ====== JS: jQuery + DataTables (Bootstrap bundle lo carga el layout) ====== -->
<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function () {
        // Evita doble inicialización si navegas con PJAX o turbolinks
        if (!$.fn.dataTable.isDataTable('#tablaWip')) {
            $('#tablaWip').DataTable({
                language: {
                    sProcessing:"Procesando...",
                    sLengthMenu:"Mostrar _MENU_",
                    sZeroRecords:"No se encontraron resultados",
                    sEmptyTable:"Sin datos",
                    sInfo:"Mostrando _START_–_END_ de _TOTAL_",
                    sInfoEmpty:"Mostrando 0–0 de 0",
                    sInfoFiltered:"(filtrado de _MAX_)",
                    sSearch:"Buscar:",
                    oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" }
                },
                columnDefs: [{ orderable:false, searchable:false, targets:[5] }]
            });
        }

        // Poblar modal
        const modalEl = document.getElementById('disenoModal');
        modalEl.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget; if (!btn) return;

            const get = a => btn.getAttribute(a) || '-';
            const id  = get('data-id');

            document.getElementById('d-id').textContent          = id;
            document.getElementById('d-numcliente').textContent  = get('data-numcliente');
            document.getElementById('d-codigo').textContent      = get('data-codigo');
            document.getElementById('d-nombre').textContent      = get('data-nombre');
            document.getElementById('d-descripcion').textContent = get('data-descripcion');

            document.getElementById('d-editar').href = "<?= site_url('diseno/editar') ?>/" + id;
        });

        // Limpieza por si queda backdrop y bloquea el click del menú
        modalEl.addEventListener('hidden.bs.modal', () => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        });
    });
</script>
<?= $this->endSection() ?>
