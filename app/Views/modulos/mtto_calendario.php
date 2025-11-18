<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    #calMtto{
        min-height: 650px;
        background:#fff;
        border-radius:.75rem;
        padding:.75rem;
        box-shadow:0 6px 18px rgba(0,0,0,.06);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Calendario de Mantenimiento</h2>

        <div class="d-flex gap-2">
            <!-- Botón para abrir el modal manualmente -->
            <button type="button"
                    class="btn btn-primary"
                    id="btnNuevoMtto"
                    data-bs-toggle="modal"
                    data-bs-target="#mttoModal">
                <i class="bi bi-plus-circle"></i> Nuevo mantenimiento
            </button>

            <!-- Acceso a la vista tipo lista -->
            <a href="<?= site_url('mtto/programacion') ?>" class="btn btn-outline-light">
                <i class="bi bi-list-ul"></i> Ver Programación (lista)
            </a>
        </div>
    </div>

    <div id="calMtto"></div>
</div>

<!-- ===================== MODAL: NUEVO MANTENIMIENTO ===================== -->
<div class="modal fade" id="mttoModal" tabindex="-1" aria-labelledby="mttoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formMtto" action="<?= site_url('mtto/programacion/guardar') ?>" method="post">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title" id="mttoModalLabel">
                        Programar mantenimiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- EQUIPO / MÁQUINA -->
                        <div class="col-md-6">
                            <label for="maquina_id" class="form-label">Equipo / Máquina</label>
                            <select name="maquina_id" id="maquina_id" class="form-select" required>
                                <option value="">Seleccione un equipo...</option>
                                <?php if (!empty($maquinas ?? [])): ?>
                                    <?php foreach ($maquinas as $m): ?>
                                        <option value="<?= esc($m['id']) ?>">
                                            <?= esc($m['codigo'] ?? '') ?>
                                            <?php if (!empty($m['modelo'])): ?>
                                                - <?= esc($m['modelo']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- RESPONSABLE -->
                        <div class="col-md-6">
                            <label for="responsable_id" class="form-label">Responsable</label>
                            <select name="responsable_id" id="responsable_id" class="form-select">
                                <option value="">Seleccione un responsable...</option>
                                <?php if (!empty($empleados ?? [])): ?>
                                    <?php foreach ($empleados as $e): ?>
                                        <option value="<?= esc($e['id']) ?>">
                                            <?= esc($e['noEmpleado'] ?? '') ?> -
                                            <?= esc(($e['nombre'] ?? '') . ' ' . ($e['apellido'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- FECHA PROGRAMADA -->
                        <div class="col-md-3">
                            <label for="fecha_programada" class="form-label">Fecha programada</label>
                            <input type="date"
                                   name="fecha_programada"
                                   id="fecha_programada"
                                   class="form-control"
                                   required>
                        </div>

                        <!-- HORA OPCIONAL -->
                        <div class="col-md-3">
                            <label for="hora_programada" class="form-label">Hora (opcional)</label>
                            <input type="time"
                                   name="hora_programada"
                                   id="hora_programada"
                                   class="form-control">
                        </div>

                        <!-- TIPO DE MANTENIMIENTO -->
                        <div class="col-md-3">
                            <label for="tipo_mtto" class="form-label">Tipo de mantenimiento</label>
                            <select name="tipo_mtto" id="tipo_mtto" class="form-select" required>
                                <option value="Preventivo">Preventivo</option>
                                <option value="Correctivo">Correctivo</option>
                            </select>
                        </div>

                        <!-- PRIORIDAD -->
                        <div class="col-md-3">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <select name="prioridad" id="prioridad" class="form-select">
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>

                        <!-- DESCRIPCIÓN / ACTIVIDAD -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Actividad / descripción</label>
                            <textarea name="descripcion"
                                      id="descripcion"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Describe brevemente la actividad de mantenimiento a realizar"></textarea>
                        </div>

                    </div> <!-- row -->
                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Guardar mantenimiento
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<!-- ===================================================================== -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el         = document.getElementById('calMtto');
        const modalEl    = document.getElementById('mttoModal');
        const fechaInput = document.getElementById('fecha_programada');
        const formMtto   = document.getElementById('formMtto');

        const mttoModal = (typeof bootstrap !== 'undefined' && modalEl)
            ? new bootstrap.Modal(modalEl)
            : null;

        const calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true,
            nowIndicator: true,
            weekNumbers: true,
            events: '<?= site_url('mtto/api/eventos') ?>',

            // Click en día del calendario → abre modal con la fecha seleccionada
            dateClick: function(info) {
                if (fechaInput) {
                    fechaInput.value = info.dateStr; // YYYY-MM-DD
                }
                if (mttoModal) {
                    mttoModal.show();
                }
            }
        });

        calendar.render();

        // Botón "Nuevo mantenimiento" → pone hoy por defecto si no hay fecha
        const btnNuevo = document.getElementById('btnNuevoMtto');
        if (btnNuevo && fechaInput) {
            btnNuevo.addEventListener('click', () => {
                if (!fechaInput.value) {
                    const hoy  = new Date();
                    const yyyy = hoy.getFullYear();
                    const mm   = String(hoy.getMonth() + 1).padStart(2, '0');
                    const dd   = String(hoy.getDate()).padStart(2, '0');
                    fechaInput.value = `${yyyy}-${mm}-${dd}`;
                }
            });
        }

        // ================= SweetAlert: confirmar guardado =================
        if (formMtto) {
            formMtto.addEventListener('submit', function (e) {
                e.preventDefault(); // detenemos el submit normal

                Swal.fire({
                    title: '¿Guardar mantenimiento?',
                    text: 'Se programará el mantenimiento para la máquina seleccionada.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formMtto.submit(); // ahora sí enviamos el formulario
                    }
                });
            });
        }
    });
</script>

<?php if ($msg = session()->getFlashdata('msg_mtto')): ?>
    <script>
        // ================= SweetAlert: mensaje después de guardar =================
        document.addEventListener('DOMContentLoaded', () => {
            const msg = <?= json_encode($msg) ?>;
            const isError = msg.toLowerCase().startsWith('error');

            Swal.fire({
                title: isError ? 'Error' : 'Mantenimiento',
                text: msg,
                icon: isError ? 'error' : 'success',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
