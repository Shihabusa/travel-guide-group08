<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> – Travel Guide Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #fff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-icon.blue { background: #e0f2fe; }
        .stat-icon.green { background: #dcfce7; }
        .stat-icon.amber { background: #fef3c7; }
        .stat-icon.red { background: #fee2e2; }
        .stat-icon.purple { background: #f3e8ff; }
        .stat-icon.teal { background: #ccfbf1; }
        .stat-num { font-size: 26px; font-weight: 900; color: #0c4a6e; line-height: 1; }
        .stat-lbl { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .data-table th {
            background: #f0f9ff;
            color: #0c4a6e;
            font-weight: 800;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 2px solid #bae6fd;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #e0f2fe;
            vertical-align: middle;
            color: #1e293b;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fbff; }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-sm:hover { transform: translateY(-1px); }
        .btn-approve { background: #dcfce7; color: #15803d; }
        .btn-approve:hover { background: #bbf7d0; }
        .btn-reject { background: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #fecaca; }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-edit:hover { background: #bae6fd; }
        .btn-delete { background: #fee2e2; color: #b91c1c; }
        .btn-delete:hover { background: #fecaca; }
        .btn-verify { background: #fef3c7; color: #d97706; }
        .btn-verify:hover { background: #fde68a; }
        .btn-verified { background: #dcfce7; color: #15803d; }
        .btn-verified:hover { background: #bbf7d0; }

        .status-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #b91c1c; }

        .role-badge {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .role-scout { background: #e0f2fe; color: #0369a1; }
        .role-user { background: #f3e8ff; color: #7c3aed; }
        .role-admin { background: #0c4a6e; color: #fff; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(12, 74, 110, .45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 560px;
            width: 100%;
            padding: 32px 30px;
            position: relative;
            animation: fadeUp .25s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color .2s;
        }
        .modal-close:hover { color: #dc2626; }
        .modal-title {
            font-size: 19px;
            font-weight: 850;
            color: #0c4a6e;
            margin-bottom: 18px;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        #toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            padding: 13px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            animation: slideUp .3s ease;
            min-width: 260px;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .toast-success { background: #dcfce7; color: #15803d; border-left: 4px solid #16a34a; }
        .toast-error { background: #fee2e2; color: #b91c1c; border-left: 4px solid #dc2626; }

        .tab-nav {
            display: flex;
            gap: 4px;
            margin-bottom: 20px;
            border-bottom: 2px solid #bae6fd;
            padding-bottom: 0;
        }
        .tab-btn {
            padding: 10px 18px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .2s;
            margin-bottom: -2px;
        }
        .tab-btn.active {
            color: #0369a1;
            border-bottom-color: #0369a1;
        }
        .tab-btn:hover {
            color: #0369a1;
        }
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-muted);
        }
        .empty-state .empty-icon {
            font-size: 42px;
            display: block;
            margin-bottom: 12px;
        }
        .empty-state p {
            font-size: 14px;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input,
        .filter-bar select {
            padding: 9px 13px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            color: var(--text);
            transition: var(--transition);
        }
        .filter-bar input:focus,
        .filter-bar select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(3, 105, 161, .1);
        }
        .filter-bar input {
            flex: 1;
            min-width: 200px;
        }
    </style>
</head>
<body class="app-body">

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php?page=dashboard" class="brand">
            Travel Guide
        </a>

        <ul class="nav-links">
            <li><a href="index.php?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="index.php?page=users" class="<?= $currentPage === 'users' ? 'active' : '' ?>">Users</a></li>
            <li><a href="index.php?page=posts" class="<?= $currentPage === 'posts' ? 'active' : '' ?>">Posts</a></li>
            <li><a href="index.php?page=comments" class="<?= $currentPage === 'comments' ? 'active' : '' ?>">Comments</a></li>
        </ul>

        <div class="nav-user">
            <div class="user-pill">
                <div class="user-avatar">
                    <?php if (!empty($_SESSION['user']['picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['user']['name'] ?? 'A', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            <a href="index.php?page=logout" class="btn-logout">Log Out</a>
        </div>
    </div>
</nav>

<div id="toast-container"></div>