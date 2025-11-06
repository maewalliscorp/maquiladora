<?php /* Modal standalone para cargar por AJAX */ ?>
<div class="modal fade" id="incidenciaModal" tabindex="-1" aria-labelledby="incidenciaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="inc-form" action="<?= site_url('modulo3/incidencias/crear') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="inc-id">
      <div class="modal-header">
        <h5 class="modal-title" id="incidenciaModalLabel">
          <i class="bi bi-clipboard-plus me-2"></i><span id="inc-modal-title">Nuevo Reporte de Incidencia</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">OP (Folio)</label>
            <select id="inc-op" name="ordenProduccionFK" class="form-select">
              <option value="">-- Selecciona --</option>
              <?php foreach (($ops ?? []) as $op): ?>
                <option value="<?= (int)$op['id'] ?>"><?= esc($op['folio']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select id="inc-tipo" name="tipo" class="form-select" required>
              <option value="">-- Selecciona --</option>
              <option>Paro de máquina</option>
              <option>Falta de material</option>
              <option>Calidad</option>
              <option>Seguridad</option>
              <option>Otro</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Fecha</label>
            <input id="inc-fecha" type="date" name="fecha" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Prioridad</label>
            <select id="inc-prioridad" name="prioridad" class="form-select">
              <option>Baja</option><option>Media</option><option>Alta</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Empleado responsable</label>
            <select id="inc-empleado" name="empleadoFK" class="form-select">
              <option value="">(Sin asignar)</option>
              <?php foreach (($empleados ?? []) as $e): ?>
                <option value="<?= (int)$e['id'] ?>"><?= esc($e['nombre'] . ' ' . $e['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Acción/Seguimiento</label>
            <input id="inc-accion" name="accion" class="form-control" placeholder="Ej. Cambiar sensor, solicitar material, etc.">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea id="inc-descripcion" name="descripcion" class="form-control" rows="3" placeholder="Describe la incidencia..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="inc-submit" class="btn btn-danger" type="submit">
          <i class="bi bi-send me-1"></i><span id="inc-submit-text">Reportar</span>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
