<?php

session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

/*  Logout  */
if ($page === 'logout') {
    if (isset($_SESSION['user'])) {
        clearRememberToken($conn, $_SESSION['user']['id']);
    }
    $_SESSION = [];
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/', '', false, false);
    // Keep remember_email so the login form can stay prefilled after logout.
    header('Location: index.php?page=login');
    exit;
}

/* AJAX Add And Remove from Wishlist
*/
if ($page === 'ajax') {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';

    if ($type === 'wishlist_add') {
        ajaxWishlistAdd($conn);
    } elseif ($type === 'wishlist_remove') {
        ajaxWishlistRemove($conn);
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

autoLoginFromCookie($conn);


if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    header('Location: index.php?page=home');
    exit;
}


if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

if ($page === 'wishlist') {
    if ($_SESSION['user']['role'] !== 'user' || !$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=home');
        exit;
    }
}

/*  Dispatch  */
switch ($page) {
    case 'login':    loginCtrl($conn);    break;
    case 'register': registerCtrl($conn); break;
    case 'home':     homeCtrl($conn);     break;
    case 'profile':  profileCtrl($conn);  break;
    case 'wishlist': wishlistCtrl($conn); break;
    default:
        header('Location: index.php?page=login');
        exit;
}

mysqli_close($conn);
