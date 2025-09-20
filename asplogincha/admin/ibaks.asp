<%
' 数据维护页面
' 功能：备份数据库、显示历史记录
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "backup"
        Call BackupDatabase()
    Case "list"
        Call GetBackupList()
    Case "restore"
        Call RestoreDatabase()
    Case Else
        Call ShowBackupPage()
End Select

' 显示备份页面
Sub ShowBackupPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>数据维护</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>数据库备份</h4>
                    </div>
                    <div class="card-body">
                        <p>备份当前数据库并压缩原数据库文件</p>
                        <button class="btn btn-primary" onclick="backupDatabase()">立即备份</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>备份历史</h4>
                    </div>
                    <div class="card-body">
                        <div id="backupList">
                            <div class="loading"></div> 正在加载备份列表...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="backupResult" class="mt-3"></div>
    </div>
</div>

<script>
// 页面加载时获取备份列表
window.onload = function() {
    loadBackupList();
};

function loadBackupList() {
    ajaxRequest('?act=list', {}, function(response) {
        var listDiv = document.getElementById('backupList');
        if (response.status == 1) {
            var html = '';
            if (response.data && response.data.length > 0) {
                html = '<table class="table table-sm">';
                html += '<thead><tr><th>文件名</th><th>大小</th><th>创建时间</th><th>操作</th></tr></thead>';
                html += '<tbody>';
                
                for (var i = 0; i < response.data.length; i++) {
                    var item = response.data[i];
                    html += '<tr>';
                    html += '<td>' + item.name + '</td>';
                    html += '<td>' + item.size + '</td>';
                    html += '<td>' + item.date + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-info" onclick="downloadBackup(\'' + item.name + '\')">下载</button> ';
                    html += '<button class="btn btn-sm btn-warning" onclick="restoreBackup(\'' + item.name + '\')">恢复</button>';
                    html += '</td>';
                    html += '</tr>';
                }
                
                html += '</tbody></table>';
            } else {
                html = '<div class="alert alert-info">暂无备份文件</div>';
            }
            listDiv.innerHTML = html;
        } else {
            listDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}

function backupDatabase() {
    var resultDiv = document.getElementById('backupResult');
    resultDiv.innerHTML = '<div class="loading"></div> 正在备份数据库...';
    
    ajaxRequest('?act=backup', {}, function(response) {
        if (response.status == 1) {
            resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
            loadBackupList(); // 刷新备份列表
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
}

function downloadBackup(fileName) {
    window.open('backup/' + fileName, '_blank');
}

function restoreBackup(fileName) {
    if (confirm('确定要恢复备份文件 ' + fileName + ' 吗？这将覆盖当前数据库！')) {
        ajaxRequest('?act=restore', {fileName: fileName}, function(response) {
            if (response.status == 1) {
                showMessage('数据库恢复成功', 'success');
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

' 备份数据库
Sub BackupDatabase()
    On Error Resume Next
    
    ' 创建备份文件夹
    Dim backupPath, fso
    backupPath = Server.MapPath("backup")
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    If Not fso.FolderExists(backupPath) Then
        fso.CreateFolder(backupPath)
    End If
    
    ' 生成备份文件名
    Dim backupFileName, sourceFile, targetFile
    backupFileName = "backup_" & Format(Now(), "yyyymmddhhmmss") & ".mdb"
    sourceFile = Server.MapPath("../data.mdb")
    targetFile = backupPath & "\" & backupFileName
    
    ' 复制数据库文件
    If fso.FileExists(sourceFile) Then
        fso.CopyFile sourceFile, targetFile
        
        If Err.Number = 0 Then
            ' 压缩原数据库文件（这里简化处理，实际项目中可以使用压缩组件）
            ReturnSuccess "数据库备份成功，备份文件：" & backupFileName
        Else
            ReturnError "数据库备份失败：" & Err.Description
        End If
    Else
        ReturnError "源数据库文件不存在"
    End If
    
    Set fso = Nothing
End Sub

' 获取备份列表
Sub GetBackupList()
    On Error Resume Next
    
    Dim backupPath, fso, folder, file, files()
    backupPath = Server.MapPath("backup")
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    If fso.FolderExists(backupPath) Then
        Set folder = fso.GetFolder(backupPath)
        
        Dim i
        i = 0
        ReDim files(0)
        
        For Each file In folder.Files
            If LCase(Right(file.Name, 4)) = ".mdb" Then
                ReDim Preserve files(i)
                files(i) = Array(file.Name, FormatNumber(file.Size / 1024, 2) & " KB", file.DateCreated)
                i = i + 1
            End If
        Next
        
        ' 按时间降序排序
        Call SortFilesByDate(files)
        
        Dim result
        result = "["
        For i = 0 To UBound(files)
            If i > 0 Then result = result & ","
            result = result & "{""name"":""" & files(i)(0) & """,""size"":""" & files(i)(1) & """,""date"":""" & files(i)(2) & """}"
        Next
        result = result & "]"
        
        ReturnJson "{""status"":1,""data"":" & result & "}"
    Else
        ReturnJson "{""status"":1,""data"":[]}"
    End If
    
    Set fso = Nothing
End Sub

' 恢复数据库
Sub RestoreDatabase()
    On Error Resume Next
    
    Dim fileName, backupPath, sourceFile, targetFile, fso
    fileName = Request.Form("fileName")
    
    If fileName = "" Then
        ReturnError "请选择要恢复的备份文件"
        Exit Sub
    End If
    
    backupPath = Server.MapPath("backup")
    sourceFile = backupPath & "\" & fileName
    targetFile = Server.MapPath("../data.mdb")
    
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    
    If fso.FileExists(sourceFile) Then
        ' 备份当前数据库
        Dim currentBackup
        currentBackup = Server.MapPath("../data_backup_" & Format(Now(), "yyyymmddhhmmss") & ".mdb")
        If fso.FileExists(targetFile) Then
            fso.CopyFile targetFile, currentBackup
        End If
        
        ' 恢复备份文件
        fso.CopyFile sourceFile, targetFile
        
        If Err.Number = 0 Then
            ReturnSuccess "数据库恢复成功"
        Else
            ReturnError "数据库恢复失败：" & Err.Description
        End If
    Else
        ReturnError "备份文件不存在"
    End If
    
    Set fso = Nothing
End Sub

' 按日期排序文件数组
Sub SortFilesByDate(files)
    Dim i, j, temp
    For i = 0 To UBound(files) - 1
        For j = i + 1 To UBound(files)
            If files(i)(2) < files(j)(2) Then
                temp = files(i)
                files(i) = files(j)
                files(j) = temp
            End If
        Next
    Next
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->