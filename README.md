# Chiro Halle website

Nieuwe statische website voor [chirohalle.be](https://chirohalle.be). Geen build-stap nodig: zet de bestanden op eender welke webhost.

```
index.html
assets/css/style.css     stijl (kleuren uit het Instagram-logo en de afdelingshighlights)
assets/js/main.js        menu en geboortejaarzoeker
assets/img/              logo, leidingsfoto's en sfeerfoto's (webp)
assets/docs/             geneeskundig getuigschrift (verzekering)
```

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
