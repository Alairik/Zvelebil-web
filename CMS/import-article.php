<?php
/**
 * Import stávajícího článku do CMS databáze
 * Spustit jednou, pak SMAZAT!
 *
 * Použití: https://zvelebil.online/cms/import-article.php
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$db = db_connect();

// ============================
// 1. Vytvořit kategorii "Webdesign"
// ============================
$stmt = $db->prepare('SELECT id FROM categories WHERE slug = ?');
$stmt->execute(['webdesign']);
$categoryId = $stmt->fetchColumn();

if (!$categoryId) {
    $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
    $stmt->execute(['Webdesign', 'webdesign']);
    $categoryId = $db->lastInsertId();
    echo "✅ Kategorie 'Webdesign' vytvořena (ID: {$categoryId})<br>";
} else {
    echo "ℹ️ Kategorie 'Webdesign' již existuje (ID: {$categoryId})<br>";
}

// ============================
// 2. Vytvořit tagy
// ============================
$tagNames = ['Webdesign', 'SEO', 'E-shop', 'Landing page', 'Podnikání'];
$tagIds = [];

foreach ($tagNames as $tagName) {
    $tagSlug = slug($tagName);
    $stmt = $db->prepare('SELECT id FROM tags WHERE slug = ?');
    $stmt->execute([$tagSlug]);
    $tagId = $stmt->fetchColumn();

    if (!$tagId) {
        $stmt = $db->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
        $stmt->execute([$tagName, $tagSlug]);
        $tagId = $db->lastInsertId();
        echo "✅ Tag '{$tagName}' vytvořen (ID: {$tagId})<br>";
    } else {
        echo "ℹ️ Tag '{$tagName}' již existuje (ID: {$tagId})<br>";
    }
    $tagIds[] = $tagId;
}

// ============================
// 3. Vložit článek
// ============================
$articleSlug = 'jak-vybrat-spravny-typ-webu-pro-vas-byznys';

$stmt = $db->prepare('SELECT id FROM articles WHERE slug = ?');
$stmt->execute([$articleSlug]);
$existingId = $stmt->fetchColumn();

if ($existingId) {
    echo "<br>⚠️ Článek s tímto slugem již existuje (ID: {$existingId}). Import přeskočen.<br>";
    echo "<a href='admin/article-edit.php?id={$existingId}'>Upravit v adminu</a>";
    exit;
}

// Získat ID prvního admin uživatele
$stmt = $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
$authorId = $stmt->fetchColumn();
if (!$authorId) {
    die("❌ Nebyl nalezen žádný admin uživatel. Nejdříve spusťte install.php");
}

$content = <<<'HTML'
<!-- TL;DR -->
<div class="article-tldr">
    <h2 class="article-tldr-title">TL;DR</h2>
    <ul>
        <li><strong>Jednostránkový web</strong> – ideální pro freelancery, malé služby, landing pages</li>
        <li><strong>Vícestránkový web</strong> – pro firmy s více službami nebo produkty</li>
        <li><strong>E-shop</strong> – když potřebujete prodávat online s košíkem a platební bránou</li>
        <li>Rozhoduje: rozsah nabídky, rozpočet a cíl webu</li>
    </ul>
</div>

<p>Výběr správného typu webu je jedno z prvních rozhodnutí, které musíte udělat. A je důležitější, než si možná myslíte. Špatně zvolený formát může Vašemu byznysu uškodit – buď budete platit za něco, co nepotřebujete, nebo naopak budete omezeni tam, kde byste potřebovali více prostoru.</p>

<h2 id="jednostrankovy-web">Jednostránkový web (One-page)</h2>

<p>Jednostránkový web je přesně to, co název napovídá – veškerý obsah je na jedné stránce, kterou návštěvník scrolluje. Jednotlivé sekce jsou odděleny vizuálně, ale technicky jde o jeden HTML dokument.</p>

<h3>Kdy je jednostránkový web ideální volbou?</h3>

<ul>
    <li><strong>Freelanceři a osobní značky</strong> – fotografové, designéři, konzultanti</li>
    <li><strong>Malé služby</strong> – kadeřnictví, masáže, osobní trenéři</li>
    <li><strong>Landing pages</strong> – cílené stránky pro kampaně</li>
    <li><strong>Portfolia</strong> – prezentace prací</li>
    <li><strong>Události</strong> – svatby, konference, festivaly</li>
</ul>

<h3>Výhody</h3>

<ul>
    <li>Nižší cena a rychlejší realizace</li>
    <li>Jednoduchá navigace pro návštěvníky</li>
    <li>Lepší konverzní poměr (vše je na dosah)</li>
    <li>Snadná údržba</li>
</ul>

<h3>Nevýhody</h3>

<ul>
    <li>Omezené možnosti SEO (jedna stránka = méně klíčových slov)</li>
    <li>Může být nepřehledný při velkém množství obsahu</li>
    <li>Delší načítání, pokud obsahuje hodně obrázků</li>
</ul>

<h2 id="vicestrankovy-web">Vícestránkový web</h2>

<p>Klasický web s více podstránkami – úvodní stránka, o nás, služby, reference, kontakt. Každá stránka má vlastní URL a může být optimalizovaná na jiná klíčová slova.</p>

<h3>Kdy zvolit vícestránkový web?</h3>

<ul>
    <li><strong>Firmy s více službami</strong> – každá služba si zaslouží vlastní stránku</li>
    <li><strong>Společnosti s historií</strong> – potřebujete prostor pro příběh</li>
    <li><strong>B2B služby</strong> – kde rozhodování trvá déle a zákazník hledá detaily</li>
    <li><strong>Weby s blogem</strong> – pravidelný obsahový marketing</li>
</ul>

<h3>Výhody</h3>

<ul>
    <li>Lepší SEO – více stránek = více příležitostí k rankingu</li>
    <li>Přehledná struktura pro složitější nabídku</li>
    <li>Profesionálnější dojem pro větší firmy</li>
    <li>Snadnější analytika (které stránky fungují)</li>
</ul>

<h3>Nevýhody</h3>

<ul>
    <li>Vyšší cena a delší realizace</li>
    <li>Náročnější údržba</li>
    <li>Riziko, že se návštěvník ztratí</li>
</ul>

<h2 id="e-shop">E-shop</h2>

<p>E-shop je speciální kategorie – nejde jen o prezentaci, ale o kompletní prodejní systém s košíkem, platební bránou, správou objednávek a skladem.</p>

<h3>Kdy potřebujete e-shop?</h3>

<ul>
    <li>Prodáváte fyzické nebo digitální produkty</li>
    <li>Potřebujete automatizovaný prodejní proces</li>
    <li>Chcete přijímat platby online</li>
    <li>Máte více než 5-10 produktů</li>
</ul>

<h3>Alternativa: Katalog s poptávkovým formulářem</h3>

<p>Pokud máte méně produktů nebo prodáváte služby na míru, možná nepotřebujete plnohodnotný e-shop. Stačí katalog s možností poptávky – je levnější a jednodušší na správu.</p>

<h2 id="jak-se-rozhodnout">Jak se rozhodnout?</h2>

<p>Položte si tyto otázky:</p>

<ol>
    <li><strong>Kolik máte služeb/produktů?</strong> – Méně než 5? Jednostránkový web stačí.</li>
    <li><strong>Potřebujete blog?</strong> – Pokud ano, vícestránkový web je lepší volba.</li>
    <li><strong>Chcete prodávat online?</strong> – Pak potřebujete e-shop nebo alespoň katalog.</li>
    <li><strong>Jaký máte rozpočet?</strong> – Jednostránkový web je nejlevnější, e-shop nejdražší.</li>
    <li><strong>Jak důležité je SEO?</strong> – Pro lokální byznys stačí one-page, pro konkurenční obory je lepší více stránek.</li>
</ol>

<h2 id="zaver">Závěr</h2>

<p>Neexistuje univerzálně správná odpověď. Záleží na Vašem byznysu, cílech a rozpočtu. Pokud si nejste jistí, <a href="/#kontakt">napište mi</a> – probereme to a najdeme řešení, které dává smysl právě pro Vás.</p>

<p>A pamatujte: vždy je lepší začít s jednodušším webem a časem ho rozšířit, než se hned pouštět do komplexního projektu, který pak nevyužijete.</p>
HTML;

$excerpt = 'Jednostránkový web, vícestránková prezentace nebo e-shop? Praktický průvodce výběrem správného typu webu pro Váš byznys.';

$stmt = $db->prepare('
    INSERT INTO articles (title, slug, content, excerpt, featured_image, category_id, author_id, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
');
$stmt->execute([
    'Jak vybrat správný typ webu pro Váš byznys',
    $articleSlug,
    $content,
    $excerpt,
    null, // featured_image - can be uploaded later via admin
    $categoryId,
    $authorId,
    'published',
    '2025-01-15 10:00:00',
]);

$articleId = $db->lastInsertId();
echo "<br>✅ Článek importován (ID: {$articleId})<br>";

// ============================
// 4. Přiřadit tagy
// ============================
$stmt = $db->prepare('INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)');
foreach ($tagIds as $tagId) {
    $stmt->execute([$articleId, $tagId]);
}
echo "✅ Tagy přiřazeny (" . count($tagIds) . " tagů)<br>";

echo "<br><strong>Import dokončen!</strong><br>";
echo "<a href='admin/article-edit.php?id={$articleId}'>Upravit v adminu</a> | ";
echo "<a href='/blog/{$articleSlug}'>Zobrazit na webu</a><br>";
echo "<br>⚠️ <strong>Smažte tento soubor po importu!</strong>";
