import { Offcanvas } from 'bootstrap';

/**
 * Halaman "Racik bundle".
 * Isi bundle dipegang di sini, lalu setiap perubahan dikirim ke server
 * (PUT /bundle/racik). Server yang menghitung ulang total, porsi, dan sisa
 * budget; tampilannya diperbarui dari jawaban server.
 */
export function initBundleBuilder(root, rupiah) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const url = root.dataset.url;

    const els = {
        lines: root.querySelector('[data-lines]'),
        empty: root.querySelector('[data-empty]'),
        receiptRows: root.querySelector('[data-receipt-rows]'),
        totals: document.querySelectorAll('[data-total]'),
        counts: document.querySelectorAll('[data-count]'),
        budgetMeter: root.querySelector('[data-budget-meter]'),
        budgetNote: root.querySelector('[data-budget-note]'),
        mealsValue: root.querySelector('[data-meals-value]'),
        mealsMeter: root.querySelector('[data-meals-meter]'),
        mealsNote: root.querySelector('[data-meals-note]'),
        submits: document.querySelectorAll('[data-submit-bundle]'),
        saving: root.querySelector('[data-saving]'),
        picker: document.getElementById('picker'),
        pickerTitle: document.querySelector('[data-picker-title]'),
        pickerSearch: document.querySelector('[data-picker-search]'),
        pickerEmpty: document.querySelector('[data-picker-empty]'),
    };

    const pickerItems = [...els.picker.querySelectorAll('.picker-item')];
    const catalog = new Map(pickerItems.map((item) => [Number(item.dataset.id), {
        id: Number(item.dataset.id),
        name: item.dataset.name,
        price: Number(item.dataset.price),
        stock: Number(item.dataset.stock),
        weight: item.dataset.weight,
        category: item.dataset.category,
        el: item,
    }]));

    const offcanvas = Offcanvas.getOrCreateInstance(els.picker);

    let summary = JSON.parse(root.dataset.summary);
    let items = new Map(summary.lines.map((line) => [line.id, line.qty])); // id => qty
    let swapTarget = null;   // id produk yang sedang diganti, null = mode tambah
    let activeCategory = '';
    let pending = Promise.resolve();
    let rendered = new Set();

    /* ---------- Tampilan ---------- */
    const localTotal = () => [...items].reduce((sum, [id, qty]) => sum + catalog.get(id).price * qty, 0);

    function renderLines() {
        els.lines.replaceChildren(...[...items].map(([id, qty]) => {
            const p = catalog.get(id);
            const row = document.createElement('div');
            row.className = 'builder-line';
            row.dataset.id = id;
            if (rendered.has(id)) row.style.animation = 'none';

            const thumb = p.el.querySelector('.pack').cloneNode(true);
            thumb.classList.remove('pack--mini');
            thumb.classList.add('pack--thumb');

            row.innerHTML = `
                <div>
                    <span class="line-item__name"></span>
                    <div class="line-item__meta">${p.weight} · ${rupiah(p.price)} / kemasan</div>
                </div>
                <div class="builder-line__actions">
                    <button type="button" class="btn-fm btn-fm--ghost btn-fm--sm" data-swap><i class="ph-bold ph-swap"></i> Ganti</button>
                    <div class="qty qty--sm">
                        <button type="button" data-step="-1" aria-label="Kurangi"><i class="ph-bold ph-minus"></i></button>
                        <input type="number" inputmode="numeric" min="1" max="${Math.min(p.stock, 20)}" value="${qty}" aria-label="Jumlah">
                        <button type="button" data-step="1" aria-label="Tambah"><i class="ph-bold ph-plus"></i></button>
                    </div>
                    <span class="builder-line__sub">${rupiah(p.price * qty)}</span>
                    <button type="button" class="icon-btn icon-btn--danger" data-remove aria-label="Hapus dari bundle"><i class="ph ph-trash"></i></button>
                </div>`;
            row.querySelector('.line-item__name').textContent = p.name;
            row.prepend(thumb);

            return row;
        }));

        rendered = new Set(items.keys());
        els.empty.hidden = items.size > 0;
        els.lines.hidden = items.size === 0;
    }

    function renderReceipt() {
        els.receiptRows.replaceChildren(...[...items].map(([id, qty]) => {
            const p = catalog.get(id);
            const row = document.createElement('div');
            row.className = 'receipt__row';
            row.innerHTML = `<span class="q">${qty}×</span><span class="n"></span><span class="dots"></span><span>${rupiah(p.price * qty)}</span>`;
            row.querySelector('.n').textContent = p.name;
            return row;
        }));

        if (items.size === 0) {
            els.receiptRows.innerHTML = '<div class="receipt__note" style="margin:0">Belum ada produk.</div>';
        }

        const total = localTotal();
        const count = [...items.values()].reduce((a, b) => a + b, 0);
        els.totals.forEach((el) => (el.textContent = rupiah(total)));
        els.counts.forEach((el) => (el.textContent = count));
        els.submits.forEach((el) => (el.disabled = items.size === 0));
    }

    /** Bagian yang angkanya dihitung server: budget & porsi. */
    function renderServerState() {
        if (summary.budget && els.budgetMeter) {
            els.budgetMeter.classList.toggle('is-over', summary.budget.state === 'over');
            els.budgetMeter.firstElementChild.style.width = summary.budget.percent + '%';
            els.budgetNote.textContent = summary.budget.message;
            els.budgetNote.classList.toggle('is-over', summary.budget.state === 'over');
        }

        if (summary.target && els.mealsMeter) {
            els.mealsValue.textContent = `±${summary.meals_covered}× makan`;
            els.mealsMeter.firstElementChild.style.width = summary.coverage + '%';
            els.mealsMeter.classList.toggle('is-warm', summary.coverage < 100);
            els.mealsNote.textContent = summary.coverage >= 100
                ? `Cukup untuk target ${summary.meals}× makan (${summary.people} orang).`
                : `Baru ${summary.coverage}% dari target ${summary.meals}× makan untuk ${summary.people} orang.`;
        }
    }

    function render() {
        renderLines();
        renderReceipt();
    }

    /* ---------- Sinkron ke server ---------- */
    function sync() {
        const payload = { items: [...items].map(([id, qty]) => ({ id, qty })) };
        els.saving.hidden = false;

        pending = pending
            .then(() => fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            }))
            .then((response) => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then((data) => {
                summary = data;
                // Server bisa memotong qty (stok berubah) — samakan tampilan.
                const serverItems = new Map(data.lines.map((line) => [line.id, line.qty]));
                const changed = serverItems.size !== items.size
                    || [...serverItems].some(([id, qty]) => items.get(id) !== qty);

                if (changed) {
                    items = serverItems;
                    render();
                }

                renderServerState();
            })
            .catch(() => {
                els.budgetNote && (els.budgetNote.textContent = 'Gagal menyimpan perubahan. Muat ulang halaman.');
            })
            .finally(() => (els.saving.hidden = true));

        return pending;
    }

    function commit() {
        render();
        sync();
    }

    /* ---------- Aksi pada baris ---------- */
    els.lines.addEventListener('click', (event) => {
        const row = event.target.closest('.builder-line');
        if (!row) return;
        const id = Number(row.dataset.id);

        if (event.target.closest('[data-remove]')) {
            items.delete(id);
            commit();
        } else if (event.target.closest('[data-swap]')) {
            openPicker(id);
        }
    });

    els.lines.addEventListener('change', (event) => {
        const input = event.target.closest('.qty input');
        if (!input) return;
        const id = Number(input.closest('.builder-line').dataset.id);
        items.set(id, Math.max(1, Number(input.value)));
        commit();
    });

    /* ---------- Pemilih produk (tambah / ganti) ---------- */
    function openPicker(swapId = null) {
        swapTarget = swapId;
        els.pickerTitle.textContent = swapId
            ? `Ganti ${catalog.get(swapId).name}`
            : 'Tambah produk';
        els.pickerSearch.value = '';
        refreshPicker();
        offcanvas.show();
    }

    function refreshPicker() {
        const query = els.pickerSearch.value.trim().toLowerCase();
        const total = localTotal();
        const max = summary.budget?.max ?? null;
        const swapQty = swapTarget ? items.get(swapTarget) : 1;
        const swapCost = swapTarget ? catalog.get(swapTarget).price * swapQty : 0;
        let visible = 0;

        pickerItems.forEach((item) => {
            const p = catalog.get(Number(item.dataset.id));
            const match = (!query || p.name.toLowerCase().includes(query))
                && (!activeCategory || p.category === activeCategory)
                && p.id !== swapTarget;

            item.hidden = !match;
            if (!match) return;
            visible++;

            item.classList.toggle('is-in', items.has(p.id));
            const delta = item.querySelector('.delta');
            const diff = p.price * swapQty - swapCost;
            const after = total + diff;
            const parts = [];

            if (swapTarget) {
                parts.push(diff === 0 ? 'harga sama' : (diff > 0 ? '+' : '−') + rupiah(Math.abs(diff)));
            } else if (items.has(p.id)) {
                parts.push(`sudah ${items.get(p.id)}× di bundle`);
            }

            if (max !== null && after > max) {
                parts.push(`lewat budget ${rupiah(after - max)}`);
            }

            delta.textContent = parts.join(' · ');
            delta.style.color = max !== null && after > max ? 'var(--danger)' : '';
        });

        els.pickerEmpty.hidden = visible > 0;
    }

    els.picker.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-picker-cat]');
        if (chip) {
            activeCategory = chip.dataset.pickerCat;
            els.picker.querySelectorAll('[data-picker-cat]').forEach((c) => c.classList.toggle('is-active', c === chip));
            refreshPicker();
            return;
        }

        const item = event.target.closest('.picker-item');
        if (!item) return;
        const id = Number(item.dataset.id);
        const p = catalog.get(id);

        if (swapTarget) {
            // Pertahankan urutan baris: produk baru menempati posisi yang lama.
            const qty = items.get(swapTarget);
            const next = new Map();
            items.forEach((value, key) => {
                if (key === swapTarget) {
                    if (!items.has(id)) next.set(id, Math.min(qty, p.stock));
                } else if (key === id) {
                    next.set(key, Math.min(value + qty, p.stock, 20));
                } else {
                    next.set(key, value);
                }
            });
            items = next;
        } else {
            items.set(id, Math.min((items.get(id) ?? 0) + 1, p.stock, 20));
        }

        offcanvas.hide();
        commit();
    });

    els.pickerSearch.addEventListener('input', refreshPicker);
    document.querySelectorAll('[data-open-picker]').forEach((button) => {
        button.addEventListener('click', () => openPicker());
    });

    /* ---------- Kirim ke keranjang: tunggu simpanan terakhir selesai ---------- */
    document.querySelectorAll('form[data-bundle-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.ready) return;
            event.preventDefault();
            pending.finally(() => {
                form.dataset.ready = '1';
                form.submit();
            });
        });
    });

    render();
    renderServerState();
}
