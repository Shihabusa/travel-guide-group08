<?php

function requireAdmin() {

    if (
        !isset($_SESSION['user']) ||
        $_SESSION['user']['role'] !== 'admin'
    ) {

        header('Location: index.php?page=login');
        exit;
    }
}

function loginCtrl($conn) {

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            !isset($_POST['csrf_token']) ||
            $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')
        ) {

            $error = 'Invalid request. Please try again.';

        } else {

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {

                $error = 'Please fill in both fields.';

            } else {

                $user = authUser($conn, $email, $password);

                if ($user && $user['role'] === 'admin') {

                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id'          => $user['id'],
                        'name'        => $user['name'],
                        'email'       => $user['email'],
                        'role'        => $user['role'],
                        'is_verified' => $user['is_verified'],
                        'picture'     => $user['profile_picture'] ?? null,
                    ];

                    header('Location: index.php?page=dashboard');
                    exit;

                } elseif ($user && $user['role'] !== 'admin') {

                    $error = 'Access denied.';

                } else {

                    $error = 'Invalid email or password.';
                }
            }
        }
    }

    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    require 'views/login.php';
}

function logoutCtrl() {

    $_SESSION = [];

    session_destroy();

    header('Location: index.php?page=login');

    exit;
}

function dashboardCtrl($conn) {

    requireAdmin();

    $stats = getDashboardStats($conn);

    require 'views/dashboard.php';
}

function usersCtrl($conn) {

    requireAdmin();

    $error   = '';
    $success = '';

    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            !isset($_POST['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']
        ) {

            $error = 'Invalid request token.';

        } else {

            $action = $_POST['action'] ?? '';

            if ($action === 'add_user') {

                $name        = trim($_POST['name'] ?? '');
                $email       = trim($_POST['email'] ?? '');
                $password    = $_POST['password'] ?? '';
                $role        = $_POST['role'] ?? 'user';
                $is_verified = isset($_POST['is_verified']) ? 1 : 0;

                if (
                    $name === '' ||
                    $email === '' ||
                    $password === ''
                ) {

                    $error = 'All fields are required.';

                } elseif (
                    !filter_var($email, FILTER_VALIDATE_EMAIL)
                ) {

                    $error = 'Invalid email address.';

                } elseif (
                    strlen($password) < 8
                ) {

                    $error = 'Password must be at least 8 characters.';

                } elseif (
                    !in_array($role, ['scout', 'user'])
                ) {

                    $error = 'Invalid role selected.';

                } elseif (
                    emailExists($conn, $email)
                ) {

                    $error = 'Email already exists.';

                } else {

                    if (
                        addUser(
                            $conn,
                            $name,
                            $email,
                            $password,
                            $role,
                            $is_verified
                        )
                    ) {

                        $success = 'User added successfully.';

                    } else {

                        $error = 'Failed to add user.';
                    }
                }
            }
        }
    }

    $users = getAllUsers($conn);

    require 'views/users.php';
}

function postsCtrl($conn) {

    requireAdmin();

    $error   = '';
    $success = '';

    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            !isset($_POST['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']
        ) {

            $error = 'Invalid request token.';

        } else {

            $action = $_POST['action'] ?? '';

            if ($action === 'edit_post') {

                $id            = intval($_POST['post_id'] ?? 0);
                $title         = trim($_POST['title'] ?? '');
                $shortHistory  = trim($_POST['short_history'] ?? '');
                $country       = trim($_POST['country'] ?? '');
                $genre         = $_POST['genre'] ?? '';
                $costLevel     = $_POST['cost_level'] ?? '';
                $travelMedium  = trim($_POST['travel_medium_info'] ?? '');
                $status        = $_POST['status'] ?? '';

                if (
                    $id <= 0 ||
                    $title === '' ||
                    $shortHistory === '' ||
                    $country === '' ||
                    $travelMedium === ''
                ) {

                    $error = 'All fields are required.';

                } elseif (
                    !in_array($genre, [
                        'beach',
                        'mountain',
                        'city',
                        'historical',
                        'nature',
                        'other'
                    ])
                ) {

                    $error = 'Invalid genre.';

                } elseif (
                    !in_array($costLevel, [
                        'low',
                        'medium',
                        'high'
                    ])
                ) {

                    $error = 'Invalid cost level.';

                } elseif (
                    !in_array($status, [
                        'pending',
                        'approved',
                        'rejected'
                    ])
                ) {

                    $error = 'Invalid status.';

                } else {

                    if (
                        updatePost(
                            $conn,
                            $id,
                            $title,
                            $shortHistory,
                            $country,
                            $genre,
                            $costLevel,
                            $travelMedium,
                            $status
                        )
                    ) {

                        $success = 'Post updated successfully.';

                    } else {

                        $error = 'Failed to update post.';
                    }
                }
            }
        }
    }

    $posts    = getAllPosts($conn);

    $requests = getPendingRequests($conn);

    require 'views/posts.php';
}

function commentsCtrl($conn) {

    requireAdmin();

    $comments = getAllComments($conn);

    require 'views/comments.php';
}

function ajaxHandler($conn) {

    header('Content-Type: application/json');

    if (
        !isset($_SESSION['user']) ||
        $_SESSION['user']['role'] !== 'admin'
    ) {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'error'   => 'Unauthorized'
        ]);

        exit;
    }

    $type = $_GET['type'] ?? '';

    switch ($type) {

        case 'toggle_verify':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $userId = intval($data['user_id'] ?? 0);

            if ($userId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid user ID.'
                ]);

                exit;
            }

            if (toggleVerify($conn, $userId)) {

                $user = getUserById($conn, $userId);

                echo json_encode([
                    'success'     => true,
                    'is_verified' => $user['is_verified']
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to update.'
                ]);
            }

            break;

        case 'delete_user':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $userId  = intval($data['user_id'] ?? 0);

            $adminId = $_SESSION['user']['id'];

            if ($userId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid user ID.'
                ]);

                exit;
            }

            if ((int)$userId === (int)$adminId) {

                http_response_code(403);

                echo json_encode([
                    'success' => false,
                    'error'   => 'You cannot delete your own account.'
                ]);

                exit;
            }

            if (
                deleteUser(
                    $conn,
                    $userId,
                    $adminId
                )
            ) {

                echo json_encode([
                    'success' => true
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to delete user.'
                ]);
            }

            break;

        case 'change_role':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $userId = intval($data['user_id'] ?? 0);

            $newRole = $data['role'] ?? '';

            if (
                $userId <= 0 ||
                !in_array($newRole, ['scout', 'user'])
            ) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid data.'
                ]);

                exit;
            }

            if (
                changeUserRole(
                    $conn,
                    $userId,
                    $newRole
                )
            ) {

                echo json_encode([
                    'success' => true,
                    'role'    => $newRole
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to update role.'
                ]);
            }

            break;

        case 'approve_request':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $requestId = intval($data['request_id'] ?? 0);

            if ($requestId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid request ID.'
                ]);

                exit;
            }

            if (
                approveRequest(
                    $conn,
                    $requestId
                )
            ) {

                echo json_encode([
                    'success' => true,
                    'message' => 'Request approved successfully.'
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to approve request.'
                ]);
            }

            break;

        case 'reject_request':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $requestId = intval($data['request_id'] ?? 0);

            if ($requestId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid request ID.'
                ]);

                exit;
            }

            if (
                rejectRequest(
                    $conn,
                    $requestId
                )
            ) {

                echo json_encode([
                    'success' => true
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to reject request.'
                ]);
            }

            break;

        case 'delete_post':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $postId = intval($data['post_id'] ?? 0);

            if ($postId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid post ID.'
                ]);

                exit;
            }

            if (
                deletePost(
                    $conn,
                    $postId
                )
            ) {

                echo json_encode([
                    'success' => true
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to delete post.'
                ]);
            }

            break;

        case 'delete_comment':

            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            $commentId = intval($data['comment_id'] ?? 0);

            if ($commentId <= 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid comment ID.'
                ]);

                exit;
            }

            if (
                deleteComment(
                    $conn,
                    $commentId
                )
            ) {

                echo json_encode([
                    'success' => true
                ]);

            } else {

                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'error'   => 'Failed to delete comment.'
                ]);
            }

            break;

        default:

            http_response_code(404);

            echo json_encode([
                'success' => false,
                'error'   => 'Unknown AJAX type.'
            ]);
    }

    exit;
}

function registerCtrl($conn) {

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';

        if (
            $name === '' ||
            $email === '' ||
            $password === ''
        ) {

            $error = 'All fields are required.';

        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {

            $error = 'Invalid email address.';

        } elseif (
            strlen($password) < 8
        ) {

            $error = 'Password must be at least 8 characters.';

        } elseif (
            emailExists($conn, $email)
        ) {

            $error = 'Email already exists.';

        } else {

            $ok = addUser(
                $conn,
                $name,
                $email,
                $password,
                $role,
                1
            );

            if ($ok) {

                header('Location: index.php?page=login');

                exit;

            } else {

                $error = 'Registration failed.';
            }
        }
    }

    require 'views/register.php';
}

?>

?>