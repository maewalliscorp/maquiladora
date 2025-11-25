<form id="formAduana">
    <input type="hidden" name="id" id="aduanaId" value="">
    <input type="hidden" name="maquiladoraID" id="aduanaMaquiladoraID"
           value="<?= esc($embarque['maquiladoraID'] ?? $maquiladoraID ?? '') ?>">
    <input type="hidden" name="embarqueId" id="aduanaEmbarqueId"
           value="<?= esc($embarque['id'] ?? $embarqueId ?? '') ?>">

    <div class="mb-3">
        <label for="aduana" class="form-label">Aduana</label>
        <input type="text" name="aduana" id="aduana" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="numeroPedimento" class="form-label">Número de pedimento</label>
        <input type="text" name="numeroPedimento" id="numeroPedimento" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="fraccionArancelaria" class="form-label">Fracción arancelaria</label>
        <input type="text" name="fraccionArancelaria" id="fraccionArancelaria" class="form-control">
    </div>

    <div class="mb-3">
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea name="observaciones" id="observaciones" rows="3" class="form-control"></textarea>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>
