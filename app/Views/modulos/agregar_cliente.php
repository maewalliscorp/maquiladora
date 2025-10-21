<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-auto">Clientes</h1>
        <button type="button" class="btn btn-success" id="btnAgregarCliente" data-bs-toggle="modal" data-bs-target="#modalCliente">
            <i class="bi bi-person-plus"></i> Agregar Cliente
        </button>
    </div>
    <div class="modal fade" id="modalClienteDel" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Eliminar cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">¿Confirmas que deseas eliminar este cliente? Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmDel">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Listado de Clientes</strong>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="tabla-clientes">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th style="width: 160px;">Fecha Registro</th>
                            <th style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientes-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalClienteVer" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle de cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="v_nombre" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" name="v_email" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="v_telefono" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Fecha registro</label>
                <input type="text" class="form-control" name="v_fechaRegistro" readonly>
              </div>
              <div class="col-12"><hr class="my-2"></div>
              <div class="col-md-6">
                <label class="form-label">Calle</label>
                <input type="text" class="form-control" name="v_calle" readonly>
              </div>
              <div class="col-md-3">
                <label class="form-label">Num. Ext</label>
                <input type="text" class="form-control" name="v_numExt" readonly>
              </div>
              <div class="col-md-3">
                <label class="form-label">Num. Int</label>
                <input type="text" class="form-control" name="v_numInt" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="v_ciudad" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" class="form-control" name="v_estado" readonly>
              </div>
              <div class="col-md-2">
                <label class="form-label">CP</label>
                <input type="text" class="form-control" name="v_cp" readonly>
              </div>
              <div class="col-md-2">
                <label class="form-label">País</label>
                <input type="text" class="form-control" name="v_pais" readonly>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Editar cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="formCliente">
          <?= csrf_field() ?>
          <div class="modal-body">
              <input type="hidden" name="id">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nombre</label>
                  <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <input type="text" class="form-control" name="telefono">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fecha registro</label>
                  <input type="date" class="form-control" name="fechaRegistro">
                </div>
                <div class="col-12"><hr class="my-2"></div>
                <div class="col-md-6">
                  <label class="form-label">Calle</label>
                  <input type="text" class="form-control" name="calle">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Num. Ext</label>
                  <input type="text" class="form-control" name="numExt">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Num. Int</label>
                  <input type="text" class="form-control" name="numInt">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Ciudad</label>
                  <input type="text" class="form-control" name="ciudad">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Estado</label>
                  <input type="text" class="form-control" name="estado">
                </div>
                <div class="col-md-2">
                  <label class="form-label">CP</label>
                  <input type="text" class="form-control" name="cp">
                </div>
                <div class="col-md-2">
                  <label class="form-label">País</label>
                  <input type="text" class="form-control" name="pais">
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
          </form>
        </div>
      </div>
    </div>
    <script>
    (function(){
        const tbody = document.getElementById('clientes-body');
        const btnAdd = document.getElementById('btnAgregarCliente');
        const fmt = (v) => v == null ? '' : String(v);
        const toDate = (v) => {
            if (!v) return '';
            try { const d = new Date(v); return isNaN(d) ? fmt(v) : d.toISOString().slice(0,10); } catch(e){ return fmt(v); }
        };

        fetch('<?= base_url('modulo1/clientes/json') ?>' + '?_=' + Date.now(), { headers: { 'Accept': 'application/json' }})
            .then(r => r.json())
            .then(data => {
                const items = Array.isArray(data) ? data : (Array.isArray(data.items) ? data.items : []);
                tbody.innerHTML = items.map(row => {
                    const id = row.id ?? row.ID ?? row.clienteId ?? '';
                    const nombre = row.nombre ?? row.name ?? '';
                    const email = row.email ?? row.correo ?? '';
                    const tel = row.telefono ?? row.tel ?? '';
                    const fecha = row.fechaRegistro ?? row.fecha ?? row.created_at ?? '';
                    return `
                        <tr>
                            <td>${fmt(nombre)}</td>
                            <td>${fmt(email)}</td>
                            <td>${fmt(tel)}</td>
                            <td>${toDate(fecha)}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-view" data-id="${fmt(id)}" aria-label="Ver">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${fmt(id)}" aria-label="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${fmt(id)}" aria-label="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No fue posible cargar los clientes.</td></tr>';
            });

        // Eliminar con confirmación
        let delId = null; let delBtn = null;
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.btn-del');
            if (!btn) return;
            delId = btn.getAttribute('data-id');
            if (!delId) return;
            delBtn = btn; delBtn.disabled = true;
            const m = new bootstrap.Modal(document.getElementById('modalClienteDel'));
            m.show();
            const onHidden = () => { if (delBtn) delBtn.disabled = false; delBtn = null; document.getElementById('modalClienteDel').removeEventListener('hidden.bs.modal', onHidden); };
            document.getElementById('modalClienteDel').addEventListener('hidden.bs.modal', onHidden);
        });
        document.getElementById('btnConfirmDel').addEventListener('click', async function(){
            if (!delId) return;
            this.disabled = true;
            try {
                const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(delId) + '/eliminar', { method: 'POST', headers: { 'Accept': 'application/json' }});
                if (res.ok) { location.reload(); return; }
                let msg = 'No se pudo eliminar';
                try { const js = await res.json(); if (js && js.error) msg = js.error; } catch(e) {}
                alert(msg);
            } catch(err) { alert('Error de red al eliminar'); }
            this.disabled = false;
        });

        let lastEditBtn = null;
        let addLocked = false;
        document.addEventListener('click', async function(e){
            const btn = e.target.closest('.btn-edit');
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            if (!id) return;
            btn.disabled = true; lastEditBtn = btn;
            const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }});
            const data = await res.json();
            const m = document.getElementById('modalCliente');
            m.querySelector('[name="id"]').value = data.id || '';
            m.querySelector('[name="nombre"]').value = data.nombre || '';
            m.querySelector('[name="email"]').value = data.email || '';
            m.querySelector('[name="telefono"]').value = data.telefono || '';
            m.querySelector('[name="fechaRegistro"]').value = toDate(data.fechaRegistro) || '';
            const d = data.direccion || {};
            m.querySelector('[name="calle"]').value = d.calle || '';
            m.querySelector('[name="numExt"]').value = d.numExt || '';
            m.querySelector('[name="numInt"]').value = d.numInt || '';
            m.querySelector('[name="ciudad"]').value = d.ciudad || '';
            m.querySelector('[name="estado"]').value = d.estado || '';
            m.querySelector('[name="cp"]').value = d.cp || '';
            m.querySelector('[name="pais"]').value = d.pais || '';
            const modal = new bootstrap.Modal(m);
            modal.show();
        });

        document.getElementById('formCliente').addEventListener('submit', async function(e){
            e.preventDefault();
            const fd = new FormData(this);
            const id = fd.get('id');
            const btnSave = this.querySelector('button[type="submit"]');
            if (btnSave) btnSave.disabled = true;
            if (btnAdd) btnAdd.disabled = true;
            try {
                const url = id ? '<?= site_url('api/clientes') ?>/' + encodeURIComponent(id) + '/editar' : '<?= site_url('api/clientes/crear') ?>';
                const res = await fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }});
                if (res.ok) {
                    try { await res.json(); } catch(e) {}
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCliente'));
                    if (modal) modal.hide();
                    alert(id ? 'Cambios guardados' : 'Cliente agregado');
                    location.reload();
                    return;
                }
                let msg = 'No se pudo guardar';
                try { const js = await res.json(); if (js && js.error) msg = js.error; } catch(e) {}
                alert(msg);
            } catch(err) { alert('Error de red al guardar'); }
            if (btnSave) btnSave.disabled = false;
            if (lastEditBtn) lastEditBtn.disabled = false;
            if (btnAdd) btnAdd.disabled = false;
        });

        const modalEl = document.getElementById('modalCliente');
        modalEl.addEventListener('hidden.bs.modal', function(){
            if (lastEditBtn) { lastEditBtn.disabled = false; lastEditBtn = null; }
            if (btnAdd) { btnAdd.disabled = false; }
        });

        document.getElementById('btnAgregarCliente').addEventListener('click', function(){
            if (addLocked) return; addLocked = true; this.disabled = true;
            const m = document.getElementById('modalCliente');
            m.querySelector('[name="id"]').value = '';
            m.querySelector('[name="nombre"]').value = '';
            m.querySelector('[name="email"]').value = '';
            m.querySelector('[name="telefono"]').value = '';
            m.querySelector('[name="fechaRegistro"]').value = '';
            m.querySelector('[name="calle"]').value = '';
            m.querySelector('[name="numExt"]').value = '';
            m.querySelector('[name="numInt"]').value = '';
            m.querySelector('[name="ciudad"]').value = '';
            m.querySelector('[name="estado"]').value = '';
            m.querySelector('[name="cp"]').value = '';
            m.querySelector('[name="pais"]').value = '';
            m.querySelector('.modal-title').textContent = 'Agregar cliente';
            const modal = new bootstrap.Modal(m);
            modal.show();
            setTimeout(()=>{ addLocked = false; }, 300);
        });

        // Ver detalle (solo lectura)
        document.addEventListener('click', async function(e){
            const btn = e.target.closest('.btn-view');
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            if (!id) return;
            try {
                const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                const m = document.getElementById('modalClienteVer');
                m.querySelector('[name="v_nombre"]').value = data.nombre || '';
                m.querySelector('[name="v_email"]').value = data.email || '';
                m.querySelector('[name="v_telefono"]').value = data.telefono || '';
                m.querySelector('[name="v_fechaRegistro"]').value = toDate(data.fechaRegistro) || '';
                const d = data.direccion || {};
                m.querySelector('[name="v_calle"]').value = d.calle || '';
                m.querySelector('[name="v_numExt"]').value = d.numExt || '';
                m.querySelector('[name="v_numInt"]').value = d.numInt || '';
                m.querySelector('[name="v_ciudad"]').value = d.ciudad || '';
                m.querySelector('[name="v_estado"]').value = d.estado || '';
                m.querySelector('[name="v_cp"]').value = d.cp || '';
                m.querySelector('[name="v_pais"]').value = d.pais || '';
                const modal = new bootstrap.Modal(m);
                modal.show();
            } catch (err) {
                alert('No fue posible cargar el detalle');
            }
        });
    })();
    </script>
<?= $this->endSection() ?>
