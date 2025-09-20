<%
' 数据库安装页面
' 功能：数据库写表功能页，可选导入示范数据
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "install"
        Call InstallDatabase()
    Case "import"
        Call ImportSampleData()
    Case Else
        Call ShowInstallPage()
End Select

' 显示安装页面
Sub ShowInstallPage()
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库安装 - 通用工资查询系统</title>
    <link rel="stylesheet" href="../inc/css.css">
</head>
<body>
    <div class="header">
        <h1>通用工资查询系统</h1>
        <p>数据库安装向导</p>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>数据库安装</h3>
            </div>
            <div class="card-body">
                <p>欢迎使用通用工资查询系统！请按照以下步骤完成数据库安装：</p>
                
                <div class="alert alert-info">
                    <h4>安装说明：</h4>
                    <ol>
                        <li>点击"安装数据库"按钮创建数据表</li>
                        <li>可选择导入示例数据（30条用户数据和30条工资数据）</li>
                        <li>安装完成后请删除install文件夹以确保安全</li>
                    </ol>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="importSample" value="1">
                        导入示例数据（推荐用于测试）
                    </label>
                </div>
                
                <div class="form-group">
                    <button class="btn btn-primary" onclick="installDatabase()">安装数据库</button>
                    <button class="btn btn-secondary" onclick="checkDatabase()">检查数据库状态</button>
                </div>
                
                <div id="installResult" class="mt-3"></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>系统信息</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td width="120">系统版本</td>
                        <td>通用工资查询系统 2025版</td>
                    </tr>
                    <tr>
                        <td>数据库类型</td>
                        <td>Microsoft Access (.mdb)</td>
                    </tr>
                    <tr>
                        <td>服务器环境</td>
                        <td>ASP + Access</td>
                    </tr>
                    <tr>
                        <td>编码格式</td>
                        <td>GB2312</td>
                    </tr>
                    <tr>
                        <td>默认管理员</td>
                        <td>admin / admin123</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        function installDatabase() {
            var importSample = document.getElementById('importSample').checked;
            var resultDiv = document.getElementById('installResult');
            
            resultDiv.innerHTML = '<div class="loading"></div> 正在安装数据库...';
            
            ajaxRequest('?act=install', {
                importSample: importSample ? 1 : 0
            }, function(response) {
                if (response.status == 1) {
                    resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
                    if (importSample) {
                        setTimeout(function() {
                            importSampleData();
                        }, 1000);
                    }
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
                }
            });
        }
        
        function importSampleData() {
            var resultDiv = document.getElementById('installResult');
            resultDiv.innerHTML += '<div class="loading"></div> 正在导入示例数据...';
            
            ajaxRequest('?act=import', {}, function(response) {
                if (response.status == 1) {
                    resultDiv.innerHTML += '<div class="alert alert-success">' + response.msg + '</div>';
                } else {
                    resultDiv.innerHTML += '<div class="alert alert-danger">' + response.msg + '</div>';
                }
            });
        }
        
        function checkDatabase() {
            var resultDiv = document.getElementById('installResult');
            resultDiv.innerHTML = '<div class="loading"></div> 正在检查数据库状态...';
            
            ajaxRequest('?act=check', {}, function(response) {
                if (response.status == 1) {
                    resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">' + response.msg + '</div>';
                }
            });
        }
    </script>
</body>
</html>
<%
End Sub

' 安装数据库
Sub InstallDatabase()
    On Error Resume Next
    
    Dim importSample
    importSample = Request.Form("importSample")
    
    ' 创建数据库文件
    Dim dbPath, fso, dbFile
    dbPath = Server.MapPath("../data.mdb")
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    ' 如果数据库文件不存在，创建它
    If Not fso.FileExists(dbPath) Then
        ' 创建空的Access数据库文件
        Dim cat
        Set cat = Server.CreateObject("ADOX.Catalog")
        cat.Create "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & dbPath
        Set cat = Nothing
    End If
    
    ' 连接数据库
    Dim conn
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & dbPath
    
    If Err.Number <> 0 Then
        ReturnError "数据库连接失败：" & Err.Description
        Exit Sub
    End If
    
    ' 创建表结构
    Dim sqls(2)
    sqls(0) = "CREATE TABLE [conn] ([id] AUTOINCREMENT PRIMARY KEY, [s_name] TEXT(50), [s_urls] TEXT(255), [s_tiao] TEXT(100), [s_isma] YESNO, [s_logs] YESNO, [s_desc] TEXT(255), [s_chax] TEXT(255), [s_jies] TEXT(255))"
    sqls(1) = "CREATE TABLE [user] ([user_id] AUTOINCREMENT PRIMARY KEY, [usertype] TEXT(10), [username] TEXT(50), [password] TEXT(32), [nickname] TEXT(50), [check] YESNO)"
    sqls(2) = "CREATE TABLE [data] ([id] AUTOINCREMENT PRIMARY KEY, [timu] TEXT(100), [tiao] TEXT(100), [path] TEXT(255), [icha] YESNO, [add_time] TEXT(8), [cha_note] TEXT(255))"
    
    ' 执行创建表语句
    Dim i, success
    success = True
    For i = 0 To 2
        conn.Execute sqls(i)
        If Err.Number <> 0 Then
            success = False
            Exit For
        End If
    Next
    
    If success Then
    ' 插入默认网站设置
    conn.Execute "INSERT INTO [conn] ([s_name], [s_urls], [s_tiao], [s_isma], [s_logs], [s_desc], [s_chax], [s_jies]) VALUES ('通用工资查询系统', '', '姓名', True, True, '请输入您的姓名进行查询', '工资查询结果', '暂无相关数据')"
    
    ' 插入默认管理员账户
    conn.Execute "INSERT INTO [user] ([usertype], [username], [password], [nickname], [check]) VALUES ('admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '系统管理员', True)"
        
        ' 如果选择导入示例数据
        If importSample = "1" Then
            Call ImportSampleDataToDB(conn)
        End If
        
        If importSample = "1" Then
            ReturnSuccess "数据库安装成功！示例数据已导入。"
        Else
            ReturnSuccess "数据库安装成功！"
        End If
    Else
        ReturnError "数据库安装失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 导入示例数据到数据库
Sub ImportSampleDataToDB(conn)
    Dim i, sql
    
    ' 导入示例用户数据
    For i = 1 To 30
        sql = "INSERT INTO [user] ([usertype], [username], [password], [nickname], [check]) VALUES ('user', 'user" & i & "', '5d41402abc4b2a76b9719d911017c592', '用户" & i & "', True)"
        conn.Execute sql
    Next
    
    ' 导入示例工资数据
    For i = 1 To 30
        sql = "INSERT INTO [data] ([timu], [tiao], [path], [icha], [add_time], [cha_note]) VALUES ('2025年" & i & "月工资', 'user" & i & "', 'data/salary_" & i & ".txt', True, '" & Format(Now(), "yyyymmdd") & "', '示例工资数据')"
        conn.Execute sql
    Next
End Sub

' 导入示例数据
Sub ImportSampleData()
    On Error Resume Next
    
    Dim dbPath, conn
    dbPath = Server.MapPath("../data.mdb")
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & dbPath
    
    If Err.Number <> 0 Then
        ReturnError "数据库连接失败：" & Err.Description
        Exit Sub
    End If
    
    Call ImportSampleDataToDB(conn)
    
    If Err.Number = 0 Then
        ReturnSuccess "示例数据导入成功！"
    Else
        ReturnError "示例数据导入失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->