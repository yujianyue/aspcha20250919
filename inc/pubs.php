<?php
// 公共函数库

// JSON响应函数
function jsonResponse($code, $msg, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 成功响应
function success($msg, $data = []) {
    jsonResponse(1, $msg, $data);
}

// 错误响应
function error($msg, $data = []) {
    jsonResponse(0, $msg, $data);
}

// 安全过滤函数
function safeFilter($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// 验证手机号
function validatePhone($phone) {
    return preg_match('/^1[3-9]\d{9}$/', $phone);
}

// 验证密码格式
function validatePassword($password) {
    return preg_match('/^[a-zA-Z0-9]{6,16}$/', $password);
}

// 生成随机字符串
function generateRandomString($length = 32) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/62))), 0, $length);
}

// 验证登录状态
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        error('请先登录');
    }
}

// 验证管理员登录
function checkAdminLogin() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        error('请先登录管理员账户');
    }
}

// 记录日志
function writeLog($user, $domain, $ip, $act, $remark = '') {
    global $db;
    $data = [
        'log_time' => date('Y-m-d H:i:s'),
        'user' => $user,
        'domain' => $domain,
        'ip' => $ip,
        'act' => $act,
        'remark' => $remark
    ];
    $db->insert('logs', $data);
}

// 获取客户端IP
function getClientIP() {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return explode(',', $ip)[0];
}

// 验证码生成
function generateCaptcha() {
    $code = rand(1000, 9999);
    $_SESSION['captcha'] = $code;
    return $code;
}

// 验证码验证
function verifyCaptcha($input) {
    return isset($_SESSION['captcha']) && $_SESSION['captcha'] == $input;
}

// 分页HTML生成
function generatePagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // 首页
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '&page=1" class="page-btn">首页</a>';
    } else {
        $html .= '<span class="page-btn disabled">首页</span>';
    }
    
    // 上一页
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '&page=' . ($current_page - 1) . '" class="page-btn">上一页</a>';
    } else {
        $html .= '<span class="page-btn disabled">上一页</span>';
    }
    
    // 页码选择
    $html .= '<select class="page-select" onchange="location.href=\'' . $base_url . '&page=\'+this.value">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $selected = $i == $current_page ? 'selected' : '';
        $html .= "<option value=\"{$i}\" {$selected}>第{$i}页</option>";
    }
    $html .= '</select>';
    
    // 下一页
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '&page=' . ($current_page + 1) . '" class="page-btn">下一页</a>';
    } else {
        $html .= '<span class="page-btn disabled">下一页</span>';
    }
    
    // 末页
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '&page=' . $total_pages . '" class="page-btn">末页</a>';
    } else {
        $html .= '<span class="page-btn disabled">末页</span>';
    }
    
    $html .= '</div>';
    return $html;
}

// 解析JSON数据
function parseJsonData($json_str) {
    $data = [];
    if ($json_str) {
        $lines = explode("\n", $json_str);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line) {
                $parts = explode(',', $line, 2);
                if (count($parts) == 2) {
                    $data[$parts[0]] = $parts[1];
                }
            }
        }
    }
    return $data;
}

// 生成JSON数据
function generateJsonData($data) {
    $lines = [];
    foreach ($data as $key => $value) {
        $lines[] = $key . ',' . $value;
    }
    return implode("\n", $lines);
}
?>