<?php
// Haalt de Twizzit-kalender op via de server, zodat de website hem kan inlezen
// zonder CORS-problemen.
//
// Twizzit (achter Cloudflare) laat deze export maar om de 12 uur ophalen en
// antwoordt anders met "Retry-After". Daarom:
//  - bewaren we de kalender 12 uur;
//  - proberen we niet opnieuw zolang Twizzit ons laat wachten;
//  - tonen we altijd de laatst bekende versie (of kalender-backup.ics) als
//    ophalen niet lukt.

const TWIZZIT_ICAL = 'https://static.twizzit.com/v2/activity/export/ical?c=QTVKOGtkejEwT0d1OWp2cUNlUmsrUT09&o=TmxRUnFMU0JIdVRycU1CNklpY3dYZz09&f=eyJjIjoxMjM3NjY0MSwiZyI6bnVsbCwiciI6W10sImdjIjpbXSwiYXQiOlsiMSIsIjIiLCIzIiwiNCIsIjUiXSwiYXN0IjpbXX0=';
const CACHE_SECONDS = 43200;        // 12 uur, zoals Twizzit vraagt (X-PUBLISHED-TTL)
const DEFAULT_RETRY_SECONDS = 3600; // wachttijd na een mislukte poging zonder Retry-After
const USER_AGENT = 'Mozilla/5.0 (compatible; ChiroHalleKalender/1.0; +https://chirohalle.be)';

// Waarschuwingen nooit mee in de uitvoer: die zouden de kalenderdata ongeldig maken.
ini_set('display_errors', '0');

$source = getenv('KALENDER_ICS_URL') ?: TWIZZIT_ICAL;

// Cache naast dit script (map 'cache'), anders in de tijdelijke map van de server.
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755);
if (!is_dir($cacheDir) || !is_writable($cacheDir)) $cacheDir = sys_get_temp_dir();
$cacheFile = $cacheDir . '/chirohalle-kalender.ics';
$blockFile = $cacheDir . '/chirohalle-kalender.wacht';
$backupFile = __DIR__ . '/kalender-backup.ics';

function isCalendar($text) {
    return is_string($text) && preg_match('/^(\xEF\xBB\xBF)?\s*BEGIN:VCALENDAR/', $text) === 1;
}

// Geeft ['body' => ..., 'status' => ..., 'retryAfter' => seconden of null].
function fetchIcs($url) {
    $retryAfter = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => USER_AGENT,
            CURLOPT_HTTPHEADER => ['Accept: text/calendar, */*'],
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$retryAfter) {
                if (stripos($line, 'Retry-After:') === 0) $retryAfter = (int) trim(substr($line, 12));
                return strlen($line);
            },
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body === false ? null : $body, 'status' => $status, 'retryAfter' => $retryAfter];
    }
    $ctx = stream_context_create(['http' => [
        'timeout' => 10, 'user_agent' => USER_AGENT, 'ignore_errors' => true,
        'header' => "Accept: text/calendar, */*\r\n",
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    foreach (isset($http_response_header) ? $http_response_header : [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) $status = (int) $m[1];
        if (stripos($h, 'Retry-After:') === 0) $retryAfter = (int) trim(substr($h, 12));
    }
    return ['body' => $body === false ? null : $body, 'status' => $status, 'retryAfter' => $retryAfter];
}

function readFileOrNull($path) {
    if (!@is_file($path)) return null;
    $text = @file_get_contents($path);
    return isCalendar($text) ? $text : null;
}

// Diagnose: kalender.php?test toont wat er op deze server gebeurt (geen geheime info).
// Haalt niets opnieuw op als Twizzit ons nog laat wachten.
if (isset($_GET['test'])) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo "PHP-versie: " . PHP_VERSION . "\n";
    echo "curl beschikbaar: " . (function_exists('curl_init') ? 'ja' : 'nee') . "\n";
    echo "cache-map: " . $cacheDir . " (" . (is_writable($cacheDir) ? 'schrijfbaar' : 'NIET schrijfbaar') . ")\n";
    $cached = readFileOrNull($cacheFile);
    echo "cache: " . ($cached ? 'aanwezig, ' . round((time() - filemtime($cacheFile)) / 60) . ' minuten oud' : 'leeg') . "\n";
    echo "reservekopie: " . (readFileOrNull($backupFile) ? 'aanwezig' : 'ontbreekt') . "\n";
    $until = (int) @file_get_contents($blockFile);
    if ($until > time()) {
        echo "Twizzit: we wachten nog " . round(($until - time()) / 60) . " minuten voor een nieuwe poging\n";
        exit;
    }
    $r = fetchIcs($source);
    echo "Twizzit: HTTP " . $r['status'] . ($r['retryAfter'] ? ", Retry-After " . $r['retryAfter'] . "s" : '') . "\n";
    if (isCalendar($r['body'])) {
        @file_put_contents($cacheFile, $r['body']);
        echo "Twizzit: gelukt, " . substr_count($r['body'], 'BEGIN:VEVENT') . " activiteiten (cache bijgewerkt)\n";
    } else {
        @file_put_contents($blockFile, time() + ($r['retryAfter'] ?: DEFAULT_RETRY_SECONDS));
        echo "Twizzit: geen kalender ontvangen. Begin van het antwoord: " . substr(preg_replace('/\s+/', ' ', (string) $r['body']), 0, 120) . "\n";
    }
    exit;
}

$cached = readFileOrNull($cacheFile);
$fresh = $cached !== null && (time() - filemtime($cacheFile) < CACHE_SECONDS);
$ics = $fresh ? $cached : null;

if ($ics === null) {
    $waitUntil = (int) @file_get_contents($blockFile);
    if ($waitUntil <= time()) {
        $r = fetchIcs($source);
        if (isCalendar($r['body'])) {
            $ics = $r['body'];
            @file_put_contents($cacheFile, $ics);
            @unlink($blockFile);
        } else {
            // Niet opnieuw proberen voor Twizzit het toelaat.
            @file_put_contents($blockFile, time() + ($r['retryAfter'] ?: DEFAULT_RETRY_SECONDS));
        }
    }
    // Ophalen lukte niet (of we moeten nog wachten): laatst bekende versie, anders de reservekopie.
    if ($ics === null) $ics = $cached !== null ? $cached : readFileOrNull($backupFile);
}

if ($ics === null) {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Kalender tijdelijk niet beschikbaar';
    exit;
}

header('Content-Type: text/calendar; charset=utf-8');
header('Cache-Control: public, max-age=600');
echo $ics;
