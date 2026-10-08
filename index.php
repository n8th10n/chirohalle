<?php
require __DIR__ . '/includes/site.php';
$c = content_load();
$gl = $c['groepsleiding'];
$glNamen = names_join(array_column($gl['leden'], 'naam'));
$glTel = array_values(array_filter($gl['leden'], function ($l) { return trim($l['telefoon'] ?? '') !== ''; }));
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chiro Halle</title>
  <meta name="description" content="Chiro Halle: elke zondag van 14 tot 18u spelen in de chirolokalen op Stroppen in Halle. Voor iedereen van 6 tot 18 jaar.">
  <meta property="og:title" content="Chiro Halle">
  <meta property="og:description" content="Elke zondag van 14 tot 18u spelen op Stroppen in Halle. Voor iedereen van 6 tot 18 jaar.">
  <meta property="og:image" content="assets/img/logo.webp">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
  <link rel="apple-touch-icon" href="assets/img/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="wrap header-inner">
    <a href="#top" class="logo" aria-label="Chiro Halle, naar boven">
      <img src="assets/img/logo.webp" alt="" width="44" height="44">
      <span>Chiro Halle</span>
    </a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="nav" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
    <nav id="nav" class="nav">
      <a href="#afdelingen">Afdelingen</a>
      <a href="#activiteiten">Activiteiten</a>
      <a href="kalender.html">Kalender</a>
      <a href="#praktisch">Praktisch</a>
      <a href="#kleding">Kleding</a>
      <a href="#verhuur">Verhuur</a>
      <a href="#contact">Contact</a>
      <a href="https://app.twizzit.com/v2/form/VlhWQUFUMXZKdU9ibWZyT3hXUkVYUT09" class="btn btn-small" target="_blank" rel="noopener">Inschrijven</a>
    </nav>
  </div>
</header>

<main id="top">

  <!-- HERO -->
  <section class="hero">
    <div class="clouds" aria-hidden="true">
      <svg width="0" height="0" style="position:absolute">
        <symbol id="cloud" viewBox="0 0 120 64"><circle cx="34" cy="40" r="20"/><circle cx="60" cy="30" r="27"/><circle cx="88" cy="40" r="20"/><rect x="34" y="40" width="54" height="20"/></symbol>
      </svg>
      <svg class="cloud cloud-1"><use href="#cloud"/></svg>
      <svg class="cloud cloud-2"><use href="#cloud"/></svg>
      <svg class="cloud cloud-3"><use href="#cloud"/></svg>
      <svg class="cloud cloud-4"><use href="#cloud"/></svg>
    </div>
    <div class="wrap hero-inner">
      <div class="hero-text">
        <p class="tag">Elke zondag · 14u – 18u · Stroppen</p>
        <h1>
          <span class="burst burst-l" aria-hidden="true"><i></i><i></i><i></i></span>
          Spelen, ravotten &amp; vrienden voor het leven!
          <span class="burst burst-r" aria-hidden="true"><i></i><i></i><i></i></span>
        </h1>
        <p class="lead">Bij Chiro Halle is iedereen van 6 tot 18 jaar welkom. Groot, klein, ros, blond: bij ons valt niemand uit de boot.</p>
        <div class="hero-cta">
          <a href="https://app.twizzit.com/v2/form/VlhWQUFUMXZKdU9ibWZyT3hXUkVYUT09" class="btn btn-white" target="_blank" rel="noopener">Schrijf je in</a>
          <a href="#afdelingen" class="btn btn-outline">Ontdek de afdelingen</a>
        </div>
      </div>
    </div>
    <div class="hero-photo">
      <img src="<?= e($c['hero']['foto']) ?>" width="<?= (int) $c['hero']['breedte'] ?>" height="<?= (int) $c['hero']['hoogte'] ?>"
           style="aspect-ratio: <?= (int) $c['hero']['breedte'] ?> / <?= round($c['hero']['hoogte'] * 0.72) ?>"
           alt="De leiding van Chiro Halle in de kleuren van hun afdeling">
    </div>
  </section>

  <!-- WIE ZIJN WE -->
  <section id="wie" class="section">
    <div class="wrap grid-2">
      <div>
        <p class="tag tag-dark">Wie zijn we</p>
        <h2>Iedereen kent iedereen, en iedereen is van tel</h2>
      </div>
      <div class="prose">
        <p>Chiro Halle is een jeugdbeweging voor jongens en meisjes van 6 tot 18 jaar. De leden zitten in afdelingen volgens leeftijd, en elke afdeling wordt begeleid door enthousiaste leiding van minstens 18 jaar. Wie 18 wordt, kan zelf leiding worden.</p>
        <p>Onze leiding doet dit helemaal vrijwillig en volgt zoveel mogelijk cursussen van Chirojeugd Vlaanderen. We willen dat elk kind net zulke toffe zondagen beleeft als wij vroeger: aan motivatie geen gebrek!</p>
      </div>
    </div>
    <div class="wrap facts">
      <div class="fact g-aspi"><strong>6–18</strong><span>jaar</span></div>
      <div class="fact g-speelclub"><strong>14u–18u</strong><span>elke zondag</span></div>
      <div class="fact g-rakwi"><strong>Stroppen</strong><span>Guido Gezellestraat</span></div>
      <div class="fact g-keti"><strong>21–31 juli</strong><span>op kamp</span></div>
    </div>
  </section>

  <!-- AFDELINGEN -->
  <section id="afdelingen" class="section section-tint">
    <div class="wrap">
      <p class="tag tag-dark">Afdelingen</p>
      <h2>Voor elke leeftijd een eigen bende</h2>
      <p class="section-lead">Elke afdeling heeft haar eigen kleur. Kies het geboortejaar van je kind en ontdek in welke afdeling je kind terechtkomt.</p>

      <div class="age-finder">
        <label for="geboortejaar">Geboortejaar</label>
        <select id="geboortejaar"></select>
        <p id="age-result" class="age-result" aria-live="polite"></p>
      </div>

      <div class="cards" id="afdeling-cards">
<?php foreach ($c['afdelingen'] as $a):
    $leiding = array_values(array_filter($a['leiding'], function ($l) { return trim($l['naam']) !== ''; })); ?>
        <article class="card g-<?= e($a['key']) ?>" data-min="<?= (int) $a['min'] ?>" data-max="<?= (int) $a['max'] ?>">
          <img src="<?= e($a['foto']) ?>" alt="De leiding van de <?= e($a['naam']) ?>" loading="lazy">
          <div class="card-body">
            <h3><?= e($a['naam']) ?></h3>
            <p class="age"><?= e($a['leeftijd']) ?></p>
            <p><?= e($a['beschrijving']) ?></p>
<?php if ($leiding): ?>
            <div class="leiding">
              <span class="label">Leiding</span>
              <ul class="leiders"><?php foreach ($leiding as $l): ?><li><?= e($l['naam']) ?></li><?php endforeach; ?></ul>
<?php foreach ($leiding as $l): if (trim($l['quote'] ?? '') === '') continue; ?>
              <blockquote class="quote"><p><?= e($l['quote']) ?></p><cite><?= e($l['naam']) ?></cite></blockquote>
<?php endforeach; ?>
            </div>
<?php endif; ?>
          </div>
        </article>
<?php endforeach; ?>
      </div>

      <div class="groepsleiding">
        <img src="<?= e($gl['foto']) ?>" alt="De groepsleiding van Chiro Halle: <?= e($glNamen) ?>" loading="lazy">
        <div>
          <p class="tag">Groepsleiding</p>
          <h3><?= e($glNamen) ?></h3>
          <p>Zij houden de hele Chiro draaiende en zijn je aanspreekpunt voor alle vragen.</p>
<?php if ($glTel): ?>
          <ul class="phones">
<?php foreach ($glTel as $l): ?>
            <li><span><?= e($l['naam']) ?></span><a href="<?= e(tel_href($l['telefoon'])) ?>"><?= e($l['telefoon']) ?></a></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ACTIVITEITEN -->
  <section id="activiteiten" class="section">
    <div class="wrap">
      <p class="tag tag-dark">Activiteiten</p>
      <h2>Fantastische zondagen, beestige activiteiten &amp; een onvergetelijk kamp</h2>
      <div class="highlights">
        <div class="highlight g-tito">
          <h3>Zondagen</h3>
          <p>Elke zondag van 14u tot 18u spelen we in onze lokalen op Stroppen, tenzij we op uitstap zijn.</p>
        </div>
        <div class="highlight g-speelclub">
          <h3>Uitstappen</h3>
          <p>Een groepsactiviteit, een dag naar zee, een pastafestijn… er valt elke week iets te beleven.</p>
        </div>
        <div class="highlight g-rakwi">
          <h3>Sjaloomweekend</h3>
          <p>Om de twee jaar trekken we met z'n allen een weekend op verplaatsing: een mini-kamp om te proeven.</p>
        </div>
        <div class="highlight g-keti">
          <h3>Kamp</h3>
          <p>Elk jaar van 21 tot 31 juli sluiten we het Chirojaar af met een onvergetelijk kamp.</p>
        </div>
      </div>
    </div>

    <div class="wrap binnenkort">
      <div class="binnenkort-head">
        <h3>Binnenkort op de Chiro</h3>
        <a href="kalender.html" class="btn btn-small">Volledige kalender →</a>
      </div>
      <div data-kalender data-src="kalender.php" data-limit="3">
        <p class="kalender-empty">Kalender laden…</p>
      </div>
    </div>

    <div class="gallery" aria-label="Foto's van Chiro Halle">
<?php foreach ($c['galerij'] as $g): ?>
      <img src="<?= e($g['foto']) ?>" alt="<?= e($g['alt']) ?>" loading="lazy">
<?php endforeach; ?>
    </div>
    <div class="wrap center">
      <a href="<?= e($c['contact']['instagram']) ?>" class="btn btn-dark" target="_blank" rel="noopener">Meer foto's op <?= e(instagram_handle($c['contact']['instagram'])) ?></a>
    </div>
  </section>

  <!-- INCLUSIE -->
  <section id="inclusie" class="section section-brand">
    <div class="wrap grid-2">
      <div>
        <p class="tag">Inclusie</p>
        <h2>De Chiro is een thuis voor iedereen</h2>
      </div>
      <div class="prose">
        <p>Chiro Halle stapt mee in het inclusieproject van SVZ (inclusieve jeugdbewegingen). Kinderen met een beperking zijn één keer per maand welkom op onze zondagsactiviteit.</p>
        <p>Zou je kind met een beperking graag in een jeugdbeweging zitten? Dan is je kind bij ons van harte welkom! Neem contact op met de groepsleiding, dan bekijken we samen hoe we dit aanpakken.</p>
        <a href="#contact" class="btn btn-white">Contacteer ons</a>
      </div>
    </div>
  </section>

  <!-- PRAKTISCH -->
  <section id="praktisch" class="section">
    <div class="wrap">
      <p class="tag tag-dark">Praktisch</p>
      <h2>Alles wat je moet weten</h2>
      <div class="info-grid">
        <div class="info">
          <h3>Lidgeld</h3>
          <p class="price"><strong>€40</strong> per Chirojaar</p>
          <p>Heb je een <a href="https://www.halle.be/kompas" target="_blank" rel="noopener">Kompas-pas</a> van de stad Halle? Dan betaal je maar <strong>€10</strong>. Het lidgeld dekt onder meer je verzekering bij elke Chiroactiviteit. Je schrijft in en betaalt via <a href="https://app.twizzit.com/v2/form/VlhWQUFUMXZKdU9ibWZyT3hXUkVYUT09" target="_blank" rel="noopener">Twizzit</a>.</p>
        </div>
        <div class="info">
          <h3>Vieruurtje</h3>
          <p class="price"><strong>€1</strong> per zondag</p>
          <p>Elke zondag is er een drankje en een koekje. Daarvoor koop je <strong>4-uurkaarten</strong> bij de leiding.</p>
        </div>
        <div class="info">
          <h3>Waar vind je ons?</h3>
          <p>Onze lokalen liggen op het <strong>Jeugdcentrum Stroppen</strong>, Guido Gezellestraat, 1500 Halle. Rij de Guido Gezellestraat af richting jeugdcentrum en parkeer op de grote parking. Steek het voetbalveld over: onze lokalen zijn de linkerkant van het gebouw voor je.</p>
          <p><a href="https://www.google.com/maps/search/?api=1&query=Jeugdcentrum+Stroppen+Guido+Gezellestraat+1500+Halle" target="_blank" rel="noopener">Bekijk op Google Maps →</a></p>
        </div>
        <div class="info">
          <h3>Verzekering</h3>
          <p>Zodra je kind ingeschreven is, is je kind ook verzekerd. Gebeurt er een ongeval tijdens een Chiroactiviteit of op kamp, dan geeft de leiding je de verzekeringspapieren mee. Moet je pas later naar de dokter, contacteer dan de leiding.</p>
          <p>Laat de dokter het formulier invullen en bezorg een kopie of scan aan de leiding. Wij regelen de rest.</p>
          <p><a href="assets/docs/geneeskundig_getuigschrift.pdf" download>Download het geneeskundig getuigschrift →</a></p>
        </div>
      </div>
    </div>
  </section>

  <!-- KLEDING -->
  <section id="kleding" class="section section-tint">
    <div class="wrap">
      <p class="tag tag-dark">Kleding</p>
      <h2>Wat trek je aan naar de Chiro?</h2>
      <p class="section-lead">Op zondag draag je gewoon kleren die vuil mogen worden. Wil je een Chiro-uniform, dan lees je hier waar je alles vindt.</p>
      <div class="kleding-grid">
        <div class="kleding g-tito">
          <p class="kleding-price">€15</p>
          <h3>Chiro Halle T-shirt</h3>
          <p>Ons eigen groeps-T-shirt koop je op de Chiro, bij de leiding.</p>
        </div>
        <div class="kleding g-keti">
          <p class="kleding-price">De Banier</p>
          <h3>Short, rok &amp; hemd</h3>
          <p>De rest van het uniform koop je bij <a href="https://www.banier.be/" target="_blank" rel="noopener">De Banier</a>, de winkel van Chirojeugd Vlaanderen.</p>
        </div>
        <div class="kleding g-rakwi">
          <p class="kleding-price">Vrije bijdrage</p>
          <h3>Tweedehands</h3>
          <p>Op de Chiro is er tweedehandskleding. Te klein geworden? Breng het binnen. Iets nodig? Neem het mee voor een vrije bijdrage.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- INSCHRIJVEN -->
  <section id="inschrijven" class="section">
    <div class="wrap cta-box">
      <div>
        <h2>Zin om mee te doen?</h2>
        <p>Je hoeft je niet meteen in te schrijven: kom eerst 3 zondagen vrijblijvend proberen, je bent dan al verzekerd via Chiro nationaal. Wil je blijven (en dat denken we wel), schrijf je dan in via Twizzit.</p>
      </div>
      <a href="https://app.twizzit.com/v2/form/VlhWQUFUMXZKdU9ibWZyT3hXUkVYUT09" class="btn btn-white" target="_blank" rel="noopener">Inschrijven via Twizzit</a>
    </div>
  </section>

  <!-- VERHUUR -->
  <section id="verhuur" class="section">
    <div class="wrap grid-2">
      <div>
        <p class="tag tag-dark">Verhuur</p>
        <h2>Onze lokalen huren?</h2>
        <p>Buiten de Chiro-uren verhuurt <strong>vzw Lok'Halle</strong> onze lokalen aan jeugdgroepen voor kampen en weekends. De vzw ondersteunt Chiro Halle en zorgt voor het onderhoud van de lokalen en het terrein.</p>
        <div class="verhuur-cta">
          <a href="https://www.mychiro.be/verhuur/verhuur.php" class="btn" target="_blank" rel="noopener">Bekijk beschikbaarheid</a>
          <a href="https://www.instagram.com/vzw.lokhalle/" class="btn btn-dark" target="_blank" rel="noopener">@vzw.lokhalle</a>
        </div>
      </div>
      <ul class="checklist">
        <li>2 slaaplokalen en 2 daglokalen</li>
        <li>Keuken en refter</li>
        <li>Toegang tot het sanitair blok</li>
        <li>Plaats voor maximaal 30 personen</li>
        <li>Een groot terrein, plus 3 voetbalvelden en een speeltuin op het jeugdcentrum</li>
        <li>Op wandelafstand van het centrum van Halle</li>
      </ul>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" class="section section-tint">
    <div class="wrap grid-2">
      <div>
        <p class="tag tag-dark">Contact</p>
        <h2>Vragen? Laat iets weten!</h2>
        <p>Voor algemene vragen mail je naar ons groepsadres of bel je de groepsleiding. Je kan de leiding ook gewoon aanspreken op zondag.</p>
      </div>
      <ul class="contact-list">
        <li><span>Mail</span><a href="mailto:<?= e($c['contact']['email']) ?>"><?= e($c['contact']['email']) ?></a></li>
<?php foreach ($glTel as $l): ?>
        <li><span><?= e($l['naam']) ?></span><a href="<?= e(tel_href($l['telefoon'])) ?>"><?= e($l['telefoon']) ?></a></li>
<?php endforeach; ?>
        <li><span>Instagram</span><a href="<?= e($c['contact']['instagram']) ?>" target="_blank" rel="noopener"><?= e(instagram_handle($c['contact']['instagram'])) ?></a></li>
<?php if (trim($c['contact']['facebook']) !== ''): ?>
        <li><span>Facebook</span><a href="<?= e($c['contact']['facebook']) ?>" target="_blank" rel="noopener">Chiro Halle</a></li>
<?php endif; ?>
        <li><span>Lokalen</span>Jeugdcentrum Stroppen, Guido Gezellestraat, 1500 Halle</li>
      </ul>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="wrap footer-inner">
    <p>© <span id="year"></span> Chiro Halle · Lid van <a href="https://chiro.be" target="_blank" rel="noopener">Chirojeugd Vlaanderen</a> · <a href="privacy.html">Privacyverklaring</a> · Website door Nathan Mertens</p>
    <a href="#top">Naar boven ↑</a>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script src="assets/js/kalender.js"></script>
</body>
</html>
