<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DokumenController extends Controller
{
  /**
   * Display a listing of the resource (View or DataTables Server-Side JSON)
   */
  public function index(Request $request)
  {
    $currentUser = Auth::user();
    $isAdmin = ($currentUser->role == 0); // 0 = admin, 1 = opd

    // Jika request AJAX dari DataTables (server-side pagination)
    if ($request->ajax()) {
      $draw = intval($request->input('draw', 1));
      $start = intval($request->input('start', 0));
      $length = intval($request->input('length', 10));
      $searchValue = $request->input('search.value');

      $baseQuery = Dokumen::whereNull('dok_deleted_at');

      // Hak akses: jika bukan admin, batasi hanya dokumen milik SKPD user yang login
      if (!$isAdmin) {
        $userKdSkpd = $currentUser->kd_skpd ?? '';
        $baseQuery->where('dok_skpd', $userKdSkpd);
      }

      $totalRecords = (clone $baseQuery)->count();

      $query = (clone $baseQuery)->with(['skpd', 'userCreated']);

      // Filter Pencarian
      if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
          $q->where('dok_file', 'like', "%{$searchValue}%")
            ->orWhere('dok_skpd', 'like', "%{$searchValue}%")
            ->orWhereHas('skpd', function ($qSkpd) use ($searchValue) {
              $qSkpd->where('opd_nama', 'like', "%{$searchValue}%")
                ->orWhere('opd_kode', 'like', "%{$searchValue}%");
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
        2 => 'dok_file',
        3 => 'dok_status',
        4 => 'dok_created_at',
        5 => 'dok_id',
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

        // 2. Kolom File PDF (Download / Preview)
        $fileUrl = asset('storage/' . $dok->dok_file);
        $fileName = basename($dok->dok_file);
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

        // 3. Kolom Status Verifikasi
        if ($dok->dok_status == 1) {
          $statusHtml = '<span class="badge bg-label-success"><i class="icon-base bx bx-check-double me-1"></i>Sudah Verifikasi</span>';
        } else {
          $statusHtml = '<span class="badge bg-label-warning"><i class="icon-base bx bx-time-five me-1"></i>Belum Verifikasi</span>';
        }

        // 4. Kolom Waktu Upload
        $waktuHtml = $dok->dok_created_at ? date('d/m/Y H:i', strtotime($dok->dok_created_at)) : '-';

        // 5. Tombol Aksi
        $actionsHtml = '<div class="d-inline-flex gap-1">';

        // Tombol Verifikasi (HANYA UNTUK ADMIN)
        if ($isAdmin) {
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

        // Tombol Edit Dokumen (Admin selalu bisa, atau OPD hanya jika belum diverifikasi)
        if ($isAdmin || $dok->dok_status == 0) {
          $actionsHtml .= '
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-primary edit-dokumen-btn"
              data-id="' . $dok->dok_id . '"
              data-skpd="' . e($dok->dok_skpd) . '"
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

    $skpdList = Skpd::orderBy('opd_nama', 'asc')->get();
    return view('content.dokumen.index', compact('skpdList', 'isAdmin', 'currentUser'));
  }

  /**
   * Store a newly created resource in storage (Upload PDF via AJAX)
   */
  public function store(Request $request)
  {
    $currentUser = Auth::user();
    $isAdmin = ($currentUser->role == 0);

    // Validasi input
    $rules = [
      'dok_file' => ['required', 'file', 'mimes:pdf', 'max:2048'], // Max 20MB PDF
    ];

    if ($isAdmin) {
      $rules['dok_skpd'] = ['required', 'string', 'max:100'];
    }

    $messages = [
      'dok_file.required' => 'File dokumen PDF wajib diunggah.',
      'dok_file.file' => 'Berkas yang diunggah harus berupa file.',
      'dok_file.mimes' => 'Format file harus berupa PDF (.pdf).',
      'dok_file.max' => 'Ukuran file PDF maksimal 2 MB.',
      'dok_skpd.required' => 'Silakan pilih SKPD pengunggah dokumen.',
    ];

    $validated = $request->validate($rules, $messages);

    // Tentukan SKPD
    $kdSkpd = $isAdmin ? $validated['dok_skpd'] : ($currentUser->kd_skpd ?? null);

    if (empty($kdSkpd)) {
      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Akun Anda belum memiliki kode SKPD. Silakan hubungi admin untuk melengkapi profil SKPD Anda.',
        ], 422);
      }
      return back()->with('error', 'Akun Anda belum memiliki kode SKPD.');
    }

    // Simpan file ke storage/app/public/dokumen_pdf
    $file = $request->file('dok_file');
    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
    $filePath = $file->storeAs('dokumen_pdf', $fileName, 'public');

    $dokumen = Dokumen::create([
      'dok_skpd' => $kdSkpd,
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
   * Update the specified resource in storage (Ganti File / SKPD via AJAX)
   */
  public function update(Request $request, $id)
  {
    $currentUser = Auth::user();
    $isAdmin = ($currentUser->role == 0);

    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    // Jika bukan admin, pastikan dokumen miliknya dan belum diverifikasi
    if (!$isAdmin) {
      if ($dokumen->dok_skpd !== $currentUser->kd_skpd) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat diubah lagi.'], 422);
      }
    }

    $rules = [
      'dok_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
    ];

    if ($isAdmin) {
      $rules['dok_skpd'] = ['required', 'string', 'max:100'];
    }

    $messages = [
      'dok_file.mimes' => 'Format file harus berupa PDF (.pdf).',
      'dok_file.max' => 'Ukuran file PDF maksimal 20 MB.',
      'dok_skpd.required' => 'SKPD wajib dipilih.',
    ];

    $validated = $request->validate($rules, $messages);

    $updateData = [
      'dok_updated_at' => now(),
      'dok_updated_by' => $currentUser->id,
    ];

    if ($isAdmin && isset($validated['dok_skpd'])) {
      $updateData['dok_skpd'] = $validated['dok_skpd'];
    }

    // Jika upload file baru
    if ($request->hasFile('dok_file')) {
      // Hapus file lama jika ada
      if ($dokumen->dok_file && Storage::disk('public')->exists($dokumen->dok_file)) {
        Storage::disk('public')->delete($dokumen->dok_file);
      }

      $file = $request->file('dok_file');
      $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
      $filePath = $file->storeAs('dokumen_pdf', $fileName, 'public');
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
    if ($currentUser->role != 0) {
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
    $isAdmin = ($currentUser->role == 0);

    $dokumen = Dokumen::whereNull('dok_deleted_at')->findOrFail($id);

    // Jika bukan admin, pastikan dokumen miliknya dan belum diverifikasi
    if (!$isAdmin) {
      if ($dokumen->dok_skpd !== $currentUser->kd_skpd) {
        return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
      }
      if ($dokumen->dok_status == 1) {
        return response()->json(['success' => false, 'message' => 'Dokumen sudah diverifikasi dan tidak dapat dihapus.'], 422);
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
}
