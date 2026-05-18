<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#9992;</span>
            <span>Travel Guide</span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php?page=home">Home</a></li>
            <?php if ($_SESSION['user']['role'] === 'user'): ?>
            <li><a href="index.php?page=wishlist">&#10084; Wishlist</a></li>
            <?php endif; ?>
            <li><a href="index.php?page=profile" class="active">Profile</a></li>
        </ul>

        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">
                    <?php if (!empty($_SESSION['user']['picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="avatar">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                    <?php endif; ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">&#128100; My Profile</h1>
            <p class="page-sub">Manage your account information and password</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="profile-wrap">

        <!-- Left: Profile picture card -->
        <div class="profile-pic-card">
            <div class="profile-pic-wrap">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
                <?php else: ?>
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <p class="profile-name"><?= htmlspecialchars($user['name']) ?></p>
            <p class="profile-role"><?= ucfirst($user['role']) ?></p>

            <div class="profile-stat">
                <div class="profile-stat-item">
                    <span class="profile-stat-num">
                        <?= $user['is_verified'] ? '&#9989;' : '&#9203;' ?>
                    </span>
                    <span class="profile-stat-lbl">
                        <?= $user['is_verified'] ? 'Verified' : 'Pending' ?>
                    </span>
                </div>
                <div class="profile-stat-item">
                    <span class="profile-stat-num">&#9992;</span>
                    <span class="profile-stat-lbl">Explorer</span>
                </div>
            </div>

            <p style="font-size:11px; color:var(--text-light); margin-top:14px;">
                Member since <?= date('M Y', strtotime($user['created_at'])) ?>
            </p>
        </div>

        <!-- Right: Forms -->
        <div>

            <!-- Update profile info -->
            <div class="card form-card" style="margin-bottom: 24px;">
                <h3 class="card-title">&#9998; Update Profile</h3>
                <form method="POST" action="index.php?page=profile"
                      enctype="multipart/form-data" class="form" novalidate id="profileForm">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="field-row">
                        <div class="field">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name"
                                   value="<?= htmlspecialchars($user['name']) ?>"
                                   placeholder="Your full name" required>
                            <span class="field-error" id="nameErr"></span>
                        </div>
                        <div class="field">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   placeholder="Your email" required>
                            <span class="field-error" id="emailErr"></span>
                        </div>
                    </div>

                    <div class="field">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <span style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                            JPG, PNG, GIF or WEBP. Max 2MB.
                        </span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes &#10003;</button>
                    </div>
                </form>
            </div>

            <!-- Change password -->
            <div class="card form-card">
                <h3 class="card-title">&#128274; Change Password</h3>
                <form method="POST" action="index.php?page=profile"
                      class="form" novalidate id="passForm">
                    <input type="hidden" name="action" value="change_password">

                    <div class="field">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Enter current password" required>
                        <span class="field-error" id="currErr"></span>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password"
                                   placeholder="Min 8 characters" required>
                            <span class="field-error" id="newErr"></span>
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Repeat new password" required>
                            <span class="field-error" id="confirmErr"></span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Change Password &#128274;</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Explore the world, one destination at a time. &#9992;
</footer>

<!-- JS Validation -->
<script>
(function () {
    /* -- Profile form -- */
    var profileForm = document.getElementById('profileForm');
    var name        = document.getElementById('name');
    var email       = document.getElementById('email');
    var nameErr     = document.getElementById('nameErr');
    var emailErr    = document.getElementById('emailErr');

    function validateEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    function showErr(el, msg) {
        el.textContent = msg;
        el.style.display = 'block';
    }

    function clearErr(el) {
        el.textContent = '';
        el.style.display = 'none';
    }

    name.addEventListener('input', function () {
        name.value.trim() === ''
            ? showErr(nameErr, 'Name is required.')
            : clearErr(nameErr);
    });

    email.addEventListener('input', function () {
        if (email.value.trim() === '') {
            showErr(emailErr, 'Email is required.');
        } else if (!validateEmail(email.value.trim())) {
            showErr(emailErr, 'Please enter a valid email.');
        } else {
            clearErr(emailErr);
        }
    });

    profileForm.addEventListener('submit', function (e) {
        var valid = true;
        if (name.value.trim() === '') {
            showErr(nameErr, 'Name is required.'); valid = false;
        }
        if (email.value.trim() === '') {
            showErr(emailErr, 'Email is required.'); valid = false;
        } else if (!validateEmail(email.value.trim())) {
            showErr(emailErr, 'Please enter a valid email.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });

    /* -- Password form -- */
    var passForm   = document.getElementById('passForm');
    var curr       = document.getElementById('current_password');
    var newPass    = document.getElementById('new_password');
    var confirm    = document.getElementById('confirm_password');
    var currErr    = document.getElementById('currErr');
    var newErr     = document.getElementById('newErr');
    var confirmErr = document.getElementById('confirmErr');

    curr.addEventListener('input', function () {
        curr.value === ''
            ? showErr(currErr, 'Current password is required.')
            : clearErr(currErr);
    });

    newPass.addEventListener('input', function () {
        if (newPass.value === '') {
            showErr(newErr, 'New password is required.');
        } else if (newPass.value.length < 8) {
            showErr(newErr, 'Password must be at least 8 characters.');
        } else {
            clearErr(newErr);
        }
        if (confirm.value !== '' && confirm.value !== newPass.value) {
            showErr(confirmErr, 'Passwords do not match.');
        } else {
            clearErr(confirmErr);
        }
    });

    confirm.addEventListener('input', function () {
        if (confirm.value === '') {
            showErr(confirmErr, 'Please confirm your password.');
        } else if (confirm.value !== newPass.value) {
            showErr(confirmErr, 'Passwords do not match.');
        } else {
            clearErr(confirmErr);
        }
    });

    passForm.addEventListener('submit', function (e) {
        var valid = true;
        if (curr.value === '') {
            showErr(currErr, 'Current password is required.'); valid = false;
        }
        if (newPass.value === '') {
            showErr(newErr, 'New password is required.'); valid = false;
        } else if (newPass.value.length < 8) {
            showErr(newErr, 'Password must be at least 8 characters.'); valid = false;
        }
        if (confirm.value === '') {
            showErr(confirmErr, 'Please confirm your password.'); valid = false;
        } else if (confirm.value !== newPass.value) {
            showErr(confirmErr, 'Passwords do not match.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>