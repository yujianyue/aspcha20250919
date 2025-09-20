<%
' 辅助工具页面
' 功能：对比用户表和工资数据文件中的用户
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "compare"
        Call CompareUsers()
    Case Else
        Call ShowToolPage()
End Select

' 显示工具页面
Sub ShowToolPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>辅助工具</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h4>用户对比工具</h4>
            <p>对比用户表中的用户和工资数据文件中的查询条件，显示各自独有的用户。</p>
        </div>
        
        <form id="compareForm">
            <div class="form-group">
                <label for="salaryId">选择工资数据：</label>
                <select class="form-control" id="salaryId" name="salaryId" required>
                    <option value="">请选择工资数据</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">开始对比</button>
            </div>
        </form>
        
        <div id="compareResult" class="mt-3"></div>
    </div>
</div>

<script>
// 页面加载时获取工资数据列表
window.onload = function() {
    loadSalaryOptions();
};

function loadSalaryOptions() {
    ajaxRequest('?act=getsalary', {}, function(response) {
        var select = document.getElementById('salaryId');
        if (response.status == 1) {
            select.innerHTML = '<option value="">请选择工资数据</option>';
            for (var i = 0; i < response.data.length; i++) {
                var item = response.data[i];
                select.innerHTML += '<option value="' + item.id + '">' + item.timu + ' (' + item.tiao + ')</option>';
            }
        } else {
            select.innerHTML = '<option value="">加载失败</option>';
        }
    });
}

document.getElementById('compareForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var salaryId = document.getElementById('salaryId').value;
    if (!salaryId) {
        showMessage('请选择工资数据', 'warning');
        return;
    }
    
    var resultDiv = document.getElementById('compareResult');
    resultDiv.innerHTML = '<div class="loading"></div> 正在对比用户数据...';
    
    ajaxRequest('?act=compare', {
        salaryId: salaryId
    }, function(response) {
        if (response.status == 1) {
            var html = '<div class="row">';
            
            // 用户表独有用户
            html += '<div class="col-md-6">';
            html += '<div class="card">';
            html += '<div class="card-header">';
            html += '<h5>用户表独有用户 (' + response.data.userOnly.length + ' 个)</h5>';
            html += '</div>';
            html += '<div class="card-body">';
            if (response.data.userOnly.length > 0) {
                html += '<ul class="list-group">';
                for (var i = 0; i < response.data.userOnly.length; i++) {
                    html += '<li class="list-group-item">' + response.data.userOnly[i] + '</li>';
                }
                html += '</ul>';
            } else {
                html += '<p class="text-muted">无独有用户</p>';
            }
            html += '</div></div></div>';
            
            // 工资数据独有用户
            html += '<div class="col-md-6">';
            html += '<div class="card">';
            html += '<div class="card-header">';
            html += '<h5>工资数据独有用户 (' + response.data.salaryOnly.length + ' 个)</h5>';
            html += '</div>';
            html += '<div class="card-body">';
            if (response.data.salaryOnly.length > 0) {
                html += '<ul class="list-group">';
                for (var i = 0; i < response.data.salaryOnly.length; i++) {
                    html += '<li class="list-group-item">' + response.data.salaryOnly[i] + '</li>';
                }
                html += '</ul>';
            } else {
                html += '<p class="text-muted">无独有用户</p>';
            }
            html += '</div></div></div>';
            
            html += '</div>';
            
            // 共同用户
            html += '<div class="mt-3">';
            html += '<div class="card">';
            html += '<div class="card-header">';
            html += '<h5>共同用户 (' + response.data.common.length + ' 个)</h5>';
            html += '</div>';
            html += '<div class="card-body">';
            if (response.data.common.length > 0) {
                html += '<ul class="list-group">';
                for (var i = 0; i < response.data.common.length; i++) {
                    html += '<li class="list-group-item">' + response.data.common[i] + '</li>';
                }
                html += '</ul>';
            } else {
                html += '<p class="text-muted">无共同用户</p>';
            }
            html += '</div></div></div>';
            
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
});
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 获取工资数据选项
Sub GetSalaryOptions()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 查询工资数据
    sql = "SELECT [id], [timu], [tiao] FROM [data] ORDER BY [id] DESC"
    Set rs = conn.Execute(sql)
    
    Dim result
    result = RecordSetToJson(rs)
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    ReturnJson "{""status"":1,""data"":" & result & "}"
End Sub

' 对比用户
Sub CompareUsers()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim salaryId
    salaryId = Request.Form("salaryId")
    
    If salaryId = "" Then
        ReturnError "请选择工资数据"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 获取用户表用户列表
    sql = "SELECT [username] FROM [user] WHERE [usertype]='user'"
    Set rs = conn.Execute(sql)
    
    Dim userList(), i
    i = 0
    ReDim userList(0)
    
    Do While Not rs.EOF
        ReDim Preserve userList(i)
        userList(i) = rs("username")
        i = i + 1
        rs.MoveNext
    Loop
    rs.Close
    
    ' 获取工资数据
    sql = "SELECT [path] FROM [data] WHERE [id]=" & salaryId
    Set rs = conn.Execute(sql)
    
    Dim salaryList()
    ReDim salaryList(0)
    
    If Not rs.EOF Then
        ' 读取工资数据文件
        Dim filePath, fso, file, content, lines, j, line, fields
        filePath = Server.MapPath("../" & rs("path"))
        Set fso = Server.CreateObject("Scripting.FileSystemObject")
        
        If fso.FileExists(filePath) Then
            Set file = fso.OpenTextFile(filePath, 1, False)
            content = file.ReadAll
            file.Close
            Set file = Nothing
            
            ' 解析数据（假设每行一个用户）
            lines = Split(content, vbCrLf)
            j = 0
            For i = 0 To UBound(lines)
                line = Trim(lines(i))
                If line <> "" Then
                    ' 这里需要根据实际数据格式解析
                    ' 假设第一列是用户名
                    fields = Split(line, vbTab)
                    If UBound(fields) >= 0 Then
                        ReDim Preserve salaryList(j)
                        salaryList(j) = Trim(fields(0))
                        j = j + 1
                    End If
                End If
            Next
        End If
        Set fso = Nothing
    End If
    rs.Close
    
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    ' 对比用户
    Dim userOnly(), salaryOnly(), common()
    ReDim userOnly(0)
    ReDim salaryOnly(0)
    ReDim common(0)
    
    Dim userCount, salaryCount, commonCount
    userCount = 0
    salaryCount = 0
    commonCount = 0
    
    ' 找出用户表独有用户
    For i = 0 To UBound(userList)
        Dim found
        found = False
        For j = 0 To UBound(salaryList)
            If userList(i) = salaryList(j) Then
                found = True
                Exit For
            End If
        Next
        
        If Not found Then
            ReDim Preserve userOnly(userCount)
            userOnly(userCount) = userList(i)
            userCount = userCount + 1
        End If
    Next
    
    ' 找出工资数据独有用户
    For i = 0 To UBound(salaryList)
        found = False
        For j = 0 To UBound(userList)
            If salaryList(i) = userList(j) Then
                found = True
                Exit For
            End If
        Next
        
        If Not found Then
            ReDim Preserve salaryOnly(salaryCount)
            salaryOnly(salaryCount) = salaryList(i)
            salaryCount = salaryCount + 1
        End If
    Next
    
    ' 找出共同用户
    For i = 0 To UBound(userList)
        found = False
        For j = 0 To UBound(salaryList)
            If userList(i) = salaryList(j) Then
                found = True
                Exit For
            End If
        Next
        
        If found Then
            ReDim Preserve common(commonCount)
            common(commonCount) = userList(i)
            commonCount = commonCount + 1
        End If
    Next
    
    Dim result
    result = "{""userOnly"":" & ArrayToJson(userOnly) & ",""salaryOnly"":" & ArrayToJson(salaryOnly) & ",""common"":" & ArrayToJson(common) & "}"
    ReturnJson "{""status"":1,""data"":" & result & "}"
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->