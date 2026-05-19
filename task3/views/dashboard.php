<?php $pageTitle = 'Dashboard'; require '_navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <div class="page-title">Admin Dashboard</div>
            <div class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?>! Here's your site overview.</div>
        </div>
        <a href="index.php?page=posts" class="btn btn-primary">Moderate Posts</a>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['users_total'] ?></div>
                <div class="stat-lbl">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['pending_requests'] ?></div>
                <div class="stat-lbl">Pending Requests</div>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['posts_total'] ?></div>
                <div class="stat-lbl">Total Posts</div>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['approved_posts'] ?></div>
                <div class="stat-lbl">Approved Posts</div>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['comments_total'] ?></div>
                <div class="stat-lbl">Comments</div>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-num"><?= $stats['unverified_users'] ?></div>
                <div class="stat-lbl">Awaiting Verification</div>
            </div>
        </div>
    </div>

    <div class="card form-card" style="margin-bottom:24px;">
        <div class="card-title">Users by Role</div>
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <?php foreach (['scout' => 'Scout', 'user' => 'User'] as $role => $label): ?>
            <div style="flex:1; min-width:140px; background:#f0f9ff; border-radius:14px; padding:18px 16px; text-align:center; border:1px solid #bae6fd;">
                <div style="font-size:24px; font-weight:900; color:#0c4a6e;"><?= $stats['user_roles'][$role] ?? 0 ?></div>
                <div style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.4px;"><?= $label ?>s</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(230px,1fr)); gap:16px;">
        <a href="index.php?page=users" style="text-decoration:none;">
            <div class="stat-card" style="cursor:pointer; transition:all .2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                <div>
                    <div style="font-weight:800; color:#0c4a6e;">Manage Users</div>
                    <div style="font-size:12px; color:#64748b;">Add, verify, delete users</div>
                </div>
            </div>
        </a>
        <a href="index.php?page=posts" style="text-decoration:none;">
            <div class="stat-card" style="cursor:pointer; transition:all .2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                <div>
                    <div style="font-weight:800; color:#0c4a6e;">Moderate Posts</div>
                    <div style="font-size:12px; color:#64748b;">Approve or reject requests</div>
                </div>
            </div>
        </a>
        <a href="index.php?page=comments" style="text-decoration:none;">
            <div class="stat-card" style="cursor:pointer; transition:all .2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                <div>
                    <div style="font-weight:800; color:#0c4a6e;">Comments</div>
                    <div style="font-size:12px; color:#64748b;">Review and remove comments</div>
                </div>
            </div>
        </a>
    </div>
</main>

<footer class="footer">
    © 2026 Travel Guide — Explore the world, one destination at a time.
</footer>

</body>
</html>