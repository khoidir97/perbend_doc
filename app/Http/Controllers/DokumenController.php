<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Pegawai;
use App\Models\Satker;
use App\Models\Skpd;
use App\Models\Hubkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DokumenController extends Controller
{
  /**
   * Mengambil daftar pegawai berdasarkan SKPD (dan opsional Satker)
   */
  public function getPegawaiBySkpd(Request $request)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    // Pegawai hanya bisa memilih data dirinya sendiri
    if ($isPegawai) {
      return response()->json([
        [
          'pgw_id' => $currentUser->pgw_id,
          'pgw_nip' => $currentUser->pgw_nip,
          'pgw_nama' => $currentUser->pgw_nama,
        ]
      ]);
    }

    // Tentukan SKPD
    $kdSkpd = $isAdmin ? $request->input('kd_skpd') : ($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? null);

    if (empty($kdSkpd)) {
      return response()->json([]);
    }

    $query = Pegawai::where('pgw_kd_skpd', $kdSkpd)
      ->whereNull('pgw_deleted_at');

    if ($request->filled('kd_satker')) {
      $cleanSatker = trim($request->input('kd_satker'));
      $query->where(function ($q) use ($cleanSatker) {
        $q->where('pgw_kd_satker', $cleanSatker)
          ->orWhereRaw('TRIM(pgw_kd_satker) = ?', [$cleanSatker]);
      });
    }

    $pegawaiList = $query->orderBy('pgw_nama', 'asc')
      ->select('pgw_id', 'pgw_nip', 'pgw_nama')
      ->get();

    return response()->json($pegawaiList);
  }

  /**
   * Display a listing of the resource (View or DataTables Server-Side JSON)
   */
  public function index(Request $request)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1; // 0 = admin, 1 = pegawai, 2 = opd
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    // Jika request AJAX dari DataTables (server-side pagination)
    if ($request->ajax()) {
      $draw = intval($request->input('draw', 1));
      $start = intval($request->input('start', 0));
      $length = intval($request->input('length', 10));
      $searchValue = $request->input('search.value');

      $baseQuery = Dokumen::whereNull('dok_deleted_at');

      // Hak akses dokumen:
      // 0 = Admin: Akses semua dokumen
      // 1 = Pegawai: Hanya bisa melihat data dirinya sendiri (dok_pgw_id == pgw_id)
      // 2 = OPD: Bisa melihat pegawai sesuai OPD-nya (dok_skpd == pgw_kd_skpd)
      if ($isPegawai) {
        $baseQuery->where('dok_pgw_id', $currentUser->pgw_id);
      } elseif ($isOpd) {
        $userKdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');
        $userKdSatker = trim($currentUser->pgw_kd_satker ?? $currentUser->kd_satker ?? '');

        if (!empty($userKdSatker)) {
          $baseQuery->where('dok_kd_satker', $userKdSatker);
        } else {
          $baseQuery->where('dok_skpd', $userKdSkpd);
        }
      }

      $totalRecords = (clone $baseQuery)->count();

      $query = (clone $baseQuery)->with(['skpd', 'satker', 'pegawai', 'userCreated']);

      // Filter Pencarian
      if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
          $q->where('dok_file', 'like', "%{$searchValue}%")
            ->orWhere('dok_skpd', 'like', "%{$searchValue}%")
            ->orWhere('dok_kd_satker', 'like', "%{$searchValue}%")
            ->orWhereHas('skpd', function ($qSkpd) use ($searchValue) {
              $qSkpd->where('opd_nama', 'like', "%{$searchValue}%")
                ->orWhere('opd_kode', 'like', "%{$searchValue}%");
            })
            ->orWhereHas('satker', function ($qSatker) use ($searchValue) {
              $qSatker->where('stk_nama', 'like', "%{$searchValue}%")
                ->orWhere('stk_kode_satker', 'like', "%{$searchValue}%");
            })
            ->orWhereHas('pegawai', function ($qPgw) use ($searchValue) {
              $qPgw->where('pgw_nama', 'like', "%{$searchValue}%")
                ->orWhere('pgw_nip', 'like', "%{$searchValue}%");
            });
        });
      }

      // Filter Status jika dipilih
      if ($request->filled('status')) {
        $query->where('dok_status', $request->input('status'));
      }

      $filteredRecords = $query->count();

      // Ordering
      $orderColumnIndex = $request->input('order.0.column');
      $orderDir = $request->input('order.0.dir', 'desc');
      $columns = [
        0 => 'dok_id',
        1 => 'dok_skpd',
        3 => 'dok_pgw_id',
        4 => 'dok_file',
        5 => 'dok_status',
        6 => 'dok_created_at',
        7 => 'dok_id',
      ];

      $orderBy = $columns[$orderColumnIndex] ?? 'dok_id';
      $dokumens = $query->orderBy($orderBy, $orderDir)
        ->skip($start)
        ->take($length)
        ->get();

      $data = [];
      $no = $start + 1;
      foreach ($dokumens as $dok) {
        // 1. Kolom SKPD
        if ($dok->skpd) {
          $skpdHtml = '
            <div class="d-flex flex-column">
              <span class="fw-semibold text-dark text-wrap" style="max-width: 250px;">' . e($dok->skpd->opd_nama) . '</span>
              <small class="text-muted"><i class="icon-base bx bx-calendar me-1"></i>' . e($dok->dok_status == 1 ? date('d/m/Y H:i', strtotime($dok->dok_tanggal_verif)) : ' - ') . '</small>
            </div>
          ';
        } else {
          $skpdHtml = '<span class="badge bg-label-secondary">Kode: ' . e($dok->dok_skpd ?: '-') . '</span>';
        }

        // 2. Kolom Satker
        $satkerClean = trim($dok->dok_kd_satker ?? '');
        $satkerNama = $dok->satker ? trim($dok->satker->stk_nama) : null;
        if ($satkerNama) {
          $satkerHtml = '
            <div class="d-flex flex-column">
              <span class="fw-semibold text-dark text-wrap" style="max-width: 220px;">' . e($satkerNama) . '</span>
              <small class="text-muted"><i class="icon-base bx bx-barcode me-1"></i>' . e($satkerClean) . '</small>
            </div>
          ';
        } elseif (!empty($satkerClean)) {
          $satkerHtml = '<span class="badge bg-label-secondary">' . e($satkerClean) . '</span>';
        } else {
          $satkerHtml = '<span class="text-muted small">-</span>';
        }

        // 3. Kolom Pegawai
        if ($dok->pegawai) {
          $pegawaiHtml = '
            <div class="d-flex flex-column">
              <span class="fw-semibold text-heading">' . e($dok->pegawai->pgw_nama) . '</span>
              <small class="text-muted"><i class="icon-base bx bx-id-card me-1"></i>NIP: ' . e($dok->pegawai->pgw_nip ?: '-') . '</small>
            </div>
          ';
        } else {
          $pegawaiHtml = '<span class="text-muted fst-italic small">- Tidak Ada Pegawai -</span>';
        }

        // 4. Kolom File PDF (Download / Preview melalui endpoint terotorisasi)
        $fileUrl = route('dokumen.file', $dok->dok_id);
        $fileName = 'Dokumen_' . $dok->dok_id . '.pdf';
        $fileHtml = '
          <div class="d-flex align-items-center">
            <i class="icon-base bx bxs-file-pdf text-danger fs-3 me-2"></i>
            <div>
              <a href="' . $fileUrl . '" target="_blank" class="fw-semibold text-primary text-break" title="Lihat PDF">
                ' . e($fileName) . '
              </a>
              <br>
              <small class="text-muted"><a href="' . $fileUrl . '" target="_blank" rel="noopener noreferrer" class="text-secondary"><i class="icon-base bx bx-show me-1"></i>Lihat</a></small>
            </div>
          </div>
        ';

        // 5. Kolom Status Verifikasi
        if ($dok->dok_status == 1) {
          $statusHtml = '<span class="badge bg-label-success"><i class="icon-base bx bx-check-double me-1"></i>Sudah Verifikasi</span>';
        } else {
          $statusHtml = '<span class="badge bg-label-warning"><i class="icon-base bx bx-time-five me-1"></i>Belum Verifikasi</span>';
        }

        // 6. Kolom Waktu Upload
        $waktuHtml = $dok->dok_created_at ? date('d/m/Y H:i', strtotime($dok->dok_created_at)) : '-';

        // 7. Tombol Aksi
        $actionsHtml = '<div class="d-inline-flex gap-1">';

        // Tombol Verifikasi (HANYA UNTUK ADMIN)
        if ($isAdmin || $isOpd) {
          if ($dok->dok_status == 1) {
            $actionsHtml .= '
              <button type="button" 
                class="btn btn-sm btn-outline-warning btn-verify-status"
                data-id="' . $dok->dok_id . '"
                data-status="0"
                title="Batalkan Verifikasi (Ubah ke Belum Verifikasi)">
                <i class="icon-base bx bx-undo me-1"></i> Batal Verif
              </button>
            ';
          } else {
            $actionsHtml .= '
              <button type="button" 
                class="btn btn-sm btn-outline-success btn-verify-status"
                data-id="' . $dok->dok_id . '"
                data-status="1"
                title="Verifikasi Dokumen ini">
                <i class="icon-base bx bx-check-circle me-1"></i> Verifikasi
              </button>
            ';
          }
        }

        // Tombol Data Keluarga (Modal XL)
        $pgwNip = $dok->pegawai ? e($dok->pegawai->pgw_nip) : '';
        $pgwNama = $dok->pegawai ? e($dok->pegawai->pgw_nama) : '';
        $skpdNama = $dok->skpd ? e($dok->skpd->opd_nama) : ($dok->dok_skpd ?? '');

        $actionsHtml .= '
          <button type="button" 
            class="btn btn-sm btn-icon btn-outline-info btn-data-keluarga"
            data-dok-id="' . $dok->dok_id . '"
            data-pgw-id="' . e($dok->dok_pgw_id ?? '') . '"
            data-nip="' . $pgwNip . '"
            data-nama="' . $pgwNama . '"
            data-skpd="' . $skpdNama . '"
            title="Pemeliharaan Data Keluarga (KP4)">
            <i class="icon-base bx bx-group"></i>
          </button>
        ';

        // Tombol Edit Dokumen (Admin selalu bisa, atau OPD hanya jika belum diverifikasi)
        if ($isAdmin || $dok->dok_status == 0) {
          $actionsHtml .= '
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-primary edit-dokumen-btn"
              data-id="' . $dok->dok_id . '"
              data-skpd="' . e($dok->dok_skpd) . '"
              data-kd-satker="' . e($satkerClean) . '"
              data-pgw-id="' . e($dok->dok_pgw_id ?? '') . '"
              data-file="' . e($fileName) . '"
              data-action="' . route('dokumen.update', $dok->dok_id) . '"
              title="Edit Dokumen">
              <i class="icon-base bx bx-edit-alt"></i>
            </button>
          ';
        }

        // Tombol Hapus Dokumen
        if ($isAdmin || $dok->dok_status == 0) {
          $actionsHtml .= '
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-danger delete-dokumen-btn"
              data-id="' . $dok->dok_id . '"
              data-name="' . e($fileName) . '"
              data-action="' . route('dokumen.destroy', $dok->dok_id) . '"
              title="Hapus Dokumen">
              <i class="icon-base bx bx-trash"></i>
            </button>
          ';
        }

        $actionsHtml .= '</div>';

        $data[] = [
          'no' => $no++,
          'skpd' => $skpdHtml,
          // 'satker' => $satkerHtml,
          'pegawai' => $pegawaiHtml,
          'file' => $fileHtml,
          'status' => $statusHtml,
          'created_at' => $waktuHtml,
          'actions' => $actionsHtml,
        ];
      }

      return response()->json([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
      ]);
    }

    $skpdList = $isAdmin ? Skpd::orderBy('opd_nama', 'asc')->get() : collect([]);

    // Ambil daftar hubungan keluarga dari database kedua (hubkel_tbl)
    $hubkelList = collect([]);
    try {
      $hubkelList = Hubkel::orderBy('KDHUBKEL', 'asc')->get();
    } catch (\Throwable $th) {
      // Fallback diam jika database kedua belum siap/dikonfigurasi
    }

    if ($hubkelList->isEmpty()) {
      $hubkelList = collect([
        (object) ['KDHUBKEL' => '00', 'NMHUBKEL' => 'DIRI SENDIRI'],
        (object) ['KDHUBKEL' => '10', 'NMHUBKEL' => 'ISTERI / SUAMI'],
        (object) ['KDHUBKEL' => '11', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '12', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '13', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '14', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '15', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '16', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '17', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '18', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '19', 'NMHUBKEL' => 'ANAK'],
        (object) ['KDHUBKEL' => '1A', 'NMHUBKEL' => 'ANAK TIRI 1 (DARI ISTERI / SUAMI KESATU)'],
        (object) ['KDHUBKEL' => '1B', 'NMHUBKEL' => 'ANAK TIRI 2 (DARI ISTERI / SUAMI KESATU)'],
        (object) ['KDHUBKEL' => '1C', 'NMHUBKEL' => 'ANAK TIRI 3 (DARI ISTERI / SUAMI KESATU)'],
      ]);
    }

    return view('content.dokumen.index', compact('skpdList', 'isAdmin', 'isPegawai', 'isOpd', 'userRole', 'currentUser', 'hubkelList'));
  }

  /**
   * Store a newly created resource in storage (Upload PDF via AJAX)
   */
  public function store(Request $request)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    // Validasi input
    $rules = [
      'dok_file' => [
        'required',
        'file',
        'mimes:pdf',
        'mimetypes:application/pdf',
        'max:2048', // Max 2MB PDF
        function ($attribute, $value, $fail) {
          if ($value instanceof \Illuminate\Http\UploadedFile) {
            $origName = $value->getClientOriginalName();
            // Cegah double extension seperti file.pdf.php, file.php.pdf, dll.
            if (substr_count($origName, '.') !== 1) {
              return $fail('Nama file tidak valid. File tidak boleh memiliki ekstensi ganda (double extension).');
            }
            // Pastikan ekstensi file murni .pdf
            $ext = strtolower($value->getClientOriginalExtension());
            if ($ext !== 'pdf') {
              return $fail('Format file harus berupa PDF (.pdf).');
            }
            // Pastikan header file diawali magic bytes PDF (%PDF-)
            $path = $value->getRealPath();
            if ($path && file_exists($path)) {
              $handle = fopen($path, 'rb');
              $header = fread($handle, 5);
              fclose($handle);
              if ($header !== '%PDF-') {
                return $fail('Isi berkas bukan merupakan dokumen PDF yang valid.');
              }
            }
          }
        },
      ],
      'dok_kd_satker' => ['nullable', 'string', 'max:50'],
      'dok_pgw_id' => ['nullable', 'integer'],
    ];

    if ($isAdmin) {
      $rules['dok_skpd'] = ['required', 'string', 'max:100'];
    } elseif ($isOpd) {
      $rules['dok_pgw_id'] = ['required', 'integer'];
    }

    $messages = [
      'dok_file.required' => 'File dokumen PDF wajib diunggah.',
      'dok_file.file' => 'Berkas yang diunggah harus berupa file.',
      'dok_file.mimes' => 'Format file harus berupa PDF (.pdf).',
      'dok_file.mimetypes' => 'Tipe konten berkas harus berupa application/pdf.',
      'dok_file.max' => 'Ukuran file PDF maksimal 2 MB.',
      'dok_skpd.required' => 'Silakan pilih SKPD pengunggah dokumen.',
      'dok_pgw_id.required' => 'Silakan pilih pegawai yang bersangkutan.',
    ];

    $validated = $request->validate($rules, $messages);

    // Tentukan SKPD, Satker & Pegawai
    if ($isAdmin) {
      $kdSkpd = $validated['dok_skpd'];
      $kdSatker = $validated['dok_kd_satker'] ?? null;
      $dokPgwId = $validated['dok_pgw_id'] ?? null;
    } elseif ($isOpd) {
      $kdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');
      $kdSatker = !empty($validated['dok_kd_satker']) ? trim($validated['dok_kd_satker']) : null;
      $dokPgwId = (int) $validated['dok_pgw_id'];

      // Validasi pastikan pegawai yang dipilih terdaftar di SKPD OPD ini
      $pegawai = Pegawai::where('pgw_id', $dokPgwId)->where('pgw_kd_skpd', $kdSkpd)->first();
      if (!$pegawai) {
        return response()->json([
          'success' => false,
          'message' => 'Pegawai yang dipilih tidak terdaftar pada SKPD Anda.',
        ], 422);
      }
      if (empty($kdSatker) && !empty($pegawai->pgw_kd_satker)) {
        $kdSatker = trim($pegawai->pgw_kd_satker);
      }
    } else { // isPegawai
      $kdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');
      $kdSatker = trim($currentUser->pgw_kd_satker ?? $currentUser->kd_satker ?? '') ?: null;
      $dokPgwId = $currentUser->pgw_id;
    }

    if (empty($kdSkpd)) {
      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Akun Anda belum memiliki kode SKPD. Silakan hubungi admin untuk melengkapi profil SKPD Anda.',
        ], 422);
      }
      return back()->with('error', 'Akun Anda belum memiliki kode SKPD.');
    }

    // Simpan file ke disk privat (storage/app/private/dokumen_pdf) dengan nama acak kriptografis (UUID)
    $file = $request->file('dok_file');
    $fileName = Str::uuid()->toString() . '.pdf';
    $filePath = $file->storeAs('dokumen_pdf', $fileName, 'local');

    Dokumen::create([
      'dok_skpd' => $kdSkpd,
      'dok_kd_satker' => $kdSatker ?: null,
      'dok_pgw_id' => $dokPgwId ?: null,
      'dok_file' => $filePath,
      'dok_status' => 0, // Default 0 = Belum Verifikasi
      'dok_created_at' => now(),
      'dok_created_by' => $currentUser->id,
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Dokumen PDF berhasil diunggah dengan status Belum Verifikasi!',
      ]);
    }

    return redirect()->route('dokumen.index')->with('success', 'Dokumen PDF berhasil diunggah.');
  }

  /**
   * Update the specified resource in storage (Ganti File / SKPD / Satker / Pegawai via AJAX)
   */
  public function update(Request $request, $id)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    // Otorisasi hak akses update
    if ($isPegawai) {
      if ($dokumen->dok_pgw_id !== $currentUser->pgw_id) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat mengubah dokumen milik Anda sendiri.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat diubah lagi.'], 422);
      }
    } elseif ($isOpd) {
      $userKdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');
      if ($dokumen->dok_skpd !== $userKdSkpd) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat mengubah dokumen di lingkungan OPD Anda.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat diubah lagi.'], 422);
      }
    }

    $rules = [
      'dok_file' => [
        'nullable',
        'file',
        'mimes:pdf',
        'mimetypes:application/pdf',
        'max:2048', // Max 2MB PDF
        function ($attribute, $value, $fail) {
          if ($value instanceof \Illuminate\Http\UploadedFile) {
            $origName = $value->getClientOriginalName();
            // Cegah double extension seperti file.pdf.php, file.php.pdf, dll.
            if (substr_count($origName, '.') !== 1) {
              return $fail('Nama file tidak valid. File tidak boleh memiliki ekstensi ganda (double extension).');
            }
            // Pastikan ekstensi file murni .pdf
            $ext = strtolower($value->getClientOriginalExtension());
            if ($ext !== 'pdf') {
              return $fail('Format file harus berupa PDF (.pdf).');
            }
            // Pastikan header file diawali magic bytes PDF (%PDF-)
            $path = $value->getRealPath();
            if ($path && file_exists($path)) {
              $handle = fopen($path, 'rb');
              $header = fread($handle, 5);
              fclose($handle);
              if ($header !== '%PDF-') {
                return $fail('Isi berkas bukan merupakan dokumen PDF yang valid.');
              }
            }
          }
        },
      ],
      'dok_kd_satker' => ['nullable', 'string', 'max:50'],
      'dok_pgw_id' => ['nullable', 'integer'],
    ];

    if ($isAdmin) {
      $rules['dok_skpd'] = ['required', 'string', 'max:100'];
    } elseif ($isOpd) {
      $rules['dok_pgw_id'] = ['required', 'integer'];
    }

    $messages = [
      'dok_file.file' => 'Berkas yang diunggah harus berupa file.',
      'dok_file.mimes' => 'Format file harus berupa PDF (.pdf).',
      'dok_file.mimetypes' => 'Tipe konten berkas harus berupa application/pdf.',
      'dok_file.max' => 'Ukuran file PDF maksimal 2 MB.',
      'dok_skpd.required' => 'SKPD wajib dipilih.',
      'dok_pgw_id.required' => 'Pegawai wajib dipilih.',
    ];

    $validated = $request->validate($rules, $messages);

    if ($isAdmin) {
      $kdSatker = $validated['dok_kd_satker'] ?? null;
      $dokPgwId = $validated['dok_pgw_id'] ?? null;
    } elseif ($isOpd) {
      $kdSatker = !empty($validated['dok_kd_satker']) ? trim($validated['dok_kd_satker']) : null;
      $dokPgwId = (int) $validated['dok_pgw_id'];
      $userKdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');

      $pegawai = Pegawai::where('pgw_id', $dokPgwId)->where('pgw_kd_skpd', $userKdSkpd)->first();
      if (!$pegawai) {
        return response()->json([
          'success' => false,
          'message' => 'Pegawai yang dipilih tidak terdaftar pada SKPD Anda.',
        ], 422);
      }
      if (empty($kdSatker) && !empty($pegawai->pgw_kd_satker)) {
        $kdSatker = trim($pegawai->pgw_kd_satker);
      }
    } else { // isPegawai
      $kdSatker = trim($currentUser->pgw_kd_satker ?? $currentUser->kd_satker ?? '') ?: null;
      $dokPgwId = $currentUser->pgw_id;
    }

    $updateData = [
      'dok_kd_satker' => $kdSatker ?: null,
      'dok_pgw_id' => $dokPgwId ?: null,
      'dok_updated_at' => now(),
      'dok_updated_by' => $currentUser->id,
    ];

    if ($isAdmin && isset($validated['dok_skpd'])) {
      $updateData['dok_skpd'] = $validated['dok_skpd'];
    }

    // Jika upload file baru
    if ($request->hasFile('dok_file')) {
      // Hapus file lama dari disk privat (dan disk public jika masih ada sisa lama)
      if ($dokumen->dok_file) {
        if (Storage::disk('local')->exists($dokumen->dok_file)) {
          Storage::disk('local')->delete($dokumen->dok_file);
        }
        if (Storage::disk('public')->exists($dokumen->dok_file)) {
          Storage::disk('public')->delete($dokumen->dok_file);
        }
      }

      $file = $request->file('dok_file');
      $fileName = Str::uuid()->toString() . '.pdf';
      $filePath = $file->storeAs('dokumen_pdf', $fileName, 'local');
      $updateData['dok_file'] = $filePath;
    }

    $dokumen->update($updateData);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Data dokumen berhasil diperbarui!',
      ]);
    }

    return redirect()->route('dokumen.index')->with('success', 'Data dokumen berhasil diperbarui.');
  }

  /**
   * Mengubah status verifikasi dokumen (Khusus Admin: 0 = Belum Verifikasi, 1 = Sudah Verifikasi)
   */
  public function verifikasi(Request $request, $id)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    if ($userRole !== 0) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya Admin yang berhak memverifikasi dokumen.',
      ], 403);
    }

    $validated = $request->validate([
      'status' => ['required', 'in:0,1'],
    ]);

    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    $dokumen->update([
      'dok_status' => $validated['status'],
      'dok_tanggal_verif' => now(),
      'dok_verified_by' => $currentUser->id,
      'dok_updated_at' => now(),
      'dok_updated_by' => $currentUser->id,
    ]);

    $statusLabel = ($validated['status'] == 1) ? 'Sudah Verifikasi' : 'Belum Verifikasi';

    return response()->json([
      'success' => true,
      'message' => "Status dokumen berhasil diubah menjadi: {$statusLabel}!",
    ]);
  }

  /**
   * Remove the specified resource from storage (Soft delete / Hard delete)
   */
  public function destroy(Request $request, $id)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    // Otorisasi hak akses hapus
    if ($isPegawai) {
      if ($dokumen->dok_pgw_id !== $currentUser->pgw_id) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat menghapus dokumen milik Anda sendiri.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat dihapus.'], 422);
      }
    } elseif ($isOpd) {
      $userKdSkpd = trim($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? '');
      if ($dokumen->dok_skpd !== $userKdSkpd) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda hanya dapat menghapus dokumen di lingkungan OPD Anda.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat dihapus.'], 422);
      }
    }

    // Hapus file fisik dari storage agar tidak lagi dapat diakses setelah dokumen dihapus
    if ($dokumen->dok_file) {
      if (Storage::disk('local')->exists($dokumen->dok_file)) {
        Storage::disk('local')->delete($dokumen->dok_file);
      }
      if (Storage::disk('public')->exists($dokumen->dok_file)) {
        Storage::disk('public')->delete($dokumen->dok_file);
      }
    }

    // Tandai deleted_at & deleted_by
    $dokumen->update([
      'dok_deleted_at' => now(),
      'dok_deleted_by' => $currentUser->id,
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Dokumen berhasil dihapus!',
      ]);
    }

    return redirect()->route('dokumen.index')->with('success', 'Dokumen berhasil dihapus.');
  }

  /**
   * Menyajikan file PDF dari disk privat dengan pemeriksaan autentikasi & otorisasi
   */
  public function showFile($id)
  {
    $currentUser = Auth::user();
    $userRole = isset($currentUser->pgw_role) ? (int) $currentUser->pgw_role : 1;
    $isAdmin = ($userRole === 0);
    $isPegawai = ($userRole === 1);
    $isOpd = ($userRole === 2);

    // Dokumen yang sudah dihapus (dok_deleted_at != null) otomatis 404
    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    // Otorisasi akses file sesuai role
    if ($isPegawai) {
      if ((int) $dokumen->dok_pgw_id !== (int) $currentUser->pgw_id) {
        abort(403, 'Akses ditolak. Anda hanya dapat membuka dokumen milik Anda sendiri.');
      }
    } elseif ($isOpd) {
      $userKdSkpd = trim((string) ($currentUser->pgw_kd_skpd ?? $currentUser->kd_skpd ?? ''));
      $userKdSatker = trim((string) ($currentUser->pgw_kd_satker ?? $currentUser->kd_satker ?? ''));

      if (!empty($userKdSatker)) {
        if (trim((string) $dokumen->dok_kd_satker) !== $userKdSatker) {
          abort(403, 'Akses ditolak. Anda tidak memiliki izin membuka dokumen Satker lain.');
        }
      } elseif (trim((string) $dokumen->dok_skpd) !== $userKdSkpd) {
        abort(403, 'Akses ditolak. Anda tidak memiliki izin membuka dokumen OPD lain.');
      }
    } elseif (!$isAdmin) {
      abort(403, 'Akses ditolak.');
    }

    $relativePath = ltrim(str_replace('\\', '/', (string) $dokumen->dok_file), '/');
    if (empty($relativePath) || str_contains($relativePath, '..') || !str_starts_with($relativePath, 'dokumen_pdf/')) {
      abort(404, 'Berkas dokumen tidak valid.');
    }

    if (!Storage::disk('local')->exists($relativePath)) {
      abort(404, 'Berkas dokumen tidak ditemukan.');
    }

    $fullPath = Storage::disk('local')->path($relativePath);

    return response()->file($fullPath, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="dokumen-' . $dokumen->dok_id . '.pdf"',
      'X-Content-Type-Options' => 'nosniff',
      'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
      'Pragma' => 'no-cache',
    ]);
  }
}
