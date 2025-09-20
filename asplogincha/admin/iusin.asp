<%
' 用户数据导入页面
' 功能：从Excel粘贴数据导入用户
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "import"
        Call ImportUsers()
    Case Else
        Call ShowImportPage()
End Select

' 显示导入页面
Sub ShowImportPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>用户数据导入</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h4>导入说明：</h4>
            <ol>
                <li>从Excel中复制数据（包含用户名、密码、姓名三列）</li>
                <li>粘贴到下方文本框中</li>
                <li>系统会自动验证数据格式</li>
                <li>用户名和密码必须是5-20位数字大小写字母组合</li>
            </ol>
        </div>
        
        <form id="importForm">
            <div class="form-group">
                <label for="userData">用户数据（制表符分隔）：</label>
                <textarea class="form-control" id="userData" name="userData" rows="10" placeholder="请粘贴Excel数据，格式：用户名	密码	姓名"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">开始导入</button>
                <button type="button" class="btn btn-secondary" onclick="clearData()">清空数据</button>
            </div>
        </form>
        
        <div id="importResult" class="mt-3"></div>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var userData = document.getElementById('userData').value.trim();
    if (!userData) {
        showMessage('请输入要导入的用户数据', 'warning');
        return;
    }
    
    var resultDiv = document.getElementById('importResult');
    resultDiv.innerHTML = '<div class="loading"></div> 正在导入用户数据...';
    
    ajaxRequest('?act=import', {
        userData: userData
    }, function(response) {
        if (response.status == 1) {
            resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
            if (response.data) {
                var html = '<div class="mt-3">';
                html += '<h5>导入结果：</h5>';
                html += '<ul>';
                html += '<li>成功导入：' + response.data.success + ' 条</li>';
                html += '<li>失败：' + response.data.failed + ' 条</li>';
                if (response.data.errors && response.data.errors.length > 0) {
                    html += '<li>错误信息：</li>';
                    html += '<ul>';
                    for (var i = 0; i < response.data.errors.length; i++) {
                        html += '<li class="text-danger">' + response.data.errors[i] + '</li>';
                    }
                    html += '</ul>';
                }
                html += '</ul></div>';
                resultDiv.innerHTML += html;
            }
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
});

function clearData() {
    document.getElementById('userData').value = '';
    document.getElementById('importResult').innerHTML = '';
}
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 导入用户数据
Sub ImportUsers()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim userData, lines, i, line, fields, username, password, nickname
    userData = Request.Form("userData")
    
    If userData = "" Then
        ReturnError "请输入要导入的用户数据"
        Exit Sub
    End If
    
    ' 分割数据行
    lines = Split(userData, vbCrLf)
    
    Dim successCount, failedCount, errors()
    successCount = 0
    failedCount = 0
    ReDim errors(0)
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 处理每一行数据
    For i = 0 To UBound(lines)
        line = Trim(lines(i))
        If line <> "" Then
            ' 分割字段（制表符分隔）
            fields = Split(line, vbTab)
            
            If UBound(fields) >= 2 Then
                username = Trim(fields(0))
                password = Trim(fields(1))
                nickname = Trim(fields(2))
                
                ' 验证数据格式
                If ValidateUserData(username, password, nickname) Then
                    ' 检查用户名是否已存在
                    sql = "SELECT COUNT(*) FROM [user] WHERE [username]='" & SafeString(username) & "'"
                    Set rs = conn.Execute(sql)
                    
                    If rs.Fields(0).Value = 0 Then
                        ' 插入用户
                        sql = "INSERT INTO [user] ([usertype], [username], [password], [nickname], [check]) VALUES ('user', '" & SafeString(username) & "', '" & MD5(password) & "', '" & SafeString(nickname) & "', True)"
                        conn.Execute sql
                        
                        If Err.Number = 0 Then
                            successCount = successCount + 1
                        Else
                            failedCount = failedCount + 1
                            AddError errors, "第" & (i+1) & "行：" & Err.Description
                        End If
                    Else
                        failedCount = failedCount + 1
                        AddError errors, "第" & (i+1) & "行：用户名已存在"
                    End If
                    rs.Close
                Else
                    failedCount = failedCount + 1
                    AddError errors, "第" & (i+1) & "行：数据格式不正确"
                End If
            Else
                failedCount = failedCount + 1
                AddError errors, "第" & (i+1) & "行：数据列数不足"
            End If
        End If
    Next
    
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    Dim result
    result = "{""success"":" & successCount & ",""failed"":" & failedCount & ",""errors"":" & ArrayToJson(errors) & "}"
    ReturnJson "{""status"":1,""msg"":""导入完成"",""data"":" & result & "}"
End Sub

' 验证用户数据格式
Function ValidateUserData(username, password, nickname)
    ' 检查用户名和密码格式（5-20位数字大小写字母组合）
    Dim usernamePattern, passwordPattern
    usernamePattern = "^[a-zA-Z0-9]{5,20}$"
    passwordPattern = "^[a-zA-Z0-9]{5,20}$"
    
    ' 简单的正则表达式验证（ASP中可以使用更复杂的验证）
    If Len(username) >= 5 And Len(username) <= 20 And Len(password) >= 5 And Len(password) <= 20 And Len(nickname) > 0 Then
        ValidateUserData = True
    Else
        ValidateUserData = False
    End If
End Function

' 添加错误信息
Sub AddError(errors, errorMsg)
    Dim newSize
    newSize = UBound(errors) + 1
    ReDim Preserve errors(newSize)
    errors(newSize) = errorMsg
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->