// JavaScript minimal MitraPesisir (progressive enhancement).
// Seluruh fitur tetap berfungsi tanpa JS kecuali: modal <dialog>, tombol "Lihat" sandi, dan konfirmasi hapus.
(function () {
  'use strict';

  // Tampilkan / sembunyikan kata sandi.
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-toggle-password'));
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Sembunyi' : 'Lihat';
      btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  });

  // Modal berbasis <dialog>.
  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = document.getElementById(btn.getAttribute('data-modal-open'));
      if (dlg && typeof dlg.showModal === 'function') dlg.showModal();
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      if (dlg) dlg.close();
    });
  });
  document.querySelectorAll('dialog.modal').forEach(function (dlg) {
    // Klik di luar kotak = tutup.
    dlg.addEventListener('click', function (e) {
      if (e.target === dlg) dlg.close();
    });
    // Buka otomatis bila ada galat validasi di dalamnya.
    if (dlg.hasAttribute('data-open-on-load') && typeof dlg.showModal === 'function') dlg.showModal();
  });

  // Konfirmasi sebelum menghapus.
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Tutup pesan flash.
  document.querySelectorAll('[data-flash-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var box = btn.closest('[data-flash]');
      if (box) box.remove();
    });
  });
  document.querySelectorAll('[data-flash]').forEach(function (box) {
    setTimeout(function () { if (box.isConnected) box.remove(); }, 8000);
  });
})();
