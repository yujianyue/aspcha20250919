<%
' 修改密码页面
' 功能：管理员修改密码
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "changepass"
        Call ChangePassword()
    Case "logout"
        Call AdminLogout()
    Case Else
        Call ShowChangePassPage()
End Select

' 显示修改密码页面
Sub ShowChangePassPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>修改密码</h3>
    </div>
    <div class="card-body">
        <form id="changePassForm">
            <div class="form-group">
                <label for="oldpass">原密码：</label>
                <input type="password" class="form-control" id="oldpass" name="oldpass" required>
            </div>
            
            <div class="form-group">
                <label for="newpass">新密码：</label>
                <input type="password" class="form-control" id="newpass" name="newpass" required minlength="5">
            </div>
            
            <div class="form-group">
                <label for="confirmpass">确认新密码：</label>
                <input type="password" class="form-control" id="confirmpass" name="confirmpass" required minlength="5">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">修改密码</button>
                <button type="button" class="btn btn-secondary" onclick="history.back()">返回</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('changePassForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var oldpass = document.getElementById('oldpass').value;
    var newpass = document.getElementById('newpass').value;
    var confirmpass = document.getElementById('confirmpass').value;
    
    if (newpass !== confirmpass) {
        showMessage('两次输入的密码不一致', 'danger');
        return;
    }
    
    if (newpass.length < 5) {
        showMessage('新密码长度不能少于5位', 'danger');
        return;
    }
    
    ajaxRequest('?act=changepass', {
        oldpass: oldpass,
        newpass: newpass
    }, function(response) {
        if (response.status == 1) {
            showMessage('密码修改成功', 'success');
            setTimeout(function() {
                window.location.href = 'index.asp';
            }, 1000);
        } else {
            showMessage(response.msg, 'danger');
        }
    });
});
</script>
<!--#include file="ifoot.asp"-->
<%
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

' 管理员退出
Sub AdminLogout()
    Session.Abandon
    ReturnSuccess "已退出登录"
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->