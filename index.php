<?php
session_start();

// Basic authentication check (adjust based on your auth system)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Get the page parameter from URL
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Default page
if (!$page) {
    $page = 'records'; // or 'records' or whatever your default should be
}

// Define allowed pages/controllers
$allowedPages = [
    'records' => 'controllers/record.php',
    'appointments' => 'controllers/appointment.php',
    'schedules' => 'controllers/schedules.php',
    'dashboard' => 'controllers/dashboard_controller.php',
    'diagnosis' => 'controllers/diagnosis.php',
];

// Route to the appropriate controller
if (array_key_exists($page, $allowedPages) && file_exists($allowedPages[$page])) {
    require $allowedPages[$page];
} else {
    // 404 - Page not found
    http_response_code(404);
    echo "Page not found";
    // or include a 404 view: require 'view/404.view.php';
}
?>