@extends('layouts/contentNavbarLayout')

@section('title', 'Data SKPD')

@section('page-style')
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
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
                <h4 class="mb-1 text-primary"><i class="icon-base bx bx-buildings me-2"></i>Data SKPD / OPD</h4>
                <p class="text-muted mb-0">Kelola master data Satuan Kerja Perangkat Daerah (Server-Side DataTable & AJAX).</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSkpdModal">
                    <i class="icon-base bx bx-plus me-1"></i> Tambah SKPD
                </button>
            </div>
        </div>

        <!-- Alert Container (Dikelola oleh AJAX) -->
        <div id="ajaxAlertContainer"></div>

        <!-- Card DataTable SKPD -->
        <div class="card shadow-sm">
            <div class="card-datatable table-responsive p-3">
                <table id="skpdTable" class="table table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 140px;">Kode OPD</th>
                            <th>Nama SKPD / OPD</th>
                            <th style="width: 110px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah SKPD -->
<div class="modal fade" id="createSkpdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="icon-base bx bx-plus-circle me-1"></i> Tambah SKPD Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSkpdForm">
                @csrf
                <div class="modal-body">
                    <!-- Alert Error Form -->
                    <div class="alert alert-danger d-none form-errors" role="alert">
                        <ul class="mb-0 ps-3"></ul>
                    </div>

                    <div class="mb-3">
                        <label for="create_opd_kode" class="form-label">Kode OPD / SKPD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_opd_kode" name="opd_kode" placeholder="Contoh: 001 atau 1.01.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_opd_nama" class="form-label">Nama SKPD / OPD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_opd_nama" name="opd_nama" placeholder="Contoh: DINAS PENDIDIKAN" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitCreate">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <i class="icon-base bx bx-save me-1"></i> Simpan SKPD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit SKPD -->
<div class="modal fade" id="editSkpdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="icon-base bx bx-edit me-1"></i> Edit SKPD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSkpdForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_skpd_id" name="id">
                <div class="modal-body">
                    <!-- Alert Error Form -->
                    <div class="alert alert-danger d-none form-errors" role="alert">
                        <ul class="mb-0 ps-3"></ul>
                    </div>

                    <div class="mb-3">
                        <label for="edit_opd_kode" class="form-label">Kode OPD / SKPD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_opd_kode" name="opd_kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_opd_nama" class="form-label">Nama SKPD / OPD <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_opd_nama" name="opd_nama" required>
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

<!-- Modal Konfirmasi Hapus SKPD -->
<div class="modal fade" id="deleteSkpdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus SKPD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteSkpdForm">
                @csrf
                @method('DELETE')
                <input type="hidden" id="delete_skpd_id" name="id">
                <div class="modal-body text-center py-4">
                    <i class="icon-base bx bx-error-circle text-danger fs-1 mb-2"></i>
                    <p class="mb-0">Apakah Anda yakin ingin menghapus SKPD <strong id="delete_skpd_nama" class="text-dark"></strong>?</p>
                    <small class="text-muted">Kode: <span id="delete_skpd_kode" class="fw-semibold"></span></small>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables JS & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
jQuery(document).ready(function ($) {
    // Setup CSRF token untuk semua AJAX request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    // Inisialisasi Server-Side DataTable SKPD
    const table = $('#skpdTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('skpd.index') }}",
            type: "GET",
            error: function (xhr, error, thrown) {
                console.error("DataTable Error:", xhr.responseText);
            }
        },
        columns: [
            { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
            { data: 'opd_kode', name: 'opd_kode' },
            { data: 'opd_nama', name: 'opd_nama' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari kode / nama SKPD...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ SKPD",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Tidak ada data SKPD yang cocok",
            emptyTable: "Belum ada data SKPD",
            loadingRecords: "Memuat data...",
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat data...',
            paginate: {
                previous: '<i class="icon-base bx bx-chevron-left"></i>',
                next: '<i class="icon-base bx bx-chevron-right"></i>'
            }
        },
        dom: '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 text-md-end"f>>t<"row align-items-center mt-3"<"col-md-6"i><"col-md-6 text-md-end"p>>'
    });

    // Helper untuk menampilkan notifikasi alert tanpa reload browser
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

    // Helper untuk menampilkan error validasi form
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
    const createModal = new bootstrap.Modal(document.getElementById('createSkpdModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editSkpdModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSkpdModal'));

    // Reset create form saat modal dibuka
    $('#createSkpdModal').on('show.bs.modal', function () {
        $('#createSkpdForm')[0].reset();
        clearFormErrors($('#createSkpdForm'));
    });

    // 1. AJAX: SIMPAN SKPD BARU
    $('#createSkpdForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitCreate');
        clearFormErrors($form);

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: "{{ route('skpd.store') }}",
            type: "POST",
            data: $form.serialize(),
            success: function (res) {
                createModal.hide();
                $form[0].reset();
                table.ajax.reload(null, false); // Reload tabel tanpa reload browser
                showNotification('success', res.message || 'SKPD berhasil ditambahkan!');
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showFormErrors($form, xhr.responseJSON.errors);
                } else {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan SKPD.';
                    showFormErrors($form, { general: [msg] });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });

    // 2. Buka Modal Edit & Isi Data
    $(document).on('click', '.edit-skpd-btn', function () {
        const id = $(this).data('id');
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');

        clearFormErrors($('#editSkpdForm'));
        $('#edit_skpd_id').val(id);
        $('#edit_opd_kode').val(kode);
        $('#edit_opd_nama').val(nama);

        editModal.show();
    });

    // 3. AJAX: UPDATE DATA SKPD
    $('#editSkpdForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitEdit');
        const skpdId = $('#edit_skpd_id').val();
        clearFormErrors($form);

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: `{{ url('skpd') }}/${skpdId}`,
            type: "POST",
            data: $form.serialize(),
            success: function (res) {
                editModal.hide();
                table.ajax.reload(null, false); // Reload data tanpa reload browser
                showNotification('success', res.message || 'Data SKPD berhasil diperbarui!');
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showFormErrors($form, xhr.responseJSON.errors);
                } else {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memperbarui SKPD.';
                    showFormErrors($form, { general: [msg] });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });

    // 4. Buka Modal Hapus
    $(document).on('click', '.delete-skpd-btn', function () {
        const id = $(this).data('id');
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');

        $('#delete_skpd_id').val(id);
        $('#delete_skpd_kode').text(kode);
        $('#delete_skpd_nama').text(nama);

        deleteModal.show();
    });

    // 5. AJAX: HAPUS SKPD
    $('#deleteSkpdForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btnSubmitDelete');
        const skpdId = $('#delete_skpd_id').val();

        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: `{{ url('skpd') }}/${skpdId}`,
            type: "POST",
            data: $form.serialize(),
            success: function (res) {
                deleteModal.hide();
                table.ajax.reload(null, false); // Reload tabel tanpa reload browser
                showNotification('success', res.message || 'SKPD berhasil dihapus!');
            },
            error: function (xhr) {
                deleteModal.hide();
                const msg = xhr.responseJSON?.message || 'Gagal menghapus SKPD.';
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
