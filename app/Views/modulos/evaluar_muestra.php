<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Evaluar Muestra</h1>
        <span class="badge bg-primary">Módulo Muestras</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Formulario de Evaluación de Muestra</strong>
        </div>
        <div class="card-body">
            <form id="formEvaluacionMuestra" action="<?= base_url('muestras/guardar-evaluacion') ?>" method="POST">
                <input type="hidden" name="muestra_id" value="<?= $muestra_id ?>">

                <h5>Información de la Muestra</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigo_prototipo">Código de Prototipo:</label>
                            <input type="text" class="form-control" id="codigo_prototipo" value="<?= $muestra_data['prototipo_codigo'] ?? '' ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="solicitado_por">Solicitado Por:</label>
                            <input type="text" class="form-control" id="solicitado_por" value="<?= $muestra_data['solicitadaPor'] ?? '' ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="archivos-section">
                    <h6>Archivos del Diseño</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Archivo CAD:</label>
                                <?php if(!empty($muestra_data['archivoCadUrl'])): ?>
                                    <a href="<?= $muestra_data['archivoCadUrl'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">📥 Descargar CAD</a>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Archivo Patrón:</label>
                                <?php if(!empty($muestra_data['archivoPatronUrl'])): ?>
                                    <a href="<?= $muestra_data['archivoPatronUrl'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">📥 Descargar Patrón</a>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="decision">Decisión de Aprobación:</label>
                    <select class="form-control" id="decision" name="decision" required>
                        <option value="">Seleccione una decisión</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                        <option value="Pendiente">Pendiente Modificaciones</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="realizar_control_calidad" name="realizar_control_calidad" onchange="toggleControlCalidad()">
                    <label class="form-check-label" for="realizar_control_calidad">Realizar Control de Calidad</label>
                </div>

                <div id="seccionControlCalidad" class="seccion-control-calidad">
                    <h5>Control de Calidad del Prototipo</h5>
                    <div class="form-group">
                        <label for="resultado_control">Resultado del Control:</label>
                        <select class="form-control" id="resultado_control" name="resultado_control">
                            <option value="">Seleccione resultado</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Rechazado">Rechazado</option>
                            <option value="Condicional">Condicional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hallazgos">Hallazgos:</label>
                        <textarea class="form-control" id="hallazgos" name="hallazgos" rows="3" placeholder="Describa los hallazgos..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="responsable_id">Responsable:</label>
                        <select class="form-control" id="responsable_id" name="responsable_id">
                            <option value="">Seleccione responsable</option>
                            <?php foreach($responsables as $responsable): ?>
                                <option value="<?= $responsable['id'] ?>"><?= $responsable['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar Evaluación</button>
                    <a href="<?= base_url('muestras') ?>" class="btn btn-danger px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        function toggleControlCalidad() {
            const realizarControl = document.getElementById('realizar_control_calidad').checked;
            const seccionControl = document.getElementById('seccionControlCalidad');
            seccionControl.style.display = realizarControl ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function() { toggleControlCalidad(); });
    </script>
<?= $this->endSection() ?>


