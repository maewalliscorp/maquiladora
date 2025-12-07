-- Script para agregar el permiso menu.pagos y asignarlo a roles apropiados

-- 1. Insertar el permiso menu.pagos si no existe
INSERT IGNORE INTO permisos (nombre, descripcion, created_at) 
VALUES ('menu.pagos', 'Acceso al módulo de Pagos de Empleados', NOW());

-- 2. Obtener el ID del permiso recién insertado
SET @permiso_id = (SELECT id FROM permisos WHERE nombre = 'menu.pagos');

-- 3. Asignar el permiso a roles Administrador, Jefe y RH
INSERT IGNORE INTO rol_permiso (rol_id, permiso_id, created_at)
SELECT r.id, @permiso_id, NOW()
FROM roles r 
WHERE r.nombre IN ('Administrador', 'Jefe', 'RH');

-- 4. Verificar los permisos asignados
SELECT 
    r.nombre as rol,
    p.nombre as permiso,
    rp.created_at as fecha_asignacion
FROM roles r
JOIN rol_permiso rp ON r.id = rp.rol_id
JOIN permisos p ON rp.permiso_id = p.id
WHERE p.nombre = 'menu.pagos'
ORDER BY r.nombre;
