<%
' 管理员公共头部
' 功能：导航栏、公共CSS/JS引用
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

' 检查管理员登录状态
If Session("usertype") <> "admin" Or Session("username") = "" Then
    Response.Redirect "login.asp"
End If
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><%=Request.QueryString("title")%> - 管理后台</title>
    <link rel="stylesheet" href="../inc/css.css">
</head>
<body>
    <div class="header">
        <h1>通用工资查询系统 - 管理后台</h1>
        <p>欢迎，<%=Session("username")%> | <a href="?act=logout" style="color: white;">退出登录</a></p>
    </div>
    
    <div class="navbar">
        <ul>
            <li><a href="index.asp">首页</a></li>
            <li><a href="iuser.asp">用户管理</a></li>
            <li><a href="isala.asp">工资管理</a></li>
            <li><a href="iusin.asp">用户导入</a></li>
            <li><a href="isain.asp">工资导入</a></li>
            <li><a href="iview.asp">数据预览</a></li>
            <li><a href="ibaks.asp">数据维护</a></li>
            <li><a href="idubi.asp">辅助工具</a></li>
        </ul>
    </div>
    
    <div class="container">