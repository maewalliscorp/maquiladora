<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Gestión de Maquilas</h1>
    <span class="badge bg-primary">Administración</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Maquilas</strong>
    </div>
    <div class="card-body">
        <p>Bienvenido al módulo de Gestión de Maquilas.</p>
        <!-- Placeholder content -->
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Esta vista ha sido creada exitosamente. Aquí podrás gestionar las maquiladoras.
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function(){
        console.log("Vista Gestión Maquilas cargada");
    });
</script>
<?= $this->endSection() ?>
