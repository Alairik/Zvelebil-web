<?php
session_start();

// Ochrana proti přímému přístupu
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

// CSRF ochrana
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    die("Neplatný bezpečnostní token. Vraťte se zpět a zkuste to znovu.");
}
// Invalidate token after use (single use)
unset($_SESSION['csrf_token']);

// Rate limiting - max 3 odeslání za 10 minut
$now = time();
$_SESSION['mail_attempts'] = array_filter(
    $_SESSION['mail_attempts'] ?? [],
    fn($t) => $now - $t < 600
);
if (count($_SESSION['mail_attempts']) >= 3) {
    die("Příliš mnoho odeslaných zpráv. Zkuste to prosím za několik minut.");
}

// Honeypot - ochrana proti spamu (skrytá pole v formuláři)
if (!empty($_POST["website"]) || !empty($_POST["company"])) {
    // Bot vyplnil honeypot pole
    http_response_code(403);
    die("Spam detekován.");
}

// Časová kontrola - formulář odeslaný příliš rychle je pravděpodobně bot
if (!empty($_POST["_timestamp"])) {
    $submitTime = intval($_POST["_timestamp"]);
    $nowMs = round(microtime(true) * 1000); // aktuální čas v ms
    $elapsed = $nowMs - $submitTime;

    // Méně než 3 sekundy = pravděpodobně bot
    if ($elapsed < 3000) {
        http_response_code(403);
        die("Spam detekován.");
    }

    // Více než 1 hodina = pravděpodobně replay útok nebo stará stránka
    if ($elapsed > 3600000) {
        die("Formulář vypršel. Obnovte prosím stránku a zkuste to znovu.");
    }
} else {
    // Chybí timestamp = pravděpodobně přímý POST bez JS
    http_response_code(403);
    die("Spam detekován.");
}

// Validace povinných polí
if (empty($_POST["name"]) || empty($_POST["email"]) || empty($_POST["message"])) {
    die("Vyplňte prosím všechna povinná pole.");
}

// Validace emailu
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Zadejte prosím platný email.");
}

// Validace GDPR souhlasu
if (empty($_POST["gdpr_consent"])) {
    die("Pro odeslání formuláře musíte souhlasit se zpracováním osobních údajů.");
}

// Sanitizace dat
$jmeno = htmlspecialchars(trim($_POST["name"]), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);

// Ochrana proti email header injection - odstranění newlines
$email = str_replace(["\r", "\n", "%0a", "%0d"], '', $email);
$jmeno = str_replace(["\r", "\n", "%0a", "%0d"], '', $jmeno);

$zprava = htmlspecialchars(trim($_POST["message"]), ENT_QUOTES, 'UTF-8');

// Volitelná pole
$projekt = isset($_POST["project"]) ? htmlspecialchars(trim($_POST["project"]), ENT_QUOTES, 'UTF-8') : "Neuvedeno";
$rozpocet = isset($_POST["budget"]) ? htmlspecialchars(trim($_POST["budget"]), ENT_QUOTES, 'UTF-8') : "Neuvedeno";

// Příjemce
$to = "info@zvelebil.online";

// Předmět
$subject = "=?UTF-8?B?" . base64_encode("Nová zpráva z webu zvelebil.online") . "?=";

// Tělo emailu
$body = "Nová zpráva z kontaktního formuláře\n";
$body .= "================================\n\n";
$body .= "Jméno: $jmeno\n";
$body .= "Email: $email\n";
$body .= "Typ projektu: $projekt\n";
$body .= "Rozpočet: $rozpocet\n\n";
$body .= "Zpráva:\n";
$body .= "--------------------------------\n";
$body .= "$zprava\n";
$body .= "--------------------------------\n\n";
$body .= "Odesláno: " . date("d.m.Y H:i:s") . "\n";
$body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Hlavičky
$headers = "From: noreply@zvelebil.online\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Odeslání hlavního emailu (vám)
$mailSent = mail($to, $subject, $body, $headers);

// Potvrzovací email odesilateli
if ($mailSent) {
    // Zaznamenat úspěšné odeslání pro rate limiting
    $_SESSION['mail_attempts'][] = $now;

    $confirmSubject = "=?UTF-8?B?" . base64_encode("Děkuji za Vaši zprávu | zvelebil.online") . "?=";

    $confirmBody = "Dobrý den, $jmeno,\n\n";
    $confirmBody .= "děkuji za Vaši zprávu. Obdržel jsem ji a ozvu se Vám co nejdříve, obvykle do 24 hodin.\n\n";
    $confirmBody .= "Pro připomenutí, zde je kopie Vaší zprávy:\n";
    $confirmBody .= "--------------------------------\n";
    $confirmBody .= "$zprava\n";
    $confirmBody .= "--------------------------------\n\n";
    $confirmBody .= "S pozdravem,\n";
    $confirmBody .= "Petr Zvelebil\n";
    $confirmBody .= "zvelebil.online\n";

    $confirmHeaders = "From: info@zvelebil.online\r\n";
    $confirmHeaders .= "Reply-To: info@zvelebil.online\r\n";
    $confirmHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $confirmHeaders .= "X-Mailer: PHP/" . phpversion();

    mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

    header("Location: pages/kontakt-dekuji.html");
    exit;
} else {
    die("Omlouváme se, při odesílání došlo k chybě. Zkuste to prosím znovu nebo napište přímo na info@zvelebil.online");
}
?>
