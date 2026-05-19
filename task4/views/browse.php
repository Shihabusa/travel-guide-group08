<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Destinations &mdash; Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="app-body">

    <header class="navbar">
        <div class="navbar-inner">
            <a class="brand" href="index.php?page=home">
                <span class="brand-icon">&#9992;</span>
                <span>Travel Guide</span>
            </a>

            <ul class="nav-links">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'scout'): ?>
                    <li><a href="index.php?page=dashboard">&#128203; My Requests</a></li>
                    <li><a href="index.php?page=approved">&#9989; Approved</a></li>
                    <li><a href="index.php?page=request_form&action=add">&#10010; New Request</a></li>
                    <li><a href="index.php?page=posts" class="active">&#127758; Browse</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=home">&#127968; Home</a></li>
                    <li><a href="index.php?page=posts" class="active">&#127758; Destinations</a></li>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user'): ?>
                        <li><a href="index.php?page=wishlist">&#10084; Wishlist</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?page=profile">&#128100; Profile</a></li>
                <?php endif; ?>
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


        <div class="hero" style="padding: 44px 40px; margin-bottom: 28px;">
            <span class="hero-icon">&#127758;</span>
            <h1>Explore Destinations</h1>
            <p>Search, filter and discover places handpicked by our scouts worldwide.</p>


            <div class="t4-search-wrap">
                <span class="t4-search-icon">&#128269;</span>
                <input
                    type="text"
                    id="searchBox"
                    class="t4-search-input"
                    placeholder="Search by title or country…"
                    autocomplete="off"
                    aria-label="Search destinations">
            </div>
        </div>


        <div class="card t4-filter-bar">
            <div class="card-toolbar" style="flex-wrap: wrap; gap: 16px; padding: 18px 24px;">
                <span style="font-weight: 800; color: #0c4a6e; font-size: 14px;">&#9881; Filters</span>


                <div class="t4-filter-group">
                    <label class="t4-filter-label">Country</label>
                    <select id="countryFilter" class="t4-filter-select">
                        <option value="">All Countries</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="t4-filter-group">
                    <label class="t4-filter-label">Genre</label>
                    <div class="t4-genre-wrap">
                        <?php
                        $genres = ['beach', 'mountain', 'city', 'historical', 'nature', 'other'];
                        foreach ($genres as $g): ?>
                            <label class="t4-check-label">
                                <input type="checkbox" class="genre-check" value="<?= $g ?>">
                                <?= ucfirst($g) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="t4-filter-group">
                    <label class="t4-filter-label">Cost Level</label>
                    <div class="t4-genre-wrap">
                        <label class="t4-check-label">
                            <input type="radio" name="cost" class="cost-radio" value="" checked> Any
                        </label>
                        <label class="t4-check-label">
                            <input type="radio" name="cost" class="cost-radio" value="low">
                            <span class="badge-low">Low</span>
                        </label>
                        <label class="t4-check-label">
                            <input type="radio" name="cost" class="cost-radio" value="medium">
                            <span class="badge-medium">Medium</span>
                        </label>
                        <label class="t4-check-label">
                            <input type="radio" name="cost" class="cost-radio" value="high">
                            <span class="badge-high">High</span>
                        </label>
                    </div>
                </div>

                <button id="resetFilters" class="btn btn-ghost" style="padding: 10px 18px; font-size: 13px;">
                    &#8635; Reset
                </button>
            </div>
        </div>

        <div class="page-header" style="margin-bottom: 16px;">
            <div>
                <h2 class="section-title">&#127957; Available Destinations</h2>
                <p class="section-sub" id="resultsCount">
                    <?= count($posts) ?> destination<?= count($posts) !== 1 ? 's' : '' ?> found
                </p>
            </div>
        </div>


        <div id="loadingSpinner" class="t4-spinner" style="display:none;">
            <span class="t4-spinner-ring"></span> Loading destinations…
        </div>

        <div id="noResults" class="pending-box" style="display:none;">
            <span class="pending-icon">&#127757;</span>
            <h2>No Destinations Found</h2>
            <p>Try adjusting your search or filters.</p>
            <button id="resetFromEmpty" class="btn btn-ghost">&#8635; Reset Filters</button>
        </div>


        <div class="posts-grid" id="postsGrid">
            <?php if (empty($posts)): ?>
                <div class="pending-box" style="grid-column: 1/-1;">
                    <span class="pending-icon">&#127757;</span>
                    <h2>No Destinations Yet</h2>
                    <p>Our scouts are collecting amazing travel destinations. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-card-img">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>"
                                    alt="<?= htmlspecialchars($post['title']) ?>">
                            <?php else: ?>
                                &#127958;
                            <?php endif; ?>
                        </div>
                        <div class="post-card-body">
                            <h3 class="post-card-title">
                                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <div class="post-card-meta">
                                <span class="badge-accent">&#127757; <?= htmlspecialchars($post['country'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge-<?= $post['cost_level'] ?>"><?= ucfirst($post['cost_level']) ?> Cost</span>
                                <span class="badge-accent"><?= ucfirst($post['genre']) ?></span>
                            </div>
                            <p class="post-card-snippet">
                                <?= htmlspecialchars(mb_substr($post['short_history'], 0, 130), ENT_QUOTES, 'UTF-8') ?>…
                            </p>
                            <a class="btn btn-primary"
                                href="index.php?page=post_detail&id=<?= $post['id'] ?>"
                                style="font-size: 13px; padding: 10px; margin-top: auto;">
                                Read More &#8594;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <footer class="footer">
        &copy; <?= date('Y') ?> Travel Guide &mdash; Group 8. Explore the world, one destination at a time. &#9992;
    </footer>

    <script>
        (function() {
            "use strict";

            var searchBox = document.getElementById('searchBox');
            var postsGrid = document.getElementById('postsGrid');
            var loadSpinner = document.getElementById('loadingSpinner');
            var noResults = document.getElementById('noResults');
            var resultsCount = document.getElementById('resultsCount');
            var countryFilter = document.getElementById('countryFilter');
            var resetBtn = document.getElementById('resetFilters');
            var resetFromEmpty = document.getElementById('resetFromEmpty');

            function escHtml(str) {
                var d = document.createElement('div');
                d.textContent = str || '';
                return d.innerHTML;
            }

            function buildCard(p) {
                var costBadge = '<span class="badge-' + escHtml(p.cost_level) + '">' +
                    escHtml(p.cost_level.charAt(0).toUpperCase() + p.cost_level.slice(1)) +
                    ' Cost</span>';

                var img = p.image ?
                    '<img src="' + escHtml(p.image) + '" alt="' + escHtml(p.title) + '">' :
                    '&#127958;';

                return '<div class="post-card">' +
                    '<div class="post-card-img">' + img + '</div>' +
                    '<div class="post-card-body">' +
                    '<h3 class="post-card-title">' + escHtml(p.title) + '</h3>' +
                    '<div class="post-card-meta">' +
                    '<span class="badge-accent">&#127757; ' + escHtml(p.country) + '</span>' +
                    costBadge +
                    '<span class="badge-accent">' + escHtml(p.genre.charAt(0).toUpperCase() + p.genre.slice(1)) + '</span>' +
                    '</div>' +
                    '<p class="post-card-snippet">' + escHtml(p.short_history) + '</p>' +
                    '<a class="btn btn-primary" href="index.php?page=post_detail&id=' + p.id +
                    '" style="font-size:13px; padding:10px; margin-top:auto;">Read More &#8594;</a>' +
                    '</div></div>';
            }

            function renderPosts(posts) {
                if (!posts.length) {
                    postsGrid.innerHTML = '';
                    postsGrid.style.display = 'none';
                    noResults.style.display = '';
                    resultsCount.textContent = '0 destinations found';
                } else {
                    noResults.style.display = 'none';
                    postsGrid.style.display = '';
                    postsGrid.innerHTML = posts.map(buildCard).join('');
                    var n = posts.length;
                    resultsCount.textContent = n + ' destination' + (n !== 1 ? 's' : '') + ' found';
                }
            }

            function showSpinner() {
                loadSpinner.style.display = '';
            }

            function hideSpinner() {
                loadSpinner.style.display = 'none';
            }

            function getFilters() {
                var country = countryFilter ? countryFilter.value : '';
                var cost = (document.querySelector('.cost-radio:checked') || {}).value || '';
                var genres = Array.from(document.querySelectorAll('.genre-check:checked'))
                    .map(function(el) {
                        return el.value;
                    });
                return {
                    country: country,
                    cost: cost,
                    genres: genres
                };
            }

            function debounce(fn, ms) {
                var t;
                return function() {
                    var args = arguments;
                    clearTimeout(t);
                    t = setTimeout(function() {
                        fn.apply(null, args);
                    }, ms);
                };
            }

            var doSearch = debounce(function(q) {
                if (q.trim() === '') {
                    applyFilters();
                    return;
                }

                showSpinner();
                fetch('index.php?page=ajax&type=posts_search&q=' + encodeURIComponent(q))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) renderPosts(data.posts);
                    })
                    .catch(function(e) {
                        console.error('Search error', e);
                    })
                    .finally(hideSpinner);
            }, 320);

            searchBox && searchBox.addEventListener('input', function() {
                doSearch(searchBox.value);
            });


            function applyFilters() {
                var f = getFilters();
                var params = new URLSearchParams();
                if (f.country) params.append('country', f.country);
                if (f.cost) params.append('cost', f.cost);
                f.genres.forEach(function(g) {
                    params.append('genre[]', g);
                });

                showSpinner();
                fetch('index.php?page=ajax&type=posts_filter&' + params.toString())
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) renderPosts(data.posts);
                    })
                    .catch(function(e) {
                        console.error('Filter error', e);
                    })
                    .finally(hideSpinner);
            }

            countryFilter && countryFilter.addEventListener('change', applyFilters);
            document.querySelectorAll('.genre-check').forEach(function(el) {
                el.addEventListener('change', applyFilters);
            });
            document.querySelectorAll('.cost-radio').forEach(function(el) {
                el.addEventListener('change', applyFilters);
            });

            function resetAll() {
                if (countryFilter) countryFilter.value = '';
                document.querySelectorAll('.genre-check').forEach(function(el) {
                    el.checked = false;
                });
                var anyRadio = document.querySelector('.cost-radio[value=""]');
                if (anyRadio) anyRadio.checked = true;
                if (searchBox) searchBox.value = '';
                applyFilters();
            }

            resetBtn && resetBtn.addEventListener('click', resetAll);
            resetFromEmpty && resetFromEmpty.addEventListener('click', resetAll);

        })();
    </script>

</body>

</html>