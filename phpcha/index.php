<?php 
$title="康熙字典查询系统";		//设置查询标题
$copyr="查立得-";		//设置底部版权文字
$jscss="V20210909"; 		//用户js css调用的参数，更改本参数可更新js css缓存
$ismas="1";			//设置是否使用验证码，1是0否。
$baoha="1";			//设置是否精准查询，1等于0包含。

//设置查询条件列(和数据共有则查搜条件)。||开头结尾分开
$itiao = "||zi||lu||shu||"; 

//设置隐藏列(和数据共有则查搜条件)。||开头结尾分开
$ihide = "||id||"; 

//设置网址列(和数据共有则查搜条件)。||开头结尾分开，该列内容为文本网址或相对路径
$isurl ="||";

//设置图片列(和数据共有则查搜条件)。||开头结尾分开，该列内容为文本网址或相对路径
$isimg ="||";

$dbdir ="./shujuku/data.mdb";	//数据库文件放的文件夹
$copyr = "康熙字典";	//单位简称
$copyu ="/";		//设置底部单位简称链接
$pagex = "10";	//每页显示
$pagem = "20";	//最大显示页，防止全部数据全显示泄露

// 字段映射为中文显示
$field_map = array(
    'id' => 'ID',
    'zi' => '字',
    'lu' => '部首',
    'shu' => '释义'
);

// 处理AJAX请求
if(isset($_POST['action']) && $_POST['action'] == 'search') {
    include 'search.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo $jscss; ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo $title; ?></h1>
        </header>
        
        <div class="search-form" id="searchForm">
            <form id="searchFormElement">
                <div class="form-group">
                    <input type="text" id="searchInput" placeholder="请输入要查询的字" required>
                </div>
                <div class="form-group">
                    <select id="searchType">
                        <option value="zi">按字查询</option>
                        <option value="lu">按部首查询</option>
                        <option value="shu">按释义查询</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" id="searchBtn">查询</button>
                    <button type="button" id="resetBtn">重置</button>
                </div>
            </form>
        </div>
        
        <div class="results-container" id="resultsContainer" style="display: none;">
            <div class="results-header">
                <h2>查询结果</h2>
                <div class="view-controls">
                    <button id="tableView" class="view-btn active">表格视图</button>
                    <button id="listView" class="view-btn">列表视图</button>
                </div>
                <button id="closeResults" class="close-btn">关闭</button>
            </div>
            
            <div class="results-content">
                <div id="resultsTable" class="table-view"></div>
                <div id="resultsList" class="list-view" style="display: none;"></div>
            </div>
            
            <div class="pagination" id="pagination"></div>
        </div>
        
        <div class="loading" id="loading" style="display: none;">
            <div class="spinner"></div>
            <p>查询中...</p>
        </div>
        
        <div class="toast" id="toast"></div>
    </div>
    
    <!-- 图片全屏显示遮罩 -->
    <div class="image-overlay" id="imageOverlay">
        <div class="image-container">
            <img id="fullImage" src="" alt="">
            <button class="close-image" id="closeImage">×</button>
        </div>
    </div>
    
    <!-- 链接弹窗遮罩 -->
    <div class="link-overlay" id="linkOverlay">
        <div class="link-container">
            <h3>即将访问外站</h3>
            <p id="linkUrl"></p>
            <div class="link-buttons">
                <button id="confirmLink" class="btn-primary">访问</button>
                <button id="cancelLink" class="btn-secondary">取消</button>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo $copyr; ?> <?php echo date('Y'); ?></p>
    </footer>
    
    <script src="script.js?v=<?php echo $jscss; ?>"></script>
</body>
</html>