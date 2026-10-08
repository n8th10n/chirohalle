<?php
// Gedeelde functies voor de site en het adminpaneel.
// De inhoud staat in data/content.json (aangemaakt door het adminpaneel).
// Bestaat dat nog niet, dan gebruiken we content.default.json uit de code.

define('SITE_ROOT', dirname(__DIR__));
define('DATA_DIR', SITE_ROOT . '/data');
define('UPLOAD_DIR', DATA_DIR . '/uploads');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('BACKUP_DIR', DATA_DIR . '/backups');
define('MAX_BACKUPS', 30);

function content_default() {
    return json_decode(file_get_contents(SITE_ROOT . '/content.default.json'), true);
}

function content_load() {
    if (is_file(CONTENT_FILE)) {
        $c = json_decode((string) @file_get_contents(CONTENT_FILE), true);
        if (is_array($c)) return $c;
    }
    return content_default();
}

function ensure_dir($dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException("Kan de map $dir niet aanmaken.");
    }
}

// Schrijft de inhoud weg; de vorige versie gaat eerst naar data/backups.
function content_save(array $content) {
    ensure_dir(DATA_DIR);
    ensure_dir(BACKUP_DIR);
    if (is_file(CONTENT_FILE)) {
        @copy(CONTENT_FILE, BACKUP_DIR . '/content-' . date('Ymd-His') . '.json');
        $backups = glob(BACKUP_DIR . '/content-*.json') ?: [];
        sort($backups);
        foreach (array_slice($backups, 0, max(0, count($backups) - MAX_BACKUPS)) as $old) @unlink($old);
    }
    $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = CONTENT_FILE . '.tmp';
    if (file_put_contents($tmp, $json) === false || !rename($tmp, CONTENT_FILE)) {
        throw new RuntimeException('Kan de inhoud niet opslaan (data-map niet schrijfbaar?).');
    }
}

function e($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// "0479 10 59 41" of "+32 479 10 59 41" -> "+32479105941"
function tel_href($nummer) {
    $n = preg_replace('/[^\d+]/', '', (string) $nummer);
    if (strpos($n, '00') === 0) $n = '+' . substr($n, 2);
    elseif (strpos($n, '0') === 0) $n = '+32' . substr($n, 1);
    return 'tel:' . $n;
}

// "https://www.instagram.com/chirohalle/" -> "@chirohalle"
function instagram_handle($url) {
    $path = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');
    return $path !== '' ? '@' . explode('/', $path)[0] : 'Instagram';
}

// ["Toon", "Jade", "Marie"] -> "Toon, Jade & Marie"
function names_join(array $names) {
    $names = array_values(array_filter(array_map('trim', $names), 'strlen'));
    if (count($names) < 2) return implode('', $names);
    $last = array_pop($names);
    return implode(', ', $names) . ' & ' . $last;
}
