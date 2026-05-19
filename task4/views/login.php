<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Travel Guide</title>
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
            <p>Discover breathtaking destinations worldwide</p>
        </div>

        <h2>Welcome Back</h2>
        <p class="muted">Sign in to continue your adventure</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
            <div class="alert alert-success">&#9989; Account created! Please wait for admin approval before logging in.</div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=login" class="form" novalidate id="loginForm">
            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($prefill ?? '') ?>"
                       placeholder="Enter your email" required autofocus>
                <span class="field-error" id="emailErr"></span>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
                <span class="field-error" id="passErr"></span>
            </div>
            <label class="checkbox">
                <input type="checkbox" name="remember" <?= !empty($prefill) ? 'checked' : '' ?>>
                <span>Remember me for 30 days</span>
            </label>
            <button type="submit" class="btn btn-primary btn-block">Sign In &#9992;</button>
        </form>

        <p class="auth-foot">Don't have an account?
            <a href="index.php?page=register">Register here</a>
        </p>
    </div>
    </div>


<!-- JS Validation -->
<script>
(function () {
    var form     = document.getElementById('loginForm');
    var email    = document.getElementById('email');
    var pass     = document.getElementById('password');
    var emailErr = document.getElementById('emailErr');
    var passErr  = document.getElementById('passErr');

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
        pass.value === '' ? showErr(passErr, 'Password is required.') : clearErr(passErr);
    });

    form.addEventListener('submit', function (e) {
        var valid = true;
        if (email.value.trim() === '') {
            showErr(emailErr, 'Email is required.'); valid = false;
        } else if (!validateEmail(email.value.trim())) {
            showErr(emailErr, 'Please enter a valid email address.'); valid = false;
        }
        if (pass.value === '') {
            showErr(passErr, 'Password is required.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>
