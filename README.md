# Chiro Halle website

Nieuwe statische website voor [chirohalle.be](https://chirohalle.be). Geen build-stap nodig: zet de bestanden op eender welke webhost.

```
index.php                startpagina (leest de inhoud uit data/content.json)
content.default.json     standaardinhoud, gebruikt zolang er nog niets via /admin is opgeslagen
includes/site.php        gedeelde PHP-functies
admin/                   adminpaneel (chirohalle.be/admin)
kalender.html            volledige kalender
kalender.php             haalt de Twizzit-kalender op (iCal) en bewaart hem 12 uur
kalender-backup.ics      reservekopie, getoond zolang Twizzit nog niets teruggaf
privacy.html             privacyverklaring (ook als PDF in assets/docs/)
assets/css/style.css     stijl (kleuren uit het Instagram-logo en de afdelingshighlights)
assets/js/main.js        menu en geboortejaarzoeker
assets/js/kalender.js    leest de kalender in en toont de komende activiteiten
assets/img/              logo, leidingsfoto's en sfeerfoto's (webp)
assets/docs/             geneeskundig getuigschrift (verzekering)
```

De kalender heeft PHP nodig op de webhost. Lokaal, zonder PHP, toont de site "De kalender kon niet geladen worden".

Twizzit laat de kalender maar om de 12 uur ophalen (wie vaker vraagt, wordt tijdelijk geblokkeerd). `kalender.php` houdt daar rekening mee. Wijzigingen in Twizzit verschijnen dus binnen 12 uur op de site. Wil je weten wat de server ziet, open dan `kalender.php?test` (niet herhaaldelijk: elke test telt als een poging bij Twizzit).

## Adminpaneel

Op `chirohalle.be/admin` kan de leiding aanpassen: de foto en leiding (met quotes) per afdeling, de groepsleiding met gsm-nummers, e-mail, Instagram en Facebook, de 8 foto's van het fotoraster en de groepsfoto bovenaan (moet een uitgesneden PNG met transparante achtergrond zijn).

- **Eerste keer:** surf naar `/admin` en kies meteen een wachtwoord. Wie als eerste die pagina opent, kiest het wachtwoord, dus doe dit direct na de upload.
- **Opslag:** alles komt in de map `data/` op de server (`content.json`, `uploads/`, `backups/` en het gehashte wachtwoord in `admin.php`). Die map staat niet in git.
- **Code updaten via FTP:** upload nooit een lege of oude `data/`-map, anders gaan de aanpassingen verloren. De map `data/` staat niet in de repo, dus een gewone upload van de code laat ze ongemoeid.
- **Wachtwoord vergeten:** verwijder `data/admin.php` via FTP en kies op `/admin` een nieuw.
- **Terugzetten:** bij elke opslag gaat de vorige versie naar `data/backups/`. Kopieer een back-up over `data/content.json` om terug te gaan.
- **Map `data/` moet schrijfbaar zijn** voor PHP. Lukt opslaan niet, zet de rechten van `data/` dan op 755 (of 775).

Inschrijven en betalen lopen via het [Twizzit-formulier](https://app.twizzit.com/v2/form/VlhWQUFUMXZKdU9ibWZyT3hXUkVYUT09).

## Jaarlijks bijwerken

- Leiding per afdeling en groepsleiding (namen, foto's in `assets/img/leiding-*.webp`, gsm-nummers)
- Lidgeld en vieruurtje
- Kampdata (nu: 21–31 juli)
- Kledingprijzen (T-shirt nu €15)

## Een citaat van een leid(st)er toevoegen

Zet in `index.html`, in de kaart van de juiste afdeling, net onder de lijst `<ul class="leiders">`:

```html
<blockquote class="quote"><p>Hier komt het citaat.</p><cite>Naam</cite></blockquote>
```

## Nog na te kijken

- **Verhuur**: de opsomming (2 slaap- en 2 daglokalen, keuken/refter, sanitair, max. 30 personen, groot terrein) komt van een externe verhuurlijst (Kampas). Laat vzw Lok'Halle ze even bevestigen.
