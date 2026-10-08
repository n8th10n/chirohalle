<?php
// Haalt de Twizzit-kalender op via de server, zodat de website hem kan inlezen
// zonder CORS-problemen. Het resultaat wordt 15 minuten bewaard.

const TWIZZIT_ICAL = 'https://static.twizzit.com/v2/activity/export/ical?c=QTVKOGtkejEwT0d1OWp2cUNlUmsrUT09&o=TmxRUnFMU0JIdVRycU1CNklpY3dYZz09&f=eyJjIjoxMjM3NjY0MSwiZyI6bnVsbCwiciI6W10sImdjIjpbXSwiYXQiOlsiMSIsIjIiLCIzIiwiNCIsIjUiXSwiYXN0IjpbXX0=';
const CACHE_SECONDS = 900;

$source = getenv('KALENDER_ICS_URL') ?: TWIZZIT_ICAL;
$cacheFile = sys_get_temp_dir() . '/chirohalle-kalender.ics';

function fetchIcs(string $url): ?string {
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

$fresh = is_file($cacheFile) && (time() - filemtime($cacheFile) < CACHE_SECONDS);
$ics = $fresh ? file_get_contents($cacheFile) : null;

if ($ics === null) {
    $ics = fetchIcs($source);
    if ($ics !== null && str_contains($ics, 'BEGIN:VCALENDAR')) {
        @file_put_contents($cacheFile, $ics);
    } elseif (is_file($cacheFile)) {
        // Twizzit onbereikbaar: toon de laatst bekende versie.
        $ics = file_get_contents($cacheFile);
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
