// Adminpaneel: rijen toevoegen/verwijderen, foto's verkleinen vóór upload,
// en waarschuwen bij niet-opgeslagen wijzigingen.
(() => {
  const form = document.getElementById('editor');
  if (!form) return;

  let dirty = false;
  form.addEventListener('input', () => { dirty = true; });
  form.addEventListener('submit', () => { dirty = false; });
  window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  // ---- Rijen ----
  let counter = 1000;
  form.addEventListener('click', (e) => {
    const remove = e.target.closest('.remove');
    if (remove) {
      remove.closest('.row').remove();
      dirty = true;
      return;
    }
    const add = e.target.closest('.add');
    if (add) {
      const prefix = add.dataset.add;
      const rows = form.querySelector(`.rows[data-rows="${CSS.escape(prefix)}"]`);
      const row = document.createElement('div');
      row.className = 'row';
      const i = counter++;
      for (const spec of add.dataset.fields.split(',')) {
        const [field, placeholder] = spec.split(':');
        const input = document.createElement('input');
        input.name = `${prefix}[${i}][${field}]`;
        input.placeholder = placeholder;
        input.setAttribute('aria-label', placeholder);
        input.maxLength = field === 'quote' ? 200 : field === 'telefoon' ? 30 : 60;
        if (field === 'telefoon') input.inputMode = 'tel';
        row.append(input);
      }
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'remove';
      btn.title = btn.ariaLabel = 'Verwijderen';
      btn.textContent = '×';
      row.append(btn);
      rows.append(row);
      row.querySelector('input').focus();
      dirty = true;
    }
  });

  // ---- Foto's ----
  const MAX_SIDE = 1600;
  const MAX_SIDE_PNG = 2200;

  function loadImage(file) {
    return new Promise((resolve, reject) => {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = () => reject(new Error('Kan de foto niet lezen.'));
      img.src = url;
    });
  }

  function hasTransparency(ctx, w, h) {
    const data = ctx.getImageData(0, 0, w, h).data;
    for (let i = 3; i < data.length; i += 4 * 7) if (data[i] < 250) return true;
    return false;
  }

  async function prepare(input) {
    const file = input.files[0];
    const field = input.closest('.photo-field');
    const preview = field.querySelector('.photo-preview');
    const hint = field.querySelector('.file-btn span');
    if (!file) return;
    const isPng = input.hasAttribute('data-png');
    hint.textContent = 'Bezig…';
    try {
      const img = await loadImage(file);
      const max = isPng ? MAX_SIDE_PNG : MAX_SIDE;
      const scale = Math.min(1, max / Math.max(img.naturalWidth, img.naturalHeight));
      const w = Math.round(img.naturalWidth * scale);
      const h = Math.round(img.naturalHeight * scale);
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, w, h);

      if (isPng && (file.type !== 'image/png' || !hasTransparency(ctx, w, h))) {
        input.value = '';
        hint.textContent = 'Andere foto kiezen';
        alert('Deze foto heeft geen transparante achtergrond. De groepsfoto moet een uitgesneden PNG zijn.');
        return;
      }

      const type = isPng ? 'image/png' : 'image/jpeg';
      const blob = await new Promise((r) => canvas.toBlob(r, type, 0.85));
      if (blob && blob.size < file.size) {
        const name = file.name.replace(/\.[^.]+$/, '') + (isPng ? '.png' : '.jpg');
        const dt = new DataTransfer();
        dt.items.add(new File([blob], name, { type }));
        input.files = dt.files;
      }
      preview.src = URL.createObjectURL(input.files[0]);
      field.classList.add('changed');
      hint.textContent = 'Nieuwe foto gekozen ✓';
    } catch (err) {
      // Verkleinen lukt niet in deze browser: het origineel wordt geüpload.
      preview.src = URL.createObjectURL(file);
      field.classList.add('changed');
      hint.textContent = 'Nieuwe foto gekozen ✓';
    }
    dirty = true;
  }

  form.querySelectorAll('input[type=file][data-resize]').forEach((input) => {
    input.addEventListener('change', () => prepare(input));
  });
})();
