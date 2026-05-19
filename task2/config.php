<?php

$conn = mysqli_connect('localhost', 'root', '', 'travel_guide');
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

$check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($check && mysqli_num_rows($check) === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, is_verified)
         VALUES ('Administrator', 'admin@travel.com', ?, 'admin', 1)");
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
