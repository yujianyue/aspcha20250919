<?php
// 防止直接下载数据文件
if (!isset($_GET['do']) || $_GET['do'] !== 'data') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}
?>
姓名	工号	部门	职位	电话	邮箱	身份证号	密码
刘一	E001	技术部	软件工程师	13900139001	liuyi@company.com	110101199101011234	123456
陈二	E002	技术部	前端工程师	13900139002	chener@company.com	110101199202021234	123456
杨三	E003	销售部	销售经理	13900139003	yangsan@company.com	110101199303031234	123456
黄四	E004	人事部	人事专员	13900139004	huangsi@company.com	110101199404041234	123456
周五	E005	财务部	会计	13900139005	zhouwu@company.com	110101199505051234	123456