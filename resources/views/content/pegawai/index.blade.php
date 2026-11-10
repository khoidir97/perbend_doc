@extends('layouts/contentNavbarLayout')

@section('title', 'Data Pegawai')

@section('page-style')
  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}" />
  <!-- Select2 (SearchableSelect) CSS -->
  @include('_partials.searchable-select.styles')
  <style>
    /* Styling agar DataTable serasi dengan tema Sneat */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0 !important;
      margin: 0 2px !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link {
      border-radius: 0.375rem !important;
    }

    div.dataTables_wrapper div.dataTables_info {
      padding-top: 0.85em;
      color: #8592a3;
      font-size: 0.875rem;
    }

    div.dataTables_wrapper div.dataTables_length select {
      width: 70px;
      display: inline-block;
    }

    table.dataTable thead th {
      border-bottom: 1px solid #d9dee3 !important;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(67, 89, 113, 0.04) !important;
    }
  </style>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="mb-1 text-primary"><i class="icon-base bx bx-id-card me-2"></i>Data Pegawai</h4>
          <p class="text-muted mb-0">Kelola master data Pegawai & ASN (Server-Side DataTable & AJAX).</p>
        </div>
        <div>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPegawaiModal">
            <i class="icon-base bx bx-plus me-1"></i> Tambah Pegawai
          </button>
        </div>
      </div>

      <!-- Alert Container (Dikelola oleh AJAX) -->
      <div id="ajaxAlertContainer"></div>

      <!-- Card DataTable Pegawai -->
      <div class="card shadow-sm">
        <div class="card-datatable table-responsive p-3">
          <table id="pegawaiTable" class="table table-hover w-100">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 170px;">NIP</th>
                <th>Nama Pegawai</th>
                <th>SKPD / OPD</th>
                <th>Satuan Kerja (Satker)</th>
                <th style="width: 100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Tambah Pegawai -->
  <div class="modal fade" id="createPegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base bx bx-plus-circle me-1"></i> Tambah Pegawai Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="createPegawaiForm">
          @csrf
          <div class="modal-body">
            <!-- Alert Error Form -->
            <div class="alert alert-danger d-none form-errors" role="alert">
              <ul class="mb-0 ps-3"></ul>
            </div>

            <div class="mb-3">
              <label for="create_pgw_nip" class="form-label">NIP Pegawai <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="create_pgw_nip" name="pgw_nip"
                placeholder="Contoh: 198501012010011001" required>
            </div>
            <div class="mb-3">
              <label for="create_pgw_nama" class="form-label">Nama Lengkap Pegawai <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="create_pgw_nama" name="pgw_nama"
                placeholder="Contoh: AHMAD FAUZI, S.Kom" required>
            </div>
            <div class="mb-3">
              <label for="create_pgw_kd_skpd" class="form-label">SKPD / OPD</label>
              <x-select2 id="create_pgw_kd_skpd" name="pgw_kd_skpd" :options="$skpdList"
                value-field="opd_kode" label-field="opd_nama" code-field="opd_kode"
                placeholder="-- Pilih SKPD / OPD --"
                search-placeholder="Ketik kode / nama SKPD..."
                no-results="SKPD tidak ditemukan" />
            </div>
            <div class="mb-3">
              <label for="create_pgw_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
              <select class="form-select" id="create_pgw_kd_satker" name="pgw_kd_satker"
                data-searchable-select
                data-placeholder="-- Pilih SKPD terlebih dahulu --"
                data-search-placeholder="Ketik nama atau kode satker..."
                data-no-results="Satker tidak ditemukan">
                <option value=""></option>
              </select>
              <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD yang dipilih.</small>
            </div>
            <div class="mb-3 form-password-toggle">
              <label for="create_pgw_password" class="form-label">Password Login Pegawai</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="create_pgw_password" name="pgw_password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="create_pgw_password">
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
              <small class="text-muted">Password login aplikasi (minimal 6 karakter). Boleh dikosongkan jika belum ingin diset.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmitCreate">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              <i class="icon-base bx bx-save me-1"></i> Simpan Pegawai
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Pegawai -->
  <div class="modal fade" id="editPegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base bx bx-edit me-1"></i> Edit Data Pegawai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editPegawaiForm">
          @csrf
          @method('PUT')
          <input type="hidden" id="edit_pgw_id" name="id">
          <div class="modal-body">
            <!-- Alert Error Form -->
            <div class="alert alert-danger d-none form-errors" role="alert">
              <ul class="mb-0 ps-3"></ul>
            </div>

            <div class="mb-3">
              <label for="edit_pgw_nip" class="form-label">NIP Pegawai <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_pgw_nip" name="pgw_nip" required>
            </div>
            <div class="mb-3">
              <label for="edit_pgw_nama" class="form-label">Nama Lengkap Pegawai <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_pgw_nama" name="pgw_nama" required>
            </div>
            <div class="mb-3">
              <label for="edit_pgw_kd_skpd" class="form-label">SKPD / OPD</label>
              <x-select2 id="edit_pgw_kd_skpd" name="pgw_kd_skpd" :options="$skpdList"
                value-field="opd_kode" label-field="opd_nama" code-field="opd_kode"
                placeholder="-- Pilih SKPD / OPD --"
                search-placeholder="Ketik kode / nama SKPD..."
                no-results="SKPD tidak ditemukan" />
            </div>
            <div class="mb-3">
              <label for="edit_pgw_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
              <select class="form-select" id="edit_pgw_kd_satker" name="pgw_kd_satker"
                data-searchable-select
                data-placeholder="-- Pilih SKPD terlebih dahulu --"
                data-search-placeholder="Ketik nama atau kode satker..."
                data-no-results="Satker tidak ditemukan">
                <option value=""></option>
              </select>
              <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD yang dipilih.</small>
            </div>
            <div class="mb-3 form-password-toggle">
              <label for="edit_pgw_password" class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah password)</small></label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="edit_pgw_password" name="pgw_password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="edit_pgw_password">
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
              <small class="text-muted">Isi minimal 6 karakter hanya jika ingin mengganti password pegawai ini.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmitEdit">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              <i class="icon-base bx bx-save me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus Pegawai -->
  <div class="modal fade" id="deletePegawaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus Pegawai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deletePegawaiForm">
          @csrf
          @method('DELETE')
          <input type="hidden" id="delete_pgw_id" name="id">
          <div class="modal-body text-center py-4">
            <i class="icon-base bx bx-error-circle text-danger fs-1 mb-2"></i>
            <p class="mb-0">Apakah Anda yakin ingin menghapus data pegawai <strong id="delete_pgw_nama" class="text-dark"></strong>?</p>
            <small class="text-muted">NIP: <span id="delete_pgw_nip" class="fw-semibold"></span></small>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger" id="btnSubmitDelete">
              <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              <i class="icon-base bx bx-trash me-1"></i> Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <!-- Memastikan jQuery selalu tersedia untuk DataTables -->
  <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
  <!-- DataTables JS & Bootstrap 5 Integration -->
  <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
  <!-- Select2 (SearchableSelect) JS -->
  @include('_partials.searchable-select.scripts')

  <script>
    jQuery(document).ready(function ($) {
      // Setup CSRF token untuk semua AJAX request
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          'Accept': 'application/json'
        }
      });

      // Inisialisasi Server-Side DataTable Pegawai
      const table = $('#pegawaiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ route('pegawai.index') }}",
          type: "GET",
          error: function (xhr, error, thrown) {
            console.error("DataTable Error:", xhr.responseText);
          }
        },
        columns: [
          { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
          { data: 'pgw_nip', name: 'pgw_nip' },
          { data: 'pgw_nama', name: 'pgw_nama' },
          { data: 'skpd', name: 'pgw_kd_skpd' },
          { data: 'satker', name: 'pgw_kd_satker' },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[2, 'asc']],
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Cari NIP, nama, SKPD, satker...",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_ pegawai",
          infoEmpty: "Menampilkan 0 data",
          infoFiltered: "(disaring dari _MAX_ total data)",
          zeroRecords: "Tidak ada data pegawai yang cocok",
          emptyTable: "Belum ada data pegawai",
          loadingRecords: "Memuat data...",
          processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat data...',
          paginate: {
            previous: '<i class="icon-base bx bx-chevron-left"></i>',
            next: '<i class="icon-base bx bx-chevron-right"></i>'
          }
        },
        dom: '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 text-md-end"f>>t<"row align-items-center mt-3"<"col-md-6"i><"col-md-6 text-md-end"p>>'
      });

      // Helper untuk notifikasi toast / alert
      function showNotification(type, message) {
        const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
        const alertHtml = `
          <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="icon-base bx ${icon} me-1"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        `;
        $('#ajaxAlertContainer').html(alertHtml);
        setTimeout(() => {
          $('#ajaxAlertContainer .alert').alert('close');
        }, 5000);
      }

      // Helper untuk error validasi form
      function showFormErrors($form, errors) {
        const $errorDiv = $form.find('.form-errors');
        const $ul = $errorDiv.find('ul');
        $ul.empty();
        $.each(errors, function (field, messages) {
          $.each(messages, function (i, msg) {
            $ul.append(`<li>${msg}</li>`);
          });
        });
        $errorDiv.removeClass('d-none');
      }

      function clearFormErrors($form) {
        $form.find('.form-errors').addClass('d-none').find('ul').empty();
      }

      // Modal Instance
      const createModal = new bootstrap.Modal(document.getElementById('createPegawaiModal'));
      const editModal = new bootstrap.Modal(document.getElementById('editPegawaiModal'));
      const deleteModal = new bootstrap.Modal(document.getElementById('deletePegawaiModal'));

      // Helper untuk memuat pilihan Satker berdasarkan SKPD via AJAX
      function loadSatkerOptions($selectSatker, kdSkpd, selectedSatkerKode = null) {
        SearchableSelect.destroy($selectSatker);
        $selectSatker.empty().append('<option value=""></option>');

        if (!kdSkpd) {
          $selectSatker.attr('data-placeholder', '-- Pilih SKPD terlebih dahulu --');
          SearchableSelect.init($selectSatker);
          return;
        }

        $selectSatker.attr('data-placeholder', '-- Memuat Satker... --');
        SearchableSelect.init($selectSatker);

        $.ajax({
          url: "{{ route('pegawai.satker-by-skpd') }}",
          type: "GET",
          data: { kd_skpd: kdSkpd },
          success: function (data) {
            SearchableSelect.destroy($selectSatker);
            $selectSatker.empty().append('<option value=""></option>');

            if (data && data.length > 0) {
              $selectSatker.attr('data-placeholder', '-- Pilih Satker (Opsional) --');
              $.each(data, function (i, item) {
                const $opt = $('<option>')
                  .val(item.stk_kode_satker)
                  .attr('data-code', item.stk_kode_satker)
                  .attr('data-label', item.stk_nama)
                  .text((item.stk_kode_satker ? '[' + item.stk_kode_satker + '] ' : '') + item.stk_nama);

                if (selectedSatkerKode && String(selectedSatkerKode) === String(item.stk_kode_satker)) {
                  $opt.prop('selected', true);
                }
                $selectSatker.append($opt);
              });
            } else {
              $selectSatker.attr('data-placeholder', '-- Tidak ada Satker di SKPD ini --');
            }

            SearchableSelect.init($selectSatker);
            if (selectedSatkerKode) {
              SearchableSelect.setValue($selectSatker, selectedSatkerKode);
            }
          },
          error: function () {
            SearchableSelect.destroy($selectSatker);
            $selectSatker.attr('data-placeholder', '-- Gagal memuat data Satker --');
            SearchableSelect.init($selectSatker);
          }
        });
      }

      // Event Chained Dropdown SKPD -> Satker
      $('#create_pgw_kd_skpd').on('change', function () {
        const kdSkpd = $(this).val();
        loadSatkerOptions($('#create_pgw_kd_satker'), kdSkpd);
      });

      $('#edit_pgw_kd_skpd').on('change', function () {
        const kdSkpd = $(this).val();
        loadSatkerOptions($('#edit_pgw_kd_satker'), kdSkpd);
      });

      // Reset create form saat modal dibuka
      $('#createPegawaiModal').on('show.bs.modal', function () {
        $('#createPegawaiForm')[0].reset();
        SearchableSelect.reset('#create_pgw_kd_skpd');
        loadSatkerOptions($('#create_pgw_kd_satker'), null);
        clearFormErrors($('#createPegawaiForm'));
        if (window.Helpers && window.Helpers.initPasswordToggle) {
          window.Helpers.initPasswordToggle();
        }
      });

      // 1. AJAX: SIMPAN PEGAWAI BARU
      $('#createPegawaiForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitCreate');
        clearFormErrors($form);

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
          url: "{{ route('pegawai.store') }}",
          type: "POST",
          data: $form.serialize(),
          success: function (res) {
            createModal.hide();
            $form[0].reset();
            SearchableSelect.reset('#create_pgw_kd_skpd');
            loadSatkerOptions($('#create_pgw_kd_satker'), null);
            table.ajax.reload(null, false);
            showNotification('success', res.message || 'Pegawai berhasil ditambahkan!');
          },
          error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
              showFormErrors($form, xhr.responseJSON.errors);
            } else {
              const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan data pegawai.';
              showFormErrors($form, { general: [msg] });
            }
          },
          complete: function () {
            $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
          }
        });
      });

      // 2. Buka Modal Edit & Isi Data
      $(document).on('click', '.edit-pegawai-btn', function () {
        const id = $(this).data('id');
        const nip = $(this).data('nip');
        const nama = $(this).data('nama');
        const kdSkpd = $(this).data('kd_skpd');
        const kdSatker = $(this).data('kd_satker');

        clearFormErrors($('#editPegawaiForm'));
        $('#edit_pgw_id').val(id);
        $('#edit_pgw_nip').val(nip);
        $('#edit_pgw_nama').val(nama);
        $('#edit_pgw_password').val('');
        SearchableSelect.setValue('#edit_pgw_kd_skpd', kdSkpd);
        loadSatkerOptions($('#edit_pgw_kd_satker'), kdSkpd, kdSatker);
        if (window.Helpers && window.Helpers.initPasswordToggle) {
          window.Helpers.initPasswordToggle();
        }

        editModal.show();
      });

      // 3. AJAX: UPDATE DATA PEGAWAI
      $('#editPegawaiForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitEdit');
        const pegawaiId = $('#edit_pgw_id').val();
        clearFormErrors($form);

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
          url: `{{ url('pegawai') }}/${pegawaiId}`,
          type: "POST",
          data: $form.serialize(),
          success: function (res) {
            editModal.hide();
            table.ajax.reload(null, false);
            showNotification('success', res.message || 'Data pegawai berhasil diperbarui!');
          },
          error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
              showFormErrors($form, xhr.responseJSON.errors);
            } else {
              const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memperbarui data pegawai.';
              showFormErrors($form, { general: [msg] });
            }
          },
          complete: function () {
            $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
          }
        });
      });

      // 4. Buka Modal Hapus
      $(document).on('click', '.delete-pegawai-btn', function () {
        const id = $(this).data('id');
        const nip = $(this).data('nip');
        const nama = $(this).data('nama');

        $('#delete_pgw_id').val(id);
        $('#delete_pgw_nip').text(nip);
        $('#delete_pgw_nama').text(nama);

        deleteModal.show();
      });

      // 5. AJAX: HAPUS PEGAWAI (Soft Delete)
      $('#deletePegawaiForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitDelete');
        const pegawaiId = $('#delete_pgw_id').val();

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
          url: `{{ url('pegawai') }}/${pegawaiId}`,
          type: "POST",
          data: $form.serialize(),
          success: function (res) {
            deleteModal.hide();
            table.ajax.reload(null, false);
            showNotification('success', res.message || 'Pegawai berhasil dihapus!');
          },
          error: function (xhr) {
            deleteModal.hide();
            const msg = xhr.responseJSON?.message || 'Gagal menghapus data pegawai.';
            showNotification('danger', msg);
          },
          complete: function () {
            $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
          }
        });
      });
    });
  </script>
@endsection
