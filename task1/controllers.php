<?php

function autoLoginFromCookie($conn) {
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
            return true;
        }
    }
    return false;
}

/* Login */
function loginCtrl($conn) {
    $error  = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

    if (autoLoginFromCookie($conn)) {
        header('Location: index.php?page=home');
        exit;
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
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, false);
                    setcookie('remember_email', $email, time() + 86400 * 30, '/', '', false, false);
                } else {
                    setcookie('remember_token', '', time() - 3600, '/', '', false, false);
                    setcookie('remember_email', '', time() - 3600, '/', '', false, false);
                }

                header('Location: index.php?page=home');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }

    require 'views/login.php';
}

/*  Register  */
function registerCtrl($conn) {
    $error   = '';
    $success = '';
    $old     = ['name' => '', 'email' => '', 'role' => 'user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $old      = compact('name', 'email', 'role');

        // Server side validation
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
                // Redirect to login with flash message as PDF requires
                header('Location: index.php?page=login&msg=registered');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }

    require 'views/register.php';
}

/*  Home  */
function homeCtrl($conn) {
    $posts = [];
    if (isset($_SESSION['user']) && $_SESSION['user']['is_verified']) {
        $posts = getApprovedPosts($conn);
    }
    require 'views/home.php';
}

/* Profile */
function profileCtrl($conn) {
    $error   = '';
    $success = '';
    $userId  = $_SESSION['user']['id'];
    $user    = getUser($conn, $userId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        /* --- Update profile info --- */
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
                // Handle profile picture upload
                if (!empty($_FILES['profile_picture']['name'])) {
                    $file     = $_FILES['profile_picture'];
                    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $maxSize  = 2 * 1024 * 1024; // 2MB

                    if (!in_array($file['type'], $allowed)) {
                        $error = 'Only JPG, PNG, GIF or WEBP images are allowed.';
                    } elseif ($file['size'] > $maxSize) {
                        $error = 'Image must be under 2MB.';
                    } else {
                        $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $fname   = 'user_' . $userId . '_' . time() . '.' . $ext;
                        $dest    = 'public/uploads/profiles/' . $fname;
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
                        $user                        = getUser($conn, $userId);
                        $success                     = 'Profile updated successfully.';
                    } else {
                        $error = 'Update failed. Please try again.';
                    }
                }
            }
        }

        /* Change password  */
        if ($action === 'change_password') {
            $current  = $_POST['current_password'] ?? '';
            $new      = $_POST['new_password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if ($current === '' || $new === '' || $confirm === '') {
                $error = 'All password fields are required.';
            } elseif (!password_verify($current, $user['password_hash'] ?? '')) {
                // Re-fetch with password_hash for verification
                $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);

                if (!$row || !password_verify($current, $row['password_hash'])) {
                    $error = 'Current password is incorrect.';
                }
            }

            if ($error === '') {
                if (strlen($new) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } elseif ($new !== $confirm) {
                    $error = 'New passwords do not match.';
                } else {
                    if (updatePassword($conn, $userId, $new)) {
                        $success = 'Password changed successfully.';
                    } else {
                        $error = 'Failed to change password. Try again.';
                    }
                }
            }
        }
    }

    require 'views/profile.php';
}

/* Wishlist Page */
function wishlistCtrl($conn) {
    $userId   = $_SESSION['user']['id'];
    $wishlist = getWishlist($conn, $userId);
    require 'views/wishlist.php';
}

/*  AJAX - Wishlist Add */
function ajaxWishlistAdd($conn) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $user = $_SESSION['user'];
    if ($user['role'] !== 'user' || !$user['is_verified']) {
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
        echo json_encode(['error' => 'Failed to add to wishlist.']);
    }
    exit;
}

/*  AJAX - Wishlist Remove  */
function ajaxWishlistRemove($conn) {
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
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove from wishlist.']);
    }
    exit;
}
