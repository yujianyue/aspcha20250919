// 全局变量
let currentPage = 1;
let totalPages = 1;
let currentData = [];
let searchType = 'zi';
let searchKeyword = '';

// DOM元素
const searchForm = document.getElementById('searchFormElement');
const searchInput = document.getElementById('searchInput');
const searchTypeSelect = document.getElementById('searchType');
const searchBtn = document.getElementById('searchBtn');
const resetBtn = document.getElementById('resetBtn');
const resultsContainer = document.getElementById('resultsContainer');
const resultsTable = document.getElementById('resultsTable');
const resultsList = document.getElementById('resultsList');
const tableView = document.getElementById('tableView');
const listView = document.getElementById('listView');
const closeResults = document.getElementById('closeResults');
const loading = document.getElementById('loading');
const toast = document.getElementById('toast');
const pagination = document.getElementById('pagination');
const imageOverlay = document.getElementById('imageOverlay');
const fullImage = document.getElementById('fullImage');
const closeImage = document.getElementById('closeImage');
const linkOverlay = document.getElementById('linkOverlay');
const linkUrl = document.getElementById('linkUrl');
const confirmLink = document.getElementById('confirmLink');
const cancelLink = document.getElementById('cancelLink');

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
});

// 事件监听器初始化
function initializeEventListeners() {
    // 搜索表单提交
    searchForm.addEventListener('submit', handleSearch);
    
    // 重置按钮
    resetBtn.addEventListener('click', handleReset);
    
    // 搜索类型改变
    searchTypeSelect.addEventListener('change', function() {
        searchType = this.value;
        updatePlaceholder();
    });
    
    // 视图切换
    tableView.addEventListener('click', () => switchView('table'));
    listView.addEventListener('click', () => switchView('list'));
    
    // 关闭结果
    closeResults.addEventListener('click', handleCloseResults);
    
    // 图片全屏相关
    closeImage.addEventListener('click', closeImageOverlay);
    imageOverlay.addEventListener('click', function(e) {
        if (e.target === imageOverlay) {
            closeImageOverlay();
        }
    });
    
    // 链接弹窗相关
    confirmLink.addEventListener('click', handleConfirmLink);
    cancelLink.addEventListener('click', closeLinkOverlay);
    linkOverlay.addEventListener('click', function(e) {
        if (e.target === linkOverlay) {
            closeLinkOverlay();
        }
    });
    
    // 键盘事件
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSearch(e);
        }
    });
    
    // 初始化占位符
    updatePlaceholder();
}

// 更新输入框占位符
function updatePlaceholder() {
    const placeholders = {
        'zi': '请输入要查询的字',
        'lu': '请输入要查询的部首',
        'shu': '请输入要查询的释义'
    };
    searchInput.placeholder = placeholders[searchType] || '请输入要查询的内容';
}

// 处理搜索
function handleSearch(e) {
    e.preventDefault();
    
    searchKeyword = searchInput.value.trim();
    if (!searchKeyword) {
        showToast('请输入查询内容', 'error');
        return;
    }
    
    currentPage = 1;
    performSearch();
}

// 执行搜索
function performSearch() {
    showLoading(true);
    
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('keyword', searchKeyword);
    formData.append('type', searchType);
    formData.append('page', currentPage);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            currentData = data.data;
            totalPages = data.totalPages;
            displayResults(data);
            showResults();
        } else {
            showToast(data.message || '查询失败', 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        showToast('网络错误，请重试', 'error');
    });
}

// 显示结果
function displayResults(data) {
    if (data.data.length === 0) {
        showToast('未找到相关结果', 'warning');
        return;
    }
    
    // 更新表格视图
    updateTableView(data.data, data.fields);
    
    // 更新列表视图
    updateListView(data.data, data.fields);
    
    // 更新分页
    updatePagination();
}

// 更新表格视图
function updateTableView(data, fields) {
    let html = '<table><thead><tr>';
    
    // 表头
    fields.forEach(field => {
        if (field.visible) {
            html += `<th>${field.label}</th>`;
        }
    });
    html += '</tr></thead><tbody>';
    
    // 数据行
    data.forEach(row => {
        html += '<tr>';
        fields.forEach(field => {
            if (field.visible) {
                const value = row[field.name] || '';
                html += `<td>${formatFieldValue(value, field)}</td>`;
            }
        });
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    resultsTable.innerHTML = html;
}

// 更新列表视图
function updateListView(data, fields) {
    let html = '';
    
    data.forEach((row, index) => {
        html += `<div class="result-item">`;
        fields.forEach(field => {
            if (field.visible && row[field.name]) {
                html += `
                    <div class="result-field">
                        <span class="result-label">${field.label}:</span>
                        <span class="result-value">${formatFieldValue(row[field.name], field)}</span>
                    </div>
                `;
            }
        });
        html += '</div>';
    });
    
    resultsList.innerHTML = html;
}

// 格式化字段值
function formatFieldValue(value, field) {
    if (!value) return '';
    
    // 图片字段
    if (field.type === 'image') {
        return `<img src="${value}" alt="图片" class="clickable-image" onclick="showImage('${value}')">`;
    }
    
    // 链接字段
    if (field.type === 'url') {
        return `<span class="clickable-link" onclick="showLink('${value}')">${value}</span>`;
    }
    
    return escapeHtml(value);
}

// HTML转义
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 更新分页
function updatePagination() {
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // 首页按钮
    html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(1)">首页</button>`;
    
    // 上一页按钮
    html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">上一页</button>`;
    
    // 页码选择
    html += '<select onchange="goToPage(this.value)">';
    for (let i = 1; i <= totalPages; i++) {
        html += `<option value="${i}" ${i === currentPage ? 'selected' : ''}>第${i}页</option>`;
    }
    html += '</select>';
    
    // 下一页按钮
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">下一页</button>`;
    
    // 末页按钮
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${totalPages})">末页</button>`;
    
    // 页面信息
    html += `<span class="page-info">共${totalPages}页</span>`;
    
    pagination.innerHTML = html;
}

// 跳转到指定页
function goToPage(page) {
    page = parseInt(page);
    if (page < 1 || page > totalPages || page === currentPage) return;
    
    currentPage = page;
    performSearch();
}

// 切换视图
function switchView(view) {
    if (view === 'table') {
        tableView.classList.add('active');
        listView.classList.remove('active');
        resultsTable.style.display = 'block';
        resultsList.style.display = 'none';
    } else {
        tableView.classList.remove('active');
        listView.classList.add('active');
        resultsTable.style.display = 'none';
        resultsList.style.display = 'block';
    }
}

// 显示图片全屏
function showImage(src) {
    fullImage.src = src;
    imageOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// 关闭图片全屏
function closeImageOverlay() {
    imageOverlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// 显示链接弹窗
function showLink(url) {
    linkUrl.textContent = url;
    linkOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// 处理确认链接
function handleConfirmLink() {
    const url = linkUrl.textContent;
    if (url) {
        window.open(url, '_blank');
    }
    closeLinkOverlay();
}

// 关闭链接弹窗
function closeLinkOverlay() {
    linkOverlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// 显示结果容器
function showResults() {
    searchForm.style.display = 'none';
    resultsContainer.style.display = 'block';
}

// 关闭结果
function handleCloseResults() {
    resultsContainer.style.display = 'none';
    searchForm.style.display = 'block';
    resultsTable.innerHTML = '';
    resultsList.innerHTML = '';
    pagination.innerHTML = '';
    currentData = [];
    currentPage = 1;
    totalPages = 1;
}

// 重置表单
function handleReset() {
    searchInput.value = '';
    searchTypeSelect.value = 'zi';
    searchType = 'zi';
    updatePlaceholder();
    handleCloseResults();
}

// 显示加载状态
function showLoading(show) {
    loading.style.display = show ? 'block' : 'none';
}

// 显示提示信息
function showToast(message, type = 'info') {
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// 全局函数，供HTML调用
window.goToPage = goToPage;
window.showImage = showImage;
window.showLink = showLink;