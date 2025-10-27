<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .card-result .kv{ display:grid; grid-template-columns: 140px 1fr; gap:.25rem .75rem; }
    .card-result .kv div{ padding:.15rem 0; border-bottom:1px dashed #e9ecef; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="m-0">Facturación de envío</h1>
    <a href="<?= site_url('modulo3/documentos') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Datos del receptor</strong></div>
            <div class="card-body">
                <form id="frmFacturar">
                    <?= csrf_field() ?>
                    <input type="hidden" id="embarqueId" value="<?= (int)($embarqueId ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label">RFC *</label>
                        <input class="form-control" id="rfc" placeholder="XAXX010101000" value="XAXX010101000" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre / Razón social</label>
                        <input class="form-control" id="nombre" placeholder="Cliente Demo" value="Cliente Demo">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Uso CFDI</label>
                            <input class="form-control" id="usoCFDI" value="G03">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Régimen fiscal rec.</label>
                            <input class="form-control" id="regimenFiscalReceptor" value="601">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">CP</label>
                            <input class="form-control" id="domicilioFiscalReceptor" value="00000">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Forma de pago</label>
                            <input class="form-control" id="formaPago" value="03">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Método de pago</label>
                            <input class="form-control" id="metodoPago" value="PUE">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Moneda</label>
                            <input class="form-control" id="moneda" value="MXN">
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <strong>Conceptos</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddConcepto">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </div>
                    <div id="conceptos"></div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-file-text"></i> Generar factura (demo)
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        Proveedor: <code><?= esc(getenv('facturacion.provider') ?: 'mock') ?></code>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm card-result d-none" id="cardResultado">
            <div class="card-header"><strong>Resultado</strong></div>
            <div class="card-body">
                <div class="kv" id="kv"></div>
                <div class="mt-3 d-flex gap-2">
                    <a id="btnPdf" class="btn btn-outline-secondary" href="#" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                    </a>
                    <a id="btnXml" class="btn btn-outline-secondary" href="#" target="_blank" rel="noopener">
                        <i class="bi bi-filetype-xml"></i> Descargar XML
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function(){
        const conceptosDiv = document.getElementById('conceptos');
        const frm          = document.getElementById('frmFacturar');

        // CSRF (para los POST que abrimos en nueva pestaña)
        const CSRF_NAME = '<?= esc(csrf_token()) ?>';
        const CSRF_HASH = '<?= esc(csrf_hash()) ?>';

        // Estado para reutilizar payload/resultado al descargar PDF
        let lastPayload = null;
        let lastResult  = null;

        const addConcepto = (c={descripcion:'Servicio de envío', claveProdServ:'01010101', claveUnidad:'E48', cantidad:1, precioUnitario:100})=>{
            const wrap = document.createElement('div');
            wrap.className = 'border rounded p-2 mb-2';
            wrap.innerHTML = `
          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Descripción</label>
              <input class="form-control c-desc" value="${c.descripcion||''}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Clave ProdServ</label>
              <input class="form-control c-cps" value="${c.claveProdServ||'01010101'}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Clave Unidad</label>
              <input class="form-control c-cu" value="${c.claveUnidad||'E48'}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Cantidad</label>
              <input type="number" step="0.001" class="form-control c-cant" value="${c.cantidad||1}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Precio Unit.</label>
              <input type="number" step="0.01" class="form-control c-pre" value="${c.precioUnitario||0}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Importe</label>
              <input class="form-control c-imp" value="0" disabled>
            </div>
            <div class="col-md-3 d-grid">
              <button type="button" class="btn btn-outline-danger btnRemove"><i class="bi bi-trash"></i></button>
            </div>
          </div>`;
            conceptosDiv.appendChild(wrap);
            const recalc = ()=> {
                const cant = parseFloat(wrap.querySelector('.c-cant').value||0);
                const pre  = parseFloat(wrap.querySelector('.c-pre').value||0);
                wrap.querySelector('.c-imp').value = (cant*pre).toFixed(2);
            };
            wrap.querySelectorAll('.c-cant,.c-pre').forEach(i=>i.addEventListener('input',recalc));
            wrap.querySelector('.btnRemove').addEventListener('click',()=>wrap.remove());
            recalc();
        };

        document.getElementById('btnAddConcepto').addEventListener('click',()=>addConcepto());
        addConcepto(); // uno por defecto

        const buildPayload = ()=>{
            return {
                rfc:   document.getElementById('rfc').value.trim(),
                nombre:document.getElementById('nombre').value.trim(),
                usoCFDI: document.getElementById('usoCFDI').value.trim() || 'G03',
                regimenFiscalReceptor: document.getElementById('regimenFiscalReceptor').value.trim() || '601',
                domicilioFiscalReceptor: document.getElementById('domicilioFiscalReceptor').value.trim() || '00000',
                formaPago: document.getElementById('formaPago').value.trim() || '03',
                metodoPago: document.getElementById('metodoPago').value.trim() || 'PUE',
                moneda: document.getElementById('moneda').value.trim() || 'MXN',
                conceptos: Array.from(conceptosDiv.children).map(w=>({
                    descripcion:   w.querySelector('.c-desc').value.trim(),
                    claveProdServ: w.querySelector('.c-cps').value.trim() || '01010101',
                    claveUnidad:   w.querySelector('.c-cu').value.trim() || 'E48',
                    cantidad:      parseFloat(w.querySelector('.c-cant').value||0),
                    precioUnitario:parseFloat(w.querySelector('.c-pre').value||0),
                }))
            };
        };

        // Enviar a tu endpoint "timbrar" (demo/mock) y mostrar resultado
        frm.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const id = document.getElementById('embarqueId').value;
            const payload = buildPayload();

            const r = await fetch(`<?= site_url('logistica/embarque') ?>/${id}/facturar`, {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body: JSON.stringify(payload)
            });

            const d = await r.json().catch(()=>null);
            if (!r.ok || !d || !d.ok) {
                alert((d && d.msg) ? d.msg : 'No se pudo timbrar (demo).');
                return;
            }

            // Guarda estado para "Descargar PDF"
            lastPayload = payload;
            lastResult  = d;

            // Pinta resultado
            const kv = document.getElementById('kv');
            kv.innerHTML = '';
            const row = (k,v)=>{ const a=document.createElement('div'); a.textContent=k; const b=document.createElement('div'); b.textContent=(v ?? '—'); kv.append(a,b); };
            row('UUID', d.uuid);
            row('Serie', d.serie||'—');
            row('Folio', d.folio||'—');
            row('Total', (d.total!=null) ? d.total : '—');
            row('Fecha', d.fecha||'—');
            row('Proveedor', d.provider||'mock');

            // Si tu servicio devuelve URLs directas, úsalas; si no, las generamos localmente
            const btnPdf = document.getElementById('btnPdf');
            const btnXml = document.getElementById('btnXml');

            if (d.pdf_url) { btnPdf.href = d.pdf_url; btnPdf.onclick = null; }
            else {
                btnPdf.href = "#";
                btnPdf.onclick = (ev)=>{
                    ev.preventDefault();
                    postToNewTab('<?= site_url('facturacion/demo/pdf') ?>', {
                        [CSRF_NAME]: CSRF_HASH,
                        payload: JSON.stringify(lastPayload),
                        result:  JSON.stringify(lastResult)
                    });
                };
            }

            if (d.xml_url) { btnXml.href = d.xml_url; btnXml.onclick = null; }
            else {
                btnXml.href = "#";
                btnXml.onclick = (ev)=>{
                    ev.preventDefault();
                    alert('Demo: XML no disponible en mock.');
                };
            }

            document.getElementById('cardResultado').classList.remove('d-none');
        });

        // Utilidad: abre POST en nueva pestaña (para generar PDF con Dompdf)
        function postToNewTab(action, dataObj){
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = action;
            f.target = '_blank';
            Object.entries(dataObj || {}).forEach(([k,v])=>{
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = k;
                inp.value = typeof v === 'string' ? v : JSON.stringify(v);
                f.appendChild(inp);
            });
            document.body.appendChild(f);
            f.submit();
            setTimeout(()=>f.remove(), 5000);
        }

    })();
</script>
<?= $this->endSection() ?>
