<%
' 管理员首页
' 功能：显示系统概览信息
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "stats"
        Call GetStats()
    Case "logout"
        Call AdminLogout()
    Case Else
        Call ShowAdminPage()
End Select

' 显示管理员页面
Sub ShowAdminPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>系统概览</h3>
    </div>
    <div class="card-body">
        <div class="row" id="statsContainer">
            <div class="loading"></div> 正在加载统计数据...
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>快速操作</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="iuser.asp" class="btn btn-primary w-100">用户管理</a>
            </div>
            <div class="col-md-3">
                <a href="isala.asp" class="btn btn-success w-100">工资管理</a>
            </div>
            <div class="col-md-3">
                <a href="iusin.asp" class="btn btn-info w-100">用户导入</a>
            </div>
            <div class="col-md-3">
                <a href="isain.asp" class="btn btn-warning w-100">工资导入</a>
            </div>
        </div>
    </div>
</div>

<script>
// 页面加载时获取统计数据
window.onload = function() {
    loadStats();
};

function loadStats() {
    ajaxRequest('?act=stats', {}, function(response) {
        var container = document.getElementById('statsContainer');
        if (response.status == 1) {
            var html = '';
            html += '<div class="col-md-3">';
            html += '<div class="card text-center">';
            html += '<div class="card-body">';
            html += '<h4 class="text-primary">' + response.data.userCount + '</h4>';
            html += '<p>用户总数</p>';
            html += '</div></div></div>';
            
            html += '<div class="col-md-3">';
            html += '<div class="card text-center">';
            html += '<div class="card-body">';
            html += '<h4 class="text-success">' + response.data.salaryCount + '</h4>';
            html += '<p>工资记录</p>';
            html += '</div></div></div>';
            
            html += '<div class="col-md-3">';
            html += '<div class="card text-center">';
            html += '<div class="card-body">';
            html += '<h4 class="text-info">' + response.data.activeCount + '</h4>';
            html += '<p>可查询记录</p>';
            html += '</div></div></div>';
            
            html += '<div class="col-md-3">';
            html += '<div class="card text-center">';
            html += '<div class="card-body">';
            html += '<h4 class="text-warning">' + response.data.todayCount + '</h4>';
            html += '<p>今日新增</p>';
            html += '</div></div></div>';
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 获取统计数据
Sub GetStats()
    On Error Resume Next
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    Dim userCount, salaryCount, activeCount, todayCount
    
    ' 用户总数
    sql = "SELECT COUNT(*) FROM [user] WHERE [usertype]='user'"
    Set rs = conn.Execute(sql)
    userCount = rs.Fields(0).Value
    rs.Close
    
    ' 工资记录总数
    sql = "SELECT COUNT(*) FROM [data]"
    Set rs = conn.Execute(sql)
    salaryCount = rs.Fields(0).Value
    rs.Close
    
    ' 可查询记录数
    sql = "SELECT COUNT(*) FROM [data] WHERE [icha]=1"
    Set rs = conn.Execute(sql)
    activeCount = rs.Fields(0).Value
    rs.Close
    
    ' 今日新增
    sql = "SELECT COUNT(*) FROM [data] WHERE [add_time]='" & Format(Now(), "yyyymmdd") & "'"
    Set rs = conn.Execute(sql)
    todayCount = rs.Fields(0).Value
    rs.Close
    
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    Dim result
    result = "{""userCount"":" & userCount & ",""salaryCount"":" & salaryCount & ",""activeCount"":" & activeCount & ",""todayCount"":" & todayCount & "}"
    ReturnJson "{""status"":1,""data"":" & result & "}"
End Sub

' 管理员退出
Sub AdminLogout()
    Session.Abandon
    ReturnSuccess "已退出登录"
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->