<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;

class Notificaciones1 extends BaseController
{
    public function index()
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        $db = \Config\Database::connect();

        // Listado: no leídas primero, luego más recientes.
        $items = $db->table('notificaciones n')
            ->select('n.*, IFNULL(un.is_leida,0) AS is_leida, un.id AS enlace_id')
            ->join(
                'usuarioNotificacion un',
                'un.idNotificacionFK = n.id AND un.idUserFK = '.$db->escape($userId),
                'left',
                false // no escape en la condición del JOIN
            )
            // FIX: un solo orderBy crudo para evitar que CI meta "ASC" dentro del IFNULL
            ->orderBy('IFNULL(un.is_leida,0) ASC, n.created_at DESC', '', false)
            ->get()->getResultArray();

        $notifCount = (new UsuarioNotificacionModel())->contarNoLeidas($userId);

        return view('modulos/notificaciones1', [
            'title'      => 'Notificaciones',
            'items'      => $items,
            'notifCount' => $notifCount,
        ]);
    }

    // Marca UNA notificación como leída para el usuario
    public function leer(int $notifId)
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        $un = new UsuarioNotificacionModel();
        $row = $un->where([
            'idUserFK'        => $userId,
            'idNotificacionFK'=> $notifId
        ])->first();

        if ($row) {
            $un->update($row['id'], ['is_leida' => 1]);
        } else {
            $un->insert([
                'idUserFK'         => $userId,
                'idNotificacionFK' => $notifId,
                'is_leida'         => 1
            ]);
        }
        return redirect()->back();
    }

    // Marca TODAS como leídas
    public function leerTodas()
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        $db = \Config\Database::connect();
        // marca las que ya tengan enlace
        $db->table('usuarioNotificacion')
            ->where('idUserFK', $userId)
            ->set('is_leida', 1)
            ->update();

        // crea enlaces leídos para las que no existan aún
        $sub = $db->table('usuarioNotificacion')
            ->select('idNotificacionFK')
            ->where('idUserFK', $userId);

        $faltantes = $db->table('notificaciones n')
            ->select('n.id')
            ->whereNotIn('n.id', $sub, false)
            ->get()->getResultArray();

        if ($faltantes) {
            $rows = array_map(fn($r) => [
                'idUserFK'         => $userId,
                'idNotificacionFK' => (int)$r['id'],
                'is_leida'         => 1
            ], $faltantes);
            $db->table('usuarioNotificacion')->insertBatch($rows);
        }

        return redirect()->back();
    }

    // Elimina SOLO el enlace usuario-notificación (no borra la notificación maestra)
    public function eliminar(int $notifId)
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        (new UsuarioNotificacionModel())
            ->where(['idUserFK'=>$userId, 'idNotificacionFK'=>$notifId])
            ->delete();

        return redirect()->back();
    }
}
