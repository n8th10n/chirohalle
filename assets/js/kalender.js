// Leest de Twizzit-kalender (iCal) in via kalender.php en toont de komende activiteiten.
// Gebruik: <div data-kalender data-src="kalender.php" data-limit="3"></div>
// Zonder data-limit worden alle komende activiteiten getoond, per maand gegroepeerd.

(() => {
  const MAX_OCCURRENCES = 200;

  function unfold(text) {
    return text.replace(/\r\n/g, '\n').replace(/\n[ \t]/g, '');
  }

  function unescapeText(v) {
    return v.replace(/\\n/gi, '\n').replace(/\\,/g, ',').replace(/\\;/g, ';').replace(/\\\\/g, '\\');
  }

  // "20261011T140000Z", "20261011T140000" of "20261011"
  function parseDate(value, params) {
    const m = value.match(/^(\d{4})(\d{2})(\d{2})(?:T(\d{2})(\d{2})(\d{2})?(Z)?)?$/);
    if (!m) return null;
    const [, y, mo, d, h, mi, s, z] = m;
    const allDay = !h || /VALUE=DATE(?!-)/.test(params);
    if (allDay) return { date: new Date(+y, +mo - 1, +d), allDay: true };
    const parts = [+y, +mo - 1, +d, +h, +mi, +(s || 0)];
    // UTC-tijden omzetten; tijden met TZID of zonder zone zijn al lokale (Belgische) tijd.
    return { date: z ? new Date(Date.UTC(...parts)) : new Date(...parts), allDay: false };
  }

  function parseIcs(text) {
    const events = [];
    let cur = null;
    for (const line of unfold(text).split('\n')) {
      if (line === 'BEGIN:VEVENT') { cur = {}; continue; }
      if (line === 'END:VEVENT') { if (cur && cur.start) events.push(cur); cur = null; continue; }
      if (!cur) continue;
      const idx = line.indexOf(':');
      if (idx < 0) continue;
      const [name, ...rest] = line.slice(0, idx).split(';');
      const params = rest.join(';');
      const value = line.slice(idx + 1);
      switch (name.toUpperCase()) {
        case 'SUMMARY': cur.title = unescapeText(value); break;
        case 'LOCATION': cur.location = unescapeText(value); break;
        case 'DESCRIPTION': cur.description = unescapeText(value); break;
        case 'DTSTART': { const p = parseDate(value, params); if (p) { cur.start = p.date; cur.allDay = p.allDay; } break; }
        case 'DTEND': { const p = parseDate(value, params); if (p) cur.end = p.date; break; }
        case 'RRULE': cur.rrule = value; break;
        case 'STATUS': cur.status = value.toUpperCase(); break;
      }
    }
    return events.filter((e) => e.status !== 'CANCELLED').flatMap(expand);
  }

  // Eenvoudige herhalingen (dagelijks, wekelijks, maandelijks) uitschrijven.
  function expand(ev) {
    if (!ev.rrule) return [ev];
    const rule = Object.fromEntries(ev.rrule.split(';').map((p) => p.split('=')));
    const step = { DAILY: [0, 1], WEEKLY: [0, 7], MONTHLY: [1, 0] }[rule.FREQ];
    if (!step) return [ev];
    const interval = +(rule.INTERVAL || 1);
    const count = rule.COUNT ? +rule.COUNT : MAX_OCCURRENCES;
    const untilParsed = rule.UNTIL && parseDate(rule.UNTIL, '');
    const until = untilParsed ? untilParsed.date : null;
    const horizon = new Date(); horizon.setFullYear(horizon.getFullYear() + 1);
    const duration = ev.end ? ev.end - ev.start : 0;
    const out = [];
    for (let i = 0; i < Math.min(count, MAX_OCCURRENCES); i++) {
      const s = new Date(ev.start);
      s.setMonth(s.getMonth() + step[0] * interval * i);
      s.setDate(s.getDate() + step[1] * interval * i);
      if ((until && s > until) || s > horizon) break;
      out.push({ ...ev, start: s, end: ev.end ? new Date(s.getTime() + duration) : undefined });
    }
    return out;
  }

  const fmt = (opts) => new Intl.DateTimeFormat('nl-BE', opts);
  const fDay = fmt({ day: 'numeric' });
  const fMonthShort = fmt({ month: 'short' });
  const fWeekday = fmt({ weekday: 'long' });
  const fMonthYear = fmt({ month: 'long', year: 'numeric' });
  const fTime = fmt({ hour: '2-digit', minute: '2-digit' });
  const fDate = fmt({ day: 'numeric', month: 'long' });
  const sameDay = (a, b) => a.toDateString() === b.toDateString();

  function whenText(ev) {
    if (ev.allDay) {
      // DTEND is bij hele dagen exclusief.
      const last = ev.end ? new Date(ev.end.getTime() - 86400000) : ev.start;
      return sameDay(ev.start, last) ? 'Hele dag' : `${fDate.format(ev.start)} – ${fDate.format(last)}`;
    }
    if (!ev.end) return fTime.format(ev.start);
    if (sameDay(ev.start, ev.end)) return `${fTime.format(ev.start)} – ${fTime.format(ev.end)}`;
    return `${fTime.format(ev.start)} tot ${fDate.format(ev.end)} ${fTime.format(ev.end)}`;
  }

  function el(tag, cls, text) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }

  function eventItem(ev, isNext) {
    const li = el('li', 'event');
    const badge = el('div', 'event-date');
    badge.append(el('span', 'event-day', fDay.format(ev.start)), el('span', 'event-month', fMonthShort.format(ev.start).replace('.', '')));
    const body = el('div', 'event-body');
    if (isNext) {
      const today = sameDay(ev.start, new Date()) || (ev.start <= new Date());
      body.append(el('span', 'event-next', today ? 'Vandaag' : 'Eerstvolgende'));
    }
    body.append(el('p', 'event-weekday', `${fWeekday.format(ev.start)} · ${whenText(ev)}`));
    body.append(el('h3', 'event-title', ev.title || 'Activiteit'));
    if (ev.location) body.append(el('p', 'event-location', ev.location));
    if (ev.description) body.append(el('p', 'event-desc', ev.description));
    li.append(badge, body);
    return li;
  }

  function render(root, events) {
    const limit = root.dataset.limit ? +root.dataset.limit : null;
    const now = new Date();
    const upcoming = events
      .filter((e) => (e.end || e.start) >= now || sameDay(e.start, now))
      .sort((a, b) => a.start - b.start);
    const list = limit ? upcoming.slice(0, limit) : upcoming;
    root.replaceChildren();

    if (!list.length) {
      root.append(el('p', 'kalender-empty', 'Er staan momenteel geen activiteiten gepland. Kijk binnenkort nog eens!'));
      return;
    }
    if (limit) {
      const ul = el('ul', 'events');
      list.forEach((e) => ul.append(eventItem(e, e === upcoming[0])));
      root.append(ul);
      return;
    }
    let month = null, ul = null;
    for (const e of list) {
      const m = fMonthYear.format(e.start);
      if (m !== month) {
        month = m;
        root.append(el('h2', 'kalender-month', m));
        ul = el('ul', 'events');
        root.append(ul);
      }
      ul.append(eventItem(e, e === upcoming[0]));
    }
  }

  // Via onze eigen server (kalender.php): Twizzit laat browsers niet rechtstreeks meelezen.
  // Een antwoord telt alleen als het echt een kalender is: als PHP niet draait,
  // krijgen we de PHP-broncode terug in plaats van de kalender.
  async function loadIcs(src) {
    const res = await fetch(src);
    if (!res.ok) throw new Error(res.status);
    const text = await res.text();
    if (!/^\uFEFF?\s*BEGIN:VCALENDAR/.test(text)) throw new Error('Geen kalender');
    return text;
  }

  document.querySelectorAll('[data-kalender]').forEach(async (root) => {
    try {
      render(root, parseIcs(await loadIcs(root.dataset.src || 'kalender.php')));
    } catch (err) {
      root.replaceChildren(el('p', 'kalender-empty', 'De kalender kon niet geladen worden. Probeer later opnieuw of zet de kalender in je eigen agenda via de knoppen op de kalenderpagina.'));
    }
  });
})();
