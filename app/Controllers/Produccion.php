<?php namespace App\Controllers;

use App\Models\OrdenProduccionModel;

class Produccion extends BaseController
{
    public function ordenes()
    {
        $model   = new OrdenProduccionModel();
        $ordenes = $model->getListado();

        foreach ($ordenes as &$r) {
            $r['ini'] = $r['ini'] ? date('Y-m-d', strtotime($r['ini'])) : '';
            $r['fin'] = $r['fin'] ? date('Y-m-d', strtotime($r['fin'])) : '';
        }

        return view('modulos/m1_ordenes', [
            'title'   => 'Órdenes de Producción',
            'ordenes' => $ordenes,
        ]);
    }

    public function actualizarEstatus()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        $id = (int)($this->request->getPost('id') ?? 0);
        $estatus = trim((string)($this->request->getPost('estatus') ?? ''));
        if ($id <= 0 || $estatus === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        }

        try {
            $db = \Config\Database::connect();
            $db->table('orden_produccion')->where('id', $id)->update(['status' => $estatus]);
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function orden_json($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        $db = \Config\Database::connect();
        // Intento tabla en minúsculas, con joins opcionales si existen
        $sql = "SELECT 
                    op.id,
                    op.ordenCompraId,
                    op.disenoVersionId,
                    op.folio,
                    op.cantidadPlan,
                    op.fechaInicioPlan,
                    op.fechaFinPlan,
                    op.status,
                    d.nombre AS disenoNombre,
                    dv.version AS disenoVersion,
                    dv.fecha   AS disenoFecha,
                    dv.notas   AS disenoNotas,
                    dv.archivoCadUrl    AS disenoArchivoCadUrl,
                    dv.archivoPatronUrl AS disenoArchivoPatronUrl,
                    dv.aprobado         AS disenoAprobado
                FROM orden_produccion op
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d ON d.id = dv.disenoId
                WHERE op.id = ?";
        try {
            $row = $db->query($sql, [$id])->getRowArray();
        } catch (\Throwable $e) {
            // Variante con mayúsculas en nombres de tabla
            $sql2 = "SELECT 
                        op.id,
                        op.ordenCompraId,
                        op.disenoVersionId,
                        op.folio,
                        op.cantidadPlan,
                        op.fechaInicioPlan,
                        op.fechaFinPlan,
                        op.status,
                        d.nombre AS disenoNombre,
                        dv.version AS disenoVersion,
                        dv.fecha   AS disenoFecha,
                        dv.notas   AS disenoNotas,
                        dv.archivoCadUrl    AS disenoArchivoCadUrl,
                        dv.archivoPatronUrl AS disenoArchivoPatronUrl,
                        dv.aprobado         AS disenoAprobado
                     FROM OrdenProduccion op
                     LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                     LEFT JOIN Diseno d ON d.id = dv.disenoId
                     WHERE op.id = ?";
            try {
                $row = $db->query($sql2, [$id])->getRowArray();
            } catch (\Throwable $e2) {
                $row = null;
            }
        }

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden no encontrada']);
        }

        // Normalizar fechas a ISO (incluye hora si disponible)
        $fmt = function($v){ return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        $out = [
            'id'              => (int)$row['id'],
            'ordenCompraId'   => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio'           => $row['folio'] ?? '',
            'cantidadPlan'    => isset($row['cantidadPlan']) ? (int)$row['cantidadPlan'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan'    => $fmt($row['fechaFinPlan'] ?? null),
            'status'          => $row['status'] ?? '',
            'diseno'          => [
                'nombre'  => $row['disenoNombre'] ?? '',
                'version' => $row['disenoVersion'] ?? '',
                'fecha'   => $fmt($row['disenoFecha'] ?? null),
                'notas'   => $row['disenoNotas'] ?? '',
                'archivoCadUrl'    => $row['disenoArchivoCadUrl'] ?? '',
                'archivoPatronUrl' => $row['disenoArchivoPatronUrl'] ?? '',
                'aprobado'         => isset($row['disenoAprobado']) ? (int)$row['disenoAprobado'] : null,
            ],
        ];

        return $this->response->setJSON($out);
    }
}
