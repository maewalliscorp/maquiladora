<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionModel extends Model
{
    protected $table          = 'inspeccion';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    protected $allowedFields  = [
        'ordenProduccionId',
        'puntoInspeccionId',
        'inspectorId',
        'fecha',
        'resultado',
        'observaciones'
    ];

    // Ajusta solo estos nombres si tus tablas se llaman distinto
    protected array $t = [
        'orden'   => 'orden_produccion', // o como se llame en tu BD
        'cliente' => 'cliente',          // o 'clientes' / 'empresa'
        'punto'   => 'punto_inspeccion', // ya confirmamos que es singular
        'empleado'=> 'empleado',
    ];

    /* ------------ helpers ------------- */
    protected function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    protected function fieldExists(string $table, string $field): bool
    {
        try { return in_array($field, $this->db->getFieldNames($table), true); }
        catch (\Throwable $e) { return false; }
    }

    /** Devuelve el primer campo que exista entre los candidatos */
    protected function pickField(string $table, array $candidates): ?string
    {
        try { $names = $this->db->getFieldNames($table); }
        catch (\Throwable $e) { return null; }
        foreach ($candidates as $f) {
            if (in_array($f, $names, true)) return $f;
        }
        return null;
    }

    /** Devuelve el primer campo FK que exista en $table apuntando a otra tabla */
    protected function pickFkToClient(string $ordenTable): ?string
    {
        // posibles nombres del FK al cliente/empresa en la orden
        $candidates = ['clienteId','cliente_id','idCliente','empresaId','empresa_id','cliente','id_cliente'];
        return $this->pickField($ordenTable, $candidates);
    }

    /* ------------ queries ------------- */
    public function getListado(): array
    {
        $t = $this->t;

        $b = $this->db->table($this->table.' i')
            ->select('i.id, i.fecha, i.resultado, i.observaciones, i.ordenProduccionId');

        $joinedOp = false;

        // JOIN con orden_produccion si existen tabla y PK "id"
        if ($this->tableExists($t['orden']) && $this->fieldExists($t['orden'],'id')) {
            $descField   = $this->pickField($t['orden'], ['descripcion','detalle','descripcionOrden','descripcionPedido','observaciones','nota']);
            $statusField = $this->pickField($t['orden'], ['estatus','status','estado']);

            $descSel   = $descField   ? "op.$descField AS descripcion" : "'' AS descripcion";
            $statusSel = $statusField ? "op.$statusField AS estatus"   : "'' AS estatus";

            $b->select("op.id AS ordenId, $descSel, $statusSel", false)
                ->join($t['orden'].' op', 'op.id = i.ordenProduccionId', 'left');

            $joinedOp = true;
        } else {
            $b->select("i.ordenProduccionId AS ordenId, '' AS descripcion, '' AS estatus", false);
        }

        // JOIN con cliente solo si:
        // 1) existe la tabla cliente
        // 2) tenemos JOIN con op
        // 3) existe un FK en op que apunte a cliente (clienteId, idCliente, etc.)
        if ($joinedOp && $this->tableExists($t['cliente'])) {
            $fk = $this->pickFkToClient($t['orden']); // p.ej. 'clienteId'
            if ($fk) {
                $clienteNombre = $this->pickField($t['cliente'], ['nombre','razonSocial','empresa','nombreComercial']);
                $empresaSel    = $clienteNombre ? "c.$clienteNombre" : "''";
                $b->select("$empresaSel AS empresa", false)
                    ->join($t['cliente'].' c', "c.id = op.$fk", 'left');
            } else {
                // No hay FK en op → no intentes el join
                $b->select("'' AS empresa", false);
            }
        } else {
            $b->select("'' AS empresa", false);
        }

        // Punto de inspección
        if ($this->tableExists($t['punto'])) {
            $puntoNombre = $this->pickField($t['punto'], ['nombre','descripcion','punto']);
            $puntoSel    = $puntoNombre ? "pi.$puntoNombre" : "''";
            $b->select("$puntoSel AS punto", false)
                ->join($t['punto'].' pi', 'pi.id = i.puntoInspeccionId', 'left');
        } else {
            $b->select("'' AS punto", false);
        }

        // Inspector
        if ($this->tableExists($t['empleado'])) {
            $empNombre   = $this->pickField($t['empleado'], ['nombres','nombre','nombreCompleto']);
            $inspectorSel= $empNombre ? "e.$empNombre" : "''";
            $b->select("$inspectorSel AS inspector", false)
                ->join($t['empleado'].' e', 'e.id = i.inspectorId', 'left');
        } else {
            $b->select("'' AS inspector", false);
        }

        $b->orderBy('i.id','ASC');

        return $b->get()->getResultArray();
    }

    public function getDetalle(int $id): ?array
    {
        $t = $this->t;
        $b = $this->db->table($this->table.' i')->select('i.*');

        $joinedOp = false;
        if ($this->tableExists($t['orden']) && $this->fieldExists($t['orden'],'id')) {
            $descField   = $this->pickField($t['orden'], ['descripcion','detalle','descripcionOrden','descripcionPedido','observaciones','nota']);
            $statusField = $this->pickField($t['orden'], ['estatus','status','estado']);

            $descSel   = $descField   ? "op.$descField AS ordenDescripcion" : "'' AS ordenDescripcion";
            $statusSel = $statusField ? "op.$statusField AS ordenEstatus"   : "'' AS ordenEstatus";

            $b->select("$descSel, $statusSel", false)
                ->join($t['orden'].' op', 'op.id = i.ordenProduccionId', 'left');

            $joinedOp = true;
        } else {
            $b->select("'' AS ordenDescripcion, '' AS ordenEstatus", false);
        }

        if ($joinedOp && $this->tableExists($t['cliente'])) {
            $fk = $this->pickFkToClient($t['orden']);
            if ($fk) {
                $clienteNombre = $this->pickField($t['cliente'], ['nombre','razonSocial','empresa','nombreComercial']);
                $empresaSel    = $clienteNombre ? "c.$clienteNombre" : "''";
                $b->select("$empresaSel AS empresa", false)
                    ->join($t['cliente'].' c', "c.id = op.$fk", 'left');
            } else {
                $b->select("'' AS empresa", false);
            }
        } else {
            $b->select("'' AS empresa", false);
        }

        if ($this->tableExists($t['punto'])) {
            $puntoNombre = $this->pickField($t['punto'], ['nombre','descripcion','punto']);
            $puntoSel    = $puntoNombre ? "pi.$puntoNombre" : "''";
            $b->select("$puntoSel AS punto", false)
                ->join($t['punto'].' pi', 'pi.id = i.puntoInspeccionId', 'left');
        } else {
            $b->select("'' AS punto", false);
        }

        if ($this->tableExists($t['empleado'])) {
            $empNombre   = $this->pickField($t['empleado'], ['nombres','nombre','nombreCompleto']);
            $inspectorSel= $empNombre ? "e.$empNombre" : "''";
            $b->select("$inspectorSel AS inspector", false)
                ->join($t['empleado'].' e', 'e.id = i.inspectorId', 'left');
        } else {
            $b->select("'' AS inspector", false);
        }

        $row = $b->where('i.id', $id)->get()->getRowArray();
        return $row ?: null;
    }
}
