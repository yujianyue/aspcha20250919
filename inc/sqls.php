<?php
require_once 'conn.php';

class Database {
    private $mysqli;
    private $prefix;
    
    public function __construct() {
        global $mysqli, $table_prefix;
        $this->mysqli = $mysqli;
        $this->prefix = $table_prefix;
    }
    
    // 执行查询
    public function query($sql) {
        $result = $this->mysqli->query($sql);
        if (!$result) {
            throw new Exception('数据库查询错误: ' . $this->mysqli->error);
        }
        return $result;
    }
    
    // 获取单行数据
    public function fetchRow($sql) {
        $result = $this->query($sql);
        return $result->fetch_assoc();
    }
    
    // 获取多行数据
    public function fetchAll($sql) {
        $result = $this->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    // 插入数据
    public function insert($table, $data) {
        $fields = implode('`, `', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO `{$this->prefix}_{$table}` (`{$fields}`) VALUES ({$values})";
        $this->query($sql);
        return $this->mysqli->insert_id;
    }
    
    // 更新数据
    public function update($table, $data, $where) {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "`{$key}` = '{$value}'";
        }
        $sql = "UPDATE `{$this->prefix}_{$table}` SET " . implode(', ', $sets) . " WHERE {$where}";
        return $this->query($sql);
    }
    
    // 删除数据
    public function delete($table, $where) {
        $sql = "DELETE FROM `{$this->prefix}_{$table}` WHERE {$where}";
        return $this->query($sql);
    }
    
    // 检查表是否存在
    public function tableExists($table) {
        $sql = "SHOW TABLES LIKE '{$this->prefix}_{$table}'";
        $result = $this->query($sql);
        return $result->num_rows > 0;
    }
    
    // 创建表
    public function createTable($table, $fields) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}_{$table}` ({$fields}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return $this->query($sql);
    }
    
    // 获取分页数据
    public function getPageData($table, $where = '', $order = '', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where_clause = $where ? "WHERE {$where}" : '';
        $order_clause = $order ? "ORDER BY {$order}" : '';
        
        // 获取总数
        $count_sql = "SELECT COUNT(*) as total FROM `{$this->prefix}_{$table}` {$where_clause}";
        $total = $this->fetchRow($count_sql)['total'];
        
        // 获取数据
        $data_sql = "SELECT * FROM `{$this->prefix}_{$table}` {$where_clause} {$order_clause} LIMIT {$offset}, {$limit}";
        $data = $this->fetchAll($data_sql);
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    // 自动创建表
    public function autoCreateTable($table, $fields) {
        if (!$this->tableExists($table)) {
            $this->createTable($table, $fields);
        }
    }
}

$db = new Database();
?>