<?php

if (!function_exists('clear_user_cache')) {
    /**
     * Limpia el cache de permisos y roles del usuario actual
     */
    function clear_user_cache(): void {
        $session = session();
        $session->remove('cached_permissions');
        $session->remove('cached_roles');
        $session->remove('role_name');
    }
}

if (!function_exists('preload_user_permissions')) {
    /**
     * Precarga todos los permisos del usuario en cache
     */
    function preload_user_permissions(): void {
        $session = session();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return;
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Obtener todos los roles del usuario
            $roles = $db->query(
                'SELECT r.id, r.nombre FROM usuario_rol ur JOIN rol r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ?',
                [$userId]
            )->getResultArray();
            
            if (empty($roles)) {
                return;
            }
            
            $roleIds = array_column($roles, 'id');
            $roleNames = array_column($roles, 'nombre');
            
            // Obtener todos los permisos para estos roles
            $roleIdsStr = implode(',', array_map('intval', $roleIds));
            $permissions = $db->query(
                'SELECT DISTINCT permiso FROM rol_permiso WHERE rol_id IN (' . $roleIdsStr . ')'
            )->getResultArray();
            
            $permissionList = array_column($permissions, 'permiso');
            
            // Guardar en cache de sesión
            $session->set([
                'cached_permissions' => $permissionList,
                'cached_roles' => $roleNames,
                'role_name' => $roleNames[0] ?? null,
            ]);
            
        } catch (\Throwable $e) {
            log_message('error', 'Error precargando permisos: ' . $e->getMessage());
        }
    }
}
