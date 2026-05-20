<?php

session_start();

require 'config.php';

require 'models.php';

require 'controllers.php';

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {

    logoutCtrl();

    exit;
}

if ($page === 'ajax') {

    ajaxHandler($conn);

    exit;
}

$publicPages = [
    'login',
    'register'
];

if (
    $page === 'login' &&
    isset($_SESSION['user']) &&
    $_SESSION['user']['role'] === 'admin'
) {

    header('Location: index.php?page=dashboard');

    exit;
}

if (
    !in_array($page, $publicPages) &&
    (
        !isset($_SESSION['user']) ||
        $_SESSION['user']['role'] !== 'admin'
    )
) {

    header('Location: index.php?page=login');

    exit;
}

switch ($page) {

    case 'login':

        loginCtrl($conn);

        break;

    case 'register':

       registerCtrl($conn);

       break;

    case 'dashboard':

        dashboardCtrl($conn);

        break;

    case 'users':

        usersCtrl($conn);

        break;

    case 'posts':

        postsCtrl($conn);

        break;

    case 'comments':

        commentsCtrl($conn);

        break;

    default:

        header('Location: index.php?page=dashboard');

        exit;
}

mysqli_close($conn);

?>