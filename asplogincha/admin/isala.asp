<%
' 工资管理页面
' 功能：工资列表、增删改查、批量操作
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "list"
        Call GetSalaryList()
    Case "edit"
        Call EditSalary()
    Case "delete"
        Call DeleteSalary()
    Case "batchdelete"
        Call BatchDeleteSalary()
    Case Else
        Call ShowSalaryPage()
End Select

' 显示工资管理页面
Sub ShowSalaryPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>工资管理</h3>
    </div>
    <div class="card-body">
        <!-- 搜索表单 -->
        <form id="searchForm" class="mb-3">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchKeyword" name="keyword" placeholder="搜索标题或查询条件">
                </div>
                <div class="form-group">
                    <select class="form-control" id="searchStatus" name="status">
                        <option value="">全部状态</option>
                        <option value="1">可查询</option>
                        <option value="0">不可查询</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="searchSalaries()">搜索</button>
                    <button type="button" class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
            </div>
        </form>
        
        <!-- 工资列表 -->
        <div id="salaryList">
            <div class="loading"></div> 正在加载工资列表...
        </div>
        
        <!-- 分页 -->
        <div id="pagination" class="pagination"></div>
    </div>
</div>

<script>
var currentPage = 1;
var pageSize = 10;

// 页面加载时获取工资列表
window.onload = function() {
    loadSalaryList();
};

function loadSalaryList(page) {
    if (page) currentPage = page;
    
    var keyword = document.getElementById('searchKeyword').value;
    var status = document.getElementById('searchStatus').value;
    
    ajaxRequest('?act=list', {
        page: currentPage,
        pageSize: pageSize,
        keyword: keyword,
        status: status
    }, function(response) {
        var listDiv = document.getElementById('salaryList');
        if (response.status == 1) {
            var html = '';
            if (response.data && response.data.length > 0) {
                html = '<table class="table" data-selectable="true">';
                html += '<thead><tr>';
                html += '<th><input type="checkbox" data-select-all></th>';
                html += '<th>查询标题</th>';
                html += '<th>查询条件</th>';
                html += '<th>文件路径</th>';
                html += '<th>状态</th>';
                html += '<th>添加时间</th>';
                html += '<th>操作</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                
                for (var i = 0; i < response.data.length; i++) {
                    var salary = response.data[i];
                    var statusText = salary.icha == '1' ? '<span class="text-success">可查询</span>' : '<span class="text-danger">不可查询</span>';
                    var addTime = salary.add_time;
                    if (addTime.length == 8) {
                        addTime = addTime.substring(0,4) + '-' + addTime.substring(4,6) + '-' + addTime.substring(6,8);
                    }
                    
                    html += '<tr>';
                    html += '<td><input type="checkbox" value="' + salary.id + '"></td>';
                    html += '<td>' + salary.timu + '</td>';
                    html += '<td>' + salary.tiao + '</td>';
                    html += '<td>' + salary.path + '</td>';
                    html += '<td>' + statusText + '</td>';
                    html += '<td>' + addTime + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary" onclick="editSalary(' + salary.id + ')">编辑</button> ';
                    html += '<button class="btn btn-sm btn-danger" onclick="deleteSalary(' + salary.id + ')">删除</button>';
                    html += '</td>';
                    html += '</tr>';
                }
                
                html += '</tbody></table>';
                
                // 批量操作按钮
                html += '<div class="mt-3">';
                html += '<button class="btn btn-danger" onclick="batchDeleteSalaries()">批量删除</button>';
                html += '</div>';
                
                // 分页
                if (response.totalPages > 1) {
                    initPagination('pagination', currentPage, response.totalPages, pageSize);
                }
            } else {
                html = '<div class="alert alert-info">暂无工资数据</div>';
            }
            listDiv.innerHTML = html;
            
            // 初始化表格选择功能
            initTableSelection('salaryList');
        } else {
            listDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}

function searchSalaries() {
    currentPage = 1;
    loadSalaryList();
}

function resetSearch() {
    document.getElementById('searchKeyword').value = '';
    document.getElementById('searchStatus').value = '';
    currentPage = 1;
    loadSalaryList();
}

function editSalary(id) {
    ajaxRequest('?act=get', {id: id}, function(response) {
        if (response.status == 1) {
            var salary = response.data;
            var content = '<form id="editSalaryForm">';
            content += '<input type="hidden" id="id" value="' + salary.id + '">';
            content += '<div class="form-group">';
            content += '<label for="timu">查询标题：</label>';
            content += '<input type="text" class="form-control" id="timu" name="timu" value="' + salary.timu + '" required>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="tiao">查询条件：</label>';
            content += '<input type="text" class="form-control" id="tiao" name="tiao" value="' + salary.tiao + '" required>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="path">文件路径：</label>';
            content += '<input type="text" class="form-control" id="path" name="path" value="' + salary.path + '" required>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="icha">查询状态：</label>';
            content += '<select class="form-control" id="icha" name="icha">';
            content += '<option value="1"' + (salary.icha == '1' ? ' selected' : '') + '>可查询</option>';
            content += '<option value="0"' + (salary.icha == '0' ? ' selected' : '') + '>不可查询</option>';
            content += '</select>';
            content += '</div>';
            content += '<div class="form-group">';
            content += '<label for="cha_note">查询备注：</label>';
            content += '<textarea class="form-control" id="cha_note" name="cha_note" rows="3">' + (salary.cha_note || '') + '</textarea>';
            content += '</div>';
            content += '</form>';
            
            showModal('编辑工资记录', content, '<button class="btn btn-primary" onclick="submitEditSalary()">保存</button><button class="btn btn-secondary" onclick="hideModal()">取消</button>');
        } else {
            showMessage(response.msg, 'danger');
        }
    });
}

function submitEditSalary() {
    var formData = {
        id: document.getElementById('id').value,
        timu: document.getElementById('timu').value,
        tiao: document.getElementById('tiao').value,
        path: document.getElementById('path').value,
        icha: document.getElementById('icha').value,
        cha_note: document.getElementById('cha_note').value
    };
    
    ajaxRequest('?act=edit', formData, function(response) {
        if (response.status == 1) {
            showMessage('工资记录更新成功', 'success');
            hideModal();
            loadSalaryList();
        } else {
            showMessage(response.msg, 'danger');
        }
    });
}

function deleteSalary(id) {
    if (confirm('确定要删除这个工资记录吗？')) {
        ajaxRequest('?act=delete', {id: id}, function(response) {
            if (response.status == 1) {
                showMessage('工资记录删除成功', 'success');
                loadSalaryList();
            } else {
                showMessage(response.msg, 'danger');
            }
        });
    }
}

function batchDeleteSalaries() {
    var ids = getSelectedIds('salaryList');
    if (ids.length === 0) {
        showMessage('请选择要删除的工资记录', 'warning');
        return;
    }
    
    if (confirm('确定要删除选中的 ' + ids.length + ' 个工资记录吗？')) {
        ajaxRequest('?act=batchdelete', {ids: ids.join(',')}, function(response) {
            if (response.status == 1) {
                showMessage('批量删除成功', 'success');
                loadSalaryList();
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

' 获取工资列表
Sub GetSalaryList()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
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
    whereClause = "WHERE 1=1"
    If keyword <> "" Then
        whereClause = whereClause & " AND ([timu] LIKE '%" & SafeString(keyword) & "%' OR [tiao] LIKE '%" & SafeString(keyword) & "%')"
    End If
    If status <> "" Then
        whereClause = whereClause & " AND [icha]=" & status
    End If
    
    ' 获取总数
    sql = "SELECT COUNT(*) FROM [data] " & whereClause
    Set rs = conn.Execute(sql)
    Dim totalCount
    totalCount = rs.Fields(0).Value
    rs.Close
    
    ' 分页查询
    sql = "SELECT * FROM [data] " & whereClause & " ORDER BY [id] DESC"
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

' 编辑工资记录
Sub EditSalary()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim id, timu, tiao, path, icha, cha_note
    id = Request.Form("id")
    timu = Request.Form("timu")
    tiao = Request.Form("tiao")
    path = Request.Form("path")
    icha = Request.Form("icha")
    cha_note = Request.Form("cha_note")
    
    If id = "" Or timu = "" Or tiao = "" Or path = "" Then
        ReturnError "必填字段不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 更新工资记录
    sql = "UPDATE [data] SET [timu]='" & SafeString(timu) & "', [tiao]='" & SafeString(tiao) & "', [path]='" & SafeString(path) & "', [icha]=" & icha & ", [cha_note]='" & SafeString(cha_note) & "' WHERE [id]=" & id
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "工资记录更新成功"
    Else
        ReturnError "工资记录更新失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 删除工资记录
Sub DeleteSalary()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim id
    id = Request.Form("id")
    
    If id = "" Then
        ReturnError "记录ID不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 删除工资记录
    sql = "DELETE FROM [data] WHERE [id]=" & id
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "工资记录删除成功"
    Else
        ReturnError "工资记录删除失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 批量删除工资记录
Sub BatchDeleteSalary()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim ids
    ids = Request.Form("ids")
    
    If ids = "" Then
        ReturnError "请选择要删除的记录"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 批量删除工资记录
    sql = "DELETE FROM [data] WHERE [id] IN (" & ids & ")"
    conn.Execute sql
    
    If Err.Number = 0 Then
        ReturnSuccess "批量删除成功"
    Else
        ReturnError "批量删除失败：" & Err.Description
    End If
    
    conn.Close
    Set conn = Nothing
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->