<?php
// 防止直接下载数据文件
if (!isset($_GET['do']) || $_GET['do'] !== 'data') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}
?>
产品名称	产品编号	分类	价格	库存	产品图片	购买地址	优惠券地址	描述	供应商
笔记本电脑	LAP001	电脑	5999	50	https://via.placeholder.com/200x150/4CAF50/white?text=Laptop	https://example.com/laptop	https://coupon.com/laptop	高性能笔记本电脑	科技公司A
智能手机	PHO001	手机	2999	100	https://via.placeholder.com/200x150/2196F3/white?text=Phone	https://example.com/phone	https://coupon.com/phone	最新款智能手机	科技公司B
平板电脑	TAB001	平板	1999	80	https://via.placeholder.com/200x150/FF9800/white?text=Tablet	https://example.com/tablet	https://coupon.com/tablet	轻薄便携平板	科技公司C
无线耳机	EAR001	配件	299	200	https://via.placeholder.com/200x150/9C27B0/white?text=Earphone	https://example.com/earphone	https://coupon.com/earphone	降噪无线耳机	科技公司D
智能手表	WAT001	配件	1299	150	https://via.placeholder.com/200x150/E91E63/white?text=Watch	https://example.com/watch	https://coupon.com/watch	健康监测手表	科技公司E