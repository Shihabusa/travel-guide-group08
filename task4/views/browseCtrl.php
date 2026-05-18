<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?> &mdash; Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="app-body">

    <?php
    $user           = $_SESSION['user'] ?? null;
    $isLoggedIn     = $user !== null;
    $isGeneralUser  = $isLoggedIn && $user['role'] === 'user' && $user['is_verified'];
    $currentUserId  = (int)($user['id'] ?? 0);
    ?>

    <!-- Navbar -->
    <header class="navbar">
        <div class="navbar-inner">
            <a class="brand" href="index.php?page=home">
                <span class="brand-icon">&#9992;</span>
                <span>Travel Guide</span>
            </a>

            <ul class="nav-links">
                <?php if ($isLoggedIn && $user['role'] === 'scout'): ?>
                    <li><a href="index.php?page=dashboard">&#128203; My Requests</a></li>
                    <li><a href="index.php?page=approved">&#9989; Approved</a></li>
                    <li><a href="index.php?page=request_form&action=add">&#10010; New Request</a></li>
                    <li><a href="index.php?page=posts" class="active">&#127758; Browse</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=home">&#127968; Home</a></li>
                    <li><a href="index.php?page=posts" class="active">&#127758; Destinations</a></li>
                    <?php if ($isLoggedIn && $user['role'] === 'user'): ?>
                        <li><a href="index.php?page=wishlist">&#10084; Wishlist</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?page=profile">&#128100; Profile</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-user">
                <?php if ($isLoggedIn): ?>
                    <span class="user-pill">
                        <span class="user-avatar">
                            <?php if (!empty($user['picture'])): ?>
                                <img src="<?= htmlspecialchars($user['picture']) ?>" alt="avatar">
                            <?php else: ?>
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            <?php endif; ?>
                        </span>
                        <span class="user-meta">
                            <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                            <span class="user-role"><?= htmlspecialchars($user['role']) ?></span>
                        </span>
                    </span>
                    <a href="index.php?page=logout" class="btn-logout">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content" style="max-width: 860px;">

        <!-- Back link -->
        <a href="index.php?page=posts" class="btn btn-ghost"
            style="display: inline-flex; margin-bottom: 22px; font-size: 13px; padding: 9px 18px;">
            &#8592; Back to Destinations
        </a>

        <!-- ═══════════════════════════════════════════════
         POST DETAIL CARD
         ═══════════════════════════════════════════════ -->
        <div class="card form-card" style="margin-bottom: 24px;">

            <!-- Post image -->
            <?php if (!empty($post['image'])): ?>
                <div style="margin: -30px -34px 28px; border-radius: var(--radius-lg) var(--radius-lg) 0 0; overflow:hidden; height: 240px;">
                    <img src="<?= htmlspecialchars($post['image']) ?>"
                        alt="<?= htmlspecialchars($post['title']) ?>"
                        style="width:100%; height:100%; object-fit:cover;">
                </div>
            <?php else: ?>
                <div style="margin: -30px -34px 28px; border-radius: var(--radius-lg) var(--radius-lg) 0 0;
                    height: 180px; background: linear-gradient(135deg,#0c4a6e,#0369a1,#38bdf8);
                    display:flex; align-items:center; justify-content:center; font-size:72px;">
                    &#127958;
                </div>
            <?php endif; ?>

            <!-- Badges -->
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 14px;">
                <span class="badge-<?= $post['cost_level'] ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
                <span class="badge-accent">&#127757; <?= htmlspecialchars($post['country'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge-accent"><?= ucfirst($post['genre']) ?></span>
            </div>

            <!-- Title -->
            <h1 style="font-size:26px; font-weight:900; color:#0c4a6e; letter-spacing:-.3px; margin-bottom:6px;">
                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:22px;">
                &#9997; By <?= htmlspecialchars($post['scout_name'], ENT_QUOTES, 'UTF-8') ?>
                &nbsp;&bull;&nbsp;
                &#128197; <?= date('M d, Y', strtotime($post['created_at'])) ?>
            </p>

            <!-- Short History -->
            <div style="border-top:1.5px solid #e0f2fe; padding-top:20px; margin-bottom:20px;">
                <h2 class="card-title" style="border-bottom:none; padding-bottom:0; margin-bottom:10px;">
                    &#128218; About This Destination
                </h2>
                <p style="font-size:15px; line-height:1.8; color:var(--text);">
                    <?= nl2br(htmlspecialchars($post['short_history'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
            </div>

            <!-- Travel Medium -->
            <?php if (!empty($post['travel_medium_info'])): ?>
                <div style="border-top:1.5px solid #e0f2fe; padding-top:20px;">
                    <h2 class="card-title" style="border-bottom:none; padding-bottom:0; margin-bottom:10px;">
                        &#9992; Travel Medium & Getting There
                    </h2>
                    <p style="font-size:14px; line-height:1.7; color:var(--text-muted);">
                        <?= nl2br(htmlspecialchars($post['travel_medium_info'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Add to wishlist button for general users -->
            <?php if ($isGeneralUser): ?>
                <div style="border-top:1.5px solid #e0f2fe; padding-top:20px; margin-top:20px;">
                    <button class="btn btn-accent wishlist-btn" data-id="<?= $post['id'] ?>"
                        style="font-size:14px;">
                        &#10084; Add to Wishlist
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══════════════════════════════════════════════
         COST CALCULATOR
         ═══════════════════════════════════════════════ -->
        <div class="card form-card" id="costCalculator" style="margin-bottom: 24px;">
            <h2 class="card-title">&#128176; Probable Cost Estimate</h2>

            <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
                Base cost per person per day:
                <strong style="color:#0c4a6e;">
                    <?= $costData['currency'] ?> <?= number_format($costData['base_cost']) ?>
                </strong>
            </p>

            <div class="field-row" style="max-width:480px;">
                <div class="field">
                    <label for="numTravelers">&#128101; Number of Travelers (1–10)</label>
                    <input type="number" id="numTravelers" min="1" max="10" value="1">
                    <span class="field-error" id="travelersError"></span>
                </div>
                <div class="field">
                    <label for="numDays">&#128197; Number of Days</label>
                    <input type="number" id="numDays" min="1" value="7">
                    <span class="field-error" id="daysError"></span>
                </div>
            </div>

            <div class="form-actions" style="margin-top:10px; justify-content:flex-start;">
                <button id="calcBtn" class="btn btn-primary">&#128204; Calculate Total</button>
            </div>

            <div id="calcResult" style="display:none; margin-top:20px; padding:20px 24px;
             background:#f0f9ff; border-radius:var(--radius-sm);
             border-left: 4px solid var(--primary);">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:4px;">Estimated Total:</p>
                <p id="calcTotal" style="font-size:2rem; font-weight:900; color:#0c4a6e;"></p>
                <p id="calcFormula" style="font-size:12px; color:var(--text-light); margin-top:4px;"></p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
         COMMENTS SECTION
         ═══════════════════════════════════════════════ -->
        <div class="card form-card" id="commentsSection">
            <h2 class="card-title">
                &#128172; Comments
                <span class="badge" id="commentCount"
                    style="margin-left:8px; font-size:12px;">
                    <?= count($comments) ?>
                </span>
            </h2>

            <!-- Alert box for comment actions -->
            <div id="commentAlert"></div>

            <!-- Existing comments -->
            <div id="commentsList">
                <?php if (empty($comments)): ?>
                    <div id="noCommentMsg" class="pending-box"
                        style="padding: 32px 24px; border:none; box-shadow:none;">
                        <span class="pending-icon" style="font-size:36px;">&#128172;</span>
                        <h2 style="font-size:16px;">No comments yet</h2>
                        <p style="font-size:13px;">Be the first to share your thoughts!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="t4-comment-item" id="comment-<?= $c['id'] ?>">
                            <div class="t4-comment-avatar">
                                <?= strtoupper(substr($c['reviewer_name'], 0, 1)) ?>
                            </div>
                            <div class="t4-comment-body">
                                <div class="t4-comment-header">
                                    <strong class="t4-comment-author">
                                        <?= htmlspecialchars($c['reviewer_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <span class="t4-comment-date">
                                        <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <?php if ($isGeneralUser && (int)$c['user_id'] === $currentUserId): ?>
                                        <button class="btn-delete-comment"
                                            data-id="<?= $c['id'] ?>"
                                            title="Delete comment">&#128465; Delete</button>
                                    <?php endif; ?>
                                </div>
                                <p class="t4-comment-text">
                                    <?= htmlspecialchars($c['content'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Comment form – verified general users only -->
            <?php if ($isGeneralUser): ?>
                <div style="border-top:1.5px solid #e0f2fe; padding-top:22px; margin-top:22px;">
                    <h3 style="font-size:15px; font-weight:800; color:#0c4a6e; margin-bottom:16px;">
                        &#9997; Leave a Comment
                    </h3>

                    <div id="commentFormErrors" class="alert alert-error" style="display:none;"></div>

                    <div class="form" style="gap:12px;">
                        <div class="field">
                            <label for="commentName">Your Name</label>
                            <input type="text"
                                id="commentName"
                                name="commentName"
                                value="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Your name"
                                maxlength="100">
                            <span class="field-error" id="nameError"></span>
                        </div>
                        <div class="field">
                            <label for="commentContent">Comment</label>
                            <textarea id="commentContent" rows="4" maxlength="1000"
                                placeholder="Share your experience or thoughts…"></textarea>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                                <span class="field-error" id="contentError"></span>
                                <span style="font-size:11px; color:var(--text-light);">
                                    <span id="charCount">0</span>/1000
                                </span>
                            </div>
                        </div>

                        <div class="form-actions" style="justify-content:flex-start;">
                            <button id="submitComment" class="btn btn-primary">
                                &#128172; Post Comment
                            </button>
                        </div>
                    </div>
                </div>

            <?php elseif (!$isLoggedIn): ?>
                <div style="border-top:1.5px solid #e0f2fe; padding-top:20px; margin-top:20px;
                    text-align:center;">
                    <p style="color:var(--text-muted); font-size:14px;">
                        <a href="index.php?page=login">Log in</a> to leave a comment.
                    </p>
                </div>

            <?php else: ?>
                <div style="border-top:1.5px solid #e0f2fe; padding-top:20px; margin-top:20px;
                    text-align:center;">
                    <p style="color:var(--text-muted); font-size:14px;">
                        Only verified General Users can post comments.
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="footer">
        &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Explore the world, one destination at a time. &#9992;
    </footer>

    <!-- JS data for calculator & comments -->
    <script>
        window.POST_ID = <?= (int)$post['id'] ?>;
        window.POST_BASE = <?= (float)$costData['base_cost'] ?>;
        window.POST_CURRENCY = "<?= htmlspecialchars($costData['currency'], ENT_QUOTES) ?>";
        window.CURRENT_USER = <?= $currentUserId ?>;
        window.IS_GEN_USER = <?= $isGeneralUser ? 'true' : 'false' ?>;
    </script>

    <!-- ============================================================
     TASK 4 – Cost Calculator + Comments JS
     ============================================================ -->
    <script>
        (function() {
            "use strict";

            /* ── helpers ─────────────────────────────────────────── */
            function escHtml(str) {
                var d = document.createElement('div');
                d.textContent = str || '';
                return d.innerHTML;
            }

            function showEl(id) {
                var e = document.getElementById(id);
                if (e) e.style.display = '';
            }

            function hideEl(id) {
                var e = document.getElementById(id);
                if (e) e.style.display = 'none';
            }

            function showAlert(msg, type) {
                var box = document.getElementById('commentAlert');
                if (!box) return;
                box.innerHTML = '<div class="alert alert-' + type + '">' + msg + '</div>';
                setTimeout(function() {
                    box.innerHTML = '';
                }, 3500);
            }

            /* ── Cost Calculator ─────────────────────────────────── */
            var calcBtn = document.getElementById('calcBtn');
            var calcResult = document.getElementById('calcResult');
            var calcTotal = document.getElementById('calcTotal');
            var calcFormula = document.getElementById('calcFormula');
            var numTravelers = document.getElementById('numTravelers');
            var numDays = document.getElementById('numDays');
            var travErr = document.getElementById('travelersError');
            var daysErr = document.getElementById('daysError');

            function clearCalcErrors() {
                travErr.textContent = '';
                travErr.style.display = 'none';
                daysErr.textContent = '';
                daysErr.style.display = 'none';
            }

            calcBtn && calcBtn.addEventListener('click', function() {
                clearCalcErrors();
                var travelers = parseInt(numTravelers.value, 10);
                var days = parseInt(numDays.value, 10);
                var valid = true;

                // JS Validation (positive integers)
                if (isNaN(travelers) || travelers < 1 || travelers > 10) {
                    travErr.textContent = 'Enter a number between 1 and 10.';
                    travErr.style.display = 'block';
                    valid = false;
                }
                if (isNaN(days) || days < 1) {
                    daysErr.textContent = 'Enter a positive number of days.';
                    daysErr.style.display = 'block';
                    valid = false;
                }

                if (!valid) {
                    calcResult.style.display = 'none';
                    return;
                }

                var base = window.POST_BASE || 1500;
                var cur = window.POST_CURRENCY || 'USD';
                // Formula from spec: base_cost * travelers * days / 7
                var total = Math.round(base * travelers * (days / 7));

                calcTotal.textContent = cur + ' ' + total.toLocaleString();
                calcFormula.textContent = cur + ' ' + base.toLocaleString() +
                    ' base × ' + travelers +
                    ' traveler' + (travelers > 1 ? 's' : '') +
                    ' × ' + days + ' day' + (days > 1 ? 's' : '') +
                    ' ÷ 7';
                calcResult.style.display = '';
            });

            [numTravelers, numDays].forEach(function(el) {
                el && el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') calcBtn && calcBtn.click();
                });
            });

            /* ── Character counter ───────────────────────────────── */
            var contentArea = document.getElementById('commentContent');
            var charCount = document.getElementById('charCount');
            var contentErr = document.getElementById('contentError');

            contentArea && contentArea.addEventListener('input', function() {
                var len = contentArea.value.length;
                charCount.textContent = len;
                if (len > 1000) {
                    contentErr.textContent = 'Max 1000 characters.';
                    contentErr.style.display = 'block';
                } else {
                    contentErr.textContent = '';
                    contentErr.style.display = 'none';
                }
            });

            /* ── Submit comment ──────────────────────────────────── */
            var submitBtn = document.getElementById('submitComment');
            var formErrors = document.getElementById('commentFormErrors');

            submitBtn && submitBtn.addEventListener('click', function() {
                if (!window.IS_GEN_USER) return;

                // Reset errors
                if (formErrors) {
                    formErrors.style.display = 'none';
                    formErrors.innerHTML = '';
                }
                if (contentErr) {
                    contentErr.textContent = '';
                    contentErr.style.display = 'none';
                }
                var nameErr = document.getElementById('nameError');
                if (nameErr) {
                    nameErr.textContent = '';
                    nameErr.style.display = 'none';
                }

                var nameField = document.getElementById('commentName');
                var nameVal = nameField ? nameField.value.trim() : '';
                var content = contentArea ? contentArea.value.trim() : '';

                // JS Validation – name + content
                var errors = [];
                if (nameVal === '') {
                    errors.push('Name cannot be empty.');
                    if (nameErr) {
                        nameErr.textContent = 'Name is required.';
                        nameErr.style.display = 'block';
                    }
                }
                if (content === '') errors.push('Comment cannot be empty.');
                else if (content.length < 3) errors.push('Comment must be at least 3 characters.');
                else if (content.length > 1000) errors.push('Comment must be under 1000 characters.');

                if (errors.length) {
                    if (contentErr && content === '') {
                        contentErr.textContent = 'Comment cannot be empty.';
                        contentErr.style.display = 'block';
                    }
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Posting…';

                fetch('index.php?page=ajax&type=comment_add', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            post_id: window.POST_ID,
                            content: content,
                            display_name: nameVal
                        }),
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            var c = data.comment;
                            var list = document.getElementById('commentsList');
                            var noMsg = document.getElementById('noCommentMsg');
                            if (noMsg) noMsg.remove();

                            var div = document.createElement('div');
                            div.className = 't4-comment-item';
                            div.id = 'comment-' + c.id;
                            div.innerHTML =
                                '<div class="t4-comment-avatar">' +
                                escHtml(c.reviewer_name.charAt(0).toUpperCase()) + '</div>' +
                                '<div class="t4-comment-body">' +
                                '<div class="t4-comment-header">' +
                                '<strong class="t4-comment-author">' + escHtml(c.reviewer_name) + '</strong>' +
                                '<span class="t4-comment-date">' + escHtml(c.created_at) + '</span>' +
                                '<button class="btn-delete-comment" data-id="' + c.id +
                                '" title="Delete">&#128465; Delete</button>' +
                                '</div>' +
                                '<p class="t4-comment-text">' + escHtml(c.content) + '</p>' +
                                '</div>';

                            list && list.prepend(div);
                            div.querySelector('.btn-delete-comment')
                                .addEventListener('click', handleDelete);

                            if (contentArea) contentArea.value = '';
                            if (charCount) charCount.textContent = '0';

                            var cnt = document.getElementById('commentCount');
                            if (cnt) cnt.textContent = parseInt(cnt.textContent || '0', 10) + 1;

                            showAlert('&#10004; Comment posted!', 'success');

                        } else {
                            var msgs = data.errors || [data.message || 'Could not post comment.'];
                            if (formErrors) {
                                formErrors.innerHTML = msgs.map(function(m) {
                                    return '<span>&#9888; ' + escHtml(m) + '</span>';
                                }).join('<br>');
                                formErrors.style.display = '';
                            }
                        }
                    })
                    .catch(function() {
                        showAlert('Network error. Please try again.', 'error');
                    })
                    .finally(function() {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '&#128172; Post Comment';
                    });
            });

            /* ── Delete comment ──────────────────────────────────── */
            function handleDelete(e) {
                var btn = e.currentTarget;
                var commentId = btn.getAttribute('data-id');
                if (!confirm('Delete this comment?')) return;

                btn.disabled = true;
                btn.textContent = '…';

                fetch('index.php?page=ajax&type=comment_delete', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: parseInt(commentId, 10)
                        }),
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            var item = document.getElementById('comment-' + commentId);
                            if (item) {
                                item.style.opacity = '0';
                                item.style.transition = 'opacity .3s ease';
                                setTimeout(function() {
                                    item.remove();
                                    var cnt = document.getElementById('commentCount');
                                    if (cnt) cnt.textContent = Math.max(0, parseInt(cnt.textContent || '1', 10) - 1);

                                    var list = document.getElementById('commentsList');
                                    if (list && list.children.length === 0) {
                                        list.innerHTML = '<div class="pending-box" style="padding:32px 24px;border:none;box-shadow:none;">' +
                                            '<span class="pending-icon" style="font-size:36px;">&#128172;</span>' +
                                            '<h2 style="font-size:16px;">No comments yet</h2>' +
                                            '<p style="font-size:13px;">Be the first to share your thoughts!</p>' +
                                            '</div>';
                                    }
                                }, 300);
                            }
                            showAlert('&#10003; Comment deleted.', 'success');
                        } else {
                            alert(data.message || 'Could not delete comment.');
                            btn.disabled = false;
                            btn.textContent = '&#128465; Delete';
                        }
                    })
                    .catch(function() {
                        btn.disabled = false;
                        btn.textContent = '&#128465; Delete';
                        showAlert('Network error.', 'error');
                    });
            }

            document.querySelectorAll('.btn-delete-comment').forEach(function(btn) {
                btn.addEventListener('click', handleDelete);
            });

            /* ── Wishlist button (same as home.php) ──────────────── */
            var wlBtn = document.querySelector('.wishlist-btn');
            wlBtn && wlBtn.addEventListener('click', function() {
                var postId = wlBtn.getAttribute('data-id');
                wlBtn.disabled = true;
                wlBtn.textContent = 'Adding…';

                fetch('index.php?page=ajax&type=wishlist_add', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            post_id: parseInt(postId)
                        }),
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            wlBtn.textContent = '&#10004; Saved to Wishlist!';
                            wlBtn.style.background = '#16a34a';
                        } else {
                            wlBtn.textContent = '&#10084; Already in Wishlist';
                            wlBtn.disabled = false;
                        }
                    })
                    .catch(function() {
                        wlBtn.disabled = false;
                        wlBtn.textContent = '&#10084; Add to Wishlist';
                    });
            });

        })();
    </script>

</body>

</html>