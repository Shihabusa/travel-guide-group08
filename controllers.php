<?php
// ================================================================
// CONTROLLERS – Travel Guide Group 8
// Task 1 (Auth/Profile/Wishlist) + Task 2 (Scout) + Task 4 (Browse)
// ================================================================

/* ================================================================
   SHARED: Login  –  redirects by role after login
   ================================================================ */
function loginCtrl($conn)
{
    $error   = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

    // Auto-login via remember me cookie
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
            // Role-based redirect
            $dest = ($user['role'] === 'scout') ? 'dashboard' : 'home';
            header("Location: index.php?page=$dest");
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

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    saveRememberToken($conn, $user['id'], $token);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
                    setcookie('remember_email', $email,  time() + 86400 * 30, '/');
                } else {
                    setcookie('remember_token', '', time() - 3600, '/');
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                // ── Role-based redirect ──
                $dest = ($user['role'] === 'scout') ? 'dashboard' : 'home';
                header("Location: index.php?page=$dest");
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
    require 'views/login.php';
}

/* ================================================================
   SHARED: Register
   ================================================================ */
function registerCtrl($conn)
{
    $error = '';
    $old   = ['name' => '', 'email' => '', 'role' => 'user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
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

/* ================================================================
   TASK 1 – Home
   ================================================================ */
function homeCtrl($conn)
{
    $posts = [];
    if (isset($_SESSION['user']) && $_SESSION['user']['is_verified']) {
        $posts = getApprovedPosts($conn);
    }
    require 'views/home.php';
}

/* ================================================================
   TASK 1 – Profile
   ================================================================ */
function profileCtrl($conn)
{
    $error   = '';
    $success = '';
    $userId  = $_SESSION['user']['id'];
    $user    = getUser($conn, $userId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $picture = $user['profile_picture'];

            if ($name === '' || $email === '') {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (emailExists($conn, $email, $userId)) {
                $error = 'That email is already used by another account.';
            } else {
                if (!empty($_FILES['profile_picture']['name'])) {
                    $file    = $_FILES['profile_picture'];
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($file['type'], $allowed)) {
                        $error = 'Only JPG, PNG, GIF or WEBP images are allowed.';
                    } elseif ($file['size'] > 2 * 1024 * 1024) {
                        $error = 'Image must be under 2MB.';
                    } else {
                        $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $fname = 'user_' . $userId . '_' . time() . '.' . $ext;
                        $dest  = 'public/uploads/profiles/' . $fname;
                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            $picture = $dest;
                        } else {
                            $error = 'Failed to upload image.';
                        }
                    }
                }
                if ($error === '') {
                    if (updateProfile($conn, $userId, $name, $email, $picture)) {
                        $_SESSION['user']['name']    = $name;
                        $_SESSION['user']['email']   = $email;
                        $_SESSION['user']['picture'] = $picture;
                        $user    = getUser($conn, $userId);
                        $success = 'Profile updated successfully.';
                    } else {
                        $error = 'Update failed. Please try again.';
                    }
                }
            }
        }

        if ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($current === '' || $new === '' || $confirm === '') {
                $error = 'All password fields are required.';
            } else {
                $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);

                if (!$row || !password_verify($current, $row['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } elseif (strlen($new) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } elseif ($new !== $confirm) {
                    $error = 'New passwords do not match.';
                } else {
                    if (updatePassword($conn, $userId, $new)) {
                        $success = 'Password changed successfully.';
                    } else {
                        $error = 'Failed to change password.';
                    }
                }
            }
        }
    }
    require 'views/profile.php';
}

/* ================================================================
   TASK 1 – Wishlist page
   ================================================================ */
function wishlistCtrl($conn)
{
    $userId   = $_SESSION['user']['id'];
    $wishlist = getWishlist($conn, $userId);
    require 'views/wishlist.php';
}

/* ================================================================
   TASK 1 – AJAX Wishlist
   ================================================================ */
function ajaxWishlistAdd($conn)
{
    header('Content-Type: application/json');
    $user = $_SESSION['user'] ?? null;
    if (!$user || $user['role'] !== 'user' || !$user['is_verified']) {
        http_response_code(403);
        echo json_encode(['error' => 'Only verified general users can use the wishlist.']);
        exit;
    }
    $data   = json_decode(file_get_contents('php://input'), true);
    $postId = intval($data['post_id'] ?? 0);
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid post.']);
        exit;
    }
    if (isInWishlist($conn, $user['id'], $postId)) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist.']);
        exit;
    }
    if (addToWishlist($conn, $user['id'], $postId)) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist!']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add.']);
    }
    exit;
}

function ajaxWishlistRemove($conn)
{
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data   = json_decode(file_get_contents('php://input'), true);
    $postId = intval($data['post_id'] ?? 0);
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid post.']);
        exit;
    }
    if (removeFromWishlist($conn, $_SESSION['user']['id'], $postId)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove.']);
    }
    exit;
}

/* ================================================================
   TASK 2 – Scout Dashboard (My Requests)
   ================================================================ */
function dashboardCtrl($conn)
{
    $scoutId  = $_SESSION['user']['id'];
    $requests = getMyRequests($conn, $scoutId);
    require 'views/dashboard.php';
}

/* ================================================================
   TASK 2 – Create / Edit / Update Request form
   ================================================================ */
function requestFormCtrl($conn)
{
    $action  = $_GET['action'] ?? 'add';
    $error   = '';
    $editing = null;
    $scoutId = $_SESSION['user']['id'];

    // Show edit form (GET)
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getRequest($conn, $id, $scoutId);
        if (!$editing || $editing['status'] !== 'pending') {
            header('Location: index.php?page=dashboard&msg=no_edit');
            exit;
        }
    }

    // Add (POST)
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title          = trim($_POST['title'] ?? '');
        $shortHistory   = trim($_POST['short_history'] ?? '');
        $country        = trim($_POST['country'] ?? '');
        $genre          = $_POST['genre'] ?? '';
        $costLevel      = $_POST['cost_level'] ?? '';
        $travelMedium   = trim($_POST['travel_medium_info'] ?? '');
        $originalPostId = intval($_POST['original_post_id'] ?? 0) ?: null;
        $image          = null;

        if ($title === '' || $shortHistory === '' || $country === '' || $travelMedium === '') {
            $error = 'All fields are required.';
        } elseif (!in_array($genre, ['beach', 'mountain', 'city', 'historical', 'nature', 'other'])) {
            $error = 'Please select a valid genre.';
        } elseif (!in_array($costLevel, ['low', 'medium', 'high'])) {
            $error = 'Please select a valid cost level.';
        } else {
            if (!empty($_FILES['image']['name'])) {
                $res = _handleImageUpload($scoutId);
                if ($res['error']) {
                    $error = $res['error'];
                } else {
                    $image = $res['path'];
                }
            }
            if ($error === '') {
                if (addRequest(
                    $conn,
                    $scoutId,
                    $title,
                    $shortHistory,
                    $country,
                    $genre,
                    $costLevel,
                    $travelMedium,
                    $image,
                    $originalPostId
                )) {
                    header('Location: index.php?page=dashboard&msg=added');
                    exit;
                }
                $error = 'Failed to submit request. Try again.';
            }
        }
    }

    // Update (POST)
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
            $error = 'All fields are required.';
        } elseif (!in_array($genre, ['beach', 'mountain', 'city', 'historical', 'nature', 'other'])) {
            $error = 'Please select a valid genre.';
        } elseif (!in_array($costLevel, ['low', 'medium', 'high'])) {
            $error = 'Please select a valid cost level.';
        } else {
            if (!empty($_FILES['image']['name'])) {
                $res = _handleImageUpload($scoutId);
                if ($res['error']) {
                    $error = $res['error'];
                } else {
                    $image = $res['path'];
                }
            }
            if ($error === '') {
                if (updateRequest(
                    $conn,
                    $id,
                    $scoutId,
                    $title,
                    $shortHistory,
                    $country,
                    $genre,
                    $costLevel,
                    $travelMedium,
                    $image
                )) {
                    header('Location: index.php?page=dashboard&msg=updated');
                    exit;
                }
                $error = 'Update failed.';
                $editing = getRequest($conn, $id, $scoutId);
            }
        }
    }

    require 'views/request_form.php';
}

/** Internal helper: handle post image uploads */
function _handleImageUpload($ownerId): array
{
    $file    = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return ['error' => 'Only JPG, PNG, GIF or WEBP images allowed.', 'path' => null];
    if ($file['size'] > 2 * 1024 * 1024)   return ['error' => 'Image must be under 2MB.',                   'path' => null];
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fname = 'post_' . $ownerId . '_' . time() . '.' . $ext;
    $dest  = 'public/uploads/posts/' . $fname;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Failed to upload image.', 'path' => null];
    return ['error' => null, 'path' => $dest];
}

/* ================================================================
   TASK 2 – Approved Posts (Scout's own)
   ================================================================ */
function approvedCtrl($conn)
{
    $scoutId = $_SESSION['user']['id'];
    $posts   = getMyApprovedPosts($conn, $scoutId);
    require 'views/approved.php';
}

/* ================================================================
   TASK 2 – AJAX: Delete & Search requests
   ================================================================ */
function ajaxDeleteRequest($conn)
{
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
        echo json_encode(['error' => 'Invalid ID.']);
        exit;
    }
    if (deleteRequest($conn, $id, $scoutId)) {
        echo json_encode(['success' => true, 'message' => 'Request deleted.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed. Only pending requests can be deleted.']);
    }
    exit;
}

function ajaxSearchRequests($conn)
{
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $scoutId = $_SESSION['user']['id'];
    $q       = trim($_GET['q'] ?? '');
    echo json_encode(searchMyRequests($conn, $scoutId, $q));
    exit;
}

/* ================================================================
   TASK 4 – Browse & Detail
   ================================================================ */
function postsCtrl($conn)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=login');
        exit;
    }
    $posts     = getApprovedPosts($conn);
    $countries = getDistinctCountries($conn);
    require 'views/posts.php';
}

function postDetailCtrl($conn)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=login');
        exit;
    }
    $postId = intval($_GET['id'] ?? 0);
    if ($postId <= 0) {
        header('Location: index.php?page=posts');
        exit;
    }

    $post = getPostById($conn, $postId);
    if (!$post) {
        header('Location: index.php?page=posts');
        exit;
    }

    $comments = getCommentsByPost($conn, $postId);
    $costData = getBaseCost($conn, $postId, $post['cost_level']);
    require 'views/post_detail.php';
}

/* ================================================================
   TASK 4 – AJAX: Search, Filter, Comments
   ================================================================ */
function ajaxPostsSearch($conn)
{
    header('Content-Type: application/json');
    $user = $_SESSION['user'] ?? null;
    if (!$user || !$user['is_verified']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please log in.']);
        exit;
    }
    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode(['success' => true, 'posts' => []]);
        exit;
    }
    if (mb_strlen($q) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Query too long.']);
        exit;
    }
    echo json_encode(['success' => true, 'posts' => _sanitisePosts(searchPosts($conn, $q))]);
    exit;
}

function ajaxPostsFilter($conn)
{
    header('Content-Type: application/json');
    $user = $_SESSION['user'] ?? null;
    if (!$user || !$user['is_verified']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please log in.']);
        exit;
    }
    $country  = trim($_GET['country'] ?? '');
    $cost     = trim($_GET['cost'] ?? '');
    if (!in_array($cost, ['low', 'medium', 'high', ''], true)) $cost = '';
    $genreRaw = $_GET['genre'] ?? [];
    if (!is_array($genreRaw)) $genreRaw = [$genreRaw];
    $genres   = array_values(array_filter(array_map('trim', $genreRaw)));
    $posts    = filterPosts($conn, $country, $genres, $cost);
    echo json_encode(['success' => true, 'posts' => _sanitisePosts($posts), 'count' => count($posts)]);
    exit;
}

function ajaxCommentAdd($conn)
{
    header('Content-Type: application/json');
    $user = $_SESSION['user'] ?? null;
    if (!$user || $user['role'] !== 'user' || !$user['is_verified']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Login as a verified user to comment.']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit;
    }

    $input       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $postId      = intval($input['post_id']      ?? 0);
    $content     = trim($input['content']        ?? '');
    $displayName = trim($input['display_name']   ?? $user['name']);

    $errors = [];
    if ($postId <= 0)                    $errors[] = 'Invalid post.';
    if ($displayName === '')             $errors[] = 'Name cannot be empty.';
    if ($content === '')                 $errors[] = 'Comment cannot be empty.';
    elseif (mb_strlen($content) < 3)    $errors[] = 'Comment must be at least 3 characters.';
    elseif (mb_strlen($content) > 1000) $errors[] = 'Comment must be under 1000 characters.';

    $content     = strip_tags($content);
    $displayName = strip_tags($displayName);

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    $commentId = addComment($conn, $postId, (int)$user['id'], $content);
    echo json_encode(['success' => true, 'comment' => [
        'id'            => $commentId,
        'content'       => htmlspecialchars($content,     ENT_QUOTES, 'UTF-8'),
        'reviewer_name' => htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'),
        'created_at'    => date('Y-m-d H:i'),
        'user_id'       => (int)$user['id'],
    ]]);
    exit;
}

function ajaxCommentDelete($conn)
{
    header('Content-Type: application/json');
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }
    $input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $commentId = intval($input['id'] ?? $_GET['id'] ?? 0);
    if ($commentId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
        exit;
    }
    $comment = getCommentById($conn, $commentId);
    if (!$comment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comment not found.']);
        exit;
    }
    if ((int)$comment['user_id'] !== (int)$user['id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only delete your own comments.']);
        exit;
    }
    echo json_encode(['success' => deleteComment($conn, $commentId, (int)$user['id'])]);
    exit;
}

/** Strip/escape posts before sending as JSON */
function _sanitisePosts(array $posts): array
{
    return array_map(fn($p) => [
        'id'            => $p['id'],
        'title'         => htmlspecialchars($p['title'],        ENT_QUOTES, 'UTF-8'),
        'country'       => htmlspecialchars($p['country'],      ENT_QUOTES, 'UTF-8'),
        'genre'         => htmlspecialchars($p['genre'],        ENT_QUOTES, 'UTF-8'),
        'cost_level'    => $p['cost_level'],
        'image'         => htmlspecialchars($p['image'] ?? '',  ENT_QUOTES, 'UTF-8'),
        'short_history' => htmlspecialchars(mb_substr($p['short_history'], 0, 130) . '…', ENT_QUOTES, 'UTF-8'),
    ], $posts);
}
