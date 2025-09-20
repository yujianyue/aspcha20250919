<%
' 用户首页
' 功能：用户登录、修改密码、退出、查询工资数据
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "login"
        Call UserLogin()
    Case "logout"
        Call UserLogout()
    Case "changepass"
        Call ChangePassword()
    Case "query"
        Call QuerySalary()
    Case "getdata"
        Call GetSalaryData()
    Case Else
        Call ShowUserPage()
End Select

' 显示用户页面
Sub ShowUserPage()
    ' 检查是否已登录
    If Session("usertype") <> "user" Or Session("username") = "" Then
        Call ShowLoginForm()
    Else
        Call ShowUserDashboard()
    End If
End Sub

' 显示登录表单
Sub ShowLoginForm()
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - 通用工资查询系统</title>
    <link rel="stylesheet" href="../inc/css.css">
</head>
<body>
    <div class="header">
        <h1>通用工资查询系统</h1>
        <p>用户登录</p>
    </div>
    
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <div class="card-header">
                <h3>用户登录</h3>
            </div>
            <div class="card-body">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="username">用户名：</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">密码：</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100">登录</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="../admin/login.asp" class="text-muted">管理员登录</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };
            
            ajaxRequest('?act=login', formData, function(response) {
                if (response.status == 1) {
                    showMessage('登录成功！', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage(response.msg, 'danger');
                }
            });
        });
    </script>
</body>
</html>
<%
End Sub

' 显示用户仪表板
Sub ShowUserDashboard()
    Dim username
    username = Session("username")
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>工资查询 - 通用工资查询系统</title>
    <link rel="stylesheet" href="../inc/css.css">
</head>
<body>
    <div class="header">
        <h1>通用工资查询系统</h1>
        <p>欢迎，<%=username%></p>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>我的工资查询</h3>
                <div class="text-right">
                    <button class="btn btn-sm btn-secondary" onclick="changePassword()">修改密码</button>
                    <button class="btn btn-sm btn-danger" onclick="logout()">退出登录</button>
                </div>
            </div>
            <div class="card-body">
                <div id="salaryList">
                    <div class="loading"></div> 正在加载数据...
                </div>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 页面加载时获取工资列表
        window.onload = function() {
            loadSalaryList();
        };
        
        function loadSalaryList() {
            ajaxRequest('?act=query', {}, function(response) {
                var listDiv = document.getElementById('salaryList');
                if (response.status == 1) {
                    var html = '';
                    if (response.data && response.data.length > 0) {
                        html = '<table class="table">';
                        html += '<thead><tr><th>查询标题</th><th>添加时间</th><th>状态</th><th>操作</th></tr></thead>';
                        html += '<tbody>';
                        for (var i = 0; i < response.data.length; i++) {
                            var item = response.data[i];
                            var statusText = item.icha == '1' ? '<span class="text-success">可查询</span>' : '<span class="text-danger">不可查询</span>';
                            var actionBtn = item.icha == '1' ? '<button class="btn btn-sm btn-primary" onclick="viewSalary(' + item.id + ')">查看详情</button>' : '<span class="text-muted">暂不可查询</span>';
                            
                            html += '<tr>';
                            html += '<td>' + item.timu + '</td>';
                            html += '<td>' + item.add_time + '</td>';
                            html += '<td>' + statusText + '</td>';
                            html += '<td>' + actionBtn + '</td>';
                            html += '</tr>';
                        }
                        html += '</tbody></table>';
                    } else {
                        html = '<div class="alert alert-info">暂无工资数据</div>';
                    }
                    listDiv.innerHTML = html;
                } else {
                    listDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
                }
            });
        }
        
        function viewSalary(id) {
            ajaxRequest('?act=getdata', {id: id}, function(response) {
                if (response.status == 1) {
                    var content = '<h4>工资详情</h4>';
                    if (response.data) {
                        content += '<div class="table-responsive">';
                        content += '<table class="table">';
                        content += '<thead><tr><th>项目</th><th>金额</th></tr></thead>';
                        content += '<tbody>';
                        for (var key in response.data) {
                            content += '<tr><td>' + key + '</td><td>' + response.data[key] + '</td></tr>';
                        }
                        content += '</tbody></table>';
                        content += '</div>';
                    } else {
                        content += '<div class="alert alert-warning">暂无详细数据</div>';
                    }
                    
                    showModal('工资详情', content, '<button class="btn btn-secondary" onclick="hideModal()">关闭</button>');
                } else {
                    showMessage(response.msg, 'danger');
                }
            });
        }
        
        function changePassword() {
            var content = '<form id="changePassForm">';
            content += '<div class="form-group">';
            content += '<label for="oldpass">原密码：</label>';
            content += '<input type="password" class="form-control" id="oldpass" name="oldpass" required>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="newpass">新密码：</label>';
            content += '<input type="password" class="form-control" id="newpass" name="newpass" required>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="confirmpass">确认新密码：</label>';
            content += '<input type="password" class="form-control" id="confirmpass" name="confirmpass" required>';
            content += '</div>';
            content += '</form>';
            
            showModal('修改密码', content, '<button class="btn btn-primary" onclick="submitChangePass()">确认修改</button><button class="btn btn-secondary" onclick="hideModal()">取消</button>');
        }
        
        function submitChangePass() {
            var oldpass = document.getElementById('oldpass').value;
            var newpass = document.getElementById('newpass').value;
            var confirmpass = document.getElementById('confirmpass').value;
            
            if (newpass !== confirmpass) {
                showMessage('两次输入的密码不一致', 'danger');
                return;
            }
            
            ajaxRequest('?act=changepass', {
                oldpass: oldpass,
                newpass: newpass
            }, function(response) {
                if (response.status == 1) {
                    showMessage('密码修改成功', 'success');
                    hideModal();
                } else {
                    showMessage(response.msg, 'danger');
                }
            });
        }
        
        function logout() {
            if (confirm('确定要退出登录吗？')) {
                ajaxRequest('?act=logout', {}, function(response) {
                    window.location.reload();
                });
            }
        }
    </script>
</body>
</html>
<%
End Sub

' 用户登录
Sub UserLogin()
    On Error Resume Next
    
    Dim username, password
    username = Request.Form("username")
    password = Request.Form("password")
    
    If username = "" Or password = "" Then
        ReturnError "用户名和密码不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 查询用户
    sql = "SELECT * FROM [user] WHERE [username]='" & SafeString(username) & "' AND [usertype]='user' AND [check]=1"
    Set rs = conn.Execute(sql)
    
    If Not rs.EOF Then
        ' 验证密码（这里使用简单的MD5，实际项目中应使用更安全的方式）
        Dim md5pass
        md5pass = MD5(password)
        
        If rs("password") = md5pass Then
            Session("usertype") = "user"
            Session("username") = username
            Session("userid") = rs("user_id")
            ReturnSuccess "登录成功"
        Else
            ReturnError "密码错误"
        End If
    Else
        ReturnError "用户不存在或已被禁用"
    End If
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
End Sub

' 用户退出
Sub UserLogout()
    Session.Abandon
    ReturnSuccess "已退出登录"
End Sub

' 修改密码
Sub ChangePassword()
    On Error Resume Next
    
    Dim oldpass, newpass, userid
    oldpass = Request.Form("oldpass")
    newpass = Request.Form("newpass")
    userid = Session("userid")
    
    If userid = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    If newpass = "" Or Len(newpass) < 5 Then
        ReturnError "新密码长度不能少于5位"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 验证原密码
    sql = "SELECT password FROM [user] WHERE user_id=" & userid
    Set rs = conn.Execute(sql)
    
    If Not rs.EOF Then
        If rs("password") = MD5(oldpass) Then
            ' 更新密码
            sql = "UPDATE [user] SET password='" & MD5(newpass) & "' WHERE user_id=" & userid
            conn.Execute sql
            
            If Err.Number = 0 Then
                ReturnSuccess "密码修改成功"
            Else
                ReturnError "密码修改失败：" & Err.Description
            End If
        Else
            ReturnError "原密码错误"
        End If
    Else
        ReturnError "用户不存在"
    End If
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
End Sub

' 查询工资列表
Sub QuerySalary()
    On Error Resume Next
    
    Dim userid, username
    userid = Session("userid")
    username = Session("username")
    
    If userid = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 查询该用户的工资数据
    sql = "SELECT * FROM [data] WHERE [tiao]='" & SafeString(username) & "' ORDER BY [add_time] DESC"
    Set rs = conn.Execute(sql)
    
    Dim result
    result = RecordSetToJson(rs)
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    ReturnJson "{""status"":1,""data"":" & result & "}"
End Sub

' 获取工资详细数据
Sub GetSalaryData()
    On Error Resume Next
    
    Dim id, userid, username
    id = Request.Form("id")
    userid = Session("userid")
    username = Session("username")
    
    If userid = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 查询工资数据
    sql = "SELECT * FROM [data] WHERE [id]=" & id & " AND [tiao]='" & SafeString(username) & "' AND [icha]=1"
    Set rs = conn.Execute(sql)
    
    If Not rs.EOF Then
        ' 读取文件数据
        Dim filePath, fso, file, content
        filePath = Server.MapPath("../" & rs("path"))
        Set fso = Server.CreateObject("Scripting.FileSystemObject")
        
        If fso.FileExists(filePath) Then
            Set file = fso.OpenTextFile(filePath, 1, False)
            content = file.ReadAll
            file.Close
            Set file = Nothing
            
            ' 解析数据（假设是JSON格式）
            Dim data
            data = content
            ReturnJson "{""status"":1,""data"":" & data & "}"
        Else
            ReturnError "数据文件不存在"
        End If
        
        Set fso = Nothing
    Else
        ReturnError "数据不存在或无权限访问"
    End If
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->