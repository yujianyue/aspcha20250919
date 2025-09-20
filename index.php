<?php
require_once 'inc/conn.php';
require_once 'inc/sqls.php';
require_once 'inc/pubs.php';

session_start();

// 获取域名配置
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config_file = "inc/json_{$domain}.txt";

// 读取配置
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
} else {
    // 从数据库读取配置
    $site = $db->fetchRow("SELECT * FROM `{$table_prefix}_site` WHERE domain = '{$domain}'");
    if (!$site) {
        die('域名未配置，请联系管理员');
    }
    $config = $site;
    
    // 缓存配置
    file_put_contents($config_file, json_encode($config));
}

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'query':
        handleQuery();
        break;
    case 'detail':
        handleDetail();
        break;
    case 'captcha':
        generateCaptchaImage();
        break;
    default:
        showQueryPage();
        break;
}

function showQueryPage() {
    global $config;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($config['title'] ?? '查询系统'); ?></title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
        <style>
            .query-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 8px;
                padding: 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .query-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #f0f0f0;
            }
            .query-form {
                margin-bottom: 30px;
            }
            .form-row {
                display: flex;
                gap: 15px;
                margin-bottom: 20px;
                align-items: center;
            }
            .form-row label {
                min-width: 100px;
                font-weight: 500;
            }
            .form-row input, .form-row select {
                flex: 1;
                padding: 10px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            .captcha-row {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .captcha-img {
                width: 100px;
                height: 40px;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }
            .query-btn {
                width: 100%;
                padding: 12px;
                background: #3498db;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .query-btn:hover {
                background: #2980b9;
            }
            .query-btn:disabled {
                background: #bdc3c7;
                cursor: not-allowed;
            }
            .result-container {
                margin-top: 30px;
                display: none;
            }
            .result-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .result-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .result-table th,
            .result-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            .result-table th {
                background: #f8f9fa;
                font-weight: 600;
            }
            .result-table tbody tr:hover {
                background: #f8f9fa;
            }
            .detail-link {
                color: #3498db;
                text-decoration: none;
                cursor: pointer;
            }
            .detail-link:hover {
                text-decoration: underline;
            }
            .no-result {
                text-align: center;
                padding: 40px;
                color: #666;
                font-size: 16px;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                color: #666;
            }
            .loading {
                text-align: center;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="query-container">
                <div class="query-header">
                    <h1><?php echo htmlspecialchars($config['title'] ?? '查询系统'); ?></h1>
                    <button class="btn btn-secondary" onclick="refreshPage()">刷新</button>
                </div>
                
                <div class="query-form">
                    <form id="queryForm">
                        <?php if ($config['condition1']): ?>
                        <div class="form-row">
                            <label><?php echo htmlspecialchars($config['condition1']); ?>:</label>
                            <input type="text" name="condition1" required>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($config['condition2']): ?>
                        <div class="form-row">
                            <label><?php echo htmlspecialchars($config['condition2']); ?>:</label>
                            <?php if ($config['query_rule'] == 'T3'): ?>
                            <select name="condition2">
                                <option value="">请选择</option>
                                <option value="option1">选项1</option>
                                <option value="option2">选项2</option>
                            </select>
                            <?php else: ?>
                            <input type="text" name="condition2">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($config['condition3']): ?>
                        <div class="form-row">
                            <label><?php echo htmlspecialchars($config['condition3']); ?>:</label>
                            <?php if ($config['query_rule'] == 'T3'): ?>
                            <select name="condition3">
                                <option value="">请选择</option>
                                <option value="option1">选项1</option>
                                <option value="option2">选项2</option>
                            </select>
                            <?php else: ?>
                            <input type="text" name="condition3">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($config['captcha_enabled']): ?>
                        <div class="form-row">
                            <label>验证码:</label>
                            <div class="captcha-row">
                                <input type="text" name="captcha" style="width: 120px;" placeholder="请输入验证码">
                                <div class="captcha-img" onclick="refreshCaptcha()" id="captchaImg">点击刷新</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="query-btn" id="queryBtn">查询</button>
                    </form>
                </div>
                
                <div class="result-container" id="resultContainer">
                    <div class="result-header">
                        <h3>查询结果</h3>
                        <div>
                            <button class="btn btn-primary" onclick="printResult()">打印</button>
                            <button class="btn btn-secondary" onclick="resetQuery()">重新查询</button>
                        </div>
                    </div>
                    <div id="resultContent"></div>
                </div>
                
                <div class="footer">
                    <?php echo htmlspecialchars($config['footer_text'] ?? '版权所有'); ?>
                    <?php if ($config['footer_link']): ?>
                    <br><a href="<?php echo htmlspecialchars($config['footer_link']); ?>" target="_blank"><?php echo htmlspecialchars($config['footer_link']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let currentPage = 1;
            let totalPages = 1;
            let queryParams = {};
            
            // 页面加载完成后初始化
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($config['captcha_enabled']): ?>
                refreshCaptcha();
                <?php endif; ?>
                
                // 绑定表单提交事件
                document.getElementById('queryForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    performQuery();
                });
            });
            
            // 刷新验证码
            function refreshCaptcha() {
                const captchaImg = document.getElementById('captchaImg');
                if (captchaImg) {
                    captchaImg.textContent = '加载中...';
                    captchaImg.style.background = '#f8f9fa';
                    setTimeout(() => {
                        captchaImg.textContent = Math.floor(Math.random() * 9000 + 1000);
                        captchaImg.style.background = '#e9ecef';
                    }, 500);
                }
            }
            
            // 执行查询
            function performQuery() {
                const form = document.getElementById('queryForm');
                const formData = new FormData(form);
                queryParams = Object.fromEntries(formData);
                
                showLoading();
                
                ajaxRequest('index.php?act=query', queryParams, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displayResults(response.data);
                        showToast(response.msg, 'success');
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            // 显示查询结果
            function displayResults(data) {
                const container = document.getElementById('resultContainer');
                const content = document.getElementById('resultContent');
                
                if (data.results && data.results.length > 0) {
                    let html = '<table class="result-table">';
                    html += '<thead><tr>';
                    html += '<th>序号</th>';
                    html += '<th>条件一</th>';
                    if (data.condition2) html += '<th>条件二</th>';
                    if (data.condition3) html += '<th>条件三</th>';
                    html += '<th>操作</th>';
                    html += '</tr></thead><tbody>';
                    
                    data.results.forEach((item, index) => {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + (item.condition1 || '') + '</td>';
                        if (data.condition2) html += '<td>' + (item.condition2 || '') + '</td>';
                        if (data.condition3) html += '<td>' + (item.condition3 || '') + '</td>';
                        html += '<td><a href="#" class="detail-link" onclick="showDetail(' + item.id + ')">查看详情</a></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    
                    // 添加分页
                    if (data.pages > 1) {
                        html += generatePaginationHtml(data.current_page, data.pages);
                    }
                    
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<div class="no-result"><?php echo htmlspecialchars($config['no_result_text'] ?? '未找到相关数据'); ?></div>';
                }
                
                container.style.display = 'block';
            }
            
            // 生成分页HTML
            function generatePaginationHtml(current, total) {
                let html = '<div class="pagination">';
                
                // 首页
                if (current > 1) {
                    html += '<a href="#" onclick="goToPage(1)" class="page-btn">首页</a>';
                } else {
                    html += '<span class="page-btn disabled">首页</span>';
                }
                
                // 上一页
                if (current > 1) {
                    html += '<a href="#" onclick="goToPage(' + (current - 1) + ')" class="page-btn">上一页</a>';
                } else {
                    html += '<span class="page-btn disabled">上一页</span>';
                }
                
                // 页码选择
                html += '<select class="page-select" onchange="goToPage(this.value)">';
                for (let i = 1; i <= total; i++) {
                    const selected = i == current ? 'selected' : '';
                    html += '<option value="' + i + '" ' + selected + '>第' + i + '页</option>';
                }
                html += '</select>';
                
                // 下一页
                if (current < total) {
                    html += '<a href="#" onclick="goToPage(' + (current + 1) + ')" class="page-btn">下一页</a>';
                } else {
                    html += '<span class="page-btn disabled">下一页</span>';
                }
                
                // 末页
                if (current < total) {
                    html += '<a href="#" onclick="goToPage(' + total + ')" class="page-btn">末页</a>';
                } else {
                    html += '<span class="page-btn disabled">末页</span>';
                }
                
                html += '</div>';
                return html;
            }
            
            // 跳转页面
            function goToPage(page) {
                currentPage = parseInt(page);
                queryParams.page = currentPage;
                
                showLoading();
                ajaxRequest('index.php?act=query', queryParams, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        displayResults(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            // 显示详情
            function showDetail(id) {
                showLoading();
                ajaxRequest('index.php?act=detail', {id: id}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showDetailModal(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            // 显示详情模态框
            function showDetailModal(data) {
                let content = '<div class="detail-content">';
                content += '<h4>详细信息</h4>';
                content += '<table class="table">';
                for (const key in data) {
                    if (data.hasOwnProperty(key) && key !== 'id') {
                        content += '<tr><td><strong>' + key + '</strong></td><td>' + (data[key] || '') + '</td></tr>';
                    }
                }
                content += '</table>';
                content += '</div>';
                
                showModal('详情信息', content, [
                    {text: '关闭', type: 'secondary', action: 'closeModal()'}
                ]);
            }
            
            // 打印结果
            function printResult() {
                const resultContent = document.getElementById('resultContent');
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>查询结果</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                            th { background: #f0f0f0; }
                        </style>
                    </head>
                    <body>
                        <h2>查询结果</h2>
                        ${resultContent.innerHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
            
            // 重置查询
            function resetQuery() {
                document.getElementById('queryForm').reset();
                document.getElementById('resultContainer').style.display = 'none';
                <?php if ($config['captcha_enabled']): ?>
                refreshCaptcha();
                <?php endif; ?>
            }
        </script>
    </body>
    </html>
    <?php
}

function handleQuery() {
    global $db, $table_prefix, $config;
    
    $condition1 = safeFilter($_POST['condition1'] ?? '');
    $condition2 = safeFilter($_POST['condition2'] ?? '');
    $condition3 = safeFilter($_POST['condition3'] ?? '');
    $captcha = safeFilter($_POST['captcha'] ?? '');
    $page = intval($_POST['page'] ?? 1);
    
    // 验证必填条件
    if (empty($condition1)) {
        error('条件一不能为空');
    }
    
    // 验证验证码
    if ($config['captcha_enabled'] && !verifyCaptcha($captcha)) {
        error('验证码错误');
    }
    
    // 构建查询条件
    $where = "condition1 = '{$condition1}' AND query_status = 1";
    if (!empty($condition2)) {
        $where .= " AND condition2 = '{$condition2}'";
    }
    if (!empty($condition3)) {
        $where .= " AND condition3 = '{$condition3}'";
    }
    
    // 根据匹配规则构建查询
    $match_rule = $config['match_rule'] ?? 'd';
    switch ($match_rule) {
        case 'b':
            $where = str_replace("condition1 = '{$condition1}'", "condition1 LIKE '%{$condition1}%'", $where);
            break;
        case 's':
            $where = str_replace("condition1 = '{$condition1}'", "condition1 LIKE '{$condition1}%'", $where);
            break;
        case 'e':
            $where = str_replace("condition1 = '{$condition1}'", "condition1 LIKE '%{$condition1}'", $where);
            break;
        case 'k':
            $keywords = explode(' ', $condition1);
            $like_conditions = [];
            foreach ($keywords as $keyword) {
                if (!empty($keyword)) {
                    $like_conditions[] = "condition1 LIKE '%{$keyword}%'";
                }
            }
            if (!empty($like_conditions)) {
                $where = str_replace("condition1 = '{$condition1}'", "(" . implode(' AND ', $like_conditions) . ")", $where);
            }
            break;
    }
    
    // 获取分页数据
    $page_data = $db->getPageData("{$table_prefix}_data", $where, "id DESC", $page, $config['page_size'] ?? 20);
    
    // 更新查询次数
    $db->query("UPDATE `{$table_prefix}_data` SET query_count = query_count + 1 WHERE {$where}");
    
    // 记录日志
    writeLog('guest', $_SERVER['HTTP_HOST'], getClientIP(), 'query', "查询条件: {$condition1}");
    
    success($config['has_result_text'] ?? '查询成功', $page_data);
}

function handleDetail() {
    global $db, $table_prefix;
    
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        error('参数错误');
    }
    
    $data = $db->fetchRow("SELECT * FROM `{$table_prefix}_data` WHERE id = {$id}");
    if (!$data) {
        error('数据不存在');
    }
    
    // 解析详细数据
    $detail_data = [];
    if ($data['detail_data']) {
        $detail_data = parseJsonData($data['detail_data']);
    }
    
    // 合并基础字段和详细数据
    $result = array_merge([
        'id' => $data['id'],
        'condition1' => $data['condition1'],
        'condition2' => $data['condition2'],
        'condition3' => $data['condition3'],
        'batch' => $data['batch'],
        'add_time' => $data['add_time'],
        'query_count' => $data['query_count']
    ], $detail_data);
    
    success('获取详情成功', $result);
}

function generateCaptchaImage() {
    session_start();
    $code = generateCaptcha();
    
    // 创建验证码图片
    $width = 100;
    $height = 40;
    $image = imagecreate($width, $height);
    
    // 设置颜色
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    
    // 填充背景
    imagefill($image, 0, 0, $bg_color);
    
    // 添加干扰线
    for ($i = 0; $i < 5; $i++) {
        $line_color = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
        imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
    }
    
    // 添加文字
    imagestring($image, 5, 25, 10, $code, $text_color);
    
    // 输出图片
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}
?>