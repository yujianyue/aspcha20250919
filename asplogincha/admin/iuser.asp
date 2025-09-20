<%
' 用户管理页面
' 功能：用户列表、增删改查、批量操作
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "list"
        Call GetUserList()
    Case "add"
        Call AddUser()
    Case "edit"
        Call EditUser()
    Case "delete"
        Call DeleteUser()
    Case "batchdelete"
        Call BatchDeleteUser()
    Case "changepass"
        Call ChangeUserPassword()
    Case Else
        Call ShowUserPage()
End Select

' 显示用户管理页面
Sub ShowUserPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>用户管理</h3>
        <div class="text-right">
            <button class="btn btn-primary" onclick="showAddUser()">添加用户</button>
        </div>
    </div>
    <div class="card-body">
        <!-- 搜索表单 -->
        <form id="searchForm" class="mb-3">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchKeyword" name="keyword" placeholder="搜索用户名或姓名">
                </div>
                <div class="form-group">
                    <select class="form-control" id="searchStatus" name="status">
                        <option value="">全部状态</option>
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="searchUsers()">搜索</button>
                    <button type="button" class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
            </div>
        </form>
        
        <!-- 用户列表 -->
        <div id="userList">
            <div class="loading"></div> 正在加载用户列表...
        </div>
        
        <!-- 分页 -->
        <div id="pagination" class="pagination"></div>
    </div>
</div>

<script>
var currentPage = 1;
var pageSize = 10;

// 页面加载时获取用户列表
window.onload = function() {
    loadUserList();
};

function loadUserList(page) {
    if (page) currentPage = page;
    
    var keyword = document.getElementById('searchKeyword').value;
    var status = document.getElementById('searchStatus').value;
    
    ajaxRequest('?act=list', {
        page: currentPage,
        pageSize: pageSize,
        keyword: keyword,
        status: status
    }, function(response) {
        var listDiv = document.getElementById('userList');
        if (response.status == 1) {
            var html = '';
            if (response.data && response.data.length > 0) {
                html = '<table class="table" data-selectable="true">';
                html += '<thead><tr>';
                html += '<th><input type="checkbox" data-select-all></th>';
                html += '<th>用户名</th>';
                html += '<th>姓名</th>';
                html += '<th>状态</th>';
                html += '<th>操作</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                
                for (var i = 0; i < response.data.length; i++) {
                    var user = response.data[i];
                    var statusText = user.check == '1' ? '<span class="text-success">启用</span>' : '<span class="text-danger">禁用</span>';
                    
                    html += '<tr>';
                    html += '<td><input type="checkbox" value="' + user.user_id + '"></td>';
                    html += '<td>' + user.username + '</td>';
                    html += '<td>' + user.nickname + '</td>';
                    html += '<td>' + statusText + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary" onclick="editUser(' + user.user_id + ')">编辑</button> ';
                    html += '<button class="btn btn-sm btn-warning" onclick="changeUserPass(' + user.user_id + ')">改密码</button> ';
                    html += '<button class="btn btn-sm btn-danger" onclick="deleteUser(' + user.user_id + ')">删除</button>';
                    html += '</td>';
                    html += '</tr>';
                }
                
                html += '</tbody></table>';
                
                // 批量操作按钮
                html += '<div class="mt-3">';
                html += '<button class="btn btn-danger" onclick="batchDeleteUsers()">批量删除</button>';
                html += '</div>';
                
                // 分页
                if (response.totalPages > 1) {
                    initPagination('pagination', currentPage, response.totalPages, pageSize);
                }
            } else {
                html = '<div class="alert alert-info">暂无用户数据</div>';
            }
            listDiv.innerHTML = html;
            
            // 初始化表格选择功能
            initTableSelection('userList');
        } else {
            listDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}

function searchUsers() {
    currentPage = 1;
    loadUserList();
}

function resetSearch() {
    document.getElementById('searchKeyword').value = '';
    document.getElementById('searchStatus').value = '';
    currentPage = 1;
    loadUserList();
}

function showAddUser() {
    var content = '<form id="addUserForm">';
    content += '<div class="form-group">';
    content += '<label for="username">用户名：</label>';
    content += '<input type="text" class="form-control" id="username" name="username" required>';
    content += '</div>';
    content += '<div class="form-group">';
    content += '<label for="password">密码：</label>';
    content += '<input type="password" class="form-control" id="password" name="password" required minlength="5">';
    content += '</div>';
    content += '<div class="form-group">';
    content += '<label for="nickname">姓名：</label>';
    content += '<input type="text" class="form-control" id="nickname" name="nickname" required>';
    content += '</div>';
    content += '<div class="form-group">';
    content += '<label for="check">状态：</label>';
    content += '<select class="form-control" id="check" name="check">';
    content += '<option value="1">启用</option>';
    content += '<option value="0">禁用</option>';
    content += '</select>';
    content += '</div>';
    content += '</form>';
    
    showModal('添加用户', content, '<button class="btn btn-primary" onclick="submitAddUser()">添加</button><button class="btn btn-secondary" onclick="hideModal()">取消</button>');
}

function submitAddUser() {
    var formData = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
        nickname: document.getElementById('nickname').value,
        check: document.getElementById('check').value
    };
    
    ajaxRequest('?act=add', formData, function(response) {
        if (response.status == 1) {
            showMessage('用户添加成功', 'success');
            hideModal();
            loadUserList();
        } else {
            showMessage(response.msg, 'danger');
        }
    });
}

function editUser(id) {
    // 这里可以实现编辑用户功能
    showMessage('编辑功能待实现', 'info');
}

function changeUserPass(id) {
    var content = '<form id="changeUserPassForm">';
    content += '<input type="hidden" id="user_id" value="' + id + '">';
    content += '<div class="form-group">';
    content += '<label for="newpass">新密码：</label>';
    content += '<input type="password" class="form-control" id="newpass" name="newpass" required minlength="5">';
    content += '</div>';
    content += '</form>';
    
    showModal('修改用户密码', content, '<button class="btn btn-primary" onclick="submitChangeUserPass()">修改</button><button class="btn btn-secondary" onclick="hideModal()">取消</button>');
}

function submitChangeUserPass() {
    var formData = {
        user_id: document.getElementById('user_id').value,
        newpass: document.getElementById('newpass').value
    };
    
    ajaxRequest('?act=changepass', formData, function(response) {
        if (response.status == 1) {
            showMessage('密码修改成功', 'success');
            hideModal();
        } else {
            showMessage(response.msg, 'danger');
        }
    });
}

function deleteUser(id) {
    if (confirm('确定要删除这个用户吗？')) {
        ajaxRequest('?act=delete', {id: id}, function(response) {
            if (response.status == 1) {
                showMessage('用户删除成功', 'success');
                loadUserList();
            } else {
                showMessage(response.msg, 'danger');
            }
        });
    }
}

function batchDeleteUsers() {
    var ids = getSelectedIds('userList');
    if (ids.length === 0) {
        showMessage('请选择要删除的用户', 'warning');
        return;
    }
    
    if (confirm('确定要删除选中的 ' + ids.length + ' 个用户吗？')) {
        ajaxRequest('?act=batchdelete', {ids: ids.join(',')}, function(response) {
            if (response.status == 1) {
                showMessage('批量删除成功', 'success');
                loadUserList();
            } else {
                showMessage(response.msg, 'danger');
            }
        });
    }
}
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 获取用户列表
Sub GetUserList()
    On Error Resume Next
    
    Dim page, pageSize, keyword, status
    page = Request.Form("page")
    pageSize = Request.Form("pageSize")
    keyword = Request.Form("keyword")
    status = Request.Form("status")
    
    If page = "" Then page = 1
    If pageSize = "" Then pageSize = 10
    
    ' 连接数据库
    Dim conn, rs, sql, whereClause
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 构建查询条件
    whereClause = "WHERE [usertype]='user'"
    If keyword <> "" Then
        whereClause = whereClause & " AND ([username] LIKE '%" & SafeString(keyword) & "%' OR [nickname] LIKE '%" & SafeString(keyword) & "%')"
    End If
    If status <> "" Then
        whereClause = whereClause & " AND [check]=" & status
    End If
    
    ' 获取总数
    sql = "SELECT COUNT(*) FROM [user] " & whereClause
    Set rs = conn.Execute(sql)
    Dim totalCount
    totalCount = rs.Fields(0).Value
    rs.Close
    
    ' 分页查询
    Dim startRow, endRow
    startRow = (page - 1) * pageSize + 1
    endRow = page * pageSize
    
    sql = "SELECT * FROM [user] " & whereClause & " ORDER BY [user_id] DESC"
    Set rs = conn.Execute(sql)
    
    Dim result, totalPages
    result = RecordSetToJson(rs)
    totalPages = Int((totalCount + pageSize - 1) / pageSize)
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    ReturnJson "{""status"":1,""data"":" & result & ",""totalPages"":" & totalPages & "}"
End Sub

' 添加用户
Sub AddUser()
    On Error Resume Next
    
    Dim username, password, nickname, check
    username = Request.Form("username")
    password = Request.Form("password")
    nickname = Request.Form("nickname")
    check = Request.Form("check")
    
    If username = "" Or password = "" Or nickname = "" Then
        ReturnError "用户名、密码和姓名不能为空"
        Exit Sub
    End If
    
    If Len(password) < 5 Then
        ReturnError "密码长度不能少于5位"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 检查用户名是否已存在
    sql = "SELECT COUNT(*) FROM [user] WHERE [username]='" & SafeString(username) & "'"
    Set rs = conn.Execute(sql)
    If rs.Fields(0).Value > 0 Then
        ReturnError "用户名已存在"
        rs.Close
        conn.Close
        Set rs = Nothing
        Set conn = Nothing
        Exit Sub
    End If
    rs.Close
    
    ' 插入用户
    sql = "INSERT INTO [user] ([usertype], [username], [password], [nickname], [check]) VALUES ('user', '" & SafeString(username) & "', '" & MD5(password) & "', '" & SafeString(nickname) & "', " & check & ")"
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "用户添加成功"
    Else
        ReturnError "用户添加失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 删除用户
Sub DeleteUser()
    On Error Resume Next
    
    Dim id
    id = Request.Form("id")
    
    If id = "" Then
        ReturnError "用户ID不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 删除用户
    sql = "DELETE FROM [user] WHERE [user_id]=" & id & " AND [usertype]='user'"
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "用户删除成功"
    Else
        ReturnError "用户删除失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 批量删除用户
Sub BatchDeleteUser()
    On Error Resume Next
    
    Dim ids
    ids = Request.Form("ids")
    
    If ids = "" Then
        ReturnError "请选择要删除的用户"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 批量删除用户
    sql = "DELETE FROM [user] WHERE [user_id] IN (" & ids & ") AND [usertype]='user'"
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "批量删除成功"
    Else
        ReturnError "批量删除失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 修改用户密码
Sub ChangeUserPassword()
    On Error Resume Next
    
    Dim userid, newpass
    userid = Request.Form("user_id")
    newpass = Request.Form("newpass")
    
    If userid = "" Or newpass = "" Then
        ReturnError "用户ID和新密码不能为空"
        Exit Sub
    End If
    
    If Len(newpass) < 5 Then
        ReturnError "新密码长度不能少于5位"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 更新密码
    sql = "UPDATE [user] SET password='" & MD5(newpass) & "' WHERE [user_id]=" & userid & " AND [usertype]='user'"
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "密码修改成功"
    Else
        ReturnError "密码修改失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->