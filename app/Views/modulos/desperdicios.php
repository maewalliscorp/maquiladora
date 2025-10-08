<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Desperdicios & Reprocesos</h1>
    <span class="badge bg-danger">Calidad</span>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<style>
    .table thead.table-primary th{ vertical-align:middle; font-weight:600; letter-spacing:.2px; }
    .table td, .table th{ padding:.85rem .8rem; }
</style>

<!-- Botón de diagnóstico -->
<div class="d-flex justify-content-end mb-2">
    <button id="btnDiag" class="btn btn-sm btn-outline-secondary">Diagnóstico</button>
</div>

<div class="row g-3">

    <!-- ====== DESECHOS ====== -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Registro de desecho</strong>
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDesecho">Agregar</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle text-center mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>Fecha</th><th>OP</th><th>Cantidad</th><th>Motivo</th><th>Vista</th><th>Editar</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($desp)): foreach($desp as $x): ?>
                            <tr>
                                <td><?= esc($x['fecha']) ?></td>
                                <td><?= esc($x['op']) ?></td>
                                <td><?= esc($x['cantidad']) ?></td>
                                <td><?= esc($x['observaciones']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info ver-desecho"
                                            data-id="<?= (int)$x['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalVistaDesecho">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-desecho"
                                            data-id="<?= (int)$x['id'] ?>"
                                            data-fecha="<?= esc($x['fecha']) ?>"
                                            data-op="<?= esc($x['op']) ?>"
                                            data-cantidad="<?= esc($x['cantidad']) ?>"
                                            data-motivo="<?= esc($x['observaciones']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalDesecho">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== REPROCESOS ====== -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Unidades en reproceso</strong>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalReproceso">Agregar</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle text-center mb-0">
                        <thead class="table-primary">
                        <tr>
                            <th>OP</th><th>Tarea</th><th>Pend.</th><th>ETA</th><th>Vista</th><th>Editar</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($rep)): foreach($rep as $r): ?>
                            <tr>
                                <td><?= esc($r['op']) ?></td>
                                <td><?= esc($r['tarea']) ?></td>
                                <td><?= (int)$r['pendientes'] ?></td>
                                <td><?= esc($r['eta']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info ver-rep"
                                            data-id="<?= (int)$r['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalVistaReproceso">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-rep"
                                            data-id="<?= (int)$r['id'] ?>"
                                            data-op="<?= esc($r['op']) ?>"
                                            data-tarea="<?= esc($r['tarea']) ?>"
                                            data-pendientes="<?= (int)$r['pendientes'] ?>"
                                            data-eta="<?= esc($r['eta']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalReproceso">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===== Modales ===== -->

<!-- Agregar/Editar Desecho -->
<div class="modal fade" id="modalDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Desecho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formDesecho" action="<?= site_url('calidad/desperdicios/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="d-fecha" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">OP</label>
                            <input class="form-control" name="op" id="d-op" placeholder="1001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" class="form-control" name="cantidad" id="d-cantidad" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo (observaciones)</label>
                            <input class="form-control" name="motivo" id="d-motivo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Desecho -->
<div class="modal fade" id="modalVistaDesecho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Detalle del desecho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9" id="vd-fecha">-</dd>
                    <dt class="col-sm-3">OP</dt><dd class="col-sm-9" id="vd-op">-</dd>
                    <dt class="col-sm-3">Cantidad</dt><dd class="col-sm-9" id="vd-cantidad">-</dd>
                    <dt class="col-sm-3">Motivo</dt><dd class="col-sm-9" id="vd-motivo">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Agregar/Editar Reproceso -->
<div class="modal fade" id="modalReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Reproceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="formReproceso" action="<?= site_url('calidad/reprocesos/guardar') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">OP</label>
                            <input class="form-control" name="op" id="r-op" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Tarea</label>
                            <input class="form-control" name="tarea" id="r-tarea" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendientes</label>
                            <input type="number" min="0" class="form-control" name="pendientes" id="r-pendientes" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ETA</label>
                            <input type="date" class="form-control" name="eta" id="r-eta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="r-estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="terminado">Terminado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vista Reproceso -->
<div class="modal fade" id="modalVistaReproceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header"><h5 class="modal-title">Detalle del reproceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">OP</dt><dd class="col-sm-9" id="vr-op">-</dd>
                    <dt class="col-sm-3">Tarea</dt><dd class="col-sm-9" id="vr-tarea">-</dd>
                    <dt class="col-sm-3">Pendientes</dt><dd class="col-sm-9" id="vr-pendientes">-</dd>
                    <dt class="col-sm-3">ETA</dt><dd class="col-sm-9" id="vr-eta">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Modal diagnóstico -->
<div class="modal fade" id="modalDiag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Diagnóstico de datos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="diagSummary" class="mb-3"></div>
                <h6 class="mt-2">Valores de <code>reproceso.accion</code></h6>
                <div id="diagAccion" class="mb-3"></div>
                <h6 class="mt-2">Muestra (JOIN reproceso + inspeccion)</h6>
                <pre id="diagSample" class="bg-light p-2 rounded small mb-0" style="white-space:pre-wrap"></pre>
            </div>
        </div>
    </div>
</div>

<script>
    // Helpers
    function S(id, val){ const el=document.getElementById(id); if(el) el.textContent = val ?? '-'; }
    function V(id, val){ const el=document.getElementById(id); if(el) el.value = val; }

    // Vista -> fetch JSON
    document.querySelectorAll('.ver-desecho').forEach(b=>b.addEventListener('click', async ()=>{
        const r = await (await fetch('<?= site_url('calidad/desperdicios') ?>/'+b.dataset.id)).json();
        S('vd-fecha', r.fecha); S('vd-op', r.op);
        S('vd-cantidad', r.cantidad); S('vd-motivo', r.observaciones || '-');
    }));

    document.querySelectorAll('.ver-rep').forEach(b=>b.addEventListener('click', async ()=>{
        const r = await (await fetch('<?= site_url('calidad/reprocesos') ?>/'+b.dataset.id)).json();
        S('vr-op', r.op); S('vr-tarea', r.tarea);
        S('vr-pendientes', r.cantidad ?? r.pendientes); S('vr-eta', r.fecha ?? r.eta);
    }));

    // Editar -> precarga modal y cambia action
    document.querySelectorAll('.edit-desecho').forEach(b=>b.addEventListener('click', ()=>{
        const f = document.getElementById('formDesecho');
        f.action = '<?= site_url('calidad/desperdicios') ?>/'+b.dataset.id+'/editar';
        V('d-fecha', b.dataset.fecha); V('d-op', b.dataset.op);
        V('d-cantidad', b.dataset.cantidad); V('d-motivo', b.dataset.motivo||'');
    }));
    document.getElementById('modalDesecho').addEventListener('show.bs.modal', e=>{
        if (!e.relatedTarget.classList.contains('edit-desecho')) {
            const f = document.getElementById('formDesecho');
            f.action = '<?= site_url('calidad/desperdicios/guardar') ?>';
            ['d-fecha','d-op','d-cantidad','d-motivo'].forEach(id=>V(id,''));
        }
    });

    document.querySelectorAll('.edit-rep').forEach(b=>b.addEventListener('click', ()=>{
        const f = document.getElementById('formReproceso');
        f.action = '<?= site_url('calidad/reprocesos') ?>/'+b.dataset.id+'/editar';
        V('r-op', b.dataset.op); V('r-tarea', b.dataset.tarea);
        V('r-pendientes', b.dataset.pendientes); V('r-eta', b.dataset.eta);
    }));
    document.getElementById('modalReproceso').addEventListener('show.bs.modal', e=>{
        if (!e.relatedTarget.classList.contains('edit-rep')) {
            const f = document.getElementById('formReproceso');
            f.action = '<?= site_url('calidad/reprocesos/guardar') ?>';
            ['r-op','r-tarea','r-pendientes','r-eta'].forEach(id=>V(id,''));
            V('r-estado','pendiente');
        }
    });

    // -------- Diagnóstico
    document.getElementById('btnDiag')?.addEventListener('click', async ()=>{
        try{
            const resp = await fetch('<?= site_url('calidad/desperdicios/diag') ?>');
            const j = await resp.json();

            const sumEl = document.getElementById('diagSummary');
            const accEl = document.getElementById('diagAccion');
            const smpEl = document.getElementById('diagSample');

            if(!j.ok){
                sumEl.innerHTML = `<div class="alert alert-danger">Error: ${j.error || 'desconocido'}</div>`;
                accEl.innerHTML = ''; smpEl.textContent = '';
            }else{
                const hasIns = j.has?.inspeccion ? '✅' : '❌';
                const hasRep = j.has?.reproceso  ? '✅' : '❌';
                const cIns = j.counts?.inspeccion ?? 0;
                const cRep = j.counts?.reproceso  ?? 0;
                const joinC = j.join_count ?? 0;

                sumEl.innerHTML = `
          <div class="table-responsive mb-2">
            <table class="table table-sm table-bordered align-middle text-center mb-0">
              <thead class="table-primary">
                <tr><th>BD</th><th>inspeccion</th><th>reproceso</th><th>#inspeccion</th><th>#reproceso</th><th>#JOIN</th></tr>
              </thead>
              <tbody>
                <tr>
                  <td>${j.database || '-'}</td>
                  <td>${hasIns}</td>
                  <td>${hasRep}</td>
                  <td>${cIns}</td>
                  <td>${cRep}</td>
                  <td>${joinC}</td>
                </tr>
              </tbody>
            </table>
          </div>
        `;

                if (Array.isArray(j.accion_values) && j.accion_values.length){
                    accEl.innerHTML = `
            <div class="table-responsive">
              <table class="table table-sm table-bordered text-center mb-0">
                <thead class="table-light"><tr><th>accion (lower)</th><th>count</th></tr></thead>
                <tbody>
                  ${j.accion_values.map(r=>`<tr><td>${r.accion}</td><td>${r.c}</td></tr>`).join('')}
                </tbody>
              </table>
            </div>`;
                } else {
                    accEl.innerHTML = `<div class="text-muted">Sin valores en <code>reproceso.accion</code></div>`;
                }

                smpEl.textContent = JSON.stringify(j.sample ?? [], null, 2);
            }

            new bootstrap.Modal(document.getElementById('modalDiag')).show();
        }catch(e){
            alert('No se pudo ejecutar el diagnóstico: ' + e.message);
        }
    });
</script>

<?= $this->endSection() ?>
