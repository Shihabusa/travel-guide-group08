<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist &mdash; Travel Guide</title>
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
            <li><a href="index.php?page=wishlist" class="active">&#10084; Wishlist</a></li>
            <li><a href="index.php?page=profile">Profile</a></li>
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
            <h1 class="page-title">&#10084; My Wishlist</h1>
            <p class="page-sub">Your saved travel destinations</p>
        </div>
        <a href="index.php?page=home" class="btn btn-ghost">&#127758; Browse More</a>
    </div>

    <!-- Alert messages -->
    <div id="alertBox"></div>

    <?php if (empty($wishlist)): ?>
        <!-- Empty state -->
        <div class="pending-box">
            <span class="pending-icon">&#10084;</span>
            <h2>Your Wishlist is Empty</h2>
            <p>You haven't saved any destinations yet. Browse and add places you'd love to visit!</p>
            <a href="index.php?page=home" class="btn btn-primary">&#127758; Explore Destinations</a>
        </div>

    <?php else: ?>
        <!-- Wishlist count badge -->
        <div class="card-toolbar" style="border-radius: var(--radius-lg); margin-bottom: 20px;">
            <span style="font-weight:700; color:#0c4a6e; font-size:15px;">
                &#10084; Saved Destinations
            </span>
            <span class="badge" id="wishCount"><?= count($wishlist) ?> saved</span>
        </div>

        <!-- Wishlist items -->
        <div class="card" id="wishlistContainer">
            <?php foreach ($wishlist as $item): ?>
            <div class="wishlist-card" id="wish-<?= $item['id'] ?>">

                <!-- Thumbnail -->
                <div class="wishlist-thumb">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <?php else: ?>
                        &#127958;
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="wishlist-info">
                    <p class="wishlist-title"><?= htmlspecialchars($item['title']) ?></p>
                    <div class="wishlist-meta">
                        <span class="badge-accent">&#127757; <?= htmlspecialchars($item['country']) ?></span>
                        <span class="badge-<?= $item['cost_level'] ?>"><?= ucfirst($item['cost_level']) ?> Cost</span>
                        <span class="badge-accent"><?= ucfirst($item['genre']) ?></span>
                    </div>
                    <p style="font-size:12px; color:var(--text-light); margin-top:6px;">
                        &#128197; Saved on <?= date('M d, Y', strtotime($item['added_at'])) ?>
                    </p>
                </div>

                <!-- Remove button -->
                <button class="btn btn-delete remove-btn"
                        data-id="<?= $item['post_id'] ?>"
                        data-row="wish-<?= $item['id'] ?>"
                        style="flex-shrink:0; padding: 10px 16px; font-size:13px;">
                    &#128465; Remove
                </button>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Explore the world, one destination at a time. &#9992;
</footer>

<!-- AJAX Remove from Wishlist -->
<script>
(function () {
    var alertBox  = document.getElementById('alertBox');
    var wishCount = document.getElementById('wishCount');

    function showAlert(msg, type) {
        alertBox.innerHTML =
            '<div class="alert alert-' + type + '">' + msg + '</div>';
        setTimeout(function () { alertBox.innerHTML = ''; }, 3000);
    }

    function updateCount() {
        if (!wishCount) return;
        var remaining = document.querySelectorAll('.wishlist-card').length;
        wishCount.textContent = remaining + ' saved';

        if (remaining === 0) {
            document.getElementById('wishlistContainer').innerHTML =
                '<div class="pending-box" style="border:none; box-shadow:none;">' +
                '<span class="pending-icon">&#10084;</span>' +
                '<h2>Your Wishlist is Empty</h2>' +
                '<p>Browse destinations and save the ones you love!</p>' +
                '<a href="index.php?page=home" class="btn btn-primary">&#127758; Explore Destinations</a>' +
                '</div>';
            var toolbar = document.querySelector('.card-toolbar');
            if (toolbar) toolbar.style.display = 'none';
        }
    }

    document.querySelectorAll('.remove-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var postId = this.getAttribute('data-id');
            var rowId  = this.getAttribute('data-row');
            var self   = this;

            self.disabled = true;
            self.textContent = 'Removing...';

            fetch('index.php?page=ajax&type=wishlist_remove', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: parseInt(postId) })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    var row = document.getElementById(rowId);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transition = 'opacity .3s ease';
                        setTimeout(function () {
                            row.remove();
                            updateCount();
                        }, 300);
                    }
                    showAlert('&#10003; Removed from wishlist.', 'success');
                } else {
                    self.disabled = false;
                    self.textContent = '&#128465; Remove';
                    showAlert('Failed to remove. Try again.', 'error');
                }
            })
            .catch(function () {
                self.disabled = false;
                self.textContent = '&#128465; Remove';
                showAlert('Something went wrong. Try again.', 'error');
            });
        });
    });
})();
</script>

</body>
</html>