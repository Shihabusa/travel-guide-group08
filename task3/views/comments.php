<?php $pageTitle = 'Comment Moderation'; require '_navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <div class="page-title">Comment Moderation</div>
            <div class="page-sub">Review and remove inappropriate comments across all posts.</div>
        </div>
        <span class="badge"><?= count($comments) ?> Total Comments</span>
    </div>

    <div class="card">
        <div class="card-toolbar">
            <div class="filter-bar" style="margin:0; flex:1;">
                <input type="text" id="commentSearch" placeholder="Search comments, users or posts..." oninput="filterComments()">
            </div>
        </div>

        <?php if (empty($comments)): ?>
            <div class="empty-state">
                <p>No comments yet.</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table" id="commentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Commenter</th>
                        <th>Post</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comments as $c): ?>
                    <tr id="comment-row-<?= $c['id'] ?>">
                        <td><?= $c['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($c['commenter_name']) ?></strong>
                        </td>
                        <td>
                            <span style="color:#0369a1; font-weight:700; font-size:13px;">
                                <?= htmlspecialchars($c['post_title']) ?>
                            </span>
                        </td>
                        <td style="max-width:320px;">
                            <div style="white-space:pre-wrap; font-size:13px; color:#334155; line-height:1.5; max-height:60px; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($c['content']) ?>
                            </div>
                        </td>
                        <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                            <?= date('M d, Y', strtotime($c['created_at'])) ?><br>
                            <span style="font-size:11px;"><?= date('h:i A', strtotime($c['created_at'])) ?></span>
                        </td>
                        <td>
                            <button class="btn-sm btn-delete delete-comment-btn"
                                    data-comment-id="<?= $c['id'] ?>"
                                    data-commenter="<?= htmlspecialchars($c['commenter_name']) ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="deleteCommentModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('deleteCommentModal')">✕</button>
        <div class="modal-title">Delete Comment</div>
        <p style="color:#64748b; font-size:14px; margin-bottom:14px;">
            Delete comment by <strong id="deleteCommenterName"></strong>? This action cannot be undone.
        </p>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('deleteCommentModal')">Cancel</button>
            <button class="btn btn-primary" id="confirmDeleteCommentBtn"
                    style="background:linear-gradient(135deg,#dc2626,#b91c1c);">Delete</button>
        </div>
    </div>
</div>

<footer class="footer">
    © 2026 Travel Guide — Explore the world, one destination at a time.
</footer>
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.textContent = msg;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

function filterComments() {
    const q = document.getElementById('commentSearch').value.toLowerCase();
    document.querySelectorAll('#commentsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

let pendingDeleteCommentId = null;

document.querySelectorAll('.delete-comment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        pendingDeleteCommentId = this.dataset.commentId;
        document.getElementById('deleteCommenterName').textContent = this.dataset.commenter;
        openModal('deleteCommentModal');
    });
});

document.getElementById('confirmDeleteCommentBtn').addEventListener('click', function() {
    if (!pendingDeleteCommentId) return;
    this.disabled = true;
    this.textContent = 'Deleting...';

    fetch('index.php?page=ajax&type=delete_comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment_id: parseInt(pendingDeleteCommentId) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('comment-row-' + pendingDeleteCommentId);
            if (row) row.remove();
            closeModal('deleteCommentModal');
            showToast('Comment deleted.');
            pendingDeleteCommentId = null;
            this.disabled = false;
            this.textContent = 'Delete';
        } else {
            showToast(data.error || 'Failed to delete.', 'error');
            this.disabled = false;
            this.textContent = 'Delete';
        }
    })
    .catch(() => {
        showToast('Network error.', 'error');
        this.disabled = false;
        this.textContent = 'Delete';
    });
});
</script>
</body>
</html>