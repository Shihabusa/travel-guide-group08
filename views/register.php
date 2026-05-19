<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-topbar">
    <div class="auth-topbar-logo">
        Travel Guide
    </div>
</div>

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <h1>Travel Guide</h1>
            <p>Join thousands of explorers worldwide</p>
        </div>

        <h2>Create Account</h2>
        <p class="muted">Register to start exploring destinations</p>

        <form class="form" method="POST" action="index.php?page=register" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g. John Doe" required>
                </div>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. john@email.com" autocomplete="email" required>
                </div>
            </div>

            <div class="field">
                <label for="role">Register As</label>
                <select name="role" id="role" required>
                    <option value="user">General User</option>
                    <option value="scout">Scout</option>
                </select>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 8 characters" minlength="8" autocomplete="new-password" required>
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" minlength="8" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Create Account
            </button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="index.php?page=login">Sign in here</a>
        </p>

        <div class="hint">
            After registration, wait for <strong>admin approval</strong> before logging in.
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function (e) {
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const email = document.getElementById('email').value.trim();

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        e.preventDefault();
        return;
    }

    if (password.length < 8) {
        alert('Password must be at least 8 characters long.');
        e.preventDefault();
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match.');
        e.preventDefault();
        return;
    }
});
</script>

<footer class="footer">
    &copy; <?= date('Y') ?>
    Travel Guide &mdash;
    Explore the world, one destination at a time.
</footer>

</body>
</html>