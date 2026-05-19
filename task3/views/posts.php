<?php
$pageTitle = 'Post Moderation';
require '_navbar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <div class="page-title">Post Moderation</div>
            <div class="page-sub">Review scout requests, approve or reject submissions, and manage published posts.</div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="tab-nav">
        <button class="tab-btn active" data-tab="requests">
            Pending Requests
            <?php if (count($requests) > 0): ?>
                <span class="tab-count"><?= count($requests) ?></span>
            <?php endif; ?>
        </button>

        <button class="tab-btn" data-tab="published">
            All Posts (<?= count($posts) ?>)
        </button>
    </div>

    <div class="tab-panel active" id="tab-requests">
        <div class="card">
            <div class="card-toolbar">
                <span class="badge">
                    <?= count($requests) ?> Pending
                </span>

                <div class="filter-bar" style="margin:0; flex:1;">
                    <input
                        type="text"
                        placeholder="Search requests..."
                        oninput="filterTable(this, 'requestsTable')"
                    >
                </div>
            </div>

            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <p>No pending requests. All caught up.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table" id="requestsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Scout</th>
                                <th>Title</th>
                                <th>Country</th>
                                <th>Genre</th>
                                <th>Cost</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= (int)$req['id'] ?></td>
                                <td><?= htmlspecialchars($req['scout_name']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($req['title']) ?></strong>
                                    <?php if (!empty($req['original_post_id'])): ?>
                                        <br>
                                        <small style="color:#d97706;">
                                            Change request for post #<?= (int)$req['original_post_id'] ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($req['country']) ?></td>
                                <td>
                                    <span class="badge-accent">
                                        <?= ucfirst(htmlspecialchars($req['genre'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-<?= htmlspecialchars($req['cost_level']) ?>">
                                        <?= ucfirst(htmlspecialchars($req['cost_level'])) ?>
                                    </span>
                                </td>
                                <td style="font-size:12px;color:#64748b;">
                                    <?= date('M d, Y', strtotime($req['requested_at'])) ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <button
                                            class="btn-sm btn-edit preview-req-btn"
                                            data-req='<?= htmlspecialchars(json_encode($req), ENT_QUOTES) ?>'
                                        >
                                            View
                                        </button>

                                        <button
                                            class="btn-sm btn-approve approve-req-btn"
                                            data-req-id="<?= (int)$req['id'] ?>"
                                            data-title="<?= htmlspecialchars($req['title']) ?>"
                                        >
                                            Approve
                                        </button>

                                        <button
                                            class="btn-sm btn-reject reject-req-btn"
                                            data-req-id="<?= (int)$req['id'] ?>"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-panel" id="tab-published">
        <div class="card">
            <div class="card-toolbar">
                <span class="badge">
                    <?= count($posts) ?> Posts
                </span>

                <div class="filter-bar" style="margin:0; flex:1;">
                    <input
                        type="text"
                        placeholder="Search posts..."
                        oninput="filterTable(this, 'postsTable')"
                    >

                    <select onchange="filterByStatus(this)">
                        <option value="">All Statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <p>No posts found.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table" id="postsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Scout</th>
                                <th>Country</th>
                                <th>Genre</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr data-status="<?= htmlspecialchars($post['status']) ?>">
                                <td><?= (int)$post['id'] ?></td>
                                <td><strong><?= htmlspecialchars($post['title']) ?></strong></td>
                                <td><?= htmlspecialchars($post['scout_name']) ?></td>
                                <td><?= htmlspecialchars($post['country']) ?></td>
                                <td>
                                    <span class="badge-accent">
                                        <?= ucfirst(htmlspecialchars($post['genre'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-<?= htmlspecialchars($post['cost_level']) ?>">
                                        <?= ucfirst(htmlspecialchars($post['cost_level'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($post['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($post['status'])) ?>
                                    </span>
                                </td>
                                <td><?= (int)($post['comment_count'] ?? 0) ?></td>
                                <td>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <button
                                            class="btn-sm btn-delete delete-post-btn"
                                            data-post-id="<?= (int)$post['id'] ?>"
                                            data-title="<?= htmlspecialchars($post['title']) ?>"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div class="modal-overlay" id="previewReqModal">
    <div class="modal" style="max-width:640px;">
        <button class="modal-close" onclick="closeModal('previewReqModal')">✕</button>
        <div class="modal-title">Request Details</div>
        <div id="reqPreviewContent" style="font-size:14px;color:#1e293b;"></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('previewReqModal')">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="approveModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('approveModal')">✕</button>
        <div class="modal-title">Approve Request</div>
        <p style="color:#64748b; font-size:14px; margin-bottom:16px;">
            Approve "<strong id="approveTitle"></strong>"?
        </p>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('approveModal')">Cancel</button>
            <button class="btn btn-primary" id="confirmApproveBtn">Approve and Publish</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="rejectModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('rejectModal')">✕</button>
        <div class="modal-title">Reject Request</div>
        <div class="field">
            <label>Reason for rejection (optional)</label>
            <textarea id="rejectReason" rows="3" placeholder="Enter reason..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('rejectModal')">Cancel</button>
            <button
                class="btn btn-primary"
                id="confirmRejectBtn"
                style="background:linear-gradient(135deg,#dc2626,#b91c1c);"
            >
                Reject
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deletePostModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('deletePostModal')">✕</button>
        <div class="modal-title">Delete Post</div>
        <p style="color:#64748b; font-size:14px; margin-bottom:16px;">
            Are you sure you want to delete "<strong id="deletePostTitle"></strong>"?
        </p>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('deletePostModal')">Cancel</button>
            <button
                class="btn btn-primary"
                id="confirmDeletePostBtn"
                style="background:linear-gradient(135deg,#dc2626,#b91c1c);"
            >
                Delete
            </button>
        </div>
    </div>
</div>

<footer class="footer">
    © 2026 Travel Guide — Explore the world, one destination at a time.
</footer>

<div id="toast-container"></div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.textContent = msg;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => {
        t.remove();
    }, 3500);
}

function filterTable(input, tableId) {
    const q = input.value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function filterByStatus(sel) {
    const val = sel.value;
    document.querySelectorAll('#postsTable tbody tr').forEach(row => {
        row.style.display = val === '' || row.dataset.status === val ? '' : 'none';
    });
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

document.querySelectorAll('.preview-req-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const req = JSON.parse(this.dataset.req);

        document.getElementById('reqPreviewContent').innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <strong>Title:</strong><br>
                    ${req.title}
                </div>
                <div>
                    <strong>Country:</strong><br>
                    ${req.country}
                </div>
                <div>
                    <strong>Genre:</strong><br>
                    ${req.genre}
                </div>
                <div>
                    <strong>Cost Level:</strong><br>
                    ${req.cost_level}
                </div>
                <div>
                    <strong>Travel Medium:</strong><br>
                    ${req.travel_medium_info}
                </div>
                <div>
                    <strong>Scout:</strong><br>
                    ${req.scout_name}
                </div>
            </div>
            <div>
                <strong>Short History:</strong>
            </div>
            <div style="background:#f0f9ff;border-radius:10px;padding:12px;margin-top:8px;font-size:13px;color:#1e293b;line-height:1.6;">
                ${req.short_history}
            </div>
        `;

        openModal('previewReqModal');
    });
});

let pendingApproveId = null;

document.querySelectorAll('.approve-req-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        pendingApproveId = this.dataset.reqId;
        document.getElementById('approveTitle').textContent = this.dataset.title;
        openModal('approveModal');
    });
});

document.getElementById('confirmApproveBtn')?.addEventListener('click', function () {
    if (!pendingApproveId) return;

    fetch('index.php?page=ajax&type=approve_request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: parseInt(pendingApproveId)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('approveModal');
            showToast('Request approved successfully!');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showToast(data.error || 'Failed.', 'error');
        }
    });
});

let pendingRejectId = null;

document.querySelectorAll('.reject-req-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        pendingRejectId = this.dataset.reqId;
        openModal('rejectModal');
    });
});

document.getElementById('confirmRejectBtn')?.addEventListener('click', function () {
    if (!pendingRejectId) return;

    const reason = document.getElementById('rejectReason').value.trim();

    fetch('index.php?page=ajax&type=reject_request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: parseInt(pendingRejectId),
            reason: reason
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('rejectModal');
            showToast('Request rejected.');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showToast(data.error || 'Failed.', 'error');
        }
    });
});

let pendingDeletePostId = null;

document.querySelectorAll('.delete-post-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        pendingDeletePostId = this.dataset.postId;
        document.getElementById('deletePostTitle').textContent = this.dataset.title;
        openModal('deletePostModal');
    });
});

document.getElementById('confirmDeletePostBtn')?.addEventListener('click', function () {
    if (!pendingDeletePostId) return;

    fetch('index.php?page=ajax&type=delete_post', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            post_id: parseInt(pendingDeletePostId)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Post deleted.');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showToast(data.error || 'Failed.', 'error');
        }
    });
});
</script>

</body>
</html>