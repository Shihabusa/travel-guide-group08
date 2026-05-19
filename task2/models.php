<?php


/*  Auth  */
function authUser($conn, $email, $password) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, password_hash, role, is_verified, profile_picture
         FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return ($row && password_verify($password, $row['password_hash'])) ? $row : false;
}

/* Remember Me  */
function saveRememberToken($conn, $userId, $token) {
    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET remember_token = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getUserByToken($conn, $token) {
    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, role, is_verified, profile_picture
         FROM users WHERE remember_token = ?");
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: false;
}

function clearRememberToken($conn, $userId) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET remember_token = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/* Registration */
function emailExists($conn, $email, $excludeId = null) {
    if ($excludeId) {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function registerUser($conn, $name, $email, $password, $role) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, is_verified)
         VALUES (?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hash, $role);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/*  Post Requests  */
function getMyRequests($conn, $scoutId) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, title, country, genre, cost_level, status, requested_at, original_post_id
         FROM post_requests WHERE scout_id = ?
         ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getRequest($conn, $id, $scoutId) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM post_requests WHERE id = ? AND scout_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function addRequest($conn, $scoutId, $title, $shortHistory, $country, $genre, $costLevel, $travelMedium, $image, $originalPostId) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO post_requests
         (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image, original_post_id, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, 'isssssssi',
        $scoutId, $title, $shortHistory, $country, $genre,
        $costLevel, $travelMedium, $image, $originalPostId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateRequest($conn, $id, $scoutId, $title, $shortHistory, $country, $genre, $costLevel, $travelMedium, $image) {
    $stmt = mysqli_prepare($conn,
        "UPDATE post_requests
         SET title = ?, short_history = ?, country = ?, genre = ?,
             cost_level = ?, travel_medium_info = ?, image = ?
         WHERE id = ? AND scout_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'sssssssii',
        $title, $shortHistory, $country, $genre,
        $costLevel, $travelMedium, $image, $id, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteRequest($conn, $id, $scoutId) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM post_requests WHERE id = ? AND scout_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/*  Approved Posts */
function getMyApprovedPosts($conn, $scoutId) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, title, country, genre, cost_level, travel_medium_info, image, created_at
         FROM posts WHERE scout_id = ? AND status = 'approved'
         ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApprovedPost($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM posts WHERE id = ? AND status = 'approved'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

/* Search Requests */
function searchMyRequests($conn, $scoutId, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT id, title, country, genre, cost_level, status, requested_at
         FROM post_requests
         WHERE scout_id = ? AND (title LIKE ? OR country LIKE ? OR genre LIKE ?)
         ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'isss', $scoutId, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}
?>
