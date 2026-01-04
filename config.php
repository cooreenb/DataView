<?php
/**
 * 系统配置文件
 * 【用户仅需修改此文件】
 * 
 * 使用说明：
 * 1. 修改下方的密码为您的自定义密码
 * 2. 保存文件后即可使用新密码登录后台
 */

// ========== 安全配置 ==========

// 后台登录密码（请修改为您的密码）
define('ADMIN_PASSWORD', 'admin123');

// 数据文件名（随机字符串，防止被扫描下载）
// 首次安装后请勿修改，否则数据会丢失
define('DATA_FILE_NAME', 'storage_' . substr(md5(__FILE__), 0, 12) . '.db.php');

// ========== 系统配置 ==========

// Session 有效期（秒），默认2小时
define('SESSION_LIFETIME', 7200);

// 网站标题
define('SITE_TITLE', '数据展示系统');

// 是否开启调试模式（生产环境请设为 false）
define('DEBUG_MODE', false);

// ========== 请勿修改以下内容 ==========

// 数据目录路径
define('DATA_DIR', __DIR__ . '/data/');

// 数据文件完整路径
define('DATA_FILE', DATA_DIR . DATA_FILE_NAME);

// 版本号
define('VERSION', '1.0.0');
