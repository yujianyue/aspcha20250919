<?php
// 统计管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_stats':
        getStats();
        break;
    case 'download':
        downloadData();
        break;
    case 'clear':
        clearData();
        break;
    default:
        showStatsPage();
        break;
}

function showStatsPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>统计管理 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>统计管理</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('list')">数据列表</button>
                <button class="tab" onclick="switchTab('liin')">数据导入</button>
                <button class="tab active" onclick="switchTab('tong')">统计管理</button>
                <button class="tab" onclick="switchTab('site')">系统设置</button>
                <button class="tab" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">批次统计</h3>
                </div>
                
                <div class="search-box">
                    <select id="batchSelect" class="form-control" style="width: 200px;">
                        <option value="">选择批次</option>
                    </select>
                    <button class="btn btn-primary" onclick="loadStats()">查看统计</button>
                    <button class="btn btn-success" onclick="downloadData()">下载数据</button>
                    <button class="btn btn-danger" onclick="clearData()">清空批次</button>
                </div>
                
                <div id="statsContent"></div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                loadBatches();
            });
            
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
            
            function loadBatches() {
                ajaxRequest('user.php?do=tong&act=get_stats', {type: 'batches'}, function(response) {
                    if (response.code === 1) {
                        const select = document.getElementById('batchSelect');
                        select.innerHTML = '<option value="">选择批次</option>';
                        
                        response.data.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.batch;
                            option.textContent = batch.batch + ' (' + batch.count + '条)';
                            select.appendChild(option);
                        });
                    }
                });
            }
            
            function loadStats() {
                const batch = document.getElementById('batchSelect').value;
                if (!batch) {
                    showToast('请选择批次', 'warning');
                    return;
                }
                
                showLoading();
                ajaxRequest('user.php?do=tong&act=get_stats', {type: 'stats', batch: batch}, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displayStats(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function displayStats(data) {
                let html = '<div class="stats">';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.total + '</div>';
                html += '<div class="stat-label">总记录数</div>';
                html += '</div>';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.queried + '</div>';
                html += '<div class="stat-label">已查询</div>';
                html += '</div>';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.unqueried + '</div>';
                html += '<div class="stat-label">未查询</div>';
                html += '</div>';
                html += '<div class="stat-item">';
                html += '<div class="stat-number">' + data.query_rate + '%</div>';
                html += '<div class="stat-label">查询率</div>';
                html += '</div>';
                html += '</div>';
                
                // 添加详细统计表格
                html += '<div class="table-container" style="margin-top: 20px;">';
                html += '<table class="table">';
                html += '<thead><tr>';
                html += '<th>条件一</th>';
                html += '<th>条件二</th>';
                html += '<th>条件三</th>';
                html += '<th>查询次数</th>';
                html += '<th>最后查询时间</th>';
                html += '</tr></thead><tbody>';
                
                if (data.details && data.details.length > 0) {
                    data.details.forEach(item => {
                        html += '<tr>';
                        html += '<td>' + (item.condition1 || '') + '</td>';
                        html += '<td>' + (item.condition2 || '') + '</td>';
                        html += '<td>' + (item.condition3 || '') + '</td>';
                        html += '<td>' + item.query_count + '</td>';
                        html += '<td>' + (item.last_query_time || '从未查询') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="5" style="text-align: center;">暂无数据</td></tr>';
                }
                
                html += '</tbody></table>';
                html += '</div>';
                
                document.getElementById('statsContent').innerHTML = html;
            }
            
            function downloadData() {
                const batch = document.getElementById('batchSelect').value;
                if (!batch) {
                    showToast('请选择批次', 'warning');
                    return;
                }
                
                window.open('user.php?do=tong&act=download&batch=' + encodeURIComponent(batch));
            }
            
            function clearData() {
                const batch = document.getElementById('batchSelect').value;
                if (!batch) {
                    showToast('请选择批次', 'warning');
                    return;
                }
                
                confirmDialog('确定要清空该批次的所有数据吗？此操作不可恢复！', 'performClear()');
            }
            
            function performClear() {
                const batch = document.getElementById('batchSelect').value;
                
                showLoading();
                ajaxRequest('user.php?do=tong&act=clear', {batch: batch}, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('清空成功', 'success');
                        loadBatches();
                        document.getElementById('statsContent').innerHTML = '';
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
        </script>
    </body>
    </html>
    <?php
}

function getStats() {
    global $db, $table_prefix;
    
    $type = safeFilter($_GET['type'] ?? '');
    $batch = safeFilter($_GET['batch'] ?? '');
    
    if ($type === 'batches') {
        // 获取所有批次
        $batches = $db->fetchAll("
            SELECT batch, COUNT(*) as count 
            FROM `{$table_prefix}_data` 
            GROUP BY batch 
            ORDER BY batch DESC
        ");
        
        success('获取批次成功', $batches);
    } elseif ($type === 'stats') {
        // 获取指定批次的统计
        if (empty($batch)) {
            error('批次不能为空');
        }
        
        $total = $db->fetchRow("SELECT COUNT(*) as count FROM `{$table_prefix}_data` WHERE batch = '{$batch}'")['count'];
        $queried = $db->fetchRow("SELECT COUNT(*) as count FROM `{$table_prefix}_data` WHERE batch = '{$batch}' AND query_count > 0")['count'];
        $unqueried = $total - $queried;
        $query_rate = $total > 0 ? round(($queried / $total) * 100, 2) : 0;
        
        // 获取详细数据
        $details = $db->fetchAll("
            SELECT condition1, condition2, condition3, query_count, 
                   CASE WHEN query_count > 0 THEN add_time ELSE NULL END as last_query_time
            FROM `{$table_prefix}_data` 
            WHERE batch = '{$batch}' 
            ORDER BY query_count DESC, id DESC
            LIMIT 100
        ");
        
        success('获取统计成功', [
            'total' => $total,
            'queried' => $queried,
            'unqueried' => $unqueried,
            'query_rate' => $query_rate,
            'details' => $details
        ]);
    } else {
        error('参数错误');
    }
}

function downloadData() {
    global $db, $table_prefix;
    
    $batch = safeFilter($_GET['batch'] ?? '');
    if (empty($batch)) {
        error('批次不能为空');
    }
    
    // 获取数据
    $data = $db->fetchAll("
        SELECT condition1, condition2, condition3, detail_data, query_count, add_time
        FROM `{$table_prefix}_data` 
        WHERE batch = '{$batch}' 
        ORDER BY id
    ");
    
    if (empty($data)) {
        error('该批次没有数据');
    }
    
    // 设置下载头
    $filename = 'data_' . $batch . '_' . date('YmdHis') . '.txt';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // 输出数据
    echo "账号\t详细\t查询次数\t添加时间\n";
    
    foreach ($data as $row) {
        $detail = '';
        if ($row['detail_data']) {
            $detail_array = parseJsonData($row['detail_data']);
            $detail_parts = [];
            foreach ($detail_array as $key => $value) {
                $detail_parts[] = $key . ':' . $value;
            }
            $detail = implode('; ', $detail_parts);
        }
        
        echo $row['condition1'] . "\t" . 
             $detail . "\t" . 
             $row['query_count'] . "\t" . 
             $row['add_time'] . "\n";
    }
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'download_data', "下载批次: {$batch}");
    
    exit;
}

function clearData() {
    global $db, $table_prefix;
    
    $batch = safeFilter($_POST['batch'] ?? '');
    if (empty($batch)) {
        error('批次不能为空');
    }
    
    // 删除数据
    $count = $db->query("DELETE FROM `{$table_prefix}_data` WHERE batch = '{$batch}'");
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'clear_batch', "清空批次: {$batch}");
    
    success('清空成功', ['count' => $count]);
}
?>