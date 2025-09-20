<%
' 主页面
' 功能：根据登录账号类型跳转，默认用户登录首页
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

' 检查是否已登录
Dim userType, username
userType = Session("usertype")
username = Session("username")

If userType = "" Or username = "" Then
    ' 未登录，跳转到用户登录页面
    Response.Redirect "user/index.asp"
ElseIf userType = "admin" Then
    ' 管理员登录，跳转到管理后台
    Response.Redirect "admin/index.asp"
Else
    ' 普通用户登录，跳转到用户首页
    Response.Redirect "user/index.asp"
End If
%>