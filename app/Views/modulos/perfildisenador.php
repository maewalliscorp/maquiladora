<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Perfil del Diseñador</h1>
    <span class="badge bg-primary">Módulo 2</span>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Información Personal</strong>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
                </div>
                <h5><?= esc($disenador['nombre']) ?></h5>
                <p class="text-muted"><?= esc($disenador['email']) ?></p>
                <hr>
                <p><strong>Especialidad:</strong> <?= esc($disenador['especialidad']) ?></p>
                <p><strong>Experiencia:</strong> <?= esc($disenador['experiencia']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Estadísticas de Trabajo</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-primary"><?= esc($disenador['proyectos_completados']) ?></h3>
                            <p class="mb-0">Proyectos Completados</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 border rounded">
                            <h3 class="text-warning"><?= esc($disenador['proyectos_activos']) ?></h3>
                            <p class="mb-0">Proyectos Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <strong>Acciones Rápidas</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <a href="<?= base_url('modulo2/catalogodisenos') ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-collection me-2"></i>
                            Ver Catálogo
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= base_url('modulo2/agregardiseno') ?>" class="btn btn-outline-success w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuevo Diseño
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="bi bi-upload me-2"></i>
                            Subir Archivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>