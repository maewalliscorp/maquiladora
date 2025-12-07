-- Agregar campo idcliente a la tabla users
ALTER TABLE users 
ADD COLUMN idcliente INT(11) UNSIGNED NULL AFTER active,
ADD INDEX idx_idcliente (idcliente);

-- Opcional: Agregar llave foránea (solo si la tabla cliente existe)
-- ALTER TABLE users 
-- ADD CONSTRAINT fk_users_cliente 
-- FOREIGN KEY (idcliente) REFERENCES cliente(id) 
-- ON DELETE SET NULL;

-- Comentario para documentación
ALTER TABLE users COMMENT = 'Tabla de usuarios con campo idcliente para asociar clientes';
