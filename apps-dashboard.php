<?php
/**
 * XAMPP Local Apps Dashboard
 * Displays all available applications in the htdocs directory
 */

// Get the htdocs directory
$htdocs = dirname(__FILE__);

// Arrays to store apps
$apps = array();
$ignored = array('.', '..', '.dist', '.vscode', 'webalizer', 'xampp', 'xampp-apps-dashboard-repo'); // Directories to ignore

// Scan htdocs directory
if ($handle = opendir($htdocs)) {
    while (false !== ($entry = readdir($handle))) {
        $full_path = $htdocs . DIRECTORY_SEPARATOR . $entry;
        
        // Skip ignored items and files
        if (in_array($entry, $ignored) || $entry[0] === '.') {
            continue;
        }
        
        // Only include directories
        if (is_dir($full_path)) {
            // Check for common app entry points
            $index_php = $full_path . DIRECTORY_SEPARATOR . 'index.php';
            $index_html = $full_path . DIRECTORY_SEPARATOR . 'index.html';
            
            $has_entry = file_exists($index_php) || file_exists($index_html);
            
            $app_name = ucfirst(str_replace('-', ' ', str_replace('_', ' ', $entry)));
            
            $apps[] = array(
                'name' => $app_name,
                'path' => '/' . $entry . '/',
                'dir' => $entry,
                'has_entry' => $has_entry,
                'is_project' => $has_entry
            );
        }
    }
    closedir($handle);
}

// Sort apps by name
usort($apps, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Determine current protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Apps Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .app-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .app-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
        }
        
        .app-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .app-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .app-path {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 15px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }
        
        .app-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        
        .app-btn {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }
        
        .app-btn-primary {
            background-color: #667eea;
            color: white;
        }
        
        .app-btn-primary:hover {
            background-color: #5568d3;
        }
        
        .app-btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .app-btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .empty-state {
            text-align: center;
            color: white;
            padding: 40px;
        }
        
        .empty-state p {
            font-size: 1.1em;
        }
        
        .footer {
            text-align: center;
            color: white;
            opacity: 0.8;
            font-size: 0.9em;
        }
        
        .badge {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            margin-left: 10px;
        }
        
        .badge.warning {
            background-color: #ff9800;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2em;
            }
            
            .apps-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Local Apps Dashboard</h1>
            <p>Your XAMPP htdocs Applications</p>
        </div>
        
        <?php if (count($apps) > 0): ?>
            <div class="apps-grid">
                <?php foreach ($apps as $app): ?>
                    <div class="app-card">
                        <div class="app-icon">
                            <?php 
                            // Different icons for different app types
                            if (stripos($app['dir'], 'php') !== false) {
                                echo '🐘';
                            } elseif (stripos($app['dir'], 'api') !== false) {
                                echo '⚙️';
                            } elseif (stripos($app['dir'], 'dashboard') !== false) {
                                echo '📊';
                            } elseif (stripos($app['dir'], 'admin') !== false) {
                                echo '👑';
                            } else {
                                echo '📁';
                            }
                            ?>
                        </div>
                        <div class="app-content">
                            <div class="app-name">
                                <?php echo htmlspecialchars($app['name']); ?>
                                <?php if ($app['is_project']): ?>
                                    <span class="badge">Active</span>
                                <?php else: ?>
                                    <span class="badge warning">No Entry</span>
                                <?php endif; ?>
                            </div>
                            <div class="app-path"><?php echo htmlspecialchars($app['path']); ?></div>
                            <div class="app-actions">
                                <?php if ($app['is_project']): ?>
                                    <a href="<?php echo htmlspecialchars($app['path']); ?>" class="app-btn app-btn-primary" target="_blank">
                                        Open App
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo htmlspecialchars($app['path']); ?>" class="app-btn app-btn-secondary" target="_blank">
                                    View Files
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No applications found in your htdocs directory.</p>
                <p>Add folders with index.php or index.html files to see them here.</p>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>💡 Tip: Place your projects in c:\xampp\htdocs\ to have them appear here automatically</p>
        </div>
    </div>
</body>
</html>
