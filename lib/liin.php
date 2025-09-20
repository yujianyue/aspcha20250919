<?php
// 数据导入管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'upload':
        handleUpload();
        break;
    case 'paste':
        handlePaste();
        break;
    default:
        showImportPage();
        break;
}

function showImportPage() {
    global $config, $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>数据导入 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>数据导入</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('list')">数据列表</button>
                <button class="tab active" onclick="switchTab('liin')">数据导入</button>
                <button class="tab" onclick="switchTab('tong')">统计管理</button>
                <button class="tab" onclick="switchTab('site')">系统设置</button>
                <button class="tab" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">数据导入</h3>
                </div>
                
                <div class="import-tabs">
                    <button class="tab active" onclick="switchImportTab('upload')">文件上传</button>
                    <button class="tab" onclick="switchImportTab('paste')">复制粘贴</button>
                </div>
                
                <!-- 文件上传 -->
                <div id="uploadTab" class="import-content">
                    <div class="upload-area" onclick="selectFile()">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text">点击选择文件或拖拽文件到此处</div>
                        <div class="upload-hint">支持 .txt 和 .csv 文件，最大 4MB</div>
                    </div>
                    <input type="file" id="fileInput" style="display: none;" accept=".txt,.csv" onchange="handleFileSelect()">
                    <div id="fileInfo" style="display: none; margin-top: 15px;">
                        <div class="file-info">
                            <span id="fileName"></span>
                            <span id="fileSize"></span>
                            <button class="btn btn-danger" onclick="clearFile()">清除</button>
                        </div>
                    </div>
                    <button id="uploadBtn" class="btn btn-primary" style="margin-top: 15px; display: none;" onclick="uploadFile()">开始导入</button>
                </div>
                
                <!-- 复制粘贴 -->
                <div id="pasteTab" class="import-content" style="display: none;">
                    <div class="form-group">
                        <label>请粘贴Excel数据 (制表符分隔):</label>
                        <textarea id="pasteData" class="form-control" rows="10" placeholder="请从Excel复制数据并粘贴到这里..."></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="pasteImport()">开始导入</button>
                </div>
                
                <div id="importProgress" style="display: none; margin-top: 20px;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text" id="progressText">准备导入...</div>
                </div>
                
                <div id="importResult" style="display: none; margin-top: 20px;"></div>
            </div>
        </div>
        
        <style>
            .import-tabs {
                display: flex;
                border-bottom: 1px solid #e0e0e0;
                margin-bottom: 20px;
            }
            
            .import-content {
                padding: 20px 0;
            }
            
            .upload-area {
                border: 2px dashed #ddd;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .upload-area:hover {
                border-color: #3498db;
                background: #f8f9fa;
            }
            
            .upload-area.dragover {
                border-color: #3498db;
                background: #e3f2fd;
            }
            
            .upload-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            
            .upload-text {
                font-size: 16px;
                color: #333;
                margin-bottom: 10px;
            }
            
            .upload-hint {
                font-size: 14px;
                color: #666;
            }
            
            .file-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                background: #f8f9fa;
                border-radius: 4px;
                border: 1px solid #e9ecef;
            }
            
            .progress-bar {
                width: 100%;
                height: 20px;
                background: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
                margin-bottom: 10px;
            }
            
            .progress-fill {
                height: 100%;
                background: #3498db;
                width: 0%;
                transition: width 0.3s;
            }
            
            .progress-text {
                text-align: center;
                color: #666;
            }
        </style>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let selectedFile = null;
            
            document.addEventListener('DOMContentLoaded', function() {
                // 拖拽上传
                const uploadArea = document.querySelector('.upload-area');
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadArea.classList.add('dragover');
                });
                
                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                });
                
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        handleFile(files[0]);
                    }
                });
            });
            
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
            
            function switchImportTab(tab) {
                document.querySelectorAll('.import-content').forEach(content => {
                    content.style.display = 'none';
                });
                document.querySelectorAll('.import-tabs .tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                if (tab === 'upload') {
                    document.getElementById('uploadTab').style.display = 'block';
                    document.querySelector('.import-tabs .tab:first-child').classList.add('active');
                } else {
                    document.getElementById('pasteTab').style.display = 'block';
                    document.querySelector('.import-tabs .tab:last-child').classList.add('active');
                }
            }
            
            function selectFile() {
                document.getElementById('fileInput').click();
            }
            
            function handleFileSelect() {
                const file = document.getElementById('fileInput').files[0];
                if (file) {
                    handleFile(file);
                }
            }
            
            function handleFile(file) {
                // 验证文件类型
                const allowedTypes = ['text/plain', 'text/csv', 'application/csv'];
                const allowedExts = ['.txt', '.csv'];
                const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                
                if (!allowedTypes.includes(file.type) && !allowedExts.includes(fileExt)) {
                    showToast('请选择 .txt 或 .csv 文件', 'error');
                    return;
                }
                
                // 验证文件大小 (4MB)
                if (file.size > 4 * 1024 * 1024) {
                    showToast('文件大小不能超过 4MB', 'error');
                    return;
                }
                
                selectedFile = file;
                
                // 显示文件信息
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('fileInfo').style.display = 'block';
                document.getElementById('uploadBtn').style.display = 'inline-block';
            }
            
            function clearFile() {
                selectedFile = null;
                document.getElementById('fileInput').value = '';
                document.getElementById('fileInfo').style.display = 'none';
                document.getElementById('uploadBtn').style.display = 'none';
            }
            
            function uploadFile() {
                if (!selectedFile) {
                    showToast('请先选择文件', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('file', selectedFile);
                
                showImportProgress();
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'user.php?do=liin&act=upload', true);
                
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        updateProgress(percentComplete, '上传中...');
                    }
                };
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.code === 1) {
                                    showImportResult(response.data);
                                } else {
                                    showToast(response.msg, 'error');
                                }
                            } catch (e) {
                                showToast('服务器响应错误', 'error');
                            }
                        } else {
                            showToast('上传失败', 'error');
                        }
                        hideImportProgress();
                    }
                };
                
                xhr.send(formData);
            }
            
            function pasteImport() {
                const data = document.getElementById('pasteData').value.trim();
                if (!data) {
                    showToast('请粘贴数据', 'error');
                    return;
                }
                
                showImportProgress();
                
                ajaxRequest('user.php?do=liin&act=paste', {data: data}, function(response) {
                    hideImportProgress();
                    
                    if (response.code === 1) {
                        showImportResult(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function showImportProgress() {
                document.getElementById('importProgress').style.display = 'block';
                document.getElementById('importResult').style.display = 'none';
            }
            
            function hideImportProgress() {
                document.getElementById('importProgress').style.display = 'none';
            }
            
            function updateProgress(percent, text) {
                document.getElementById('progressFill').style.width = percent + '%';
                document.getElementById('progressText').textContent = text;
            }
            
            function showImportResult(data) {
                let html = '<div class="import-result">';
                html += '<h4>导入结果</h4>';
                html += '<div class="result-stats">';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.total + '</div>';
                html += '<div class="stat-label">总记录数</div>';
                html += '</div>';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.success + '</div>';
                html += '<div class="stat-label">成功导入</div>';
                html += '</div>';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.failed + '</div>';
                html += '<div class="stat-label">导入失败</div>';
                html += '</div>';
                html += '</div>';
                
                if (data.errors && data.errors.length > 0) {
                    html += '<div class="error-list">';
                    html += '<h5>错误信息:</h5>';
                    html += '<ul>';
                    data.errors.forEach(error => {
                        html += '<li>' + error + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                
                html += '</div>';
                
                document.getElementById('importResult').innerHTML = html;
                document.getElementById('importResult').style.display = 'block';
            }
        </script>
    </body>
    </html>
    <?php
}

function handleUpload() {
    global $db, $table_prefix, $upload_max_size;
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        error('文件上传失败');
    }
    
    $file = $_FILES['file'];
    
    // 验证文件大小
    if ($file['size'] > $upload_max_size) {
        error('文件大小超过限制');
    }
    
    // 验证文件类型
    $allowed_types = ['text/plain', 'text/csv', 'application/csv'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        error('不支持的文件类型');
    }
    
    // 读取文件内容
    $content = file_get_contents($file['tmp_name']);
    if ($content === false) {
        error('读取文件失败');
    }
    
    // 处理数据
    $result = processImportData($content, 'file');
    
    success('导入完成', $result);
}

function handlePaste() {
    global $db, $table_prefix;
    
    $data = safeFilter($_POST['data'] ?? '');
    if (empty($data)) {
        error('数据不能为空');
    }
    
    // 处理数据
    $result = processImportData($data, 'paste');
    
    success('导入完成', $result);
}

function processImportData($content, $type) {
    global $db, $table_prefix, $config;
    
    $lines = explode("\n", $content);
    $total = 0;
    $success = 0;
    $failed = 0;
    $errors = [];
    
    // 获取系统配置的查询条件
    $condition1 = $config['condition1'] ?? '';
    $condition2 = $config['condition2'] ?? '';
    $condition3 = $config['condition3'] ?? '';
    
    if (empty($condition1)) {
        error('系统未配置查询条件');
    }
    
    // 解析第一行作为字段名
    $header = array_shift($lines);
    $fields = [];
    
    if ($type === 'file') {
        // 文件上传，使用逗号分隔
        $fields = str_getcsv($header);
    } else {
        // 复制粘贴，使用制表符分隔
        $fields = explode("\t", $header);
    }
    
    // 清理字段名
    $fields = array_map(function($field) {
        return preg_replace('/[^a-zA-Z0-9\u4e00-\u9fa5_]/', '', trim($field));
    }, $fields);
    
    // 检查是否包含必需的查询条件字段
    $has_condition1 = in_array($condition1, $fields);
    if (!$has_condition1) {
        error("数据中缺少必需字段: {$condition1}");
    }
    
    $condition1_index = array_search($condition1, $fields);
    $condition2_index = $condition2 ? array_search($condition2, $fields) : false;
    $condition3_index = $condition3 ? array_search($condition3, $fields) : false;
    
    // 生成批次号
    $batch = date('YmdHis') . rand(1000, 9999);
    
    // 批量插入数据
    $batch_data = [];
    $batch_size = 1000;
    
    foreach ($lines as $line_num => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $total++;
        
        try {
            // 解析数据行
            if ($type === 'file') {
                $row_data = str_getcsv($line);
            } else {
                $row_data = explode("\t", $line);
            }
            
            // 确保数据行长度与字段数匹配
            while (count($row_data) < count($fields)) {
                $row_data[] = '';
            }
            
            $condition1_value = $row_data[$condition1_index] ?? '';
            $condition2_value = ($condition2_index !== false) ? ($row_data[$condition2_index] ?? '') : '';
            $condition3_value = ($condition3_index !== false) ? ($row_data[$condition3_index] ?? '') : '';
            
            if (empty($condition1_value)) {
                $failed++;
                $errors[] = "第" . ($line_num + 2) . "行: 条件一不能为空";
                continue;
            }
            
            // 构建详细数据
            $detail_data = [];
            foreach ($fields as $index => $field) {
                if ($index !== $condition1_index && 
                    $index !== $condition2_index && 
                    $index !== $condition3_index) {
                    $detail_data[$field] = $row_data[$index] ?? '';
                }
            }
            
            $batch_data[] = [
                'condition1' => $condition1_value,
                'condition2' => $condition2_value,
                'condition3' => $condition3_value,
                'detail_data' => generateJsonData($detail_data),
                'batch' => $batch,
                'query_status' => 1,
                'add_time' => date('Y-m-d H:i:s'),
                'query_count' => 0
            ];
            
            // 批量插入
            if (count($batch_data) >= $batch_size) {
                batchInsertData($batch_data);
                $success += count($batch_data);
                $batch_data = [];
            }
            
        } catch (Exception $e) {
            $failed++;
            $errors[] = "第" . ($line_num + 2) . "行: " . $e->getMessage();
        }
    }
    
    // 插入剩余数据
    if (!empty($batch_data)) {
        batchInsertData($batch_data);
        $success += count($batch_data);
    }
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'import_data', "导入数据: 总数{$total}, 成功{$success}, 失败{$failed}");
    
    return [
        'total' => $total,
        'success' => $success,
        'failed' => $failed,
        'errors' => array_slice($errors, 0, 50) // 最多显示50个错误
    ];
}

function batchInsertData($data) {
    global $db, $table_prefix;
    
    if (empty($data)) return;
    
    $fields = array_keys($data[0]);
    $field_names = '`' . implode('`, `', $fields) . '`';
    
    $values = [];
    foreach ($data as $row) {
        $row_values = [];
        foreach ($fields as $field) {
            $row_values[] = "'" . addslashes($row[$field]) . "'";
        }
        $values[] = '(' . implode(', ', $row_values) . ')';
    }
    
    $sql = "INSERT INTO `{$table_prefix}_data` ({$field_names}) VALUES " . implode(', ', $values);
    $db->query($sql);
}
?>