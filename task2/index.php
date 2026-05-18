<?php

session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

/* Logout  */
if ($page === 'logout') {
    if (isset($_SESSION['user'])) {
        clearRememberToken($conn, $_SESSION['user']['id']);
    }
    $_SESSION = [];
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remember_email', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

/*  AJAX endpoints */
if ($page === 'ajax') {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';

    if ($type === 'delete_request') {
        ajaxDeleteRequest($conn);
    } elseif ($type === 'search_requests') {
        ajaxSearchRequests($conn);
    } else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Unknown AJAX type.']);
        exit;
    }
    exit;
}

/* Auth gates  */
$publicPages = ['login', 'register'];

//  skip login/register
if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'scout' && $_SESSION['user']['is_verified']) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// Protected pages require login
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

// only verified scouts can access scout pages
$scoutPages = ['dashboard', 'request_form', 'approved'];
if (in_array($page, $scoutPages)) {
    if ($_SESSION['user']['role'] !== 'scout') {
        header('Location: index.php?page=login');
        exit;
    }
    if (!$_SESSION['user']['is_verified']) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}
}

/*  Dispatch  */
switch ($page) {
    case 'login':        loginCtrl($conn);       break;
    case 'register':     registerCtrl($conn);    break;
    case 'dashboard':    dashboardCtrl($conn);   break;
    case 'request_form': requestFormCtrl($conn); break;
    case 'approved':     approvedCtrl($conn);    break;
    default:
        header('Location: index.php?page=login');
        exit;
}

mysqli_close($conn);
?>
