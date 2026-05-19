<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= !empty($editing) ? 'Edit Request' : 'New Request' ?> &mdash; Travel Guide</title>
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
            <li><a href="index.php?page=approved">&#9989; Approved Posts</a></li>
            <li><a href="index.php?page=request_form&action=add" class="active">&#10010; New Request</a></li>
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
            <h1 class="page-title">
                <?= !empty($editing) ? '&#9998; Edit Request #' . intval($editing['id']) : '&#10010; New Post Request' ?>
            </h1>
            <p class="page-sub">
                <?= !empty($editing) ? 'Update your pending request' : 'Submit a new travel place for admin approval' ?>
            </p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Request Form -->
    <div class="card form-card">
        <h3 class="card-title">
            <?= !empty($editing) ? '&#9998; Edit Place Information' : '&#127758; Place Information' ?>
        </h3>

        <form method="POST"
              action="index.php?page=request_form&action=<?= !empty($editing) ? 'update&id=' . intval($editing['id']) : 'add' ?>"
              enctype="multipart/form-data"
              class="form" novalidate id="requestForm">

            <!-- Title & Country -->
            <div class="field-row">
                <div class="field">
                    <label for="title">Place Title</label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($editing['title'] ?? '') ?>"
                           placeholder="e.g. Kyrgyzstan Mountains" required>
                    <span class="field-error" id="titleErr"></span>
                </div>
                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country"
                           value="<?= htmlspecialchars($editing['country'] ?? '') ?>"
                           placeholder="e.g. Kyrgyzstan" required>
                    <span class="field-error" id="countryErr"></span>
                </div>
            </div>

            <!-- Genre & Cost Level -->
            <div class="field-row">
                <div class="field">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" required>
                        <option value="">-- Select Genre --</option>
                        <?php
                        $genres = ['beach','mountain','city','historical','nature','other'];
                        foreach ($genres as $g):
                            $sel = ($editing['genre'] ?? '') === $g ? 'selected' : '';
                        ?>
                        <option value="<?= $g ?>" <?= $sel ?>><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="genreErr"></span>
                </div>
                <div class="field">
                    <label for="cost_level">Cost Level</label>
                    <select id="cost_level" name="cost_level" required>
                        <option value="">-- Select Cost --</option>
                        <?php
                        $costs = ['low','medium','high'];
                        foreach ($costs as $c):
                            $sel = ($editing['cost_level'] ?? '') === $c ? 'selected' : '';
                        ?>
                        <option value="<?= $c ?>" <?= $sel ?>><?= ucfirst($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="costErr"></span>
                </div>
            </div>

            <!-- Travel Medium -->
            <div class="field">
                <label for="travel_medium_info">Travel Medium</label>
                <input type="text" id="travel_medium_info" name="travel_medium_info"
                       value="<?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?>"
                       placeholder="e.g. Flight + Bus, Train, Car" required>
                <span class="field-error" id="travelErr"></span>
            </div>

            <!-- Short History -->
            <div class="field">
                <label for="short_history">Short History / Description</label>
                <textarea id="short_history" name="short_history"
                          placeholder="Describe the place, its history and significance..." required><?= htmlspecialchars($editing['short_history'] ?? '') ?></textarea>
                <span class="field-error" id="historyErr"></span>
            </div>

            <!-- Image Upload -->
            <div class="field">
                <label for="image">Place Image (optional)</label>
                <input type="file" id="image" name="image"
                       accept="image/jpeg,image/png,image/gif,image/webp">
                <span style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                    JPG, PNG, GIF or WEBP. Max 2MB.
                </span>
                <?php if (!empty($editing['image'])): ?>
                    <div style="margin-top:8px;">
                        <img src="<?= htmlspecialchars($editing['image']) ?>"
                             alt="Current image"
                             style="height:80px; border-radius:8px; object-fit:cover;">
                        <p style="font-size:11px; color:var(--text-muted); margin-top:4px;">Current image — upload new to replace</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Request Changes field -->
            <?php if (!empty($_GET['original_post_id'])): ?>
            <div class="field">
                <input type="hidden" name="original_post_id"
                       value="<?= intval($_GET['original_post_id']) ?>">
                <div class="alert alert-warning" style="margin-bottom:0;">
                    &#9888; This is a <strong>change request</strong> for an approved post (ID: <?= intval($_GET['original_post_id']) ?>).
                    Admin will review your suggested changes.
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="index.php?page=dashboard" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <?= !empty($editing) ? 'Update Request &#9998;' : 'Submit Request &#128269;' ?>
                </button>
            </div>
        </form>
    </div>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Scout Dashboard &#128269;
</footer>

<!-- JS Validation -->
<script>
(function () {
    var form    = document.getElementById('requestForm');
    var title   = document.getElementById('title');
    var country = document.getElementById('country');
    var genre   = document.getElementById('genre');
    var cost    = document.getElementById('cost_level');
    var travel  = document.getElementById('travel_medium_info');
    var history = document.getElementById('short_history');
    var image   = document.getElementById('image');

    var titleErr   = document.getElementById('titleErr');
    var countryErr = document.getElementById('countryErr');
    var genreErr   = document.getElementById('genreErr');
    var costErr    = document.getElementById('costErr');
    var travelErr  = document.getElementById('travelErr');
    var historyErr = document.getElementById('historyErr');

    function showErr(el, msg) {
        el.textContent = msg;
        el.style.display = 'block';
    }

    function clearErr(el) {
        el.textContent = '';
        el.style.display = 'none';
    }

    /* Live validation */
    title.addEventListener('input', function () {
        title.value.trim() === ''
            ? showErr(titleErr, 'Title is required.')
            : clearErr(titleErr);
    });

    country.addEventListener('input', function () {
        country.value.trim() === ''
            ? showErr(countryErr, 'Country is required.')
            : clearErr(countryErr);
    });

    genre.addEventListener('change', function () {
        genre.value === ''
            ? showErr(genreErr, 'Please select a genre.')
            : clearErr(genreErr);
    });

    cost.addEventListener('change', function () {
        cost.value === ''
            ? showErr(costErr, 'Please select a cost level.')
            : clearErr(costErr);
    });

    travel.addEventListener('input', function () {
        travel.value.trim() === ''
            ? showErr(travelErr, 'Travel medium is required.')
            : clearErr(travelErr);
    });

    history.addEventListener('input', function () {
        if (history.value.trim() === '') {
            showErr(historyErr, 'Description is required.');
        } else if (history.value.trim().length < 20) {
            showErr(historyErr, 'Description must be at least 20 characters.');
        } else {
            clearErr(historyErr);
        }
    });

    /* Image validation */
    image.addEventListener('change', function () {
        if (image.files.length > 0) {
            var file     = image.files[0];
            var allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            var maxSize  = 2 * 1024 * 1024;
            if (!allowed.includes(file.type)) {
                alert('Only JPG, PNG, GIF or WEBP images are allowed.');
                image.value = '';
            } else if (file.size > maxSize) {
                alert('Image must be under 2MB.');
                image.value = '';
            }
        }
    });

    /* Submit validation */
    form.addEventListener('submit', function (e) {
        var valid = true;

        if (title.value.trim() === '') {
            showErr(titleErr, 'Title is required.'); valid = false;
        }
        if (country.value.trim() === '') {
            showErr(countryErr, 'Country is required.'); valid = false;
        }
        if (genre.value === '') {
            showErr(genreErr, 'Please select a genre.'); valid = false;
        }
        if (cost.value === '') {
            showErr(costErr, 'Please select a cost level.'); valid = false;
        }
        if (travel.value.trim() === '') {
            showErr(travelErr, 'Travel medium is required.'); valid = false;
        }
        if (history.value.trim() === '') {
            showErr(historyErr, 'Description is required.'); valid = false;
        } else if (history.value.trim().length < 20) {
            showErr(historyErr, 'Description must be at least 20 characters.'); valid = false;
        }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>
