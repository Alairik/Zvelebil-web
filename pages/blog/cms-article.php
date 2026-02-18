<?php
/**
 * Dynamic article template - fetches from CMS API and renders with website CSS
 */

// Get slug from query parameter (set by .htaccess rewrite)
$slug = isset($_GET['article_slug']) ? trim($_GET['article_slug']) : '';
if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    header('Location: /blog');
    exit;
}

// Fetch article from CMS API
$apiUrl = 'https://zvelebil.online/admin/api/articles.php?slug=' . urlencode($slug);
$context = stream_context_create(['http' => ['timeout' => 5]]);
$json = @file_get_contents($apiUrl, false, $context);

if ($json === false) {
    // Fallback: try local API
    $apiUrl = 'http://localhost/admin/api/articles.php?slug=' . urlencode($slug);
    $json = @file_get_contents($apiUrl, false, $context);
}

if ($json === false) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Článek se nepodařilo načíst.';
    exit;
}

$article = json_decode($json, true);

if (!$article || isset($article['error'])) {
    header('HTTP/1.0 404 Not Found');
    header('Location: /blog');
    exit;
}

// Prepare data
$title = htmlspecialchars($article['title'] ?? '', ENT_QUOTES, 'UTF-8');
$excerpt = htmlspecialchars($article['excerpt'] ?? '', ENT_QUOTES, 'UTF-8');
$content = $article['content'] ?? '';
$category = htmlspecialchars($article['category_name'] ?? '', ENT_QUOTES, 'UTF-8');
$author = htmlspecialchars($article['author_name'] ?? 'Zvelebil.online', ENT_QUOTES, 'UTF-8');
$image = $article['featured_image_url'] ?? '';
$created = $article['created_at'] ?? '';
$tags = $article['tags'] ?? [];
$canonicalUrl = 'https://zvelebil.online/blog/' . $slug;

// Format date
$dateFormatted = '';
if (!empty($created)) {
    $months = ['ledna','února','března','dubna','května','června','července','srpna','září','října','listopadu','prosince'];
    $ts = strtotime($created);
    $dateFormatted = date('j', $ts) . '. ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    $dateISO = date('Y-m-d', $ts);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <!-- Cookie Consent - MUSÍ být před GTM -->
    <script src="/js/cookie-consent.js"></script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TGK8FRRL');</script>
    <!-- End Google Tag Manager -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $excerpt ?>">
    <meta name="author" content="Zvelebil.online">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <meta property="og:title" content="<?= $title ?> | Zvelebil.online">
    <meta property="og:description" content="<?= $excerpt ?>">
<?php if (!empty($image)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<?php if (!empty($dateISO)): ?>
    <meta property="article:published_time" content="<?= $dateISO ?>">
<?php endif; ?>
    <meta property="article:author" content="https://zvelebil.online">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?>">
    <meta name="twitter:description" content="<?= $excerpt ?>">
<?php if (!empty($image)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

    <title><?= $title ?> | Zvelebil.online</title>
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= $title ?>",
        "description": "<?= $excerpt ?>",
<?php if (!empty($image)): ?>
        "image": "<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>",
<?php endif; ?>
<?php if (!empty($dateISO)): ?>
        "datePublished": "<?= $dateISO ?>",
<?php endif; ?>
        "author": {
            "@type": "Person",
            "name": "Zvelebil.online",
            "url": "https://zvelebil.online"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Zvelebil.online",
            "url": "https://zvelebil.online"
        }
    }
    </script>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TGK8FRRL"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Navbar Background Bar -->
    <div class="navbar-spacer"></div>

    <!-- Navigation -->
    <nav class="navbar scrolled" id="navbar">
        <div class="nav-container">
            <a href="/" class="logo">zvelebil<span>.online</span></a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/#sluzby">Služby</a></li>
                <li><a href="/#proc-ja">Proč já</a></li>
                <li><a href="/#cenik">Ceník</a></li>
                <li><a href="/#jak-to-funguje">Jak to funguje</a></li>
                <li><a href="/#o-mne">O mně</a></li>
                <li><a href="/blog" class="active">Blog</a></li>
                <li><a href="/#kontakt" class="nav-cta">Napište mi</a></li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Menu Toggle -->
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Article Header -->
    <header class="article-header">
        <div class="container">
            <div class="article-header-content<?= empty($image) ? ' no-image' : '' ?>">
<?php if (!empty($image)): ?>
                <div class="article-featured-image">
                    <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $title ?>">
                </div>
<?php endif; ?>
                <div class="article-header-text">
<?php if (!empty($category)): ?>
                    <span class="article-category"><?= $category ?></span>
<?php endif; ?>
                    <h1 class="article-title"><?= $title ?></h1>
<?php if (!empty($excerpt)): ?>
                    <p class="article-perex"><?= $excerpt ?></p>
<?php endif; ?>
                    <div class="article-meta">
<?php if (!empty($dateFormatted)): ?>
                        <span class="article-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <?= $dateFormatted ?>
                        </span>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Article Body -->
    <main class="article-body">
        <div class="container">
            <div class="article-container no-sidebar">
                <!-- Article Content -->
                <article class="article-content cms-content">
                    <?= $content ?>

                    <!-- Article Footer -->
                    <footer class="article-footer">
<?php if (!empty($tags)): ?>
                        <div class="article-tags">
<?php foreach ($tags as $tag): ?>
                            <span class="skill-tag"><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></span>
<?php endforeach; ?>
                        </div>
<?php endif; ?>

                        <div class="article-author-box">
                            <img src="/assets/Foto.jpg" alt="Autor" class="article-author-photo">
                            <div class="article-author-info">
                                <h4>Zvelebil.online</h4>
                                <p>Tvořím moderní weby s pomocí AI. 10+ let zkušeností v marketingu a webdesignu.</p>
                            </div>
                        </div>
                    </footer>
                </article>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="/" class="logo">zvelebil<span>.online</span></a>
                    <p>Moderní weby s pomocí AI</p>
                </div>
                <div class="footer-links">
                    <a href="/#sluzby">Služby</a>
                    <a href="/#cenik">Ceník</a>
                    <a href="/#kontakt">Kontakt</a>
                    <a href="/ochrana-soukromi">Ochrana soukromí</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 zvelebil.online. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html>
