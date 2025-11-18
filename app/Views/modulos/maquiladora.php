<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Información de la Maquiladora</h4>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                            
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-3"><?= esc($maquiladora['Nombre_Maquila']) ?></h3>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Información de Contacto</h5>
                                <p class="mb-1">
                                    <i class="bi bi-geo-alt-fill me-2"></i>
                                    <?= esc($maquiladora['Domicilio'] ?? 'No especificado') ?>
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-telephone-fill me-2"></i>
                                    <?= esc($maquiladora['Telefono'] ?? 'No especificado') ?>
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-envelope-fill me-2"></i>
                                    <?= esc($maquiladora['Correo'] ?? 'No especificado') ?>
                                </p>
                                <?php if (!empty($maquiladora['Dueno'])): ?>
                                    <p class="mb-0">
                                        <i class="bi bi-person-badge-fill me-2"></i>
                                        Representante: <?= esc($maquiladora['Dueno']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h5 class="text-muted mb-3">Detalles Adicionales</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ID de la Maquiladora:</label>
                                    <p class="form-control bg-light"><?= $maquiladora['idmaquiladora'] ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estado:</label>
                                    <p class="form-control bg-light">Activa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Última actualización: <?= date('d/m/Y H:i') ?>
                        </small>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarMaquiladoraModal">
                            <i class="bi bi-pencil-square me-1"></i> Editar Información
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar información (puedes implementar esta funcionalidad después) -->
<div class="modal fade" id="editarMaquiladoraModal" tabindex="-1" aria-labelledby="editarMaquiladoraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarMaquiladoraModalLabel">Editar Información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center text-muted">La funcionalidad de edición estará disponible próximamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Aquí puedes agregar cualquier funcionalidad JavaScript necesaria
    document.addEventListener('DOMContentLoaded', function() {
        // Ejemplo de funcionalidad para el modal de edición
        const editarModal = document.getElementById('editarMaquiladoraModal');
        if (editarModal) {
            editarModal.addEventListener('show.bs.modal', function (event) {
                // Aquí puedes cargar los datos actuales en el formulario de edición
                console.log('Modal de edición abierto');
            });
        }
    });
</script>
<?= $this->endSection() ?>