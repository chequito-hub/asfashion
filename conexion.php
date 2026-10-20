<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'asfashion_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/asfashion/');

class Conexion {
    private static ?PDO $instancia = null;

    public static function getConexion(): PDO {
        if (self::$instancia === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$instancia;
    }
}

// Token CSRF
function generarCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCSRFToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Registro de Auditoría
function registrarAuditoria($usuarioId, $accion, $tabla, $registroId, $detalles) {
    $db = Conexion::getConexion();
    $stmt = $db->prepare("INSERT INTO bitacora_auditoria (usuario_id, accion, tabla_afectada, registro_id, detalles) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$usuarioId, $accion, $tabla, $registroId, is_array($detalles) ? json_encode($detalles) : $detalles]);
}

// Verificación RBAC por Jerarquía de Rol
function verificarAccesoRol(array $rolesPermitidos) {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol_id'])) {
        header('Location: ' . BASE_URL . 'mi-cuenta.php');
        exit;
    }
    
    // Si no tiene un rol permitido, redirigir
    if (!in_array($_SESSION['rol_id'], $rolesPermitidos)) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function esSuperAdmin(): bool {
    return isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
}
?>