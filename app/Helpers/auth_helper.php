<?php

use CodeIgniter\Database\BaseConnection;

if (!function_exists('current_role_name')) {
    function current_role_name(): ?string {
        $role = session()->get('role_name');
        if ($role) { return $role; }
        $userId = session()->get('user_id');
        if (!$userId) { return null; }
        try {
            /** @var BaseConnection $db */
            $db = \Config\Database::connect();
            // Intentar con nombres de tablas comunes
            $queries = [
                'SELECT r.nombre FROM usuario_rol ur JOIN rol r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ? LIMIT 1',
                'SELECT r.nombre FROM Usuario_Rol ur JOIN Rol r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ? LIMIT 1',
                'SELECT r.nombre FROM usuario_rol ur JOIN roles r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ? LIMIT 1',
            ];
            $name = null;
            foreach ($queries as $q) {
                try {
                    $row = $db->query($q, [$userId])->getRowArray();
                    if ($row && isset($row['nombre']) && $row['nombre'] !== '') { $name = (string)$row['nombre']; break; }
                } catch (\Throwable $e) { /* intentar siguiente */ }
            }
            if ($name) {
                session()->set('role_name', $name);
                return $name;
            }
        } catch (\Throwable $e) { /* sin rol */ }
        return null;
    }
}

if (!function_exists('can_menu')) {
    function can_menu(string $perm): bool {
        $role = current_role_name();
        // Normalizar
        $roleNorm = $role ? mb_strtolower(trim($role)) : '';
        if ($roleNorm === 'administrador' || $roleNorm === 'jefe') {
            return true; // todo el menú
        }
        if ($roleNorm === 'inspector') {
            $allowed = [
                'menu.pedidos',
                'menu.ordenes',
                'menu.produccion',
                'menu.muestras',
                'menu.inspeccion',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'almacenista') {
            $allowed = [
                'menu.inventario_almacen',
                'menu.inv_maquinas',
                'menu.desperdicios',
                'menu.incidencias',
                'menu.logistica_preparacion',
                'menu.logistica_gestion',
                'menu.logistica_documentos',
                'menu.planificacion_materiales',
                'menu.mant_correctivo',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'calidad') {
            $allowed = [
                'menu.inspeccion',
                'menu.muestras',
                'menu.pedidos',
                'menu.logistica_preparacion',
                'menu.logistica_gestion',
                'menu.logistica_documentos',
                'menu.desperdicios',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'empleado') {
            $allowed = [
                'menu.produccion',
                'menu.incidencias',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'corte') {
            $allowed = [
                'menu.produccion',
                'menu.incidencias',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'diseñador' || $roleNorm === 'disenador') { // soportar sin tilde
            $allowed = [
                'menu.catalogo_disenos',
                'menu.pedidos',
                'menu.produccion',
                'menu.muestras',
                'menu.desperdicios',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'envios') {
            $allowed = [
                'menu.logistica_preparacion',
                'menu.logistica_gestion',
                'menu.logistica_documentos',
            ];
            return in_array($perm, $allowed, true);
        }
        if ($roleNorm === 'rh' || $roleNorm === 'rrhh') {
            $allowed = [
                'menu.ordenes_clientes',
                'menu.ordenes',
                'menu.usuarios',
                'menu.roles',
            ];
            return in_array($perm, $allowed, true);
        }
        // Otros roles: pendiente (no mostrar)
        return false;
    }
}

// Alias común
if (!function_exists('can')) {
    function can(string $perm): bool { return can_menu($perm); }
}
