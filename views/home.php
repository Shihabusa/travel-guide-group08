<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home &mdash; Travel Guide</title>
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

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_verified']): ?>
        <ul class="nav-links">
            <li><a href="index.php?page=home" class="active">&#127968; Home</a></li>
            <?php if ($_SESSION['user']['role'] === 'user'): ?>
            <li><a href="index.php?page=wishlist">&#10084; Wishlist</a></li>
            <?php endif; ?>
            <li><a href="index.php?page=profile">&#128100; Profile</a></li>
        </ul>
        <?php endif; ?>

        <div class="nav-user">
            <?php if (isset($_SESSION['user'])): ?>
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
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="main-content">

    <?php if (!isset($_SESSION['user'])): ?>
    <!--  Non-registered users -->
    <div class="hero">
        <span class="hero-icon">&#9992;</span>
        <h1>Explore the World</h1>
        <p>Discover breathtaking destinations, plan your adventure, and get real travel cost estimates — all in one place.</p>
        <div class="hero-btns">
            <a href="index.php?page=register" class="btn btn-accent">Get Started &#127758;</a>
            <a href="index.php?page=login" class="btn btn-ghost">Sign In</a>
        </div>
    </div>

    <div style="text-align:center; padding: 40px 0;">
        <p class="section-sub">&#128274; Register or login to explore destinations, save wishlists and get cost estimates.</p>
    </div>

    <?php elseif (!$_SESSION['user']['is_verified']): ?>
    <!-- Logged in but not verified -->
    <div class="pending-box">
        <span class="pending-icon">&#9203;</span>
        <h2>Account Pending Approval</h2>
        <p>Your account has been created successfully. Please wait for an admin to verify your account before you can access all features.</p>
        <a href="index.php?page=logout" class="btn btn-ghost">Logout</a>
    </div>

    <?php else: ?>
    <!-- Verified users: show posts -->

    <!-- Flash messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = [
            'wishlist_added'   => '&#10084; Added to your wishlist!',
            'wishlist_removed' => 'Removed from wishlist.',
            'wishlist_exists'  => 'Already in your wishlist.'
        ]; ?>
        <?php if (isset($msgs[$_GET['msg']])): ?>
            <div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Hero for verified users -->
    <div class="hero">
        <span class="hero-icon">&#127958;</span>
        <h1>Where to Next, <?= htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0]) ?>?</h1>
        <p>Browse the latest approved destinations handpicked by our scouts around the world.</p>
        <?php if ($_SESSION['user']['role'] === 'user'): ?>
        <div class="hero-btns">
            <a href="index.php?page=wishlist" class="btn btn-accent">&#10084; My Wishlist</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Posts grid -->
    <?php if (empty($posts)): ?>
        <div class="pending-box">
            <span class="pending-icon">&#127757;</span>
            <h2>No Destinations Yet</h2>
            <p>Our scouts are out collecting amazing travel destinations. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="page-header">
            <div>
                <h2 class="section-title">&#127758; Latest Destinations</h2>
                <p class="section-sub"><?= count($posts) ?> approved destinations available</p>
            </div>
        </div>

        <div class="posts-grid" id="postsGrid">
            <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <div class="post-card-img">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php else: ?>
                        &#127958;
                    <?php endif; ?>
                </div>
                <div class="post-card-body">
                    <h3 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <div class="post-card-meta">
                        <span class="badge-accent">&#127757; <?= htmlspecialchars($post['country']) ?></span>
                        <span class="badge-<?= $post['cost_level'] ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
                        <span class="badge-accent"><?= ucfirst($post['genre']) ?></span>
                    </div>
                    <p class="post-card-snippet"><?= htmlspecialchars($post['short_history']) ?></p>

                    <?php if ($_SESSION['user']['role'] === 'user'): ?>
                    <button class="btn btn-accent btn-block wishlist-btn"
                            data-id="<?= $post['id'] ?>"
                            style="font-size:13px; padding: 10px;">
                        &#10084; Add to Wishlist
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php endif; ?>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Explore the world, one destination at a time. &#9992;
</footer>

<!-- AJAX Wishlist -->
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user' && $_SESSION['user']['is_verified']): ?>
<script>
(function () {
    document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var postId = this.getAttribute('data-id');
            var self   = this;

            self.disabled = true;
            self.textContent = 'Adding...';

            fetch('index.php?page=ajax&type=wishlist_add', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: parseInt(postId) })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    self.textContent = '&#10004; Saved!';
                    self.style.background = '#16a34a';
                } else {
                    self.textContent = '&#10084; Already Saved';
                    self.disabled = false;
                }
            })
            .catch(function () {
                self.textContent = '&#10084; Add to Wishlist';
                self.disabled = false;
            });
        });
    });
})();
</script>
<?php endif; ?>

</body>
</html>
