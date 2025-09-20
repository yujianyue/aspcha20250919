<?php
// 管理员 - 操作日志管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_logs':
        getLogs();
        break;
    default:
        showLogsPage();
        break;
}

function showLogsPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>操作日志 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>操作日志</h1>
            <button class="btn btn-secondary" onclick="location.href='admin.php?de=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('user')">用户列表</button>
                <button class="tab" onclick="switchTab('list')">查询列表</button>
                <button class="tab active" onclick="switchTab('logs')">操作日志</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">操作日志</h3>
                </div>
                
                <div class="search-box">
                    <select id="searchField" class="form-control" style="width: 150px;">
                        <option value="user">用户</option>
                        <option value="domain">域名</option>
                        <option value="act">操作</option>
                        <option value="ip">IP地址</option>
                    </select>
                    <input type="text" id="searchKeyword" class="form-control" placeholder="请输入搜索关键词">
                    <button class="btn btn-primary" onclick="searchLogs()">搜索</button>
                    <button class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
                
                <div id="logTable"></div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let currentPage = 1;
            let searchField = '';
            let searchKeyword = '';
            
            document.addEventListener('DOMContentLoaded', function() {
                loadLogs();
            });
            
            function switchTab(tab) {
                window.location.href = 'admin.php?de=' + tab;
            }
            
            function loadLogs() {
                showLoading();
                
                const params = {
                    page: currentPage,
                    field: searchField,
                    keyword: searchKeyword
                };
                
                ajaxRequest('admin.php?de=logs&act=get_logs', params, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displayLogTable(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function displayLogTable(data) {
                let html = '<div class="table-container">';
                html += '<table class="table">';
                html += '<thead><tr>';
                html += '<th>ID</th>';
                html += '<th>时间</th>';
                html += '<th>用户</th>';
                html += '<th>域名</th>';
                html += '<th>IP地址</th>';
                html += '<th>操作</th>';
                html += '<th>备注</th>';
                html += '</tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(log => {
                        html += '<tr>';
                        html += '<td>' + log.id + '</td>';
                        html += '<td>' + log.log_time + '</td>';
                        html += '<td>' + log.user + '</td>';
                        html += '<td>' + log.domain + '</td>';
                        html += '<td>' + log.ip + '</td>';
                        html += '<td>';
                        switch(log.act) {
                            case 'login':
                                html += '<span class="status-success">登录</span>';
                                break;
                            case 'logout':
                                html += '<span class="status-warning">退出</span>';
                                break;
                            case 'query':
                                html += '<span class="status-info">查询</span>';
                                break;
                            case 'edit_data':
                                html += '<span class="status-info">编辑数据</span>';
                                break;
                            case 'delete_data':
                                html += '<span class="status-error">删除数据</span>';
                                break;
                            case 'import_data':
                                html += '<span class="status-info">导入数据</span>';
                                break;
                            case 'backup_data':
                                html += '<span class="status-info">备份数据</span>';
                                break;
                            case 'save_config':
                                html += '<span class="status-info">保存配置</span>';
                                break;
                            case 'change_password':
                                html += '<span class="status-warning">修改密码</span>';
                                break;
                            case 'reset_password':
                                html += '<span class="status-warning">重置密码</span>';
                                break;
                            case 'toggle_user_status':
                                html += '<span class="status-warning">用户状态</span>';
                                break;
                            case 'toggle_access':
                                html += '<span class="status-warning">访问控制</span>';
                                break;
                            case 'update_user_type':
                                html += '<span class="status-info">用户类型</span>';
                                break;
                            case 'init_system':
                                html += '<span class="status-error">系统初始化</span>';
                                break;
                            case 'clear_batch':
                                html += '<span class="status-error">清空批次</span>';
                                break;
                            default:
                                html += '<span class="status-info">' + log.act + '</span>';
                        }
                        html += '</td>';
                        html += '<td>' + (log.remark || '') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="7" style="text-align: center;">暂无数据</td></tr>';
                }
                
                html += '</tbody></table>';
                html += '</div>';
                
                // 添加分页
                if (data.pages > 1) {
                    html += generatePaginationHtml(data.current_page, data.pages);
                }
                
                document.getElementById('logTable').innerHTML = html;
            }
            
            function generatePaginationHtml(current, total) {
                let html = '<div class="pagination">';
                
                if (current > 1) {
                    html += '<a href="#" onclick="goToPage(1)" class="page-btn">首页</a>';
                } else {
                    html += '<span class="page-btn disabled">首页</span>';
                }
                
                if (current > 1) {
                    html += '<a href="#" onclick="goToPage(' + (current - 1) + ')" class="page-btn">上一页</a>';
                } else {
                    html += '<span class="page-btn disabled">上一页</span>';
                }
                
                html += '<select class="page-select" onchange="goToPage(this.value)">';
                for (let i = 1; i <= total; i++) {
                    const selected = i == current ? 'selected' : '';
                    html += '<option value="' + i + '" ' + selected + '>第' + i + '页</option>';
                }
                html += '</select>';
                
                if (current < total) {
                    html += '<a href="#" onclick="goToPage(' + (current + 1) + ')" class="page-btn">下一页</a>';
                } else {
                    html += '<span class="page-btn disabled">下一页</span>';
                }
                
                if (current < total) {
                    html += '<a href="#" onclick="goToPage(' + total + ')" class="page-btn">末页</a>';
                } else {
                    html += '<span class="page-btn disabled">末页</span>';
                }
                
                html += '</div>';
                return html;
            }
            
            function goToPage(page) {
                currentPage = parseInt(page);
                loadLogs();
            }
            
            function searchLogs() {
                searchField = document.getElementById('searchField').value;
                searchKeyword = document.getElementById('searchKeyword').value;
                currentPage = 1;
                loadLogs();
            }
            
            function resetSearch() {
                searchField = '';
                searchKeyword = '';
                document.getElementById('searchField').value = 'user';
                document.getElementById('searchKeyword').value = '';
                currentPage = 1;
                loadLogs();
            }
        </script>
    </body>
    </html>
    <?php
}

function getLogs() {
    global $db, $table_prefix;
    
    $page = intval($_GET['page'] ?? 1);
    $field = safeFilter($_GET['field'] ?? '');
    $keyword = safeFilter($_GET['keyword'] ?? '');
    
    $where = '1=1';
    if (!empty($field) && !empty($keyword)) {
        $where .= " AND {$field} LIKE '%{$keyword}%'";
    }
    
    $page_data = $db->getPageData("{$table_prefix}_logs", $where, "id DESC", $page, 20);
    
    success('获取日志成功', $page_data);
}
?>