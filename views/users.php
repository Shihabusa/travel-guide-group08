<?php $pageTitle = 'User Management'; require '_navbar.php'; ?>

<main class="main-content">

    <div class="page-header">

        <div>
            <div class="page-title">User Management</div>
            <div class="page-sub">Add, verify, change roles, and remove users.</div>
        </div>

        <button class="btn btn-primary" onclick="openModal('addUserModal')">
            Add New User
        </button>

    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card">

        <div class="card-toolbar">

            <span class="badge"><?= count($users) ?> Users</span>

            <div class="filter-bar" style="margin:0; flex:1;">

                <input type="text"
                       id="userSearch"
                       placeholder="Search by name or email..."
                       oninput="filterUsers()">

                <select id="roleFilter" onchange="filterUsers()">
                    <option value="">All Roles</option>
                    <option value="scout">Scout</option>
                    <option value="user">User</option>
                </select>

                <select id="verifyFilter" onchange="filterUsers()">
                    <option value="">All Status</option>
                    <option value="1">Verified</option>
                    <option value="0">Unverified</option>
                </select>

            </div>

        </div>

        <div style="overflow-x:auto;">

            <table class="data-table" id="usersTable">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($users)): ?>

                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <p>No users found.</p>
                            </div>
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($users as $u): ?>

                    <tr data-name="<?= strtolower(htmlspecialchars($u['name'])) ?>"
                        data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>"
                        data-role="<?= $u['role'] ?>"
                        data-verified="<?= $u['is_verified'] ?>">

                        <td><?= $u['id'] ?></td>

                        <td>

                            <div style="display:flex; align-items:center; gap:9px;">

                                <div style="
                                    width:34px;
                                    height:34px;
                                    border-radius:50%;
                                    background:linear-gradient(135deg,#0c4a6e,#38bdf8);
                                    color:#fff;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    font-weight:900;
                                    font-size:13px;
                                    flex-shrink:0;
                                ">

                                    <?php if (!empty($u['profile_picture'])): ?>

                                        <img src="<?= htmlspecialchars($u['profile_picture']) ?>"
                                             style="
                                                width:100%;
                                                height:100%;
                                                object-fit:cover;
                                                border-radius:50%;
                                             ">

                                    <?php else: ?>

                                        <?= strtoupper(substr($u['name'],0,1)) ?>

                                    <?php endif; ?>

                                </div>

                                <strong><?= htmlspecialchars($u['name']) ?></strong>

                            </div>

                        </td>

                        <td><?= htmlspecialchars($u['email']) ?></td>

                        <td>

                            <select class="role-select"
                                    data-user-id="<?= $u['id'] ?>"
                                    style="
                                        padding:5px 8px;
                                        border:1.5px solid #bae6fd;
                                        border-radius:8px;
                                        font-size:12px;
                                        font-weight:700;
                                        cursor:pointer;
                                        background:#fff;
                                        color:#0c4a6e;
                                    ">

                                <option value="scout"
                                    <?= $u['role'] === 'scout' ? 'selected' : '' ?>>
                                    Scout
                                </option>

                                <option value="user"
                                    <?= $u['role'] === 'user' ? 'selected' : '' ?>>
                                    User
                                </option>

                            </select>

                        </td>

                        <td>

                            <button class="btn-sm <?= $u['is_verified'] ? 'btn-verified' : 'btn-verify' ?> verify-btn"
                                    data-user-id="<?= $u['id'] ?>"
                                    data-verified="<?= $u['is_verified'] ?>">

                                <?= $u['is_verified'] ? 'Verified' : 'Unverified' ?>

                            </button>

                        </td>

                        <td style="font-size:13px; color:#64748b;">
                            <?= date('M d, Y', strtotime($u['created_at'])) ?>
                        </td>

                        <td>

                            <button class="btn-sm btn-delete delete-user-btn"
                                    data-user-id="<?= $u['id'] ?>"
                                    data-user-name="<?= htmlspecialchars($u['name']) ?>">

                                Delete

                            </button>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<div class="modal-overlay" id="addUserModal">

    <div class="modal">

        <button class="modal-close"
                onclick="closeModal('addUserModal')">
            ×
        </button>

        <div class="modal-title">Add New User</div>

        <form class="form"
              id="addUserForm"
              method="POST"
              action="index.php?page=users"
              novalidate>

            <input type="hidden" name="action" value="add_user">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="field-row">

                <div class="field">

                    <label>Full Name *</label>

                    <input type="text"
                           name="name"
                           id="au_name"
                           placeholder="John Doe"
                           required>

                    <span class="field-error" id="au_nameErr">
                        Name is required.
                    </span>

                </div>

                <div class="field">

                    <label>Email Address *</label>

                    <input type="email"
                           name="email"
                           id="au_email"
                           placeholder="john@email.com"
                           autocomplete="email"
                           required>

                    <span class="field-error" id="au_emailErr">
                        Valid email required.
                    </span>

                </div>

            </div>

            <div class="field-row">

                <div class="field">

                    <label>Password *</label>

                    <input type="password"
                           name="password"
                           id="au_pass"
                           autocomplete="new-password">

                    <span class="field-error" id="au_passErr">
                        Minimum 8 characters required.
                    </span>

                </div>

                <div class="field">

                    <label>Role *</label>

                    <select name="role" id="au_role">
                        <option value="user">General User</option>
                        <option value="scout">Scout</option>
                    </select>

                </div>

            </div>

            <label class="checkbox">

                <input type="checkbox"
                       name="is_verified"
                       value="1"
                       checked>

                Mark as verified immediately

            </label>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-ghost"
                        onclick="closeModal('addUserModal')">

                    Cancel

                </button>

                <button type="submit"
                        class="btn btn-primary">

                    Add User

                </button>

            </div>

        </form>

    </div>

</div>

<div class="modal-overlay" id="deleteUserModal">

    <div class="modal">

        <button class="modal-close"
                onclick="closeModal('deleteUserModal')">
            ×
        </button>

        <div class="modal-title">Delete User</div>

        <p style="
            color:#64748b;
            font-size:14px;
            margin-bottom:14px;
        ">

            Are you sure you want to delete
            <strong id="deleteUserName"></strong>?

        </p>

        <div style="
            background:#fee2e2;
            border-radius:10px;
            padding:12px 14px;
            font-size:13px;
            color:#b91c1c;
            margin-bottom:16px;
            border-left:4px solid #dc2626;
        ">

            This action cannot be undone.

        </div>

        <div class="modal-footer">

            <button class="btn btn-ghost"
                    onclick="closeModal('deleteUserModal')">

                Cancel

            </button>

            <button class="btn btn-primary"
                    id="confirmDeleteUserBtn"
                    style="background:linear-gradient(135deg,#dc2626,#b91c1c);">

                Delete

            </button>

        </div>

    </div>

</div>

<footer class="footer">
    © 2026 Travel Guide — Explore the world, one destination at a time.
</footer>

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

function filterUsers() {

    const q    = document.getElementById('userSearch').value.toLowerCase();
    const role = document.getElementById('roleFilter').value;
    const ver  = document.getElementById('verifyFilter').value;

    document.querySelectorAll('#usersTable tbody tr').forEach(row => {

        const name    = row.dataset.name || '';
        const email   = row.dataset.email || '';
        const rowRole = row.dataset.role || '';
        const rowVer  = row.dataset.verified || '';

        const matchQ    = name.includes(q) || email.includes(q);
        const matchRole = role === '' || rowRole === role;
        const matchVer  = ver === '' || rowVer === ver;

        row.style.display =
            (matchQ && matchRole && matchVer)
            ? ''
            : 'none';
    });
}

document.querySelectorAll('.verify-btn').forEach(btn => {

    btn.addEventListener('click', function() {

        const userId = this.dataset.userId;

        fetch('index.php?page=ajax&type=toggle_verify', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({
                user_id: parseInt(userId)
            })

        })

        .then(r => r.json())

        .then(data => {

            if (data.success) {

                const isVerified = data.is_verified == 1;

                this.textContent =
                    isVerified
                    ? 'Verified'
                    : 'Unverified';

                this.className =
                    'btn-sm ' +
                    (isVerified ? 'btn-verified' : 'btn-verify') +
                    ' verify-btn';

                this.dataset.verified = data.is_verified;

                this.closest('tr').dataset.verified = data.is_verified;

                showToast(
                    isVerified
                    ? 'User verified.'
                    : 'User unverified.'
                );

            } else {

                showToast(
                    data.error || 'Failed to update.',
                    'error'
                );
            }
        })

        .catch(() => {
            showToast('Network error.', 'error');
        });

    });
});

document.querySelectorAll('.role-select').forEach(sel => {

    sel.addEventListener('change', function() {

        const userId  = this.dataset.userId;
        const newRole = this.value;

        fetch('index.php?page=ajax&type=change_role', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({
                user_id: parseInt(userId),
                role: newRole
            })

        })

        .then(r => r.json())

        .then(data => {

            if (data.success) {

                this.closest('tr').dataset.role = newRole;

                showToast('Role updated.');

            } else {

                showToast(
                    data.error || 'Failed.',
                    'error'
                );
            }
        })

        .catch(() => {
            showToast('Network error.', 'error');
        });

    });
});

let pendingDeleteUserId = null;

document.querySelectorAll('.delete-user-btn').forEach(btn => {

    btn.addEventListener('click', function() {

        pendingDeleteUserId = this.dataset.userId;

        document.getElementById('deleteUserName').textContent =
            this.dataset.userName;

        openModal('deleteUserModal');

    });
});

document.getElementById('confirmDeleteUserBtn')
.addEventListener('click', function() {

    if (!pendingDeleteUserId) return;

    fetch('index.php?page=ajax&type=delete_user', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json'
        },

        body: JSON.stringify({
            user_id: parseInt(pendingDeleteUserId)
        })

    })

    .then(r => r.json())

    .then(data => {

        if (data.success) {

            document.querySelectorAll('.delete-user-btn')
            .forEach(b => {

                if (b.dataset.userId == pendingDeleteUserId) {
                    b.closest('tr').remove();
                }
            });

            closeModal('deleteUserModal');

            showToast('User deleted successfully.');

            pendingDeleteUserId = null;

        } else {

            showToast(
                data.error || 'Failed to delete.',
                'error'
            );
        }
    })

    .catch(() => {
        showToast('Network error.', 'error');
    });

});

document.getElementById('addUserForm')
.addEventListener('submit', function(e) {

    let valid = true;

    const name  = document.getElementById('au_name');
    const email = document.getElementById('au_email');
    const pass  = document.getElementById('au_pass');

    const show = (id, state) => {
        document.getElementById(id).style.display =
            state ? 'block' : 'none';
    };

    const border = (el, err) => {
        el.style.borderColor = err ? '#dc2626' : '';
    };

    if (name.value.trim() === '') {

        show('au_nameErr', true);
        border(name, true);

        valid = false;

    } else {

        show('au_nameErr', false);
        border(name, false);
    }

    const emailR = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailR.test(email.value.trim())) {

        show('au_emailErr', true);
        border(email, true);

        valid = false;

    } else {

        show('au_emailErr', false);
        border(email, false);
    }

    if (pass.value.length < 8) {

        show('au_passErr', true);
        border(pass, true);

        valid = false;

    } else {

        show('au_passErr', false);
        border(pass, false);
    }

    if (!valid) {
        e.preventDefault();
    }

});

<?php if (!empty($error)): ?>

openModal('addUserModal');

<?php endif; ?>

</script>


</body>
</html>