<?php
// 管理员 - 用户管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_users':
        getUsers();
        break;
    case 'reset_password':
        resetPassword();
        break;
    case 'toggle_status':
        toggleStatus();
        break;
    default:
        showUserPage();
        break;
}

function showUserPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>用户管理 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>用户管理</h1>
            <button class="btn btn-secondary" onclick="location.href='admin.php?de=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('user')">用户列表</button>
                <button class="tab" onclick="switchTab('list')">查询列表</button>
                <button class="tab" onclick="switchTab('logs')">操作日志</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户管理</h3>
                </div>
                
                <div class="search-box">
                    <select id="searchField" class="form-control" style="width: 150px;">
                        <option value="phone">手机号</option>
                        <option value="status">状态</option>
                    </select>
                    <input type="text" id="searchKeyword" class="form-control" placeholder="请输入搜索关键词">
                    <button class="btn btn-primary" onclick="searchUsers()">搜索</button>
                    <button class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
                
                <div id="userTable"></div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let currentPage = 1;
            let searchField = '';
            let searchKeyword = '';
            
            document.addEventListener('DOMContentLoaded', function() {
                loadUsers();
            });
            
            function switchTab(tab) {
                window.location.href = 'admin.php?de=' + tab;
            }
            
            function loadUsers() {
                showLoading();
                
                const params = {
                    page: currentPage,
                    field: searchField,
                    keyword: searchKeyword
                };
                
                ajaxRequest('admin.php?de=user&act=get_users', params, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displayUserTable(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function displayUserTable(data) {
                let html = '<div class="table-container">';
                html += '<table class="table">';
                html += '<thead><tr>';
                html += '<th>ID</th>';
                html += '<th>手机号</th>';
                html += '<th>微信ID</th>';
                html += '<th>状态</th>';
                html += '<th>创建时间</th>';
                html += '<th>操作</th>';
                html += '</tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(user => {
                        html += '<tr>';
                        html += '<td>' + user.id + '</td>';
                        html += '<td>' + user.phone + '</td>';
                        html += '<td>' + (user.wechat_id || '') + '</td>';
                        html += '<td>';
                        if (user.status === 'normal') {
                            html += '<span class="status-success">正常</span>';
                        } else {
                            html += '<span class="status-error">禁用</span>';
                        }
                        html += '</td>';
                        html += '<td>' + user.create_time + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-warning" onclick="resetPassword(' + user.id + ')">重置密码</button> ';
                        if (user.status === 'normal') {
                            html += '<button class="btn btn-danger" onclick="toggleStatus(' + user.id + ', \'disabled\')">禁用</button>';
                        } else {
                            html += '<button class="btn btn-success" onclick="toggleStatus(' + user.id + ', \'normal\')">启用</button>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="6" style="text-align: center;">暂无数据</td></tr>';
                }
                
                html += '</tbody></table>';
                html += '</div>';
                
                // 添加分页
                if (data.pages > 1) {
                    html += generatePaginationHtml(data.current_page, data.pages);
                }
                
                document.getElementById('userTable').innerHTML = html;
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
                loadUsers();
            }
            
            function searchUsers() {
                searchField = document.getElementById('searchField').value;
                searchKeyword = document.getElementById('searchKeyword').value;
                currentPage = 1;
                loadUsers();
            }
            
            function resetSearch() {
                searchField = '';
                searchKeyword = '';
                document.getElementById('searchField').value = 'phone';
                document.getElementById('searchKeyword').value = '';
                currentPage = 1;
                loadUsers();
            }
            
            function resetPassword(userId) {
                confirmDialog('确定要重置该用户的密码吗？新密码将设置为 123456', 'performResetPassword(' + userId + ')');
            }
            
            function performResetPassword(userId) {
                showLoading();
                ajaxRequest('admin.php?de=user&act=reset_password', {user_id: userId}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('密码重置成功', 'success');
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function toggleStatus(userId, status) {
                const action = status === 'normal' ? '启用' : '禁用';
                confirmDialog('确定要' + action + '该用户吗？', 'performToggleStatus(' + userId + ', \'' + status + '\')');
            }
            
            function performToggleStatus(userId, status) {
                showLoading();
                ajaxRequest('admin.php?de=user&act=toggle_status', {user_id: userId, status: status}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('状态更新成功', 'success');
                        loadUsers();
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

function getUsers() {
    global $db, $table_prefix;
    
    $page = intval($_GET['page'] ?? 1);
    $field = safeFilter($_GET['field'] ?? '');
    $keyword = safeFilter($_GET['keyword'] ?? '');
    
    $where = '1=1';
    if (!empty($field) && !empty($keyword)) {
        if ($field === 'status') {
            $where .= " AND status = '{$keyword}'";
        } else {
            $where .= " AND {$field} LIKE '%{$keyword}%'";
        }
    }
    
    $page_data = $db->getPageData("{$table_prefix}_user", $where, "id DESC", $page, 20);
    
    success('获取用户成功', $page_data);
}

function resetPassword() {
    global $db, $table_prefix;
    
    $user_id = intval($_POST['user_id'] ?? 0);
    if ($user_id <= 0) {
        error('参数错误');
    }
    
    // 重置密码为 123456
    $new_password = password_hash('123456', PASSWORD_DEFAULT);
    $db->update("{$table_prefix}_user", ['password' => $new_password], "id = {$user_id}");
    
    // 记录日志
    writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'reset_password', "重置用户ID: {$user_id} 的密码");
    
    success('密码重置成功');
}

function toggleStatus() {
    global $db, $table_prefix;
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $status = safeFilter($_POST['status'] ?? '');
    
    if ($user_id <= 0 || !in_array($status, ['normal', 'disabled'])) {
        error('参数错误');
    }
    
    $db->update("{$table_prefix}_user", ['status' => $status], "id = {$user_id}");
    
    // 记录日志
    $action = $status === 'normal' ? '启用' : '禁用';
    writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'toggle_user_status', "{$action}用户ID: {$user_id}");
    
    success('状态更新成功');
}
?>