<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Inspección</h1>
        <span class="badge bg-primary">Calidad</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Pedidos para inspección</strong>
        </div>
        <div class="card-body">
            <table id="tablaInspeccion" class="table table-striped table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Empresa</th>
                        <th>Descripción</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Empresa A</td>
                        <td>Pedido de prueba</td>
                        <td><span class="estatus estatus-verde">Activo</span></td>
                        <td>
                            <a href="<?= base_url('modulo1/evaluar/1') ?>" class="btn btn-info btn-sm">Evaluar</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Empresa B</td>
                        <td>Pedido urgente</td>
                        <td><span class="estatus estatus-amarillo">Pendiente</span></td>
                        <td>
                            <a href="<?= base_url('modulo1/evaluar/2') ?>" class="btn btn-info btn-sm">Evaluar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            $('#tablaInspeccion').DataTable({ language: { sSearch: 'Buscar:' } });
        });
    </script>
<?= $this->endSection() ?>


