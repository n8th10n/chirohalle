<?php
// Adminpaneel van Chiro Halle: leiding, foto's en contactgegevens aanpassen.
// Het wachtwoord wordt bij het eerste bezoek gekozen en alleen gehasht bewaard
// in data/admin.php (een PHP-bestand, zodat het via de browser niet leesbaar is).

require dirname(__DIR__) . '/includes/site.php';

const AUTH_FILE = DATA_DIR . '/admin.php';
const MAX_FAILS = 5;
const LOCK_SECONDS = 900;
const MAX_UPLOAD_BYTES = 12 * 1024 * 1024;

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

session_name('chirohalle_admin');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => dirname($_SERVER['SCRIPT_NAME']) . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ---------- Hulpfuncties ----------

function auth_load() {
    if (!is_file(AUTH_FILE)) return null;
    $a = include AUTH_FILE;
    return is_array($a) ? $a : null;
}

function auth_save(array $a) {
    ensure_dir(DATA_DIR);
    $php = "<?php\n// Automatisch aangemaakt door het adminpaneel. Niet aanpassen.\nreturn " . var_export($a, true) . ";\n";
    if (file_put_contents(AUTH_FILE, $php, LOCK_EX) === false) {
        throw new RuntimeException('Kan de data-map niet beschrijven. Controleer de schrijfrechten van de map "data".');
    }
    if (function_exists('opcache_invalidate')) @opcache_invalidate(AUTH_FILE, true);
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        throw new RuntimeException('De pagina was verlopen. Probeer opnieuw.');
    }
}

function is_logged_in() {
    return !empty($_SESSION['admin']);
}

function clean_text($v, $max = 300) {
    $v = trim(preg_replace('/\s+/u', ' ', (string) $v));
    return mb_substr($v, 0, $max);
}

function clean_url($v, $label) {
    $v = trim((string) $v);
    if ($v === '') return '';
    if (!preg_match('#^https?://#i', $v)) $v = 'https://' . $v;
    if (!filter_var($v, FILTER_VALIDATE_URL)) throw new RuntimeException("De link bij $label is ongeldig.");
    return $v;
}

// PNG met transparantie? (kleurtype 4 of 6, of een tRNS-blok)
function png_has_alpha($path) {
    $data = file_get_contents($path, false, null, 0, 1024 * 64);
    if (substr($data, 0, 8) !== "\x89PNG\r\n\x1a\n") return false;
    $colorType = ord($data[25]);
    return $colorType === 4 || $colorType === 6 || strpos($data, 'tRNS') !== false;
}

// Controleert en bewaart een geüploade foto. Geeft [pad, breedte, hoogte] of null als er geen bestand was.
function handle_upload($field, $label, $mustBeTransparentPng = false) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException("De foto bij $label is te groot.");
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException("Uploaden van de foto bij $label is mislukt.");
    }
    if ($f['size'] > MAX_UPLOAD_BYTES) throw new RuntimeException("De foto bij $label is groter dan 12 MB.");
    $info = @getimagesize($f['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException("Het bestand bij $label is geen JPG-, PNG- of WebP-foto.");
    }
    if ($mustBeTransparentPng && ($info[2] !== IMAGETYPE_PNG || !png_has_alpha($f['tmp_name']))) {
        throw new RuntimeException("De groepsfoto moet een uitgesneden PNG zijn (met transparante achtergrond).");
    }
    ensure_dir(UPLOAD_DIR);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException("Kan de foto bij $label niet bewaren.");
    }
    return ['data/uploads/' . $name, $info[0], $info[1]];
}

// Bouwt nieuwe inhoud op uit het formulier. Wat niet aanpasbaar is, blijft behouden.
function content_from_post(array $old, $withUploads = true) {
    $c = $old;

    $email = trim($_POST['email'] ?? '');
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Het e-mailadres is ongeldig.');
    $c['contact'] = [
        'email' => $email,
        'instagram' => clean_url($_POST['instagram'] ?? '', 'Instagram'),
        'facebook' => clean_url($_POST['facebook'] ?? '', 'Facebook'),
    ];

    if ($withUploads && $up = handle_upload('hero_foto', 'de groepsfoto bovenaan', true)) {
        $c['hero'] = ['foto' => $up[0], 'breedte' => $up[1], 'hoogte' => $up[2]];
    }

    $leden = [];
    foreach ((array) ($_POST['gl'] ?? []) as $row) {
        $naam = clean_text($row['naam'] ?? '', 60);
        if ($naam === '') continue;
        $leden[] = ['naam' => $naam, 'telefoon' => clean_text($row['telefoon'] ?? '', 30)];
    }
    $c['groepsleiding']['leden'] = $leden;
    if ($withUploads && $up = handle_upload('gl_foto', 'de groepsleiding')) $c['groepsleiding']['foto'] = $up[0];

    foreach ($c['afdelingen'] as $i => $a) {
        $key = $a['key'];
        $leiding = [];
        foreach ((array) ($_POST['afd'][$key] ?? []) as $row) {
            $naam = clean_text($row['naam'] ?? '', 60);
            if ($naam === '') continue;
            $leiding[] = ['naam' => $naam, 'quote' => clean_text($row['quote'] ?? '', 200)];
        }
        $c['afdelingen'][$i]['leiding'] = $leiding;
        if ($withUploads && $up = handle_upload('afd_foto_' . $key, 'de ' . $a['naam'])) $c['afdelingen'][$i]['foto'] = $up[0];
    }

    foreach ($c['galerij'] as $i => $g) {
        $alt = clean_text($_POST['gal'][$i]['alt'] ?? $g['alt'], 150);
        $c['galerij'][$i]['alt'] = $alt;
        if ($withUploads && $up = handle_upload('gal_foto_' . $i, 'foto ' . ($i + 1) . ' van het fotoraster')) $c['galerij'][$i]['foto'] = $up[0];
    }
    return $c;
}

// ---------- Acties ----------

$error = null;
$notice = null;
$draft = null;
$auth = auth_load();
$action = $_POST['action'] ?? '';

try {
    // Te groot formulier: PHP gooit dan alles weg en $_POST is leeg.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        throw new RuntimeException('Het formulier was te groot om te verwerken (' . ini_get('post_max_size') . ' max). Upload minder foto\'s tegelijk.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

    if ($action === 'setup' && $auth === null) {
        $pw = $_POST['password'] ?? '';
        if (mb_strlen($pw) < 8) throw new RuntimeException('Kies een wachtwoord van minstens 8 tekens.');
        if ($pw !== ($_POST['password2'] ?? '')) throw new RuntimeException('De twee wachtwoorden zijn niet gelijk.');
        auth_save(['hash' => password_hash($pw, PASSWORD_DEFAULT), 'fails' => 0, 'lock_until' => 0]);
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: ./?ok=setup');
        exit;
    }

    if ($action === 'login' && $auth !== null) {
        if (($auth['lock_until'] ?? 0) > time()) {
            throw new RuntimeException('Te veel foute pogingen. Probeer het over ' . ceil(($auth['lock_until'] - time()) / 60) . ' minuten opnieuw.');
        }
        if (password_verify($_POST['password'] ?? '', $auth['hash'])) {
            $auth['fails'] = 0;
            $auth['lock_until'] = 0;
            auth_save($auth);
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: ./');
            exit;
        }
        $auth['fails'] = ($auth['fails'] ?? 0) + 1;
        if ($auth['fails'] >= MAX_FAILS) {
            $auth['fails'] = 0;
            $auth['lock_until'] = time() + LOCK_SECONDS;
        }
        auth_save($auth);
        sleep(1);
        throw new RuntimeException('Fout wachtwoord.');
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: ./');
        exit;
    }

    if (is_logged_in() && $action === 'save') {
        content_save(content_from_post(content_load()));
        header('Location: ./?ok=save');
        exit;
    }

    if (is_logged_in() && $action === 'password') {
        if (!password_verify($_POST['current'] ?? '', $auth['hash'])) throw new RuntimeException('Je huidige wachtwoord klopt niet.');
        $pw = $_POST['password'] ?? '';
        if (mb_strlen($pw) < 8) throw new RuntimeException('Kies een nieuw wachtwoord van minstens 8 tekens.');
        if ($pw !== ($_POST['password2'] ?? '')) throw new RuntimeException('De twee nieuwe wachtwoorden zijn niet gelijk.');
        $auth['hash'] = password_hash($pw, PASSWORD_DEFAULT);
        auth_save($auth);
        header('Location: ./?ok=password');
        exit;
    }
} catch (RuntimeException $ex) {
    $error = $ex->getMessage();
    // Bij een fout tijdens opslaan: getypte wijzigingen tonen zodat ze niet verloren gaan.
    if ($action === 'save' && is_logged_in()) {
        try { $draft = content_from_post(content_load(), false); } catch (RuntimeException $ignored) { $draft = null; }
        $error .= ' Er is nog niets opgeslagen: controleer en klik opnieuw op Opslaan.';
    }
}

$notices = ['setup' => 'Wachtwoord ingesteld. Welkom!', 'save' => 'Opgeslagen! De wijzigingen staan meteen op de site.', 'password' => 'Wachtwoord gewijzigd.'];
if (!$error && isset($_GET['ok'], $notices[$_GET['ok']])) $notice = $notices[$_GET['ok']];

$c = $draft ?? content_load();
$view = $auth === null ? 'setup' : (is_logged_in() ? 'edit' : 'login');

function photo_input($name, $current, $hint, $png = false) { ?>
  <div class="photo-field<?= $png ? ' photo-png' : '' ?>">
    <img src="../<?= e($current) ?>" alt="" class="photo-preview" data-preview="<?= e($name) ?>">
    <label class="file-btn">
      <input type="file" name="<?= e($name) ?>" accept="<?= $png ? 'image/png' : 'image/jpeg,image/png,image/webp' ?>" data-resize<?= $png ? ' data-png' : '' ?>>
      <span>Andere foto kiezen</span>
    </label>
    <p class="hint"><?= $hint ?></p>
  </div>
<?php }
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin · Chiro Halle</title>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="bar">
  <a href="../" class="brand"><img src="../assets/img/logo.webp" alt=""> Chiro Halle <span>admin</span></a>
  <?php if ($view === 'edit'): ?>
  <div class="bar-actions">
    <a href="../" target="_blank" rel="noopener">Bekijk de site ↗</a>
    <form method="post" action="./"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button name="action" value="logout" class="link">Afmelden</button></form>
  </div>
  <?php endif; ?>
</header>

<main class="wrap">
  <?php if ($error): ?><p class="alert alert-error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <?php if ($notice): ?><p class="alert alert-ok" role="status"><?= e($notice) ?></p><?php endif; ?>

<?php if ($view === 'setup'): ?>
  <form method="post" action="./" class="panel narrow">
    <h1>Welkom!</h1>
    <p>Kies een wachtwoord voor het adminpaneel. Deel het alleen met de leiding.</p>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Wachtwoord <input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
    <label>Nog eens <input type="password" name="password2" minlength="8" required autocomplete="new-password"></label>
    <button class="btn" name="action" value="setup">Wachtwoord instellen</button>
  </form>

<?php elseif ($view === 'login'): ?>
  <form method="post" action="./" class="panel narrow">
    <h1>Aanmelden</h1>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Wachtwoord <input type="password" name="password" required autofocus autocomplete="current-password"></label>
    <button class="btn" name="action" value="login">Aanmelden</button>
  </form>

<?php else: ?>
  <form method="post" action="./" enctype="multipart/form-data" id="editor">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <nav class="toc">
      <a href="#s-afdelingen">Afdelingen</a>
      <a href="#s-groepsleiding">Groepsleiding</a>
      <a href="#s-contact">Contact</a>
      <a href="#s-fotos">Fotoraster</a>
      <a href="#s-hero">Groepsfoto</a>
    </nav>

    <section class="panel" id="s-afdelingen">
      <h2>Afdelingen</h2>
      <p class="hint">Laat een naam leeg om die persoon te verwijderen. Een quote is optioneel.</p>
      <?php foreach ($c['afdelingen'] as $a): $k = $a['key']; ?>
      <div class="afd g-<?= e($k) ?>">
        <h3><?= e($a['naam']) ?> <small><?= e($a['leeftijd']) ?></small></h3>
        <div class="afd-grid">
          <?php photo_input('afd_foto_' . $k, $a['foto'], 'Groepsfoto van de leiding. Liggend werkt het best.'); ?>
          <div>
            <div class="rows" data-rows="afd[<?= e($k) ?>]">
              <?php foreach ($a['leiding'] as $i => $l): ?>
              <div class="row">
                <input name="afd[<?= e($k) ?>][<?= $i ?>][naam]" value="<?= e($l['naam']) ?>" placeholder="Naam" maxlength="60" aria-label="Naam">
                <input name="afd[<?= e($k) ?>][<?= $i ?>][quote]" value="<?= e($l['quote']) ?>" placeholder="Quote (optioneel)" maxlength="200" aria-label="Quote">
                <button type="button" class="remove" title="Verwijderen" aria-label="Verwijderen">×</button>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="add" data-add="afd[<?= e($k) ?>]" data-fields="naam:Naam,quote:Quote (optioneel)">+ Leiding toevoegen</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </section>

    <section class="panel" id="s-groepsleiding">
      <h2>Groepsleiding</h2>
      <div class="afd-grid">
        <?php photo_input('gl_foto', $c['groepsleiding']['foto'], 'Foto van de groepsleiding.'); ?>
        <div>
          <div class="rows" data-rows="gl">
            <?php foreach ($c['groepsleiding']['leden'] as $i => $l): ?>
            <div class="row">
              <input name="gl[<?= $i ?>][naam]" value="<?= e($l['naam']) ?>" placeholder="Naam" maxlength="60" aria-label="Naam">
              <input name="gl[<?= $i ?>][telefoon]" value="<?= e($l['telefoon']) ?>" placeholder="Gsm (optioneel)" maxlength="30" inputmode="tel" aria-label="Gsm-nummer">
              <button type="button" class="remove" title="Verwijderen" aria-label="Verwijderen">×</button>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add" data-add="gl" data-fields="naam:Naam,telefoon:Gsm (optioneel)">+ Groepsleiding toevoegen</button>
          <p class="hint">De gsm-nummers verschijnen bij de groepsleiding en bij Contact.</p>
        </div>
      </div>
    </section>

    <section class="panel" id="s-contact">
      <h2>Contact &amp; sociale media</h2>
      <label>E-mailadres <input type="email" name="email" value="<?= e($c['contact']['email']) ?>" required></label>
      <label>Instagram-link <input type="url" name="instagram" value="<?= e($c['contact']['instagram']) ?>" placeholder="https://www.instagram.com/..." required></label>
      <label>Facebook-link <input type="url" name="facebook" value="<?= e($c['contact']['facebook']) ?>" placeholder="https://www.facebook.com/... (leeg = verbergen)"></label>
    </section>

    <section class="panel" id="s-fotos">
      <h2>Fotoraster (8 foto's)</h2>
      <p class="hint">De foto's worden vierkant bijgesneden. Geef bij elke foto een korte omschrijving: die wordt voorgelezen voor blinden en slechtzienden.</p>
      <div class="gallery-grid">
        <?php foreach ($c['galerij'] as $i => $g): ?>
        <div class="gal">
          <?php photo_input('gal_foto_' . $i, $g['foto'], 'Foto ' . ($i + 1)); ?>
          <input name="gal[<?= $i ?>][alt]" value="<?= e($g['alt']) ?>" placeholder="Wat staat erop?" maxlength="150" aria-label="Omschrijving foto <?= $i + 1 ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel" id="s-hero">
      <h2>Groepsfoto bovenaan</h2>
      <div class="hero-field">
        <?php photo_input('hero_foto', $c['hero']['foto'], '<strong>Moet een uitgesneden PNG zijn</strong>: de mensen zonder achtergrond (transparant), zodat het kleurverloop erachter zichtbaar blijft. Uitsnijden kan gratis met bv. <a href="https://www.remove.bg/nl" target="_blank" rel="noopener">remove.bg</a> of op een iPhone door lang op het onderwerp in een foto te drukken. Gewone foto\'s worden geweigerd. Onderaan staan grasheuvels voor de benen.', true); ?>
      </div>
    </section>

    <div class="savebar">
      <button class="btn" name="action" value="save">Opslaan</button>
      <span class="hint">Nieuwe foto's worden automatisch verkleind voor ze geüpload worden.</span>
    </div>
  </form>

  <form method="post" action="./" class="panel narrow" id="s-wachtwoord">
    <h2>Wachtwoord wijzigen</h2>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Huidig wachtwoord <input type="password" name="current" required autocomplete="current-password"></label>
    <label>Nieuw wachtwoord <input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
    <label>Nog eens <input type="password" name="password2" minlength="8" required autocomplete="new-password"></label>
    <button class="btn btn-secondary" name="action" value="password">Wachtwoord wijzigen</button>
  </form>
<?php endif; ?>
</main>

<script src="admin.js"></script>
</body>
</html>
