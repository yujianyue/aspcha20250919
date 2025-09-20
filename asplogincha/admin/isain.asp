<%
' 工资数据导入页面
' 功能：支持XLS文件和TXT方式导入工资数据
' 版权声明：保留发行权和署名权
' 作者：15058593138@qq.com

Response.Charset = "gb2312"
Response.ContentType = "text/html"

Dim act
act = Request.QueryString("act")

Select Case act
    Case "importtxt"
        Call ImportSalaryTxt()
    Case "importxls"
        Call ImportSalaryXls()
    Case Else
        Call ShowImportPage()
End Select

' 显示导入页面
Sub ShowImportPage()
%>
<!--#include file="ihead.asp"-->
<div class="card">
    <div class="card-header">
        <h3>工资数据导入</h3>
    </div>
    <div class="card-body">
        <!-- Tab导航 -->
        <ul class="nav nav-tabs" id="importTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="txt-tab" data-toggle="tab" href="#txt" role="tab">TXT方式</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="xls-tab" data-toggle="tab" href="#xls" role="tab">XLS文件方式</a>
            </li>
        </ul>
        
        <div class="tab-content" id="importTabContent">
            <!-- TXT方式 -->
            <div class="tab-pane fade show active" id="txt" role="tabpanel">
                <div class="mt-3">
                    <div class="alert alert-info">
                        <h4>TXT方式导入说明：</h4>
                        <ol>
                            <li>从Excel中复制数据（包含查询条件、工资数据等列）</li>
                            <li>粘贴到下方文本框中</li>
                            <li>系统会自动解析并保存到数据库</li>
                        </ol>
                    </div>
                    
                    <form id="importTxtForm">
                        <div class="form-group">
                            <label for="salaryData">工资数据（制表符分隔）：</label>
                            <textarea class="form-control" id="salaryData" name="salaryData" rows="10" placeholder="请粘贴Excel数据"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">开始导入</button>
                            <button type="button" class="btn btn-secondary" onclick="clearTxtData()">清空数据</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- XLS文件方式 -->
            <div class="tab-pane fade" id="xls" role="tabpanel">
                <div class="mt-3">
                    <div class="alert alert-info">
                        <h4>XLS文件方式导入说明：</h4>
                        <ol>
                            <li>选择Excel文件（.xls格式）</li>
                            <li>系统会读取文件中的前三行数据</li>
                            <li>第一列作为查询条件，其他列作为工资数据</li>
                        </ol>
                    </div>
                    
                    <form id="importXlsForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="xlsFile">选择Excel文件：</label>
                            <input type="file" class="form-control" id="xlsFile" name="xlsFile" accept=".xls">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">开始导入</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="importResult" class="mt-3"></div>
    </div>
</div>

<style>
.nav-tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
    padding: 0.5rem 1rem;
    color: #495057;
    text-decoration: none;
}

.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    padding: 1rem;
}
</style>

<script>
// Tab切换功能
document.querySelectorAll('[data-toggle="tab"]').forEach(function(tab) {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        
        // 移除所有active类
        document.querySelectorAll('.nav-link').forEach(function(link) {
            link.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });
        
        // 添加active类到当前tab
        this.classList.add('active');
        var target = document.querySelector(this.getAttribute('href'));
        target.classList.add('show', 'active');
    });
});

// TXT方式导入
document.getElementById('importTxtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var salaryData = document.getElementById('salaryData').value.trim();
    if (!salaryData) {
        showMessage('请输入要导入的工资数据', 'warning');
        return;
    }
    
    var resultDiv = document.getElementById('importResult');
    resultDiv.innerHTML = '<div class="loading"></div> 正在导入工资数据...';
    
    ajaxRequest('?act=importtxt', {
        salaryData: salaryData
    }, function(response) {
        if (response.status == 1) {
            resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
            if (response.data) {
                var html = '<div class="mt-3">';
                html += '<h5>导入结果：</h5>';
                html += '<ul>';
                html += '<li>成功导入：' + response.data.success + ' 条</li>';
                html += '<li>失败：' + response.data.failed + ' 条</li>';
                if (response.data.errors && response.data.errors.length > 0) {
                    html += '<li>错误信息：</li>';
                    html += '<ul>';
                    for (var i = 0; i < response.data.errors.length; i++) {
                        html += '<li class="text-danger">' + response.data.errors[i] + '</li>';
                    }
                    html += '</ul>';
                }
                html += '</ul></div>';
                resultDiv.innerHTML += html;
            }
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
        }
    });
});

// XLS文件方式导入
document.getElementById('importXlsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var fileInput = document.getElementById('xlsFile');
    if (!fileInput.files[0]) {
        showMessage('请选择Excel文件', 'warning');
        return;
    }
    
    var formData = new FormData();
    formData.append('xlsFile', fileInput.files[0]);
    
    var resultDiv = document.getElementById('importResult');
    resultDiv.innerHTML = '<div class="loading"></div> 正在导入Excel文件...';
    
    // 使用XMLHttpRequest上传文件
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '?act=importxls', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status == 1) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + response.msg + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + response.msg + '</div>';
                    }
                } catch (e) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">文件上传失败</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger">文件上传失败</div>';
            }
        }
    };
    xhr.send(formData);
});

function clearTxtData() {
    document.getElementById('salaryData').value = '';
    document.getElementById('importResult').innerHTML = '';
}
</script>
<!--#include file="ifoot.asp"-->
<%
End Sub

' 导入TXT工资数据
Sub ImportSalaryTxt()
    On Error Resume Next
    
    Dim salaryData, lines, i, line, fields, tiao, dataContent
    salaryData = Request.Form("salaryData")
    
    If salaryData = "" Then
        ReturnError "请输入要导入的工资数据"
        Exit Sub
    End If
    
    ' 分割数据行
    lines = Split(salaryData, vbCrLf)
    
    Dim successCount, failedCount, errors()
    successCount = 0
    failedCount = 0
    ReDim errors(0)
    
    ' 连接数据库
    Dim conn, rs, sql
    Set conn = Server.CreateObject("ADODB.Connection")
    conn.Open "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" & Server.MapPath("../data.mdb")
    
    ' 处理每一行数据
    For i = 0 To UBound(lines)
        line = Trim(lines(i))
        If line <> "" Then
            ' 分割字段（制表符分隔）
            fields = Split(line, vbTab)
            
            If UBound(fields) >= 1 Then
                tiao = Trim(fields(0)) ' 第一列作为查询条件
                
                ' 构建JSON格式的工资数据
                Dim jsonData, j
                jsonData = "{"
                For j = 1 To UBound(fields)
                    If j > 1 Then jsonData = jsonData & ","
                    jsonData = jsonData & """field" & j & """:""" & Replace(Trim(fields(j)), """", "\""") & """"
                Next
                jsonData = jsonData & "}"
                
                ' 生成文件名
                Dim fileName, filePath
                fileName = "salary_" & Format(Now(), "yyyymmddhhmmss") & "_" & i & ".txt"
                filePath = Server.MapPath("../data/" & fileName)
                
                ' 创建data文件夹
                Dim fso
                Set fso = Server.CreateObject("Scripting.FileSystemObject")
                If Not fso.FolderExists(Server.MapPath("../data")) Then
                    fso.CreateFolder(Server.MapPath("../data"))
                End If
                
                ' 保存数据文件
                Dim file
                Set file = fso.CreateTextFile(filePath, True)
                file.Write jsonData
                file.Close
                Set file = Nothing
                Set fso = Nothing
                
                ' 插入数据库记录
                sql = "INSERT INTO [data] ([timu], [tiao], [path], [icha], [add_time], [cha_note]) VALUES ('工资数据', '" & SafeString(tiao) & "', 'data/" & fileName & "', 1, '" & Format(Now(), "yyyymmdd") & "', 'TXT导入')"
                conn.Execute sql
                
                If Err.Number = 0 Then
                    successCount = successCount + 1
                Else
                    failedCount = failedCount + 1
                    AddError errors, "第" & (i+1) & "行：" & Err.Description
                End If
            Else
                failedCount = failedCount + 1
                AddError errors, "第" & (i+1) & "行：数据列数不足"
            End If
        End If
    Next
    
    conn.Close
    Set rs = Nothing
    Set conn = Nothing
    
    Dim result
    result = "{""success"":" & successCount & ",""failed"":" & failedCount & ",""errors"":" & ArrayToJson(errors) & "}"
    ReturnJson "{""status"":1,""msg"":""TXT导入完成"",""data"":" & result & "}"
End Sub

' 导入XLS工资数据
Sub ImportSalaryXls()
    On Error Resume Next
    
    ' 这里需要处理文件上传，由于ASP的限制，简化处理
    ReturnError "XLS文件导入功能需要服务器支持文件上传，请使用TXT方式导入"
End Sub

' 添加错误信息
Sub AddError(errors, errorMsg)
    Dim newSize
    newSize = UBound(errors) + 1
    ReDim Preserve errors(newSize)
    errors(newSize) = errorMsg
End Sub

' 包含公共函数
%>
<!--#include file="../inc/pubs.asp"-->