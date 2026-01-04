<?php
/**
 * 后台管理界面
 * Apple极简风格 + Vue.js + SortableJS
 */

$basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));
$basePath = rtrim($basePath, '/');
if (empty($basePath)) $basePath = '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://unpkg.com/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-color: #007AFF;
            --primary-dark: #0051D5;
            --glass-bg: rgba(255, 255, 255, 0.78);
            --glass-border: rgba(255, 255, 255, 0.65);
            --glass-shadow: 0 8px 32px rgba(0, 122, 255, 0.1);
            --blur-amount: 20px;
            --danger-color: #FF3B30;
            --success-color: #34C759;
            --warning-color: #FF9500;
            --text-color: #1d1d1f;
            --text-secondary: #86868b;
            --bg-gradient: linear-gradient(135deg, #e8f4ff 0%, #f5f5f7 50%, #e3f0ff 100%);
            --glow-color: rgba(0, 122, 255, 0.5);
            --glow-strong: rgba(0, 122, 255, 0.8);
            --border-radius: 16px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-color);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--glass-shadow);
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            transition: border-color 0.1s, box-shadow 0.1s;
        }
        
        .login-card:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 40px var(--glow-color);
        }
        
        .login-card h1 { text-align: center; font-size: 1.75rem; font-weight: 600; margin-bottom: 8px; }
        .login-card p { text-align: center; color: var(--text-secondary); margin-bottom: 32px; }
        
        .admin-container {
            width: 92%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .admin-header {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            padding: 20px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            transition: border-color 0.1s, box-shadow 0.1s;
        }
        
        .admin-header:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 30px var(--glow-color);
        }
        
        .admin-header h1 { font-size: 1.5rem; font-weight: 600; }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.08s;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: 0 0 20px var(--glow-color); }
        
        .btn-secondary {
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-color);
            border: 1px solid transparent;
        }
        .btn-secondary:hover {
            background: rgba(0, 122, 255, 0.15);
            border-color: var(--glow-color);
            box-shadow: 0 0 15px var(--glow-color);
        }
        
        .btn-success { background: rgba(52, 199, 89, 0.1); color: var(--success-color); }
        .btn-success:hover { background: var(--success-color); color: white; }
        
        .btn-warning { background: rgba(255, 149, 0, 0.1); color: var(--warning-color); }
        .btn-warning:hover { background: var(--warning-color); color: white; }
        
        .btn-danger { background: rgba(255, 59, 48, 0.1); color: var(--danger-color); }
        .btn-danger:hover { background: var(--danger-color); color: white; }
        
        .btn-sm { padding: 7px 12px; font-size: 0.8125rem; }
        .btn-icon { padding: 9px; width: 36px; height: 36px; justify-content: center; }
        
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-weight: 500; margin-bottom: 6px; font-size: 0.9375rem; }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            font-size: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.08s;
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.12), 0 0 20px var(--glow-color);
        }
        
        .form-textarea { min-height: 70px; resize: vertical; font-family: inherit; }
        .form-hint { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 4px; }
        
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            margin-bottom: 20px;
            overflow: hidden;
            transition: border-color 0.1s, box-shadow 0.1s;
        }
        
        .card:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 35px var(--glow-color);
        }
        
        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 122, 255, 0.02);
        }
        
        .card-header h2 { font-size: 1.125rem; font-weight: 600; }
        
        .module-item {
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: background 0.1s;
        }
        
        .module-item:last-child { border-bottom: none; }
        .module-item:hover { background: rgba(0, 122, 255, 0.02); }
        
        .module-header {
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        
        .module-drag-handle {
            cursor: grab;
            color: var(--text-secondary);
            padding: 6px;
            border-radius: 6px;
            transition: all 0.06s;
        }
        
        .module-drag-handle:hover { background: rgba(0, 122, 255, 0.1); color: var(--primary-color); }
        .module-drag-handle:active { cursor: grabbing; }
        
        .module-info { flex: 1; }
        .module-title { font-size: 1rem; font-weight: 600; }
        .module-meta { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 2px; }
        
        .module-actions { display: flex; gap: 6px; }
        
        .module-toggle {
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.15s;
            color: var(--text-secondary);
        }
        .module-toggle.expanded { transform: rotate(180deg); }
        
        .module-content {
            display: none;
            padding: 0 22px 22px;
            background: rgba(0, 122, 255, 0.02);
        }
        .module-content.expanded { display: block; }
        
        .content-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            padding-top: 16px;
            flex-wrap: wrap;
        }
        
        /* 表头排序区域 */
        .column-sort-area {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            padding: 12px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            margin-bottom: 16px;
        }
        
        .column-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: grab;
            transition: all 0.06s;
        }
        
        .column-tag:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 12px var(--glow-color);
        }
        
        .column-tag:active { cursor: grabbing; }
        
        .column-tag .drag-icon {
            color: var(--text-secondary);
            width: 14px;
            height: 14px;
        }
        
        .row-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.08s;
        }
        
        .row-item:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 20px var(--glow-color);
        }
        
        .row-drag-handle {
            cursor: grab;
            color: var(--text-secondary);
            padding: 4px;
            flex-shrink: 0;
            border-radius: 4px;
            transition: all 0.06s;
        }
        
        .row-drag-handle:hover { background: rgba(0, 122, 255, 0.1); color: var(--primary-color); }
        .row-drag-handle:active { cursor: grabbing; }
        
        .row-data { flex: 1; display: grid; gap: 8px; }
        .row-field { display: flex; flex-direction: column; gap: 2px; }
        .row-field-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.03em; }
        .row-field-value { font-size: 0.9375rem; word-break: break-word; }
        .row-actions { display: flex; gap: 4px; flex-shrink: 0; }
        
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .modal {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 22px 26px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 { font-size: 1.25rem; font-weight: 600; }
        
        .modal-close {
            width: 34px; height: 34px;
            border: none;
            background: rgba(0, 0, 0, 0.04);
            cursor: pointer;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary);
            transition: all 0.06s;
        }
        
        .modal-close:hover { background: rgba(0, 0, 0, 0.08); color: var(--text-color); }
        
        .modal-body { padding: 26px; }
        .modal-footer {
            padding: 18px 26px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .column-list { margin-bottom: 12px; }
        .column-item { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
        .column-item .drag-col { cursor: grab; color: var(--text-secondary); padding: 4px; }
        .column-item .drag-col:active { cursor: grabbing; }
        .column-item input { flex: 1; }
        
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 14px 22px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            z-index: 2000;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .toast.success { border-left: 4px solid var(--success-color); }
        .toast.error { border-left: 4px solid var(--danger-color); }
        
        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(255, 149, 0, 0.08);
            border: 1px solid rgba(255, 149, 0, 0.2);
            color: #9a6700;
        }
        
        .alert-icon { flex-shrink: 0; width: 20px; height: 20px; }
        
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-secondary);
        }
        
        .empty-state svg { width: 56px; height: 56px; margin-bottom: 18px; opacity: 0.4; color: var(--primary-color); }
        .empty-state h3 { font-size: 1.125rem; font-weight: 600; color: var(--text-color); margin-bottom: 6px; }
        
        .loading { display: flex; justify-content: center; align-items: center; padding: 60px; }
        
        .spinner {
            width: 44px; height: 44px;
            border: 3px solid rgba(0, 122, 255, 0.15);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .sortable-ghost { opacity: 0.4; background: rgba(0, 122, 255, 0.1); }
        .sortable-chosen { box-shadow: 0 0 30px var(--glow-strong); }
        
        .file-input { display: none; }
        
        /* 批量选择样式 */
        .batch-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            background: rgba(0, 122, 255, 0.08);
            border-bottom: 1px solid rgba(0, 122, 255, 0.1);
            flex-wrap: wrap;
        }
        
        .batch-bar .batch-info {
            font-size: 0.875rem;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            padding: 4px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }
        
        .module-checkbox, .row-checkbox {
            margin-right: 8px;
        }
        
        /* 内容块样式 */
        .content-blocks-section {
            margin-bottom: 16px;
            padding-top: 16px;
        }
        
        .content-blocks-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .content-blocks-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .content-block-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            background: rgba(255, 149, 0, 0.05);
            border: 1px solid rgba(255, 149, 0, 0.15);
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.06s;
        }
        
        .content-block-item:hover {
            border-color: rgba(255, 149, 0, 0.4);
            box-shadow: 0 0 15px rgba(255, 149, 0, 0.15);
        }
        
        .content-block-item .block-drag-handle {
            cursor: grab;
            color: var(--text-secondary);
            padding: 4px;
            flex-shrink: 0;
        }
        
        .content-block-item .block-drag-handle:active { cursor: grabbing; }
        
        .content-block-info {
            flex: 1;
            min-width: 0;
        }
        
        .content-block-info .block-title {
            font-weight: 600;
            font-size: 0.9375rem;
            margin-bottom: 4px;
        }
        
        .content-block-info .block-preview {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 400px;
        }
        
        .content-block-info .block-position {
            display: inline-block;
            font-size: 0.6875rem;
            padding: 2px 8px;
            border-radius: 4px;
            background: rgba(255, 149, 0, 0.15);
            color: var(--warning-color);
            margin-top: 6px;
        }
        
        .block-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
        }
        
        /* 统计面板 */
        .stats-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.15s;
            box-shadow: var(--glass-shadow);
        }
        
        .stat-card:hover {
            border-color: var(--glow-color);
            box-shadow: 0 0 25px var(--glow-color);
            transform: translateY(-2px);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            line-height: 1.2;
            min-height: 2.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-label {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-top: 8px;
            font-weight: 500;
        }
        
        .stat-card.time-card .stat-value {
            font-size: 0.9375rem;
            font-weight: 600;
            white-space: nowrap;
            color: var(--text-color);
        }
        
        .stat-card.highlight .stat-value {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .admin-container { width: 100%; padding: 20px 16px; }
            .admin-header { flex-direction: column; align-items: stretch; padding: 16px 18px; }
            .header-actions { justify-content: center; }
            .module-header { flex-wrap: wrap; padding: 14px 16px; }
            .module-actions { width: 100%; justify-content: flex-end; margin-top: 8px; }
            .row-item { flex-direction: column; }
            .row-actions { width: 100%; justify-content: flex-end; }
            .content-toolbar { justify-content: center; }
            .stats-panel { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 16px; }
            .stat-value { font-size: 1.5rem; }
        }
        
        .icon { width: 16px; height: 16px; flex-shrink: 0; }
    </style>
</head>
<body>
<div id="app">
    <!-- 登录页面 -->
    <div v-if="!isLoggedIn" class="login-container">
        <div class="login-card">
            <h1>后台管理</h1>
            <p>请输入管理密码</p>
            <form @submit.prevent="login">
                <div class="form-group">
                    <label class="form-label">密码</label>
                    <input type="password" class="form-input" v-model="loginPassword" placeholder="请输入密码" autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">登录</button>
            </form>
        </div>
    </div>
    
    <!-- 管理面板 -->
    <div v-else class="admin-container">
        <header class="admin-header">
            <h1>内容管理</h1>
            <div class="header-actions">
                <a :href="basePath + '/'" class="btn btn-secondary" target="_blank">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                    前台
                </a>
                <button class="btn btn-success" @click="exportData">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    导出
                </button>
                <button class="btn btn-warning" @click="triggerImport">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                    导入
                </button>
                <input type="file" ref="importInput" class="file-input" accept=".json" @change="handleImport">
                <button class="btn btn-primary" @click="showAddModuleModal">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    新建
                </button>
                <button class="btn btn-secondary" @click="showSettingsModal = true">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"></path></svg>
                    设置
                </button>
                <button class="btn btn-danger btn-sm" @click="logout">退出</button>
            </div>
        </header>
        
        <div v-if="!permissions.writable" class="alert">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <div><strong>权限警告：</strong>{{ permissions.message }}</div>
        </div>
        
        <!-- 统计面板 -->
        <div class="stats-panel" v-if="!loading">
            <div class="stat-card highlight">
                <div class="stat-value">{{ stats.module_count }}</div>
                <div class="stat-label">展示模块</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ stats.total_rows }}</div>
                <div class="stat-label">数据行数</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ stats.total_columns }}</div>
                <div class="stat-label">表头字段</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ stats.file_size_formatted }}</div>
                <div class="stat-label">数据文件</div>
            </div>
            <div class="stat-card time-card">
                <div class="stat-value">{{ stats.updated_at }}</div>
                <div class="stat-label">最近更新</div>
            </div>
        </div>
        
        <div class="card" v-if="!loading">
            <!-- 批量操作栏 -->
            <div class="batch-bar" v-if="modules.length > 0">
                <label class="checkbox-wrapper">
                    <input type="checkbox" @change="toggleSelectAllModules" :checked="isAllModulesSelected">
                </label>
                <span class="batch-info" v-if="selectedModules.length > 0">已选择 {{ selectedModules.length }} 个模块</span>
                <span class="batch-info" v-else>全选</span>
                <button class="btn btn-danger btn-sm" v-if="selectedModules.length > 0" @click="batchDeleteModules">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                    批量删除
                </button>
            </div>
            
            <div v-if="modules.length === 0" class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                <h3>暂无模块</h3>
                <p>点击"新建"按钮创建展示模块</p>
            </div>
            
            <div id="moduleList" v-else>
                <div class="module-item" v-for="module in modules" :key="module.id" :data-id="module.id">
                    <div class="module-header" @click="toggleModule(module.id)">
                        <label class="checkbox-wrapper module-checkbox" @click.stop>
                            <input type="checkbox" :value="module.id" v-model="selectedModules">
                        </label>
                        <div class="module-drag-handle" @click.stop @mousedown.stop @touchstart.stop>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="12" x2="16" y2="12"></line><line x1="8" y1="18" x2="16" y2="18"></line></svg>
                        </div>
                        <div class="module-info">
                            <div class="module-title">{{ module.title }}</div>
                            <div class="module-meta">{{ module.columns.length }} 列 · {{ module.rows.length }} 行 <span v-if="getContentBlocks(module).length > 0">· {{ getContentBlocks(module).length }} 内容块</span></div>
                        </div>
                        <div class="module-actions" @click.stop>
                            <button class="btn btn-secondary btn-sm" @click="showEditModuleModal(module)">编辑</button>
                            <button class="btn btn-danger btn-sm" @click="confirmDeleteModule(module)">删除</button>
                        </div>
                        <div class="module-toggle" :class="{ expanded: expandedModule === module.id }">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    
                    <div class="module-content" :class="{ expanded: expandedModule === module.id }">
                        <!-- 表头排序区域 -->
                        <div class="column-sort-area" :id="'columnSort-' + module.id">
                            <div class="column-tag" v-for="(col, idx) in module.columns" :key="idx" :data-index="idx">
                                <svg class="drag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="19" r="1"></circle></svg>
                                {{ col }}
                            </div>
                        </div>
                        
                        <!-- 内容块区域 -->
                        <div class="content-blocks-section">
                            <div class="content-blocks-header">
                                <span class="content-blocks-title">内容块 (显示在表格上方或下方)</span>
                                <button class="btn btn-warning btn-sm" @click="showAddBlockModal(module)">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    添加内容块
                                </button>
                            </div>
                            <div v-if="getContentBlocks(module).length === 0" style="text-align: center; padding: 16px; color: var(--text-secondary); font-size: 0.875rem;">暂无内容块</div>
                            <div v-else :id="'blockList-' + module.id" class="block-list">
                                <div class="content-block-item" v-for="block in getContentBlocks(module)" :key="block.id" :data-id="block.id">
                                    <div class="block-drag-handle">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="19" r="1"></circle></svg>
                                    </div>
                                    <div class="content-block-info">
                                        <div class="block-title">{{ block.title || '无标题' }}</div>
                                        <div class="block-preview" v-html="getBlockPreview(block.content)"></div>
                                        <span class="block-position">{{ block.position === 'top' ? '表格上方' : '表格下方' }}</span>
                                    </div>
                                    <div class="block-actions">
                                        <button class="btn btn-secondary btn-icon" @click="showEditBlockModal(module, block)" title="编辑">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button class="btn btn-danger btn-icon" @click="deleteBlock(module, block)" title="删除">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 数据行工具栏 -->
                        <div class="content-toolbar">
                            <button class="btn btn-secondary btn-sm" @click="showAddRowModal(module)">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                添加数据
                            </button>
                            <template v-if="selectedRows[module.id] && selectedRows[module.id].length > 0">
                                <span class="batch-info">已选择 {{ selectedRows[module.id].length }} 行</span>
                                <button class="btn btn-danger btn-sm" @click="batchDeleteRows(module)">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                                    批量删除
                                </button>
                            </template>
                            <label class="checkbox-wrapper" style="margin-left: auto;" v-if="module.rows.length > 0">
                                <input type="checkbox" @change="toggleSelectAllRows(module)" :checked="isAllRowsSelected(module)">
                                <span style="margin-left: 6px; font-size: 0.8125rem; color: var(--text-secondary);">全选数据行</span>
                            </label>
                        </div>
                        
                        <div v-if="module.rows.length === 0" style="text-align: center; padding: 28px; color: var(--text-secondary);">暂无数据</div>
                        <div v-else :id="'rowList-' + module.id" class="row-list">
                            <div class="row-item" v-for="row in module.rows" :key="row.id" :data-id="row.id">
                                <label class="checkbox-wrapper row-checkbox" @click.stop>
                                    <input type="checkbox" :value="row.id" v-model="selectedRows[module.id]">
                                </label>
                                <div class="row-drag-handle">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="19" r="1"></circle></svg>
                                </div>
                                <div class="row-data" :style="{ gridTemplateColumns: 'repeat(' + Math.min(module.columns.length, 3) + ', 1fr)' }">
                                    <div class="row-field" v-for="(col, colIndex) in module.columns" :key="colIndex">
                                        <span class="row-field-label">{{ col }}</span>
                                        <span class="row-field-value" v-html="row.data[colIndex] || '-'"></span>
                                    </div>
                                </div>
                                <div class="row-actions">
                                    <button class="btn btn-secondary btn-icon" @click="showEditRowModal(module, row)" title="编辑">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="btn btn-danger btn-icon" @click="deleteRow(module, row)" title="删除">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="card loading"><div class="spinner"></div></div>
    </div>
    
    <!-- 设置模态框 -->
    <div class="modal-overlay" v-if="showSettingsModal" @click.self="showSettingsModal = false">
        <div class="modal">
            <div class="modal-header">
                <h2>网站设置</h2>
                <button class="modal-close" @click="showSettingsModal = false"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">网站标题</label>
                    <input type="text" class="form-input" v-model="siteSettings.site_title" placeholder="输入网站标题">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="showSettingsModal = false">取消</button>
                <button class="btn btn-primary" @click="saveSettings">保存</button>
            </div>
        </div>
    </div>
    
    <!-- 模块模态框 -->
    <div class="modal-overlay" v-if="moduleModal.show" @click.self="closeModuleModal">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ moduleModal.isEdit ? '编辑模块' : '新建模块' }}</h2>
                <button class="modal-close" @click="closeModuleModal"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">模块标题 *</label>
                    <input type="text" class="form-input" v-model="moduleModal.title" placeholder="例如：常用工具">
                </div>
                <div class="form-group">
                    <label class="form-label">模块简介</label>
                    <textarea class="form-textarea" v-model="moduleModal.description" placeholder="简短的模块介绍（支持HTML）"></textarea>
                    <p class="form-hint">支持 HTML，如 &lt;a href="..."&gt;链接&lt;/a&gt;</p>
                </div>
                <div class="form-group">
                    <label class="form-label">表头列 *</label>
                    <div class="column-list" id="editColumnList">
                        <div class="column-item" v-for="(col, index) in moduleModal.columns" :key="index" :data-index="index">
                            <span class="drag-col"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="12" x2="16" y2="12"></line><line x1="8" y1="18" x2="16" y2="18"></line></svg></span>
                            <input type="text" class="form-input" v-model="moduleModal.columns[index]" :placeholder="'列 ' + (index + 1)">
                            <button class="btn btn-danger btn-icon" @click="removeColumn(index)" v-if="moduleModal.columns.length > 1">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-secondary btn-sm" @click="addColumn">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        添加列
                    </button>
                    <p class="form-hint" v-if="moduleModal.isEdit" style="margin-top: 8px; color: var(--warning-color);">注意：删除列将同时删除该列的所有数据</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeModuleModal">取消</button>
                <button class="btn btn-primary" @click="saveModule">保存</button>
            </div>
        </div>
    </div>
    
    <!-- 行数据模态框 -->
    <div class="modal-overlay" v-if="rowModal.show" @click.self="closeRowModal">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ rowModal.isEdit ? '编辑数据' : '添加数据' }}</h2>
                <button class="modal-close" @click="closeRowModal"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <div class="form-group" v-for="(col, index) in rowModal.columns" :key="index">
                    <label class="form-label">{{ col }}</label>
                    <textarea class="form-textarea" v-model="rowModal.data[index]" :placeholder="'输入 ' + col" rows="2"></textarea>
                </div>
                <p class="form-hint">支持 HTML，如 &lt;a href="..."&gt;链接&lt;/a&gt;</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeRowModal">取消</button>
                <button class="btn btn-primary" @click="saveRow">保存</button>
            </div>
        </div>
    </div>
    
    <!-- 导入模态框 -->
    <div class="modal-overlay" v-if="importModal.show" @click.self="importModal.show = false">
        <div class="modal">
            <div class="modal-header">
                <h2>导入数据</h2>
                <button class="modal-close" @click="importModal.show = false"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 16px;">检测到 <strong>{{ importModal.moduleCount }}</strong> 个模块，请选择导入方式：</p>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px; background: rgba(0, 122, 255, 0.05); border-radius: 8px; margin-bottom: 8px;">
                        <input type="radio" v-model="importModal.mode" value="merge"> 合并导入（追加到现有数据）
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px; background: rgba(255, 59, 48, 0.05); border-radius: 8px;">
                        <input type="radio" v-model="importModal.mode" value="replace"> 替换导入（清空现有数据）
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="importModal.show = false">取消</button>
                <button class="btn btn-primary" @click="confirmImport">确认导入</button>
            </div>
        </div>
    </div>
    
    <!-- 内容块模态框 -->
    <div class="modal-overlay" v-if="blockModal.show" @click.self="closeBlockModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h2>{{ blockModal.isEdit ? '编辑内容块' : '添加内容块' }}</h2>
                <button class="modal-close" @click="closeBlockModal"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">内容块标题</label>
                    <input type="text" class="form-input" v-model="blockModal.title" placeholder="可选，用于标识内容块">
                </div>
                <div class="form-group">
                    <label class="form-label">显示位置 *</label>
                    <div style="display: flex; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: rgba(0, 122, 255, 0.05); border-radius: 8px; flex: 1;" :style="{ background: blockModal.position === 'top' ? 'rgba(0, 122, 255, 0.15)' : '' }">
                            <input type="radio" v-model="blockModal.position" value="top"> 表格上方
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: rgba(0, 122, 255, 0.05); border-radius: 8px; flex: 1;" :style="{ background: blockModal.position === 'bottom' ? 'rgba(0, 122, 255, 0.15)' : '' }">
                            <input type="radio" v-model="blockModal.position" value="bottom"> 表格下方
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">内容 *</label>
                    <textarea class="form-textarea" v-model="blockModal.content" placeholder="输入内容（支持HTML）" rows="8" style="min-height: 180px;"></textarea>
                    <p class="form-hint">支持 HTML 标签，如 &lt;p&gt;, &lt;a&gt;, &lt;strong&gt;, &lt;ul&gt;, &lt;li&gt; 等</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeBlockModal">取消</button>
                <button class="btn btn-primary" @click="saveBlock">保存</button>
            </div>
        </div>
    </div>
    
    <!-- 批量删除确认模态框 -->
    <div class="modal-overlay" v-if="batchDeleteModal.show" @click.self="batchDeleteModal.show = false">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h2>确认删除</h2>
                <button class="modal-close" @click="batchDeleteModal.show = false"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px 0;">
                    <svg style="width: 48px; height: 48px; color: var(--danger-color); margin-bottom: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <p style="font-size: 1rem; margin-bottom: 8px;">确定要删除选中的 <strong style="color: var(--danger-color);">{{ batchDeleteModal.count }}</strong> {{ batchDeleteModal.type === 'modules' ? '个模块' : '行数据' }}吗？</p>
                    <p style="font-size: 0.875rem; color: var(--text-secondary);">此操作不可恢复</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="batchDeleteModal.show = false">取消</button>
                <button class="btn btn-danger" @click="confirmBatchDelete">确认删除</button>
            </div>
        </div>
    </div>
    
    <!-- Toast -->
    <div class="toast" :class="toast.type" v-if="toast.show">
        <svg v-if="toast.type === 'success'" class="icon" viewBox="0 0 24 24" fill="none" stroke="#34C759" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="#FF3B30" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        {{ toast.message }}
    </div>
</div>

<script>
const { createApp, ref, reactive, onMounted, nextTick, watch } = Vue;
const basePath = '<?php echo $basePath; ?>';
const apiUrl = basePath + '/api.php';

createApp({
    setup() {
        const isLoggedIn = ref(false);
        const loading = ref(true);
        const loginPassword = ref('');
        const modules = ref([]);
        const expandedModule = ref(null);
        const permissions = ref({ writable: true, message: '' });
        const importInput = ref(null);
        
        const siteSettings = reactive({ site_title: '' });
        const showSettingsModal = ref(false);
        const stats = reactive({ 
            module_count: 0, 
            total_rows: 0, 
            total_columns: 0, 
            file_size: 0, 
            file_size_formatted: '0 B', 
            updated_at: '-' 
        });
        
        const moduleModal = reactive({ show: false, isEdit: false, id: null, title: '', description: '', columns: [''], originalColumns: [] });
        const rowModal = reactive({ show: false, isEdit: false, moduleId: null, rowId: null, columns: [], data: [] });
        const importModal = reactive({ show: false, data: null, moduleCount: 0, mode: 'merge' });
        const blockModal = reactive({ show: false, isEdit: false, moduleId: null, blockId: null, title: '', content: '', position: 'top' });
        const batchDeleteModal = reactive({ show: false, type: 'modules', count: 0, moduleId: null });
        const toast = reactive({ show: false, type: 'success', message: '' });
        
        // 批量选择状态
        const selectedModules = ref([]);
        const selectedRows = reactive({});
        
        const isAllModulesSelected = Vue.computed(() => {
            return modules.value.length > 0 && selectedModules.value.length === modules.value.length;
        });
        
        let moduleSortable = null;
        let rowSortables = {};
        let columnSortables = {};
        let blockSortables = {};
        
        // 内容块辅助函数
        function getContentBlocks(module) {
            return (module.content_blocks || []).sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
        }
        
        function getBlockPreview(content) {
            if (!content) return '无内容';
            const text = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            return text.length > 60 ? text.substring(0, 60) + '...' : text;
        }
        
        // 批量选择函数
        function toggleSelectAllModules(e) {
            if (e.target.checked) {
                selectedModules.value = modules.value.map(m => m.id);
            } else {
                selectedModules.value = [];
            }
        }
        
        function isAllRowsSelected(module) {
            const rows = selectedRows[module.id] || [];
            return module.rows.length > 0 && rows.length === module.rows.length;
        }
        
        function toggleSelectAllRows(module) {
            if (!selectedRows[module.id]) {
                selectedRows[module.id] = [];
            }
            if (isAllRowsSelected(module)) {
                selectedRows[module.id] = [];
            } else {
                selectedRows[module.id] = module.rows.map(r => r.id);
            }
        }
        
        // 批量删除模块
        function batchDeleteModules() {
            if (selectedModules.value.length === 0) return;
            batchDeleteModal.type = 'modules';
            batchDeleteModal.count = selectedModules.value.length;
            batchDeleteModal.moduleId = null;
            batchDeleteModal.show = true;
        }
        
        // 批量删除行
        function batchDeleteRows(module) {
            const rows = selectedRows[module.id] || [];
            if (rows.length === 0) return;
            batchDeleteModal.type = 'rows';
            batchDeleteModal.count = rows.length;
            batchDeleteModal.moduleId = module.id;
            batchDeleteModal.show = true;
        }
        
        // 确认批量删除
        async function confirmBatchDelete() {
            try {
                if (batchDeleteModal.type === 'modules') {
                    await apiRequest('batch_delete_modules', { ids: selectedModules.value });
                    showToast(`成功删除 ${selectedModules.value.length} 个模块`);
                    selectedModules.value = [];
                } else {
                    const moduleId = batchDeleteModal.moduleId;
                    const rowIds = selectedRows[moduleId] || [];
                    await apiRequest('batch_delete_rows', { module_id: moduleId, row_ids: rowIds });
                    showToast(`成功删除 ${rowIds.length} 行数据`);
                    selectedRows[moduleId] = [];
                }
                batchDeleteModal.show = false;
                await loadModules();
                loadStats();
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        function showToast(message, type = 'success') {
            toast.message = message;
            toast.type = type;
            toast.show = true;
            setTimeout(() => { toast.show = false; }, 3000);
        }
        
        async function apiRequest(action, data = {}, method = 'POST') {
            const options = { method, headers: { 'Content-Type': 'application/json' } };
            if (method === 'POST') options.body = JSON.stringify({ ...data, action });
            const url = apiUrl + '?action=' + action;
            const response = await fetch(url, options);
            const result = await response.json();
            if (!result.success) {
                if (response.status === 401) isLoggedIn.value = false;
                throw new Error(result.message || '请求失败');
            }
            return result;
        }
        
        async function checkAuth() {
            try {
                const result = await apiRequest('check_auth', {}, 'GET');
                isLoggedIn.value = result.data.logged_in;
                permissions.value = result.data.permissions || { writable: true, message: '' };
                if (isLoggedIn.value) {
                    await Promise.all([loadModules(), loadSettings(), loadStats()]);
                }
            } catch (error) { console.error(error); }
            loading.value = false;
        }
        
        async function loadStats() {
            try {
                const result = await apiRequest('get_stats', {}, 'GET');
                if (result.data) {
                    stats.module_count = result.data.module_count || 0;
                    stats.total_rows = result.data.total_rows || 0;
                    stats.total_columns = result.data.total_columns || 0;
                    stats.file_size = result.data.file_size || 0;
                    stats.file_size_formatted = result.data.file_size_formatted || '0 B';
                    stats.updated_at = result.data.updated_at || '-';
                }
            } catch (error) { console.error('loadStats error:', error); }
        }
        
        async function loadSettings() {
            try {
                const result = await apiRequest('get_settings', {}, 'GET');
                siteSettings.site_title = result.data.site_title || '';
            } catch (error) { console.error(error); }
        }
        
        async function saveSettings() {
            try {
                await apiRequest('update_settings', { settings: { site_title: siteSettings.site_title } });
                showToast('设置已保存');
                showSettingsModal.value = false;
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function login() {
            if (!loginPassword.value) { showToast('请输入密码', 'error'); return; }
            try {
                await apiRequest('login', { password: loginPassword.value });
                isLoggedIn.value = true;
                loginPassword.value = '';
                showToast('登录成功');
                await Promise.all([loadModules(), loadSettings(), loadStats()]);
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function logout() {
            try { await apiRequest('logout'); } catch (e) {}
            isLoggedIn.value = false;
            modules.value = [];
        }
        
        async function loadModules() {
            loading.value = true;
            try {
                const result = await apiRequest('get_modules', {}, 'GET');
                modules.value = result.data || [];
                await nextTick();
                setTimeout(() => initModuleSortable(), 50);
            } catch (error) { showToast(error.message, 'error'); }
            loading.value = false;
        }
        
        function initModuleSortable() {
            const el = document.getElementById('moduleList');
            if (!el) return;
            if (moduleSortable) { moduleSortable.destroy(); moduleSortable = null; }
            
            moduleSortable = new Sortable(el, {
                animation: 120,
                handle: '.module-drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: async function(evt) {
                    const items = el.querySelectorAll('.module-item');
                    const order = Array.from(items).map(item => item.dataset.id);
                    try {
                        await apiRequest('sort_modules', { order });
                        const newModules = order.map(id => modules.value.find(m => m.id === id)).filter(Boolean);
                        modules.value = newModules;
                        showToast('排序已保存');
                    } catch (error) { showToast(error.message, 'error'); await loadModules(); }
                }
            });
        }
        
        function initRowSortable(moduleId) {
            const el = document.getElementById('rowList-' + moduleId);
            if (!el) return;
            if (rowSortables[moduleId]) { rowSortables[moduleId].destroy(); delete rowSortables[moduleId]; }
            
            rowSortables[moduleId] = new Sortable(el, {
                animation: 120,
                handle: '.row-drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: async function(evt) {
                    const items = el.querySelectorAll('.row-item');
                    const order = Array.from(items).map(item => item.dataset.id);
                    try {
                        await apiRequest('sort_rows', { module_id: moduleId, order });
                        const module = modules.value.find(m => m.id === moduleId);
                        if (module) {
                            const newRows = order.map(id => module.rows.find(r => r.id === id)).filter(Boolean);
                            module.rows = newRows;
                        }
                        showToast('排序已保存');
                    } catch (error) { showToast(error.message, 'error'); }
                }
            });
        }
        
        function initColumnSortable(moduleId) {
            const el = document.getElementById('columnSort-' + moduleId);
            if (!el) return;
            if (columnSortables[moduleId]) { columnSortables[moduleId].destroy(); delete columnSortables[moduleId]; }
            
            columnSortables[moduleId] = new Sortable(el, {
                animation: 120,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: async function(evt) {
                    const items = el.querySelectorAll('.column-tag');
                    const order = Array.from(items).map(item => parseInt(item.dataset.index));
                    try {
                        await apiRequest('sort_columns', { module_id: moduleId, order });
                        showToast('表头排序已保存');
                        await loadModules();
                        expandedModule.value = moduleId;
                        nextTick(() => {
                            setTimeout(() => {
                                initRowSortable(moduleId);
                                initColumnSortable(moduleId);
                            }, 100);
                        });
                    } catch (error) { showToast(error.message, 'error'); }
                }
            });
        }
        
        function initBlockSortable(moduleId) {
            const el = document.getElementById('blockList-' + moduleId);
            if (!el) return;
            if (blockSortables[moduleId]) { blockSortables[moduleId].destroy(); delete blockSortables[moduleId]; }
            
            blockSortables[moduleId] = new Sortable(el, {
                animation: 120,
                handle: '.block-drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: async function(evt) {
                    const items = el.querySelectorAll('.content-block-item');
                    const order = Array.from(items).map(item => item.dataset.id);
                    try {
                        await apiRequest('sort_content_blocks', { module_id: moduleId, order });
                        showToast('内容块排序已保存');
                    } catch (error) { showToast(error.message, 'error'); }
                }
            });
        }
        
        function toggleModule(moduleId) {
            if (expandedModule.value === moduleId) {
                expandedModule.value = null;
            } else {
                expandedModule.value = moduleId;
                nextTick(() => {
                    setTimeout(() => {
                        initRowSortable(moduleId);
                        initColumnSortable(moduleId);
                        initBlockSortable(moduleId);
                    }, 100);
                });
            }
        }
        
        // 导出数据
        async function exportData() {
            try {
                const result = await apiRequest('export_data', {}, 'GET');
                const dataStr = JSON.stringify(result.data, null, 2);
                const blob = new Blob([dataStr], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `data_export_${new Date().toISOString().slice(0,10)}.json`;
                a.click();
                URL.revokeObjectURL(url);
                showToast('导出成功');
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        function triggerImport() {
            importInput.value.click();
        }
        
        function handleImport(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(event) {
                try {
                    const data = JSON.parse(event.target.result);
                    if (!data.modules || !Array.isArray(data.modules)) {
                        showToast('无效的数据格式', 'error');
                        return;
                    }
                    importModal.data = data;
                    importModal.moduleCount = data.modules.length;
                    importModal.mode = 'merge';
                    importModal.show = true;
                } catch (error) {
                    showToast('JSON解析失败', 'error');
                }
            };
            reader.readAsText(file);
            e.target.value = '';
        }
        
        async function confirmImport() {
            try {
                await apiRequest('import_data', { data: importModal.data, mode: importModal.mode });
                showToast('导入成功');
                importModal.show = false;
                await loadModules();
                loadStats();
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        function showAddModuleModal() {
            moduleModal.show = true;
            moduleModal.isEdit = false;
            moduleModal.id = null;
            moduleModal.title = '';
            moduleModal.description = '';
            moduleModal.columns = [''];
            moduleModal.originalColumns = [];
        }
        
        function showEditModuleModal(module) {
            moduleModal.show = true;
            moduleModal.isEdit = true;
            moduleModal.id = module.id;
            moduleModal.title = module.title;
            moduleModal.description = module.description || '';
            moduleModal.columns = [...module.columns];
            moduleModal.originalColumns = [...module.columns];
        }
        
        function closeModuleModal() { moduleModal.show = false; }
        function addColumn() { moduleModal.columns.push(''); }
        function removeColumn(index) { if (moduleModal.columns.length > 1) moduleModal.columns.splice(index, 1); }
        
        async function saveModule() {
            if (!moduleModal.title.trim()) { showToast('请输入模块标题', 'error'); return; }
            const columns = moduleModal.columns.filter(c => c.trim());
            if (columns.length === 0) { showToast('请至少定义一个表头列', 'error'); return; }
            
            try {
                if (moduleModal.isEdit) {
                    await apiRequest('update_module', { 
                        id: moduleModal.id, 
                        title: moduleModal.title.trim(), 
                        description: moduleModal.description.trim(), 
                        columns 
                    });
                    showToast('模块更新成功');
                } else {
                    await apiRequest('create_module', { 
                        title: moduleModal.title.trim(), 
                        description: moduleModal.description.trim(), 
                        columns 
                    });
                    showToast('模块创建成功');
                }
                closeModuleModal();
                await loadModules();
                loadStats();
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function confirmDeleteModule(module) {
            if (!confirm(`确定删除「${module.title}」及其所有数据吗？`)) return;
            try {
                await apiRequest('delete_module', { id: module.id });
                showToast('已删除');
                if (expandedModule.value === module.id) expandedModule.value = null;
                await loadModules();
                loadStats();
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        function showAddRowModal(module) {
            rowModal.show = true;
            rowModal.isEdit = false;
            rowModal.moduleId = module.id;
            rowModal.rowId = null;
            rowModal.columns = [...module.columns];
            rowModal.data = module.columns.map(() => '');
        }
        
        function showEditRowModal(module, row) {
            rowModal.show = true;
            rowModal.isEdit = true;
            rowModal.moduleId = module.id;
            rowModal.rowId = row.id;
            rowModal.columns = [...module.columns];
            rowModal.data = [...row.data];
            while (rowModal.data.length < rowModal.columns.length) rowModal.data.push('');
        }
        
        function closeRowModal() { rowModal.show = false; }
        
        // 内容块模态框函数
        function showAddBlockModal(module) {
            blockModal.show = true;
            blockModal.isEdit = false;
            blockModal.moduleId = module.id;
            blockModal.blockId = null;
            blockModal.title = '';
            blockModal.content = '';
            blockModal.position = 'top';
        }
        
        function showEditBlockModal(module, block) {
            blockModal.show = true;
            blockModal.isEdit = true;
            blockModal.moduleId = module.id;
            blockModal.blockId = block.id;
            blockModal.title = block.title || '';
            blockModal.content = block.content || '';
            blockModal.position = block.position || 'top';
        }
        
        function closeBlockModal() { blockModal.show = false; }
        
        async function saveBlock() {
            if (!blockModal.content.trim()) {
                showToast('请输入内容', 'error');
                return;
            }
            
            try {
                if (blockModal.isEdit) {
                    await apiRequest('update_content_block', {
                        module_id: blockModal.moduleId,
                        block_id: blockModal.blockId,
                        title: blockModal.title,
                        content: blockModal.content,
                        position: blockModal.position
                    });
                    showToast('内容块更新成功');
                } else {
                    await apiRequest('add_content_block', {
                        module_id: blockModal.moduleId,
                        title: blockModal.title,
                        content: blockModal.content,
                        position: blockModal.position
                    });
                    showToast('内容块添加成功');
                }
                closeBlockModal();
                const currentExpanded = expandedModule.value;
                await loadModules();
                if (currentExpanded) {
                    expandedModule.value = currentExpanded;
                    nextTick(() => {
                        setTimeout(() => {
                            initRowSortable(currentExpanded);
                            initColumnSortable(currentExpanded);
                            initBlockSortable(currentExpanded);
                        }, 50);
                    });
                }
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function deleteBlock(module, block) {
            if (!confirm('确定删除这个内容块吗？')) return;
            try {
                await apiRequest('delete_content_block', { module_id: module.id, block_id: block.id });
                showToast('内容块已删除');
                const currentExpanded = expandedModule.value;
                await loadModules();
                if (currentExpanded) {
                    expandedModule.value = currentExpanded;
                    nextTick(() => {
                        setTimeout(() => {
                            initRowSortable(currentExpanded);
                            initColumnSortable(currentExpanded);
                            initBlockSortable(currentExpanded);
                        }, 50);
                    });
                }
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function saveRow() {
            try {
                if (rowModal.isEdit) {
                    await apiRequest('update_row', { module_id: rowModal.moduleId, row_id: rowModal.rowId, data: rowModal.data });
                    showToast('更新成功');
                } else {
                    await apiRequest('add_row', { module_id: rowModal.moduleId, data: rowModal.data });
                    showToast('添加成功');
                }
                closeRowModal();
                const currentExpanded = expandedModule.value;
                await loadModules();
                loadStats();
                if (currentExpanded) {
                    expandedModule.value = currentExpanded;
                    nextTick(() => {
                        setTimeout(() => {
                            initRowSortable(currentExpanded);
                            initColumnSortable(currentExpanded);
                        }, 50);
                    });
                }
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        async function deleteRow(module, row) {
            if (!confirm('确定删除这行数据吗？')) return;
            try {
                await apiRequest('delete_row', { module_id: module.id, row_id: row.id });
                showToast('已删除');
                const currentExpanded = expandedModule.value;
                await loadModules();
                loadStats();
                if (currentExpanded) {
                    expandedModule.value = currentExpanded;
                    nextTick(() => {
                        setTimeout(() => {
                            initRowSortable(currentExpanded);
                            initColumnSortable(currentExpanded);
                        }, 50);
                    });
                }
            } catch (error) { showToast(error.message, 'error'); }
        }
        
        onMounted(() => checkAuth());
        
        return {
            basePath, isLoggedIn, loading, loginPassword, modules, expandedModule, permissions, stats,
            siteSettings, showSettingsModal, moduleModal, rowModal, importModal, blockModal, batchDeleteModal, toast, importInput,
            selectedModules, selectedRows, isAllModulesSelected,
            login, logout, loadSettings, saveSettings, toggleModule, exportData, triggerImport, handleImport, confirmImport,
            showAddModuleModal, showEditModuleModal, closeModuleModal, addColumn, removeColumn, saveModule, confirmDeleteModule,
            showAddRowModal, showEditRowModal, closeRowModal, saveRow, deleteRow,
            getContentBlocks, getBlockPreview, showAddBlockModal, showEditBlockModal, closeBlockModal, saveBlock, deleteBlock,
            toggleSelectAllModules, isAllRowsSelected, toggleSelectAllRows, batchDeleteModules, batchDeleteRows, confirmBatchDelete
        };
    }
}).mount('#app');
</script>
</body>
</html>
