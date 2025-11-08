<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    if (!function_exists('current_role_name')) { helper('auth'); }
    $__roleName = function_exists('current_role_name') ? (string)current_role_name() : '';
?>
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Iniciar Producción de Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <!-- Secciones con pestañas: Pendientes / Finalizados -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <style>
                .timer-badge{ font-size:1.6rem; font-weight:600; padding:.35rem .8rem; min-width:150px; text-align:center; }
                .status-badge{ font-size:1.25rem; font-weight:700; padding:.4rem .7rem; min-width:110px; text-align:center; }
            </style>
            <ul class="nav nav-tabs mb-3" id="prodTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pend" data-bs-toggle="tab" data-bs-target="#pane-pend" type="button" role="tab">
                        <i class="bi bi-list-ul me-1"></i> Pendientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-done" data-bs-toggle="tab" data-bs-target="#pane-done" type="button" role="tab">
                        <i class="bi bi-check2-circle me-1"></i> Finalizados
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="prodTabsContent">
                <div class="tab-pane fade show active" id="pane-pend" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Todos los pedidos pendientes</h5>
                            <span class="badge bg-secondary" id="pendingCount">0 pendientes</span>
                        </div>
                        <div id="pendingOrdersList"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pane-done" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Pedidos finalizados</h5>
                            <span class="badge bg-success" id="completedCount">0</span>
                        </div>
                        <div id="completedOrdersList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="searchResults" class="mt-4"></div>

    
</div>

<!-- Modal para confirmar inicio de producción -->
<div class="modal fade" id="startProductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Iniciar producción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas iniciar la producción del pedido <strong id="modalOrderCode"></strong>?</p>
                <div class="mb-3">
                    <label for="productionNotes" class="form-label">Notas iniciales (opcional)</label>
                    <textarea class="form-control" id="productionNotes" rows="2" placeholder="Agregar notas sobre el inicio de producción..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmStartProduction">
                    <i class="bi bi-play-circle me-1"></i> Iniciar producción
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const __isRolCorte = <?= json_encode(strcasecmp(trim($__roleName), 'corte') === 0) ?>;
    const __isRolEmpleado = <?= json_encode(strcasecmp(trim($__roleName), 'empleado') === 0) ?>;
    const empId = <?= json_encode($empleadoId ?? null) ?>;
    
    // Función para cargar las órdenes
    function cargarOrdenes() {
        const base = '<?= base_url('modulo1/produccion/tareas') ?>';
        // Agregar timestamp para evitar caché
        const timestamp = Date.now();
        const url = base + (empId ? ('?empleadoId=' + encodeURIComponent(empId)) : '') + (empId ? '&' : '?') + 't=' + timestamp + '&_nocache=' + timestamp;
        const $pendList = document.getElementById('pendingOrdersList');
        const $pendCount = document.getElementById('pendingCount');
        const $doneList = document.getElementById('completedOrdersList');
        const $doneCount = document.getElementById('completedCount');
        if ($pendList) { $pendList.innerHTML = '<div class="text-muted">Cargando pendientes...</div>'; }
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                console.log('=== CARGAR ORDENES ===');
                console.log('Items recibidos:', data.items);
                const items = Array.isArray(data.items) ? data.items : [];
                // Log del estatus de cada item
                items.forEach(item => {
                    console.log('OP:', item.folio, 'Estatus:', item.status);
                });
                const renderCard = (it) => {
                    const folio = it.folio || '-';
                    const status = it.status || '-';
                    const desde = it.asignadoDesde || '-';
                    const hasta = it.asignadoHasta || '-';
                    const tieneFinalizado = it.tieneFinalizado === true;
                    const lower = String(status).toLowerCase();
                    // Mostrar botón si: el estatus es "En proceso" o "En corte" (permite reactivar cuando vuelve a proceso)
                    // O si no tiene finalizado y el estatus es correcto
                    const isEnProceso = (__isRolEmpleado && lower === 'en proceso');
                    const isEnCorte = (__isRolCorte && lower === 'en corte');
                    const showStart = (isEnProceso || isEnCorte) && 
                                     (lower !== 'completada' && lower !== 'corte finalizado');
                    
                    let actionButton = '';
                    if (showStart) {
                        // Si está en proceso/corte, mostrar botón incluso si tiene finalizado (permite reactivar)
                        actionButton = '<button class="btn btn-sm btn-success btn-start-production" data-folio="' + (folio||'') + '" data-id="' + (it.opId||it.id||'') + '"><i class="bi bi-play-circle me-1"></i>Empezar</button>';
                    } else if (tieneFinalizado && !showStart) {
                        // Solo mostrar "Ya finalizado" si no está en proceso/corte
                        actionButton = '<span class="badge bg-secondary">Ya finalizado</span>';
                    }
                    
                    const right = (
                        '<div class="d-flex align-items-center w-100">'
                        + '<div class="flex-grow-1 d-flex justify-content-center gap-2">'
                        + actionButton
                        + '</div>'
                        + '<span class="badge bg-info text-dark status-badge ms-auto">' + status + '</span>'
                        + '</div>'
                    );
                    return (
                        '<div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">'
                        + '<div>'
                        + '<div class="fw-semibold">OP ' + folio + '</div>'
                        + '<div class="text-muted small">Desde: ' + desde + ' · Hasta: ' + hasta + '</div>'
                        + '</div>'
                        + right
                        + '</div>'
                    );
                };

                if ($pendList && $pendCount) {
                    // Filtrar por estatus completado (case-insensitive)
                    const pending = items.filter(it => {
                        const statusLower = String(it.status || '').toLowerCase();
                        return statusLower !== 'completada' && statusLower !== 'corte finalizado';
                    });
                    $pendCount.textContent = pending.length + ' pendientes';
                    $pendList.innerHTML = pending.length ? pending.map(renderCard).join('') : '<div class="text-muted">No hay pedidos pendientes.</div>';
                }

                if ($doneList && $doneCount) {
                    // Filtrar por estatus completado (case-insensitive)
                    const done = items.filter(it => {
                        const statusLower = String(it.status || '').toLowerCase();
                        return statusLower === 'completada' || statusLower === 'corte finalizado';
                    });
                    $doneCount.textContent = done.length;
                    $doneList.innerHTML = done.length ? done.map(renderCard).join('') : '<div class="text-muted">No hay pedidos finalizados.</div>';
                }
            })
            .catch(() => {
                if ($pendList) $pendList.innerHTML = '<div class="text-danger">No se pudieron cargar los pendientes.</div>';
            });
    }
    
    // Cargar órdenes al inicio
    cargarOrdenes();
    const __runningTimers = new Map();
    function __format(ms){
        const s = Math.floor(ms/1000);
        const hh = String(Math.floor(s/3600)).padStart(2,'0');
        const mm = String(Math.floor((s%3600)/60)).padStart(2,'0');
        const ss = String(s%60).padStart(2,'0');
        return hh+':'+mm+':'+ss;
    }
    document.addEventListener('click', function(ev){
        const startBtn = ev.target.closest('.btn-start-production');
        if (startBtn) {
            const folio = startBtn.getAttribute('data-folio') || '';
            const id = startBtn.getAttribute('data-id') || '';
            Swal.fire({
                title: '¿Empezar producción?',
                text: 'Se iniciará el cronómetro de esta OP.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, empezar',
                cancelButtonText: 'Cancelar'
            }).then(function(res){
                if (!res.isConfirmed) return;
                // Llamar al endpoint para iniciar tiempo de trabajo
                const urlIniciar = '<?= base_url('modulo1/produccion/tiempo/iniciar') ?>';
                fetch(urlIniciar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'ordenProduccionId=' + encodeURIComponent(id) + (empId && empId !== null ? '&empleadoId=' + encodeURIComponent(empId) : '')
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        Swal.fire({ icon:'success', title:'Iniciado', text:'Cronómetro en marcha.', timer:1200, showConfirmButton:false });
                        const right = startBtn.parentElement;
                        const statusEl = right.querySelector('.status-badge');
                        const timer = document.createElement('span');
                        timer.className = 'badge bg-success timer-badge';
                        const t0 = Date.now();
                        timer.textContent = __format(0);
                        const finBtn = document.createElement('button');
                        finBtn.className = 'btn btn-sm btn-outline-danger btn-finalizar-production';
                        finBtn.setAttribute('data-id', id);
                        finBtn.setAttribute('data-tiempo-id', data.id || '');
                        finBtn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Finalizar';
                        const tick = setInterval(function(){ timer.textContent = __format(Date.now()-t0); }, 1000);
                        __runningTimers.set(id, {t0, tick, el: timer, tiempoTrabajoId: data.id || null});
                        // Reordenar: [Timer grande, Finalizar] y al final el estatus
                        right.innerHTML = '';
                        const centerWrap = document.createElement('div');
                        centerWrap.className = 'd-flex align-items-center justify-content-center gap-3 flex-wrap';
                        centerWrap.appendChild(timer);
                        centerWrap.appendChild(finBtn);
                        right.appendChild(centerWrap);
                        if (statusEl) right.appendChild(statusEl);
                    } else {
                        Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo iniciar el tiempo de trabajo.' });
                    }
                })
                .catch(err => {
                    console.error('Error al iniciar tiempo de trabajo:', err);
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo iniciar el tiempo de trabajo.' });
                });
            });
            return;
        }
        const finBtn = ev.target.closest('.btn-finalizar-production');
        if (finBtn) {
            const id = finBtn.getAttribute('data-id') || '';
            const tiempoTrabajoId = finBtn.getAttribute('data-tiempo-id') || '';
            const rec = __runningTimers.get(id);
            if (rec) { clearInterval(rec.tick); __runningTimers.delete(id); }
            
            // Llamar al endpoint para finalizar tiempo de trabajo
            const urlFinalizar = '<?= base_url('modulo1/produccion/tiempo/finalizar') ?>';
            const bodyData = tiempoTrabajoId 
                ? 'tiempoTrabajoId=' + encodeURIComponent(tiempoTrabajoId)
                : 'ordenProduccionId=' + encodeURIComponent(id) + (empId && empId !== null ? '&empleadoId=' + encodeURIComponent(empId) : '');
            
            fetch(urlFinalizar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: bodyData
            })
            .then(r => r.json())
            .then(data => {
                // Mostrar información de debug en la consola
                console.log('=== DEBUG FINALIZAR TIEMPO TRABAJO ===');
                console.log('Respuesta completa:', data);
                if (data.debug) {
                    console.log('Debug info:', data.debug);
                    console.log('Puesto:', data.debug.puesto);
                    console.log('Tipo verificado:', data.debug.tipoVerificado);
                    console.log('Todos finalizados:', data.debug.todosFinalizados);
                    console.log('Nuevo estatus:', data.debug.nuevoEstatus);
                    console.log('Estatus actualizado:', data.debug.estatusActualizado);
                    if (data.debug.tiempos) {
                        console.log('Registros de tiempo:', data.debug.tiempos);
                    }
                    if (data.debug.asignaciones) {
                        console.log('Asignaciones:', data.debug.asignaciones);
                    }
                }
                console.log('========================================');
                
                if (data.ok) {
                    // Buscar el contenedor correcto
                    const card = finBtn.closest('.border.rounded');
                    if (card) {
                        const right = finBtn.parentElement;
                        if (right) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-secondary';
                            badge.textContent = 'Finalizado';
                            finBtn.remove();
                            if (rec && rec.el) { rec.el.className = 'badge bg-primary me-2'; }
                            right.appendChild(badge);
                        }
                    }
                    
                    // Mostrar mensaje principal sin mencionar el cambio de estatus (se mostrará después si aplica)
                    let mensaje = 'La producción fue finalizada. Horas trabajadas: ' + (data.horas ? parseFloat(data.horas).toFixed(2) : '0.00');
                    if (data.todosFinalizados === false) {
                        mensaje += '\nEsperando que otros empleados finalicen...';
                    }
                    
                    Swal.fire({ 
                        icon:'success', 
                        title:'Finalizado', 
                        text: mensaje, 
                        timer:2000, 
                        showConfirmButton:false 
                    });
                    
                    // Si el estatus se actualizó, mostrar una notificación separada más discreta y recargar
                    if (data.estatusActualizado && data.nuevoEstatus) {
                        console.log('Recargando lista de órdenes porque el estatus se actualizó a:', data.nuevoEstatus);
                        // Mostrar notificación discreta después de un breve delay
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'info',
                                title: 'Estatus actualizado',
                                text: 'La orden ahora está: ' + data.nuevoEstatus,
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }, 2500);
                        // Esperar más tiempo para asegurar que la actualización se haya completado en la BD
                        setTimeout(() => {
                            cargarOrdenes();
                        }, 1500);
                    } else if (data.todosFinalizados === true && data.nuevoEstatus) {
                        // Si todos finalizaron pero no se actualizó, recargar de todas formas
                        console.log('Recargando lista de órdenes porque todos finalizaron:', data.nuevoEstatus);
                        setTimeout(() => {
                            cargarOrdenes();
                        }, 1500);
                    }
                } else {
                    Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo finalizar el tiempo de trabajo.' });
                }
            })
            .catch(err => {
                console.error('Error al finalizar tiempo de trabajo:', err);
                Swal.fire({ icon:'error', title:'Error', text:'No se pudo finalizar el tiempo de trabajo.' });
            });
        }
    });
    </script>
<?= $this->endSection() ?>
