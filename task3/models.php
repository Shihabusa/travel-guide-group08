<?php

function authUser($conn, $email, $password) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            name,
            email,
            password_hash,
            role,
            is_verified,
            profile_picture
         FROM users
         WHERE email = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        's',
        $email
    );

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    return (
        $row &&
        password_verify($password, $row['password_hash'])
    )
    ? $row
    : false;
}

function getUserById($conn, $id) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            name,
            email,
            role,
            is_verified,
            profile_picture,
            created_at
         FROM users
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $id
    );

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    return $row ?: false;
}

function emailExists($conn, $email, $excludeId = null) {

    if ($excludeId) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'si',
            $email,
            $excludeId
        );

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            's',
            $email
        );
    }

    mysqli_stmt_execute($stmt);

    mysqli_stmt_store_result($stmt);

    $exists =
        mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $exists;
}

function getDashboardStats($conn) {

    $stats = [];

    $r = mysqli_query(
        $conn,
        "SELECT role, COUNT(*) AS cnt
         FROM users
         GROUP BY role"
    );

    $stats['user_roles'] = [];

    while ($row = mysqli_fetch_assoc($r)) {

        $stats['user_roles'][$row['role']] =
            $row['cnt'];
    }

    $stats['users_total'] =
        array_sum($stats['user_roles']);

    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt
         FROM post_requests
         WHERE status = 'pending'"
    );

    $stats['pending_requests'] =
        mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt
         FROM posts"
    );

    $stats['posts_total'] =
        mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt
         FROM posts
         WHERE status = 'approved'"
    );

    $stats['approved_posts'] =
        mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt
         FROM comments"
    );

    $stats['comments_total'] =
        mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt
         FROM users
         WHERE is_verified = 0
         AND role != 'admin'"
    );

    $stats['unverified_users'] =
        mysqli_fetch_assoc($r)['cnt'];

    return $stats;
}

function getAllUsers($conn) {

    $r = mysqli_query(
        $conn,
        "SELECT
            id,
            name,
            email,
            role,
            is_verified,
            profile_picture,
            created_at
         FROM users
         WHERE role != 'admin'
         ORDER BY created_at DESC"
    );

    return mysqli_fetch_all(
        $r,
        MYSQLI_ASSOC
    );
}

function addUser(
    $conn,
    $name,
    $email,
    $password,
    $role,
    $is_verified
) {

    $hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users
        (
            name,
            email,
            password_hash,
            role,
            is_verified
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'ssssi',
        $name,
        $email,
        $hash,
        $role,
        $is_verified
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function toggleVerify($conn, $userId) {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET is_verified = 1 - is_verified
         WHERE id = ?
         AND role != 'admin'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $userId
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function deleteUser(
    $conn,
    $userId,
    $adminId
) {

    if ((int)$userId === (int)$adminId) {
        return false;
    }

    mysqli_begin_transaction($conn);

    try {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM comments
             WHERE user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $userId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM wishlist
             WHERE user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $userId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM post_requests
             WHERE scout_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $userId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM posts
             WHERE scout_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $userId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM users
             WHERE id = ?
             AND role != 'admin'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $userId
        );

        $ok = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return $ok;

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        return false;
    }
}

function changeUserRole(
    $conn,
    $userId,
    $newRole
) {

    if (
        !in_array($newRole, ['scout', 'user'])
    ) {
        return false;
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET role = ?
         WHERE id = ?
         AND role != 'admin'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'si',
        $newRole,
        $userId
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function getPendingRequests($conn) {

    $r = mysqli_query(
        $conn,
        "SELECT
            pr.*,
            u.name AS scout_name,
            u.email AS scout_email
         FROM post_requests pr
         JOIN users u
         ON u.id = pr.scout_id
         WHERE pr.status = 'pending'
         ORDER BY pr.requested_at DESC"
    );

    return mysqli_fetch_all(
        $r,
        MYSQLI_ASSOC
    );
}

function getAllRequests($conn) {

    $r = mysqli_query(
        $conn,
        "SELECT
            pr.*,
            u.name AS scout_name
         FROM post_requests pr
         JOIN users u
         ON u.id = pr.scout_id
         ORDER BY pr.requested_at DESC"
    );

    return mysqli_fetch_all(
        $r,
        MYSQLI_ASSOC
    );
}

function getRequestById($conn, $id) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            pr.*,
            u.name AS scout_name
         FROM post_requests pr
         JOIN users u
         ON u.id = pr.scout_id
         WHERE pr.id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $id
    );

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    return $row ?: false;
}

function approveRequest($conn, $requestId) {

    $req = getRequestById(
        $conn,
        $requestId
    );

    if (!$req) {
        return false;
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO posts
        (
            scout_id,
            title,
            short_history,
            country,
            genre,
            cost_level,
            travel_medium_info,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'approved'
        )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'issssss',
        $req['scout_id'],
        $req['title'],
        $req['short_history'],
        $req['country'],
        $req['genre'],
        $req['cost_level'],
        $req['travel_medium_info']
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    if (!$ok) {
        return false;
    }

    $stmt2 = mysqli_prepare(
        $conn,
        "DELETE FROM post_requests
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt2,
        'i',
        $requestId
    );

    mysqli_stmt_execute($stmt2);

    mysqli_stmt_close($stmt2);

    return true;
}

function rejectRequest($conn, $requestId) {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE post_requests
         SET status = 'rejected'
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $requestId
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function getAllPosts($conn) {

    $r = mysqli_query(
        $conn,
        "SELECT
            p.*,
            u.name AS scout_name,
            (
                SELECT COUNT(*)
                FROM comments c
                WHERE c.post_id = p.id
            ) AS comment_count
         FROM posts p
         JOIN users u
         ON u.id = p.scout_id
         ORDER BY p.created_at DESC"
    );

    return mysqli_fetch_all(
        $r,
        MYSQLI_ASSOC
    );
}

function getPostById($conn, $id) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            p.*,
            u.name AS scout_name
         FROM posts p
         JOIN users u
         ON u.id = p.scout_id
         WHERE p.id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $id
    );

    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    return $row ?: false;
}

function updatePost(
    $conn,
    $id,
    $title,
    $shortHistory,
    $country,
    $genre,
    $costLevel,
    $travelMediumInfo,
    $status
) {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE posts
         SET
            title = ?,
            short_history = ?,
            country = ?,
            genre = ?,
            cost_level = ?,
            travel_medium_info = ?,
            status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssi',
        $title,
        $shortHistory,
        $country,
        $genre,
        $costLevel,
        $travelMediumInfo,
        $status,
        $id
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function deletePost($conn, $id) {

    mysqli_begin_transaction($conn);

    try {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM comments
             WHERE post_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM wishlist
             WHERE post_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM cost_estimates
             WHERE post_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM posts
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $id
        );

        $ok = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return $ok;

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        return false;
    }
}

function getAllComments($conn) {

    $r = mysqli_query(
        $conn,
        "SELECT
            c.*,
            u.name AS commenter_name,
            p.title AS post_title
         FROM comments c
         JOIN users u
         ON u.id = c.user_id
         JOIN posts p
         ON p.id = c.post_id
         ORDER BY c.created_at DESC"
    );

    return mysqli_fetch_all(
        $r,
        MYSQLI_ASSOC
    );
}

function deleteComment($conn, $commentId) {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM comments
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $commentId
    );

    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

?>