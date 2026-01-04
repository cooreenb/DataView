<?php
/**
 * API 数据交互接口
 * 处理前后台所有数据请求
 */

// 错误处理
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 引入配置文件
require_once __DIR__ . '/config.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 启动 Session
session_start();
if (defined('SESSION_LIFETIME')) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

/**
 * 工具类
 */
class DataHelper {
    
    /**
     * 读取数据文件
     */
    public static function readData() {
        // 检查数据目录是否存在
        if (!is_dir(DATA_DIR)) {
            if (!mkdir(DATA_DIR, 0755, true)) {
                return self::getDefaultData();
            }
        }
        
        // 如果数据文件不存在，尝试从初始化文件复制或创建默认数据
        if (!file_exists(DATA_FILE)) {
            $initFile = DATA_DIR . '.init_data.db.php';
            if (file_exists($initFile)) {
                // 读取初始化数据
                $content = file_get_contents($initFile);
                $content = preg_replace('/^<\?php\s+exit;\s*\?>\s*/i', '', $content);
                $data = json_decode($content, true);
                if ($data) {
                    self::saveData($data);
                    return $data;
                }
            }
            // 创建默认数据
            $defaultData = self::getDefaultData();
            self::saveData($defaultData);
            return $defaultData;
        }
        
        // 读取数据文件
        $content = file_get_contents(DATA_FILE);
        
        // 移除 PHP 伪装头
        $content = preg_replace('/^<\?php\s+exit;\s*\?>\s*/i', '', $content);
        
        $data = json_decode($content, true);
        
        if ($data === null) {
            return self::getDefaultData();
        }
        
        return $data;
    }
    
    /**
     * 保存数据文件
     */
    public static function saveData($data) {
        // 确保数据目录存在
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0755, true);
        }
        
        // 更新时间戳
        $data['settings']['updated_at'] = date('Y-m-d H:i:s');
        
        // 添加 PHP 伪装头
        $content = "<?php exit; ?>\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return file_put_contents(DATA_FILE, $content, LOCK_EX) !== false;
    }
    
    /**
     * 获取默认数据结构
     */
    public static function getDefaultData() {
        return [
            'modules' => [],
            'settings' => [
                'site_title' => defined('SITE_TITLE') ? SITE_TITLE : '数据展示系统',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * 生成唯一ID
     */
    public static function generateId($prefix = '') {
        return $prefix . uniqid() . '_' . bin2hex(random_bytes(4));
    }
    
    /**
     * 检查数据目录权限
     */
    public static function checkPermissions() {
        if (!is_dir(DATA_DIR)) {
            if (!@mkdir(DATA_DIR, 0755, true)) {
                return ['writable' => false, 'message' => 'data 目录不存在且无法创建'];
            }
        }
        
        if (!is_writable(DATA_DIR)) {
            return ['writable' => false, 'message' => '请在宝塔面板中将 data 目录权限设置为 755 或 777'];
        }
        
        // 测试写入
        $testFile = DATA_DIR . '.test_' . time();
        if (@file_put_contents($testFile, 'test') === false) {
            return ['writable' => false, 'message' => 'data 目录无写入权限，请设置为 755 或 777'];
        }
        @unlink($testFile);
        
        return ['writable' => true, 'message' => ''];
    }
}

/**
 * 验证登录状态
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * 验证管理员权限
 */
function requireAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '请先登录']);
        exit;
    }
}

/**
 * 输出JSON响应
 */
function jsonResponse($data, $success = true, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 输出错误响应
 */
function errorResponse($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========== API 路由处理 ==========

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// 获取JSON请求体
$jsonInput = null;
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonInput = json_decode($rawInput, true);
    }
    if ($jsonInput === null) {
        $jsonInput = $_POST;
    }
}

try {
    switch ($action) {
        
        // ========== 公共接口 ==========
        
        // 获取前台展示数据
        case 'get_public_data':
            $data = DataHelper::readData();
            // 按 sort_order 排序
            usort($data['modules'], function($a, $b) {
                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
            });
            jsonResponse([
                'modules' => $data['modules'],
                'settings' => $data['settings']
            ]);
            break;
        
        // ========== 认证接口 ==========
        
        // 登录
        case 'login':
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $password = $jsonInput['password'] ?? '';
            
            if (empty($password)) {
                errorResponse('请输入密码');
            }
            
            if ($password === ADMIN_PASSWORD) {
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                jsonResponse(['logged_in' => true], true, '登录成功');
            } else {
                errorResponse('密码错误');
            }
            break;
        
        // 登出
        case 'logout':
            session_destroy();
            jsonResponse(null, true, '已退出登录');
            break;
        
        // 检查登录状态
        case 'check_auth':
            jsonResponse([
                'logged_in' => isLoggedIn(),
                'permissions' => DataHelper::checkPermissions()
            ]);
            break;
        
        // ========== 管理接口（需要登录）==========
        
        // 获取所有模块（管理端）
        case 'get_modules':
            requireAuth();
            $data = DataHelper::readData();
            usort($data['modules'], function($a, $b) {
                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
            });
            jsonResponse($data['modules']);
            break;
        
        // 创建模块
        case 'create_module':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $title = trim($jsonInput['title'] ?? '');
            $description = trim($jsonInput['description'] ?? '');
            $columns = $jsonInput['columns'] ?? [];
            
            if (empty($title)) {
                errorResponse('模块标题不能为空');
            }
            
            if (empty($columns) || !is_array($columns)) {
                errorResponse('请至少定义一个表头列');
            }
            
            $data = DataHelper::readData();
            
            $newModule = [
                'id' => DataHelper::generateId('module_'),
                'title' => $title,
                'description' => $description,
                'columns' => array_values($columns),
                'rows' => [],
                'sort_order' => count($data['modules'])
            ];
            
            $data['modules'][] = $newModule;
            
            if (DataHelper::saveData($data)) {
                jsonResponse($newModule, true, '模块创建成功');
            } else {
                errorResponse('保存数据失败，请检查目录权限');
            }
            break;
        
        // 更新模块
        case 'update_module':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['id'] ?? '';
            $title = trim($jsonInput['title'] ?? '');
            $description = trim($jsonInput['description'] ?? '');
            $columns = $jsonInput['columns'] ?? null;
            
            if (empty($moduleId)) {
                errorResponse('模块ID不能为空');
            }
            
            if (empty($title)) {
                errorResponse('模块标题不能为空');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    $module['title'] = $title;
                    $module['description'] = $description;
                    
                    if ($columns !== null && is_array($columns)) {
                        $newColumns = array_values($columns);
                        $newColumnCount = count($newColumns);
                        $oldColumnCount = count($module['columns']);
                        
                        // 如果列数发生变化，需要调整每行数据
                        if ($newColumnCount !== $oldColumnCount) {
                            foreach ($module['rows'] as &$row) {
                                $rowData = $row['data'] ?? [];
                                // 调整行数据长度以匹配新的列数
                                if ($newColumnCount < $oldColumnCount) {
                                    // 列减少，截断行数据
                                    $row['data'] = array_slice($rowData, 0, $newColumnCount);
                                } else {
                                    // 列增加，补充空值
                                    while (count($row['data']) < $newColumnCount) {
                                        $row['data'][] = '';
                                    }
                                }
                            }
                            unset($row);
                        }
                        
                        $module['columns'] = $newColumns;
                    }
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '模块更新成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 删除模块
        case 'delete_module':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['id'] ?? '';
            
            if (empty($moduleId)) {
                errorResponse('模块ID不能为空');
            }
            
            $data = DataHelper::readData();
            $originalCount = count($data['modules']);
            
            $data['modules'] = array_values(array_filter($data['modules'], function($m) use ($moduleId) {
                return $m['id'] !== $moduleId;
            }));
            
            if (count($data['modules']) === $originalCount) {
                errorResponse('模块不存在');
            }
            
            // 重新排序
            foreach ($data['modules'] as $i => &$module) {
                $module['sort_order'] = $i;
            }
            unset($module);
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '模块删除成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 模块排序
        case 'sort_modules':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $order = $jsonInput['order'] ?? [];
            
            if (!is_array($order)) {
                errorResponse('排序数据格式错误');
            }
            
            $data = DataHelper::readData();
            
            // 创建ID到模块的映射
            $moduleMap = [];
            foreach ($data['modules'] as $module) {
                $moduleMap[$module['id']] = $module;
            }
            
            // 按新顺序重建模块数组
            $newModules = [];
            foreach ($order as $i => $id) {
                if (isset($moduleMap[$id])) {
                    $moduleMap[$id]['sort_order'] = $i;
                    $newModules[] = $moduleMap[$id];
                    unset($moduleMap[$id]);
                }
            }
            
            // 添加未在排序中的模块
            foreach ($moduleMap as $module) {
                $module['sort_order'] = count($newModules);
                $newModules[] = $module;
            }
            
            $data['modules'] = $newModules;
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '排序保存成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // ========== 行数据管理 ==========
        
        // 添加行
        case 'add_row':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $rowData = $jsonInput['data'] ?? [];
            
            if (empty($moduleId)) {
                errorResponse('模块ID不能为空');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    $newRow = [
                        'id' => DataHelper::generateId('row_'),
                        'data' => array_values($rowData)
                    ];
                    $module['rows'][] = $newRow;
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse($newRow, true, '添加成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 更新行
        case 'update_row':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $rowId = $jsonInput['row_id'] ?? '';
            $rowData = $jsonInput['data'] ?? [];
            
            if (empty($moduleId) || empty($rowId)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    foreach ($module['rows'] as &$row) {
                        if ($row['id'] === $rowId) {
                            $row['data'] = array_values($rowData);
                            $found = true;
                            break 2;
                        }
                    }
                    unset($row);
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('数据行不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '更新成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 删除行
        case 'delete_row':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $rowId = $jsonInput['row_id'] ?? '';
            
            if (empty($moduleId) || empty($rowId)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    $originalCount = count($module['rows']);
                    $module['rows'] = array_values(array_filter($module['rows'], function($r) use ($rowId) {
                        return $r['id'] !== $rowId;
                    }));
                    if (count($module['rows']) < $originalCount) {
                        $found = true;
                        break;
                    }
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('数据行不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '删除成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 行排序
        case 'sort_rows':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $order = $jsonInput['order'] ?? [];
            
            if (empty($moduleId) || !is_array($order)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    // 创建ID到行的映射
                    $rowMap = [];
                    foreach ($module['rows'] as $row) {
                        $rowMap[$row['id']] = $row;
                    }
                    
                    // 按新顺序重建行数组
                    $newRows = [];
                    foreach ($order as $id) {
                        if (isset($rowMap[$id])) {
                            $newRows[] = $rowMap[$id];
                            unset($rowMap[$id]);
                        }
                    }
                    
                    // 添加未在排序中的行
                    foreach ($rowMap as $row) {
                        $newRows[] = $row;
                    }
                    
                    $module['rows'] = $newRows;
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '排序保存成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // ========== 系统设置 ==========
        
        // 更新设置
        case 'update_settings':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $settings = $jsonInput['settings'] ?? [];
            
            $data = DataHelper::readData();
            $data['settings'] = array_merge($data['settings'], $settings);
            
            if (DataHelper::saveData($data)) {
                jsonResponse($data['settings'], true, '设置已保存');
            } else {
                errorResponse('保存设置失败');
            }
            break;
        
        // 检查系统状态
        case 'system_status':
            $permissions = DataHelper::checkPermissions();
            jsonResponse([
                'version' => VERSION,
                'php_version' => PHP_VERSION,
                'permissions' => $permissions,
                'data_file' => basename(DATA_FILE)
            ]);
            break;
        
        // 获取设置
        case 'get_settings':
            requireAuth();
            $data = DataHelper::readData();
            jsonResponse($data['settings']);
            break;
        
        // 获取统计信息
        case 'get_stats':
            requireAuth();
            $data = DataHelper::readData();
            
            $moduleCount = count($data['modules']);
            $totalRows = 0;
            $totalColumns = 0;
            
            foreach ($data['modules'] as $module) {
                $totalRows += count($module['rows'] ?? []);
                $totalColumns += count($module['columns'] ?? []);
            }
            
            // 计算数据文件大小
            $fileSize = 0;
            if (file_exists(DATA_FILE)) {
                $fileSize = filesize(DATA_FILE);
            }
            
            // 格式化文件大小
            $fileSizeFormatted = $fileSize < 1024 
                ? $fileSize . ' B' 
                : ($fileSize < 1048576 
                    ? round($fileSize / 1024, 2) . ' KB' 
                    : round($fileSize / 1048576, 2) . ' MB');
            
            jsonResponse([
                'module_count' => $moduleCount,
                'total_rows' => $totalRows,
                'total_columns' => $totalColumns,
                'file_size' => $fileSize,
                'file_size_formatted' => $fileSizeFormatted,
                'updated_at' => $data['settings']['updated_at'] ?? '-'
            ]);
            break;
        
        // 导出数据
        case 'export_data':
            requireAuth();
            $data = DataHelper::readData();
            // 按 sort_order 排序
            usort($data['modules'], function($a, $b) {
                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
            });
            jsonResponse([
                'modules' => $data['modules'],
                'settings' => $data['settings'],
                'export_time' => date('Y-m-d H:i:s')
            ]);
            break;
        
        // 导入数据
        case 'import_data':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $importData = $jsonInput['data'] ?? null;
            $mode = $jsonInput['mode'] ?? 'merge'; // merge 或 replace
            
            if (!$importData || !isset($importData['modules'])) {
                errorResponse('导入数据格式错误');
            }
            
            $data = DataHelper::readData();
            
            if ($mode === 'replace') {
                // 完全替换
                $data['modules'] = [];
            }
            
            // 导入模块
            $importedCount = 0;
            foreach ($importData['modules'] as $importModule) {
                // 生成新ID避免冲突
                $newModule = [
                    'id' => DataHelper::generateId('module_'),
                    'title' => $importModule['title'] ?? '未命名模块',
                    'description' => $importModule['description'] ?? '',
                    'columns' => $importModule['columns'] ?? [],
                    'rows' => [],
                    'sort_order' => count($data['modules']) + $importedCount
                ];
                
                // 导入行数据
                if (isset($importModule['rows']) && is_array($importModule['rows'])) {
                    foreach ($importModule['rows'] as $row) {
                        $newModule['rows'][] = [
                            'id' => DataHelper::generateId('row_'),
                            'data' => $row['data'] ?? []
                        ];
                    }
                }
                
                $data['modules'][] = $newModule;
                $importedCount++;
            }
            
            // 导入设置（可选）
            if (isset($importData['settings']['site_title'])) {
                $data['settings']['site_title'] = $importData['settings']['site_title'];
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(['imported' => $importedCount], true, "成功导入 {$importedCount} 个模块");
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 表头排序
        case 'sort_columns':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $columnOrder = $jsonInput['order'] ?? [];
            
            if (empty($moduleId) || !is_array($columnOrder)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    $oldColumns = $module['columns'];
                    $newColumns = [];
                    
                    // 按新顺序重建列数组
                    foreach ($columnOrder as $oldIndex) {
                        if (isset($oldColumns[$oldIndex])) {
                            $newColumns[] = $oldColumns[$oldIndex];
                        }
                    }
                    
                    // 重新排列每行数据
                    foreach ($module['rows'] as &$row) {
                        $newData = [];
                        foreach ($columnOrder as $oldIndex) {
                            $newData[] = $row['data'][$oldIndex] ?? '';
                        }
                        $row['data'] = $newData;
                    }
                    unset($row);
                    
                    $module['columns'] = $newColumns;
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '表头排序已保存');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // ========== 批量删除接口 ==========
        
        // 批量删除模块
        case 'batch_delete_modules':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleIds = $jsonInput['ids'] ?? [];
            
            if (!is_array($moduleIds) || empty($moduleIds)) {
                errorResponse('请选择要删除的模块');
            }
            
            $data = DataHelper::readData();
            $originalCount = count($data['modules']);
            
            $data['modules'] = array_values(array_filter($data['modules'], function($m) use ($moduleIds) {
                return !in_array($m['id'], $moduleIds);
            }));
            
            $deletedCount = $originalCount - count($data['modules']);
            
            if ($deletedCount === 0) {
                errorResponse('未找到要删除的模块');
            }
            
            // 重新排序
            foreach ($data['modules'] as $i => &$module) {
                $module['sort_order'] = $i;
            }
            unset($module);
            
            if (DataHelper::saveData($data)) {
                jsonResponse(['deleted' => $deletedCount], true, "成功删除 {$deletedCount} 个模块");
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 批量删除行
        case 'batch_delete_rows':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $rowIds = $jsonInput['row_ids'] ?? [];
            
            if (empty($moduleId)) {
                errorResponse('模块ID不能为空');
            }
            
            if (!is_array($rowIds) || empty($rowIds)) {
                errorResponse('请选择要删除的数据行');
            }
            
            $data = DataHelper::readData();
            $found = false;
            $deletedCount = 0;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    $originalCount = count($module['rows']);
                    $module['rows'] = array_values(array_filter($module['rows'], function($r) use ($rowIds) {
                        return !in_array($r['id'], $rowIds);
                    }));
                    $deletedCount = $originalCount - count($module['rows']);
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if ($deletedCount === 0) {
                errorResponse('未找到要删除的数据行');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(['deleted' => $deletedCount], true, "成功删除 {$deletedCount} 行数据");
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // ========== 内容块管理接口 ==========
        
        // 添加内容块
        case 'add_content_block':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $title = trim($jsonInput['title'] ?? '');
            $content = $jsonInput['content'] ?? '';
            $position = $jsonInput['position'] ?? 'top'; // top 或 bottom
            
            if (empty($moduleId)) {
                errorResponse('模块ID不能为空');
            }
            
            $data = DataHelper::readData();
            $found = false;
            $newBlock = null;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId) {
                    // 初始化内容块数组
                    if (!isset($module['content_blocks'])) {
                        $module['content_blocks'] = [];
                    }
                    
                    $newBlock = [
                        'id' => DataHelper::generateId('block_'),
                        'title' => $title,
                        'content' => $content,
                        'position' => $position,
                        'sort_order' => count($module['content_blocks'])
                    ];
                    
                    $module['content_blocks'][] = $newBlock;
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse($newBlock, true, '内容块添加成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 更新内容块
        case 'update_content_block':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $blockId = $jsonInput['block_id'] ?? '';
            $title = trim($jsonInput['title'] ?? '');
            $content = $jsonInput['content'] ?? '';
            $position = $jsonInput['position'] ?? null;
            
            if (empty($moduleId) || empty($blockId)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId && isset($module['content_blocks'])) {
                    foreach ($module['content_blocks'] as &$block) {
                        if ($block['id'] === $blockId) {
                            $block['title'] = $title;
                            $block['content'] = $content;
                            if ($position !== null) {
                                $block['position'] = $position;
                            }
                            $found = true;
                            break 2;
                        }
                    }
                    unset($block);
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('内容块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '内容块更新成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 删除内容块
        case 'delete_content_block':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $blockId = $jsonInput['block_id'] ?? '';
            
            if (empty($moduleId) || empty($blockId)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId && isset($module['content_blocks'])) {
                    $originalCount = count($module['content_blocks']);
                    $module['content_blocks'] = array_values(array_filter($module['content_blocks'], function($b) use ($blockId) {
                        return $b['id'] !== $blockId;
                    }));
                    
                    // 重新排序
                    foreach ($module['content_blocks'] as $i => &$b) {
                        $b['sort_order'] = $i;
                    }
                    unset($b);
                    
                    if (count($module['content_blocks']) < $originalCount) {
                        $found = true;
                        break;
                    }
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('内容块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '内容块删除成功');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        // 内容块排序
        case 'sort_content_blocks':
            requireAuth();
            if ($method !== 'POST') {
                errorResponse('请求方法错误', 405);
            }
            
            $moduleId = $jsonInput['module_id'] ?? '';
            $order = $jsonInput['order'] ?? [];
            
            if (empty($moduleId) || !is_array($order)) {
                errorResponse('参数不完整');
            }
            
            $data = DataHelper::readData();
            $found = false;
            
            foreach ($data['modules'] as &$module) {
                if ($module['id'] === $moduleId && isset($module['content_blocks'])) {
                    // 创建ID到内容块的映射
                    $blockMap = [];
                    foreach ($module['content_blocks'] as $block) {
                        $blockMap[$block['id']] = $block;
                    }
                    
                    // 按新顺序重建数组
                    $newBlocks = [];
                    foreach ($order as $i => $id) {
                        if (isset($blockMap[$id])) {
                            $blockMap[$id]['sort_order'] = $i;
                            $newBlocks[] = $blockMap[$id];
                            unset($blockMap[$id]);
                        }
                    }
                    
                    // 添加未在排序中的块
                    foreach ($blockMap as $block) {
                        $block['sort_order'] = count($newBlocks);
                        $newBlocks[] = $block;
                    }
                    
                    $module['content_blocks'] = $newBlocks;
                    $found = true;
                    break;
                }
            }
            unset($module);
            
            if (!$found) {
                errorResponse('模块不存在');
            }
            
            if (DataHelper::saveData($data)) {
                jsonResponse(null, true, '内容块排序已保存');
            } else {
                errorResponse('保存数据失败');
            }
            break;
        
        default:
            errorResponse('未知的操作类型', 400);
    }
    
} catch (Exception $e) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        errorResponse('服务器错误: ' . $e->getMessage(), 500);
    } else {
        errorResponse('服务器内部错误', 500);
    }
}
