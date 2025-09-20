    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 管理员专用JavaScript函数
        function confirmDelete(message) {
            return confirm(message || '确定要删除吗？此操作不可恢复！');
        }
        
        function batchDelete(ids, url) {
            if (ids.length === 0) {
                showMessage('请选择要删除的项目', 'warning');
                return;
            }
            
            if (confirm('确定要删除选中的 ' + ids.length + ' 个项目吗？')) {
                ajaxRequest(url, {ids: ids.join(',')}, function(response) {
                    if (response.status == 1) {
                        showMessage('删除成功', 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showMessage(response.msg, 'danger');
                    }
                });
            }
        }
        
        function exportData(url, params) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            for (var key in params) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = params[key];
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
</body>
</html>