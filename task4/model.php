<?php


function authUser($conn, $email, $password)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, password_hash, role, is_verified, profile_picture
         FROM users WHERE email = ?"
    );
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return ($row && password_verify($password, $row['password_hash'])) ? $row : false;
}

function saveRememberToken($conn, $userId, $token)
{
    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET remember_token = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $hash, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getUserByToken($conn, $token)
{
    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, role, is_verified, profile_picture
         FROM users WHERE remember_token = ?"
    );
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: false;
}

function clearRememberToken($conn, $userId)
{
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET remember_token = NULL WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function emailExists($conn, $email, $excludeId = null)
{
    if ($excludeId) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? AND id != ?"
        );
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function registerUser($conn, $name, $email, $password, $role)
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (name, email, password_hash, role, is_verified)
         VALUES (?, ?, ?, ?, 0)"
    );
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hash, $role);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


function getUser($conn, $id)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, role, is_verified, profile_picture, created_at
         FROM users WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function updateProfile($conn, $id, $name, $email, $picture)
{
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $picture, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updatePassword($conn, $id, $newPassword)
{
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET password_hash = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getWishlist($conn, $userId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT w.id, w.added_at, p.id AS post_id, p.title, p.country,
                p.genre, p.cost_level, p.image
         FROM wishlist w
         JOIN posts p ON p.id = w.post_id
         WHERE w.user_id = ?
         ORDER BY w.added_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function isInWishlist($conn, $userId, $postId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM wishlist WHERE user_id = ? AND post_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function addToWishlist($conn, $userId, $postId)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT IGNORE INTO wishlist (user_id, post_id) VALUES (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function removeFromWishlist($conn, $userId, $postId)
{
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist WHERE user_id = ? AND post_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/* ================================================================
   TASK 1 + TASK 4 – Shared: get all approved posts
   ================================================================ */

function getApprovedPosts($conn)
{
    $r = mysqli_query(
        $conn,
        "SELECT id, title, country, genre, cost_level, short_history, image
         FROM posts WHERE status = 'approved'
         ORDER BY created_at DESC"
    );
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}



function getMyRequests($conn, $scoutId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, country, genre, cost_level, status, requested_at, original_post_id
         FROM post_requests WHERE scout_id = ?
         ORDER BY requested_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getRequest($conn, $id, $scoutId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM post_requests WHERE id = ? AND scout_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function addRequest(
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
) {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO post_requests
         (scout_id, title, short_history, country, genre, cost_level,
          travel_medium_info, image, original_post_id, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'isssssssi',
        $scoutId,
        $title,
        $shortHistory,
        $country,
        $genre,
        $costLevel,
        $travelMedium,
        $image,
        $originalPostId
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateRequest(
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
) {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE post_requests
         SET title = ?, short_history = ?, country = ?, genre = ?,
             cost_level = ?, travel_medium_info = ?, image = ?
         WHERE id = ? AND scout_id = ? AND status = 'pending'"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'sssssssii',
        $title,
        $shortHistory,
        $country,
        $genre,
        $costLevel,
        $travelMedium,
        $image,
        $id,
        $scoutId
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteRequest($conn, $id, $scoutId)
{
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM post_requests WHERE id = ? AND scout_id = ? AND status = 'pending'"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getMyApprovedPosts($conn, $scoutId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, country, genre, cost_level, travel_medium_info, image, created_at
         FROM posts WHERE scout_id = ? AND status = 'approved'
         ORDER BY created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApprovedPost($conn, $id)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM posts WHERE id = ? AND status = 'approved'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function searchMyRequests($conn, $scoutId, $term)
{
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, country, genre, cost_level, status, requested_at
         FROM post_requests
         WHERE scout_id = ? AND (title LIKE ? OR country LIKE ? OR genre LIKE ?)
         ORDER BY requested_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'isss', $scoutId, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}


function getPostById($conn, $id)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT p.*, u.name AS scout_name
         FROM posts p
         JOIN users u ON u.id = p.scout_id
         WHERE p.id = ? AND p.status = 'approved'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function searchPosts($conn, $q)
{
    $like = '%' . mysqli_real_escape_string($conn, $q) . '%';
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, country, genre, cost_level, short_history, image
         FROM posts
         WHERE status = 'approved' AND (title LIKE ? OR country LIKE ?)
         ORDER BY created_at DESC LIMIT 30"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function filterPosts($conn, $country = '', $genres = [], $cost = '')
{
    $sql    = "SELECT id, title, country, genre, cost_level, short_history, image
               FROM posts WHERE status = 'approved'";
    $types  = '';
    $params = [];

    if ($country !== '') {
        $sql    .= " AND country = ?";
        $types  .= 's';
        $params[] = $country;
    }
    if (!empty($genres)) {
        $ph     = implode(',', array_fill(0, count($genres), '?'));
        $sql   .= " AND genre IN ($ph)";
        $types .= str_repeat('s', count($genres));
        foreach ($genres as $g) $params[] = $g;
    }
    if ($cost !== '') {
        $sql    .= " AND cost_level = ?";
        $types  .= 's';
        $params[] = $cost;
    }
    $sql .= " ORDER BY created_at DESC LIMIT 60";

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getDistinctCountries($conn)
{
    $r = mysqli_query(
        $conn,
        "SELECT DISTINCT country FROM posts WHERE status='approved' ORDER BY country"
    );
    return mysqli_fetch_all($r, MYSQLI_COLUMN);
}


function getCommentsByPost($conn, $postId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT c.id, c.content, c.created_at, c.user_id, u.name AS reviewer_name
         FROM comments c
         JOIN users u ON u.id = c.user_id
         WHERE c.post_id = ?
         ORDER BY c.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function addComment($conn, $postId, $userId, $content)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, 'iis', $postId, $userId, $content);
    mysqli_stmt_execute($stmt);
    $newId = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $newId;
}

function deleteComment($conn, $commentId, $userId)
{
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM comments WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $commentId, $userId);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

function getCommentById($conn, $commentId)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM comments WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $commentId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}



function getBaseCost($conn, $postId, $costLevel)
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT base_cost, currency FROM cost_estimates WHERE post_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($row) return $row;
    $map = ['low' => 500, 'medium' => 1500, 'high' => 3000];
    return ['base_cost' => $map[strtolower($costLevel)] ?? 1500, 'currency' => 'USD'];
}
