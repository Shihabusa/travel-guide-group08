<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-topbar">
    <div class="auth-topbar-logo">
        <span>🌍</span>
        Travel Guide
    </div>
</div>

<div class="auth-shell">
    <div class="auth-card">

        <div class="auth-brand">
            <div class="logo-big">✈</div>
            <h1>Travel Guide</h1>
            <p>Discover breathtaking destinations worldwide</p>
        </div>

<h2>Welcome Back</h2>
<p class="muted">Sign in to continue your adventure</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" role="alert">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out'): ?>
            <div class="alert alert-success" role="alert">✅ You have been logged out successfully.</div>
        <?php endif; ?>

        <form class="form" id="loginForm" method="POST" action="index.php?page=login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                <span class="field-error" id="emailErr">Please enter a valid email.</span>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password"
                       required autocomplete="current-password">
                <span class="field-error" id="passErr">Password is required.</span>
            </div>

            <div class="checkbox-row">
<input type="checkbox" id="remember" name="remember">
<label for="remember">Remember me for 30 days</label>            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In ✈</button>
        </form>

<p class="auth-switch">
    Don't have an account?
<a href="index.php?page=register">Register here</a></p>

    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;

    const email = document.getElementById('email');
    const emailErr = document.getElementById('emailErr');
    const pass = document.getElementById('password');
    const passErr = document.getElementById('passErr');

    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        emailErr.style.display = 'block';
        email.style.borderColor = '#dc2626';
        valid = false;
    } else {
        emailErr.style.display = 'none';
        email.style.borderColor = '';
    }

    // Validate password
    if (pass.value.trim() === '') {
        passErr.style.display = 'block';
        pass.style.borderColor = '#dc2626';
        valid = false;
    } else {
        passErr.style.display = 'none';
        pass.style.borderColor = '';
    }

    if (!valid) e.preventDefault();
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?>
    Travel Guide &mdash;
    Explore the world, one destination at a time. ✈️
</footer>
</body>
</html>
