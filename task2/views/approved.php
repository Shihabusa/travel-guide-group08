<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Approved Posts &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=dashboard">
            <span class="brand-icon">&#128269;</span>
            <span>Travel Guide</span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php?page=dashboard">&#128203; My Requests</a></li>
            <li><a href="index.php?page=approved" class="active">&#9989; Approved Posts</a></li>
            <li><a href="index.php?page=request_form&action=add">&#10010; New Request</a></li>
        </ul>

        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <span class="user-role">Scout</span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">&#9989; My Approved Posts</h1>
            <p class="page-sub">Travel places you submitted that got approved by admin</p>
        </div>
        <a href="index.php?page=request_form&action=add" class="btn btn-primary">
            &#10010; New Request
        </a>
    </div>

    <?php if (empty($posts)): ?>
        <!-- Empty state -->
        <div class="pending-box">
            <span class="pending-icon">&#127758;</span>
            <h2>No Approved Posts Yet</h2>
            <p>None of your submissions have been approved yet. Keep submitting great places!</p>
            <a href="index.php?page=request_form&action=add" class="btn btn-primary">
                &#10010; Submit New Request
            </a>
        </div>

    <?php else: ?>

        <div style="margin-bottom: 20px;">
            <span class="badge"><?= count($posts) ?> approved</span>
        </div>

        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
            <div class="post-card">

                <!-- Image -->
                <div class="post-card-img">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= htmlspecialchars($post['image']) ?>"
                             alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php else: ?>
                        &#127958;
                    <?php endif; ?>
                </div>

                <!-- Body -->
                <div class="post-card-body">
                    <h3 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h3>

                    <div class="post-card-meta">
                        <span class="badge-accent">&#127757; <?= htmlspecialchars($post['country']) ?></span>
                        <span class="badge-<?= $post['cost_level'] ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
                        <span class="badge-approved">&#9989; Approved</span>
                    </div>

                    <p class="post-card-snippet">
                        &#9992; <?= htmlspecialchars($post['travel_medium_info']) ?>
                    </p>

                    <p style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                        &#128197; Approved on <?= date('M d, Y', strtotime($post['created_at'])) ?>
                    </p>

                    <!-- Request changes button -->
                    <div style="margin-top:12px;">
                        <a href="index.php?page=request_form&action=add&original_post_id=<?= $post['id'] ?>"
                           class="btn btn-ghost"
                           style="font-size:12px; padding: 8px 14px;">
                            &#9998; Request Changes
                        </a>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Scout Dashboard &#128269;
</footer>

</body>
</html>
