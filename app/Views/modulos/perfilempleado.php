<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php 
// Depuración temporal - verificar sesión
$userId = session()->get('user_id');
$userName = session()->get('user_name');
$logged_in = session()->get('logged_in');
?>
<!-- DEBUG: Sesión activa: <?php var_dump($userId); ?> | <?php var_dump($userName); ?> | <?php var_dump($logged_in); ?> -->
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
                <?php 
                $defaultAvatar = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22200%22%20height%3D%22200%22%20fill%3D%22%23eee%22%2F%3E%3Ctext%20x%3D%22100%22%20y%3D%22110%22%20font-family%3D%22Arial%22%20font-size%3D%2280%22%20text-anchor%3D%22middle%22%20fill%3D%22%23999%22%3E👤%3C%2Ftext%3E%3C%2Fsvg%3E';
                $avatarSrc = isset($empleado['foto']) && !empty($empleado['foto']) ? 
                    'data:image/jpeg;base64,' . $empleado['foto'] : 
                    $defaultAvatar;
                ?>
                <img src="<?= $avatarSrc ?>" alt="Foto" class="profile-img" style="width: 200px; height: 200px; object-fit: cover;">
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
      <form id="formEmpleado" method="post" action="<?= base_url('modulo1/empleado/guardar') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 mb-3 text-center">
              <div class="mb-3">
                <div class="position-relative d-inline-block">
                  <img id="foto-preview" src="<?= $hasEmpleado && !empty($empleado['foto']) ? 'data:image/jpeg;base64,' . $empleado['foto'] : base_url('assets/img/default-avatar.png') ?>" 
                       class="rounded-circle border" 
                       style="width: 150px; height: 150px; object-fit: cover;"
                       alt="Foto de perfil">
                  <div class="position-absolute bottom-0 start-0 d-flex gap-1">
                    <label for="emp-foto" class="bg-primary text-white rounded-circle p-2" style="cursor: pointer;" title="Subir archivo">
                      <i class="bi bi-upload"></i>
                      <input type="file" class="d-none" id="emp-foto" name="foto" accept="image/*">
                    </label>
                    <label for="emp-foto-cam" class="bg-success text-white rounded-circle p-2" style="cursor: pointer;" title="Tomar foto" onclick="abrirCamaraMovil(event)">
                      <i class="bi bi-camera"></i>
                      <input type="file" class="d-none" id="emp-foto-cam" name="foto" accept="image/*" capture="environment">
                    </label>
                    <label for="emp-foto-webcam" class="bg-info text-white rounded-circle p-2" style="cursor: pointer;" title="Cámara web" onclick="abrirWebcam(event)">
                      <i class="bi bi-webcam"></i>
                      <input type="file" class="d-none" id="emp-foto-webcam" name="foto" accept="image/*">
                    </label>
                  </div>
                </div>
              </div>
              <div>
                <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
              </div>
            </div>
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
<!-- jQuery first, then SweetAlert2 (Bootstrap ya lo carga el layout) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Esperar a que jQuery y Bootstrap estén cargados
document.addEventListener('DOMContentLoaded', function() {
  // Verificar si jQuery está cargado
  if (typeof jQuery === 'undefined') {
    console.error('jQuery no está cargado');
    return;
  }
  console.log('jQuery versión:', jQuery.fn.jquery);
  
  // Configuración global para evitar conflictos con $ de jQuery
  var $j = jQuery.noConflict();
  
  // Verificar si Bootstrap está cargado
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap no está cargado');
  } else {
    console.log('Bootstrap está cargado');
  }
  
  // Mostrar el modal al hacer clic en Editar/Agregar
  $j(document).ready(function() {
    // Verificar si los botones existen
    console.log('Botón Editar encontrado:', $j('#btnEditarEmp').length > 0);
    console.log('Botón Agregar encontrado:', $j('#btnAgregarEmp').length > 0);
    
    $j('#btnEditarEmp, #btnAgregarEmp').on('click', function(e) {
      e.preventDefault();
      console.log('Botón clickeado:', $j(this).attr('id'));
      const mode = $j(this).attr('id') === 'btnEditarEmp' ? 'edit' : 'add';
      console.log('Modo:', mode);
      openModal(mode);
    });
  });
  
  
  // Prefill modal with current empleado (si existe)
  const data = <?php echo json_encode($empleado ?? []); ?>;
  
  // Mostrar vista previa de la imagen al seleccionar archivo
  const fotoInput = document.getElementById('emp-foto');
  const fotoCamInput = document.getElementById('emp-foto-cam');
  const fotoWebcamInput = document.getElementById('emp-foto-webcam');
  
  function setupFotoPreview(input) {
    if (input) {
      input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Validar tamaño (2MB máximo)
          if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'La imagen no debe pesar más de 2MB',
              confirmButtonText: 'Entendido'
            });
            e.target.value = '';
            return;
          }
          
          // Validar tipo de archivo
          const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
          if (!validTypes.includes(file.type)) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Formato de archivo no válido. Use JPG, PNG o GIF',
              confirmButtonText: 'Entendido'
            });
            e.target.value = '';
            return;
          }
          
          // Mostrar vista previa
          const reader = new FileReader();
          reader.onload = function(event) {
            document.getElementById('foto-preview').src = event.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }
  
  setupFotoPreview(fotoInput);
  setupFotoPreview(fotoCamInput);
  setupFotoPreview(fotoWebcamInput);
  
  // Función para abrir cámara del móvil
  window.abrirCamaraMovil = function(event) {
    event.preventDefault();
    
    // Detectar si es un dispositivo móvil
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if (isMobile) {
      // Para móviles, usar la API de MediaDevices si está disponible
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Crear modal para la cámara del móvil
        const mobileCameraModal = document.createElement('div');
        mobileCameraModal.className = 'modal fade';
        mobileCameraModal.id = 'mobileCameraModal';
        mobileCameraModal.innerHTML = `
          <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Cámara del Móvil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center p-0">
                <video id="mobileCameraVideo" style="width: 100%; height: auto; max-height: 70vh; object-fit: cover;"></video>
                <canvas id="mobileCameraCanvas" style="display: none;"></canvas>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="capturarFotoMovil()">
                  <i class="bi bi-camera-fill"></i> Capturar Foto
                </button>
              </div>
            </div>
          </div>
        `;
        
        document.body.appendChild(mobileCameraModal);
        
        const modal = new bootstrap.Modal(mobileCameraModal);
        modal.show();
        
        // Configuración para cámara trasera del móvil
        const video = document.getElementById('mobileCameraVideo');
        const constraints = {
          video: {
            facingMode: 'environment', // Cámara trasera
            width: { ideal: 1920 },
            height: { ideal: 1080 }
          }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
          .then(function(stream) {
            video.srcObject = stream;
            window.mobileCameraStream = stream;
          })
          .catch(function(err) {
            console.error('Error al acceder a la cámara del móvil:', err);
            // Si falla, intentar con el input file
            fallbackToInputFile();
            modal.hide();
          });
        
        // Limpiar al cerrar el modal
        mobileCameraModal.addEventListener('hidden.bs.modal', function() {
          if (window.mobileCameraStream) {
            window.mobileCameraStream.getTracks().forEach(track => track.stop());
          }
          document.body.removeChild(mobileCameraModal);
        });
        
        // Función para capturar foto del móvil
        window.capturarFotoMovil = function() {
          const video = document.getElementById('mobileCameraVideo');
          const canvas = document.getElementById('mobileCameraCanvas');
          const context = canvas.getContext('2d');
          
          // Configurar canvas con las dimensiones del video
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          
          // Dibujar el frame actual
          context.drawImage(video, 0, 0, canvas.width, canvas.height);
          
          // Convertir a base64 y mostrar en el preview
          const imageData = canvas.toDataURL('image/jpeg', 0.8);
          document.getElementById('foto-preview').src = imageData;
          
          // Crear un archivo para el formulario
          canvas.toBlob(function(blob) {
            const file = new File([blob], 'mobile_photo.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('emp-foto-cam').files = dataTransfer.files;
          }, 'image/jpeg', 0.8);
          
          // Cerrar el modal
          const mobileCameraModal = document.getElementById('mobileCameraModal');
          const modalInstance = bootstrap.Modal.getInstance(mobileCameraModal);
          modalInstance.hide();
        };
      } else {
        // Fallback para móviles que no soportan MediaDevices
        fallbackToInputFile();
      }
    } else {
      // Para desktop, usar el input file normal
      fallbackToInputFile();
    }
    
    function fallbackToInputFile() {
      // Simular clic en el input file
      document.getElementById('emp-foto-cam').click();
    }
  };
  
  // Función para abrir cámara web con MediaDevices API
  window.abrirWebcam = function(event) {
    event.preventDefault();
    
    // Crear modal para la cámara web
    const webcamModal = document.createElement('div');
    webcamModal.className = 'modal fade';
    webcamModal.id = 'webcamModal';
    webcamModal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cámara Web</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <video id="webcamVideo" width="400" height="300" autoplay style="border-radius: 8px;"></video>
            <canvas id="webcamCanvas" width="400" height="300" style="display: none;"></canvas>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="capturarFoto()">Capturar Foto</button>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(webcamModal);
    
    const modal = new bootstrap.Modal(webcamModal);
    modal.show();
    
    // Iniciar la cámara
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('webcamCanvas');
    const context = canvas.getContext('2d');
    
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(function(stream) {
        video.srcObject = stream;
        window.webcamStream = stream;
      })
      .catch(function(err) {
        console.error('Error al acceder a la cámara:', err);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo acceder a la cámara web. Verifique los permisos.',
          confirmButtonText: 'Entendido'
        });
        modal.hide();
      });
    
    // Limpiar al cerrar el modal
    webcamModal.addEventListener('hidden.bs.modal', function() {
      if (window.webcamStream) {
        window.webcamStream.getTracks().forEach(track => track.stop());
      }
      document.body.removeChild(webcamModal);
    });
  };
  
  // Función para capturar la foto
  window.capturarFoto = function() {
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('webcamCanvas');
    const context = canvas.getContext('2d');
    
    // Dibujar el frame actual del video en el canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convertir a base64 y mostrar en el preview
    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('foto-preview').src = imageData;
    
    // Crear un archivo para el formulario
    canvas.toBlob(function(blob) {
      const file = new File([blob], 'webcam_photo.jpg', { type: 'image/jpeg' });
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById('emp-foto-webcam').files = dataTransfer.files;
    }, 'image/jpeg', 0.8);
    
    // Cerrar el modal
    const webcamModal = document.getElementById('webcamModal');
    const modal = bootstrap.Modal.getInstance(webcamModal);
    modal.hide();
  };
  
  const sessEmail  = '<?= esc(session()->get('user_email') ?? session()->get('correo') ?? '') ?>';
  const sessPuesto = '<?= esc((session()->get('primary_role') ?? ((($tmp=session()->get('role_names')) && is_array($tmp) && isset($tmp[0])) ? $tmp[0] : null)) ?? session()->get('user_role') ?? session()->get('status') ?? '') ?>';
  const sessUid    = '<?= esc((string)(session()->get('user_id') ?? '')) ?>';
  let mode = 'edit'; // 'edit' | 'add'
  function fillForm(){
    if (!data || Object.keys(data).length === 0) return;
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
    // Mostrar la foto actual si existe
    if (data.foto) {
      const imgPreview = document.getElementById('foto-preview');
      if (imgPreview) {
        imgPreview.src = 'data:image/jpeg;base64,' + data.foto;
      }
    }
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

  // Función para abrir el modal
  function openModal(mode = 'edit') {
    console.log('openModal llamado con modo:', mode);
    // Mostrar el modal
    const modal = $j('#modalEmpleado');
    console.log('Modal encontrado:', modal.length > 0);
    
    // Reiniciar el formulario y la vista previa de la imagen
    const form = document.getElementById('formEmpleado');
    if (form) form.reset();
    
    // Restablecer la imagen de vista previa
    const imgPreview = document.getElementById('foto-preview');
    if (imgPreview) {
      imgPreview.src = '<?= base_url('assets/img/default-avatar.png') ?>';
    }
    
    // Si es editar, precargar los datos
    if (mode === 'edit') {
      // No requerir la foto en edición (puede ser opcional)
      const fotoInput = document.getElementById('emp-foto');
      if (fotoInput) fotoInput.required = false;
      
      fillForm();
      
      // Mostrar No. Empleado (solo para editar) como solo lectura
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = '';
      
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.readOnly = true;
    } else {
      // Limpiar el formulario para agregar nuevo
      const defaults = {
        'emp-noEmpleado': sessUid ? ('EMP0' + sessUid) : '',
        'emp-nombre': '', 
        'emp-apellido': '',
        'emp-email': (sessEmail || ''), 
        'emp-telefono': '', 
        'emp-domicilio': '',
        'emp-puesto': (sessPuesto || ''), 
        'emp-fecha_nac': '', 
        'emp-curp': ''
      };
      
      Object.entries(defaults).forEach(([id, val]) => { 
        const elI = document.getElementById(id); 
        if (elI) elI.value = val; 
      });
      
      // Ocultar No. Empleado (se generará automático en backend) y asignar valor sugerido
      const grp = document.getElementById('grp-noEmpleado');
      if (grp) grp.style.display = 'none';
      
      const noEmp = document.getElementById('emp-noEmpleado');
      if (noEmp) noEmp.readOnly = false;
    }
    
    // Mostrar el modal al final de la función
    console.log('Intentando mostrar modal...');
    try {
      const modalInstance = new bootstrap.Modal(modal[0]);
      modalInstance.show();
      console.log('Modal mostrado exitosamente');
    } catch (error) {
      console.error('Error al mostrar modal:', error);
    }
  }
  
  // Los manejadores de eventos ya están configurados arriba con jQuery

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
