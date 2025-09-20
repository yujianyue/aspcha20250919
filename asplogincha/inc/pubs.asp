<%
' 公共函数库
' 功能：提供通用ASP函数
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

' 数组转JSON函数
Function ArrayToJson(arr)
    Dim i, result
    result = "["
    For i = 0 To UBound(arr)
        If i > 0 Then result = result & ","
        result = result & """" & Replace(arr(i), """", "\""") & """"
    Next
    result = result & "]"
    ArrayToJson = result
End Function

' 记录集转JSON
Function RecordSetToJson(rs)
    Dim result, i, field
    result = "["
    
    If Not rs.EOF Then
        Do While Not rs.EOF
            If result <> "[" Then result = result & ","
            result = result & "{"
            
            For i = 0 To rs.Fields.Count - 1
                If i > 0 Then result = result & ","
                field = rs.Fields(i).Name
                result = result & """" & field & """:"
                If IsNull(rs.Fields(i).Value) Then
                    result = result & "null"
                Else
                    result = result & """" & Replace(rs.Fields(i).Value, """", "\""") & """"
                End If
            Next
            
            result = result & "}"
            rs.MoveNext
        Loop
    End If
    
    result = result & "]"
    RecordSetToJson = result
End Function

' 安全过滤函数
Function SafeString(str)
    If IsNull(str) Or str = "" Then
        SafeString = ""
    Else
        str = Replace(str, "'", "''")
        str = Replace(str, "<", "&lt;")
        str = Replace(str, ">", "&gt;")
        str = Replace(str, """", "&quot;")
        str = Replace(str, "&", "&amp;")
        SafeString = str
    End If
End Function

' 检查文件是否存在
Function FileExists(filePath)
    Dim fso
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    FileExists = fso.FileExists(filePath)
    Set fso = Nothing
End Function

' 检查文件夹是否存在
Function FolderExists(folderPath)
    Dim fso
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    FolderExists = fso.FolderExists(folderPath)
    Set fso = Nothing
End Function

' 创建文件夹
Function CreateFolder(folderPath)
    Dim fso
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    If Not fso.FolderExists(folderPath) Then
        fso.CreateFolder(folderPath)
        CreateFolder = True
    Else
        CreateFolder = False
    End If
    Set fso = Nothing
End Function

' 创建文件
Function CreateFile(filePath, content)
    Dim fso, file
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    Set file = fso.CreateTextFile(filePath, True)
    file.Write content
    file.Close
    Set file = Nothing
    Set fso = Nothing
    CreateFile = True
End Function

' MD5加密函数
Function MD5(str)
    ' 简单的MD5实现，实际项目中建议使用更安全的加密方式
    Dim i, result
    result = ""
    For i = 1 To Len(str)
        result = result & Right("0" & Hex(Asc(Mid(str, i, 1))), 2)
    Next
    MD5 = LCase(result)
End Function

' 生成随机字符串
Function RandomString(length)
    Dim chars, result, i
    chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
    result = ""
    Randomize
    For i = 1 To length
        result = result & Mid(chars, Int(Rnd() * Len(chars)) + 1, 1)
    Next
    RandomString = result
End Function

' 返回JSON响应
Sub ReturnJson(jsonStr)
    Response.ContentType = "application/json; charset=gb2312"
    Response.Write jsonStr
    Response.End
End Sub

' 返回成功消息
Sub ReturnSuccess(msg)
    Dim result
    result = "{""status"":1,""msg"":""" & msg & """}"
    ReturnJson result
End Sub

' 返回错误消息
Sub ReturnError(msg)
    Dim result
    result = "{""status"":0,""msg"":""" & msg & """}"
    ReturnJson result
End Sub

' 分页计算
Function GetPageInfo(currentPage, pageSize, totalCount)
    Dim totalPages, startRow, endRow
    totalPages = Int((totalCount + pageSize - 1) / pageSize)
    If currentPage < 1 Then currentPage = 1
    If currentPage > totalPages Then currentPage = totalPages
    startRow = (currentPage - 1) * pageSize + 1
    endRow = currentPage * pageSize
    If endRow > totalCount Then endRow = totalCount
    
    GetPageInfo = Array(currentPage, totalPages, startRow, endRow)
End Function
%>