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

            <!-- Toast Container (Pojok kanan atas layar dengan z-index tertinggi agar di atas modal) -->
            <div class="toast-container position-fixed top-0 end-0 p-3"
                style="z-index: 99999 !important; margin-top: 1rem; margin-right: 1.5rem;" id="toastPlacementContainer">
                <div id="ajaxLiveToast" class="bs-toast toast fade shadow-lg" role="alert" aria-live="assertive"
                    aria-atomic="true" style="z-index: 99999 !important;">
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
                                <th style="width: 165px;" class="text-center">Aksi</th>
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

                            <div class="mb-3">
                                <label for="create_dok_pgw_id" class="form-label">Pegawai / ASN</label>
                                <select class="form-select" id="create_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilih pegawai bersangkutan berdasarkan SKPD.</small>
                            </div>
                        @elseif ($isOpd)
                            {{-- Form Khusus OPD: SKPD otomatis OPD user, dapat memilih Satker & Pegawai di OPD-nya --}}
                            <div class="mb-3">
                                <label class="form-label">SKPD / OPD Pengunggah</label>
                                <input type="text" class="form-control bg-light"
                                    value="[{{ $currentUser->kd_skpd }}] {{ $currentUser->skpd->opd_nama ?? 'SKPD Anda' }}"
                                    readonly>
                                <input type="hidden" id="create_dok_skpd" name="dok_skpd" value="{{ $currentUser->kd_skpd }}">
                                <small class="text-muted">Dokumen diasosiasikan ke instansi OPD Anda.</small>
                            </div>

                            <div class="mb-3">
                                <label for="create_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                <select class="form-select" id="create_dok_kd_satker" name="dok_kd_satker"
                                    data-searchable-select data-placeholder="-- Pilih Satker (Opsional) --"
                                    data-search-placeholder="Ketik nama atau kode satker..."
                                    data-no-results="Satker tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilihan Satker di lingkungan OPD Anda.</small>
                            </div>

                            <div class="mb-3">
                                <label for="create_dok_pgw_id" class="form-label">Pegawai / ASN <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="create_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Cari / Pilih Pegawai --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan" required>
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilih pegawai yang bersangkutan di lingkungan OPD Anda.</small>
                            </div>
                        @else
                            {{-- Form Otomatis untuk Pegawai yang Login --}}
                            <div class="mb-3">
                                <label class="form-label">SKPD Pengunggah</label>
                                <input type="text" class="form-control bg-light"
                                    value="[{{ $currentUser->kd_skpd }}] {{ $currentUser->skpd->opd_nama ?? 'SKPD Anda' }}"
                                    readonly>
                                <input type="hidden" name="dok_skpd" value="{{ $currentUser->kd_skpd }}">
                                <small class="text-muted">Dokumen otomatis diasosiasikan ke SKPD Anda.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Satuan Kerja (Satker)</label>
                                @if (!empty($currentUser->kd_satker))
                                    <input type="text" class="form-control bg-light"
                                        value="[{{ trim($currentUser->kd_satker) }}] {{ $currentUser->satker->stk_nama ?? 'Satker Anda' }}"
                                        readonly>
                                    <input type="hidden" name="dok_kd_satker" value="{{ trim($currentUser->kd_satker) }}">
                                    <small class="text-muted">Dokumen otomatis diasosiasikan ke Satker Anda.</small>
                                @else
                                    <input type="text" class="form-control bg-light" value="Kantor Induk SKPD (Tanpa Satker)"
                                        readonly>
                                    <small class="text-muted">Dokumen otomatis diasosiasikan ke kantor induk SKPD Anda.</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pegawai / Pemilik Dokumen</label>
                                <input type="text" class="form-control bg-light"
                                    value="[{{ $currentUser->pgw_nip ?? $currentUser->nip }}] {{ $currentUser->pgw_nama ?? $currentUser->name }}"
                                    readonly>
                                <input type="hidden" name="dok_pgw_id" value="{{ $currentUser->pgw_id ?? $currentUser->id }}">
                                <small class="text-muted">Dokumen otomatis diasosiasikan ke akun Anda.</small>
                            </div>
                        @endif

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

                            <div class="mb-3">
                                <label for="edit_dok_pgw_id" class="form-label">Pegawai / ASN</label>
                                <select class="form-select" id="edit_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Pilih SKPD terlebih dahulu --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilih pegawai bersangkutan berdasarkan SKPD.</small>
                            </div>
                        @elseif ($isOpd)
                            {{-- Form Edit Khusus OPD: SKPD terkunci ke OPD user, dapat mengubah Satker & Pegawai di OPD-nya
                            --}}
                            <div class="mb-3">
                                <label class="form-label">SKPD / OPD</label>
                                <input type="text" class="form-control bg-light"
                                    value="[{{ $currentUser->kd_skpd }}] {{ $currentUser->skpd->opd_nama ?? 'SKPD Anda' }}"
                                    readonly>
                                <input type="hidden" id="edit_dok_skpd" name="dok_skpd" value="{{ $currentUser->kd_skpd }}">
                                <small class="text-muted">Instansi OPD pemilik dokumen.</small>
                            </div>

                            <div class="mb-3">
                                <label for="edit_dok_kd_satker" class="form-label">Satuan Kerja (Satker)</label>
                                <select class="form-select" id="edit_dok_kd_satker" name="dok_kd_satker" data-searchable-select
                                    data-placeholder="-- Pilih Satker (Opsional) --"
                                    data-search-placeholder="Ketik nama atau kode satker..."
                                    data-no-results="Satker tidak ditemukan">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilihan Satker di lingkungan OPD Anda.</small>
                            </div>

                            <div class="mb-3">
                                <label for="edit_dok_pgw_id" class="form-label">Pegawai / ASN <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_dok_pgw_id" name="dok_pgw_id" data-searchable-select
                                    data-placeholder="-- Cari / Pilih Pegawai --"
                                    data-search-placeholder="Ketik NIP atau nama pegawai..."
                                    data-no-results="Pegawai tidak ditemukan" required>
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Pilih pegawai yang bersangkutan di lingkungan OPD Anda.</small>
                            </div>
                        @else
                            <input type="hidden" name="dok_skpd" value="{{ $currentUser->kd_skpd }}">
                            @if (!empty($currentUser->kd_satker))
                                <input type="hidden" name="dok_kd_satker" value="{{ trim($currentUser->kd_satker) }}">
                            @endif
                            <input type="hidden" id="edit_dok_pgw_id" name="dok_pgw_id"
                                value="{{ $currentUser->pgw_id ?? $currentUser->id }}">
                        @endif

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

    <!-- Modal Extra Large: Pemeliharaan Data Keluarga (KP4 / Tunjangan) -->
    <div class="modal fade" id="modalDataKeluarga" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0">
                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div
                            class="avatar avatar-sm bg-white rounded p-1 text-primary d-flex align-items-center justify-content-center shadow-sm">
                            <i class="icon-base bx bx-group fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white mb-0 fw-semibold">Pemeliharaan Data Keluarga (KP4)</h5>
                            <small class="text-white-50">Pengelolaan rincian susunan keluarga dan tunjangan pegawai</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <!-- 1. Header Info Pegawai Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-3">
                                <div class="col-md-3">
                                    <label class="form-label text-muted small mb-1 fw-semibold"><i
                                            class="icon-base bx bx-id-card me-1 text-primary"></i>NIP Pegawai</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" id="kel_pegawai_nip"
                                            class="form-control form-control-sm fw-bold bg-white"
                                            placeholder="NIP Pegawai..." readonly />
                                        <button class="btn btn-sm btn-primary" type="button" id="btnCariNipPegawai"
                                            title="Cari Data Pegawai">
                                            <i class="icon-base bx bx-search-alt"></i> Cari
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1 fw-semibold"><i
                                            class="icon-base bx bx-user me-1 text-primary"></i>Nama Pegawai</label>
                                    <div class="fw-bold text-dark fs-6" id="kel_pegawai_nama">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small mb-1 fw-semibold"><i
                                            class="icon-base bx bx-buildings me-1 text-primary"></i>SKPD / Satker</label>
                                    <div class="text-secondary small fw-medium" id="kel_pegawai_skpd">-</div>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <label class="form-label text-muted small mb-1 fw-semibold"><i
                                            class="icon-base bx bx-time-five me-1 text-primary"></i>Tgl Update</label>
                                    <div>
                                        <span class="badge bg-label-secondary"
                                            id="kel_tgl_update">{{ date('d-m-Y H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-3">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">
                                    <i class="icon-base bx bx-list-ul me-1 text-primary"></i> Susunan Anggota Keluarga
                                    Pegawai
                                </h6>
                                <small class="text-muted">Klik pada salah satu baris untuk melihat / mengedit rincian
                                    data</small>
                            </div>
                            <span class="badge bg-label-info" id="countAnggotaKeluarga">7 Anggota Keluarga</span>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover table-striped mb-0 align-middle" id="tabelAnggotaKeluarga">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">Kd</th>
                                        <th>Nama Anggota Keluarga</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Hubungan</th>
                                        <th>Status Tunjangan</th>
                                        <th style="width: 90px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyAnggotaKeluarga">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            </div>
                                            Memuat data susunan keluarga dari database kedua...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Form Input / Edit Rincian Anggota Keluarga -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="icon-base bx bx-user-plus me-1"></i> Rincian Identitas Anggota Keluarga
                            </h6>
                            <span class="badge bg-label-primary px-3 py-1" id="badgeFormMode">Mode: Tambah / Edit</span>
                        </div>
                        <div class="card-body p-3">
                            <form id="formInputKeluarga" onsubmit="return false;">
                                <input type="hidden" id="kel_nip" name="NIP" value="" />
                                <input type="hidden" id="kel_old_kdhubkel" name="OLD_KDHUBKEL" value="" />
                                <input type="hidden" id="kel_dok_id" name="dok_id" value="" />
                                <input type="hidden" id="kel_pgw_id" name="pgw_id" value="" />
                                <input type="hidden" id="kel_kddati1" name="KDDATI1" value="04" />
                                <input type="hidden" id="kel_kddati2" name="KDDATI2" value="11" />

                                <!-- Baris 1: Hubungan Keluarga, Gelar Depan, Nama, Gelar Belakang -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label for="kel_hub_keluarga" class="form-label small fw-semibold">Hubungan Keluarga
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" id="kel_hub_keluarga" name="KDHUBKEL"
                                            data-searchable-select required>
                                            <option value="">-- Pilih Hubungan --</option>
                                            @if(isset($hubkelList) && count($hubkelList) > 0)
                                                @foreach($hubkelList as $hk)
                                                    @php
                                                        $kd = trim($hk->KDHUBKEL);
                                                        $label = $hk->NMHUBKEL;
                                                        $kdNum = is_numeric($kd) ? (int) $kd : 0;
                                                        if ($kdNum >= 11 && $kdNum <= 19) {
                                                            $anakKe = (int) substr($kd, 1);
                                                            $label = "ANAK KE {$anakKe}";
                                                        }
                                                    @endphp
                                                    <option value="{{ $kd }}" data-code="{{ $kd }}" data-label="{{ $label }}">
                                                        [{{ $kd }}] {{ $label }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kel_gelar_depan" class="form-label small fw-semibold">Gelar
                                            Depan</label>
                                        <input type="text" class="form-control form-control-sm" id="kel_gelar_depan"
                                            name="GLRDEPAN" placeholder="Contoh: dr., Drs." maxlength="20" />
                                    </div>
                                    <div class="col-md-5">
                                        <label for="kel_nama" class="form-label small fw-semibold">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm fw-semibold" id="kel_nama"
                                            name="NMKEL" placeholder="Masukkan nama lengkap anggota keluarga" maxlength="50"
                                            required />
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kel_gelar_belakang" class="form-label small fw-semibold">Gelar
                                            Belakang</label>
                                        <input type="text" class="form-control form-control-sm" id="kel_gelar_belakang"
                                            name="GLRBELAKANG" placeholder="Contoh: S.Kom, M.M." maxlength="20" />
                                    </div>
                                </div>

                                <!-- Baris 2: Tanggal Lahir, Jenis Kelamin, Status Kawin, Pekerjaan -->
                                <!-- Baris 2: Tanggal Lahir, No Akta Lahir, Jenis Kelamin, Status Kawin, Pekerjaan -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label for="kel_tgl_lahir" class="form-label small fw-semibold">Tanggal Lahir <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" id="kel_tgl_lahir"
                                            name="TGLLHR" required />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="kel_no_akta_lahir" class="form-label small fw-semibold">
                                            <i class="icon-base bx bx-certification me-1 text-info"></i>No. Akta Kelahiran
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="kel_no_akta_lahir"
                                            name="noaktalahir" placeholder="Contoh: 1234/DIS/2020" maxlength="100" />
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kel_jenis_kelamin" class="form-label small fw-semibold">Jenis Kelamin
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" id="kel_jenis_kelamin" name="KDJENKEL"
                                            required>
                                            <option value="">-- Pilih --</option>
                                            <option value="1">1 - Laki-laki</option>
                                            <option value="2">2 - Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kel_kdstawin" class="form-label small fw-semibold">Status Kawin
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" id="kel_kdstawin" name="kdstawin"
                                            required>
                                            <option value="1">1 - Tidak Kawin</option>
                                            <option value="2">2 - Kawin</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kel_pekerjaan" class="form-label small fw-semibold">Pekerjaan</label>
                                        <input type="text" class="form-control form-control-sm" id="kel_pekerjaan"
                                            name="PEKERJAAN" placeholder="PNS, Pelajar, dsb" maxlength="40" />
                                    </div>
                                </div>

                                <!-- Baris 3: Legalitas Pasangan & Status (Nikah, Cerai, Wafat) -->
                                <div class="border rounded p-3 mb-3 bg-white"
                                    style="border-left: 4px solid #696cff !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-bold text-primary">
                                            <i class="icon-base bx bx-heart me-1"></i> Data Pernikahan & Status Pasangan
                                        </span>
                                        <small class="text-danger fw-semibold">*) Lengkapi NIP jika Suami/Istri berstatus
                                            ASN</small>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label for="kel_nip_pasangan" class="form-label small fw-semibold">NIP Istri /
                                                Suami</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" id="kel_nip_pasangan"
                                                    name="NIPSUAMIISTRI" placeholder="Ketik NIP Pasangan ASN..."
                                                    maxlength="18" />
                                                <button class="btn btn-outline-primary" type="button"
                                                    id="btnCariNipPasangan">
                                                    <i class="icon-base bx bx-search me-1"></i> Cari NIP
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kel_tgl_cerai" class="form-label small fw-semibold">Tanggal
                                                Cerai</label>
                                            <input type="date" class="form-control form-control-sm" id="kel_tgl_cerai"
                                                name="TGLCERAI" />
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label for="kel_tgl_nikah" class="form-label small fw-semibold">Tanggal
                                                Nikah</label>
                                            <input type="date" class="form-control form-control-sm" id="kel_tgl_nikah"
                                                name="TGLNIKAH" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kel_no_surat_nikah" class="form-label small fw-semibold">
                                                <i class="icon-base bx bx-file me-1 text-primary"></i>Nomor Surat / Buku
                                                Nikah
                                            </label>
                                            <input type="text" class="form-control form-control-sm" id="kel_no_surat_nikah"
                                                name="nosuratnikah" placeholder="Contoh: No. Buku Nikah / Akta Nikah"
                                                maxlength="100" />
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label for="kel_tgl_wafat" class="form-label small fw-semibold">Tanggal
                                                Wafat</label>
                                            <input type="date" class="form-control form-control-sm" id="kel_tgl_wafat"
                                                name="TGLWAFAT" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kel_no_surat_kematian" class="form-label small fw-semibold">
                                                <i class="icon-base bx bx-file-blank me-1 text-danger"></i>Nomor Surat /
                                                Akta Kematian (NOSURAT)
                                            </label>
                                            <input type="text" class="form-control form-control-sm"
                                                id="kel_no_surat_kematian" name="nosuratkematian"
                                                placeholder="Contoh: No. Akta Kematian / Keterangan Wafat"
                                                maxlength="100" />
                                        </div>
                                    </div>

                                    <!-- Keterangan Khusus Pegawai Wafat (Tabel TWafat) -->
                                    <div id="section_twafat_pegawai" class="d-none mt-2 pt-2 border-top">
                                        <div
                                            class="d-flex align-items-center justify-content-between mb-2 p-2 rounded bg-label-danger">
                                            <span class="small fw-bold text-danger">
                                                <i class="icon-base bx bx-user-x me-1"></i> Rincian Kematian Pegawai (Tabel
                                                TWafat)
                                            </span>
                                            <span class="badge bg-danger">Khusus Pegawai (00)</span>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label for="twafat_tglsurat" class="form-label small fw-semibold">Tanggal
                                                    Surat Kematian (TGLSURAT)</label>
                                                <input type="date" class="form-control form-control-sm" id="twafat_tglsurat"
                                                    name="twafat_tglsurat" />
                                            </div>
                                            <div class="col-md-6">
                                                <label for="twafat_penerbit" class="form-label small fw-semibold">Instansi /
                                                    RS Penerbit Surat (PENERBIT)</label>
                                                <input type="text" class="form-control form-control-sm" id="twafat_penerbit"
                                                    name="twafat_penerbit" placeholder="Contoh: RS SYAFIRA / DISDUKCAPIL"
                                                    maxlength="40" />
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label for="twafat_jmlterusan" class="form-label small fw-semibold">Gaji
                                                    Terusan (JMLTERUSAN)</label>
                                                <select class="form-select form-select-sm" id="twafat_jmlterusan"
                                                    name="twafat_jmlterusan">
                                                    <option value="4" selected>4 Bulan (Standar)</option>
                                                    <option value="0">0 Bulan (Tanpa Terusan)</option>
                                                    <option value="1">1 Bulan</option>
                                                    <option value="2">2 Bulan</option>
                                                    <option value="3">3 Bulan</option>
                                                    <option value="5">5 Bulan</option>
                                                    <option value="6">6 Bulan</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="twafat_tglstop" class="form-label small fw-semibold">Tanggal
                                                    Stop Gaji (TGLSTOP)</label>
                                                <input type="date" class="form-control form-control-sm" id="twafat_tglstop"
                                                    name="twafat_tglstop" />
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Otomatis tgl 1
                                                    setelah masa terusan.</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="twafat_kdhubkelpenerima"
                                                    class="form-label small fw-semibold">Hubkel Penerima
                                                    (KDHUBKELPENERIMA)</label>
                                                <select class="form-select form-select-sm" id="twafat_kdhubkelpenerima"
                                                    name="twafat_kdhubkelpenerima">
                                                    <option value="">-- Pilih Hubkel --</option>
                                                    <option value="10">10 - ISTERI</option>
                                                    <option value="20">20 - SUAMI</option>
                                                    <option value="11">11 - ANAK KE-1</option>
                                                    <option value="12">12 - ANAK KE-2</option>
                                                    <option value="13">13 - ANAK KE-3</option>
                                                    <option value="14">14 - ANAK KE-4</option>
                                                    <option value="15">15 - ANAK KE-5</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Baris 4: Keterangan SKS Anak (NOSKS) -->
                                <div class="border rounded p-3 mb-3 bg-white"
                                    style="border-left: 4px solid #03c3ec !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-bold text-info">
                                            <i class="icon-base bx bx-book-reader me-1"></i> Surat Keterangan Aktif Kuliah
                                            (SKS) Anak
                                        </span>
                                        <span class="badge bg-label-info">SKS Kuliah</span>
                                    </div>
                                    <div class="row g-3 mb-2">
                                        <div class="col-md-4">
                                            <label for="kel_tgl_awal_sks" class="form-label small fw-semibold">Tanggal Awal
                                                Surat Aktif Kuliah (SKS)</label>
                                            <input type="date" class="form-control form-control-sm" id="kel_tgl_awal_sks"
                                                name="TGLSKS" />
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Wajib bagi anak
                                                usia 21–25 th yang masih kuliah.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="kel_tgl_akhir_sks" class="form-label small fw-semibold">Tanggal
                                                Akhir Surat Aktif Kuliah (SKS)</label>
                                            <input type="date" class="form-control form-control-sm bg-light"
                                                id="kel_tgl_akhir_sks" name="TATSKS" disabled readonly />
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Otomatis 1 tahun
                                                sejak tanggal awal.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="kel_no_surat_sks"
                                                class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1">
                                                <span>
                                                    <i class="icon-base bx bx-barcode me-1 text-info"></i>
                                                    <span>Nomor Surat Aktif Kuliah (NOSKS)</span>
                                                </span>
                                                <!-- <small class="fw-normal text-muted">(Khusus Surat Aktif Kuliah / Sekolah
                                                                                Anak)</small> -->
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i
                                                        class="icon-base bx bx-book-reader text-info"></i></span>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="kel_no_surat_sks" name="NOSKS"
                                                    placeholder="Contoh: 123/UN.1/AK/2026 atau No. Surat Keterangan Kampus"
                                                    maxlength="50" />
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                (khusus untuk anak kuliah usia 21–25 tahun).
                                            </small>
                                        </div>
                                    </div>

                                </div>

                                <!-- Baris 5: Pilihan Status Tunjangan & Toolbar Tombol Form -->
                                <div class="row align-items-center g-3 pt-2">
                                    <div class="col-md-7">
                                        <label class="form-label small fw-bold text-dark d-block mb-2">
                                            <i class="icon-base bx bx-money me-1 text-success"></i> Status Tunjangan
                                            Keluarga:
                                        </label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check custom-option-basic">
                                                <input class="form-check-input" type="radio" name="KDTUNJANG"
                                                    id="status_tidak_tertunjang" value="1" checked />
                                                <label class="form-check-label fw-semibold text-secondary small"
                                                    for="status_tidak_tertunjang">
                                                    <span class="badge bg-label-secondary me-1"><i
                                                            class="icon-base bx bx-x-circle"></i></span> 1 - TIDAK
                                                    TERTUNJANG
                                                </label>
                                            </div>
                                            <div class="form-check custom-option-basic">
                                                <input class="form-check-input" type="radio" name="KDTUNJANG"
                                                    id="status_tertunjang" value="2" />
                                                <label class="form-check-label fw-semibold text-success small"
                                                    for="status_tertunjang">
                                                    <span class="badge bg-label-success me-1"><i
                                                            class="icon-base bx bx-check-circle"></i></span> 2 - TERTUNJANG
                                                </label>
                                            </div>

                                            <div class="form-check custom-option-basic">
                                                <input class="form-check-input" type="radio" name="KDTUNJANG"
                                                    id="status_tercatat" value="3" />
                                                <label class="form-check-label fw-semibold text-info small"
                                                    for="status_tercatat">
                                                    <span class="badge bg-label-info me-1"><i
                                                            class="icon-base bx bx-bookmark"></i></span> 3 - TERCATAT
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-danger mt-1 d-none" id="keteranganTunjangLocked">
                                            <i class="icon-base bx bx-lock-alt me-1"></i>Status otomatis <strong>1 - TIDAK
                                                TERTUNJANG</strong> dan dikunci karena Tanggal Cerai / Wafat terisi.
                                        </small>
                                        <small class="text-warning mt-1 d-none" id="keteranganTunjangMaxAnak">
                                            <i class="icon-base bx bx-error-circle me-1"></i>Kuota tunjangan anak sudah
                                            mencapai batas maksimal (3 orang anak tertunjang). Pilihan status tunjangan
                                            dikunci ke <strong>1 - TIDAK TERTUNJANG</strong>.
                                        </small>
                                        <small class="text-warning mt-1 d-none" id="keteranganTunjangMax5">
                                            <i class="icon-base bx bx-error-circle me-1"></i>Kuota tunjangan keluarga sudah
                                            mencapai batas maksimal (5 orang tertunjang). Pilihan status tunjangan dikunci
                                            ke <strong>1 - TIDAK TERTUNJANG</strong>.
                                        </small>
                                        <small class="text-danger mt-1 d-none" id="keteranganTunjangUsiaAnak">
                                            <i class="icon-base bx bx-user-x me-1"></i><span id="textTunjangUsiaAnak">Anak
                                                usia 21 tahun ke atas tidak dapat ditunjang kecuali memiliki Surat Aktif
                                                Kuliah (SKS) maksimal 25 tahun.</span>
                                        </small>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="btnBaruKeluarga">
                                                <i class="icon-base bx bx-plus-circle me-1"></i> Baru
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary" id="btnSimpanKeluarga">
                                                <span class="spinner-border spinner-border-sm d-none me-1"
                                                    id="spinnerSimpanKeluarga" role="status"></span>
                                                <i class="icon-base bx bx-save me-1"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                id="btnHapusKeluarga">
                                                <span class="spinner-border spinner-border-sm d-none me-1"
                                                    id="spinnerHapusKeluarga" role="status"></span>
                                                <i class="icon-base bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 3. Tabel Rincian Anggota Keluarga -->

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between">
                    <div>
                        <small class="text-muted"><i class="icon-base bx bx-info-circle me-1"></i>Data keluarga disimpan ke
                            koneksi database sekunder.</small>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="icon-base bx bx-x me-1"></i> Tutup
                    </button>
                </div>
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

            // Helper Notifikasi Toast (Pojok kanan atas, auto-hide, z-index paling atas di atas modal)
            let toastTimer = null;
            function showNotification(type, message) {
                const toastContainer = document.getElementById('toastPlacementContainer');
                if (toastContainer && toastContainer.parentElement !== document.body) {
                    document.body.appendChild(toastContainer);
                }
                const toastEl = document.getElementById('ajaxLiveToast');
                const toastTitle = document.getElementById('toastTitle');
                const toastIcon = document.getElementById('toastIcon');
                const toastBody = document.getElementById('toastBody');

                // Reset class background & text warna (tanpa class toast-placement-ex agar tidak terpotong)
                toastEl.className = 'bs-toast toast fade shadow-lg';
                toastEl.style.zIndex = '99999';

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
            const keluargaModal = new bootstrap.Modal(document.getElementById('modalDataKeluarga'));

            let isSettingEditValues = false;

            // Helper untuk memuat opsi Satker berdasarkan SKPD via AJAX
            function loadDokumenSatkerOptions($selectSatker, kdSkpd, selectedSatkerKode = null, selectedPgwId = null) {
                if (!$selectSatker.length || !$selectSatker.is('select')) return;
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
                if (!$selectPgw || !$selectPgw.length || !$selectPgw.is('select')) return;
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
                if ($('#create_dok_skpd').is('select')) {
                    SearchableSelect.reset('#create_dok_skpd');
                    loadDokumenSatkerOptions($('#create_dok_kd_satker'), null);
                    loadPegawaiOptions($('#create_dok_pgw_id'), null);
                } else if (@json($isOpd)) {
                    const opdSkpd = $('#create_dok_skpd').val() || '{{ $currentUser->kd_skpd ?? "" }}';
                    loadDokumenSatkerOptions($('#create_dok_kd_satker'), opdSkpd);
                    loadPegawaiOptions($('#create_dok_pgw_id'), opdSkpd);
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
                        if ($('#create_dok_skpd').is('select')) {
                            SearchableSelect.reset('#create_dok_skpd');
                            loadDokumenSatkerOptions($('#create_dok_kd_satker'), null);
                            loadPegawaiOptions($('#create_dok_pgw_id'), null);
                        } else if (@json($isOpd)) {
                            const opdSkpd = $('#create_dok_skpd').val() || '{{ $currentUser->kd_skpd ?? "" }}';
                            loadDokumenSatkerOptions($('#create_dok_kd_satker'), opdSkpd);
                            loadPegawaiOptions($('#create_dok_pgw_id'), opdSkpd);
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

                if ($('#edit_dok_skpd').is('select')) {
                    SearchableSelect.setValue('#edit_dok_skpd', skpd);
                    loadDokumenSatkerOptions($('#edit_dok_kd_satker'), skpd, kdSatker, pgwId);
                    loadPegawaiOptions($('#edit_dok_pgw_id'), skpd, kdSatker, pgwId);
                } else if (@json($isOpd)) {
                    const opdSkpd = $('#edit_dok_skpd').val() || skpd || '{{ $currentUser->kd_skpd ?? "" }}';
                    loadDokumenSatkerOptions($('#edit_dok_kd_satker'), opdSkpd, kdSatker, pgwId);
                    loadPegawaiOptions($('#edit_dok_pgw_id'), opdSkpd, kdSatker, pgwId);
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

            // ========================================================
            // 7. MODAL EXTRA LARGE: DATA KELUARGA (DATABASE KEDUA)
            // ========================================================
            let currentKeluargaList = [];

            // Fungsi memuat referensi hubungan keluarga dari hubkel_tbl jika diperlukan
            function loadHubkelOptions(selectedVal = null) {
                const $select = $('#kel_hub_keluarga');
                if ($select.find('option').length > 3) {
                    if (selectedVal) $select.val(selectedVal);
                    return;
                }

                $.ajax({
                    url: "{{ route('keluarga.hubkel') }}",
                    type: "GET",
                    success: function (res) {
                        if (res.success && res.data && res.data.length > 0) {
                            $select.empty().append('<option value="">-- Pilih Hubungan --</option>');
                            $.each(res.data, function (i, item) {
                                const label = item.NMHUBKEL_LABEL || item.NMHUBKEL;
                                $select.append(`<option value="${item.KDHUBKEL}">${item.KDHUBKEL} - ${label}</option>`);
                            });
                            if (selectedVal) $select.val(selectedVal);
                        }
                    }
                });
            }

            // Fungsi memuat daftar keluarga dari tabel 'keluarga' (Database Kedua)
            function loadKeluargaData(nip) {
                const $tbody = $('#tbodyAnggotaKeluarga');
                const $count = $('#countAnggotaKeluarga');

                if (!nip || nip === '-') {
                    $tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="icon-base bx bx-info-circle me-1"></i>NIP pegawai belum dipilih.</td></tr>');
                    $count.text('0 Anggota');
                    return;
                }

                $tbody.html(`
                                                                            <tr>
                                                                                <td colspan="6" class="text-center py-4 text-muted">
                                                                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                                                    Memuat data susunan keluarga untuk NIP <strong>${nip}</strong> dari database kedua...
                                                                                </td>
                                                                            </tr>
                                                                        `);

                $.ajax({
                    url: `{{ url('keluarga/by-nip') }}/${encodeURIComponent(nip)}`,
                    type: "GET",
                    success: function (res) {
                        currentKeluargaList = res.data || [];
                        $count.text(`${currentKeluargaList.length} Anggota Keluarga`);

                        if (currentKeluargaList.length === 0) {
                            $tbody.html(`
                                                                                    <tr>
                                                                                        <td colspan="6" class="text-center py-4 text-muted">
                                                                                            <i class="icon-base bx bx-folder-open fs-3 d-block mb-1 text-secondary"></i>
                                                                                            Belum ada data anggota keluarga di database kedua untuk NIP ini.<br>
                                                                                            <small class="text-muted">Gunakan form di atas untuk menambahkan data baru.</small>
                                                                                        </td>
                                                                                    </tr>
                                                                                `);
                            return;
                        }

                        let html = '';
                        $.each(currentKeluargaList, function (i, item) {
                            const gelarD = item.GLRDEPAN ? item.GLRDEPAN + ' ' : '';
                            const gelarB = item.GLRBELAKANG ? ', ' + item.GLRBELAKANG : '';
                            const namaLengkap = gelarD + (item.NMKEL || '-') + gelarB;

                            // Format Tanggal Lahir (d-m-Y)
                            let tglLahirFmt = '-';
                            if (item.TGLLHR) {
                                const parts = item.TGLLHR.substring(0, 10).split('-');
                                if (parts.length === 3) {
                                    tglLahirFmt = `${parts[2]}-${parts[1]}-${parts[0]}`;
                                }
                            }

                            // Nama Hubungan (gunakan label_hubkel yang sudah diformat di controller)
                            const nmHubkel = item.label_hubkel || (item.hubkel ? item.hubkel.NMHUBKEL : item.KDHUBKEL);

                            // Badge Status Tunjangan (KDTUNJANG: 0 = TIDAK TERTUNJANG, 1 = TERTUNJANG, 2 = CALON TERTUNJANG, 3 = TERCATAT)
                            let statusBadge = '<span class="badge bg-label-secondary">TIDAK TERTUNJANG</span>';
                            if (item.KDTUNJANG == 2 || item.KDTUNJANG === '2') {
                                statusBadge = '<span class="badge bg-label-success"><i class="icon-base bx bx-check me-1"></i>TERTUNJANG</span>';
                            } else if (item.KDTUNJANG == 3 || item.KDTUNJANG === '3') {
                                statusBadge = '<span class="badge bg-label-info"><i class="icon-base bx bx-bookmark me-1"></i>TERCATA</span>';
                            }

                            html += `
                                                                                        <tr class="table-row-keluarga" data-index="${i}" style="cursor: pointer;">
                                                                                            <td><span class="badge bg-label-dark">${item.KDHUBKEL}</span></td>
                                                                                            <td>
                                                                                                <span class="fw-bold text-dark d-block">${namaLengkap}</span>
                                                                                                ${item.NIPSUAMIISTRI ? `<small class="text-muted"><i class="icon-base bx bx-id-card me-1"></i>Pasangan ASN: ${item.NIPSUAMIISTRI}</small>` : ''}
                                                                                            </td>
                                                                                            <td>${tglLahirFmt}</td>
                                                                                            <td><span class="badge bg-label-primary">${nmHubkel}</span></td>
                                                                                            <td>${statusBadge}</td>
                                                                                            <td class="text-center">
                                                                                                <div class="d-inline-flex gap-1">
                                                                                                    <button type="button" class="btn btn-xs btn-icon btn-outline-primary btn-pilih-keluarga" title="Edit Baris Ini">
                                                                                                        <i class="icon-base bx bx-edit-alt"></i>
                                                                                                    </button>
                                                                                                    <button type="button" class="btn btn-xs btn-icon btn-outline-danger btn-hapus-row-keluarga" data-nip="${item.NIP}" data-kdhub="${item.KDHUBKEL}" data-nama="${item.NMKEL}" title="Hapus Data">
                                                                                                        <i class="icon-base bx bx-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    `;
                        });

                        $tbody.html(html);
                    },
                    error: function (xhr) {
                        const errMsg = xhr.responseJSON?.message || 'Gagal memuat data keluarga dari database kedua.';
                        $tbody.html(`
                                                                                        <tr>
                                                                                            <td colspan="6" class="text-center py-4 text-danger">
                                                                                                <i class="icon-base bx bx-error-circle fs-3 d-block mb-1"></i>
                                                                                                <strong>Koneksi Database Kedua:</strong><br>
                                                                                                <small>${errMsg}</small>
                                                                                            </td>
                                                                                        </tr>
                                                                                    `);
                        $count.text('0 Anggota');
                    }
                });
            }

            // Buka Modal Data Keluarga dari Tombol Aksi Dokumen
            $(document).on('click', '.btn-data-keluarga', function () {
                const dokId = $(this).data('dok-id');
                const pgwId = $(this).data('pgw-id');
                const nip = $(this).data('nip') || '-';
                const nama = $(this).data('nama') || '- Tidak Ada Pegawai -';
                const skpd = $(this).data('skpd') || '-';

                $('#kel_dok_id').val(dokId);
                $('#kel_pgw_id').val(pgwId);
                $('#kel_nip').val(nip);
                $('#kel_pegawai_nip').val(nip);
                $('#kel_pegawai_nama').text(nama);
                $('#kel_pegawai_skpd').text(skpd);

                // Reset form ke mode tambah baru
                $('#btnBaruKeluarga').trigger('click');

                // Muat opsi hubungan & data keluarga untuk NIP bersangkutan
                loadHubkelOptions();
                loadKeluargaData(nip);

                keluargaModal.show();
            });

            // Tombol Cari / Refresh NIP di header kartu keluarga
            $('#btnCariNipPegawai').on('click', function () {
                const nip = $('#kel_pegawai_nip').val();
                loadKeluargaData(nip);
            });

            // Klik Baris Tabel Anggota Keluarga untuk Memilih/Edit Data
            $(document).on('click', '.table-row-keluarga', function (e) {
                // Hindari bentrok jika tombol hapus di dalam baris diklik
                if ($(e.target).closest('.btn-hapus-row-keluarga').length) return;

                $('.table-row-keluarga').removeClass('table-active');
                $(this).addClass('table-active');

                const idx = $(this).data('index');
                const item = currentKeluargaList[idx];
                if (!item) return;

                // Isi seluruh field formulir dengan data dari database kedua
                const cleanHubkel = item.KDHUBKEL ? String(item.KDHUBKEL).trim() : '';
                $('#kel_old_kdhubkel').val(cleanHubkel);
                SearchableSelect.setValue('#kel_hub_keluarga', cleanHubkel);
                $('#kel_gelar_depan').val(item.GLRDEPAN || '');
                $('#kel_nama').val(item.NMKEL || '');
                $('#kel_gelar_belakang').val(item.GLRBELAKANG || '');
                $('#kel_tgl_lahir').val(item.TGLLHR ? item.TGLLHR.substring(0, 10) : '');
                $('#kel_no_akta_lahir').val(item.noaktalahir || '');
                $('#kel_jenis_kelamin').val(item.KDJENKEL || '1');
                $('#kel_kdstawin').val(item.kdstawin || '1');
                $('#kel_pekerjaan').val(item.PEKERJAAN || '');
                $('#kel_nip_pasangan').val(item.NIPSUAMIISTRI || '');
                $('#kel_tgl_nikah').val(item.TGLNIKAH ? item.TGLNIKAH.substring(0, 10) : '');
                $('#kel_no_surat_nikah').val(item.nosuratnikah || '');
                $('#kel_tgl_cerai').val(item.TGLCERAI ? item.TGLCERAI.substring(0, 10) : '');
                $('#kel_tgl_wafat').val(item.TGLWAFAT ? item.TGLWAFAT.substring(0, 10) : '');
                $('#kel_no_surat_kematian').val(item.nosuratkematian || '');
                $('#kel_tgl_awal_sks').val(item.TGLSKS ? item.TGLSKS.substring(0, 10) : '');
                if (item.TGLSKS) {
                    $('#kel_tgl_akhir_sks').val(item.TATSKS ? item.TATSKS.substring(0, 10) : '').prop('disabled', true);
                } else {
                    $('#kel_tgl_akhir_sks').val('').prop('disabled', true);
                }
                $('#kel_no_surat_sks').val(item.NOSKS || '');

                // Reset dan isi field twafat jika ada (khusus pegawai KDHUBKEL = '00')
                $('#twafat_tglsurat').val('');
                $('#twafat_penerbit').val('');
                $('#twafat_jmlterusan').val('4');
                $('#twafat_tglstop').val('');
                $('#twafat_kdhubkelpenerima').val('');

                if (item.twafat) {
                    $('#twafat_tglsurat').val(item.twafat.TGLSURAT ? item.twafat.TGLSURAT.substring(0, 10) : '');
                    $('#twafat_penerbit').val(item.twafat.PENERBIT ? item.twafat.PENERBIT.trim() : '');
                    if (item.twafat.JMLTERUSAN !== null && item.twafat.JMLTERUSAN !== undefined) {
                        $('#twafat_jmlterusan').val(String(item.twafat.JMLTERUSAN));
                    }
                    $('#twafat_tglstop').val(item.twafat.TGLSTOP ? item.twafat.TGLSTOP.substring(0, 10) : '');
                    $('#twafat_kdhubkelpenerima').val(item.twafat.KDHUBKELPENERIMA ? item.twafat.KDHUBKELPENERIMA.trim() : '');
                }

                // Status Tunjangan (KDTUNJANG: 0 = TIDAK TERTUNJANG, 1 = TERTUNJANG, 2 = CALON TERTUNJANG, 3 = TERCATAT)
                if (item.TGLCERAI || item.TGLWAFAT) {
                    $('#status_tidak_tertunjang').prop('checked', true);
                } else if (item.KDTUNJANG == 1 || item.KDTUNJANG === '1') {
                    $('#status_tidak_tertunjang').prop('checked', true);
                } else if (item.KDTUNJANG == 2 || item.KDTUNJANG === '2') {
                    $('#status_tertunjang').prop('checked', true);
                } else if (item.KDTUNJANG == 3 || item.KDTUNJANG === '3') {
                    $('#status_tercatat').prop('checked', true);
                } else {
                    $('#status_tidak_tertunjang').prop('checked', true);
                }
                updateSmartSuratLabel();
                checkSectionTwafat();

                $('#badgeFormMode').text(`Mode: Edit Data (${item.NMKEL})`).removeClass('bg-label-primary').addClass('bg-label-warning');

                // Auto scroll modal body ke atas menuju form input
                const $modalBody = $('#modalDataKeluarga .modal-body');
                $modalBody.animate({
                    scrollTop: $('#formInputKeluarga').closest('.card').position().top + $modalBody.scrollTop() - 15
                }, 350);
            });

            // Tombol "Baru" untuk Reset Form Input
            $('#btnBaruKeluarga').on('click', function () {
                const nip = $('#kel_pegawai_nip').val();
                $('.table-row-keluarga').removeClass('table-active');
                $('#formInputKeluarga')[0].reset();
                $('#kel_old_kdhubkel').val('');
                $('#kel_nip').val(nip);
                $('#kel_no_akta_lahir').val('');
                $('#kel_no_surat_nikah').val('');
                $('#kel_no_surat_kematian').val('');
                $('#kel_no_surat_sks').val('');
                $('#twafat_tglsurat').val('');
                $('#twafat_penerbit').val('');
                $('#twafat_jmlterusan').val('4');
                $('#twafat_tglstop').val('');
                $('#twafat_kdhubkelpenerima').val('');
                $('#section_twafat_pegawai').addClass('d-none');
                $('#kel_kdstawin').val('1');
                $('#kel_jenis_kelamin').val('1');
                $('#kel_tgl_awal_sks').val('');
                $('#kel_tgl_akhir_sks').val('').prop('disabled', true);
                $('#status_tidak_tertunjang').prop('checked', true);
                $('#badgeFormMode').text('Mode: Tambah Baru').removeClass('bg-label-warning').addClass('bg-label-primary');
                SearchableSelect.reset('#kel_hub_keluarga');
                updateSmartSuratLabel();
                checkSectionTwafat();
                $('#kel_hub_keluarga').focus();
            });

            // Auto-fill Tanggal Akhir SKS (TATSKS) otomatis 1 tahun setelah Tanggal Awal SKS (TGLSKS) dan disabled
            $('#kel_tgl_awal_sks').on('change input', function () {
                const val = $(this).val();
                if (!val) {
                    $('#kel_tgl_akhir_sks').val('').prop('disabled', true);
                    return;
                }
                const parts = val.split('-');
                if (parts.length === 3) {
                    const y = parseInt(parts[0], 10) + 1;
                    const m = parseInt(parts[1], 10) - 1;
                    const d = parseInt(parts[2], 10);
                    const newDate = new Date(y, m, d);
                    const yyyy = newDate.getFullYear();
                    const mm = String(newDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(newDate.getDate()).padStart(2, '0');
                    $('#kel_tgl_akhir_sks').val(`${yyyy}-${mm}-${dd}`).prop('disabled', true);
                }
                updateSmartSuratLabel();
            });

            // Logika Otomatisasi Status Tunjangan Keluarga
            function updateSmartSuratLabel() {
                const tglWafat = $('#kel_tgl_wafat').val();
                const tglCerai = $('#kel_tgl_cerai').val();
                const tglSks = $('#kel_tgl_awal_sks').val();
                const hubkel = $('#kel_hub_keluarga').val();

                // Logika Otomatis Status Tunjangan:
                // 1. Jika Tanggal Cerai atau Wafat terisi -> Kunci ke Tidak Tertunjang
                // 2. Jika Hubungan adalah Anak:
                //    - Usia > 25 tahun -> Kunci ke Tidak Tertunjang
                //    - Usia >= 21 tahun tanpa TGLSKS -> Kunci ke Tidak Tertunjang (jika TGLSKS diisi, diizinkan s/d 25 tahun)
                // 3. Jika kuota tertunjang (KDTUNJANG = 2) dalam keluarga sudah mencapai 5 orang:
                //    - Untuk tambah baru: tidak boleh memilih tertunjang (kunci ke Tidak Tertunjang).
                //    - Untuk edit: jika item ini sebelumnya BUKAN tertunjang, kunci ke Tidak Tertunjang.
                const $radioTunjang = $('input[name="KDTUNJANG"]');
                const editingOldKd = ($('#kel_old_kdhubkel').val() || '').trim();

                // Hitung berapa anggota keluarga yang saat ini KDTUNJANG == 2 (selain item yang sedang diedit jika sedang edit)
                let totalTertunjangLain = 0;
                let totalAnakTertunjangLain = 0;
                if (currentKeluargaList && currentKeluargaList.length > 0) {
                    $.each(currentKeluargaList, function (_, k) {
                        const kKd = (k.KDHUBKEL ? String(k.KDHUBKEL).trim().toUpperCase() : '');
                        // Jika sedang edit, jangan hitung anggota yang sedang diedit
                        if (editingOldKd && kKd === editingOldKd) {
                            return;
                        }
                        if (String(k.KDTUNJANG) === '2') {
                            totalTertunjangLain++;
                            // Cek apakah anggota keluarga ini adalah anak (kandung, tiri, angkat: 11-19, 21-29, 31-39, 41-49, 1A-1G, dst)
                            if (/^[1-5][1-9A-G]$/.test(kKd)) {
                                totalAnakTertunjangLain++;
                            }
                        }
                    });
                }

                // Cek batasan usia untuk anak
                const cleanHubkel = (hubkel || '').trim().toUpperCase();
                const isAnak = /^[1-5][1-9A-G]$/.test(cleanHubkel);
                const tglLahirVal = $('#kel_tgl_lahir').val();
                let isUsiaAnakTerkunci = false;
                let pesanUsiaAnak = '';

                if (isAnak && tglLahirVal) {
                    const parts = tglLahirVal.split('-');
                    if (parts.length === 3) {
                        const y = parseInt(parts[0], 10);
                        const m = parseInt(parts[1], 10) - 1;
                        const d = parseInt(parts[2], 10);
                        const dob = new Date(y, m, d);
                        if (!isNaN(dob.getTime())) {
                            const today = new Date();
                            let age = today.getFullYear() - dob.getFullYear();
                            const monthDiff = today.getMonth() - dob.getMonth();
                            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                                age--;
                            }

                            if (age > 25) {
                                isUsiaAnakTerkunci = true;
                                pesanUsiaAnak = `Usia anak sudah melebihi 25 tahun (${age} tahun). Pilihan status tunjangan dikunci ke 1 - TIDAK TERTUNJANG.`;
                            } else if (age >= 21 && !tglSks) {
                                isUsiaAnakTerkunci = true;
                                pesanUsiaAnak = `Anak usia ${age} tahun (≥ 21 tahun) tidak dapat ditunjang kecuali memiliki Tanggal Surat Keterangan Kuliah (TGLSKS) aktif (maksimal s/d usia 25 tahun).`;
                            }
                        }
                    }
                }

                if (tglCerai || tglWafat) {
                    $('#status_tidak_tertunjang').prop('checked', true);
                    $radioTunjang.prop('disabled', true);
                    $('#kel_kdstawin').val('1'); // Otomatis status kawin menjadi 1 - Tidak Kawin
                    $('#keteranganTunjangLocked').removeClass('d-none');
                    $('#keteranganTunjangMaxAnak').addClass('d-none');
                    $('#keteranganTunjangMax5').addClass('d-none');
                    $('#keteranganTunjangUsiaAnak').addClass('d-none');
                } else if (isUsiaAnakTerkunci) {
                    $('#status_tidak_tertunjang').prop('checked', true);
                    $radioTunjang.prop('disabled', true);
                    $('#textTunjangUsiaAnak').text(pesanUsiaAnak);
                    $('#keteranganTunjangUsiaAnak').removeClass('d-none');
                    $('#keteranganTunjangLocked').addClass('d-none');
                    $('#keteranganTunjangMaxAnak').addClass('d-none');
                    $('#keteranganTunjangMax5').addClass('d-none');
                } else if (isAnak && totalAnakTertunjangLain >= 3) {
                    // Kuota maksimal 3 anak tertunjang sudah penuh
                    $('#status_tidak_tertunjang').prop('checked', true);
                    $radioTunjang.prop('disabled', true);
                    $('#keteranganTunjangMaxAnak').removeClass('d-none');
                    $('#keteranganTunjangLocked').addClass('d-none');
                    $('#keteranganTunjangMax5').addClass('d-none');
                    $('#keteranganTunjangUsiaAnak').addClass('d-none');
                } else if (totalTertunjangLain >= 5) {
                    // Kuota 5 sudah penuh dipakai oleh anggota lain
                    $('#status_tidak_tertunjang').prop('checked', true);
                    $radioTunjang.prop('disabled', true);
                    $('#keteranganTunjangLocked').addClass('d-none');
                    $('#keteranganTunjangMaxAnak').addClass('d-none');
                    $('#keteranganTunjangMax5').removeClass('d-none');
                    $('#keteranganTunjangUsiaAnak').addClass('d-none');
                } else {
                    $radioTunjang.prop('disabled', false);
                    $('#keteranganTunjangLocked').addClass('d-none');
                    $('#keteranganTunjangMaxAnak').addClass('d-none');
                    $('#keteranganTunjangMax5').addClass('d-none');
                    $('#keteranganTunjangUsiaAnak').addClass('d-none');
                }
            }

            // Logika Visibilitas dan Otomatisasi Tabel TWafat khusus Pegawai Wafat (KDHUBKEL = '00')
            function checkSectionTwafat() {
                const hubkel = ($('#kel_hub_keluarga').val() || '').trim();
                const tglWafat = $('#kel_tgl_wafat').val();

                if (hubkel === '00' && tglWafat) {
                    $('#section_twafat_pegawai').removeClass('d-none');
                    // Jika twafat_tglsurat belum diisi, otomatis samakan dengan tglWafat
                    if (!$('#twafat_tglsurat').val()) {
                        $('#twafat_tglsurat').val(tglWafat);
                    }
                    // Jika twafat_tglstop belum diisi, hitung otomatis
                    if (!$('#twafat_tglstop').val()) {
                        hitungOtomatisTglStop();
                    }
                } else {
                    $('#section_twafat_pegawai').addClass('d-none');
                }
            }

            // Otomatis hitung Tanggal Stop Gaji (TGLSTOP) berdasarkan Tanggal Wafat + Jumlah Bulan Terusan
            function hitungOtomatisTglStop() {
                const tglWafat = $('#kel_tgl_wafat').val();
                if (!tglWafat) return;

                const parts = tglWafat.split('-');
                if (parts.length !== 3) return;

                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10) - 1; // 0-based month
                const jmlTerusan = parseInt($('#twafat_jmlterusan').val() || '4', 10);

                // Tanggal 1 pada bulan (bulan wafat + 1 + jmlTerusan)
                const targetDate = new Date(y, m + 1 + jmlTerusan, 1);
                const targetY = targetDate.getFullYear();
                const targetM = String(targetDate.getMonth() + 1).padStart(2, '0');
                const targetD = '01';

                $('#twafat_tglstop').val(`${targetY}-${targetM}-${targetD}`);
            }

            // Event listener perubahan pada field tanggal terkait dan hubkel untuk update smart label & twafat
            $('#kel_tgl_wafat, #kel_tgl_cerai, #kel_hub_keluarga, #kel_tgl_lahir').on('change input', function () {
                updateSmartSuratLabel();
                checkSectionTwafat();
            });

            // Perubahan jumlah terusan otomatis menghitung ulang TGLSTOP
            $('#twafat_jmlterusan').on('change', function () {
                hitungOtomatisTglStop();
            });

            // Tombol "Cari NIP" Pasangan ASN (Search by Model Keluarga)
            $('#btnCariNipPasangan').on('click', function () {
                const nipPasangan = ($('#kel_nip_pasangan').val() || '').trim();
                if (!nipPasangan) {
                    showNotification('warning', 'Ketikkan NIP Istri / Suami yang ingin dicari terlebih dahulu.');
                    $('#kel_nip_pasangan').focus();
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mencari...');

                $.ajax({
                    url: `{{ url('keluarga/cari-pasangan') }}/${encodeURIComponent(nipPasangan)}`,
                    type: "GET",
                    success: function (res) {
                        if (res.success && res.data) {
                            const p = res.data;
                            showNotification('success', `Data pasangan ditemukan di Model Keluarga: ${p.nama_lengkap} (NIP: ${p.NIP})`);

                            // Otomatis bantu isi data ke form jika sedang mengisi data Istri / Suami (KDHUBKEL = 10)
                            const currentHub = $('#kel_hub_keluarga').val();
                            if (currentHub === '10' || !currentHub) {
                                if (currentHub !== '10') $('#kel_hub_keluarga').val('10');
                                if (!$('#kel_nama').val()) $('#kel_nama').val(p.NMKEL || '');
                                if (!$('#kel_gelar_depan').val()) $('#kel_gelar_depan').val(p.GLRDEPAN || '');
                                if (!$('#kel_gelar_belakang').val()) $('#kel_gelar_belakang').val(p.GLRBELAKANG || '');
                                if (p.KDJENKEL) $('#kel_jenis_kelamin').val(p.KDJENKEL);
                                $('#kel_kdstawin').val('2'); // Status kawin = 2 (Kawin)
                                if (p.TGLLHR && !$('#kel_tgl_lahir').val()) $('#kel_tgl_lahir').val(p.TGLLHR.substring(0, 10));
                                if (p.PEKERJAAN && !$('#kel_pekerjaan').val()) $('#kel_pekerjaan').val(p.PEKERJAAN);
                                if (p.TGLNIKAH && !$('#kel_tgl_nikah').val()) $('#kel_tgl_nikah').val(p.TGLNIKAH.substring(0, 10));
                                if (p.nosuratnikah && !$('#kel_no_surat_nikah').val()) $('#kel_no_surat_nikah').val(p.nosuratnikah);
                            }
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Data NIP pasangan tidak ditemukan di tabel keluarga.';
                        showNotification('warning', msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="icon-base bx bx-search me-1"></i> Cari NIP');
                    }
                });
            });

            // Tombol "Simpan" Data Keluarga ke Database Kedua
            $('#btnSimpanKeluarga').on('click', function () {
                const nip = $('#kel_nip').val();
                const kdhubkel = $('#kel_hub_keluarga').val();
                const nmkel = $('#kel_nama').val();

                if (!nip || nip === '-') {
                    showNotification('warning', 'NIP Pegawai tidak valid.');
                    return;
                }
                if (!kdhubkel) {
                    showNotification('warning', 'Pilih Hubungan Keluarga (KDHUBKEL) terlebih dahulu.');
                    $('#kel_hub_keluarga').focus();
                    return;
                }
                if (!nmkel) {
                    showNotification('warning', 'Nama Anggota Keluarga (NMKEL) wajib diisi.');
                    $('#kel_nama').focus();
                    return;
                }

                const $btn = $(this);
                const $spinner = $('#spinnerSimpanKeluarga');
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');

                // Pastikan input disabled (seperti kel_tgl_akhir_sks) tetap ikut ter-serialize
                const $disabledFields = $('#formInputKeluarga').find(':disabled');
                $disabledFields.prop('disabled', false);
                const formData = $('#formInputKeluarga').serialize();
                $disabledFields.prop('disabled', true);

                $.ajax({
                    url: "{{ route('keluarga.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        showNotification('success', res.message || 'Data anggota keluarga berhasil disimpan ke database kedua!');
                        loadKeluargaData(nip);
                        $('#btnBaruKeluarga').trigger('click');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan data keluarga ke database kedua.';
                        showNotification('danger', msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });

            // Tombol "Hapus" dari Toolbar Form
            $('#btnHapusKeluarga').on('click', function () {
                const nip = $('#kel_nip').val();
                const kdhubkel = $('#kel_hub_keluarga').val();
                const nmkel = $('#kel_nama').val();

                if (!nip || !kdhubkel || !nmkel) {
                    showNotification('warning', 'Pilih baris anggota keluarga yang ingin dihapus pada tabel di bawah terlebih dahulu.');
                    return;
                }

                if (!confirm(`Apakah Anda yakin ingin menghapus data anggota keluarga "${nmkel}" (Kode: ${kdhubkel}) dari database kedua?`)) {
                    return;
                }

                const $btn = $(this);
                const $spinner = $('#spinnerHapusKeluarga');
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');

                $.ajax({
                    url: `{{ url('keluarga') }}/${encodeURIComponent(nip)}/${encodeURIComponent(kdhubkel)}`,
                    type: "DELETE",
                    success: function (res) {
                        showNotification('success', res.message || 'Data anggota keluarga berhasil dihapus!');
                        loadKeluargaData(nip);
                        $('#btnBaruKeluarga').trigger('click');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menghapus data dari database kedua.';
                        showNotification('danger', msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });

            // Tombol Hapus per Baris di Tabel
            $(document).on('click', '.btn-hapus-row-keluarga', function (e) {
                e.stopPropagation();
                const nip = $(this).data('nip');
                const kdhub = $(this).data('kdhub');
                const nama = $(this).data('nama');

                if (!confirm(`Hapus data anggota keluarga "${nama}" (Kode: ${kdhub}) dari database kedua?`)) {
                    return;
                }

                $.ajax({
                    url: `{{ url('keluarga') }}/${encodeURIComponent(nip)}/${encodeURIComponent(kdhub)}`,
                    type: "DELETE",
                    success: function (res) {
                        showNotification('success', res.message || 'Data anggota keluarga berhasil dihapus!');
                        loadKeluargaData(nip);
                        $('#btnBaruKeluarga').trigger('click');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menghapus data.';
                        showNotification('danger', msg);
                    }
                });
            });
        });
    </script>
@endsection