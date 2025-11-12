<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PlanMantenimientoModel;
use App\Models\MttoModel;
use App\Models\UsuarioNotificacionModel;
use CodeIgniter\I18n\Time;

class MttoAlertas extends BaseController
{
    // Endpoint tipo “cron” (GET /mtto/alertas/run)
    public function run()
    {
        $plan  = new PlanMantenimientoModel();
        $mtto  = new MttoModel();
        $notif = new UsuarioNotificacionModel();

        $hoy   = Time::today();
        $hasta = (clone $hoy)->addMonths(2);

        $reglas = $plan->where('activo',1)->findAll();

        foreach ($reglas as $r) {
            if (empty($r['fecha_inicio'])) continue;

            $fecha = Time::parse($r['fecha_inicio']);
            if ($fecha->isBefore($hoy)) $fecha = $hoy;

            $step = max(1, (int)($r['intervalo'] ?? 1));
            $freq = $r['frecuencia'] ?? 'meses';

            while ($fecha->isBefore($hasta)) {
                $exists = $mtto->where([
                    'maquinaId'        => $r['maquinaId'],
                    'programacion_id'  => $r['id'],
                    'fecha_programada' => $fecha->toDateString()
                ])->first();

                if (!$exists) {
                    $mtto->insert([
                        'maquinaId'        => $r['maquinaId'],
                        'responsableId'    => $r['responsable_id'] ?? null,
                        'tipo'             => $r['tipo'],
                        'estatus'          => 'pendiente',
                        'descripcion'      => $r['tareas'] ?? null,
                        'programacion_id'  => $r['id'],
                        'fecha_programada' => $fecha->toDateString(),
                        'fechaApertura'    => $hoy->toDateString()
                    ]);
                }

                $anticipa = (int)($r['anticipacion_dias'] ?? 7);
                $limite   = (clone $fecha)->subDays($anticipa);

                if ($hoy->toDateString() >= $limite->toDateString() && !empty($r['responsable_id'])) {
                    $titulo  = 'Mantenimiento próximo';
                    $mensaje = "La máquina #{$r['maquinaId']} tiene revisión el ".$fecha->toDateString();

                    $dup = $notif->where([
                        'user_id' => $r['responsable_id'],
                        'titulo'  => $titulo
                    ])->like('mensaje', $fecha->toDateString())->first();

                    if (!$dup) {
                        $notif->insert([
                            'user_id' => $r['responsable_id'],
                            'titulo'  => $titulo,
                            'mensaje' => $mensaje,
                            'url'     => site_url('notificaciones1'),
                            'leido'   => 0
                        ]);
                    }
                }

                switch ($freq) {
                    case 'dias':    $fecha = $fecha->addDays($step);   break;
                    case 'semanas': $fecha = $fecha->addDays(7*$step); break;
                    default:        $fecha = $fecha->addMonths($step); break;
                }
            }
        }

        return $this->response->setJSON(['ok'=>true, 'msg'=>'Revisiones y alertas procesadas']);
    }
}
