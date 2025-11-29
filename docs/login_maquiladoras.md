# Login de Maquiladoras

## Descripción
Se ha creado un nuevo sistema de login específico para maquiladoras llamado `login_maquiladoras`. Este login tiene características especiales diseñadas para usuarios asociados a maquiladoras.

## Ubicación
- **Vista**: `app/Views/login_maquiladoras.php`
- **Controlador**: `app/Controllers/UsuarioController.php`
  - Método: `login_maquiladoras()`
  - Método: `authenticate_maquiladoras()`
- **Rutas**: `app/Config/Routes.php`

## Características Principales

### 1. Diseño Visual
- **Gradiente moderno**: Fondo con gradiente púrpura (#667eea a #764ba2)
- **Tarjeta con efecto glassmorphism**: Fondo semi-transparente con blur
- **Iconos Bootstrap**: Iconos visuales para mejor UX
- **Responsive**: Adaptable a todos los dispositivos
- **Animaciones suaves**: Efectos hover y transiciones

### 2. Validaciones Especiales
El login de maquiladoras incluye validaciones adicionales:
- Verifica que el usuario esté activo
- **Valida que el usuario tenga una maquiladora asignada** (`maquiladoraIdFK`)
- Si no tiene maquiladora asignada, muestra un error específico

### 3. Datos de Sesión Adicionales
Cuando un usuario inicia sesión por el portal de maquiladoras, se guardan datos adicionales en la sesión:
```php
'maquiladora_id' => ID de la maquiladora
'maquiladora_nombre' => Nombre de la maquiladora
'login_type' => 'maquiladora' // Identificador del tipo de login
```

### 4. Seguridad
- Control de caché para evitar sesiones pegajosas
- Regeneración de ID de sesión
- Validación de credenciales con password_verify
- Headers de seguridad HTTP

## Acceso

### URL de Acceso
```
http://localhost/login_maquiladoras
```

### Diferencias con el Login Estándar

| Característica | Login Estándar | Login Maquiladoras |
|----------------|----------------|-------------------|
| Diseño | Panel lateral con logo | Tarjeta centrada con gradiente |
| Validación Maquiladora | No | Sí (obligatorio) |
| Datos de Sesión | Básicos | Incluye info de maquiladora |
| Público Objetivo | Todos los usuarios | Solo usuarios de maquiladoras |
| Enlace de Retorno | Link a registro | Link a login estándar |

## Flujo de Autenticación

1. Usuario accede a `/login_maquiladoras`
2. Ingresa correo y contraseña
3. Sistema valida:
   - Credenciales correctas
   - Usuario activo
   - **Maquiladora asignada**
4. Si todo es correcto:
   - Carga roles y permisos
   - Carga información de la maquiladora
   - Guarda datos en sesión
   - Redirige a `/dashboard`
5. Si falla alguna validación:
   - Muestra mensaje de error específico
   - Mantiene el correo en el formulario

## Mensajes de Error

- **Credenciales incorrectas**: "Correo o contraseña incorrectos."
- **Cuenta inactiva**: "Tu cuenta está inactiva. Contacta al administrador."
- **Sin maquiladora asignada**: "Este usuario no tiene una maquiladora asignada. Contacta al administrador."

## Uso en el Código

### Verificar Tipo de Login
```php
if (session()->get('login_type') === 'maquiladora') {
    // Usuario logueado desde portal de maquiladoras
    $maquiladoraNombre = session()->get('maquiladora_nombre');
}
```

### Obtener Información de la Maquiladora
```php
$maquiladoraId = session()->get('maquiladora_id');
$maquiladoraNombre = session()->get('maquiladora_nombre');
```

## Personalización Futura

El login de maquiladoras puede ser personalizado para:
- Agregar campos adicionales (código de maquiladora, etc.)
- Implementar autenticación de dos factores
- Agregar captcha para mayor seguridad
- Personalizar el logo según la maquiladora
- Agregar términos y condiciones específicos

## Notas Técnicas

- El login utiliza Bootstrap 5.3.8 con integridad SHA-384
- Los estilos están embebidos en la vista para facilitar personalización
- La validación del formulario usa la API de validación de Bootstrap
- Se implementa control de caché para evitar problemas con el botón "atrás" del navegador
