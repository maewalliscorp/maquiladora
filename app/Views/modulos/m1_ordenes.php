<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Órdenes de Producción</h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Órdenes de Producción</strong>
    </div>
    <div class="card-body">
        <table id="tablaOrdenes" class="table table-striped table-bordered text-center align-middle">
            <thead class="table-light">
            <tr>
                <th>OP</th>
                <th>Cliente</th>
                <th>Diseño</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?= esc($orden['op']) ?></td>
                        <td><?= esc($orden['cliente']) ?></td>
                        <td><?= esc($orden['diseno'] ?? '') ?></td>
                        <td><?= esc($orden['ini']) ?></td>
                        <td><?= esc($orden['fin']) ?></td>
                        <td>
                            <?php $estatusActual = trim($orden['estatus'] ?? ''); ?>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <select class="form-select form-select-sm op-estatus-select" data-id="<?= esc($orden['opId'] ?? '') ?>" data-prev="<?= esc($estatusActual) ?>" style="min-width: 150px;">
                                    <option value="Planificada" <?= strcasecmp($estatusActual,'Planificada')===0 ? 'selected' : '' ?>>Planificada</option>
                                    <option value="En proceso"  <?= strcasecmp($estatusActual,'En proceso')===0 ? 'selected' : '' ?>>En proceso</option>
                                    <option value="Completada"  <?= strcasecmp($estatusActual,'Completada')===0 ? 'selected' : '' ?>>Completada</option>
                                    <option value="Pausada"     <?= strcasecmp($estatusActual,'Pausada')===0 ? 'selected' : '' ?>>Pausada</option>
                                    <option value="Cancelada"   <?= strcasecmp($estatusActual,'Cancelada')===0 ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                                <div class="spinner-border spinner-border-sm text-primary op-estatus-saving" role="status" style="display:none;" aria-hidden="true"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-info btn-ver-op" data-id="<?= esc($orden['opId'] ?? '') ?>">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-agregar-op" data-id="<?= esc($orden['opId'] ?? '') ?>">
                                    <i class="bi bi-person-plus"></i> Agregar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-muted">No hay órdenes registradas</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Detalle OP (en body) -->
    <div class="modal fade" id="opDetalleModal" tabindex="-1" aria-labelledby="opDetalleLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-dark">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="opDetalleLabel">Detalle de Orden de Producción</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row mb-2">
              <dt class="col-sm-3">ID</dt><dd class="col-sm-9" id="op-id">-</dd>
              <dt class="col-sm-3">Folio</dt><dd class="col-sm-9" id="op-folio">-</dd>
              <dt class="col-sm-3">Estatus</dt><dd class="col-sm-9" id="op-status">-</dd>
              <dt class="col-sm-3">Cantidad plan</dt><dd class="col-sm-9" id="op-cant">-</dd>
              <dt class="col-sm-3">Inicio plan</dt><dd class="col-sm-9" id="op-ini">-</dd>
              <dt class="col-sm-3">Fin plan</dt><dd class="col-sm-9" id="op-fin">-</dd>
            </dl>
            <h6 class="mt-3">Diseño</h6>
            <dl class="row mb-2">
              <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9" id="op-dis-nombre">-</dd>
              <dt class="col-sm-3">Versión</dt><dd class="col-sm-9" id="op-dis-version">-</dd>
              <dt class="col-sm-3">Fecha versión</dt><dd class="col-sm-9" id="op-dis-fecha">-</dd>
              <dt class="col-sm-3">Aprobado</dt><dd class="col-sm-9" id="op-dis-aprobado">-</dd>
              <dt class="col-sm-3">Notas</dt><dd class="col-sm-9" id="op-dis-notas">-</dd>
              <dt class="col-sm-3">Archivo CAD</dt>
              <dd class="col-sm-9"><a id="op-dis-cad" href="#" target="_blank" style="display:none;">Ver CAD</a><span id="op-dis-cad-na" class="text-muted">—</span></dd>
              <dt class="col-sm-3">Archivo Patrón</dt>
              <dd class="col-sm-9"><a id="op-dis-patron" href="#" target="_blank" style="display:none;">Ver Patrón</a><span id="op-dis-patron-na" class="text-muted">—</span></dd>
            </dl>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(function(){
    // DataTable
    $('#tablaOrdenes').DataTable({
      language: {
        sProcessing:   "Procesando...",
        sLengthMenu:   "Mostrar _MENU_ registros",
        sZeroRecords:  "No se encontraron resultados",
        sEmptyTable:   "Ningún dato disponible en esta tabla",
        sInfo:         "Mostrando _START_ a _END_ de _TOTAL_",
        sInfoEmpty:    "Mostrando 0 a 0 de 0",
        sInfoFiltered: "(filtrado de _MAX_ en total)",
        sSearch:       "Buscar:",
        oPaginate:     { sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
        oAria:         { sSortAscending:": Orden asc", sSortDescending:": Orden desc" }
      }
    });

    // Guardar estatus inline
    $(document).on('change', '.op-estatus-select', function(){
      const $sel = $(this);
      const id = $sel.data('id');
      const estatus = $sel.val();
      const $td = $sel.closest('td');
      const $spin = $td.find('.op-estatus-saving');
      if (!id || !estatus) return;
      $sel.prop('disabled', true);
      $spin.show();
      $.ajax({
        url: '<?= base_url('modulo1/ordenes/estatus') ?>',
        method: 'POST',
        data: { id, estatus },
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).done(function(){
        $sel.addClass('is-valid');
        setTimeout(()=> $sel.removeClass('is-valid'), 1200);
      }).fail(function(){
        const prev = $sel.data('prev') || '';
        if (prev) $sel.val(prev);
        $sel.addClass('is-invalid');
        setTimeout(()=> $sel.removeClass('is-invalid'), 1500);
        alert('No se pudo actualizar el estatus de la OP.');
      }).always(function(){
        $sel.data('prev', estatus);
        $sel.prop('disabled', false);
        $spin.hide();
      });
    });

    // Ver detalle (modal)
    $(document).on('click', '.btn-ver-op', function(){
      const id = $(this).data('id');
      if (!id) return;
      const $modal = $('#opDetalleModal');
      const $btn = $(this);
      $btn.prop('disabled', true);
      // Limpiar
      $modal.find('#op-id,#op-folio,#op-status,#op-cant,#op-ini,#op-fin,#op-dis-nombre,#op-dis-version,#op-dis-fecha,#op-dis-aprobado,#op-dis-notas').text('-');
      $('#op-dis-cad').hide().attr('href','#'); $('#op-dis-cad-na').show();
      $('#op-dis-patron').hide().attr('href','#'); $('#op-dis-patron-na').show();
      $.getJSON('<?= base_url('modulo1/ordenes') ?>/' + id + '/json?t=' + Date.now())
        .done(function(data){
          $('#op-id').text(data.id ?? '-');
          $('#op-folio').text(data.folio || '-');
          $('#op-status').text(data.status || '-');
          $('#op-cant').text((data.cantidadPlan ?? '') || '-');
          $('#op-ini').text(data.fechaInicioPlan || '-');
          $('#op-fin').text(data.fechaFinPlan || '-');
          if (data.diseno){
            $('#op-dis-nombre').text(data.diseno.nombre || '-');
            $('#op-dis-version').text(data.diseno.version || '-');
            $('#op-dis-fecha').text(data.diseno.fecha || '-');
            const aprobado = (data.diseno.aprobado===1 || data.diseno.aprobado==='1') ? 'Sí' : (data.diseno.aprobado===0 || data.diseno.aprobado==='0' ? 'No' : '-');
            $('#op-dis-aprobado').text(aprobado);
            $('#op-dis-notas').text(data.diseno.notas || '-');
            if (data.diseno.archivoCadUrl){ $('#op-dis-cad').attr('href', data.diseno.archivoCadUrl).show(); $('#op-dis-cad-na').hide(); }
            if (data.diseno.archivoPatronUrl){ $('#op-dis-patron').attr('href', data.diseno.archivoPatronUrl).show(); $('#op-dis-patron-na').hide(); }
          }
          const modalEl = document.getElementById('opDetalleModal');
          const bsModal = new bootstrap.Modal(modalEl);
          bsModal.show();
        })
        .fail(function(){
          alert('No se pudo cargar el detalle de la orden.');
        })
        .always(function(){
          $btn.prop('disabled', false);
        });
    });
  });
</script>
<?= $this->endSection() ?>
