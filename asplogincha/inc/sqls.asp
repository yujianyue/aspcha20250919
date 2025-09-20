<%
' 数据库操作类
' 功能：提供数据库增删改查操作
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Class Database
    Private conn, rs
    
    ' 构造函数
    Private Sub Class_Initialize()
        Set conn = Server.CreateObject("ADODB.Connection")
        Set rs = Server.CreateObject("ADODB.RecordSet")
    End Sub
    
    ' 析构函数
    Private Sub Class_Terminate()
        If Not rs Is Nothing Then
            rs.Close
            Set rs = Nothing
        End If
        If Not conn Is Nothing Then
            If conn.State = 1 Then conn.Close
            Set conn = Nothing
        End If
    End Sub
    
    ' 连接数据库
    Public Function Connect()
        On Error Resume Next
        conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("data.mdb")
        If Err.Number <> 0 Then
            Connect = False
        Else
            Connect = True
        End If
    End Function
    
    ' 执行SQL查询
    Public Function Query(sql)
        On Error Resume Next
        Set rs = conn.Execute(sql)
        If Err.Number <> 0 Then
            Query = False
        Else
            Query = True
        End If
    End Function
    
    ' 执行SQL命令
    Public Function Execute(sql)
        On Error Resume Next
        conn.Execute sql
        If Err.Number <> 0 Then
            Execute = False
        Else
            Execute = True
        End If
    End Function
    
    ' 获取记录集
    Public Function GetRecordSet()
        Set GetRecordSet = rs
    End Function
    
    ' 获取单条记录
    Public Function GetOne(sql)
        If Query(sql) And Not rs.EOF Then
            Set GetOne = rs
        Else
            Set GetOne = Nothing
        End If
    End Function
    
    ' 获取记录数
    Public Function GetCount(sql)
        If Query(sql) Then
            GetCount = rs.RecordCount
        Else
            GetCount = 0
        End If
    End Function
    
    ' 插入记录
    Public Function Insert(table, fields, values)
        Dim sql, i
        sql = "INSERT INTO " & table & " (" & fields & ") VALUES (" & values & ")"
        Insert = Execute(sql)
    End Function
    
    ' 更新记录
    Public Function Update(table, setClause, whereClause)
        Dim sql
        sql = "UPDATE " & table & " SET " & setClause
        If whereClause <> "" Then
            sql = sql & " WHERE " & whereClause
        End If
        Update = Execute(sql)
    End Function
    
    ' 删除记录
    Public Function Delete(table, whereClause)
        Dim sql
        sql = "DELETE FROM " & table
        If whereClause <> "" Then
            sql = sql & " WHERE " & whereClause
        End If
        Delete = Execute(sql)
    End Function
    
    ' 检查记录是否存在
    Public Function Exists(table, whereClause)
        Dim sql
        sql = "SELECT COUNT(*) FROM " & table
        If whereClause <> "" Then
            sql = sql & " WHERE " & whereClause
        End If
        If Query(sql) And Not rs.EOF Then
            Exists = (rs.Fields(0).Value > 0)
        Else
            Exists = False
        End If
    End Function
    
    ' 获取分页数据
    Public Function GetPageData(sql, page, pageSize)
        Dim startRow, endRow
        startRow = (page - 1) * pageSize + 1
        endRow = page * pageSize
        
        sql = "SELECT * FROM (" & sql & ") WHERE RowNum BETWEEN " & startRow & " AND " & endRow
        GetPageData = Query(sql)
    End Function
    
    ' 关闭连接
    Public Sub Close()
        If Not rs Is Nothing Then
            rs.Close
            Set rs = Nothing
        End If
        If Not conn Is Nothing Then
            If conn.State = 1 Then conn.Close
            Set conn = Nothing
        End If
    End Sub
End Class
%>