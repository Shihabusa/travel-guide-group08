<?php


/*  Login  */
function loginCtrl($conn) {
    $error   = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

   
    if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
        $user = getUserByToken($conn, $_COOKIE['remember_token']);
        if ($user) {
            $_SESSION['user'] = [
                'id'          => $user['id'],
                'name'        => $user['name'],
                'email'       => $user['email'],
                'role'        => $user['role'],
                'is_verified' => $user['is_verified'],
                'picture'     => $user['profile_picture']
            ];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $error = 'Please fill in both fields.';
        } else {
            $user = authUser($conn, $email, $password);
            if ($user) {
                $_SESSION['user'] = [
                    'id'          => $user['id'],
                    'name'        => $user['name'],
                    'email'       => $user['email'],
                    'role'        => $user['role'],
                    'is_verified' => $user['is_verified'],
                    'picture'     => $user['profile_picture']
                ];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    saveRememberToken($conn, $user['id'], $token);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
                    setcookie('remember_email', $email, time() + 86400 * 30, '/');
                } else {
                    setcookie('remember_token', '', time() - 3600, '/');
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                header('Location: index.php?page=dashboard');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }

    require 'views/login.php';
}

/* Register  */
function registerCtrl($conn) {
    $error = '';
    $old   = ['name' => '', 'email' => '', 'role' => 'scout'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'scout';
        $old      = compact('name', 'email', 'role');

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['admin', 'scout', 'user'])) {
            $error = 'Invalid role selected.';
        } elseif (emailExists($conn, $email)) {
            $error = 'This email is already registered.';
        } else {
            if (registerUser($conn, $name, $email, $password, $role)) {
                header('Location: index.php?page=login&msg=registered');
                exit;
            }
            $error = 'Registration failed. Please try again.';
        }
    }

    require 'views/register.php';
}

/* Scout Dashboard  */
function dashboardCtrl($conn) {
    $scoutId  = $_SESSION['user']['id'];
    $requests = getMyRequests($conn, $scoutId);
    require 'views/dashboard.php';
}

/*  Create / Edit Request*/
function requestFormCtrl($conn) {
    $action  = $_GET['action'] ?? 'add';
    $error   = '';
    $editing = null;
    $scoutId = $_SESSION['user']['id'];

    /*  Show edit form  */
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getRequest($conn, $id, $scoutId);
        if (!$editing || $editing['status'] !== 'pending') {
            header('Location: index.php?page=dashboard&msg=no_edit');
            exit;
        }
    }

    /* Add  */
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title         = trim($_POST['title'] ?? '');
        $shortHistory  = trim($_POST['short_history'] ?? '');
        $country       = trim($_POST['country'] ?? '');
        $genre         = $_POST['genre'] ?? '';
        $costLevel     = $_POST['cost_level'] ?? '';
        $travelMedium  = trim($_POST['travel_medium_info'] ?? '');
        $originalPostId = intval($_POST['original_post_id'] ?? 0) ?: null;
        $image         = null;

        // Validation
        if ($title === '' || $shortHistory === '' || $country === '' || $travelMedium === '') {
            $error = 'All fields are required.';
        } elseif (!in_array($genre, ['beach','mountain','city','historical','nature','other'])) {
            $error = 'Please select a valid genre.';
        } elseif (!in_array($costLevel, ['low','medium','high'])) {
            $error = 'Please select a valid cost level.';
        } else {
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $file    = $_FILES['image'];
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024;

                if (!in_array($file['type'], $allowed)) {
                    $error = 'Only JPG, PNG, GIF or WEBP images allowed.';
                } elseif ($file['size'] > $maxSize) {
                    $error = 'Image must be under 2MB.';
                } else {
                    $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'post_' . $scoutId . '_' . time() . '.' . $ext;
                    $dest  = 'public/uploads/posts/' . $fname;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $image = $dest;
                    } else {
                        $error = 'Failed to upload image.';
                    }
                }
            }

            if ($error === '') {
                if (addRequest($conn, $scoutId, $title, $shortHistory, $country,
                               $genre, $costLevel, $travelMedium, $image, $originalPostId)) {
                    header('Location: index.php?page=dashboard&msg=added');
                    exit;
                }
                $error = 'Failed to submit request. Try again.';
            }
        }
    }

    /*  Update */
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id           = intval($_GET['id'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $shortHistory = trim($_POST['short_history'] ?? '');
        $country      = trim($_POST['country'] ?? '');
        $genre        = $_POST['genre'] ?? '';
        $costLevel    = $_POST['cost_level'] ?? '';
        $travelMedium = trim($_POST['travel_medium_info'] ?? '');
        $editing      = getRequest($conn, $id, $scoutId);
        $image        = $editing['image'] ?? null;

        if ($title === '' || $shortHistory === '' || $country === '' || $travelMedium === '') {
            $error = 'No field can be empty. All fields are required.';
        } elseif (!in_array($genre, ['beach','mountain','city','historical','nature','other'])) {
            $error = 'Please select a valid genre.';
        } elseif (!in_array($costLevel, ['low','medium','high'])) {
            $error = 'Please select a valid cost level.';
        } else {
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $file    = $_FILES['image'];
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024;

                if (!in_array($file['type'], $allowed)) {
                    $error = 'Only JPG, PNG, GIF or WEBP images allowed.';
                } elseif ($file['size'] > $maxSize) {
                    $error = 'Image must be under 2MB.';
                } else {
                    $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'post_' . $scoutId . '_' . time() . '.' . $ext;
                    $dest  = 'public/uploads/posts/' . $fname;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $image = $dest;
                    } else {
                        $error = 'Failed to upload image.';
                    }
                }
            }

            if ($error === '') {
                if (updateRequest($conn, $id, $scoutId, $title, $shortHistory,
                                  $country, $genre, $costLevel, $travelMedium, $image)) {
                    header('Location: index.php?page=dashboard&msg=updated');
                    exit;
                }
                $error = 'Update failed. Try again.';
                $editing = getRequest($conn, $id, $scoutId);
            }
        }
    }

    require 'views/request_form.php';
}

/*  Approved Posts*/
function approvedCtrl($conn) {
    $scoutId = $_SESSION['user']['id'];
    $posts   = getMyApprovedPosts($conn, $scoutId);
    require 'views/approved.php';
}

/* AJAX Delete Request  */
function ajaxDeleteRequest($conn) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $data    = json_decode(file_get_contents('php://input'), true);
    $id      = intval($data['id'] ?? 0);
    $scoutId = $_SESSION['user']['id'];

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request ID.']);
        exit;
    }

    if (deleteRequest($conn, $id, $scoutId)) {
        echo json_encode(['success' => true, 'message' => 'Request deleted successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete. Only pending requests can be deleted.']);
    }
    exit;
}

/*  AJAX Search Requests */
function ajaxSearchRequests($conn) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $scoutId = $_SESSION['user']['id'];
    $q       = trim($_GET['q'] ?? '');
    $results = searchMyRequests($conn, $scoutId, $q);
    echo json_encode($results);
    exit;
}
?>
