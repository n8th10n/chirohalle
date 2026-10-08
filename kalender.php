<?php
// Haalt de Twizzit-kalender op via de server, zodat de website hem kan inlezen
// zonder CORS-problemen. Het resultaat wordt 15 minuten bewaard.

const TWIZZIT_ICAL = 'https://static.twizzit.com/v2/activity/export/ical?c=QTVKOGtkejEwT0d1OWp2cUNlUmsrUT09&o=TmxRUnFMU0JIdVRycU1CNklpY3dYZz09&f=eyJjIjoxMjM3NjY0MSwiZyI6bnVsbCwiciI6W10sImdjIjpbXSwiYXQiOlsiMSIsIjIiLCIzIiwiNCIsIjUiXSwiYXN0IjpbXX0=';
const CACHE_SECONDS = 900;

// Waarschuwingen nooit mee in de uitvoer: die zouden de kalenderdata ongeldig maken.
ini_set('display_errors', '0');

$source = getenv('KALENDER_ICS_URL') ?: TWIZZIT_ICAL;
// Cache naast dit script (map 'cache'), anders in de tijdelijke map van de server.
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755);
if (!is_dir($cacheDir) || !is_writable($cacheDir)) $cacheDir = sys_get_temp_dir();
$cacheFile = $cacheDir . '/chirohalle-kalender.ics';

function fetchIcs($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'chirohalle.be kalender',
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $status >= 200 && $status < 300) return $body;
        return null;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'chirohalle.be kalender']]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}

// Diagnose: kalender.php?test toont wat er op deze server werkt (geen geheime info).
if (isset($_GET['test'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PHP-versie: " . PHP_VERSION . "\n";
    echo "curl beschikbaar: " . (function_exists('curl_init') ? 'ja' : 'nee') . "\n";
    echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'aan' : 'uit') . "\n";
    echo "cache-map: " . $cacheDir . " (" . (is_writable($cacheDir) ? 'schrijfbaar' : 'NIET schrijfbaar') . ")\n";
    $test = fetchIcs($source);
    if ($test === null) {
        echo "Twizzit ophalen: MISLUKT\n";
    } else {
        echo "Twizzit ophalen: gelukt, " . strlen($test) . " bytes, " . substr_count($test, 'BEGIN:VEVENT') . " activiteiten\n";
        echo "Begint met: " . substr(trim($test), 0, 40) . "\n";
    }
    exit;
}

$fresh = @is_file($cacheFile) && (time() - @filemtime($cacheFile) < CACHE_SECONDS);
$ics = $fresh ? @file_get_contents($cacheFile) : null;
if ($ics === false) $ics = null;

if ($ics === null) {
    $ics = fetchIcs($source);
    if ($ics !== null && strpos($ics, 'BEGIN:VCALENDAR') !== false) {
        @file_put_contents($cacheFile, $ics);
    } elseif (@is_file($cacheFile)) {
        // Twizzit onbereikbaar: toon de laatst bekende versie.
        $ics = @file_get_contents($cacheFile);
    } else {
        http_response_code(502);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Kalender tijdelijk niet beschikbaar';
        exit;
    }
}

header('Content-Type: text/calendar; charset=utf-8');
header('Cache-Control: public, max-age=300');
echo $ics;
