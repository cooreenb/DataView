<?php
/**
 * 前台展示页面
 * Apple极简风格 + 毛玻璃效果
 */

require_once __DIR__ . '/config.php';

function getData() {
    if (!is_dir(DATA_DIR)) {
        return ['modules' => [], 'settings' => ['site_title' => SITE_TITLE]];
    }
    
    if (!file_exists(DATA_FILE)) {
        $initFile = DATA_DIR . '.init_data.db.php';
        if (file_exists($initFile)) {
            $content = file_get_contents($initFile);
            $content = preg_replace('/^<\?php\s+exit;\s*\?>\s*/i', '', $content);
            $data = json_decode($content, true);
            if ($data) return $data;
        }
        return ['modules' => [], 'settings' => ['site_title' => SITE_TITLE]];
    }
    
    $content = file_get_contents(DATA_FILE);
    $content = preg_replace('/^<\?php\s+exit;\s*\?>\s*/i', '', $content);
    $data = json_decode($content, true);
    
    if (!$data) return ['modules' => [], 'settings' => ['site_title' => SITE_TITLE]];
    
    usort($data['modules'], function($a, $b) {
        return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
    });
    
    return $data;
}

$data = getData();
$siteTitle = $data['settings']['site_title'] ?? SITE_TITLE;
$modules = $data['modules'] ?? [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #007AFF;
            --primary-light: #5AC8FA;
            --primary-dark: #0051D5;
            --glass-bg: rgba(255, 255, 255, 0.72);
            --glass-border: rgba(255, 255, 255, 0.6);
            --glass-shadow: 0 8px 32px rgba(0, 122, 255, 0.12);
            --blur-amount: 20px;
            --text-color: #1d1d1f;
            --text-secondary: #86868b;
            --bg-gradient: linear-gradient(135deg, #e8f4ff 0%, #f5f5f7 50%, #e3f0ff 100%);
            --glow-color: rgba(0, 122, 255, 0.5);
            --glow-strong: rgba(0, 122, 255, 0.8);
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-color);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        
        /* 悬浮侧边导航面板 - 支持滚动 */
        .floating-nav {
            position: fixed;
            left: calc((100vw - min(92%, 1400px)) / 2 - 180px);
            top: 50%;
            transform: translateY(-50%);
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 160px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 14px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 122, 255, 0.3) transparent;
        }
        
        .floating-nav::-webkit-scrollbar {
            width: 4px;
        }
        
        .floating-nav::-webkit-scrollbar-track {
            background: transparent;
            margin: 8px 0;
        }
        
        .floating-nav::-webkit-scrollbar-thumb {
            background: rgba(0, 122, 255, 0.25);
            border-radius: 4px;
        }
        
        .floating-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 122, 255, 0.4);
        }
        
        .nav-pill {
            padding: 10px 14px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: background 0.08s ease, color 0.08s ease, box-shadow 0.08s ease;
            text-decoration: none;
            display: block;
            white-space: nowrap;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            border-radius: 12px;
            background: transparent;
            flex-shrink: 0;
        }
        
        .nav-pill:hover {
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-color);
            box-shadow: 0 0 15px var(--glow-color);
        }
        
        .nav-pill.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 0 20px var(--glow-strong);
        }
        
        /* 主内容区 - 响应式宽度，与后台一致 */
        .main-content {
            width: 92%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px;
            min-height: 100vh;
        }
        
        /* PC端响应式宽度断点 */
        @media (min-width: 1920px) {
            .main-content {
                width: 85%;
                max-width: 1600px;
            }
            .floating-nav {
                left: calc((100vw - min(85%, 1600px)) / 2 - 190px);
            }
        }
        
        @media (min-width: 1440px) and (max-width: 1919px) {
            .main-content {
                width: 88%;
                max-width: 1400px;
            }
            .floating-nav {
                left: calc((100vw - min(88%, 1400px)) / 2 - 185px);
            }
        }
        
        @media (min-width: 1200px) and (max-width: 1439px) {
            .main-content {
                width: 90%;
                max-width: 1200px;
            }
            .floating-nav {
                left: calc((100vw - min(90%, 1200px)) / 2 - 180px);
            }
        }
        
        @media (min-width: 1024px) and (max-width: 1199px) {
            .main-content {
                width: 92%;
                max-width: 1100px;
            }
            .floating-nav {
                left: 15px;
                width: 150px;
            }
        }
        
        /* 页面标题 */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            letter-spacing: -0.02em;
        }
        
        /* 搜索栏 */
        .search-container {
            max-width: 560px;
            margin: 0 auto 40px;
        }
        
        .search-wrapper {
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 14px 24px 14px 50px;
            font-size: 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.04);
            outline: none;
            transition: border-color 0.08s ease, box-shadow 0.08s ease;
            color: var(--text-color);
        }
        
        .search-input::placeholder {
            color: var(--text-secondary);
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.15), 0 0 30px var(--glow-color);
        }
        
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-secondary);
            pointer-events: none;
        }
        
        /* 模块卡片 */
        .module-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            margin-bottom: 28px;
            overflow: hidden;
            transition: border-color 0.08s ease, box-shadow 0.08s ease;
        }
        
        .module-card:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 40px var(--glow-color), 0 8px 40px rgba(0, 122, 255, 0.12);
        }
        
        .module-header {
            padding: 24px 28px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .module-title {
            font-size: 1.375rem;
            font-weight: 600;
            color: var(--text-color);
            letter-spacing: -0.01em;
            margin-bottom: 6px;
        }
        
        .module-description {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            line-height: 1.6;
        }
        
        .module-description a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .module-description a:hover {
            text-decoration: underline;
        }
        
        /* 表格容器 - 支持水平滚动 */
        .module-body {
            overflow-x: auto;
            overflow-y: visible;
        }
        
        /* 表格样式 */
        .data-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            white-space: nowrap;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }
        
        .data-table th {
            background: rgba(0, 122, 255, 0.04);
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr {
            transition: background 0.05s ease;
        }
        
        .data-table tr:hover td {
            background: rgba(0, 122, 255, 0.03);
        }
        
        .data-table td {
            color: var(--text-color);
            font-size: 0.9375rem;
        }
        
        /* 单元格悬停显示完整内容 */
        .data-table td .cell-content {
            display: block;
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .data-table td:hover .cell-content {
            overflow: visible;
            white-space: normal;
            word-break: break-word;
            position: relative;
            z-index: 10;
            max-width: none;
        }
        
        .data-table td a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.08s ease;
        }
        
        .data-table td a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .data-table tr.hidden {
            display: none;
        }
        
        /* 空状态 */
        .empty-state {
            text-align: center;
            padding: 80px 24px;
            color: var(--text-secondary);
        }
        
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.4;
            color: var(--primary-color);
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .no-match {
            display: none;
            padding: 32px;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .module-card.no-visible-rows .no-match {
            display: block;
        }
        
        .module-card.no-visible-rows .data-table {
            display: none;
        }
        
        /* 滚动提示 */
        .scroll-hint {
            display: none;
            padding: 8px 16px;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-secondary);
            background: rgba(0, 122, 255, 0.05);
            border-top: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .module-body.has-scroll + .scroll-hint {
            display: block;
        }
        
        /* 内容块卡片容器 */
        .content-blocks-container {
            padding: 20px 24px;
            background: rgba(255, 149, 0, 0.02);
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .content-blocks-container.position-bottom {
            border-bottom: none;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        /* 内容卡片网格布局 - 自适应多列 */
        .content-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        
        /* 内容卡片样式 */
        .content-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 149, 0, 0.2);
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(255, 149, 0, 0.06);
            transition: border-color 0.08s ease, box-shadow 0.08s ease, transform 0.08s ease;
        }
        
        .content-card:hover {
            border-color: rgba(255, 149, 0, 0.45);
            box-shadow: 0 4px 20px rgba(255, 149, 0, 0.12);
        }
        
        .content-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 149, 0, 0.15);
        }
        
        .content-card-body {
            font-size: 0.9375rem;
            line-height: 1.7;
            color: var(--text-color);
        }
        
        .content-card-body p {
            margin-bottom: 10px;
        }
        
        .content-card-body p:last-child {
            margin-bottom: 0;
        }
        
        .content-card-body a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .content-card-body a:hover {
            text-decoration: underline;
        }
        
        .content-card-body ul, .content-card-body ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .content-card-body li {
            margin-bottom: 5px;
        }
        
        .content-card-body strong, .content-card-body b {
            font-weight: 600;
        }
        
        .content-card-body em, .content-card-body i {
            font-style: italic;
        }
        
        .content-card-body code {
            background: rgba(0, 122, 255, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 0.875em;
        }
        
        /* 响应式断点 - 大屏幕 */
        @media (min-width: 1440px) {
            .content-cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 20px;
            }
        }
        
        /* 响应式断点 - 平板 */
        @media (min-width: 768px) and (max-width: 1023px) {
            .content-cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 14px;
            }
            
            .content-card {
                padding: 16px 18px;
            }
        }
        
        /* 响应式断点 - 手机（水平滑动布局） */
        @media (max-width: 767px) {
            .content-blocks-container {
                padding: 16px 0 16px 18px;
                position: relative;
            }
            
            /* 右侧渐变遮罩提示 */
            .content-blocks-container::after {
                content: '';
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 40px;
                background: linear-gradient(to right, transparent, rgba(245, 245, 247, 0.95));
                pointer-events: none;
                z-index: 2;
            }
            
            /* 水平滑动容器 */
            .content-cards-grid {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                gap: 12px;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
                padding-right: 50px;
                padding-bottom: 4px;
            }
            
            .content-cards-grid::-webkit-scrollbar {
                display: none;
            }
            
            /* 卡片固定宽度，不收缩 */
            .content-card {
                flex: 0 0 auto;
                min-width: 240px;
                max-width: 280px;
                width: calc(75vw - 30px);
                padding: 14px 16px;
            }
            
            .content-card-title {
                font-size: 0.9375rem;
            }
            
            .content-card-body {
                font-size: 0.875rem;
            }
        }
        
        /* 更小屏幕优化 */
        @media (max-width: 380px) {
            .content-card {
                min-width: 220px;
                width: calc(80vw - 24px);
            }
        }
        
        /* 移动端菜单按钮 */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 200;
            width: 48px;
            height: 48px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 5px;
        }
        
        .menu-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: var(--text-color);
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        /* 移动端侧边栏 */
        .mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(4px);
            z-index: 150;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .mobile-nav-overlay.active {
            opacity: 1;
        }
        
        .mobile-nav {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border-right: 1px solid var(--glass-border);
            z-index: 160;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 80px 20px 20px;
            overflow-y: auto;
        }
        
        .mobile-nav.active {
            transform: translateX(0);
        }
        
        .mobile-nav-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding: 0 8px;
        }
        
        .mobile-nav .nav-pill {
            width: 100%;
            max-width: none;
            margin-bottom: 6px;
            padding: 12px 16px;
        }
        
        /* 响应式 - 小屏幕 */
        @media (max-width: 1023px) {
            .floating-nav {
                display: none;
            }
            
            .main-content {
                width: 100%;
                padding: 100px 20px 40px;
            }
            
            .menu-toggle {
                display: flex;
            }
            
            .mobile-nav-overlay {
                display: block;
                pointer-events: none;
            }
            
            .mobile-nav-overlay.active {
                pointer-events: auto;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
            
            .module-header {
                padding: 18px 20px;
            }
            
            .module-title {
                font-size: 1.125rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 12px 14px;
            }
        }
        
        /* 滚动条 */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>
    <!-- 移动端菜单按钮 -->
    <button class="menu-toggle" id="menuToggle" aria-label="菜单">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- 移动端导航遮罩 -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    
    <!-- 移动端导航 -->
    <nav class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-title">导航</div>
        <?php foreach ($modules as $index => $module): ?>
            <a class="nav-pill <?php echo $index === 0 ? 'active' : ''; ?>" 
               href="#module-<?php echo htmlspecialchars($module['id']); ?>"
               data-module-id="<?php echo htmlspecialchars($module['id']); ?>">
                <?php echo htmlspecialchars($module['title']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <!-- 悬浮侧边导航 -->
    <?php if (!empty($modules)): ?>
    <nav class="floating-nav" id="floatingNav">
        <?php foreach ($modules as $index => $module): ?>
            <a class="nav-pill <?php echo $index === 0 ? 'active' : ''; ?>" 
               href="#module-<?php echo htmlspecialchars($module['id']); ?>"
               data-module-id="<?php echo htmlspecialchars($module['id']); ?>">
                <?php echo htmlspecialchars($module['title']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    
    <!-- 主内容区 -->
    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($siteTitle); ?></h1>
        </header>
        
        <!-- 搜索栏 -->
        <div class="search-container">
            <div class="search-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="搜索内容..." autocomplete="off">
            </div>
        </div>
        
        <!-- 模块列表 -->
        <div id="moduleList">
            <?php if (empty($modules)): ?>
                <div class="module-card">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                        <h3>暂无数据</h3>
                        <p>请先在后台添加展示模块</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <?php 
                    $columnCount = count($module['columns']);
                    $contentBlocks = $module['content_blocks'] ?? [];
                    usort($contentBlocks, function($a, $b) {
                        return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                    });
                    $topBlocks = array_filter($contentBlocks, function($b) { return ($b['position'] ?? 'top') === 'top'; });
                    $bottomBlocks = array_filter($contentBlocks, function($b) { return ($b['position'] ?? 'top') === 'bottom'; });
                    ?>
                    <div class="module-card" id="module-<?php echo htmlspecialchars($module['id']); ?>" 
                         data-title="<?php echo htmlspecialchars($module['title']); ?>">
                        <div class="module-header">
                            <h2 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h2>
                            <?php if (!empty($module['description'])): ?>
                                <div class="module-description"><?php echo $module['description']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($topBlocks)): ?>
                        <div class="content-blocks-container">
                            <div class="content-cards-grid">
                                <?php foreach ($topBlocks as $block): ?>
                                <div class="content-card">
                                    <?php if (!empty($block['title'])): ?>
                                    <div class="content-card-title"><?php echo htmlspecialchars($block['title']); ?></div>
                                    <?php endif; ?>
                                    <div class="content-card-body"><?php echo $block['content']; ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="module-body">
                            <?php if (empty($module['rows'])): ?>
                                <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                                    暂无数据
                                </div>
                            <?php else: ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <?php foreach ($module['columns'] as $column): ?>
                                                <th><?php echo htmlspecialchars($column); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($module['rows'] as $row): ?>
                                            <tr data-row-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                <?php for ($i = 0; $i < $columnCount; $i++): ?>
                                                    <td><span class="cell-content"><?php echo $row['data'][$i] ?? ''; ?></span></td>
                                                <?php endfor; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="no-match">没有匹配的数据</div>
                            <?php endif; ?>
                        </div>
                        <div class="scroll-hint">← 左右滑动查看更多 →</div>
                        
                        <?php if (!empty($bottomBlocks)): ?>
                        <div class="content-blocks-container position-bottom">
                            <div class="content-cards-grid">
                                <?php foreach ($bottomBlocks as $block): ?>
                                <div class="content-card">
                                    <?php if (!empty($block['title'])): ?>
                                    <div class="content-card-title"><?php echo htmlspecialchars($block['title']); ?></div>
                                    <?php endif; ?>
                                    <div class="content-card-body"><?php echo $block['content']; ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // 检测表格是否需要滚动
        document.querySelectorAll('.module-body').forEach(body => {
            const table = body.querySelector('.data-table');
            if (table && table.scrollWidth > body.clientWidth) {
                body.classList.add('has-scroll');
            }
        });
        
        // 移动端菜单
        const menuToggle = document.getElementById('menuToggle');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');
        
        function toggleMobileNav() {
            mobileNav.classList.toggle('active');
            mobileNavOverlay.classList.toggle('active');
        }
        
        menuToggle.addEventListener('click', toggleMobileNav);
        mobileNavOverlay.addEventListener('click', toggleMobileNav);
        
        // 滚动相关变量
        let scrollTimeout;
        let isScrolling = false;
        
        // 导航点击 - 平滑滚动到目标位置
        document.querySelectorAll('.nav-pill').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // 标记正在程序化滚动
                isScrolling = true;
                
                document.querySelectorAll('.nav-pill').forEach(i => i.classList.remove('active'));
                document.querySelectorAll(`.nav-pill[data-module-id="${this.dataset.moduleId}"]`).forEach(i => i.classList.add('active'));
                
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    const offset = window.innerWidth <= 1023 ? 100 : 40;
                    const top = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                    
                    // 滚动完成后重置标志
                    setTimeout(() => {
                        isScrolling = false;
                    }, 500);
                } else {
                    isScrolling = false;
                }
                
                if (window.innerWidth <= 1023) {
                    toggleMobileNav();
                }
            });
        });
        
        // 搜索功能
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performSearch(this.value.trim().toLowerCase()), 150);
        });
        
        function performSearch(keyword) {
            const moduleCards = document.querySelectorAll('.module-card');
            
            moduleCards.forEach(card => {
                const title = (card.dataset.title || '').toLowerCase();
                const tbody = card.querySelector('tbody');
                
                if (!tbody) {
                    card.style.display = title.includes(keyword) || !keyword ? '' : 'none';
                    return;
                }
                
                const rows = tbody.querySelectorAll('tr');
                let visibleCount = 0;
                const titleMatch = title.includes(keyword);
                
                rows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    const match = !keyword || titleMatch || rowText.includes(keyword);
                    row.classList.toggle('hidden', !match);
                    if (match) visibleCount++;
                });
                
                card.classList.toggle('no-visible-rows', visibleCount === 0 && keyword);
                card.style.display = (titleMatch || visibleCount > 0 || !keyword) ? '' : 'none';
            });
        }
        
        // 滚动监听 - 同步导航高亮和滚动
        window.addEventListener('scroll', function() {
            if (isScrolling) return;
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(updateActiveNav, 50);
        }, { passive: true });
        
        function updateActiveNav() {
            const moduleCards = document.querySelectorAll('.module-card[id]');
            const floatingNav = document.getElementById('floatingNav');
            let currentId = '';
            
            // 找到当前可见的模块
            moduleCards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top <= 150) {
                    currentId = card.id;
                }
            });
            
            if (currentId) {
                // 更新所有导航项的高亮状态
                document.querySelectorAll('.nav-pill').forEach(item => {
                    const href = item.getAttribute('href');
                    item.classList.toggle('active', href === '#' + currentId);
                });
                
                // 将悬浮导航滚动到当前高亮项
                if (floatingNav) {
                    const activeItem = floatingNav.querySelector('.nav-pill.active');
                    if (activeItem) {
                        const navRect = floatingNav.getBoundingClientRect();
                        const itemRect = activeItem.getBoundingClientRect();
                        
                        // 检查高亮项是否在可视区域内
                        const isVisible = itemRect.top >= navRect.top && itemRect.bottom <= navRect.bottom;
                        
                        if (!isVisible) {
                            // 滚动到居中位置
                            const scrollTop = activeItem.offsetTop - floatingNav.clientHeight / 2 + activeItem.clientHeight / 2;
                            floatingNav.scrollTo({
                                top: Math.max(0, scrollTop),
                                behavior: 'smooth'
                            });
                        }
                    }
                }
            }
        }
        
        // 初始化时调用一次
        updateActiveNav();
        
        // 窗口大小变化时重新检测滚动
        window.addEventListener('resize', function() {
            document.querySelectorAll('.module-body').forEach(body => {
                const table = body.querySelector('.data-table');
                body.classList.toggle('has-scroll', table && table.scrollWidth > body.clientWidth);
            });
        });
    </script>
</body>
</html>
