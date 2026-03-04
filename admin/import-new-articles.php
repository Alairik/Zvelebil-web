<?php
/**
 * Import nových článků do CMS databáze
 * Spustit jednou na: https://zvelebil.online/admin/import-new-articles.php
 * Pak SMAZAT!
 */

require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$db = db_connect();

// Získat ID kategorií
$stmt = $db->prepare('SELECT id FROM categories WHERE slug = ?');
$stmt->execute(['webdesign']);
$webdesignCategoryId = $stmt->fetchColumn();

// Vytvořit kategorii "Marketing" pokud neexistuje
$stmt->execute(['marketing']);
$marketingCategoryId = $stmt->fetchColumn();

if (!$marketingCategoryId) {
    $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
    $stmt->execute(['Marketing', 'marketing']);
    $marketingCategoryId = $db->lastInsertId();
    echo "✅ Kategorie 'Marketing' vytvořena (ID: {$marketingCategoryId})<br>";
} else {
    echo "ℹ️ Kategorie 'Marketing' již existuje (ID: {$marketingCategoryId})<br>";
}

// Vytvořit kategorii "SEO" pokud neexistuje
$stmt = $db->prepare('SELECT id FROM categories WHERE slug = ?');
$stmt->execute(['seo']);
$seoCategoryId = $stmt->fetchColumn();

if (!$seoCategoryId) {
    $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
    $stmt->execute(['SEO', 'seo']);
    $seoCategoryId = $db->lastInsertId();
    echo "✅ Kategorie 'SEO' vytvořena (ID: {$seoCategoryId})<br>";
} else {
    echo "ℹ️ Kategorie 'SEO' již existuje (ID: {$seoCategoryId})<br>";
}

// Získat ID admin uživatele
$stmt = $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
$authorId = $stmt->fetchColumn();
if (!$authorId) {
    die("❌ Nebyl nalezen žádný admin uživatel.");
}

echo "<br><strong>Importuji články...</strong><br><br>";

// ============================
// ČLÁNEK 1: Kolik stojí web v roce 2025
// ============================
$article1 = [
    'title' => 'Kolik stojí web v roce 2025?',
    'slug' => 'kolik-stoji-web-v-roce-2025',
    'category_id' => $marketingCategoryId,
    'excerpt' => 'Kolik stojí web v roce 2025? Realistický přehled cen od levných řešení po profi weby. Zjistěte, za co platíte a kde můžete ušetřit.',
    'content' => <<<'HTML'
<!-- TL;DR -->
<div class="article-tldr">
    <h2 class="article-tldr-title">TL;DR</h2>
    <ul>
        <li><strong>DIY řešení</strong> (Wix, Squarespace) – 0-5 000 Kč + měsíční poplatky</li>
        <li><strong>Jednoduchý web na míru</strong> – 8 000-20 000 Kč</li>
        <li><strong>Profesionální firemní web</strong> – 30 000-80 000 Kč</li>
        <li><strong>E-shop</strong> – 50 000-200 000+ Kč</li>
        <li>Nezapomeňte na roční náklady: doména (~300 Kč) + hosting (~1 500 Kč)</li>
    </ul>
</div>

<p>„Kolik stojí web?" je otázka, na kterou dostanete odpovědi v rozmezí od „udělej si sám zadarmo" po „připravte si půl milionu". Pravda je někde uprostřed a závisí na tom, co přesně potřebujete.</p>

<h2 id="z-ceho-se-sklada-cena">Z čeho se skládá cena webu?</h2>

<p>Cena webu není jen „design + programování". Skládá se z několika částí:</p>

<ul>
    <li><strong>Analýza a strategie</strong> – pochopení Vašeho byznysu, cílové skupiny, konkurence</li>
    <li><strong>Design</strong> – vizuální návrh, který odpovídá Vaší značce</li>
    <li><strong>Vývoj</strong> – technická realizace, kódování, responzivita</li>
    <li><strong>Obsah</strong> – texty, fotky, videa (často podceňovaná položka)</li>
    <li><strong>SEO základ</strong> – technická optimalizace pro vyhledávače</li>
    <li><strong>Testování</strong> – kontrola funkčnosti na různých zařízeních</li>
</ul>

<p>Když nějaká agentura nabízí „web za 5 000 Kč", pravděpodobně některé z těchto kroků přeskakuje. A to se Vám časem vrátí.</p>

<h2 id="cenove-kategorie">Cenové kategorie webů v roce 2025</h2>

<h3>DIY stavebnice (0-5 000 Kč)</h3>

<p>Wix, Squarespace, Webnode. Vytvoříte si web sami pomocí šablon. Výhoda: nízká počáteční cena. Nevýhoda: omezené možnosti, měsíční poplatky (200-500 Kč), generický vzhled, horší SEO.</p>

<p><strong>Pro koho:</strong> Hobby projekty, testování nápadu, osobní blogy.</p>

<h3>Jednoduchý web na míru (8 000-20 000 Kč)</h3>

<p>Jednostránkový web nebo malý vícestránkový web. Čistý design, základní SEO, responzivní zobrazení. Většinou bez CMS nebo s jednoduchým řešením.</p>

<p><strong>Pro koho:</strong> Freelanceři, malé služby, osobní značky, startupy.</p>

<h3>Profesionální firemní web (30 000-80 000 Kč)</h3>

<p>Více stránek, CMS pro snadnou správu obsahu, pokročilé SEO, integrace s nástroji (CRM, newsletter), unikátní design.</p>

<p><strong>Pro koho:</strong> Zavedené firmy, B2B služby, společnosti s více produkty/službami.</p>

<h3>E-shop (50 000-200 000+ Kč)</h3>

<p>Kompletní e-commerce řešení s košíkem, platební bránou, správou skladu, napojením na účetnictví. Cena závisí na počtu produktů a požadované funkčnosti.</p>

<p><strong>Pro koho:</strong> Každý, kdo chce prodávat produkty online.</p>

<h2 id="skryte-naklady">Skryté náklady, na které se zapomíná</h2>

<h3>Roční provozní náklady</h3>

<ul>
    <li><strong>Doména</strong> – 250-500 Kč/rok</li>
    <li><strong>Hosting</strong> – 1 000-3 000 Kč/rok (záleží na návštěvnosti)</li>
    <li><strong>SSL certifikát</strong> – často v ceně hostingu, jinak 500-1 500 Kč/rok</li>
    <li><strong>Zálohy a bezpečnost</strong> – 0-2 000 Kč/rok</li>
</ul>

<h3>Jednorázové náklady, které se opakují</h3>

<ul>
    <li><strong>Aktualizace obsahu</strong> – záleží na rozsahu změn</li>
    <li><strong>Technická údržba</strong> – aktualizace systému, opravy</li>
    <li><strong>Redesign</strong> – obvykle každých 3-5 let</li>
</ul>

<h2 id="kde-usetrit">Kde můžete ušetřit (a kde ne)</h2>

<h3>Ušetřit můžete na:</h3>

<ul>
    <li><strong>Rozsahu</strong> – začněte s menším webem a rozšiřte ho později</li>
    <li><strong>Funkcích</strong> – implementujte jen to, co opravdu potřebujete</li>
    <li><strong>Obsahu</strong> – dodejte vlastní texty a fotky (ale ať jsou kvalitní)</li>
    <li><strong>Animacích</strong> – jednoduché řešení funguje stejně dobře</li>
</ul>

<h3>Nešetřete na:</h3>

<ul>
    <li><strong>Responzivitě</strong> – přes 60 % návštěv je z mobilů</li>
    <li><strong>Rychlosti</strong> – pomalý web = ztracení zákazníci</li>
    <li><strong>Základním SEO</strong> – jinak Vás nikdo nenajde</li>
    <li><strong>Bezpečnosti</strong> – hacknutý web stojí mnohem víc</li>
</ul>

<h2 id="zaver">Jak tedy vybrat správnou cenovou kategorii?</h2>

<p>Zamyslete se nad těmito otázkami:</p>

<ol>
    <li><strong>Jaký je Váš rozpočet?</strong> – Buďte realistický/á.</li>
    <li><strong>Co web musí umět?</strong> – Oddělte „nice to have" od „must have".</li>
    <li><strong>Jak rychle potřebujete výsledky?</strong> – Levnější řešení často znamená delší cestu k cíli.</li>
    <li><strong>Kolik času můžete investovat?</strong> – DIY řešení vyžaduje Váš čas.</li>
</ol>

<p>Nejlepší investice je web, který odpovídá Vašim aktuálním potřebám s možností růstu. Nepotřebujete hned e-shop za 150 000 Kč, když začínáte a máte 5 produktů. Ale také nemá smysl šetřit tisíce, když pak web nepřináší zákazníky.</p>

<p>Potřebujete pomoct s odhadem? <a href="/#kontakt">Napište mi</a> – proberu s Vámi Vaše potřeby a navrhnu řešení, které dává smysl pro Váš rozpočet.</p>
HTML
];

// ============================
// ČLÁNEK 2: SEO základy pro malé firmy
// ============================
$article2 = [
    'title' => 'SEO základy pro malé firmy',
    'slug' => 'seo-zaklady-pro-male-firmy',
    'category_id' => $seoCategoryId,
    'excerpt' => 'SEO pro malé firmy: praktický průvodce optimalizací pro vyhledávače. Naučte se základy, které můžete udělat sami a zdarma.',
    'content' => <<<'HTML'
<!-- TL;DR -->
<div class="article-tldr">
    <h2 class="article-tldr-title">TL;DR</h2>
    <ul>
        <li><strong>Google Moje firma</strong> – nejdůležitější krok pro lokální byznys (zdarma)</li>
        <li><strong>Klíčová slova</strong> – pište o tom, co lidé hledají</li>
        <li><strong>Kvalitní obsah</strong> – odpovídejte na otázky zákazníků</li>
        <li><strong>Rychlost webu</strong> – pomalý web = horší pozice</li>
        <li><strong>Mobilní verze</strong> – Google hodnotí primárně mobilní zobrazení</li>
    </ul>
</div>

<p>SEO (Search Engine Optimization) je optimalizace pro vyhledávače – zjednodušeně řečeno, snaha být vidět na Googlu. Pro malé firmy je to jeden z nejefektivnějších marketingových kanálů, protože na rozdíl od reklamy platíte jednorázově a výsledky přetrvávají.</p>

<h2 id="co-je-seo">Co je SEO a proč na něm záleží</h2>

<p>Když někdo hledá na Googlu „kadeřnictví Praha 3" nebo „oprava oken Brno", zobrazí se mu výsledky. SEO je snaha, aby se tam zobrazil Váš web. Organické (neplacené) výsledky mají vyšší důvěryhodnost než reklamy a přinášejí dlouhodobý efekt.</p>

<p><strong>Proč je SEO důležité pro malé firmy:</strong></p>

<ul>
    <li>93 % online zkušeností začíná vyhledávačem</li>
    <li>75 % uživatelů nikdy nepřejde na druhou stránku výsledků</li>
    <li>Lokální vyhledávání („… blízko mě") roste o 150 % ročně</li>
</ul>

<h2 id="google-moje-firma">1. Google Moje firma – začněte zde</h2>

<p>Pokud máte lokální byznys, je Google Moje firma (Google Business Profile) absolutní základ. Je to zdarma a trvá 15 minut na nastavení.</p>

<h3>Co získáte:</h3>

<ul>
    <li>Zobrazení v mapách Google</li>
    <li>Informační panel při vyhledávání názvu firmy</li>
    <li>Možnost sbírat recenze</li>
    <li>Statistiky, kolik lidí Vás našlo</li>
</ul>

<h3>Jak na to:</h3>

<ol>
    <li>Jděte na <a href="https://business.google.com" target="_blank" rel="noopener">business.google.com</a></li>
    <li>Vytvořte nebo přihlaste se k účtu Google</li>
    <li>Přidejte firmu a vyplňte všechny údaje</li>
    <li>Ověřte vlastnictví (obvykle pohlednicí s kódem)</li>
    <li>Přidejte fotky, otevírací dobu, služby</li>
</ol>

<p><strong>Tip:</strong> Pravidelně přidávejte příspěvky a fotky. Aktivní profily se zobrazují výš.</p>

<h2 id="klicova-slova">2. Klíčová slova – pište o tom, co lidé hledají</h2>

<p>Klíčová slova jsou výrazy, které lidé zadávají do vyhledávače. Váš web by měl obsahovat slova, která Vaši potenciální zákazníci používají.</p>

<h3>Jak najít správná klíčová slova:</h3>

<ul>
    <li><strong>Přemýšlejte jako zákazník</strong> – Co by zadal do Googlu, kdyby Vás hledal?</li>
    <li><strong>Použijte Google</strong> – Začněte psát dotaz a sledujte návrhy</li>
    <li><strong>Podívejte se na „Lidé se také ptají"</strong> – Sekce ve výsledcích vyhledávání</li>
    <li><strong>Ubersuggest</strong> – Bezplatný nástroj pro hledání klíčových slov</li>
</ul>

<h3>Příklad pro kadeřnictví:</h3>

<ul>
    <li>„kadeřnictví Praha 3" – lokální vyhledávání</li>
    <li>„pánské stříhání cena" – informační dotaz</li>
    <li>„balayage vlasy" – specifická služba</li>
</ul>

<h2 id="on-page-seo">3. On-page SEO – optimalizace obsahu</h2>

<p>On-page SEO jsou úpravy přímo na Vašem webu. Základní pravidla:</p>

<h3>Title tag (titulek stránky)</h3>

<p>Zobrazuje se ve výsledcích vyhledávání a v záložce prohlížeče. Měl by obsahovat klíčové slovo a být do 60 znaků.</p>

<p><strong>Špatně:</strong> „Úvod | Náš web"<br>
<strong>Dobře:</strong> „Kadeřnictví Praha 3 | Pánské a dámské stříhání | Salon Jana"</p>

<h3>Meta description</h3>

<p>Popisek pod titulkem ve vyhledávání. Neovlivňuje přímo pozici, ale ovlivňuje, jestli lidé kliknou. Do 155 znaků.</p>

<h3>Nadpisy (H1, H2, H3)</h3>

<ul>
    <li>Každá stránka má jeden H1 – hlavní nadpis</li>
    <li>H2 pro sekce, H3 pro podsekce</li>
    <li>Používejte klíčová slova přirozeně</li>
</ul>

<h3>Obsah</h3>

<ul>
    <li>Pište pro lidi, ne pro roboty</li>
    <li>Odpovídejte na otázky zákazníků</li>
    <li>Delší obsah (500+ slov) rankuje lépe, ale kvalita je důležitější než kvantita</li>
</ul>

<h2 id="technicke-seo">4. Technické SEO – základy</h2>

<h3>Rychlost webu</h3>

<p>Google preferuje rychlé weby. Otestujte si rychlost na <a href="https://pagespeed.web.dev" target="_blank" rel="noopener">PageSpeed Insights</a>.</p>

<p><strong>Nejčastější problémy:</strong></p>

<ul>
    <li>Velké obrázky – komprimujte je (TinyPNG, Squoosh)</li>
    <li>Pomalý hosting – investujte do kvalitního</li>
    <li>Příliš mnoho pluginů (WordPress)</li>
</ul>

<h3>Mobilní zobrazení</h3>

<p>Google používá „mobile-first indexing" – hodnotí primárně mobilní verzi. Web musí být responzivní a dobře čitelný na telefonu.</p>

<h3>HTTPS</h3>

<p>SSL certifikát je nutnost. Bez něj Google označí web jako „nezabezpečený" a penalizuje ho ve výsledcích.</p>

<h2 id="co-nedela">Co nedělat – časté chyby</h2>

<ul>
    <li><strong>Kupovat odkazy</strong> – Google to pozná a penalizuje</li>
    <li><strong>Přehánět s klíčovými slovy</strong> – „keyword stuffing" škodí</li>
    <li><strong>Kopírovat obsah</strong> – duplicitní obsah = penalizace</li>
    <li><strong>Ignorovat mobilní verzi</strong> – většina návštěvníků je z mobilu</li>
    <li><strong>Očekávat okamžité výsledky</strong> – SEO je maraton, ne sprint</li>
</ul>

<h2 id="zaver">Závěr a další kroky</h2>

<p>SEO není magie ani věda pouze pro experty. Základy zvládne každý:</p>

<ol>
    <li><strong>Hned:</strong> Vytvořte Google Moje firma profil</li>
    <li><strong>Tento týden:</strong> Zkontrolujte title tagy a meta descriptions</li>
    <li><strong>Tento měsíc:</strong> Napište jeden článek odpovídající na otázku zákazníků</li>
    <li><strong>Průběžně:</strong> Sbírejte recenze, přidávejte obsah</li>
</ol>

<p>Výsledky SEO se projevují postupně – první zlepšení uvidíte za 3-6 měsíců. Ale na rozdíl od placené reklamy, jakmile jednou dosáhnete dobré pozice, zůstanete tam s minimálními náklady.</p>

<p>Potřebujete pomoct s technickým SEO nebo nevíte, kde začít? <a href="/#kontakt">Ozvěte se mi</a> – probereme to.</p>
HTML
];

// ============================
// ČLÁNEK 3: Proč každý podnikatel potřebuje web
// ============================
$article3 = [
    'title' => 'Proč každý podnikatel potřebuje web v roce 2025',
    'slug' => 'proc-kazdy-podnikatel-potrebuje-web',
    'category_id' => $marketingCategoryId,
    'excerpt' => 'Proč potřebujete web, i když máte dost zákazníků? 5 důvodů, proč je webová prezentace nezbytná pro každého podnikatele v roce 2025.',
    'content' => <<<'HTML'
<!-- TL;DR -->
<div class="article-tldr">
    <h2 class="article-tldr-title">TL;DR</h2>
    <ul>
        <li><strong>Důvěryhodnost</strong> – zákazníci si Vás ověřují online dřív, než zavolají</li>
        <li><strong>Viditelnost</strong> – web pracuje 24/7, i když spíte</li>
        <li><strong>Kontrola</strong> – na rozdíl od sociálních sítí, web vlastníte Vy</li>
        <li><strong>Konkurence</strong> – pokud nemáte web, zákazník najde toho, kdo ho má</li>
        <li><strong>Investice</strong> – jednorázový náklad s dlouhodobým přínosem</li>
    </ul>
</div>

<p>V době sociálních sítí se může zdát, že klasický web je přežitek. Proč platit za web, když máte Instagram? Realita je ale jiná – web a sociální sítě se doplňují, a web plní role, které sítě nahradit nemohou.</p>

<h2 id="duveryhodnost">1. Důvěryhodnost – první dojem se počítá</h2>

<p>Co uděláte, když se dozvíte o nové firmě? Většina lidí ji vyhledá na Googlu. A co najdou?</p>

<ul>
    <li><strong>S webem:</strong> Profesionální prezentace, kontakty, služby, reference</li>
    <li><strong>Bez webu:</strong> Možná Facebook stránka, možná nic</li>
</ul>

<p>Studie ukazují, že <strong>84 % lidí považuje firmu s webem za důvěryhodnější</strong> než firmu bez něj. Web signalizuje: „Jsme tu, bereme to vážně, můžete nám věřit."</p>

<p>Zvlášť důležité je to pro služby, kde zákazník riskuje – zdravotnictví, právní služby, finanční poradenství. Ale platí to i pro řemeslníky. Když si vybírám elektrikáře, dám přednost tomu s webem a referencemi před anonymním číslem z inzerce.</p>

<h2 id="viditelnost">2. Viditelnost 24/7 – web nikdy nespí</h2>

<p>Váš web je otevřený 24 hodin denně, 7 dní v týdnu. Zatímco spíte, zákazníci mohou:</p>

<ul>
    <li>Prohlížet Vaše služby</li>
    <li>Číst reference</li>
    <li>Zjistit ceník</li>
    <li>Odeslat poptávku</li>
</ul>

<p>Kolik poptávek přijde v 10 večer nebo v neděli ráno? S webem o ně nepřijdete. Formulář je vždy připravený.</p>

<p><strong>Příklad:</strong> Kadeřnictví s online rezervací získá zákazníky, kteří si chtějí zarezervovat termín ve 23:00. Kadeřnictví, kam musíte zavolat v pracovní době? To si zákazník rozmyslí.</p>

<h2 id="kontrola">3. Kontrola nad značkou – web je Váš</h2>

<p>Sociální sítě jsou skvělé, ale mají jeden problém: nevlastníte je.</p>

<ul>
    <li>Facebook může změnit algoritmus a Vaše příspěvky uvidí 5 % sledujících</li>
    <li>Instagram může zablokovat účet bez varování</li>
    <li>TikTok může být zakázaný (viz USA)</li>
</ul>

<p><strong>Web je Váš.</strong> Vy rozhodujete, co tam bude, jak to bude vypadat, jaká pravidla platí. Nemůže Vám ho nikdo vzít nebo změnit pravidla hry.</p>

<p>Ideální strategie? Používejte sociální sítě pro získání pozornosti, ale směrujte lidi na web pro konverzi. Sítě přitáhnou, web prodá.</p>

<h2 id="konkurence">4. Konkurence má web – Vy ne?</h2>

<p>Představte si situaci: Zákazník hledá „instalatér Brno". Najde tři firmy s webem a jednu bez. Komu zavolá?</p>

<p>Pravděpodobně ne té bez webu.</p>

<p>Když konkurence má web a Vy ne:</p>

<ul>
    <li>Zákazník Vás nenajde ve vyhledávání</li>
    <li>Nemůže si Vás ověřit</li>
    <li>Nemá důvod Vám věřit víc než konkurenci</li>
</ul>

<p>V některých oborech je konkurence online brutální. V jiných je příležitost – pokud lokální konkurence web nemá nebo má zastaralý, kvalitní web Vás může okamžitě odlišit.</p>

<h2 id="budoucnost">5. Investice do budoucnosti</h2>

<p>Web není náklad, je to investice. Na rozdíl od reklamy, která skončí ve chvíli, kdy přestanete platit, web pracuje dlouhodobě.</p>

<p><strong>Jednorázová investice:</strong></p>
<ul>
    <li>Vytvoření webu: 10 000 – 50 000 Kč (dle rozsahu)</li>
    <li>Roční provoz: ~2 000 Kč (doména + hosting)</li>
</ul>

<p><strong>Co získáte:</strong></p>
<ul>
    <li>Profesionální prezentaci na roky</li>
    <li>Základ pro online marketing</li>
    <li>Možnost měřit a optimalizovat</li>
    <li>Konkurenční výhodu</li>
</ul>

<p>Když to rozpočítáte na měsíce, web Vás vychází na pár stovek měsíčně. Jeden zákazník, kterého by jinak získala konkurence, tuto investici vrátí.</p>

<h2 id="namitky">Časté námitky (a proč neplatí)</h2>

<h3>„Mám dost zákazníků"</h3>

<p>Super! Ale co když se situace změní? Co když klíčový zákazník odejde? Web je pojistka a zdroj nových příležitostí. A i stávající zákazníci oceňují profesionální prezentaci.</p>

<h3>„Stačí mi Facebook/Instagram"</h3>

<p>Sociální sítě jsou doplněk, ne náhrada. Na sítích budujete komunitu, na webu konvertujete. A co lidé, kteří sítě nepoužívají? Starší zákazníci, B2B klientela?</p>

<h3>„Je to moc drahé"</h3>

<p>Jednoduchý web začíná na 8-10 000 Kč. To je méně než měsíční reklama na Facebooku. A na rozdíl od reklamy web funguje roky.</p>

<h3>„Nemám čas se o to starat"</h3>

<p>Moderní web nevyžaduje denní péči. Jednou za čas aktualizujete kontakty nebo přidáte referenci. To je vše. A můžete si najmout někoho na správu.</p>

<h2 id="zaver">Závěr: Web není luxus, je to základ</h2>

<p>V roce 2025 je web jako vizitka byla před 20 lety – bez ní můžete fungovat, ale působíte neprofesionálně. S ní ukazujete, že to myslíte vážně.</p>

<p>Nejde o to mít nejdražší nebo nejkomplexnější web. Jde o to mít <strong>funkční online prezentaci</strong>, která:</p>

<ul>
    <li>Řekne, kdo jste a co děláte</li>
    <li>Ukáže, proč si Vás vybrat</li>
    <li>Umožní snadný kontakt</li>
</ul>

<p>To je základ. Všechno ostatní je bonus.</p>

<p>Přemýšlíte o webu, ale nevíte, kde začít? <a href="/#kontakt">Napište mi</a> – probereme Vaše potřeby a najdeme řešení, které dává smysl.</p>
HTML
];

// ============================
// IMPORT ČLÁNKŮ
// ============================
$articles = [$article1, $article2, $article3];

foreach ($articles as $article) {
    // Zkontrolovat, jestli článek už existuje
    $stmt = $db->prepare('SELECT id FROM articles WHERE slug = ?');
    $stmt->execute([$article['slug']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        echo "⚠️ Článek '{$article['title']}' již existuje (ID: {$existingId}). Přeskakuji.<br>";
        continue;
    }

    // Vložit článek
    $stmt = $db->prepare('
        INSERT INTO articles (title, slug, content, excerpt, featured_image, category_id, author_id, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([
        $article['title'],
        $article['slug'],
        $article['content'],
        $article['excerpt'],
        null,
        $article['category_id'],
        $authorId,
        'published',
        '2025-03-02 10:00:00',
    ]);

    $articleId = $db->lastInsertId();
    echo "✅ Článek '{$article['title']}' importován (ID: {$articleId})<br>";
    echo "   → <a href='/blog/{$article['slug']}' target='_blank'>Zobrazit</a><br><br>";
}

echo "<br><strong>Import dokončen!</strong><br>";
echo "<a href='/blog'>Přejít na blog</a><br>";
echo "<br>⚠️ <strong>Smažte tento soubor po importu!</strong>";
