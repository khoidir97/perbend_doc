@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen User')

@section('page-style')
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/dataTables.bootstrap5.min.css')}}" />
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
                    <h4 class="mb-1 text-primary"><i class="icon-base bx bx-user me-2"></i>Manajemen User</h4>
                    <p class="text-muted mb-0">Kelola data pengguna aplikasi dan penugasan SKPD / OPD (Server-Side DataTable
                        & AJAX).</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah User
                    </button>
                </div>
            </div>

            <!-- Alert Container (Dikelola oleh AJAX) -->
            <div id="ajaxAlertContainer"></div>

            <!-- Card DataTable Users -->
            <div class="card shadow-sm">
                <div class="card-datatable table-responsive p-3">
                    <table id="usersTable" class="table table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama / Username</th>
                                <th>Email</th>
                                <th style="width: 90px;">Role</th>
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

    <!-- Modal Tambah User -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-base bx bx-user-plus me-1"></i> Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createUserForm">
                    @csrf
                    <div class="modal-body">
                        <!-- Alert Error Form -->
                        <div class="alert alert-danger d-none form-errors" role="alert">
                            <ul class="mb-0 ps-3"></ul>
                        </div>

                        <div class="mb-3">
                            <label for="create_name" class="form-label">Nama Pengguna (Username) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name"
                                placeholder="Contoh: Administrator atau budi" required>
                        </div>
                        <div class="mb-3">
                            <label for="create_email" class="form-label">Alamat Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email"
                                placeholder="user@bpkad.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="create_password" class="form-label">Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password" name="password"
                                placeholder="Minimal 5 karakter" required>
                        </div>
                        <div class="mb-3">
                            <label for="create_role" class="form-label">Role Pengguna <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="create_role" name="role" required>
                                <option value="0">Admin</option>
                                <option value="1">OPD</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="create_kd_skpd" class="form-label">SKPD / OPD</label>
                            <x-select2 id="create_kd_skpd" name="kd_skpd" :options="$skpdList"
                                value-field="opd_kode" label-field="opd_nama" code-field="opd_kode"
                                placeholder="-- Tidak Memilih SKPD (Semua / Administrator) --"
                                search-placeholder="Ketik kode atau nama SKPD..."
                                no-results="SKPD tidak ditemukan" />
                            <small class="text-muted">Pilih unit kerja SKPD dari relasi `users.kd_skpd =
                                skpd.opd_kode`.</small>
                        </div>
                        <div class="mb-3">
                            <label for="create_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                            <select class="form-select" id="create_kd_satker" name="kd_satker"
                                data-searchable-select
                                data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                data-search-placeholder="Ketik nama atau kode satker..."
                                data-no-results="Satker tidak ditemukan">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD yang dipilih (opsional).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitCreate">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"
                                aria-hidden="true"></span>
                            <i class="icon-base bx bx-save me-1"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-base bx bx-edit me-1"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="modal-body">
                        <!-- Alert Error Form -->
                        <div class="alert alert-danger d-none form-errors" role="alert">
                            <ul class="mb-0 ps-3"></ul>
                        </div>

                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nama Pengguna (Username) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Alamat Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password Baru <small class="text-muted">(Kosongkan
                                    jika tidak ingin mengubah)</small></label>
                            <input type="password" class="form-control" id="edit_password" name="password"
                                placeholder="Masukkan password baru jika ingin diganti">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role Pengguna <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="0">Admin</option>
                                <option value="1">OPD</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_kd_skpd" class="form-label">SKPD / OPD</label>
                            <x-select2 id="edit_kd_skpd" name="kd_skpd" :options="$skpdList"
                                value-field="opd_kode" label-field="opd_nama" code-field="opd_kode"
                                placeholder="-- Tidak Memilih SKPD (Semua / Administrator) --"
                                search-placeholder="Ketik kode atau nama SKPD..."
                                no-results="SKPD tidak ditemukan" />
                            <small class="text-muted">Pilih unit kerja SKPD dari relasi `users.kd_skpd =
                                skpd.opd_kode`.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                            <select class="form-select" id="edit_kd_satker" name="kd_satker"
                                data-searchable-select
                                data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                data-search-placeholder="Ketik nama atau kode satker..."
                                data-no-results="Satker tidak ditemukan">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD yang dipilih (opsional).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitEdit">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"
                                aria-hidden="true"></span>
                            <i class="icon-base bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus User -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteUserForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="delete_user_id" name="id">
                    <div class="modal-body text-center py-4">
                        <i class="icon-base bx bx-error-circle text-danger fs-1 mb-2"></i>
                        <p class="mb-0">Apakah Anda yakin ingin menghapus user <strong id="delete_user_name"
                                class="text-dark"></strong>?</p>
                        <small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="btnSubmitDelete">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"
                                aria-hidden="true"></span>
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
    <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>
    <!-- DataTables JS & Bootstrap 5 Integration -->
    <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/dataTables.bootstrap5.min.js')}}"></script>
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

            // Inisialisasi Server-Side DataTable
            const table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.index') }}",
                    type: "GET",
                    error: function (xhr, error, thrown) {
                        console.error("DataTable Error:", xhr.responseText);
                    }
                },
                columns: [
                    { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    { data: 'skpd', name: 'kd_skpd' },
                    { data: 'satker', name: 'kd_satker' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari nama, email, SKPD...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ user",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada user yang cocok",
                    emptyTable: "Belum ada data user",
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
            const createModal = new bootstrap.Modal(document.getElementById('createUserModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

            // Helper AJAX Chained Dropdown SKPD -> Satker pada User Modal
            function loadUserSatkerOptions($selectSatker, kdSkpd, selectedSatkerKode = null) {
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

            // Event saat SKPD berubah di Form Tambah User
            $('#create_kd_skpd').on('change', function () {
                const kdSkpd = $(this).val();
                loadUserSatkerOptions($('#create_kd_satker'), kdSkpd);
            });

            // Event saat SKPD berubah di Form Edit User
            $('#edit_kd_skpd').on('change', function () {
                const kdSkpd = $(this).val();
                loadUserSatkerOptions($('#edit_kd_satker'), kdSkpd);
            });

            // Reset create form saat modal dibuka
            $('#createUserModal').on('show.bs.modal', function () {
                $('#createUserForm')[0].reset();
                SearchableSelect.reset('#create_kd_skpd');
                loadUserSatkerOptions($('#create_kd_satker'), null);
                clearFormErrors($('#createUserForm'));
            });

            // 1. AJAX: SIMPAN USER BARU
            $('#createUserForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitCreate');
                clearFormErrors($form);

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: "{{ route('users.store') }}",
                    type: "POST",
                    data: $form.serialize(),
                    success: function (res) {
                        createModal.hide();
                        $form[0].reset();
                        SearchableSelect.reset('#create_kd_skpd');
                        loadUserSatkerOptions($('#create_kd_satker'), null);
                        table.ajax.reload(null, false); // Reload tabel tanpa reset halaman
                        showNotification('success', res.message || 'User berhasil ditambahkan!');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan user.';
                            showFormErrors($form, { general: [msg] });
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                    }
                });
            });

            // 2. Buka Modal Edit & Isi Data
            $(document).on('click', '.edit-user-btn', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const email = $(this).data('email');
                const role = $(this).data('role');
                const kdSkpd = $(this).data('kd_skpd');
                const kdSatker = $(this).data('kd_satker');

                clearFormErrors($('#editUserForm'));
                $('#edit_user_id').val(id);
                $('#edit_name').val(name);
                $('#edit_email').val(email);
                $('#edit_password').val('');
                $('#edit_role').val(role !== undefined && role !== null ? role : '0');
                SearchableSelect.setValue('#edit_kd_skpd', kdSkpd);
                loadUserSatkerOptions($('#edit_kd_satker'), kdSkpd, kdSatker);

                editModal.show();
            });

            // 3. AJAX: UPDATE DATA USER
            $('#editUserForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitEdit');
                const userId = $('#edit_user_id').val();
                clearFormErrors($form);

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: `{{ url('users') }}/${userId}`,
                    type: "POST",
                    data: $form.serialize(),
                    success: function (res) {
                        editModal.hide();
                        table.ajax.reload(null, false); // Reload data tanpa reload browser
                        showNotification('success', res.message || 'Data user berhasil diperbarui!');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memperbarui user.';
                            showFormErrors($form, { general: [msg] });
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                    }
                });
            });

            // 4. Buka Modal Hapus
            $(document).on('click', '.delete-user-btn', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#delete_user_id').val(id);
                $('#delete_user_name').text(name);

                deleteModal.show();
            });

            // 5. AJAX: HAPUS USER
            $('#deleteUserForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitDelete');
                const userId = $('#delete_user_id').val();

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: `{{ url('users') }}/${userId}`,
                    type: "POST",
                    data: $form.serialize(),
                    success: function (res) {
                        deleteModal.hide();
                        table.ajax.reload(null, false); // Reload tabel tanpa reload browser
                        showNotification('success', res.message || 'User berhasil dihapus!');
                    },
                    error: function (xhr) {
                        deleteModal.hide();
                        const msg = xhr.responseJSON?.message || 'Gagal menghapus user.';
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
