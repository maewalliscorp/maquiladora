<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Evaluar Orden</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Formulario de Evaluación</strong>
        </div>
        <div class="card-body">
            <form id="formEvaluacion" action="<?= base_url('modulo1/guardar-evaluacion') ?>" method="POST">
                <input type="hidden" name="orden_id" value="<?= $orden_id ?>">

                <!-- Datos básicos de la orden -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="folio">Folio:</label>
                            <input type="text" class="form-control" id="folio" name="folio" value="<?= $orden_data['folio'] ?? '' ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cantidad_plan">Cantidad Planificada:</label>
                            <input type="text" class="form-control" id="cantidad_plan" name="cantidad_plan" value="<?= $orden_data['cantidadPlan'] ?? '' ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Detección de defectos -->
                <div class="form-group">
                    <label for="defecto_detectado">¿Se detectó algún defecto?</label>
                    <select class="form-control" id="defecto_detectado" name="defecto_detectado" onchange="toggleSeccionDefectos()">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>

                <!-- Sección de defectos (se muestra solo si hay defectos) -->
                <div id="seccionDefectos" class="seccion-defectos">
                    <h5>Registro de Defectos</h5>

                    <!-- Selección de defecto -->
                    <div class="form-group">
                        <label for="defecto_id">Tipo de Defecto:</label>
                        <select class="form-control" id="defecto_id" name="defecto_id">
                            <option value="">Seleccione un defecto</option>
                            <?php foreach($defectos as $defecto): ?>
                                <option value="<?= $defecto['id'] ?>">
                                    <?= $defecto['codigo'] ?> - <?= $defecto['description'] ?> (<?= $defecto['severidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cantidad y ubicación -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad_defecto">Cantidad:</label>
                                <input type="number" class="form-control" id="cantidad_defecto" name="cantidad_defecto" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ubicacion_pieza">Ubicación de la Pieza:</label>
                                <input type="text" class="form-control" id="ubicacion_pieza" name="ubicacion_pieza">
                            </div>
                        </div>
                    </div>

                    <!-- Información de reproceso (si aplica) -->
                    <div id="seccionReproceso" class="mt-3">
                        <h6>Información de Reproceso</h6>
                        <div class="form-group">
                            <label for="accion_reproceso">Acción de Reproceso:</label>
                            <input type="text" class="form-control" id="accion_reproceso" name="accion_reproceso">
                        </div>
                        <div class="form-group">
                            <label for="cantidad_reproceso">Cantidad a Reprocesar:</label>
                            <input type="number" class="form-control" id="cantidad_reproceso" name="cantidad_reproceso" min="1">
                        </div>
                    </div>
                </div>

                <!-- Observaciones generales -->
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                </div>

                <!-- Resultado de la inspección -->
                <div class="form-group">
                    <label for="resultado">Resultado de la Inspección:</label>
                    <select class="form-control" id="resultado" name="resultado">
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                        <option value="Pendiente">Pendiente</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar Evaluación</button>
                    <a href="<?= base_url('modulo3/inspeccion') ?>" class="btn btn-danger px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        function toggleSeccionDefectos() {
            const defectoDetectado = document.getElementById('defecto_detectado').value;
            const seccionDefectos = document.getElementById('seccionDefectos');

            if (defectoDetectado === '1') {
                seccionDefectos.style.display = 'block';
            } else {
                seccionDefectos.style.display = 'none';
            }
        }

        // Inicializar estado del formulario
        document.addEventListener('DOMContentLoaded', function() {
            toggleSeccionDefectos();
        });
    </script>
<?= $this->endSection() ?>