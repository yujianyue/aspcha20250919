<?php
// 防止直接下载数据文件
if (!isset($_GET['do']) || $_GET['do'] !== 'data') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}
?>
商品名称	商品编号	价格	库存	商品主图	购买地址	先领优惠券	领券地址	描述
iPhone 15	P001	5999	100	https://example.com/iphone15.jpg	https://apple.com/iphone15	https://coupon.com/iphone15	https://coupon.com/iphone15	最新款iPhone
MacBook Pro	P002	12999	50	https://example.com/macbook.jpg	https://apple.com/macbook	https://coupon.com/macbook	https://coupon.com/macbook	专业笔记本电脑
iPad Air	P003	4399	80	https://example.com/ipad.jpg	https://apple.com/ipad	https://coupon.com/ipad	https://coupon.com/ipad	轻薄平板电脑
AirPods Pro	P004	1999	200	https://example.com/airpods.jpg	https://apple.com/airpods	https://coupon.com/airpods	https://coupon.com/airpods	降噪无线耳机
Apple Watch	P005	2999	150	https://example.com/watch.jpg	https://apple.com/watch	https://coupon.com/watch	https://coupon.com/watch	智能手表