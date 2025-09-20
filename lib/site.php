<?php
// 系统设置管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'get_config':
        getConfig();
        break;
    case 'save_config':
        saveConfig();
        break;
    case 'init_system':
        initSystem();
        break;
    default:
        showSettingsPage();
        break;
}

function showSettingsPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统设置 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>系统设置</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('list')">数据列表</button>
                <button class="tab" onclick="switchTab('liin')">数据导入</button>
                <button class="tab" onclick="switchTab('tong')">统计管理</button>
                <button class="tab active" onclick="switchTab('site')">系统设置</button>
                <button class="tab" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="settings-tabs">
                <button class="tab active" onclick="switchSettingsTab('basic')">基本设置</button>
                <button class="tab" onclick="switchSettingsTab('advanced')">更多设置</button>
                <button class="tab" onclick="switchSettingsTab('init')">初始化</button>
            </div>
            
            <!-- 基本设置 -->
            <div id="basicTab" class="settings-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">基本设置</h3>
                    </div>
                    
                    <form id="basicForm">
                        <div class="form-group">
                            <label>系统标题:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>查询条件一 (必填):</label>
                            <input type="text" name="condition1" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>查询条件二 (可选):</label>
                            <input type="text" name="condition2" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>查询条件三 (可选):</label>
                            <input type="text" name="condition3" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>查询规则:</label>
                            <select name="query_rule" class="form-control" onchange="updateMatchRule()">
                                <option value="T1">多输入框都对应输对</option>
                                <option value="T2">单输入框输入后查询设定条件对应的多列</option>
                                <option value="T3">下拉选设定的条件选那个查询哪列</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>匹配规则:</label>
                            <select name="match_rule" class="form-control">
                                <option value="d">等于</option>
                                <option value="b">包含</option>
                                <option value="s">关键词开头</option>
                                <option value="e">关键词结尾</option>
                                <option value="k">空格分开的多关键词</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>每页显示数量:</label>
                            <input type="number" name="page_size" class="form-control" min="1" max="100" value="20">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="captcha_enabled" value="1"> 启用验证码
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="user_access" value="1"> 用户访问开关
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">保存设置</button>
                    </form>
                </div>
            </div>
            
            <!-- 更多设置 -->
            <div id="advancedTab" class="settings-content" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">更多设置</h3>
                    </div>
                    
                    <form id="advancedForm">
                        <div class="form-group">
                            <label>有结果提示文字:</label>
                            <input type="text" name="has_result_text" class="form-control" placeholder="查询成功，找到相关数据">
                        </div>
                        
                        <div class="form-group">
                            <label>无结果提示文字:</label>
                            <input type="text" name="no_result_text" class="form-control" placeholder="未找到相关数据">
                        </div>
                        
                        <div class="form-group">
                            <label>输入页面提示文字:</label>
                            <textarea name="input_hint_text" class="form-control" rows="3" placeholder="请输入查询条件..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>底部文字:</label>
                            <input type="text" name="footer_text" class="form-control" placeholder="版权所有">
                        </div>
                        
                        <div class="form-group">
                            <label>底部链接:</label>
                            <input type="url" name="footer_link" class="form-control" placeholder="https://example.com">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">保存设置</button>
                    </form>
                </div>
            </div>
            
            <!-- 初始化 -->
            <div id="initTab" class="settings-content" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">系统初始化</h3>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>警告:</strong> 初始化将清空所有数据并重置系统设置，请谨慎操作！
                    </div>
                    
                    <form id="initForm">
                        <div class="form-group">
                            <label>查询条件一 (必填):</label>
                            <input type="text" name="condition1" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>查询条件二 (可选):</label>
                            <input type="text" name="condition2" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>查询条件三 (可选):</label>
                            <input type="text" name="condition3" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>查询规则:</label>
                            <select name="query_rule" class="form-control">
                                <option value="T1">多输入框都对应输对</option>
                                <option value="T2">单输入框输入后查询设定条件对应的多列</option>
                                <option value="T3">下拉选设定的条件选那个查询哪列</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>匹配规则:</label>
                            <select name="match_rule" class="form-control">
                                <option value="d">等于</option>
                                <option value="b">包含</option>
                                <option value="s">关键词开头</option>
                                <option value="e">关键词结尾</option>
                                <option value="k">空格分开的多关键词</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="backup_data" value="1" checked> 备份现有数据
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">确认初始化</button>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .settings-tabs {
                display: flex;
                border-bottom: 1px solid #e0e0e0;
                margin-bottom: 20px;
            }
            
            .settings-content {
                display: block;
            }
            
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
            
            .alert-warning {
                color: #856404;
                background-color: #fff3cd;
                border-color: #ffeaa7;
            }
        </style>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                loadConfig();
                
                // 绑定表单提交事件
                document.getElementById('basicForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveConfig('basic');
                });
                
                document.getElementById('advancedForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveConfig('advanced');
                });
                
                document.getElementById('initForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    initSystem();
                });
            });
            
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
            
            function switchSettingsTab(tab) {
                document.querySelectorAll('.settings-content').forEach(content => {
                    content.style.display = 'none';
                });
                document.querySelectorAll('.settings-tabs .tab').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                if (tab === 'basic') {
                    document.getElementById('basicTab').style.display = 'block';
                    document.querySelector('.settings-tabs .tab:first-child').classList.add('active');
                } else if (tab === 'advanced') {
                    document.getElementById('advancedTab').style.display = 'block';
                    document.querySelector('.settings-tabs .tab:nth-child(2)').classList.add('active');
                } else {
                    document.getElementById('initTab').style.display = 'block';
                    document.querySelector('.settings-tabs .tab:last-child').classList.add('active');
                }
            }
            
            function loadConfig() {
                ajaxRequest('user.php?do=site&act=get_config', {}, function(response) {
                    if (response.code === 1) {
                        const config = response.data;
                        
                        // 填充基本设置表单
                        Object.keys(config).forEach(key => {
                            const element = document.querySelector(`[name="${key}"]`);
                            if (element) {
                                if (element.type === 'checkbox') {
                                    element.checked = config[key] == 1;
                                } else {
                                    element.value = config[key] || '';
                                }
                            }
                        });
                    }
                });
            }
            
            function updateMatchRule() {
                const queryRule = document.querySelector('[name="query_rule"]').value;
                const matchRuleSelect = document.querySelector('[name="match_rule"]');
                
                // 根据查询规则更新匹配规则选项
                if (queryRule === 'T3') {
                    // 下拉选择模式，只支持等于
                    matchRuleSelect.innerHTML = '<option value="d">等于</option>';
                } else {
                    // 其他模式，支持所有匹配规则
                    matchRuleSelect.innerHTML = `
                        <option value="d">等于</option>
                        <option value="b">包含</option>
                        <option value="s">关键词开头</option>
                        <option value="e">关键词结尾</option>
                        <option value="k">空格分开的多关键词</option>
                    `;
                }
            }
            
            function saveConfig(type) {
                const form = document.getElementById(type + 'Form');
                const formData = new FormData(form);
                
                showLoading();
                ajaxRequest('user.php?do=site&act=save_config', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('保存成功', 'success');
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function initSystem() {
                confirmDialog('确定要初始化系统吗？这将清空所有数据！', 'performInit()');
            }
            
            function performInit() {
                const form = document.getElementById('initForm');
                const formData = new FormData(form);
                
                showLoading();
                ajaxRequest('user.php?do=site&act=init_system', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('初始化成功', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
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

function getConfig() {
    global $db, $table_prefix;
    
    $config = $db->fetchRow("SELECT * FROM `{$table_prefix}_site` WHERE domain = '{$_SERVER['HTTP_HOST']}'");
    if (!$config) {
        error('配置不存在');
    }
    
    success('获取配置成功', $config);
}

function saveConfig() {
    global $db, $table_prefix;
    
    $data = [];
    $fields = ['title', 'condition1', 'condition2', 'condition3', 'query_rule', 'match_rule', 
              'page_size', 'has_result_text', 'no_result_text', 'input_hint_text', 
              'footer_text', 'footer_link', 'captcha_enabled', 'user_access'];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $data[$field] = safeFilter($_POST[$field]);
        }
    }
    
    // 处理复选框
    $data['captcha_enabled'] = isset($_POST['captcha_enabled']) ? 1 : 0;
    $data['user_access'] = isset($_POST['user_access']) ? 1 : 0;
    
    $db->update("{$table_prefix}_site", $data, "domain = '{$_SERVER['HTTP_HOST']}'");
    
    // 更新缓存文件
    $config_file = "inc/json_{$_SERVER['HTTP_HOST']}.txt";
    if (file_exists($config_file)) {
        unlink($config_file);
    }
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'save_config', '保存系统设置');
    
    success('保存成功');
}

function initSystem() {
    global $db, $table_prefix;
    
    $condition1 = safeFilter($_POST['condition1'] ?? '');
    $condition2 = safeFilter($_POST['condition2'] ?? '');
    $condition3 = safeFilter($_POST['condition3'] ?? '');
    $query_rule = safeFilter($_POST['query_rule'] ?? 'T1');
    $match_rule = safeFilter($_POST['match_rule'] ?? 'd');
    $backup_data = isset($_POST['backup_data']);
    
    if (empty($condition1)) {
        error('查询条件一不能为空');
    }
    
    // 备份数据
    if ($backup_data) {
        $backup_file = 'backup_' . date('YmdHis') . '.sql';
        // 这里可以实现数据备份逻辑
    }
    
    // 清空数据表
    $db->query("TRUNCATE TABLE `{$table_prefix}_data`");
    
    // 更新系统配置
    $data = [
        'condition1' => $condition1,
        'condition2' => $condition2,
        'condition3' => $condition3,
        'query_rule' => $query_rule,
        'match_rule' => $match_rule,
        'title' => '查询系统',
        'page_size' => 20,
        'captcha_enabled' => 0,
        'user_access' => 1
    ];
    
    $db->update("{$table_prefix}_site", $data, "domain = '{$_SERVER['HTTP_HOST']}'");
    
    // 删除缓存文件
    $config_file = "inc/json_{$_SERVER['HTTP_HOST']}.txt";
    if (file_exists($config_file)) {
        unlink($config_file);
    }
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'init_system', '系统初始化');
    
    success('初始化成功');
}
?>