<?php
// 基础配置
$title = "康熙字典查询系统";
$copyr = "查立得-";
$jscss = "V20210909";
$ismas = "1";
$baoha = "1";

// 查询条件列配置
$itiao = "||zi||lu||shu||";

// 隐藏列配置
$ihide = "||id||";

// 网址列配置
$isurl = "||";

// 图片列配置
$isimg = "||";

// 数据库配置
$dbdir = "./shujuku/data.mdb";

// 其他配置
$copyr = "康熙字典";
$copyu = "/";
$pagex = "10";
$pagem = "20";

// 字段映射为中文显示
$field_map = array(
    'id' => 'ID',
    'zi' => '字',
    'lu' => '部首',
    'shu' => '释义'
);

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', './error.log');

// 设置时区
date_default_timezone_set('Asia/Shanghai');
?>