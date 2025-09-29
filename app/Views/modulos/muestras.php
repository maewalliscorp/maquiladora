<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Muestras</h1>
        <span class="badge bg-success">Prototipos</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Listado de Muestras</strong>
        </div>
        <div class="card-body">
            <table id="tablaMuestras" class="table table-striped table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Prototipo</th>
                        <th>Cliente</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>PR-0001</td>
                        <td>Cliente A</td>
                        <td><span class="badge bg-warning">Pendiente</span></td>
                        <td>
                            <a href="<?= base_url('muestras/evaluar/1') ?>" class="btn btn-info btn-sm">Evaluar</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>PR-0002</td>
                        <td>Cliente B</td>
                        <td><span class="badge bg-secondary">En revisión</span></td>
                        <td>
                            <a href="<?= base_url('muestras/evaluar/2') ?>" class="btn btn-info btn-sm">Evaluar</a>
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
            $('#tablaMuestras').DataTable({ language: { sSearch: 'Buscar:' } });
        });
    </script>
<?= $this->endSection() ?>


