<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Editar Pedido <?= isset($pedido['id']) ? '#'.esc($pedido['id']) : '' ?></h1>
    <span class="badge bg-primary">Módulo 1</span>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= base_url('modulo1/detalles/' . ($id ?? ($pedido['id'] ?? ''))) ?>" class="btn btn-outline-secondary btn-sm">Ver detalle</a>
        <a href="<?= base_url('modulo1') ?>" class="btn btn-volver btn-sm">← Volver</a>
    </div>
</div>

<form id="formEditarPedido" action="<?= base_url('modulo1/editar') ?>" method="POST">
    <input type="hidden" name="id" value="<?= esc($id ?? ($pedido['id'] ?? '')) ?>">
    <div class="row">
        <!-- Columna izquierda -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Cliente</strong>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div>
                            <div class="info-item">
                                <div class="info-label">Empresa:</div>
                                <div class="info-value"><?= esc($pedido['empresa'] ?? ($pedido['cliente']['nombre'] ?? '')) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Contacto:</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="estatus">
                                <?php $est = $pedido['estatus'] ?? 'Pendiente'; ?>
                                <option value="Pendiente" <?= ($est==='Pendiente')?'selected':'' ?>>Pendiente</option>
                                <option value="En proceso" <?= ($est==='En proceso')?'selected':'' ?>>En proceso</option>
                                <option value="Cancelado" <?= ($est==='Cancelado')?'selected':'' ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Moneda</label>
                            <input type="text" class="form-control" name="moneda" value="<?= esc($pedido['moneda'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="number" step="0.01" class="form-control" name="total" value="<?= esc($pedido['total'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Selección de modelo -->
                    <label class="form-label">Modelo</label>
                    <select class="form-select mb-2" id="modelo" name="modelo" required>
                        <option value="">Seleccionar...</option>
                        <option value="MODELO 1" <?= (isset($pedido['modelo']) && $pedido['modelo']=='MODELO 1')?'selected':'' ?>>MODELO 1</option>
                        <option value="MODELO 2" <?= (isset($pedido['modelo']) && $pedido['modelo']=='MODELO 2')?'selected':'' ?>>MODELO 2</option>
                        <option value="MODELO 3" <?= (isset($pedido['modelo']) && $pedido['modelo']=='MODELO 3')?'selected':'' ?>>MODELO 3</option>
                        <option value="OTRO" <?= (isset($pedido['modelo']) && $pedido['modelo']=='OTRO')?'selected':'' ?>>OTRO</option>
                    </select>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Tallas</label>
                            <input type="text" class="form-control" name="tallas" value="<?= esc($pedido['tallas'] ?? '') ?>" placeholder="S,M,L,XL">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" value="<?= esc($pedido['color'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha entrega</label>
                            <input type="date" class="form-control" name="fecha_entrega" value="<?= esc($pedido['fecha_entrega'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Vista previa del modelo -->
                    <div id="preview" class="preview-box">
                        <p class="fw-bold">Vista previa:</p>
                        <div id="preview-content"></div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Progreso (%)</label>
                        <input type="number" min="0" max="100" class="form-control" name="progreso" value="<?= esc($pedido['progreso'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="d-flex justify-content-between mt-3">
        <a href="<?= base_url('modulo1') ?>" class="btn btn-secondary-custom px-4">Cancelar</a>
        <button type="submit" id="btnActualizar" class="btn btn-primary-custom px-4">Actualizar Pedido</button>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Diccionario de modelos con imagen/descripción
    const modelos = {
        "MODELO 1": {
            desc: "Modelo 1: Camiseta clásica de algodón",
            img: "<?= base_url('img/modelo1.png') ?>"
        },
        "MODELO 2": {
            desc: "Modelo 2: Pantalón de mezclilla slim fit",
            img: "<?= base_url('img/modelo2.png') ?>"
        },
        "MODELO 3": {
            desc: "Modelo 3: Chaqueta oversize de mezclilla",
            img: "<?= base_url('img/modelo3.png') ?>"
        },
        "OTRO": {
            desc: "Otro: Modelo personalizado",
            img: "<?= base_url('img/default.png') ?>"
        }
    };

    // Función para actualizar la vista previa
    function actualizarPreview(valor) {
        const content = document.getElementById('preview-content');
        if (modelos[valor]) {
            content.innerHTML = `
            <p>${modelos[valor].desc}</p>
            <img src="${modelos[valor].img}" alt="${valor}">
        `;
        } else {
            content.innerHTML = "<p>No hay vista previa</p>";
        }
    }

    // Inicializar preview al cargar
    document.addEventListener('DOMContentLoaded', () => {
        const modelo = document.getElementById('modelo');
        actualizarPreview(modelo.value);
        modelo.addEventListener('change', () => actualizarPreview(modelo.value));

        // Manejo del envío del formulario
        document.getElementById('formEditarPedido').addEventListener('submit', function(e) {
            e.preventDefault(); // Evita el envío inmediato

            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '✅ Guardado exitosamente',
                text: 'El pedido se actualizó correctamente',
                confirmButtonText: 'OK'
            }).then(() => {
                // Enviar el formulario después de que el usuario confirme
                this.submit();
            });
        });
    });
</script>
<?= $this->endSection() ?>