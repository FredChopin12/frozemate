import 'bootstrap';
import { initBundleBuilder } from './bundle-builder';

const rupiah = (n) => 'Rp' + Number(n).toLocaleString('id-ID');

/* ---------- Toast (pesan flash) ---------- */
function dismissToast(toast) {
    toast.classList.add('is-leaving');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
}

document.querySelectorAll('.toast-fm').forEach((toast, i) => {
    toast.querySelector('button')?.addEventListener('click', () => dismissToast(toast));
    setTimeout(() => toast.isConnected && dismissToast(toast), 5200 + i * 600);
});

/* ---------- Stepper jumlah ---------- */
document.addEventListener('click', (event) => {
    const button = event.target.closest('.qty button[data-step]');
    if (!button) return;

    const input = button.closest('.qty').querySelector('input');
    const min = Number(input.min || 0);
    const max = Number(input.max || 99);
    const next = Math.min(max, Math.max(min, Number(input.value || 0) + Number(button.dataset.step)));

    if (next !== Number(input.value)) {
        input.value = next;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

// Form keranjang: kirim otomatis setelah jumlah diubah.
const submitTimers = new WeakMap();
document.addEventListener('change', (event) => {
    const input = event.target.closest('.qty input');
    if (!input) return;

    const min = Number(input.min || 0);
    const max = Number(input.max || 99);
    input.value = Math.min(max, Math.max(min, parseInt(input.value, 10) || min));

    const form = input.closest('form[data-autosubmit]');
    if (!form) return;

    clearTimeout(submitTimers.get(form));
    submitTimers.set(form, setTimeout(() => form.requestSubmit(), 450));
});

/* ---------- Konfirmasi aksi yang menghapus ---------- */
document.addEventListener('submit', (event) => {
    const message = event.target.dataset?.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

/* ---------- Panel Smart Bundle: ringkasan pilihan ---------- */
document.querySelectorAll('[data-bundle-form]').forEach((form) => {
    const readout = form.querySelector('[data-readout]');
    if (!readout) return;

    const update = () => {
        const data = new FormData(form);
        const people = Number(data.get('people'));
        const meals = Number(data.get('meals'));
        const budget = form.querySelector('input[name="budget"]:checked')?.dataset.long;

        if (!people || !meals || !budget || !data.get('need')) {
            readout.innerHTML = 'Lengkapi empat pilihan di atas.';
            return;
        }

        readout.innerHTML = `Target <b>${people * meals} porsi</b> (${people === 5 ? '5+' : people} orang × ${meals}× makan)<br>Budget <b>${budget}</b>`;
    };

    form.addEventListener('change', update);
    update();
});

/* ---------- Isi cepat akun demo di halaman masuk ---------- */
document.querySelectorAll('[data-fill-email]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = document.querySelector('[data-login-form]');
        form.querySelector('[name="email"]').value = button.dataset.fillEmail;
        form.querySelector('[name="password"]').value = button.dataset.fillPassword;
        form.querySelector('[name="password"]').focus();
    });
});

/* ---------- Admin: pratinjau foto & kemasan ---------- */
document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const target = document.querySelector(input.dataset.imageInput);
        if (!file || !target) return;

        let img = target.querySelector('img');
        if (!img) {
            img = document.createElement('img');
            img.alt = '';
            target.appendChild(img);
        }
        img.src = URL.createObjectURL(file);
    });
});

document.querySelectorAll('[data-tone-preview]').forEach((group) => {
    const target = document.querySelector(group.dataset.tonePreview);
    group.addEventListener('change', (event) => {
        if (!target || event.target.name !== 'tone') return;
        target.className = target.className.replace(/tone-\w+/, `tone-${event.target.value}`);
    });
});

/* ---------- Racik bundle ---------- */
const builder = document.getElementById('bundle-builder');
if (builder) {
    initBundleBuilder(builder, rupiah);
}
