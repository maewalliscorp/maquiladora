<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">EDITAR DISEÑO</h1>
    </div>

    <form action="<?= base_url('modulo2/actualizar/' . (isset($diseno['id']) ? $diseno['id'] : '')) ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Información del Diseño</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" value="<?= isset($diseno['nombre']) ? $diseno['nombre'] : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3" required><?= isset($diseno['descripcion']) ? $diseno['descripcion'] : '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Materiales</label>
                            <textarea class="form-control" name="materiales" rows="3" required><?= isset($diseno['materiales']) ? $diseno['materiales'] : '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Especificaciones Técnicas</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Información de cortes</label>
                            <textarea class="form-control" name="cortes" rows="3" required><?= isset($diseno['cortes']) ? $diseno['cortes'] : '' ?></textarea>
                        </div>

                        <!-- Modelo con imagen actual y opción para cambiar -->
                        <div class="mb-3">
                            <label class="form-label">Modelo</label>

                            <!-- Mostrar imagen actual si existe -->
                            <?php if (isset($diseno['archivo']) && !empty($diseno['archivo'])): ?>
                                <div class="mb-2">
                                    <div class="preview-title">Imagen actual:</div>
                                    <img src="<?= base_url($diseno['archivo']) ?>" alt="Modelo actual" class="current-image">
                                </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="archivo" name="archivo" accept="image/*">
                            <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>

                            <!-- Vista previa del archivo nuevo subido -->
                            <div id="archivoPreview" class="preview-box d-none">
                                <div class="preview-title">Vista previa de la nueva imagen:</div>
                                <div id="archivoContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mt-4">
            <a href="<?= base_url('modulo2/catalogodisenos') ?>" class="btn btn-danger px-4">Cancelar</a>
            <button type="submit" class="btn btn-success px-4">ACTUALIZAR</button>
        </div>
    </form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Vista previa cuando se sube un archivo de imagen
        document.getElementById('archivo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('archivoPreview');
            const content = document.getElementById('archivoContent');

            if (file) {
                preview.classList.remove('d-none');

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        content.innerHTML = `<img src="${e.target.result}" alt="Vista previa del nuevo modelo" class="img-fluid">`;
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                preview.classList.add('d-none');
            }
        });
    </script>
<?= $this->endSection() ?>