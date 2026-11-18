/**
 * SearchableSelect — wrapper Select2 reusable (tema Bootstrap 5 / Sneat).
 *
 * Dependensi : jQuery + select2.min.js (dimuat sebelum file ini).
 *
 * Cara pakai:
 *   1. Otomatis : tambahkan atribut `data-searchable-select` pada <select>.
 *                 Elemen akan diinisialisasi saat halaman siap.
 *   2. Manual   : SearchableSelect.init('#selector', { ...opsiSelect2 });
 *
 * Atribut <select> (opsional):
 *   data-placeholder         Teks placeholder               (default: "-- Pilih --")
 *   data-search-placeholder  Placeholder kolom pencarian    (default: "Ketik untuk mencari...")
 *   data-no-results          Pesan jika tidak ditemukan     (default: "Data tidak ditemukan")
 *   data-allow-clear         "true" / "false"               (default: true)
 *
 * Atribut <option> (opsional, untuk tampilan "badge kode + label"):
 *   data-code   Kode singkat, ditampilkan sebagai badge
 *   data-label  Teks utama
 *   Jika tidak diisi, teks <option> yang digunakan.
 *
 * Fitur:
 *   - Pencarian multi-kata (setiap kata harus cocok, tidak case-sensitive) pada kode + label.
 *   - Highlight kata kunci di hasil pencarian.
 *   - Dropdown otomatis menempel ke modal induk (aman di dalam Bootstrap Modal).
 *   - Fokus otomatis ke kolom pencarian, dropdown ditutup saat modal ditutup.
 */
(function ($, window, document) {
  'use strict';

  if (!$ || !$.fn || !$.fn.select2) {
    console.error('[SearchableSelect] jQuery dan Select2 harus dimuat terlebih dahulu.');
    return;
  }

  const AUTO_SELECTOR = 'select[data-searchable-select]';

  // ---------------------------------------------------------------- Helpers
  const escapeHtml = str =>
    String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

  const escapeRegex = str => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

  function highlight(text, term) {
    const safe = escapeHtml(text);
    if (!term) return safe;
    const words = term
      .split(/\s+/)
      .filter(Boolean)
      .map(w => escapeRegex(escapeHtml(w)));
    if (!words.length) return safe;
    return safe.replace(new RegExp('(' + words.join('|') + ')', 'gi'), '<mark>$1</mark>');
  }

  // Ambil kode & label dari <option>; fallback ke teks option
  function getParts(data) {
    const $opt = data.element ? $(data.element) : $();
    const code = $opt.attr('data-code');
    const label = $opt.attr('data-label');
    return {
      code: code !== undefined ? String(code) : '',
      label: label !== undefined ? String(label) : $.trim(data.text || '')
    };
  }

  function boolAttr(value, fallback) {
    if (value === undefined) return fallback;
    return String(value).toLowerCase() !== 'false';
  }

  // ---------------------------------------------------------------- Core
  function buildOptions($select, overrides) {
    let currentTerm = '';
    const $modal = $select.closest('.modal');

    return $.extend(
      true,
      {
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $modal.length ? $modal : $(document.body),
        placeholder: $select.attr('data-placeholder') || '-- Pilih --',
        allowClear: boolAttr($select.attr('data-allow-clear'), true),

        matcher: function (params, data) {
          currentTerm = $.trim(params.term || '');
          if (currentTerm === '') return data;
          if (!data.element || !data.id) return null;

          const parts = getParts(data);
          const haystack = (parts.code + ' ' + parts.label + ' ' + (data.text || '')).toLowerCase();
          const words = currentTerm.toLowerCase().split(/\s+/);
          return words.every(w => haystack.indexOf(w) > -1) ? data : null;
        },

        templateResult: function (data) {
          if (data.loading || !data.element) return data.text;
          const parts = getParts(data);
          let html = '<div class="ss-option">';
          if (parts.code) {
            html += '<span class="ss-code">' + highlight(parts.code, currentTerm) + '</span>';
          }
          html += '<span class="ss-label">' + highlight(parts.label, currentTerm) + '</span></div>';
          return $(html);
        },

        templateSelection: function (data) {
          if (!data.id || !data.element) return data.text;
          const parts = getParts(data);
          return parts.code ? '[' + parts.code + '] ' + parts.label : parts.label;
        },

        language: {
          noResults: () => $select.attr('data-no-results') || 'Data tidak ditemukan',
          searching: () => 'Mencari...',
          removeAllItems: () => 'Hapus pilihan'
        }
      },
      overrides || {}
    );
  }

  function initOne(el, overrides) {
    const $select = $(el);
    if (!$select.is('select') || $select.hasClass('select2-hidden-accessible')) return $select;

    $select.select2(buildOptions($select, overrides));

    // Fokus otomatis + placeholder pada kolom pencarian
    $select.on('select2:open.searchable', function () {
      setTimeout(function () {
        const field = document.querySelector('.select2-container--open .select2-search__field');
        if (field) {
          field.placeholder = $select.attr('data-search-placeholder') || 'Ketik untuk mencari...';
          field.focus();
        }
      }, 0);
    });

    return $select;
  }

  const SearchableSelect = {
    /** Inisialisasi satu / banyak <select>. */
    init: function (target, overrides) {
      $(target).each(function () {
        initOne(this, overrides);
      });
      return $(target);
    },

    /** Set nilai & perbarui tampilan (null / '' untuk mengosongkan). */
    setValue: function (target, value) {
      return $(target).val(value === undefined || value === '' ? null : value).trigger('change');
    },

    /** Kosongkan pilihan. */
    reset: function (target) {
      return this.setValue(target, null);
    },

    /** Hancurkan instance Select2. */
    destroy: function (target) {
      $(target).each(function () {
        const $s = $(this);
        if ($s.hasClass('select2-hidden-accessible')) $s.off('.searchable').select2('destroy');
      });
    },

    /** Inisialisasi semua elemen dengan atribut data-searchable-select di dalam container. */
    autoInit: function (container) {
      return this.init($(container || document).find(AUTO_SELECTOR));
    }
  };

  // Tutup dropdown yang terbuka saat modal ditutup
  $(document).on('hide.bs.modal', '.modal', function () {
    $(this)
      .find('select.select2-hidden-accessible')
      .each(function () {
        $(this).select2('close');
      });
  });

  // Saat reset form native, sinkronkan tampilan Select2
  $(document).on('reset', 'form', function () {
    const $form = $(this);
    setTimeout(function () {
      $form.find('select.select2-hidden-accessible').trigger('change.select2');
    }, 0);
  });

  $(function () {
    SearchableSelect.autoInit();
  });

  // Jadikan plugin jQuery juga: $('#el').searchableSelect({...})
  $.fn.searchableSelect = function (overrides) {
    return SearchableSelect.init(this, overrides);
  };

  window.SearchableSelect = SearchableSelect;
})(window.jQuery, window, document);

