<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Gestor de Conexión a Base de Datos - Patrón Singleton
 * Sistema: Podología Ana Ramírez
 * 
 * Diseñado para operar transparentemente tanto en desarrollo local (macOS/Windows)
 * como en producción sobre Linux VPS (Nginx + MariaDB/MySQL).
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    // Configuración con soporte para variables de entorno (VPS / Docker)
    // y valores predeterminados para desarrollo local (macOS / MAMP / nativo)
    private string $host;
    private string $port;
    private string $dbName;
    private string $username;
    private string $password;
    private string $charset;

    /**
     * Constructor privado para forzar el patrón Singleton
     */
    private function __construct()
    {
        $this->host     = getenv('DB_HOST')     ?: '127.0.0.1';
        $this->port     = getenv('DB_PORT')     ?: '3306';
        $this->dbName   = getenv('DB_NAME')     ?: 'podoana_db';
        $this->username = getenv('DB_USER')     ?: 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $this->charset  = getenv('DB_CHARSET')  ?: 'utf8mb4';

        $this->conectar();
    }

    /**
     * Establece la conexión PDO con directivas de seguridad estrictas
     */
    private function conectar(): void
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset={$this->charset}";

        $opciones = [
            // Lanzar excepciones inmediatamente ante cualquier error SQL
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Retornar resultados exclusivamente como arreglos asociativos
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Desactivar emulación de consultas preparadas para máxima protección contra SQLi
            PDO::ATTR_EMULATE_PREPARES => false,
            // Forzar timeout de conexión razonable
            PDO::ATTR_TIMEOUT => 5,
            // Establecer collation uniforme en la sesión
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $opciones);
        } catch (PDOException $e) {
            // Registro en log del sistema sin exponer credenciales en la vista
            error_log("[Database Connection Error] " . $e->getMessage());
            throw new RuntimeException("No se pudo conectar a la base de datos de Podología Ana Ramírez. Verifique el servicio MySQL/MariaDB.");
        }
    }

    /**
     * Obtiene la instancia única de la clase Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna el objeto PDO activo
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Atajo estático rápido para obtener la conexión PDO
     * Ejemplo de uso: $pdo = Database::pdo();
     */
    public static function pdo(): PDO
    {
        return self::getInstance()->getConnection();
    }

    /**
     * Inicia una transacción de base de datos
     */
    public static function beginTransaction(): bool
    {
        return self::pdo()->beginTransaction();
    }

    /**
     * Confirma una transacción activa
     */
    public static function commit(): bool
    {
        return self::pdo()->commit();
    }

    /**
     * Revierte los cambios de una transacción
     */
    public static function rollBack(): bool
    {
        if (self::pdo()->inTransaction()) {
            return self::pdo()->rollBack();
        }
        return false;
    }

    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("No está permitido deserializar una instancia de Database (Singleton).");
    }
}
