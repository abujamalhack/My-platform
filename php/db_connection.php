<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("فشل الاتصال بقاعدة البيانات: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die(json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]));
        }
    }
    
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
    
    public static function closeConnection() {
        if (self::$instance !== null) {
            self::$instance->conn->close();
            self::$instance = null;
        }
    }
    
    public static function executeQuery($sql, $params = [], $types = "") {
        $conn = self::getConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("خطأ في تنفيذ الاستعلام: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    public static function executeUpdate($sql, $params = [], $types = "") {
        $conn = self::getConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $insert_id = $conn->insert_id;
        
        $stmt->close();
        
        return [
            'success' => $success,
            'affected_rows' => $affected_rows,
            'insert_id' => $insert_id
        ];
    }
    
    public static function getSingle($sql, $params = [], $types = "") {
        $result = self::executeQuery($sql, $params, $types);
        return $result->fetch_assoc();
    }
    
    public static function getAll($sql, $params = [], $types = "") {
        $result = self::executeQuery($sql, $params, $types);
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    public static function beginTransaction() {
        $conn = self::getConnection();
        $conn->begin_transaction();
    }
    
    public static function commit() {
        $conn = self::getConnection();
        $conn->commit();
    }
    
    public static function rollback() {
        $conn = self::getConnection();
        $conn->rollback();
    }
    
    public static function escape($string) {
        $conn = self::getConnection();
        return $conn->real_escape_string($string);
    }
    
    public static function lastInsertId() {
        $conn = self::getConnection();
        return $conn->insert_id;
    }
}

// إنشاء اتصال تلقائي
$conn = Database::getConnection();

// تسجيل الخطأ إذا كان هناك مشكلة في الاتصال
if ($conn->connect_error) {
    error_log("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>