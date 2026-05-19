<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scout Dashboard &mdash; Travel Guide</title>
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
            <li><a href="index.php?page=dashboard" class="active">&#128203; My Requests</a></li>
            <li><a href="index.php?page=approved">&#9989; Approved Posts</a></li>
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
            <h1 class="page-title">&#128203; My Post Requests</h1>
            <p class="page-sub">Manage your submitted travel place requests</p>
        </div>
        <a href="index.php?page=request_form&action=add" class="btn btn-primary">
            &#10010; New Request
        </a>
    </div>

    <!-- Flash messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = [
            'added'    => 'Request submitted successfully!',
            'updated'  => 'Request updated successfully!',
            'deleted'  => 'Request deleted successfully!',
            'no_edit'  => 'Only pending requests can be edited.'
        ]; ?>
        <?php if (isset($msgs[$_GET['msg']])): ?>
            <?php $type = $_GET['msg'] === 'no_edit' ? 'alert-warning' : 'alert-success'; ?>
            <div class="alert <?= $type ?>"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Requests table -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Search by title, country or genre...">
            </div>
            <span class="badge" id="resultCount"><?= count($requests) ?> total</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Country</th>
                        <th>Genre</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="8" class="empty">No requests yet. Submit your first one!</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $i => $req): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($req['title']) ?></td>
                                <td><?= htmlspecialchars($req['country']) ?></td>
                                <td><?= ucfirst($req['genre']) ?></td>
                                <td><span class="badge-<?= $req['cost_level'] ?>"><?= ucfirst($req['cost_level']) ?></span></td>
                                <td><span class="badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($req['requested_at'])) ?></td>
                                <td class="text-right">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a class="btn-sm btn-edit"
                                           href="index.php?page=request_form&action=edit&id=<?= $req['id'] ?>">Edit</a>
                                        <a class="btn-sm btn-delete delete-btn"
                                           href="#"
                                           data-id="<?= $req['id'] ?>"
                                           data-row="row-<?= $req['id'] ?>">Delete</a>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--text-light);">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Scout Dashboard &#128269;
</footer>

<!-- AJAX Search + Delete -->
<script>
(function () {

    /*  Search  */
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function statusBadge(status) {
        return '<span class="badge-' + status + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
    }

    function costBadge(cost) {
        return '<span class="badge-' + cost + '">' + cost.charAt(0).toUpperCase() + cost.slice(1) + '</span>';
    }

    function render(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="empty">No matching results.</td></tr>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (r, i) {
            var actions = '';
            if (r.status === 'pending') {
                actions =
                    '<a class="btn-sm btn-edit" href="index.php?page=request_form&action=edit&id=' + r.id + '">Edit</a>' +
                    '<a class="btn-sm btn-delete delete-btn" href="#" data-id="' + r.id + '" data-row="row-' + r.id + '">Delete</a>';
            } else {
                actions = '<span style="font-size:12px;color:var(--text-light);">No actions</span>';
            }
            html +=
                '<tr id="row-' + r.id + '">' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + esc(r.title) + '</td>' +
                '<td>' + esc(r.country) + '</td>' +
                '<td>' + esc(r.genre) + '</td>' +
                '<td>' + costBadge(r.cost_level) + '</td>' +
                '<td>' + statusBadge(r.status) + '</td>' +
                '<td>' + esc(r.requested_at ? r.requested_at.substring(0,10) : '') + '</td>' +
                '<td class="text-right">' + actions + '</td>' +
                '</tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
        bindDeleteButtons();
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=search_requests&q=' + encodeURIComponent(input.value.trim()),
                  { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 200);
    });

    /* Delete */
    function bindDeleteButtons() {
        document.querySelectorAll('.delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!confirm('Delete this request? This cannot be undone.')) return;

                var id    = this.getAttribute('data-id');
                var rowId = this.getAttribute('data-row');
                var self  = this;

                self.textContent = 'Deleting...';
                self.style.pointerEvents = 'none';

                fetch('index.php?page=ajax&type=delete_request', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(id) })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        var row = document.getElementById(rowId);
                        if (row) {
                            row.style.opacity = '0';
                            row.style.transition = 'opacity .3s ease';
                            setTimeout(function () { row.remove(); }, 300);
                        }
                        var cnt = document.getElementById('resultCount');
                        if (cnt) {
                            var num = parseInt(cnt.textContent) - 1;
                            cnt.textContent = num + ' total';
                        }
                    } else {
                        alert(data.error || 'Failed to delete.');
                        self.textContent = 'Delete';
                        self.style.pointerEvents = '';
                    }
                })
                .catch(function () {
                    alert('Something went wrong.');
                    self.textContent = 'Delete';
                    self.style.pointerEvents = '';
                });
            });
        });
    }

    bindDeleteButtons();
})();
</script>

</body>
</html>
