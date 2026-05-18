<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<!-- Top brand bar -->
<div class="auth-topbar">
    <div class="auth-topbar-logo">
        <span>&#9992;</span>
        Travel Guide
    </div>
</div>

<!-- Centered glass card -->
<div class="auth-shell">
    <div class="auth-card">

        <!-- Brand inside card -->
        <div class="auth-brand">
            <div class="logo-big">&#9992;</div>
            <h1>Travel Guide</h1>
            <p>Join thousands of explorers worldwide</p>
        </div>

        <h2>Create Account</h2>
        <p class="muted">Register to start exploring destinations</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=register" class="form" novalidate id="registerForm">

            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           placeholder="e.g. John Doe" required>
                    <span class="field-error" id="nameErr"></span>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="e.g. john@email.com" required>
                    <span class="field-error" id="emailErr"></span>
                </div>
            </div>

            <div class="field">
                <label for="role">Register As</label>
                <select id="role" name="role">
                    <option value="user"  <?= ($old['role'] ?? '') === 'user'  ? 'selected' : '' ?>>&#127760; General User</option>
                    <option value="scout" <?= ($old['role'] ?? '') === 'scout' ? 'selected' : '' ?>>&#128269; Scout (Place Collector)</option>
                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>&#9881; Admin</option>
                </select>
                <span class="field-error" id="roleErr"></span>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min 8 characters" required>
                    <span class="field-error" id="passErr"></span>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat password" required>
                    <span class="field-error" id="confirmErr"></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account &#9992;</button>
        </form>

        <p class="auth-foot">Already have an account?
            <a href="index.php?page=login">Sign in here</a>
        </p>
        <p class="hint">&#9432; After registration, wait for <strong>admin approval</strong> before logging in.</p>
    </div>
    </div>

<!-- JS Validation -->
<script>
(function () {
    var form    = document.getElementById('registerForm');
    var name    = document.getElementById('name');
    var email   = document.getElementById('email');
    var role    = document.getElementById('role');
    var pass    = document.getElementById('password');
    var confirm = document.getElementById('confirm_password');

    var nameErr    = document.getElementById('nameErr');
    var emailErr   = document.getElementById('emailErr');
    var roleErr    = document.getElementById('roleErr');
    var passErr    = document.getElementById('passErr');
    var confirmErr = document.getElementById('confirmErr');

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

    /* Live validation */
    name.addEventListener('input', function () {
        name.value.trim() === ''
            ? showErr(nameErr, 'Full name is required.')
            : clearErr(nameErr);
    });

    email.addEventListener('input', function () {
        if (email.value.trim() === '') {
            showErr(emailErr, 'Email is required.');
        } else if (!validateEmail(email.value.trim())) {
            showErr(emailErr, 'Please enter a valid email address.');
        } else {
            clearErr(emailErr);
        }
    });

    pass.addEventListener('input', function () {
        if (pass.value === '') {
            showErr(passErr, 'Password is required.');
        } else if (pass.value.length < 8) {
            showErr(passErr, 'Password must be at least 8 characters.');
        } else {
            clearErr(passErr);
        }
        if (confirm.value !== '' && pass.value !== confirm.value) {
            showErr(confirmErr, 'Passwords do not match.');
        } else {
            clearErr(confirmErr);
        }
    });

    confirm.addEventListener('input', function () {
        if (confirm.value === '') {
            showErr(confirmErr, 'Please confirm your password.');
        } else if (confirm.value !== pass.value) {
            showErr(confirmErr, 'Passwords do not match.');
        } else {
            clearErr(confirmErr);
        }
    });

    /* Submit validation */
    form.addEventListener('submit', function (e) {
        var valid = true;

        if (name.value.trim() === '') {
            showErr(nameErr, 'Full name is required.'); valid = false;
        }
        if (email.value.trim() === '') {
            showErr(emailErr, 'Email is required.'); valid = false;
        } else if (!validateEmail(email.value.trim())) {
            showErr(emailErr, 'Please enter a valid email address.'); valid = false;
        }
        if (pass.value === '') {
            showErr(passErr, 'Password is required.'); valid = false;
        } else if (pass.value.length < 8) {
            showErr(passErr, 'Password must be at least 8 characters.'); valid = false;
        }
        if (confirm.value === '') {
            showErr(confirmErr, 'Please confirm your password.'); valid = false;
        } else if (confirm.value !== pass.value) {
            showErr(confirmErr, 'Passwords do not match.'); valid = false;
        }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>