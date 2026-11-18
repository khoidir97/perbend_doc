@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Aplikasi Manajemen Dokumen Perbendaharaan')

@section('content')
    <div class="row">
        <!-- 1. Welcome Card -->
        <div class="col-12 col-xl-8 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="d-flex align-items-center row h-100">
                    <div class="col-sm-7">
                        <div class="card-body p-4">
                            <span class="badge bg-label-primary mb-2">
                                <i class="icon-base bx {{ $isAdmin ? 'bx-shield-quarter' : 'bx-user' }} me-1"></i>
                                {{ $isAdmin ? 'Administrator BPKAD' : 'Operator OPD' }}
                            </span>
                            <h4 class="card-title text-primary mb-2 fw-bold">Selamat Datang, {{ $currentUser->name }}!</h4>
                            <p class="text-muted mb-3">
                                @if ($isAdmin)
                                    Kelola master data SKPD, Satker, Pegawai, serta pantau dan verifikasi dokumen yang diunggah oleh setiap unit kerja.
                                @else
                                    Unit Kerja: <strong>{{ $currentUser->skpd->opd_nama ?? 'SKPD Anda' }}</strong>
                                    @if ($currentUser->satker)
                                        <br><span class="small text-muted"><i class="icon-base bx bx-buildings me-1"></i>Satker: {{ $currentUser->satker->stk_nama }}</span>
                                    @endif
                                    <br>Unggah dan pantau status kelengkapan berkas dokumen digital Anda secara berkala.
                                @endif
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('dokumen.index') }}" class="btn btn-primary btn-sm">
                                    <i class="icon-base bx bx-file me-1"></i> Buka Data Dokumen
                                </a>
                                @if ($isAdmin)
                                    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="icon-base bx bx-id-card me-1"></i> Data Pegawai
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-end pe-sm-4 d-none d-sm-block">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="160"
                            alt="Dashboard Illustration" class="img-fluid" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Ringkasan Verifikasi Dokumen Card -->
        <div class="col-12 col-xl-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0 fw-semibold">Progres Verifikasi</h5>
                            <span class="badge bg-label-info">{{ $persenVerif }}% Selesai</span>
                        </div>
                        <p class="text-muted small mb-3">Perbandingan dokumen yang telah diverifikasi dengan total dokumen aktif.</p>
                        
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persenVerif }}%;" 
                                aria-valuenow="{{ $persenVerif }}" aria-valuemin="0" aria-valuemisi="100"></div>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-4">
                            <span><i class="icon-base bx bxs-circle text-success me-1"></i>Sudah Verif: <strong>{{ number_format($totalVerif) }}</strong></span>
                            <span><i class="icon-base bx bxs-circle text-warning me-1"></i>Belum Verif: <strong>{{ number_format($totalBelumVerif) }}</strong></span>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded text-center">
                        <span class="text-muted small d-block">Total Dokumen yang Terdaftar</span>
                        <h3 class="text-primary mb-0 fw-bold">{{ number_format($totalDokumen) }} <span class="fs-6 text-muted fw-normal">Berkas PDF</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Stat Cards Row -->
    <div class="row mb-4">
        <!-- Card 1: Dokumen Belum Verifikasi -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base bx bx-time-five fs-3"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-warning">Perlu Verifikasi</span>
                    </div>
                    <span class="text-muted small d-block mb-1">Belum Verifikasi</span>
                    <h3 class="card-title mb-1 text-warning fw-bold">{{ number_format($totalBelumVerif) }}</h3>
                    <small class="text-muted">Menunggu pemeriksaan</small>
                </div>
            </div>
        </div>

        <!-- Card 2: Dokumen Terverifikasi -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-check-double fs-3"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-success">Valid</span>
                    </div>
                    <span class="text-muted small d-block mb-1">Sudah Verifikasi</span>
                    <h3 class="card-title mb-1 text-success fw-bold">{{ number_format($totalVerif) }}</h3>
                    <small class="text-muted">Telah disetujui BPKAD</small>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Pegawai -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-id-card fs-3"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-primary">ASN / Pegawai</span>
                    </div>
                    <span class="text-muted small d-block mb-1">Total Data Pegawai</span>
                    <h3 class="card-title mb-1 text-primary fw-bold">{{ number_format($totalPegawai) }}</h3>
                    <small class="text-muted">{{ $isAdmin ? 'Seluruh ASN terdaftar' : 'Pegawai di unit Anda' }}</small>
                </div>
            </div>
        </div>

        <!-- Card 4: SKPD / Satker -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-buildings fs-3"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-info">{{ $isAdmin ? 'Struktur OPD' : 'Satker' }}</span>
                    </div>
                    @if ($isAdmin)
                        <span class="text-muted small d-block mb-1">SKPD / Satker</span>
                        <h3 class="card-title mb-1 text-info fw-bold">{{ number_format($totalSkpd) }} <span class="fs-6 text-muted">/ {{ number_format($totalSatker) }} Satker</span></h3>
                        <small class="text-muted">{{ number_format($totalUser) }} Pengguna aktif</small>
                    @else
                        <span class="text-muted small d-block mb-1">Jumlah Satker Anda</span>
                        <h3 class="card-title mb-1 text-info fw-bold">{{ number_format($totalSatker) }}</h3>
                        <small class="text-muted">Unit kerja di bawah SKPD</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Dokumen Terbaru (Table Preview) -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold"><i class="icon-base bx bx-time me-2 text-primary"></i>Dokumen Terakhir Diunggah</h5>
                        <small class="text-muted">Menampilkan berkas dokumen PDF terbaru yang masuk ke dalam sistem</small>
                    </div>
                    <a href="{{ route('dokumen.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua Dokumen <i class="icon-base bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
                <div class="card-datatable table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>SKPD / Satker</th>
                                <th>Pegawai</th>
                                <th>Berkas Dokumen</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 150px;">Waktu Upload</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentDokumen as $idx => $dok)
                                @php
                                    $fileName = basename($dok->dok_file);
                                    $fileUrl = asset('storage/' . $dok->dok_file);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $dok->skpd->opd_nama ?? ('[' . $dok->dok_skpd . ']') }}</span>
                                            @if ($dok->satker)
                                                <small class="text-muted"><i class="icon-base bx bx-barcode me-1"></i>{{ trim($dok->satker->stk_nama) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($dok->pegawai)
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-heading">{{ $dok->pegawai->pgw_nama }}</span>
                                                <small class="text-muted">NIP: {{ $dok->pegawai->pgw_nip ?? '-' }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="icon-base bx bxs-file-pdf text-danger fs-3 me-2"></i>
                                            <div>
                                                <a href="{{ $fileUrl }}" target="_blank" class="fw-semibold text-primary text-break" title="Lihat PDF">
                                                    {{ $fileName }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($dok->dok_status == 1)
                                            <span class="badge bg-label-success"><i class="icon-base bx bx-check-double me-1"></i>Terverifikasi</span>
                                        @else
                                            <span class="badge bg-label-warning"><i class="icon-base bx bx-time-five me-1"></i>Belum Verif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="icon-base bx bx-calendar me-1"></i>
                                            {{ $dok->dok_created_at ? date('d/m/Y H:i', strtotime($dok->dok_created_at)) : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-icon btn-outline-primary" title="Buka File PDF">
                                            <i class="icon-base bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="icon-base bx bx-folder-open fs-2 d-block mb-1"></i>
                                        Belum ada dokumen yang diunggah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
