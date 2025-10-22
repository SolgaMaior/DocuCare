<?php


// Get the page parameter from URL
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Default page
if (!$page) {
    $page = 'records';
}

// Define allowed pages/controllers
$allowedPages = [
    'records' => 'controllers/record.php',
    'appointments' => 'controllers/appointment.php',
    'schedules' => 'controllers/schedules.php',
    'dashboard' => 'controllers/dashboard_controller.php',
    'diagnosis' => 'controllers/diagnosis.php',
    'login' => 'controllers/authcontroller/login.controller.php',
    'signup' => 'controllers/authcontroller/create_account.controller.php',
    'logout' => 'controllers/authcontroller/logout.controller.php',
    'forgot_password' => 'controllers/authcontroller/forgot_password.controller.php',
];

// Include auth check only for protected pages
if ($page !== 'login' && $page !== 'signup' && $page !== 'forgot_password') {
    require('authCheck.php');
}


// Route to the appropriate controller
if (array_key_exists($page, $allowedPages) && file_exists($allowedPages[$page])) {
    require $allowedPages[$page];
} else {
    http_response_code(404);
    echo "Page not found";
}
