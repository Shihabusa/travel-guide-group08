<?php

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'travel_guide'
);

if (!$conn) {

    die(
        'Database connection failed: ' .
        mysqli_connect_error()
    );
}

mysqli_set_charset(
    $conn,
    'utf8mb4'
);

$check = mysqli_query(
    $conn,
    "SELECT id
     FROM users
     WHERE role = 'admin'
     LIMIT 1"
);

if (
    $check &&
    mysqli_num_rows($check) === 0
) {

    $adminName  = 'Administrator';

    $adminEmail = 'admin@travel.com';

    $adminPass  = password_hash(
        'admin123',
        PASSWORD_DEFAULT
    );

    $adminRole  = 'admin';

    $isVerified = 1;

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
        $adminName,
        $adminEmail,
        $adminPass,
        $adminRole,
        $isVerified
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
}

?>