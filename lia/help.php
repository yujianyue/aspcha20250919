<?php
// 管理员 - 使用帮助

function showHelpPage() {
    global $version;
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
            <button class="btn btn-secondary" onclick="location.href='admin.php?de=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('user')">用户列表</button>
                <button class="tab" onclick="switchTab('list')">查询列表</button>
                <button class="tab" onclick="switchTab('logs')">操作日志</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab active" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">管理员使用帮助</h3>
                </div>
                
                <div class="help-content">
                    <h4>管理员功能说明</h4>
                    
                    <h5>1. 用户管理</h5>
                    <ul>
                        <li>查看所有用户列表</li>
                        <li>搜索用户（按手机号、状态）</li>
                        <li>重置用户密码（新密码：123456）</li>
                        <li>启用/禁用用户账户</li>
                    </ul>
                    
                    <h5>2. 查询系统管理</h5>
                    <ul>
                        <li>查看所有查询系统列表</li>
                        <li>搜索系统（按域名、管理员、用户类型、标题）</li>
                        <li>查看系统详情（包括配置信息和数据量）</li>
                        <li>开启/关闭管理员访问权限</li>
                        <li>开启/关闭用户访问权限</li>
                        <li>修改用户类型（VIP0-VIP9）</li>
                    </ul>
                    
                    <h5>3. 操作日志</h5>
                    <ul>
                        <li>查看所有操作日志</li>
                        <li>搜索日志（按用户、域名、操作、IP）</li>
                        <li>监控系统使用情况</li>
                        <li>追踪用户操作行为</li>
                    </ul>
                    
                    <h5>4. 密码管理</h5>
                    <ul>
                        <li>修改管理员密码</li>
                        <li>密码格式要求：6-16位数字字母</li>
                    </ul>
                    
                    <h4>用户类型权限说明</h4>
                    <table class="table" style="margin: 15px 0;">
                        <thead>
                            <tr>
                                <th>用户类型</th>
                                <th>总记录限制</th>
                                <th>单次上传条数</th>
                                <th>单次上传文件大小</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>VIP0</td><td>1,000</td><td>100</td><td>1MB</td></tr>
                            <tr><td>VIP1</td><td>5,000</td><td>500</td><td>2MB</td></tr>
                            <tr><td>VIP2</td><td>10,000</td><td>1,000</td><td>4MB</td></tr>
                            <tr><td>VIP3</td><td>20,000</td><td>2,000</td><td>8MB</td></tr>
                            <tr><td>VIP4</td><td>50,000</td><td>5,000</td><td>16MB</td></tr>
                            <tr><td>VIP5</td><td>100,000</td><td>10,000</td><td>32MB</td></tr>
                            <tr><td>VIP6</td><td>200,000</td><td>20,000</td><td>64MB</td></tr>
                            <tr><td>VIP7</td><td>500,000</td><td>50,000</td><td>128MB</td></tr>
                            <tr><td>VIP8</td><td>1,000,000</td><td>100,000</td><td>256MB</td></tr>
                            <tr><td>VIP9</td><td>2,000,000</td><td>200,000</td><td>512MB</td></tr>
                        </tbody>
                    </table>
                    
                    <h4>操作注意事项</h4>
                    <ul>
                        <li>谨慎操作用户状态，禁用用户将无法登录</li>
                        <li>修改用户类型会影响其权限限制</li>
                        <li>关闭访问权限会阻止用户使用系统</li>
                        <li>定期查看操作日志，监控系统安全</li>
                        <li>及时处理异常操作和错误日志</li>
                    </ul>
                    
                    <h4>系统监控</h4>
                    <ul>
                        <li>通过操作日志监控用户行为</li>
                        <li>关注异常登录和操作</li>
                        <li>定期检查系统性能</li>
                        <li>及时处理用户反馈</li>
                    </ul>
                    
                    <h4>技术支持</h4>
                    <p>如有问题请联系系统开发团队。</p>
                    
                    <p style="margin-top: 30px; color: #666; font-size: 12px;">
                        系统版本: v<?php echo $version; ?><br>
                        更新日期: <?php echo date('Y-m-d'); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
            .help-content {
                padding: 20px;
                line-height: 1.6;
            }
            
            .help-content h4 {
                color: #2c3e50;
                margin-top: 20px;
                margin-bottom: 10px;
                border-bottom: 2px solid #3498db;
                padding-bottom: 5px;
            }
            
            .help-content h5 {
                color: #34495e;
                margin-top: 15px;
                margin-bottom: 8px;
            }
            
            .help-content ul {
                margin-left: 20px;
                margin-bottom: 15px;
            }
            
            .help-content li {
                margin-bottom: 5px;
            }
            
            .help-content table {
                font-size: 14px;
            }
            
            .help-content table th {
                background: #f8f9fa;
                font-weight: 600;
            }
        </style>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            function switchTab(tab) {
                window.location.href = 'admin.php?de=' + tab;
            }
        </script>
    </body>
    </html>
    <?php
}
?>