<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? SITE_NAME) ?></title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; color: #333; line-height: 1.7; }
        a { color: #4361ee; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .site-header { background: #1a1a2e; color: #fff; padding: 1.5rem 0; }
        .site-header .container { display: flex; justify-content: space-between; align-items: center; }
        .site-header h1 { font-size: 1.5rem; }
        .site-header h1 a { color: #fff; }
        .site-header h1 a:hover { text-decoration: none; }
        .site-nav a { color: rgba(255,255,255,.8); margin-left: 1.5rem; }
        .site-nav a:hover { color: #fff; }

        .container { max-width: 1100px; margin: 0 auto; padding: 0 1rem; }
        .main-layout { display: grid; grid-template-columns: 1fr 280px; gap: 2rem; margin: 2rem auto; }

        .article-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1.5rem; overflow: hidden; }
        .article-card-image { width: 100%; height: 200px; object-fit: cover; }
        .article-card-body { padding: 1.5rem; }
        .article-card-body h2 { margin-bottom: .5rem; font-size: 1.3rem; }
        .article-card-body h2 a { color: #1a1a2e; }
        .article-meta { color: #888; font-size: .85rem; margin-bottom: .8rem; }
        .article-meta span { margin-right: 1rem; }
        .article-excerpt { color: #555; }

        .article-full { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); padding: 2rem; }
        .article-full h1 { font-size: 2rem; margin-bottom: .5rem; color: #1a1a2e; }
        .article-full .article-meta { margin-bottom: 1.5rem; }
        .article-full .featured-img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 4px; margin-bottom: 1.5rem; }
        .article-content { line-height: 1.8; }
        .article-content h2 { margin: 1.5em 0 .5em; }
        .article-content h3 { margin: 1.2em 0 .5em; }
        .article-content p { margin: .8em 0; }
        .article-content img { max-width: 100%; border-radius: 4px; }
        .article-content blockquote { border-left: 4px solid #4361ee; padding: .5rem 1rem; margin: 1em 0; background: #f0f2f5; }
        .article-tags { margin-top: 1.5rem; display: flex; gap: .5rem; flex-wrap: wrap; }
        .tag-badge { display: inline-block; background: #e8eaf6; color: #4361ee; padding: .2rem .7rem; border-radius: 20px; font-size: .8rem; }
        .tag-badge:hover { background: #4361ee; color: #fff; text-decoration: none; }

        .sidebar .widget { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); padding: 1.2rem; margin-bottom: 1.5rem; }
        .sidebar .widget h3 { font-size: 1rem; margin-bottom: .8rem; color: #1a1a2e; border-bottom: 2px solid #4361ee; padding-bottom: .3rem; }
        .sidebar .widget ul { list-style: none; }
        .sidebar .widget ul li { padding: .3rem 0; }
        .sidebar .widget ul li a { color: #555; }
        .sidebar .widget ul li a:hover { color: #4361ee; }
        .sidebar .widget .tag-cloud { display: flex; flex-wrap: wrap; gap: .3rem; }

        .pagination { display: flex; gap: .3rem; justify-content: center; margin: 2rem 0; }
        .pagination a, .pagination span { display: inline-block; padding: .5rem 1rem; border-radius: 4px; border: 1px solid #ddd; color: #333; background: #fff; }
        .pagination a:hover { background: #f0f2f5; text-decoration: none; }
        .pagination .active { background: #4361ee; color: #fff; border-color: #4361ee; }

        .site-footer { background: #1a1a2e; color: rgba(255,255,255,.6); text-align: center; padding: 1.5rem 0; margin-top: 2rem; font-size: .9rem; }

        .not-found { text-align: center; padding: 4rem 0; }
        .not-found h1 { font-size: 4rem; color: #ddd; }

        @media (max-width: 768px) {
            .main-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1><a href="<?= SITE_URL ?>/"><?= h(SITE_NAME) ?></a></h1>
        <nav class="site-nav">
            <a href="<?= SITE_URL ?>/">Domů</a>
            <?php foreach ($allCategories as $cat): ?>
                <a href="<?= SITE_URL ?>/kategorie/<?= h($cat['slug']) ?>"><?= h($cat['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<div class="container">
<div class="main-layout">
<div class="main-content">
