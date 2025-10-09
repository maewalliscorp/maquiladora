<?php
// app/Controllers/Mrp.php
namespace App\Controllers;

class Mrp extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // OP activas: ajusta si usas otros estados
        $sql = "
        SELECT 
            a.id                       AS articuloId,
            a.nombre                   AS mat,
            a.unidadMedida             AS u,
            SUM(lm.cantidadPorUnidad * COALESCE(op.cantidadPlan,1)) AS necesidad,
            COALESCE(st.stock, 0)      AS stock,
            GREATEST(
                SUM(lm.cantidadPorUnidad * COALESCE(op.cantidadPlan,1)) - COALESCE(st.stock,0), 
                0
            )                          AS comprar
        FROM orden_produccion op
        JOIN lista_materiales lm  ON lm.disenoVersionId = op.disenoVersionId
        JOIN articulo a           ON a.id = lm.articuloId
        LEFT JOIN (
            SELECT articuloId, SUM(cantidad) AS stock
            FROM stock
            GROUP BY articuloId
        ) st ON st.articuloId = a.id
        WHERE op.status IN ('planeada','liberada','en_proceso')
        GROUP BY a.id, a.nombre, a.unidadMedida, st.stock
        ORDER BY a.nombre ASC
        ";

        $rows = $db->query($sql)->getResultArray();

        // Normaliza campos para la vista
        $reqs = array_map(static function ($r) {
            return [
                'id'        => (int)$r['articuloId'],
                'mat'       => $r['mat'],
                'u'         => $r['u'],
                'necesidad' => (float)$r['necesidad'],
                'stock'     => (float)$r['stock'],
                'comprar'   => (float)$r['comprar'],
            ];
        }, $rows);

        // Sugerencias de OC (simple: un proveedor genérico y ETA +7 días)
        $ocs = [];
        foreach ($reqs as $r) {
            if ($r['comprar'] > 0) {
                $ocs[] = [
                    'id'   => $r['id'],
                    'prov' => 'Proveedor sugerido',
                    'mat'  => $r['mat'],
                    'cant' => $r['comprar'],
                    'u'    => $r['u'],
                    'eta'  => date('Y-m-d', strtotime('+7 days')),
                ];
            }
        }

        return view('modulos/mrp', [
            'title' => 'MRP',
            'reqs'  => $reqs,
            'ocs'   => $ocs,
        ]);
    }

    // Diagnóstico rápido
    public function diag()
    {
        $db  = \Config\Database::connect();
        $out = ['ok'=>true];
        try {
            $out['database'] = $db->getDatabase();
            $out['counts'] = [
                'orden_produccion' => (int)$db->table('orden_produccion')->countAll(),
                'lista_materiales' => (int)$db->table('lista_materiales')->countAll(),
                'articulo'         => (int)$db->table('articulo')->countAll(),
                'stock'            => (int)$db->table('stock')->countAll(),
            ];
            $out['sample'] = $db->query("
                SELECT a.nombre mat, a.unidadMedida u, lm.cantidadPorUnidad,
                       op.id opId, op.status
                FROM orden_produccion op
                JOIN lista_materiales lm ON lm.disenoVersionId = op.disenoVersionId
                JOIN articulo a ON a.id = lm.articuloId
                WHERE op.status IN ('planeada','liberada','en_proceso')
                LIMIT 5
            ")->getResultArray();
        } catch (\Throwable $e) {
            $out = ['ok'=>false, 'error'=>$e->getMessage()];
        }
        return $this->response->setJSON($out, $out['ok'] ? 200 : 500);
    }
}
