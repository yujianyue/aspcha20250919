<%
' 数据预览页面
' 功能：根据数据路径读取显示工资数据，支持分页和搜索
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "list"
        Call GetDataList()
    Case "view"
        Call ViewData()
    Case Else
        Call ShowViewPage()
End Select

' 显示预览页面
Sub ShowViewPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>数据预览</h3>
    </div>
    <div class="card-body">
        <!-- 搜索表单 -->
        <form id="searchForm" class="mb-3">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchKeyword" name="keyword" placeholder="搜索查询条件">
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="searchData()">搜索</button>
                    <button type="button" class="btn btn-secondary" onclick="resetSearch()">重置</button>
                </div>
            </div>
        </form>
        
        <!-- 数据列表 -->
        <div id="dataList">
            <div class="loading"></div> 正在加载数据列表...
        </div>
        
        <!-- 分页 -->
        <div id="pagination" class="pagination"></div>
    </div>
</div>

<script>
var currentPage = 1;
var pageSize = 10;

// 页面加载时获取数据列表
window.onload = function() {
    loadDataList();
};

function loadDataList(page) {
    if (page) currentPage = page;
    
    var keyword = document.getElementById('searchKeyword').value;
    
    ajaxRequest('?act=list', {
        page: currentPage,
        pageSize: pageSize,
        keyword: keyword
    }, function(response) {
        var listDiv = document.getElementById('dataList');
        if (response.status == 1) {
            var html = '';
            if (response.data && response.data.length > 0) {
                html = '<table class="table">';
                html += '<thead><tr>';
                html += '<th>查询标题</th>';
                html += '<th>查询条件</th>';
                html += '<th>文件路径</th>';
                html += '<th>状态</th>';
                html += '<th>添加时间</th>';
                html += '<th>操作</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                
                for (var i = 0; i < response.data.length; i++) {
                    var item = response.data[i];
                    var statusText = item.icha == '1' ? '<span class="text-success">可查询</span>' : '<span class="text-danger">不可查询</span>';
                    var addTime = item.add_time;
                    if (addTime.length == 8) {
                        addTime = addTime.substring(0,4) + '-' + addTime.substring(4,6) + '-' + addTime.substring(6,8);
                    }
                    
                    html += '<tr>';
                    html += '<td>' + item.timu + '</td>';
                    html += '<td>' + item.tiao + '</td>';
                    html += '<td>' + item.path + '</td>';
                    html += '<td>' + statusText + '</td>';
                    html += '<td>' + addTime + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary" onclick="viewData(' + item.id + ')">预览数据</button>';
                    html += '</td>';
                    html += '</tr>';
                }
                
                html += '</tbody></table>';
                
                // 分页
                if (response.totalPages > 1) {
                    initPagination('pagination', currentPage, response.totalPages, pageSize);
                }
            } else {
                html = '<div class="alert alert-info">暂无数据</div>';
            }
            listDiv.innerHTML = html;
        } else {
            listDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}

function searchData() {
    currentPage = 1;
    loadDataList();
}

function resetSearch() {
    document.getElementById('searchKeyword').value = '';
    currentPage = 1;
    loadDataList();
}

function viewData(id) {
    ajaxRequest('?act=view', {id: id}, function(response) {
        if (response.status == 1) {
            var content = '<h4>数据预览</h4>';
            if (response.data) {
                content += '<div class="table-responsive">';
                content += '<table class="table">';
                content += '<thead><tr><th>项目</th><th>值</th></tr></thead>';
                content += '<tbody>';
                for (var key in response.data) {
                    content += '<tr><td>' + key + '</td><td>' + response.data[key] + '</td></tr>';
                }
                content += '</tbody></table>';
                content += '</div>';
            } else {
                content += '<div class="alert alert-warning">暂无数据内容</div>';
            }
            
            showModal('数据预览', content, '<button class="btn btn-secondary" onclick="hideModal()">关闭</button>');
        } else {
            showMessage(response.msg, 'danger');
        }
    });
}
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 获取数据列表
Sub GetDataList()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim page, pageSize, keyword
    page = Request.Form("page")
    pageSize = Request.Form("pageSize")
    keyword = Request.Form("keyword")
    
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

' 查看数据内容
Sub ViewData()
    On Error Resume Next
    
    ' 检查管理员登录状态
    If Session("usertype") <> "admin" Or Session("username") = "" Then
        ReturnError "请先登录"
        Exit Sub
    End If
    
    Dim id
    id = Request.Form("id")
    
    If id = "" Then
        ReturnError "数据ID不能为空"
        Exit Sub
    End If
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 查询数据记录
    sql = "SELECT * FROM [data] WHERE [id]=" & id
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
            
            ' 解析JSON数据
            Dim data
            data = content
            ReturnJson "{""status"":1,""data"":" & data & "}"
        Else
            ReturnError "数据文件不存在"
        End If
        
        Set fso = Nothing
    Else
        ReturnError "数据记录不存在"
    End If
    
    rs.Close
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->