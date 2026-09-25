/**
 * Unsaved Changes Guard for UC PSC Management System
 * Mencegah kehilangan draf isian form saat berpindah halaman atau menekan tombol back.
 */

(function () {
    window.isSubmittingForm = false;
    let hasPushedHistoryState = false;

    // Monkey patch HTMLFormElement.prototype.submit agar programmatic submit (form.submit())
    // tidak memicu browser beforeunload alert.
    const originalSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        window.isSubmittingForm = true;
        return originalSubmit.apply(this, arguments);
    };

    // Ambil snapshot nilai awal seluruh input form
    function initFormSnapshots() {
        const forms = document.querySelectorAll('main form:not([method="GET"]):not([method="get"]):not([data-no-guard]):not([data-no-unsaved-guard]), form[data-unsaved-guard]');
        forms.forEach(form => {
            if (form.getAttribute('action')?.includes('logout')) return;
            if (form.hasAttribute('data-no-guard') || form.hasAttribute('data-no-unsaved-guard')) return;

            const inputs = form.querySelectorAll('input:not([type="hidden"]):not([name="_token"]):not([name="_method"]), select, textarea');
            inputs.forEach(input => {
                if (!input.hasAttribute('data-initial-value')) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.setAttribute('data-initial-checked', input.checked ? 'true' : 'false');
                    } else {
                        input.setAttribute('data-initial-value', input.value || '');
                    }
                }
            });

            // Tandai saat submit form agar guard tidak memblokir
            form.addEventListener('submit', function () {
                window.isSubmittingForm = true;
            });
        });
    }

    // Periksa apakah form memiliki perubahan / data terisi
    window.checkFormDirty = function () {
        if (window.isSubmittingForm) return false;

        const forms = document.querySelectorAll('main form:not([method="GET"]):not([method="get"]):not([data-no-guard]):not([data-no-unsaved-guard]), form[data-unsaved-guard]');
        for (const form of forms) {
            if (form.getAttribute('action')?.includes('logout')) continue;
            if (form.hasAttribute('data-no-guard') || form.hasAttribute('data-no-unsaved-guard')) continue;

            const inputs = form.querySelectorAll('input:not([type="hidden"]):not([name="_token"]):not([name="_method"]), select, textarea');
            for (const input of inputs) {
                // Abaikan input yang disabled secara permanen atau tidak terlihat
                if (input.type === 'checkbox' || input.type === 'radio') {
                    const initialChecked = input.getAttribute('data-initial-checked') === 'true';
                    if (input.checked !== initialChecked) {
                        return true;
                    }
                } else {
                    const initialValue = input.getAttribute('data-initial-value') || '';
                    const currentValue = input.value || '';

                    // Jika input baru bertambah (misal tambah anggota dinamis) dan ada isinya
                    if (!input.hasAttribute('data-initial-value')) {
                        if (currentValue.trim().length > 0) return true;
                    } else if (currentValue.trim() !== initialValue.trim()) {
                        return true;
                    }
                }
            }
        }
        return false;
    };

    // Inisialisasi snapshot setelah DOM siap dan setelah Alpine siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initFormSnapshots, 300);
        });
    } else {
        setTimeout(initFormSnapshots, 300);
    }

    // Dengarkan perubahan input untuk pushState history browser (agar back button bisa di-intercept)
    document.addEventListener('input', function (e) {
        if (!hasPushedHistoryState && window.checkFormDirty()) {
            hasPushedHistoryState = true;
            try {
                history.pushState({ unsavedGuard: true }, '');
            } catch (err) {
                // fallback jika history API dibatasi
            }
        }
    });

    // 1. Intercept Link Klik (Tombol Kembali, Batal, Sidebar, Logo, Profil)
    document.addEventListener('click', function (e) {
        if (window.isSubmittingForm) return;

        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        // Abaikan link anchor, javascript, atau target new tab
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank') {
            return;
        }

        if (window.checkFormDirty()) {
            e.preventDefault();
            e.stopPropagation();
            window.dispatchEvent(new CustomEvent('open-unsaved-modal', {
                detail: { url: href, isBrowserBack: false }
            }));
        }
    }, true);

    // 2. Intercept Browser Back Button
    window.addEventListener('popstate', function (e) {
        if (window.isSubmittingForm) return;

        if (window.checkFormDirty()) {
            // Push kembali agar tidak langsung keluar halaman
            try {
                history.pushState({ unsavedGuard: true }, '');
            } catch (err) {}

            window.dispatchEvent(new CustomEvent('open-unsaved-modal', {
                detail: { url: null, isBrowserBack: true }
            }));
        }
    });

    // 3. Intercept Browser Tab Close / Reload
    window.addEventListener('beforeunload', function (e) {
        if (window.isSubmittingForm) return;

        if (window.checkFormDirty()) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
})();
