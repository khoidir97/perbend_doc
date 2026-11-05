@extends('layouts/contentNavbarLayout')

@section('title', 'Data Dokumen')

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
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h4 class="mb-1 text-primary"><i class="icon-base bx bx-file me-2"></i>Data Dokumen SKPD</h4>
                    <p class="text-muted mb-0">
                        @if ($isAdmin)
                            Kelola dan verifikasi berkas dokumen PDF yang diunggah oleh setiap SKPD / OPD.
                        @else
                            Unggah dan pantau status verifikasi berkas dokumen PDF SKPD Anda.
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createDokumenModal">
                        <i class="icon-base bx bx-cloud-upload me-1"></i> Unggah Dokumen PDF
                    </button>
                </div>
            </div>

            <!-- Info Box untuk User SKPD -->
            @if (!$isAdmin)
                <div class="alert alert-primary d-flex align-items-center" role="alert">
                    <i class="icon-base bx bx-info-circle fs-4 me-2"></i>
                    <div>
                        Anda masuk sebagai <strong>{{ $currentUser->name }}</strong>
                        (SKPD: <strong>{{ $currentUser->skpd->opd_nama ?? 'Belum terhubung' }}
                            [{{ $currentUser->kd_skpd ?? '-' }}]</strong>).
                        Anda hanya melihat dokumen yang diunggah oleh SKPD Anda.
                    </div>
                </div>
            @endif

            <!-- Toast Container (Pojok kanan atas layar dengan jarak aman) -->
            <div class="toast-container position-fixed top-0 end-0 p-3"
                style="z-index: 1090; margin-top: 1rem; margin-right: 1.5rem;" id="toastPlacementContainer">
                <div id="ajaxLiveToast" class="bs-toast toast fade shadow-lg" role="alert" aria-live="assertive"
                    aria-atomic="true">
                    <div class="toast-header">
                        <i class="icon-base bx bx-bell me-2" id="toastIcon"></i>
                        <div class="me-auto fw-semibold" id="toastTitle">Pemberitahuan</div>
                        <small class="text-muted">baru saja</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="toastBody">
                    </div>
                </div>
            </div>

            <!-- Card DataTable Dokumen -->
            <div class="card shadow-sm">
                <!-- Filter Status Bar -->
                <div
                    class="card-header border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title mb-0">Daftar Dokumen</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 text-muted small"><i
                                class="icon-base bx bx-filter-alt me-1"></i>Filter Status:</label>
                        <select id="filter_status" class="form-select form-select-sm" style="width: 170px;">
                            <option value="">Semua Status</option>
                            <option value="0">Belum Verifikasi</option>
                            <option value="1">Sudah Verifikasi</option>
                        </select>
                    </div>
                </div>

                <div class="card-datatable table-responsive p-3">
                    <table id="dokumenTable" class="table table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>SKPD / OPD</th>
                                <th>Pegawai</th>
                                <th>File Dokumen (PDF)</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 130px;">Tgl Upload</th>
                                <th style="width: 130px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Unggah Dokumen -->
    <div class="modal fade" id="createDokumenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-base bx bx-cloud-upload me-1"></i> Unggah Dokumen PDF Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createDokumenForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Alert Error Form -->
                        <div class="alert alert-danger d-none form-errors" role="alert">
                            <ul class="mb-0 ps-3"></ul>
                        </div>

                        @if ($isAdmin)
                            <div class="mb-3">
                                <label for="create_dok_skpd" class="form-label">SKPD / OPD Pemilik Dokumen <span
                                        class="text-danger">*</span></label>
                                <x-select2 id="create_dok_skpd" name="dok_skpd" :options="$skpdList" value-field="opd_kode"
                                    label-field="opd_nama" code-field="opd_kode" placeholder="-- Cari / Pilih SKPD / OPD --"
                                    search-placeholder="Ketik kode atau nama SKPD..." no-results="SKPD tidak ditemukan"
                                    required />
                                <small class="text-muted">Ketik kode atau nama SKPD untuk mencari.</small>
                            </div>

                            <div class="mb-3">
                                <label for="create_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                <select class="form-select" id="create_dok_kd_satker" name="dok_kd_satker"
                                    data-searchable-select data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik nama atau kode satker..."
                                    data-no-results="Satker tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD.</small>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">SKPD Pengunggah</label>
                                <input type="text" class="form-control"
                                    value="[{{ $currentUser->kd_skpd }}] {{ $currentUser->skpd->opd_nama ?? 'SKPD Anda' }}"
                                    readonly disabled>
                                <small class="text-muted">Dokumen otomatis diasosiasikan ke SKPD Anda.</small>
                            </div>

                            @if (!empty($currentUser->kd_satker))
                                <div class="mb-3">
                                    <label class="form-label">Satuan Kerja (Satker)</label>
                                    <input type="text" class="form-control"
                                        value="[{{ trim($currentUser->kd_satker) }}] {{ $currentUser->satker->stk_nama ?? 'Satker Anda' }}"
                                        readonly disabled>
                                    <input type="hidden" name="dok_kd_satker" value="{{ trim($currentUser->kd_satker) }}">
                                    <small class="text-muted">Dokumen otomatis diasosiasikan ke Satker Anda.</small>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label for="create_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                    <x-select2 id="create_dok_kd_satker" name="dok_kd_satker" :options="$userSatkerList"
                                        value-field="stk_kode_satker" label-field="stk_nama" code-field="stk_kode_satker"
                                        :selected="optional($userSatkerList->first())->stk_kode_satker"
                                        placeholder="-- Pilih Satker (Opsional) --"
                                        search-placeholder="Ketik nama atau kode satker..." no-results="Satker tidak ditemukan" />
                                    <small class="text-muted">Pilih Satker di bawah naungan SKPD Anda (opsional).</small>
                                </div>
                            @endif
                        @endif

                        <div class="mb-3">
                            <label for="create_dok_pgw_id" class="form-label">Pegawai / ASN</label>
                            @if (!$isAdmin)
                                <x-select2 id="create_dok_pgw_id" name="dok_pgw_id" :options="$userPegawaiList"
                                    value-field="pgw_id" label-field="pgw_nama" code-field="pgw_nip"
                                    placeholder="-- Pilih Pegawai (Opsional) --"
                                    search-placeholder="Ketik NIP atau nama pegawai..." no-results="Pegawai tidak ditemukan" />
                            @else
                                <select class="form-select" id="create_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan">
                                    <option value=""></option>
                                </select>
                            @endif
                            <small class="text-muted">Pilih pegawai bersangkutan berdasarkan SKPD.</small>
                        </div>

                        <div class="mb-3">
                            <label for="create_dok_file" class="form-label">Pilih Berkas PDF <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="create_dok_file" name="dok_file"
                                accept=".pdf,application/pdf" required>
                            <small class="text-muted">Hanya berkas format <strong>.PDF</strong> dengan ukuran maksimal
                                <strong>2 MB</strong>.</small>
                        </div>

                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="icon-base bx bx-time me-1"></i> Status awal dokumen setelah diunggah adalah
                            <strong>Belum Verifikasi</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitCreate">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"
                                aria-hidden="true"></span>
                            <i class="icon-base bx bx-upload me-1"></i> Unggah Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokumen -->
    <div class="modal fade" id="editDokumenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-base bx bx-edit me-1"></i> Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDokumenForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_dok_id" name="id">
                    <div class="modal-body">
                        <!-- Alert Error Form -->
                        <div class="alert alert-danger d-none form-errors" role="alert">
                            <ul class="mb-0 ps-3"></ul>
                        </div>

                        @if ($isAdmin)
                            <div class="mb-3">
                                <label for="edit_dok_skpd" class="form-label">SKPD / OPD <span
                                        class="text-danger">*</span></label>
                                <x-select2 id="edit_dok_skpd" name="dok_skpd" :options="$skpdList" value-field="opd_kode"
                                    label-field="opd_nama" code-field="opd_kode" placeholder="-- Cari / Pilih SKPD / OPD --"
                                    search-placeholder="Ketik kode atau nama SKPD..." no-results="SKPD tidak ditemukan"
                                    required />
                                <small class="text-muted">Ketik kode atau nama SKPD untuk mencari.</small>
                            </div>

                            <div class="mb-3">
                                <label for="edit_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                <select class="form-select" id="edit_dok_kd_satker" name="dok_kd_satker" data-searchable-select
                                    data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik nama atau kode satker..."
                                    data-no-results="Satker tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilihan Satker disaring otomatis berdasarkan SKPD.</small>
                            </div>
                        @else
                            @if (!empty($currentUser->kd_satker))
                                <input type="hidden" name="dok_kd_satker" value="{{ trim($currentUser->kd_satker) }}">
                            @else
                                <div class="mb-3">
                                    <label for="edit_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                    <x-select2 id="edit_dok_kd_satker" name="dok_kd_satker" :options="$userSatkerList"
                                        value-field="stk_kode_satker" label-field="stk_nama" code-field="stk_kode_satker"
                                        placeholder="-- Pilih Satker (Opsional) --"
                                        search-placeholder="Ketik nama atau kode satker..." no-results="Satker tidak ditemukan" />
                                    <small class="text-muted">Pilih Satker di bawah naungan SKPD Anda (opsional).</small>
                                </div>
                            @endif
                        @endif

                        <div class="mb-3">
                            <label for="edit_dok_pgw_id" class="form-label">Pegawai / ASN</label>
                            @if (!$isAdmin)
                                <x-select2 id="edit_dok_pgw_id" name="dok_pgw_id" :options="$userPegawaiList"
                                    value-field="pgw_id" label-field="pgw_nama" code-field="pgw_nip"
                                    placeholder="-- Pilih Pegawai (Opsional) --"
                                    search-placeholder="Ketik NIP atau nama pegawai..." no-results="Pegawai tidak ditemukan" />
                            @else
                                <select class="form-select" id="edit_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan">
                                    <option value=""></option>
                                </select>
                            @endif
                            <small class="text-muted">Pilih pegawai bersangkutan berdasarkan SKPD.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File PDF Saat Ini:</label>
                            <div class="p-2 border rounded bg-light">
                                <i class="icon-base bx bxs-file-pdf text-danger me-1"></i>
                                <span id="current_file_name" class="fw-semibold text-break"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_dok_file" class="form-label">Ganti File PDF <small
                                    class="text-muted">(Kosongkan jika tidak ingin mengganti file)</small></label>
                            <input type="file" class="form-control" id="edit_dok_file" name="dok_file"
                                accept=".pdf,application/pdf">
                            <small class="text-muted">Format <strong>.PDF</strong>, maksimal <strong>2 MB</strong>.</small>
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

    <!-- Modal Konfirmasi Hapus Dokumen -->
    <div class="modal fade" id="deleteDokumenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteDokumenForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="delete_dok_id" name="id">
                    <div class="modal-body text-center py-4">
                        <i class="icon-base bx bx-error-circle text-danger fs-1 mb-2"></i>
                        <p class="mb-1">Apakah Anda yakin ingin menghapus berkas dokumen ini?</p>
                        <small id="delete_dok_name" class="fw-bold text-dark text-break d-block"></small>
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
            // Inisialisasi Server-Side DataTable Dokumen
            const table = $('#dokumenTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dokumen.index') }}",
                    type: "GET",
                    data: function (d) {
                        d.status = $('#filter_status').val();
                    },
                    error: function (xhr, error, thrown) {
                        console.error("DataTable Error:", xhr.responseText);
                    }
                },
                columns: [
                    { data: 'no', name: 'no', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'skpd', name: 'dok_skpd' },
                    { data: 'pegawai', name: 'dok_pgw_id' },
                    { data: 'file', name: 'dok_file' },
                    { data: 'status', name: 'dok_status' },
                    { data: 'created_at', name: 'dok_created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari nama file, SKPD...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ dokumen",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada dokumen yang cocok",
                    emptyTable: "Belum ada dokumen yang diunggah",
                    loadingRecords: "Memuat dokumen...",
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat dokumen...',
                    paginate: {
                        previous: '<i class="icon-base bx bx-chevron-left"></i>',
                        next: '<i class="icon-base bx bx-chevron-right"></i>'
                    }
                },
                dom: '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 text-md-end"f>>t<"row align-items-center mt-3"<"col-md-6"i><"col-md-6 text-md-end"p>>'
            });

            // Event Filter Status
            $('#filter_status').on('change', function () {
                table.ajax.reload();
            });

            // Helper Notifikasi Toast (Pojok kanan atas, auto-hide, z-index paling atas)
            let toastTimer = null;
            function showNotification(type, message) {
                const toastEl = document.getElementById('ajaxLiveToast');
                const toastTitle = document.getElementById('toastTitle');
                const toastIcon = document.getElementById('toastIcon');
                const toastBody = document.getElementById('toastBody');

                // Reset class background & text warna (tanpa class toast-placement-ex agar tidak terpotong)
                toastEl.className = 'bs-toast toast fade shadow-lg';

                if (type === 'success') {
                    toastEl.classList.add('bg-success');
                    toastTitle.textContent = 'Berhasil';
                    toastIcon.className = 'icon-base bx bx-check-circle me-2 text-white';
                } else if (type === 'danger' || type === 'error') {
                    toastEl.classList.add('bg-danger');
                    toastTitle.textContent = 'Gagal / Perhatian';
                    toastIcon.className = 'icon-base bx bx-error-circle me-2 text-white';
                } else if (type === 'warning') {
                    toastEl.classList.add('bg-warning');
                    toastTitle.textContent = 'Peringatan';
                    toastIcon.className = 'icon-base bx bx-error me-2 text-white';
                } else {
                    toastEl.classList.add('bg-primary');
                    toastTitle.textContent = 'Informasi';
                    toastIcon.className = 'icon-base bx bx-bell me-2 text-white';
                }

                toastBody.textContent = message;

                // Inisialisasi dan tampilkan Toast Bootstrap dengan durasi 3.5 detik
                const toastInstance = new bootstrap.Toast(toastEl, {
                    delay: 3500,
                    autohide: true
                });
                toastInstance.show();
            }

            // Helper Error Validasi Form
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

            // Modal Instances
            const createModal = new bootstrap.Modal(document.getElementById('createDokumenModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editDokumenModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteDokumenModal'));

            let isSettingEditValues = false;

            // Helper untuk memuat opsi Satker berdasarkan SKPD via AJAX
            function loadDokumenSatkerOptions($selectSatker, kdSkpd, selectedSatkerKode = null, selectedPgwId = null) {
                if (!$selectSatker.length) return;
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

                        let targetSelected = selectedSatkerKode;

                        if (data && data.length > 0) {
                            $selectSatker.attr('data-placeholder', '-- Pilih Satker (Opsional) --');
                            
                            // Jika belum ada yang terpilih (misal saat tambah baru), auto-select data satker yang pertama
                            if (!targetSelected) {
                                targetSelected = data[0].stk_kode_satker;
                            }

                            $.each(data, function (i, item) {
                                const $opt = $('<option>')
                                    .val(item.stk_kode_satker)
                                    .attr('data-code', item.stk_kode_satker)
                                    .attr('data-label', item.stk_nama)
                                    .text((item.stk_kode_satker ? '[' + item.stk_kode_satker + '] ' : '') + item.stk_nama);

                                if (targetSelected && String(targetSelected) === String(item.stk_kode_satker)) {
                                    $opt.prop('selected', true);
                                }
                                $selectSatker.append($opt);
                            });
                        } else {
                            $selectSatker.attr('data-placeholder', '-- Tidak ada Satker di SKPD ini --');
                        }

                        SearchableSelect.init($selectSatker);
                        if (targetSelected) {
                            SearchableSelect.setValue($selectSatker, targetSelected);
                            // Picu pembaruan daftar pegawai sesuai satker yang auto-terpilih jika tidak dalam mode pengisian modal edit manual
                            const $form = $selectSatker.closest('form');
                            const $pgwSelect = $form.find('select[name="dok_pgw_id"]');
                            if ($pgwSelect.length && !isSettingEditValues) {
                                loadPegawaiOptions($pgwSelect, kdSkpd, targetSelected, selectedPgwId);
                            }
                        }
                    },
                    error: function () {
                        SearchableSelect.destroy($selectSatker);
                        $selectSatker.attr('data-placeholder', '-- Gagal memuat data Satker --');
                        SearchableSelect.init($selectSatker);
                    }
                });
            }

            // Helper untuk memuat opsi Pegawai berdasarkan SKPD dan Satker via AJAX
            function loadPegawaiOptions($selectPgw, kdSkpd, kdSatker = null, selectedPgwId = null) {
                // Hancurkan Select2 sementara jika sudah terpasang
                SearchableSelect.destroy($selectPgw);

                $selectPgw.empty().append('<option value=""></option>');

                if (!kdSkpd) {
                    $selectPgw.attr('data-placeholder', '-- Pilih SKPD terlebih dahulu --');
                    SearchableSelect.init($selectPgw);
                    return;
                }

                $selectPgw.attr('data-placeholder', '-- Memuat Pegawai... --');
                SearchableSelect.init($selectPgw);

                $.ajax({
                    url: "{{ route('dokumen.pegawai-by-skpd') }}",
                    type: "GET",
                    data: { 
                        kd_skpd: kdSkpd,
                        kd_satker: kdSatker
                    },
                    success: function (data) {
                        SearchableSelect.destroy($selectPgw);
                        $selectPgw.empty().append('<option value=""></option>');

                        if (data && data.length > 0) {
                            $selectPgw.attr('data-placeholder', '-- Pilih Pegawai (Opsional) --');
                            $.each(data, function (i, pgw) {
                                const $opt = $('<option>')
                                    .val(pgw.pgw_id)
                                    .attr('data-code', pgw.pgw_nip || '')
                                    .attr('data-label', pgw.pgw_nama)
                                    .text((pgw.pgw_nip ? '[' + pgw.pgw_nip + '] ' : '') + pgw.pgw_nama);
                                if (selectedPgwId && String(selectedPgwId) === String(pgw.pgw_id)) {
                                    $opt.prop('selected', true);
                                }
                                $selectPgw.append($opt);
                            });
                        } else {
                            $selectPgw.attr('data-placeholder', '-- Tidak ada pegawai di unit ini --');
                        }

                        SearchableSelect.init($selectPgw);
                        if (selectedPgwId) {
                            SearchableSelect.setValue($selectPgw, selectedPgwId);
                        }
                    },
                    error: function () {
                        SearchableSelect.destroy($selectPgw);
                        $selectPgw.attr('data-placeholder', '-- Gagal memuat pegawai --');
                        SearchableSelect.init($selectPgw);
                    }
                });
            }

            // Khusus Admin: Event ketika SKPD di modal tambah berubah
            $('#create_dok_skpd').on('change', function () {
                const kdSkpd = $(this).val();
                loadDokumenSatkerOptions($('#create_dok_kd_satker'), kdSkpd);
                loadPegawaiOptions($('#create_dok_pgw_id'), kdSkpd, $('#create_dok_kd_satker').val());
            });

            // Event ketika Satker di modal tambah berubah (Admin maupun OPD)
            $('#create_dok_kd_satker').on('change', function () {
                const kdSatker = $(this).val();
                const kdSkpd = $('#create_dok_skpd').length ? $('#create_dok_skpd').val() : '{{ $currentUser->kd_skpd ?? "" }}';
                loadPegawaiOptions($('#create_dok_pgw_id'), kdSkpd, kdSatker);
            });

            // Khusus Admin: Event ketika SKPD di modal edit berubah
            $('#edit_dok_skpd').on('change', function () {
                if (isSettingEditValues) return;
                const kdSkpd = $(this).val();
                loadDokumenSatkerOptions($('#edit_dok_kd_satker'), kdSkpd);
                loadPegawaiOptions($('#edit_dok_pgw_id'), kdSkpd, $('#edit_dok_kd_satker').val());
            });

            // Event ketika Satker di modal edit berubah (Admin maupun OPD)
            $('#edit_dok_kd_satker').on('change', function () {
                if (isSettingEditValues) return;
                const kdSatker = $(this).val();
                const kdSkpd = $('#edit_dok_skpd').length ? $('#edit_dok_skpd').val() : '{{ $currentUser->kd_skpd ?? "" }}';
                loadPegawaiOptions($('#edit_dok_pgw_id'), kdSkpd, kdSatker);
            });

            $('#createDokumenModal').on('show.bs.modal', function () {
                $('#createDokumenForm')[0].reset();
                if ($('#create_dok_skpd').length) {
                    SearchableSelect.reset('#create_dok_skpd');
                    loadDokumenSatkerOptions($('#create_dok_kd_satker'), null);
                    loadPegawaiOptions($('#create_dok_pgw_id'), null);
                } else {
                    if ($('#create_dok_kd_satker').length) {
                        const defaultSatker = $('#create_dok_kd_satker option[data-code]:first').val();
                        if (defaultSatker) {
                            SearchableSelect.setValue('#create_dok_kd_satker', defaultSatker);
                        } else {
                            SearchableSelect.reset('#create_dok_kd_satker');
                        }
                    }
                    SearchableSelect.reset('#create_dok_pgw_id');
                }
                clearFormErrors($('#createDokumenForm'));
            });

            // 1. AJAX: UNGGAH DOKUMEN (FormData untuk upload file)
            $('#createDokumenForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitCreate');
                clearFormErrors($form);

                const formData = new FormData(this);

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: "{{ route('dokumen.store') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        createModal.hide();
                        $form[0].reset();
                        if ($('#create_dok_skpd').length) {
                            SearchableSelect.reset('#create_dok_skpd');
                            loadDokumenSatkerOptions($('#create_dok_kd_satker'), null);
                            loadPegawaiOptions($('#create_dok_pgw_id'), null);
                        } else {
                            if ($('#create_dok_kd_satker').length) {
                                SearchableSelect.reset('#create_dok_kd_satker');
                            }
                            SearchableSelect.reset('#create_dok_pgw_id');
                        }
                        table.ajax.reload(null, false);
                        showNotification('success', res.message || 'Dokumen berhasil diunggah!');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengunggah dokumen.';
                            showFormErrors($form, { general: [msg] });
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                    }
                });
            });

            // 2. Buka Modal Edit & Isi Data
            $(document).on('click', '.edit-dokumen-btn', function () {
                const id = $(this).data('id');
                const skpd = $(this).data('skpd');
                const kdSatker = $(this).data('kd-satker');
                const pgwId = $(this).data('pgw-id');
                const fileName = $(this).data('file');

                clearFormErrors($('#editDokumenForm'));
                $('#edit_dok_id').val(id);
                $('#current_file_name').text(fileName);
                $('#edit_dok_file').val('');

                isSettingEditValues = true;

                if ($('#edit_dok_skpd').length) {
                    SearchableSelect.setValue('#edit_dok_skpd', skpd);
                    loadDokumenSatkerOptions($('#edit_dok_kd_satker'), skpd, kdSatker, pgwId);
                    loadPegawaiOptions($('#edit_dok_pgw_id'), skpd, kdSatker, pgwId);
                } else {
                    if ($('#edit_dok_kd_satker').length) {
                        SearchableSelect.setValue('#edit_dok_kd_satker', kdSatker);
                    }
                    const userKdSkpd = '{{ $currentUser->kd_skpd ?? "" }}';
                    const effectiveSatker = kdSatker || '{{ $currentUser->kd_satker ?? "" }}';
                    loadPegawaiOptions($('#edit_dok_pgw_id'), userKdSkpd, effectiveSatker, pgwId);
                }

                setTimeout(function () {
                    isSettingEditValues = false;
                }, 400);

                editModal.show();
            });

            // 3. AJAX: UPDATE DOKUMEN (Ganti File / SKPD)
            $('#editDokumenForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitEdit');
                const dokId = $('#edit_dok_id').val();
                clearFormErrors($form);

                const formData = new FormData(this);
                // Laravel PUT method spoofing untuk file upload
                formData.append('_method', 'PUT');

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: `{{ url('dokumen') }}/${dokId}`,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        editModal.hide();
                        table.ajax.reload(null, false);
                        showNotification('success', res.message || 'Dokumen berhasil diperbarui!');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui dokumen.';
                            showFormErrors($form, { general: [msg] });
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                    }
                });
            });

            // 4. AJAX: UBAH STATUS VERIFIKASI (Khusus Admin)
            $(document).on('click', '.btn-verify-status', function () {
                const dokId = $(this).data('id');
                const targetStatus = $(this).data('status');
                const $btn = $(this);

                $btn.prop('disabled', true);

                $.ajax({
                    url: `{{ url('dokumen') }}/${dokId}/verifikasi`,
                    type: "PATCH",
                    data: { status: targetStatus },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        showNotification('success', res.message || 'Status verifikasi berhasil diperbarui!');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal mengubah status verifikasi.';
                        showNotification('danger', msg);
                        $btn.prop('disabled', false);
                    }
                });
            });

            // 5. Buka Modal Hapus
            $(document).on('click', '.delete-dokumen-btn', function () {
                const id = $(this).data('id');
                const fileName = $(this).data('name');

                $('#delete_dok_id').val(id);
                $('#delete_dok_name').text(fileName);

                deleteModal.show();
            });

            // 6. AJAX: HAPUS DOKUMEN
            $('#deleteDokumenForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSubmitDelete');
                const dokId = $('#delete_dok_id').val();

                $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

                $.ajax({
                    url: `{{ url('dokumen') }}/${dokId}`,
                    type: "POST",
                    data: $form.serialize(),
                    success: function (res) {
                        deleteModal.hide();
                        table.ajax.reload(null, false);
                        showNotification('success', res.message || 'Dokumen berhasil dihapus!');
                    },
                    error: function (xhr) {
                        deleteModal.hide();
                        const msg = xhr.responseJSON?.message || 'Gagal menghapus dokumen.';
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