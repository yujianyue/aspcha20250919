<%
' 数据库连接配置文件
' 功能：数据库连接参数配置
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

' 数据库相对路径
Dim db_path
db_path = Server.MapPath("data.mdb")

' 数据库连接字符串
Dim conn_str
conn_str = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & db_path

' 创建数据库连接对象
Dim conn
Set conn = Server.CreateObject("ADODB.Connection")

' 数据库连接函数
Function OpenConn()
    On Error Resume Next
    conn.Open conn_str
    If Err.Number <> 0 Then
        Response.Write "数据库连接失败：" & Err.Description
        Response.End
    End If
End Function

' 关闭数据库连接
Function CloseConn()
    If conn.State = 1 Then
        conn.Close
    End If
    Set conn = Nothing
End Function

' 网站设置缓存文件路径
Dim cache_file
cache_file = Server.MapPath("inc/json.dat")

' 读取网站设置缓存
Function GetSiteConfig()
    Dim fso, file, content
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    If fso.FileExists(cache_file) Then
        Set file = fso.OpenTextFile(cache_file, 1, False)
        content = file.ReadAll
        file.Close
        Set file = Nothing
        GetSiteConfig = content
    Else
        GetSiteConfig = ""
    End If
    
    Set fso = Nothing
End Function

' 写入网站设置缓存
Function SetSiteConfig(jsonStr)
    Dim fso, file
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    Set file = fso.CreateTextFile(cache_file, True)
    file.Write jsonStr
    file.Close
    Set file = Nothing
    Set fso = Nothing
End Function
%>