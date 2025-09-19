<%@LANGUAGE="VBSCRIPT" CODEPAGE="65001"%>
<%
Response.Charset = "UTF-8"
Response.ContentType = "text/html; charset=UTF-8"

' 配置参数
Dim title, copyr, jscss, ismas, baoha, itiao, ihide, isurl, isimg, dbdir, dbxls, copyu, pagex, pagem
title = "多输入框都输对精准查询系统(万用查分)"
copyr = "查立得-"
jscss = "V20210909"
ismas = "1"
baoha = "1"
itiao = "||姓名||学号||工号||产品名称||产品编号||"
ihide = "||密码||身份证||身份证号||身份证号码||"
isurl = "||购买地址||先领优惠券||领券地址||网址的列标题||优惠券地址||"
isimg = "||商品主图||产品图片||"
dbdir = "./shujuku/"
dbxls = ".xls.php"
copyu = "/"
pagex = "10"
pagem = "20"

' 通用函数
Function JSONEncode(str)
    If IsNull(str) Or str = "" Then
        JSONEncode = """"
    Else
        str = Replace(str, "\", "\\")
        str = Replace(str, """", "\""")
        str = Replace(str, vbCrLf, "\n")
        str = Replace(str, vbCr, "\r")
        str = Replace(str, vbLf, "\n")
        str = Replace(str, vbTab, "\t")
        JSONEncode = """" & str & """"
    End If
End Function

Function JSONResponse(data)
    Response.ContentType = "application/json; charset=UTF-8"
    Response.Write data
    Response.End
End Function

Function GetFileList()
    Dim fso, folder, files, file, fileList, fileName, filePath, content, lines, fields, i, j, fieldCount, queryFields, fieldName
    Set fso = CreateObject("Scripting.FileSystemObject")
    
    If Not fso.FolderExists(Server.MapPath(dbdir)) Then
        JSONResponse "{""error"":""数据库文件夹不存在""}"
    End If
    
    Set folder = fso.GetFolder(Server.MapPath(dbdir))
    Set files = folder.Files
    
    fileList = "["
    fieldCount = 0
    
    For Each file In files
        If Right(LCase(file.Name), Len(dbxls)) = LCase(dbxls) Then
            fileName = Left(file.Name, Len(file.Name) - Len(dbxls))
            filePath = dbdir & file.Name
            
            ' 读取文件第三行获取字段信息
            If fso.FileExists(Server.MapPath(filePath)) Then
                Set content = fso.OpenTextFile(Server.MapPath(filePath), 1, False, 0)
                Dim fileContent
                fileContent = content.ReadAll
                content.Close
                
                ' 跳过PHP代码部分，找到TSV数据
                Dim dataStart
                dataStart = InStr(fileContent, "?>")
                If dataStart > 0 Then
                    fileContent = Mid(fileContent, dataStart + 2)
                End If
                
                lines = Split(fileContent, vbCrLf)
                
                ' 找到第一个非空行作为字段标题
                Dim headerLine
                For i = 0 To UBound(lines)
                    If Trim(lines(i)) <> "" Then
                        headerLine = i
                        Exit For
                    End If
                Next
                
                If headerLine >= 0 And UBound(lines) >= headerLine Then
                    fields = Split(lines(headerLine), vbTab)
                    queryFields = ""
                    
                    For i = 0 To UBound(fields)
                        fieldName = Trim(fields(i))
                        If InStr(itiao, "||" & fieldName & "||") > 0 Then
                            If queryFields <> "" Then queryFields = queryFields & ","
                            queryFields = queryFields & JSONEncode(fieldName)
                        End If
                    Next
                    
                    If queryFields <> "" Then
                        If fileList <> "[" Then fileList = fileList & ","
                        fileList = fileList & "{""name"":" & JSONEncode(fileName) & ",""fields"":[" & queryFields & "]}"
                        fieldCount = fieldCount + 1
                    End If
                End If
            End If
        End If
    Next
    
    fileList = fileList & "]"
    JSONResponse "{""files"":" & fileList & "}"
End Function

Function GenerateCaptcha()
    Dim captcha, i, chars
    chars = "0123456789"
    captcha = ""
    For i = 1 To 4
        captcha = captcha & Mid(chars, Int(Rnd() * Len(chars)) + 1, 1)
    Next
    Session("captcha") = captcha
    JSONResponse "{""captcha"":" & JSONEncode(captcha) & "}"
End Function

Function ValidateCaptcha(inputCaptcha)
    If Session("captcha") = inputCaptcha Then
        ValidateCaptcha = True
    Else
        ValidateCaptcha = False
    End If
End Function

Function SearchData()
    Dim fileName, filePath, fso, content, lines, fields, queryFields, i, j, k, fieldIndex, queryValue, found, results, resultCount, page, pageSize, startRow, endRow, totalPages
    Dim fieldNames, fieldValues, isHidden, isUrl, isImg, displayValue, resultJson, memoryStart, memoryEnd, timeStart, timeEnd
    
    fileName = Request.Form("fileName")
    If fileName = "" Then
        JSONResponse "{""error"":""请选择数据文件""}"
    End If
    
    filePath = dbdir & fileName & dbxls
    Set fso = CreateObject("Scripting.FileSystemObject")
    
    If Not fso.FileExists(Server.MapPath(filePath)) Then
        JSONResponse "{""error"":""数据文件不存在""}"
    End If
    
    ' 验证码检查
    If ismas = "1" Then
        If Not ValidateCaptcha(Request.Form("captcha")) Then
            JSONResponse "{""error"":""验证码错误""}"
        End If
    End If
    
    ' 读取文件
    Set content = fso.OpenTextFile(Server.MapPath(filePath), 1, False, 0)
    Dim fileContent
    fileContent = content.ReadAll
    content.Close
    
    ' 跳过PHP代码部分，找到TSV数据
    Dim dataStart
    dataStart = InStr(fileContent, "?>")
    If dataStart > 0 Then
        fileContent = Mid(fileContent, dataStart + 2)
    End If
    
    lines = Split(fileContent, vbCrLf)
    
    ' 找到第一个非空行作为字段标题
    Dim headerLine
    For i = 0 To UBound(lines)
        If Trim(lines(i)) <> "" Then
            headerLine = i
            Exit For
        End If
    Next
    
    If headerLine < 0 Or UBound(lines) < headerLine Then
        JSONResponse "{""error"":""数据文件格式错误""}"
    End If
    
    ' 获取字段信息
    fields = Split(lines(headerLine), vbTab)
    queryFields = ""
    fieldIndex = 0
    
    For i = 0 To UBound(fields)
        fieldName = Trim(fields(i))
        If InStr(itiao, "||" & fieldName & "||") > 0 Then
            If queryFields <> "" Then queryFields = queryFields & ","
            queryFields = queryFields & JSONEncode(fieldName)
            fieldIndex = fieldIndex + 1
        End If
    Next
    
    ' 检查查询条件
    Dim missingFields
    missingFields = ""
    For i = 0 To UBound(fields)
        fieldName = Trim(fields(i))
        If InStr(itiao, "||" & fieldName & "||") > 0 Then
            queryValue = Request.Form("field_" & i)
            If queryValue = "" Then
                If missingFields <> "" Then missingFields = missingFields & ","
                missingFields = missingFields & fieldName
            End If
        End If
    Next
    
    If missingFields <> "" Then
        JSONResponse "{""error"":""请填写查询条件: " & missingFields & """}"
    End If
    
    ' 开始查询
    timeStart = Timer()
    memoryStart = GetMemoryUsage()
    
    results = "["
    resultCount = 0
    
    For i = headerLine + 1 To UBound(lines)
        If Trim(lines(i)) <> "" Then
            fieldValues = Split(lines(i), vbTab)
            found = True
            
            ' 检查所有查询条件
            For j = 0 To UBound(fields)
                fieldName = Trim(fields(j))
                If InStr(itiao, "||" & fieldName & "||") > 0 Then
                    queryValue = Request.Form("field_" & j)
                    If j <= UBound(fieldValues) Then
                        If InStr(LCase(fieldValues(j)), LCase(queryValue)) = 0 Then
                            found = False
                            Exit For
                        End If
                    Else
                        found = False
                        Exit For
                    End If
                End If
            Next
            
            If found Then
                If resultCount > 0 Then results = results & ","
                results = results & "{"
                
                For k = 0 To UBound(fields)
                    fieldName = Trim(fields(k))
                    If k <= UBound(fieldValues) Then
                        displayValue = fieldValues(k)
                        
                        ' 检查是否为隐藏字段
                        If InStr(ihide, "||" & fieldName & "||") > 0 Then
                            displayValue = "***"
                        End If
                        
                        ' 检查是否为URL字段
                        If InStr(isurl, "||" & fieldName & "||") > 0 Then
                            displayValue = "<a href='#' onclick='showUrl(""" & JSONEncode(displayValue) & """)'>" & displayValue & "</a>"
                        End If
                        
                        ' 检查是否为图片字段
                        If InStr(isimg, "||" & fieldName & "||") > 0 Then
                            displayValue = "<img src='" & displayValue & "' onclick='showImage(""" & displayValue & """)' style='max-width:100px;max-height:100px;cursor:pointer;'>"
                        End If
                        
                        If k > 0 Then results = results & ","
                        results = results & JSONEncode(fieldName) & ":" & JSONEncode(displayValue)
                    End If
                Next
                
                results = results & "}"
                resultCount = resultCount + 1
            End If
        End If
    Next
    
    results = results & "]"
    
    timeEnd = Timer()
    memoryEnd = GetMemoryUsage()
    
    ' 分页处理
    page = CInt(Request.Form("page"))
    If page < 1 Then page = 1
    pageSize = CInt(pagex)
    startRow = (page - 1) * pageSize
    endRow = startRow + pageSize - 1
    
    If resultCount > pageSize Then
        totalPages = Int(resultCount / pageSize) + 1
        If resultCount Mod pageSize = 0 Then totalPages = totalPages - 1
    Else
        totalPages = 1
    End If
    
    resultJson = "{""results"":" & results & ",""total"":" & resultCount & ",""page"":" & page & ",""totalPages"":" & totalPages & ",""time"":" & (timeEnd - timeStart) & ",""memory"":" & (memoryEnd - memoryStart) & "}"
    
    JSONResponse resultJson
End Function

Function GetMemoryUsage()
    ' 简化的内存使用计算
    GetMemoryUsage = 0
End Function

' 主程序逻辑
Dim action
action = Request.QueryString("do")

Select Case action
    Case "list"
        GetFileList()
    Case "code"
        GenerateCaptcha()
    Case "cha"
        SearchData()
    Case Else
        ' 显示主页面
%>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><%=title%></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .content {
            padding: 40px;
        }
        
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: end;
            margin-bottom: 30px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #4facfe;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .captcha-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .captcha-image {
            width: 100px;
            height: 40px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .results-container {
            display: none;
            margin-top: 30px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .view-toggle {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .view-btn.active {
            background: #4facfe;
            color: white;
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination a:hover {
            background: #4facfe;
            color: white;
        }
        
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
        }
        
        .modal img {
            max-width: 100%;
            max-height: 80vh;
        }
        
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 30px;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><%=title%></h1>
        </div>
        
        <div class="content">
            <form id="searchForm">
                <div class="form-container">
                    <div class="form-group">
                        <label for="fileName">选择数据文件:</label>
                        <select id="fileName" name="fileName" required>
                            <option value="">请选择文件...</option>
                        </select>
                    </div>
                    
                    <div id="queryFields"></div>
                    
                    <% If ismas = "1" Then %>
                    <div class="form-group">
                        <label>验证码:</label>
                        <div class="captcha-group">
                            <div class="captcha-image" id="captchaImage"></div>
                            <input type="text" id="captcha" name="captcha" placeholder="请输入验证码" required>
                        </div>
                    </div>
                    <% End If %>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">查询</button>
                    </div>
                </div>
            </form>
            
            <div class="results-container" id="resultsContainer">
                <div class="results-header">
                    <h3>查询结果</h3>
                    <div class="view-toggle">
                        <button class="view-btn active" onclick="toggleView('horizontal')">横向表格</button>
                        <button class="view-btn" onclick="toggleView('vertical')">竖向表格</button>
                        <button class="close" onclick="closeResults()">&times;</button>
                    </div>
                </div>
                
                <div class="table-container" id="tableContainer"></div>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <%=copyr%> <%=copyu%></p>
        </div>
    </div>
    
    <!-- 图片预览模态框 -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('imageModal')">&times;</span>
            <img id="modalImage" src="" alt="">
        </div>
    </div>
    
    <!-- URL预览模态框 -->
    <div id="urlModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('urlModal')">&times;</span>
            <h3>即将访问外站</h3>
            <p>访问按钮</p>
            <br>
            <p>链接如下:</p>
            <p id="modalUrl"></p>
            <button class="btn" onclick="openUrl()">访问</button>
        </div>
    </div>
    
    <script>
        let currentUrl = '';
        let currentView = 'horizontal';
        let currentData = [];
        let currentPage = 1;
        let totalPages = 1;
        
        // 页面加载时获取文件列表
        window.onload = function() {
            loadFileList();
            <% If ismas = "1" Then %>
            loadCaptcha();
            <% End If %>
        };
        
        // 加载文件列表
        function loadFileList() {
            fetch('?do=list')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('fileName');
                    select.innerHTML = '<option value="">请选择文件...</option>';
                    
                    if (data.files && data.files.length > 0) {
                        data.files.forEach(file => {
                            const option = document.createElement('option');
                            option.value = file.name;
                            option.textContent = file.name;
                            select.appendChild(option);
                        });
                        
                        // 自动选择第一个文件
                        select.selectedIndex = 1;
                        select.dispatchEvent(new Event('change'));
                    }
                })
                .catch(error => {
                    console.error('加载文件列表失败:', error);
                });
        }
        
        // 文件选择变化
        document.getElementById('fileName').addEventListener('change', function() {
            const fileName = this.value;
            if (fileName) {
                loadQueryFields(fileName);
            } else {
                document.getElementById('queryFields').innerHTML = '';
            }
        });
        
        // 加载查询字段
        function loadQueryFields(fileName) {
            fetch('?do=list')
                .then(response => response.json())
                .then(data => {
                    const file = data.files.find(f => f.name === fileName);
                    if (file && file.fields) {
                        const container = document.getElementById('queryFields');
                        container.innerHTML = '';
                        
                        file.fields.forEach(field => {
                            const div = document.createElement('div');
                            div.className = 'form-group';
                            div.innerHTML = `
                                <label for="field_${field}">${field}:</label>
                                <input type="text" id="field_${field}" name="field_${field}" placeholder="请输入${field}" required>
                            `;
                            container.appendChild(div);
                        });
                    }
                });
        }
        
        // 加载验证码
        function loadCaptcha() {
            fetch('?do=code')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captchaImage').textContent = data.captcha;
                });
        }
        
        // 表单提交
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('page', '1');
            
            fetch('?do=cha', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    <% If ismas = "1" Then %>
                    loadCaptcha();
                    <% End If %>
                } else {
                    displayResults(data);
                }
            })
            .catch(error => {
                console.error('查询失败:', error);
                alert('查询失败，请重试');
            });
        });
        
        // 显示查询结果
        function displayResults(data) {
            currentData = data.results;
            currentPage = data.page;
            totalPages = data.totalPages;
            
            document.getElementById('searchForm').style.display = 'none';
            document.getElementById('resultsContainer').style.display = 'block';
            
            renderTable();
            renderPagination();
        }
        
        // 渲染表格
        function renderTable() {
            const container = document.getElementById('tableContainer');
            
            if (currentData.length === 0) {
                container.innerHTML = '<p>没有找到匹配的数据</p>';
                return;
            }
            
            if (currentView === 'horizontal') {
                renderHorizontalTable(container);
            } else {
                renderVerticalTable(container);
            }
        }
        
        // 渲染横向表格
        function renderHorizontalTable(container) {
            if (currentData.length === 0) return;
            
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            
            // 表头
            const headerRow = document.createElement('tr');
            Object.keys(currentData[0]).forEach(key => {
                const th = document.createElement('th');
                th.textContent = key;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            
            // 数据行
            currentData.forEach(row => {
                const tr = document.createElement('tr');
                Object.values(row).forEach(value => {
                    const td = document.createElement('td');
                    td.innerHTML = value;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            
            table.appendChild(thead);
            table.appendChild(tbody);
            container.innerHTML = '';
            container.appendChild(table);
        }
        
        // 渲染竖向表格
        function renderVerticalTable(container) {
            if (currentData.length === 0) return;
            
            const table = document.createElement('table');
            const tbody = document.createElement('tbody');
            
            currentData.forEach((row, index) => {
                Object.keys(row).forEach(key => {
                    const tr = document.createElement('tr');
                    const th = document.createElement('th');
                    const td = document.createElement('td');
                    
                    th.textContent = key;
                    td.innerHTML = row[key];
                    
                    tr.appendChild(th);
                    tr.appendChild(td);
                    tbody.appendChild(tr);
                });
                
                if (index < currentData.length - 1) {
                    const separator = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 2;
                    td.style.borderTop = '2px solid #ddd';
                    td.style.height = '10px';
                    separator.appendChild(td);
                    tbody.appendChild(separator);
                }
            });
            
            table.appendChild(tbody);
            container.innerHTML = '';
            container.appendChild(table);
        }
        
        // 切换视图
        function toggleView(view) {
            currentView = view;
            
            // 更新按钮状态
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            renderTable();
        }
        
        // 渲染分页
        function renderPagination() {
            const container = document.getElementById('pagination');
            container.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            // 第一页
            const firstPage = document.createElement('a');
            firstPage.href = '#';
            firstPage.textContent = '第一页';
            firstPage.className = currentPage === 1 ? 'disabled' : '';
            firstPage.onclick = currentPage === 1 ? null : () => goToPage(1);
            container.appendChild(firstPage);
            
            // 上一页
            const prevPage = document.createElement('a');
            prevPage.href = '#';
            prevPage.textContent = '上一页';
            prevPage.className = currentPage === 1 ? 'disabled' : '';
            prevPage.onclick = currentPage === 1 ? null : () => goToPage(currentPage - 1);
            container.appendChild(prevPage);
            
            // 页码选择
            const pageSelect = document.createElement('select');
            pageSelect.onchange = function() {
                goToPage(parseInt(this.value));
            };
            
            for (let i = 1; i <= totalPages; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `第${i}页`;
                if (i === currentPage) option.selected = true;
                pageSelect.appendChild(option);
            }
            container.appendChild(pageSelect);
            
            // 下一页
            const nextPage = document.createElement('a');
            nextPage.href = '#';
            nextPage.textContent = '下一页';
            nextPage.className = currentPage === totalPages ? 'disabled' : '';
            nextPage.onclick = currentPage === totalPages ? null : () => goToPage(currentPage + 1);
            container.appendChild(nextPage);
            
            // 最后页
            const lastPage = document.createElement('a');
            lastPage.href = '#';
            lastPage.textContent = '最后页';
            lastPage.className = currentPage === totalPages ? 'disabled' : '';
            lastPage.onclick = currentPage === totalPages ? null : () => goToPage(totalPages);
            container.appendChild(lastPage);
        }
        
        // 跳转页面
        function goToPage(page) {
            if (page < 1 || page > totalPages) return;
            
            const formData = new FormData(document.getElementById('searchForm'));
            formData.append('page', page);
            
            fetch('?do=cha', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    displayResults(data);
                }
            });
        }
        
        // 关闭结果
        function closeResults() {
            document.getElementById('resultsContainer').style.display = 'none';
            document.getElementById('searchForm').style.display = 'block';
            currentData = [];
        }
        
        // 显示图片
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'block';
        }
        
        // 显示URL
        function showUrl(url) {
            currentUrl = url;
            document.getElementById('modalUrl').textContent = url;
            document.getElementById('urlModal').style.display = 'block';
        }
        
        // 打开URL
        function openUrl() {
            if (currentUrl) {
                window.open(currentUrl, '_blank');
                closeModal('urlModal');
            }
        }
        
        // 关闭模态框
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // 点击模态框外部关闭
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<%
End Select
%>