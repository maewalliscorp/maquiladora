<?php

namespace App\Models;

use CodeIgniter\Model;

/** Acceso a datos de pedidos y su información relacionada. */
class PedidoModel extends Model
{
    // Tabla real según tu BD
    protected $table            = 'orden_compra';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    /** Listado de pedidos normalizado (id, empresa, folio, fecha, estatus, moneda, total). */
    public function getListadoPedidos(): array
    {
        $db = $this->db;
        // 1) Intento completo (esquema con folio/moneda/total)
        $sql = "SELECT oc.id,
                       c.nombre AS empresa,
                       oc.folio,
                       oc.fecha,
                       oc.estatus,
                       oc.moneda,
                       oc.total,
                       NULL as documento_url
                FROM orden_compra oc
                LEFT JOIN cliente c ON c.id = oc.clienteId
                ORDER BY oc.fecha DESC, oc.id DESC";
        try {
            return $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            // Fallback: esquema mínimo sin folio/moneda/total, usando solo tablas en minúsculas
            try {
                $rows = $db->query(
                    "SELECT oc.id,
                            c.nombre AS empresa,
                            oc.fecha,
                            oc.estatus
                     FROM orden_compra oc
                     LEFT JOIN cliente c ON c.id = oc.clienteId
                     ORDER BY oc.fecha DESC, oc.id DESC"
                )->getResultArray();
            } catch (\Throwable $e3) {
                return [];
            }

            // Normalizar columnas faltantes
            foreach ($rows as &$r) {
                $r['folio'] = $r['folio'] ?? '';
                $r['moneda'] = $r['moneda'] ?? '';
                $r['total'] = isset($r['total']) ? $r['total'] : 0;
                $r['documento_url'] = $r['documento_url'] ?? null;
            }
            unset($r);
            return $rows;
        }
    }

    /** Pedido base por ID; añade empresa y última OP si existen. */
    public function getPedidoPorId(int $id): ?array
    {
        $db = $this->db;
        // 1) Base mínima de orden_compra, para asegurar que algo se muestre
        $base = null;
        try {
            $base = $db->query(
                "SELECT id, clienteId, folio, fecha, estatus, moneda, total
                 FROM orden_compra WHERE id = ?",
                [$id]
            )->getRowArray();
        } catch (\Throwable $e) {
            $base = null;
        }

        // 1b) Fallback: SELECT * y mapear variantes de clienteId
        if (!$base) {
            try {
                $rowAny = $db->query("SELECT * FROM orden_compra WHERE id = ?", [$id])->getRowArray();
                if ($rowAny) {
                    $base = [
                        'id' => $rowAny['id'] ?? $id,
                        'clienteId' => $rowAny['clienteId'] ?? ($rowAny['cliente_id'] ?? ($rowAny['idCliente'] ?? ($rowAny['idcliente'] ?? null))),
                        'folio' => $rowAny['folio'] ?? '',
                        'fecha' => $rowAny['fecha'] ?? null,
                        'estatus' => $rowAny['estatus'] ?? '',
                        'moneda' => $rowAny['moneda'] ?? '',
                        'total' => $rowAny['total'] ?? 0,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        if (!$base) { return null; }

        // 2) Nombre de cliente (empresa)
        try {
            if (!empty($base['clienteId'])) {
                foreach (['cliente','Cliente'] as $t) {
                    try {
                        $cli = $db->query('SELECT nombre FROM ' . $t . ' WHERE id = ?', [$base['clienteId']])->getRowArray();
                        if ($cli && isset($cli['nombre'])) {
                            $base['empresa'] = $cli['nombre'];
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {}

        // 3) Adjuntar datos de la última orden de producción ligada (si existe)
        try {
            if (!empty($base['id'])) {
                // Variante 1: tablas en minúscula
                try {
                    $op = $db->query(
                        "SELECT op.* FROM orden_produccion op
                         INNER JOIN (
                           SELECT MAX(id) AS id, ordenCompraId
                           FROM orden_produccion
                           GROUP BY ordenCompraId
                         ) t ON t.id = op.id
                         WHERE op.ordenCompraId = ?
                         LIMIT 1",
                        [$base['id']]
                    )->getRowArray();
                    if ($op) {
                        $base['op_id'] = $op['id'] ?? null;
                        $base['op_folio'] = $op['folio'] ?? null;
                        $base['op_disenoVersionId'] = $op['disenoVersionId'] ?? null;
                        $base['op_cantidadPlan'] = $op['cantidadPlan'] ?? null;
                        $base['op_fechaInicioPlan'] = $op['fechaInicioPlan'] ?? null;
                        $base['op_fechaFinPlan'] = $op['fechaFinPlan'] ?? null;
                        $base['op_status'] = $op['status'] ?? null;
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        // 4) Consulta simple para completar/normalizar
        try {
            $row = $db->query(
                "SELECT oc.id, oc.clienteId, oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                        c.nombre AS empresa
                 FROM orden_compra oc
                 LEFT JOIN cliente c ON c.id = oc.clienteId
                 WHERE oc.id = ?",
                [$id]
            )->getRowArray();
            if ($row) {
                // Si ya trajimos op_*, preservarlos
                return array_merge($row, array_intersect_key($base, array_flip([
                    'op_id','op_folio','op_disenoVersionId','op_cantidadPlan','op_fechaInicioPlan','op_fechaFinPlan','op_status'
                ])));
            }
        } catch (\Throwable $e) {}

        return $base;
    }

    /** Detalle completo del pedido: cliente, dirección, clasificación, ítems y diseño+versión. */
    public function getPedidoDetalle(int $id): ?array
    {
        $db = $this->db;

        // 0) Base de orden_compra (sin suponer joins) usando getPedidoPorId para consistencia
        $base = $this->getPedidoPorId($id);
        if (!$base) return null;

        // 1) Cargar cliente según ID
        $cli = null;
        try {
            $cli = $db->query('SELECT * FROM cliente WHERE id = ?', [$base['clienteId']])->getRowArray();
        } catch (\Throwable $e) {
            try { $cli = $db->query('SELECT * FROM Cliente WHERE id = ?', [$base['clienteId']])->getRowArray(); } catch (\Throwable $e2) {}
        }

        // 2) Clasificación del cliente
        $clas = null;
        if (!empty($base['clienteId'])) {
            foreach (['cliente_clasificacion','ClienteClasificacion'] as $t) {
                // Variante 1: columna clienteId
                try {
                    $clas = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY id DESC LIMIT 1", [$base['clienteId']])->getRowArray();
                    if ($clas) break;
                } catch (\Throwable $e) {}
                // Variante 2: columna clienteld
                try {
                    $clas = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY id DESC LIMIT 1", [$base['clienteId']])->getRowArray();
                    if ($clas) break;
                } catch (\Throwable $e2) {}
            }
        }

        // 3) Dirección principal del cliente
        $dir = null;
        $dirs = [];
        if (!empty($base['clienteId'])) {
            foreach (['cliente_direccion','ClienteDireccion'] as $t) {
                // Variante A1: clienteId + esPrincipal
                try {
                    $dir = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY esPrincipal DESC, id ASC LIMIT 1", [$base['clienteId']])->getRowArray();
                } catch (\Throwable $e) { $dir = null; }
                // Variante A2: clienteld + esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY esPrincipal DESC, id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e2) { $dir = null; }
                }
                // Variante B1: clienteId sin esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteId = ? ORDER BY id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e3) { $dir = null; }
                }
                // Variante B2: clienteld sin esPrincipal
                if (!$dir) {
                    try { $dir = $db->query("SELECT * FROM $t WHERE clienteld = ? ORDER BY id ASC LIMIT 1", [$base['clienteId']])->getRowArray(); } catch (\Throwable $e4) { $dir = null; }
                }
                // Para depuración: contar cuántas direcciones totales (clienteId OR clienteld)
                try {
                    $dirs = $db->query("SELECT * FROM $t WHERE clienteId = ? OR clienteld = ? ORDER BY id", [$base['clienteId'], $base['clienteId']])->getResultArray();
                } catch (\Throwable $e5) {}
                if ($dir) break;
            }
        }

        // 4) Construir datos de salida: base + cliente + clasificación + dirección
        $cliente = [
            'id' => (int)($base['clienteId'] ?? 0),
            'codigo' => $cli['codigo'] ?? '',
            'nombre' => $cli['nombre'] ?? ($base['empresa'] ?? ''),
            'email' => $cli['email'] ?? '',
            'telefono' => $cli['telefono'] ?? '',
            'direccion_detalle' => [
                'calle' => $dir['calle'] ?? '',
                'numExt' => $dir['numExt'] ?? ($dir['numext'] ?? ''),
                'numInt' => $dir['numInt'] ?? ($dir['numint'] ?? ''),
                'ciudad' => $dir['ciudad'] ?? '',
                'estado' => $dir['estado'] ?? '',
                'cp' => $dir['cp'] ?? '',
                'pais' => $dir['pais'] ?? '',
            ],
            'clasificacion' => [
                'id' => (int)($clas['clasificacionId'] ?? 0),
                'nombre' => $clas['nombre'] ?? '',
                'descripcion' => $clas['descripcion'] ?? '',
            ],
            // debug opcional para verificar por qué no llega domicilio
            'direccion_debug_count' => is_array($dirs) ? count($dirs) : 0,
        ];

        // 5) Ítems + artículos
        $items = [];
        try {
            $items = $db->query(
                "SELECT oci.id, oci.articuloId, oci.cantidad, oci.precioUnitario, oci.fechaEntregaCompromiso,
                        a.codigo AS art_codigo, a.nombre AS art_nombre, a.unidadMedida AS art_unidad
                 FROM orden_compra_item oci
                 LEFT JOIN articulo a ON a.id = oci.articuloId
                 WHERE oci.ordenCompraId = ?
                 ORDER BY oci.id",
                [$id]
            )->getResultArray();
        } catch (\Throwable $e) {
            try {
                $items = $db->query(
                    "SELECT oci.id, oci.articuloId, oci.cantidad, oci.precioUnitario, oci.fechaEntregaCompromiso,
                            a.codigo AS art_codigo, a.nombre AS art_nombre, a.unidadMedida AS art_unidad
                     FROM OrdenCompraItem oci
                     LEFT JOIN Articulo a ON a.id = oci.articuloId
                     WHERE oci.ordenCompraId = ?
                     ORDER BY oci.id",
                    [$id]
                )->getResultArray();
            } catch (\Throwable $e2) {
                $items = [];
            }
        }

        // 6) Diseños del cliente (tolerando clienteld/clienteId/cliente_id)
        $disenos = [];
        $disenoRelacionado = null;
        $versionRelacionado = null;
        if (!empty($base['clienteId'])) {
            foreach (['diseno','Diseno'] as $t) {
                // Intento A: clienteld
                try {
                    $disenos = $db->query(
                        "SELECT * FROM $t WHERE clienteld = ? ORDER BY id",
                        [$base['clienteId']]
                    )->getResultArray();
                } catch (\Throwable $e) { $disenos = []; }
                // Intento B: clienteId
                if (!$disenos) {
                    try {
                        $disenos = $db->query(
                            "SELECT * FROM $t WHERE clienteId = ? ORDER BY id",
                            [$base['clienteId']]
                        )->getResultArray();
                    } catch (\Throwable $e2) { $disenos = []; }
                }
                // Intento C: cliente_id
                if (!$disenos) {
                    try {
                        $disenos = $db->query(
                            "SELECT * FROM $t WHERE cliente_id = ? ORDER BY id",
                            [$base['clienteId']]
                        )->getResultArray();
                    } catch (\Throwable $e3) { $disenos = []; }
                }

                // Escoger relacionado: último por id
                if ($disenos && !$disenoRelacionado) {
                    $disenoRelacionado = end($disenos); // último
                    // obtener versión más reciente
                    $disId = $disenoRelacionado['id'] ?? ($disenoRelacionado['disenoId'] ?? ($disenoRelacionado['diseno_id'] ?? null));
                    if ($disId) {
                        foreach (['diseno_version','DisenoVersion'] as $tv) {
                            // by disenoId
                            try {
                                $versionRelacionado = $db->query(
                                    "SELECT * FROM $tv WHERE disenoId = ? ORDER BY version DESC, id DESC LIMIT 1",
                                    [$disId]
                                )->getRowArray();
                            } catch (\Throwable $e4) { $versionRelacionado = null; }
                            // by diseno_id
                            if (!$versionRelacionado) {
                                try {
                                    $versionRelacionado = $db->query(
                                        "SELECT * FROM $tv WHERE diseno_id = ? ORDER BY version DESC, id DESC LIMIT 1",
                                        [$disId]
                                    )->getRowArray();
                                } catch (\Throwable $e5) { $versionRelacionado = null; }
                            }
                            if ($versionRelacionado) break;
                        }
                    }
                }
                if ($disenos) break; // ya cargado desde alguna variante
            }
        }

        // 7) Fallback: usar diseño/versión desde OP si existe
        if (!$disenoRelacionado && !empty($base['op_disenoVersionId'])) {
            $ver = null;
            foreach (['diseno_version','DisenoVersion'] as $tv) {
                try {
                    $ver = $db->query("SELECT * FROM $tv WHERE id = ?", [$base['op_disenoVersionId']])->getRowArray();
                    if ($ver) break;
                } catch (\Throwable $e) {}
            }
            if ($ver) {
                $versionRelacionado = $ver;
                // traer el diseño de esa versión
                $dis = null;
                $did = $ver['disenoId'] ?? ($ver['disenoid'] ?? null);
                if ($did) {
                    foreach (['diseno','Diseno'] as $td) {
                        try {
                            $dis = $db->query("SELECT id, clienteld, codigo, nombre, descripcion FROM $td WHERE id = ?", [$did])->getRowArray();
                            if ($dis) break;
                        } catch (\Throwable $e) {}
                    }
                }
                if ($dis) {
                    $disenoRelacionado = $dis;
                }
            }
        }

        // 8) Normalizar campos del diseño seleccionado
        $disenoOut = null;
        if ($disenoRelacionado) {
            $disenoOut = $disenoRelacionado;
            $disenoOut['codigo'] = $disenoRelacionado['codigo'] ?? ($disenoRelacionado['cod'] ?? ($disenoRelacionado['code'] ?? ($disenoRelacionado['clave'] ?? '')));
            $disenoOut['nombre'] = $disenoRelacionado['nombre'] ?? ($disenoRelacionado['name'] ?? ($disenoRelacionado['titulo'] ?? ''));
            $disenoOut['descripcion'] = $disenoRelacionado['descripcion'] ?? ($disenoRelacionado['description'] ?? ($disenoRelacionado['detalle'] ?? ''));
            // anidar versión si no está ya anidada
            if (!isset($disenoOut['version']) || !is_array($disenoOut['version'])) {
                $disenoOut['version'] = $versionRelacionado ?: null;
            }
        }

        return [
            'id' => (int)$base['id'],
            'folio' => $base['folio'] ?? '',
            'fecha' => $base['fecha'] ?? null,
            'estatus' => $base['estatus'] ?? '',
            'moneda' => $base['moneda'] ?? '',
            'total' => $base['total'] ?? 0,
            'cliente' => $cliente,
            'items' => $items,
            'disenos' => $disenos,
            'diseno' => $disenoOut,
        ];
    }
}
