<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $hasEmpleado = !empty($empleado); ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Perfil del Empleado</h1>
    <span class="badge bg-primary">Módulo 1</span>
    <div class="ms-auto">
        <?php if ($hasEmpleado): ?>
            <button type="button" class="btn btn-primary" id="btnEditarEmp">Editar</button>
        <?php else: ?>
            <button type="button" class="btn btn-success" id="btnAgregarEmp">Agregar</button>
        <?php endif; ?>
    </div>
    </div>
<div class="row justify-content-center">
    <!-- Foto -->
    <div class="col-md-3 text-center">
        <div class="card shadow-sm">
            <div class="card-body">
                <img src="<?= base_url('assets/img/avatar.png') ?>" alt="Foto" class="profile-img">
                <button class="btn btn-primary btn-upload">Subir Foto</button>
            </div>
        </div>
    </div>

    <!-- Datos -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Información Personal</strong>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <h6>Datos Personales</h6>
                    <p><strong>Nombre completo:</strong> <?= esc(trim(($empleado['nombre'] ?? '').' '.($empleado['apellido'] ?? ''))) ?></p>
                    <p><strong>Usuario:</strong> <?= esc($empleado['username'] ?? '') ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <?= esc($empleado['fecha_nac'] ?? '') ?></p>
                    <p><strong>CURP:</strong> <?= esc($empleado['curp'] ?? '') ?></p>
                    <p><strong>Edad:</strong> <?= esc($empleado['edad'] ?? '') ?></p>
                </div>

                <div class="info-section">
                    <h6>Contacto</h6>
                    <p><strong>Domicilio:</strong> <?= esc($empleado['domicilio'] ?? '') ?></p>
                    <p><strong>Teléfono:</strong> <?= esc($empleado['telefono'] ?? '') ?></p>
                    <p><strong>Email:</strong> <?= esc($empleado['email'] ?? ($empleado['correo'] ?? '')) ?></p>
                </div>

                <div class="info-section">
                    <h6>Información Laboral</h6>
                    <p><strong>Puesto:</strong> <?= esc($empleado['puesto'] ?? '') ?></p>
                    <p><strong>No. Empleado:</strong> <?= esc($empleado['noEmpleado'] ?? '') ?></p>
                    <p><strong>Estatus Usuario:</strong> <?= isset($empleado['usuario_activo']) ? (((int)$empleado['usuario_activo'] === 1) ? 'Activo' : 'Inactivo') : '-' ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar/agregar empleado -->
<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= $hasEmpleado ? 'Editar datos del empleado' : 'Agregar datos del empleado' ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEmpleado" method="post" action="<?= base_url('modulo1/empleado/guardar') ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4" id="grp-noEmpleado">
              <label class="form-label">No. Empleado</label>
              <input type="text" class="form-control" id="emp-noEmpleado" name="noEmpleado">
            </div>
            <div class="col-md-4">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" id="emp-nombre" name="nombre" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Apellido</label>
              <input type="text" class="form-control" id="emp-apellido" name="apellido" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="emp-email" name="email" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" id="emp-telefono" name="telefono">
            </div>
            <div class="col-md-8">
              <label class="form-label">Domicilio</label>
              <input type="text" class="form-control" id="emp-domicilio" name="domicilio">
            </div>
            <div class="col-md-4">
              <label class="form-label">Puesto</label>
              <input type="text" class="form-control" id="emp-puesto" name="puesto" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label">Fecha de nacimiento</label>
              <input type="date" class="form-control" id="emp-fecha_nac" name="fecha_nac">
            </div>
            <div class="col-md-4">
              <label class="form-label">CURP</label>
              <input type="text" class="form-control" id="emp-curp" name="curp" maxlength="18">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Prefill modal with current empleado (si existe)
  const data = <?php echo json_encode($empleado ?? []); ?>;
  const sessEmail  = '<?= esc(session()->get('user_email') ?? session()->get('correo') ?? '') ?>';
  const sessPuesto = '<?= esc((session()->get('primary_role') ?? ((($tmp=session()->get('role_names')) && is_array($tmp) && isset($tmp[0])) ? $tmp[0] : null)) ?? session()->get('user_role') ?? session()->get('status') ?? '') ?>';
  const sessUid    = '<?= esc((string)(session()->get('user_id') ?? '')) ?>';
  let mode = 'edit'; // 'edit' | 'add'
  function fillForm(){
    if (!data || Object.keys(data).length === 0) return;
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
    set('emp-noEmpleado', data.noEmpleado||'');
    set('emp-nombre', data.nombre||'');
    set('emp-apellido', data.apellido||'');
    set('emp-email', data.email || data.correo || sessEmail || '');
    set('emp-telefono', data.telefono||'');
    set('emp-domicilio', data.domicilio||'');
    set('emp-puesto', data.puesto || sessPuesto || '');
    if (data.fecha_nac){
      try { const d = new Date(data.fecha_nac); set('emp-fecha_nac', isNaN(d) ? String(data.fecha_nac).slice(0,10) : d.toISOString().slice(0,10)); } catch(_){ set('emp-fecha_nac', String(data.fecha_nac).slice(0,10)); }
    }
    set('emp-curp', data.curp||'');
  }

  // Abrir modal al tocar Editar/Agregar
  const btnEdit = document.getElementById('btnEditarEmp');
  const btnAdd  = document.getElementById('btnAgregarEmp');
  function openModal(){
    const el = document.getElementById('modalEmpleado');
    if (!el || !window.bootstrap) return;
    const modal = (bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(el) : new bootstrap.Modal(el));
    // Si es editar, precargar; si es agregar, limpiar
    if (mode === 'edit') {
      fillForm();
      // Mostrar No. Empleado (solo para editar) como solo lectura
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = '';
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.readOnly = true;
    } else {
      const defaults = {
        'emp-noEmpleado':'', 'emp-nombre':'', 'emp-apellido':'',
        'emp-email': (sessEmail||''), 'emp-telefono':'', 'emp-domicilio':'',
        'emp-puesto': (sessPuesto||''), 'emp-fecha_nac':'', 'emp-curp':''
      };
      Object.entries(defaults).forEach(([id,val])=>{ const elI=document.getElementById(id); if(elI) elI.value=val; });
      // Ocultar No. Empleado (se generará automático en backend) y asignar valor sugerido
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = 'none';
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.value = sessUid ? ('EMP0' + sessUid) : '';
    }
    modal.show();
  }
  if (btnEdit) btnEdit.addEventListener('click', function(){
    mode='edit';
    // prevenir doble clic
    btnEdit.disabled = true;
    setTimeout(()=>{ btnEdit.disabled = false; }, 800);
    openModal();
  });
  if (btnAdd)  btnAdd.addEventListener('click', function(){
    mode='add';
    btnAdd.disabled = true;
    setTimeout(()=>{ btnAdd.disabled = false; }, 800);
    openModal();
  });

  // Submit via fetch (JSON)
  const form = document.getElementById('formEmpleado');
  if (form){
    const btnSubmit = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const url = form.getAttribute('action');
      const fd  = new FormData(form);

      const confirm = await Swal.fire({
        title: '¿Guardar datos del empleado?',
        text: 'Se actualizará tu información de perfil.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d'
      });
      if (!confirm.isConfirmed) return;

      if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.textContent = 'Guardando...'; }
      try{
        const res = await fetch(url, { method:'POST', body: fd });
        const json = await res.json();
        if (json && json.success){
          await Swal.fire({ title: 'Guardado', text: (json.updated?'Datos actualizados':'Empleado agregado'), icon: 'success' });
          location.reload();
        } else {
          await Swal.fire({ title: 'No se pudo guardar', text: (json && (json.message||json.error)) || 'Error desconocido', icon: 'error' });
          if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar'; }
        }
      }catch(err){
        await Swal.fire({ title: 'Error', text: 'Error al guardar', icon: 'error' });
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.textContent = 'Guardar'; }
      }
    });
  }
});
</script>
<?= $this->endSection() ?>
