<?php
// 使用帮助管理

function showHelpPage() {
    global $version;
    
    // 读取帮助文档
    $help_file = 'readme.txt';
    $help_content = '';
    
    if (file_exists($help_file)) {
        $help_content = file_get_contents($help_file);
    } else {
        $help_content = getDefaultHelpContent();
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>使用帮助 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>使用帮助</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('list')">数据列表</button>
                <button class="tab" onclick="switchTab('liin')">数据导入</button>
                <button class="tab" onclick="switchTab('tong')">统计管理</button>
                <button class="tab" onclick="switchTab('site')">系统设置</button>
                <button class="tab" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab active" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">使用帮助</h3>
                </div>
                
                <div class="help-content">
                    <pre><?php echo htmlspecialchars($help_content); ?></pre>
                </div>
            </div>
        </div>
        
        <style>
            .help-content {
                padding: 20px;
                background: #f8f9fa;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 14px;
                line-height: 1.6;
                white-space: pre-wrap;
                word-wrap: break-word;
                max-height: 600px;
                overflow-y: auto;
            }
        </style>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
        </script>
    </body>
    </html>
    <?php
}

function getDefaultHelpContent() {
    return "查询系统使用帮助
================================

系统简介
--------
本系统是一个基于PHP+MySQL的多租户多查询规则合一通用查询系统。
支持多种查询规则和匹配模式，适用于各种数据查询场景。

环境要求
--------
- PHP 7.0 或更高版本
- MySQL 5.6 或更高版本
- Web服务器 (Apache/Nginx)
- 支持mysqli扩展

安装步骤
--------
1. 将系统文件上传到Web服务器目录
2. 访问 install.php 进行安装
3. 配置数据库连接信息
4. 创建管理员账户
5. 完成系统初始化

默认账户
--------
管理员账户: admin
默认密码: admin123
(首次登录后请立即修改密码)

功能模块
--------

1. 前端查询 (index.php)
   - 支持多种查询条件
   - 灵活的匹配规则
   - 分页显示结果
   - 详情查看功能
   - 打印功能

2. 用户管理 (user.php)
   - 数据列表管理
   - 批量数据导入
   - 统计信息查看
   - 系统设置配置
   - 数据备份恢复
   - 密码修改

3. 管理员后台 (admin.php)
   - 用户账户管理
   - 查询系统管理
   - 操作日志查看
   - 系统监控

查询规则说明
------------
T1: 多输入框都对应输对
    - 需要填写所有查询条件
    - 所有条件都必须匹配

T2: 单输入框输入后查询设定条件对应的多列
    - 只需填写一个查询条件
    - 系统会在多个字段中搜索

T3: 下拉选设定的条件选那个查询哪列
    - 通过下拉选择查询条件
    - 根据选择查询对应字段

匹配规则说明
------------
d: 等于 - 完全匹配
b: 包含 - 包含关键词
s: 关键词开头 - 以关键词开始
e: 关键词结尾 - 以关键词结束
k: 多关键词 - 空格分隔的多个关键词

数据导入格式
------------
支持 .txt 和 .csv 文件格式：
- 第一行为字段名
- 数据行用逗号或制表符分隔
- 文件大小限制 4MB
- 支持批量导入

安全说明
--------
- 所有输入都经过安全过滤
- 支持验证码防护
- 操作日志记录
- 权限分级管理

注意事项
--------
1. 定期备份数据
2. 及时更新系统
3. 设置强密码
4. 监控系统日志
5. 合理设置权限

技术支持
--------
如有问题请联系系统管理员。

版本信息
--------
系统版本: v1.0.0
更新日期: " . date('Y-m-d') . "
";
}
?>