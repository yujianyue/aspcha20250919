<?php
// 数据列表管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_data':
        getDataList();
        break;
    case 'edit':
        editData();
        break;
    case 'delete':
        deleteData();
        break;
    case 'batch_delete':
        batchDeleteData();
        break;
    default:
        showDataList();
        break;
}

function showDataList() {
    global $config, $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>数据列表 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>数据列表</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('list')">数据列表</button>
                <button class="tab" onclick="switchTab('liin')">数据导入</button>
                <button class="tab" onclick="switchTab('tong')">统计管理</button>
                <button class="tab" onclick="switchTab('site')">系统设置</button>
                <button class="tab" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">数据管理</h3>
                </div>
                
                <div class="search-box">
                    <select id="searchField" class="form-control" style="width: 150px;">
                        <option value="condition1">条件一</option>
                        <option value="condition2">条件二</option>
                        <option value="condition3">条件三</option>
                        <option value="batch">批次</option>
                    </select>
                    <input type="text" id="searchKeyword" class="form-control" placeholder="请输入搜索关键词">
                    <button class="btn btn-primary" onclick="searchData()">搜索</button>
                    <button class="btn btn-secondary" onclick="resetSearch()">重置</button>
                    <button class="btn btn-danger" onclick="batchDelete()">批量删除</button>
                </div>
                
                <div id="dataTable"></div>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            let currentPage = 1;
            let searchField = '';
            let searchKeyword = '';
            
            document.addEventListener('DOMContentLoaded', function() {
                loadDataList();
            });
            
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
            
            function loadDataList() {
                showLoading();
                
                const params = {
                    page: currentPage,
                    field: searchField,
                    keyword: searchKeyword
                };
                
                ajaxRequest('user.php?do=list&act=get_data', params, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        displayDataTable(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function displayDataTable(data) {
                let html = '<div class="table-container">';
                html += '<table class="table">';
                html += '<thead><tr>';
                html += '<th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>';
                html += '<th>ID</th>';
                html += '<th>条件一</th>';
                html += '<th>条件二</th>';
                html += '<th>条件三</th>';
                html += '<th>批次</th>';
                html += '<th>查询次数</th>';
                html += '<th>添加时间</th>';
                html += '<th>操作</th>';
                html += '</tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        html += '<tr>';
                        html += '<td><input type="checkbox" class="row-checkbox" value="' + item.id + '"></td>';
                        html += '<td>' + item.id + '</td>';
                        html += '<td>' + (item.condition1 || '') + '</td>';
                        html += '<td>' + (item.condition2 || '') + '</td>';
                        html += '<td>' + (item.condition3 || '') + '</td>';
                        html += '<td>' + (item.batch || '') + '</td>';
                        html += '<td>' + item.query_count + '</td>';
                        html += '<td>' + item.add_time + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-primary" onclick="editData(' + item.id + ')">修改</button> ';
                        html += '<button class="btn btn-danger" onclick="deleteData(' + item.id + ')">删除</button>';
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
                
                document.getElementById('dataTable').innerHTML = html;
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
                loadDataList();
            }
            
            function searchData() {
                searchField = document.getElementById('searchField').value;
                searchKeyword = document.getElementById('searchKeyword').value;
                currentPage = 1;
                loadDataList();
            }
            
            function resetSearch() {
                searchField = '';
                searchKeyword = '';
                document.getElementById('searchField').value = 'condition1';
                document.getElementById('searchKeyword').value = '';
                currentPage = 1;
                loadDataList();
            }
            
            function toggleSelectAll() {
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            }
            
            function editData(id) {
                showLoading();
                ajaxRequest('user.php?do=list&act=edit', {id: id}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showEditModal(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function showEditModal(data) {
                let content = '<form id="editForm">';
                content += '<input type="hidden" name="id" value="' + data.id + '">';
                content += '<div class="form-group">';
                content += '<label>条件一:</label>';
                content += '<input type="text" name="condition1" class="form-control" value="' + (data.condition1 || '') + '" required>';
                content += '</div>';
                content += '<div class="form-group">';
                content += '<label>条件二:</label>';
                content += '<input type="text" name="condition2" class="form-control" value="' + (data.condition2 || '') + '">';
                content += '</div>';
                content += '<div class="form-group">';
                content += '<label>条件三:</label>';
                content += '<input type="text" name="condition3" class="form-control" value="' + (data.condition3 || '') + '">';
                content += '</div>';
                content += '<div class="form-group">';
                content += '<label>批次:</label>';
                content += '<input type="text" name="batch" class="form-control" value="' + (data.batch || '') + '">';
                content += '</div>';
                content += '<div class="form-group">';
                content += '<label>详细数据 (每行一个字段,格式: 字段名,内容):</label>';
                content += '<textarea name="detail_data" class="form-control" rows="10" placeholder="字段1,内容1&#10;字段2,内容2">' + (data.detail_data || '') + '</textarea>';
                content += '</div>';
                content += '</form>';
                
                showModal('编辑数据', content, [
                    {text: '取消', type: 'secondary', action: 'closeModal()'},
                    {text: '保存', type: 'primary', action: 'saveEdit()'}
                ]);
            }
            
            function saveEdit() {
                const form = document.getElementById('editForm');
                const formData = new FormData(form);
                
                showLoading();
                ajaxRequest('user.php?do=list&act=edit', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('保存成功', 'success');
                        closeModal();
                        loadDataList();
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function deleteData(id) {
                confirmDialog('确定要删除这条数据吗？删除后不可恢复！', 'performDelete(' + id + ')');
            }
            
            function performDelete(id) {
                showLoading();
                ajaxRequest('user.php?do=list&act=delete', {id: id}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('删除成功', 'success');
                        loadDataList();
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function batchDelete() {
                const checkboxes = document.querySelectorAll('.row-checkbox:checked');
                if (checkboxes.length === 0) {
                    showToast('请选择要删除的数据', 'warning');
                    return;
                }
                
                const ids = Array.from(checkboxes).map(cb => cb.value);
                confirmDialog('确定要删除选中的 ' + ids.length + ' 条数据吗？删除后不可恢复！', 'performBatchDelete()');
            }
            
            function performBatchDelete() {
                const checkboxes = document.querySelectorAll('.row-checkbox:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);
                
                showLoading();
                ajaxRequest('user.php?do=list&act=batch_delete', {ids: ids.join(',')}, function(response) {
                    hideLoading();
                    if (response.code === 1) {
                        showToast('批量删除成功', 'success');
                        loadDataList();
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

function getDataList() {
    global $db, $table_prefix;
    
    $page = intval($_GET['page'] ?? 1);
    $field = safeFilter($_GET['field'] ?? '');
    $keyword = safeFilter($_GET['keyword'] ?? '');
    
    $where = '1=1';
    if (!empty($field) && !empty($keyword)) {
        $where .= " AND {$field} LIKE '%{$keyword}%'";
    }
    
    $page_data = $db->getPageData("{$table_prefix}_data", $where, "id DESC", $page, 20);
    
    success('获取数据成功', $page_data);
}

function editData() {
    global $db, $table_prefix;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $condition1 = safeFilter($_POST['condition1'] ?? '');
        $condition2 = safeFilter($_POST['condition2'] ?? '');
        $condition3 = safeFilter($_POST['condition3'] ?? '');
        $batch = safeFilter($_POST['batch'] ?? '');
        $detail_data = safeFilter($_POST['detail_data'] ?? '');
        
        if ($id <= 0 || empty($condition1)) {
            error('参数错误');
        }
        
        $data = [
            'condition1' => $condition1,
            'condition2' => $condition2,
            'condition3' => $condition3,
            'batch' => $batch,
            'detail_data' => $detail_data
        ];
        
        $db->update("{$table_prefix}_data", $data, "id = {$id}");
        
        // 记录日志
        writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'edit_data', "编辑数据ID: {$id}");
        
        success('保存成功');
    } else {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            error('参数错误');
        }
        
        $data = $db->fetchRow("SELECT * FROM `{$table_prefix}_data` WHERE id = {$id}");
        if (!$data) {
            error('数据不存在');
        }
        
        success('获取数据成功', $data);
    }
}

function deleteData() {
    global $db, $table_prefix;
    
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        error('参数错误');
    }
    
    $db->delete("{$table_prefix}_data", "id = {$id}");
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'delete_data', "删除数据ID: {$id}");
    
    success('删除成功');
}

function batchDeleteData() {
    global $db, $table_prefix;
    
    $ids = safeFilter($_POST['ids'] ?? '');
    if (empty($ids)) {
        error('参数错误');
    }
    
    // 验证ID格式
    if (!preg_match('/^\d+(,\d+)*$/', $ids)) {
        error('参数格式错误');
    }
    
    $db->delete("{$table_prefix}_data", "id IN ({$ids})");
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'batch_delete_data', "批量删除数据ID: {$ids}");
    
    success('批量删除成功');
}
?>