<%
' 管理员登录页面
' 功能：管理员登录验证
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "login"
        Call AdminLogin()
    Case "logout"
        Call AdminLogout()
    Case Else
        Call ShowLoginPage()
End Select

' 显示登录页面
Sub ShowLoginPage()
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 通用工资查询系统</title>
    <link rel="stylesheet" href="../inc/css.css">
</head>
<body>
    <div class="header">
        <h1>通用工资查询系统</h1>
        <p>管理员登录</p>
    </div>
    
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <div class="card-header">
                <h3>管理员登录</h3>
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
                    <a href="../user/index.asp" class="text-muted">用户登录</a>
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
                        window.location.href = 'index.asp';
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

' 管理员登录
Sub AdminLogin()
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
    
    ' 查询管理员
    sql = "SELECT * FROM [user] WHERE [username]='" & SafeString(username) & "' AND [usertype]='admin' AND [check]=True"
    Set rs = conn.Execute(sql)
    
    If Not rs.EOF Then
        ' 验证密码
        Dim md5pass
        md5pass = MD5(password)
        
        If rs("password") = md5pass Then
            Session("usertype") = "admin"
            Session("username") = username
            Session("userid") = rs("user_id")
            ReturnSuccess "登录成功"
        Else
            ReturnError "密码错误"
        End If
    Else
        ReturnError "管理员不存在或已被禁用"
    End If
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
End Sub

' 管理员退出
Sub AdminLogout()
    Session.Abandon
    ReturnSuccess "已退出登录"
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->