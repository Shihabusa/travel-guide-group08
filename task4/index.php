<?php


session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

/* ─────────────────────────── Logout ─────────────────────────── */
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


if ($page === 'ajax') {
    if (!isset($_SESSION['user'])) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';
    switch ($type) {
        // ── Task 1 ──
        case 'wishlist_add':      ajaxWishlistAdd($conn);      break;
        case 'wishlist_remove':   ajaxWishlistRemove($conn);   break;
        // ── Task 2 ──
        case 'delete_request':    ajaxDeleteRequest($conn);    break;
        case 'search_requests':   ajaxSearchRequests($conn);   break;
        // ── Task 4 ──
        case 'posts_search':      ajaxPostsSearch($conn);      break;
        case 'posts_filter':      ajaxPostsFilter($conn);      break;
        case 'comment_add':       ajaxCommentAdd($conn);       break;
        case 'comment_delete':    ajaxCommentDelete($conn);    break;
        default:
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Unknown AJAX type.']);
    }
    exit;
}

/* ─────────────────────── Public pages ──────────────────────── */
$publicPages = ['login', 'register'];

// Already logged in → redirect to correct home by role
if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'user';
    if ($role === 'scout') {
        header('Location: index.php?page=dashboard'); exit;
    }
    header('Location: index.php?page=home'); exit;
}

// All protected pages require login
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}


if ($page === 'wishlist') {
    if ($_SESSION['user']['role'] !== 'user' || !$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=home'); exit;
    }
}

// Scout pages → verified scouts only
$scoutPages = ['dashboard', 'request_form', 'approved'];
if (in_array($page, $scoutPages)) {
    if ($_SESSION['user']['role'] !== 'scout') {
        header('Location: index.php?page=home'); exit;
    }
    if (!$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=login&msg=not_verified'); exit;
    }
}

/* ──────────────────────── Dispatch ─────────────────────────── */
switch ($page) {
    // ── Task 1 – Auth & General User ──
    case 'login':        loginCtrl($conn);        break;
    case 'register':     registerCtrl($conn);     break;
    case 'home':         homeCtrl($conn);         break;
    case 'profile':      profileCtrl($conn);      break;
    case 'wishlist':     wishlistCtrl($conn);      break;

   
    case 'dashboard':    dashboardCtrl($conn);    break;
    case 'request_form': requestFormCtrl($conn);  break;
    case 'approved':     approvedCtrl($conn);     break;

    
    case 'posts':        postsCtrl($conn);         break;
    case 'post_detail':  postDetailCtrl($conn);    break;

    default:
        header('Location: index.php?page=login'); exit;
}

mysqli_close($conn);
