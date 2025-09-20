<?php
// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 包含配置
include 'config.php';

// 错误处理函数
function handleError($message, $code = 500) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取请求参数
$action = $_POST['action'] ?? '';
$keyword = trim($_POST['keyword'] ?? '');
$type = $_POST['type'] ?? 'zi';
$page = intval($_POST['page'] ?? 1);

// 验证参数
if ($action !== 'search') {
    handleError('无效的请求', 400);
}

if (empty($keyword)) {
    handleError('请输入查询内容', 400);
}

if ($page < 1) {
    $page = 1;
}

// 数据库连接
$dbsname = realpath($dbdir);
if (!$dbsname || !file_exists($dbsname)) {
    handleError('数据库文件不存在', 500);
}

$cstr = "DRIVER=Microsoft Access Driver (*.mdb);DBQ={$dbsname}";
$conn = @odbc_connect($cstr, "", "", SQL_CUR_USE_ODBC);

if (!$conn) {
    handleError('数据库连接失败', 500);
}

try {
    // 构建查询条件
    $whereClause = buildWhereClause($type, $keyword, $baoha);
    
    // 获取总记录数
    $countSql = "SELECT COUNT(*) as total FROM kangxi WHERE {$whereClause}";
    $countResult = @odbc_exec($conn, $countSql);
    
    if (!$countResult) {
        throw new Exception('查询失败');
    }
    
    $countRow = @odbc_fetch_array($countResult);
    $totalRecords = intval($countRow['total']);
    
    if ($totalRecords == 0) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'totalPages' => 0,
            'currentPage' => $page,
            'totalRecords' => 0,
            'fields' => getFieldConfig()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 计算分页
    $totalPages = min(ceil($totalRecords / $pagex), $pagem);
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $pagex;
    
    // 获取数据
    $dataSql = "SELECT * FROM kangxi WHERE {$whereClause} ORDER BY id LIMIT {$pagex} OFFSET {$offset}";
    $dataResult = @odbc_exec($conn, $dataSql);
    
    if (!$dataResult) {
        throw new Exception('数据查询失败');
    }
    
    // 处理数据
    $data = [];
    $fields = getFieldConfig();
    
    while ($row = @odbc_fetch_array($dataResult)) {
        $processedRow = [];
        foreach ($fields as $field) {
            $value = $row[$field['name']] ?? '';
            $processedRow[$field['name']] = convertEncoding($value);
        }
        $data[] = $processedRow;
    }
    
    // 返回结果
    echo json_encode([
        'success' => true,
        'data' => $data,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalRecords' => $totalRecords,
        'fields' => $fields
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    handleError('查询出错: ' . $e->getMessage(), 500);
} finally {
    @odbc_close($conn);
}

// 构建WHERE子句
function buildWhereClause($type, $keyword, $exactMatch) {
    $keyword = addslashes($keyword);
    
    // 根据查询类型选择字段
    $searchFields = [];
    switch ($type) {
        case 'zi':
            $searchFields = ['zi'];
            break;
        case 'lu':
            $searchFields = ['lu'];
            break;
        case 'shu':
            $searchFields = ['shu'];
            break;
        default:
            $searchFields = ['zi', 'lu', 'shu'];
    }
    
    $conditions = [];
    foreach ($searchFields as $field) {
        if ($exactMatch == '1') {
            $conditions[] = "{$field} = '{$keyword}'";
        } else {
            $conditions[] = "{$field} LIKE '%{$keyword}%'";
        }
    }
    
    return '(' . implode(' OR ', $conditions) . ')';
}

// 获取字段配置
function getFieldConfig() {
    global $field_map, $itiao, $ihide, $isurl, $isimg;
    
    $fields = [];
    $searchFields = explode('||', trim($itiao, '|'));
    $hideFields = explode('||', trim($ihide, '|'));
    $urlFields = explode('||', trim($isurl, '|'));
    $imgFields = explode('||', trim($isimg, '|'));
    
    // 定义所有可能的字段
    $allFields = ['id', 'zi', 'lu', 'shu'];
    
    foreach ($allFields as $fieldName) {
        $isVisible = !in_array($fieldName, $hideFields);
        $isSearchable = in_array($fieldName, $searchFields);
        $isUrl = in_array($fieldName, $urlFields);
        $isImg = in_array($fieldName, $imgFields);
        
        $fields[] = [
            'name' => $fieldName,
            'label' => $field_map[$fieldName] ?? $fieldName,
            'visible' => $isVisible,
            'searchable' => $isSearchable,
            'type' => $isImg ? 'image' : ($isUrl ? 'url' : 'text')
        ];
    }
    
    return $fields;
}

// 编码转换函数
function convertEncoding($text) {
    if (empty($text)) {
        return $text;
    }
    
    // 检测编码
    $encoding = mb_detect_encoding($text, ['UTF-8', 'GBK', 'GB2312', 'BIG5'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        return mb_convert_encoding($text, 'UTF-8', $encoding);
    }
    
    return $text;
}
?>