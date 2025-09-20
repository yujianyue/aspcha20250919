<%
' 登录测试页面
' 功能：测试用户登录功能
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "test"
        Call TestLogin()
    Case Else
        Call ShowTestPage()
End Select

' 显示测试页面
Sub ShowTestPage()
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录测试 - 通用工资查询系统</title>
    <link rel="stylesheet" href="inc/css.css">
</head>
<body>
    <div class="header">
        <h1>登录测试页面</h1>
        <p>测试用户登录功能</p>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>测试登录</h3>
            </div>
            <div class="card-body">
                <form id="testForm">
                    <div class="form-group">
                        <label for="username">用户名：</label>
                        <input type="text" class="form-control" id="username" name="username" value="admin">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">密码：</label>
                        <input type="password" class="form-control" id="password" name="password" value="admin123">
                    </div>
                    
                    <div class="form-group">
                        <label for="usertype">用户类型：</label>
                        <select class="form-control" id="usertype" name="usertype">
                            <option value="admin">管理员</option>
                            <option value="user">普通用户</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">测试登录</button>
                    </div>
                </form>
                
                <div id="testResult" class="mt-3"></div>
            </div>
        </div>
    </div>
    
    <script src="inc/js.js"></script>
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                usertype: document.getElementById('usertype').value
            };
            
            var resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="loading"></div> 正在测试登录...';
            
            ajaxRequest('?act=test', formData, function(response) {
                if (response.status == 1) {
                    resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
                }
            });
        });
    </script>
</body>
</html>
<%
End Sub

' 测试登录
Sub TestLogin()
    On Error Resume Next
    
    Dim username, password, usertype
    username = Request.Form("username")
    password = Request.Form("password")
    usertype = Request.Form("usertype")
    
    If username = "" Or password = "" Then
        ReturnError "用户名和密码不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("data.mdb")
    
    If Err.Number <> 0 Then
        ReturnError "数据库连接失败：" & Err.Description
        Exit Sub
    End If
    
    ' 查询用户
    sql = "SELECT * FROM [user] WHERE [username]='" & SafeString(username) & "' AND [usertype]='" & usertype & "' AND [check]=True"
    Set rs = conn.Execute(sql)
    
    If Not rs.EOF Then
        ' 验证密码
        Dim md5pass
        md5pass = MD5(password)
        
        If rs("password") = md5pass Then
            ReturnSuccess "登录成功！用户ID：" & rs("user_id") & "，姓名：" & rs("nickname")
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

' 包含公共函数
%>
<!--#include file="inc/pubs.asp"-->