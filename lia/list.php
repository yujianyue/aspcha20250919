<?php
// 管理员 - 查询系统管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_sites':
        getSites();
        break;
    case 'toggle_access':
        toggleAccess();
        break;
    case 'update_user_type':
        updateUserType();
        break;
    case 'get_site_detail':
        getSiteDetail();
        break;
    default:
        showSitePage();
        break;
}

function showSitePage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>查询系统管理 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>查询系统管理</h1>
            <button class="btn btn-secondary" onclick="location.href='admin.php?de=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('user')">用户列表</button>
                <button class="tab active" onclick="switchTab('list')">查询列表</button>
                <button class="tab" onclick="switchTab('logs')">操作日志</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">查询系统列表</h3>
                </div>
                
                <div class="search-box">
                    <select id="searchField" class="form-control" style="width: 150px;">
                        <option value="domain">域名</option>
                        <option value="admin">管理员</option>
                        <option value="user_type">用户类型</option>
                        <option value="title">标题</option>
                    </select>
                    <input type="text" id="searchKeyword" class="form-control" placeholder="请输入搜索关键词">
                    <button class="btn btn-primary" onclick="searchSites()">搜索</button>
                    <button class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
                
                <div id="siteTable"></div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let currentPage = 1;
            let searchField = '';
            let searchKeyword = '';
            
            document.addEventListener('DOMContentLoaded', function() {
                loadSites();
            });
            
            function switchTab(tab) {
                window.location.href = 'admin.php?de=' + tab;
            }
            
            function loadSites() {
                showLoading();
                
                const params = {
                    page: currentPage,
                    field: searchField,
                    keyword: searchKeyword
                };
                
                ajaxRequest('admin.php?de=list&act=get_sites', params, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displaySiteTable(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function displaySiteTable(data) {
                let html = '<div class="table-container">';
                html += '<table class="table">';
                html += '<thead><tr>';
                html += '<th>ID</th>';
                html += '<th>域名</th>';
                html += '<th>管理员</th>';
                html += '<th>用户类型</th>';
                html += '<th>标题</th>';
                html += '<th>管理员访问</th>';
                html += '<th>用户访问</th>';
                html += '<th>数据量</th>';
                html += '<th>操作</th>';
                html += '</tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(site => {
                        html += '<tr>';
                        html += '<td>' + site.id + '</td>';
                        html += '<td>' + site.domain + '</td>';
                        html += '<td>' + site.admin + '</td>';
                        html += '<td>' + site.user_type + '</td>';
                        html += '<td>' + (site.title || '') + '</td>';
                        html += '<td>';
                        if (site.admin_access == 1) {
                            html += '<span class="status-success">开启</span>';
                        } else {
                            html += '<span class="status-error">关闭</span>';
                        }
                        html += '</td>';
                        html += '<td>';
                        if (site.user_access == 1) {
                            html += '<span class="status-success">开启</span>';
                        } else {
                            html += '<span class="status-error">关闭</span>';
                        }
                        html += '</td>';
                        html += '<td>' + site.data_count + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-info" onclick="showDetail(' + site.id + ')">详情</button> ';
                        if (site.admin_access == 1) {
                            html += '<button class="btn btn-warning" onclick="toggleAccess(' + site.id + ', \'admin_access\', 0)">关闭管理</button>';
                        } else {
                            html += '<button class="btn btn-success" onclick="toggleAccess(' + site.id + ', \'admin_access\', 1)">开启管理</button>';
                        }
                        html += '<br><br>';
                        if (site.user_access == 1) {
                            html += '<button class="btn btn-warning" onclick="toggleAccess(' + site.id + ', \'user_access\', 0)">关闭用户</button>';
                        } else {
                            html += '<button class="btn btn-success" onclick="toggleAccess(' + site.id + ', \'user_access\', 1)">开启用户</button>';
                        }
                        html += '<br><br>';
                        html += '<select class="form-control" style="width: 120px; display: inline-block;" onchange="updateUserType(' + site.id + ', this.value)">';
                        for (let i = 0; i <= 9; i++) {
                            const selected = site.user_type === 'vip' + i ? 'selected' : '';
                            html += '<option value="vip' + i + '" ' + selected + '>VIP' + i + '</option>';
                        }
                        html += '</select>';
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="9" style="text-align: center;">暂无数据</td></tr>';
                }
                
                html += '</tbody></table>';
                html += '</div>';
                
                // 添加分页
                if (data.pages > 1) {
                    html += generatePaginationHtml(data.current_page, data.pages);
                }
                
                document.getElementById('siteTable').innerHTML = html;
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
                loadSites();
            }
            
            function searchSites() {
                searchField = document.getElementById('searchField').value;
                searchKeyword = document.getElementById('searchKeyword').value;
                currentPage = 1;
                loadSites();
            }
            
            function resetSearch() {
                searchField = '';
                searchKeyword = '';
                document.getElementById('searchField').value = 'domain';
                document.getElementById('searchKeyword').value = '';
                currentPage = 1;
                loadSites();
            }
            
            function showDetail(siteId) {
                showLoading();
                ajaxRequest('admin.php?de=list&act=get_site_detail', {site_id: siteId}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showDetailModal(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function showDetailModal(data) {
                let content = '<div class="detail-content">';
                content += '<h4>系统详情</h4>';
                content += '<table class="table">';
                content += '<tr><td><strong>域名</strong></td><td>' + data.domain + '</td></tr>';
                content += '<tr><td><strong>管理员</strong></td><td>' + data.admin + '</td></tr>';
                content += '<tr><td><strong>用户类型</strong></td><td>' + data.user_type + '</td></tr>';
                content += '<tr><td><strong>标题</strong></td><td>' + (data.title || '') + '</td></tr>';
                content += '<tr><td><strong>查询条件一</strong></td><td>' + (data.condition1 || '') + '</td></tr>';
                content += '<tr><td><strong>查询条件二</strong></td><td>' + (data.condition2 || '') + '</td></tr>';
                content += '<tr><td><strong>查询条件三</strong></td><td>' + (data.condition3 || '') + '</td></tr>';
                content += '<tr><td><strong>查询规则</strong></td><td>' + data.query_rule + '</td></tr>';
                content += '<tr><td><strong>匹配规则</strong></td><td>' + data.match_rule + '</td></tr>';
                content += '<tr><td><strong>每页显示数量</strong></td><td>' + data.page_size + '</td></tr>';
                content += '<tr><td><strong>验证码开关</strong></td><td>' + (data.captcha_enabled == 1 ? '开启' : '关闭') + '</td></tr>';
                content += '<tr><td><strong>数据总量</strong></td><td>' + data.data_count + ' 条</td></tr>';
                content += '</table>';
                content += '</div>';
                
                showModal('系统详情', content, [
                    {text: '关闭', type: 'secondary', action: 'closeModal()'}
                ]);
            }
            
            function toggleAccess(siteId, field, value) {
                const action = value == 1 ? '开启' : '关闭';
                const fieldName = field === 'admin_access' ? '管理员访问' : '用户访问';
                confirmDialog('确定要' + action + fieldName + '吗？', 'performToggleAccess(' + siteId + ', \'' + field + '\', ' + value + ')');
            }
            
            function performToggleAccess(siteId, field, value) {
                showLoading();
                ajaxRequest('admin.php?de=list&act=toggle_access', {site_id: siteId, field: field, value: value}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('更新成功', 'success');
                        loadSites();
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function updateUserType(siteId, userType) {
                showLoading();
                ajaxRequest('admin.php?de=list&act=update_user_type', {site_id: siteId, user_type: userType}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('用户类型更新成功', 'success');
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

function getSites() {
    global $db, $table_prefix;
    
    $page = intval($_GET['page'] ?? 1);
    $field = safeFilter($_GET['field'] ?? '');
    $keyword = safeFilter($_GET['keyword'] ?? '');
    
    $where = '1=1';
    if (!empty($field) && !empty($keyword)) {
        $where .= " AND {$field} LIKE '%{$keyword}%'";
    }
    
    // 获取站点数据并包含数据量统计
    $sql = "
        SELECT s.*, 
               (SELECT COUNT(*) FROM `{$table_prefix}_data` WHERE 1=1) as data_count
        FROM `{$table_prefix}_site` s 
        WHERE {$where} 
        ORDER BY s.id DESC
    ";
    
    $page_data = $db->getPageData("{$table_prefix}_site", $where, "id DESC", $page, 20);
    
    // 为每个站点添加数据量统计
    foreach ($page_data['data'] as &$site) {
        $data_count = $db->fetchRow("SELECT COUNT(*) as count FROM `{$table_prefix}_data`")['count'];
        $site['data_count'] = $data_count;
    }
    
    success('获取站点成功', $page_data);
}

function toggleAccess() {
    global $db, $table_prefix;
    
    $site_id = intval($_POST['site_id'] ?? 0);
    $field = safeFilter($_POST['field'] ?? '');
    $value = intval($_POST['value'] ?? 0);
    
    if ($site_id <= 0 || !in_array($field, ['admin_access', 'user_access'])) {
        error('参数错误');
    }
    
    $db->update("{$table_prefix}_site", [$field => $value], "id = {$site_id}");
    
    // 记录日志
    $action = $value == 1 ? '开启' : '关闭';
    $fieldName = $field === 'admin_access' ? '管理员访问' : '用户访问';
    writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'toggle_access', "{$action}站点ID: {$site_id} 的{$fieldName}");
    
    success('更新成功');
}

function updateUserType() {
    global $db, $table_prefix;
    
    $site_id = intval($_POST['site_id'] ?? 0);
    $user_type = safeFilter($_POST['user_type'] ?? '');
    
    if ($site_id <= 0 || !preg_match('/^vip[0-9]$/', $user_type)) {
        error('参数错误');
    }
    
    $db->update("{$table_prefix}_site", ['user_type' => $user_type], "id = {$site_id}");
    
    // 记录日志
    writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'update_user_type', "更新站点ID: {$site_id} 的用户类型为: {$user_type}");
    
    success('用户类型更新成功');
}

function getSiteDetail() {
    global $db, $table_prefix;
    
    $site_id = intval($_GET['site_id'] ?? 0);
    if ($site_id <= 0) {
        error('参数错误');
    }
    
    $site = $db->fetchRow("SELECT * FROM `{$table_prefix}_site` WHERE id = {$site_id}");
    if (!$site) {
        error('站点不存在');
    }
    
    // 获取数据量统计
    $data_count = $db->fetchRow("SELECT COUNT(*) as count FROM `{$table_prefix}_data`")['count'];
    $site['data_count'] = $data_count;
    
    success('获取详情成功', $site);
}
?>