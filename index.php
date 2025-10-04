<?php
// Simple router for DocuCare application
// Routes requests based on REQUEST_URI

// Get the request URI and parse it
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove the base directory from the path if it exists
$baseDir = dirname($_SERVER['SCRIPT_NAME']);
if ($baseDir !== '/') {
    $path = substr($path, strlen($baseDir));
}

// Remove leading slash
$path = ltrim($path, '/');

// Debug: Let's see what path we're getting
if (isset($_GET['debug'])) {
    echo "Request URI: " . $requestUri . "<br>";
    echo "Parsed Path: " . $path . "<br>";
    echo "Base Dir: " . $baseDir . "<br>";
    echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
    exit;
}

// Route based on the path
switch ($path) {
    case '':
    case 'index.php':
    case 'home':
        
        include('record_view.php');
        break;
        
    case 'record':
    case 'records':
    case 'record_view':
        
        include('record_view.php');
        break;
        
    case 'dashboard':
        // Dashboard view (placeholder for future implementation)
        
        break;
        
    case 'reports':
        // Reports view (placeholder for future implementation)
        
        break;
        
    default:
        // 404 - Page not found
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The requested page "' . htmlspecialchars($path) . '" was not found.</p>';
        echo '<p>Debug info: Request URI = ' . htmlspecialchars($requestUri) . ', Path = ' . htmlspecialchars($path) . '</p>';
        echo '<a href="index.php">Go to Records</a>';
        break;
}
?>