<?php

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Default page
if (!$page) {
    $page = 'dashboard';
}

// Define allowed pages/controllers
$allowedPages = [
    'records' => 'controllers/record.php',
    'appointments' => 'controllers/appointment.php',
    'schedules' => 'controllers/schedules.php',
    'dashboard' => 'controllers/dashboard.php',
    'diagnosis' => 'controllers/diagnosis.php',
    'login' => 'controllers/authcontroller/login.controller.php',
    'signup' => 'controllers/authcontroller/create_account.controller.php',
    'logout' => 'controllers/authcontroller/logout.controller.php',
    'forgot_password' => 'controllers/authcontroller/forgot_password.controller.php',
    'approve_accounts' => 'controllers/approve_accounts.php',
    'inventory' => 'controllers/inventory.php',
    'inventory_update' => 'controllers/inventory_update.php',
    'reports' => 'controllers/reports.controller.php',
    'refresh_clusters' => 'controllers/refresh_clusters.php',
    'download_file' => 'model/record_file_func/download_file.php',
];

// Include auth check only for protected pages
$publicPages = ['login', 'signup', 'forgot_password'];
if (!in_array($page, $publicPages)) {
    require('authCheck.php');
}

// Route to the appropriate controller
if (array_key_exists($page, $allowedPages) && file_exists($allowedPages[$page])) {
    require $allowedPages[$page];
} else {
    http_response_code(404);
    echo "Page not found";
}
?>

