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

  // Pemilihan penerima informasi admin.
  document.querySelectorAll('[data-recipient-list]').forEach(function (list) {
    var dialog = list.closest('dialog');
    var checkboxes = Array.from(list.querySelectorAll('[data-recipient-checkbox]'));
    var selectAll = dialog.querySelector('[data-select-all-checkbox]');
    var search = dialog.querySelector('[data-recipient-search]');
    var selectedCount = dialog.querySelector('[data-selected-count]');

    function updateSelection() {
      var selected = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
      if (selectedCount) selectedCount.textContent = String(selected);
      if (selectAll) {
        selectAll.checked = checkboxes.length > 0 && selected === checkboxes.length;
        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
      }
    }

    checkboxes.forEach(function (checkbox) {
      checkbox.addEventListener('change', updateSelection);
    });
    if (selectAll) {
      selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
        updateSelection();
      });
    }
    if (search) {
      search.addEventListener('input', function () {
        var term = search.value.trim().toLocaleLowerCase();
        list.querySelectorAll('[data-recipient-option]').forEach(function (option) {
          option.hidden = !option.getAttribute('data-search-text').includes(term);
        });
      });
    }

    var form = dialog.querySelector('#admin-share-form');
    if (form) {
      form.addEventListener('submit', function (event) {
        if (event.submitter && event.submitter.hasAttribute('data-send-all')) {
          checkboxes.forEach(function (checkbox) { checkbox.checked = true; });
          updateSelection();
        }
      });
    }

    updateSelection();
  });

  // Konfirmasi sebelum menghapus.
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var submitterPrompt = e.submitter && e.submitter.getAttribute('data-confirm');
      var prompt = submitterPrompt || form.getAttribute('data-confirm');
      if (prompt && !window.confirm(prompt)) e.preventDefault();
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
