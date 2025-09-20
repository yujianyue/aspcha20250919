/**
 * 通用工资查询系统JavaScript文件
 * 功能：提供通用JavaScript函数
 * 版权声明：保留发行权和署名权
 * 作者：15058593138@qq.com
 */

// AJAX通信函数
function ajaxRequest(url, data, callback, method) {
    method = method || 'POST';
    var xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (callback) callback(response);
                } catch (e) {
                    console.error('JSON解析错误:', e);
                    if (callback) callback({status: 0, msg: '数据格式错误'});
                }
            } else {
                console.error('请求失败:', xhr.status);
                if (callback) callback({status: 0, msg: '网络请求失败'});
            }
        }
    };
    
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=gb2312');
    
    if (data && method === 'POST') {
        xhr.send(serializeData(data));
    } else {
        xhr.send();
    }
}

// 序列化表单数据
function serializeData(data) {
    var pairs = [];
    for (var key in data) {
        if (data.hasOwnProperty(key)) {
            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
        }
    }
    return pairs.join('&');
}

// 显示消息提示
function showMessage(message, type) {
    type = type || 'info';
    var alertClass = 'alert-' + type;
    var messageHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                     '<button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>' +
                     message +
                     '</div>';
    
    var container = document.querySelector('.container');
    if (container) {
        container.insertAdjacentHTML('afterbegin', messageHtml);
        // 3秒后自动消失
        setTimeout(function() {
            var alert = container.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }
}

// 显示加载状态
function showLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (element) {
        element.innerHTML = '<span class="loading"></span> 加载中...';
        element.disabled = true;
    }
}

// 隐藏加载状态
function hideLoading(element, originalText) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (element) {
        element.innerHTML = originalText || '提交';
        element.disabled = false;
    }
}

// 显示遮罩层
function showModal(title, content, buttons) {
    var overlay = document.querySelector('.overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
    }
    
    var modalHtml = '<div class="modal">' +
                   '<div class="modal-header">' +
                   '<h3 class="modal-title">' + title + '</h3>' +
                   '<button class="modal-close" onclick="hideModal()">&times;</button>' +
                   '</div>' +
                   '<div class="modal-body">' + content + '</div>';
    
    if (buttons) {
        modalHtml += '<div class="modal-footer">' + buttons + '</div>';
    }
    
    modalHtml += '</div>';
    
    overlay.innerHTML = modalHtml;
    overlay.style.display = 'flex';
    
    // 点击遮罩层关闭
    overlay.onclick = function(e) {
        if (e.target === overlay) {
            hideModal();
        }
    };
}

// 隐藏遮罩层
function hideModal() {
    var overlay = document.querySelector('.overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// 确认对话框
function confirmDialog(message, callback) {
    var buttons = '<button class="btn btn-primary" onclick="confirmAction(true)">确认</button>' +
                 '<button class="btn btn-secondary" onclick="confirmAction(false)">取消</button>';
    
    showModal('确认操作', '<p>' + message + '</p>', buttons);
    
    window.confirmAction = function(result) {
        hideModal();
        if (callback) callback(result);
        delete window.confirmAction;
    };
}

// 表单验证
function validateForm(formId, rules) {
    var form = document.getElementById(formId);
    if (!form) return false;
    
    var isValid = true;
    var errors = [];
    
    for (var field in rules) {
        var input = form.querySelector('[name="' + field + '"]');
        if (!input) continue;
        
        var value = input.value.trim();
        var rule = rules[field];
        
        // 必填验证
        if (rule.required && !value) {
            errors.push(rule.message || (field + '不能为空'));
            isValid = false;
            addErrorClass(input);
        }
        
        // 长度验证
        if (value && rule.minLength && value.length < rule.minLength) {
            errors.push(rule.message || (field + '长度不能少于' + rule.minLength + '位'));
            isValid = false;
            addErrorClass(input);
        }
        
        if (value && rule.maxLength && value.length > rule.maxLength) {
            errors.push(rule.message || (field + '长度不能超过' + rule.maxLength + '位'));
            isValid = false;
            addErrorClass(input);
        }
        
        // 正则验证
        if (value && rule.pattern && !rule.pattern.test(value)) {
            errors.push(rule.message || (field + '格式不正确'));
            isValid = false;
            addErrorClass(input);
        }
    }
    
    if (!isValid) {
        showMessage(errors.join('<br>'), 'danger');
    }
    
    return isValid;
}

// 添加错误样式
function addErrorClass(input) {
    input.classList.add('error');
    input.style.borderColor = '#dc3545';
}

// 移除错误样式
function removeErrorClass(input) {
    input.classList.remove('error');
    input.style.borderColor = '';
}

// 表格选择功能
function initTableSelection(tableId) {
    var table = document.getElementById(tableId);
    if (!table) return;
    
    var checkboxes = table.querySelectorAll('input[type="checkbox"]');
    var selectAll = table.querySelector('input[type="checkbox"][data-select-all]');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(checkbox) {
                if (checkbox !== selectAll) {
                    checkbox.checked = selectAll.checked;
                    toggleRowSelection(checkbox);
                }
            });
        });
    }
    
    checkboxes.forEach(function(checkbox) {
        if (checkbox !== selectAll) {
            checkbox.addEventListener('change', function() {
                toggleRowSelection(checkbox);
                updateSelectAllState();
            });
        }
    });
}

// 切换行选择状态
function toggleRowSelection(checkbox) {
    var row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
}

// 更新全选状态
function updateSelectAllState() {
    var table = document.querySelector('table');
    if (!table) return;
    
    var selectAll = table.querySelector('input[type="checkbox"][data-select-all]');
    var checkboxes = table.querySelectorAll('input[type="checkbox"]:not([data-select-all])');
    
    if (selectAll && checkboxes.length > 0) {
        var checkedCount = 0;
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) checkedCount++;
        });
        
        selectAll.checked = (checkedCount === checkboxes.length);
        selectAll.indeterminate = (checkedCount > 0 && checkedCount < checkboxes.length);
    }
}

// 获取选中的行ID
function getSelectedIds(tableId) {
    var table = document.getElementById(tableId);
    if (!table) return [];
    
    var checkboxes = table.querySelectorAll('input[type="checkbox"]:checked:not([data-select-all])');
    var ids = [];
    
    checkboxes.forEach(function(checkbox) {
        var id = checkbox.value;
        if (id) ids.push(id);
    });
    
    return ids;
}

// 分页功能
function initPagination(paginationId, currentPage, totalPages, pageSize) {
    var pagination = document.getElementById(paginationId);
    if (!pagination) return;
    
    var html = '';
    
    // 首页
    if (currentPage > 1) {
        html += '<a href="javascript:goToPage(1)">首页</a>';
    } else {
        html += '<span class="disabled">首页</span>';
    }
    
    // 上一页
    if (currentPage > 1) {
        html += '<a href="javascript:goToPage(' + (currentPage - 1) + ')">上一页</a>';
    } else {
        html += '<span class="disabled">上一页</span>';
    }
    
    // 页码
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, currentPage + 2);
    
    for (var i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            html += '<span class="current">' + i + '</span>';
        } else {
            html += '<a href="javascript:goToPage(' + i + ')">' + i + '</a>';
        }
    }
    
    // 下一页
    if (currentPage < totalPages) {
        html += '<a href="javascript:goToPage(' + (currentPage + 1) + ')">下一页</a>';
    } else {
        html += '<span class="disabled">下一页</span>';
    }
    
    // 末页
    if (currentPage < totalPages) {
        html += '<a href="javascript:goToPage(' + totalPages + ')">末页</a>';
    } else {
        html += '<span class="disabled">末页</span>';
    }
    
    // 页码选择
    if (totalPages > 1) {
        html += '<select onchange="goToPage(this.value)">';
        for (var j = 1; j <= totalPages; j++) {
            var selected = (j === currentPage) ? ' selected' : '';
            html += '<option value="' + j + '"' + selected + '>第' + j + '页</option>';
        }
        html += '</select>';
    }
    
    pagination.innerHTML = html;
}

// 跳转到指定页面
function goToPage(page) {
    var form = document.querySelector('form[data-pagination]');
    if (form) {
        var pageInput = form.querySelector('input[name="page"]');
        if (pageInput) {
            pageInput.value = page;
            form.submit();
        }
    } else {
        // 如果没有表单，可以通过URL参数跳转
        var url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    }
}

// 格式化日期
function formatDate(date, format) {
    format = format || 'YYYY-MM-DD';
    var d = new Date(date);
    var year = d.getFullYear();
    var month = ('0' + (d.getMonth() + 1)).slice(-2);
    var day = ('0' + d.getDate()).slice(-2);
    var hour = ('0' + d.getHours()).slice(-2);
    var minute = ('0' + d.getMinutes()).slice(-2);
    var second = ('0' + d.getSeconds()).slice(-2);
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hour)
        .replace('mm', minute)
        .replace('ss', second);
}

// 防抖函数
function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
        var later = function() {
            clearTimeout(timeout);
            func();
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// 节流函数
function throttle(func, limit) {
    var inThrottle;
    return function() {
        var args = arguments;
        var context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(function() {
                inThrottle = false;
            }, limit);
        }
    };
}

// 工具函数
var Utils = {
    // 获取URL参数
    getUrlParam: function(name) {
        var url = new URL(window.location);
        return url.searchParams.get(name);
    },
    
    // 设置URL参数
    setUrlParam: function(name, value) {
        var url = new URL(window.location);
        url.searchParams.set(name, value);
        window.history.replaceState({}, '', url);
    },
    
    // 复制到剪贴板
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showMessage('已复制到剪贴板', 'success');
            });
        } else {
            // 兼容旧浏览器
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showMessage('已复制到剪贴板', 'success');
        }
    },
    
    // 下载文件
    downloadFile: function(url, filename) {
        var link = document.createElement('a');
        link.href = url;
        link.download = filename || '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化表格选择功能
    var tables = document.querySelectorAll('table[data-selectable]');
    tables.forEach(function(table) {
        initTableSelection(table.id);
    });
    
    // 初始化表单验证
    var forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // 这里可以添加具体的验证逻辑
        });
    });
});