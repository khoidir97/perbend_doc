<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Satker;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
  /**
   * Mengambil daftar Satker berdasarkan SKPD (untuk AJAX Select2)
   */
  public function getSatkerBySkpd(Request $request)
  {
    if (Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }

    $kdSkpd = $request->input('kd_skpd');

    if (empty($kdSkpd)) {
      return response()->json([]);
    }

    $satkerList = Satker::where('stk_kode_opd', $kdSkpd)
      ->whereNull('stk_deleted_at')
      ->orderBy('stk_nama', 'asc')
      ->get(['stk_id', 'stk_kode_satker', 'stk_nama', 'stk_kode_opd'])
      ->map(function ($s) {
        return [
          'stk_id' => $s->stk_id,
          'stk_kode_satker' => trim($s->stk_kode_satker),
          'stk_nama' => trim($s->stk_nama),
        ];
      });

    return response()->json($satkerList);
  }

  /**
   * Display a listing of the resource (View or DataTables Server-Side JSON)
   */
  public function index(Request $request)
  {
    if (Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }

    // Jika request AJAX dari DataTables (server-side pagination)
    if ($request->ajax()) {
      $draw = intval($request->input('draw', 1));
      $start = intval($request->input('start', 0));
      $length = intval($request->input('length', 10));
      $searchValue = $request->input('search.value');

      $baseQuery = Pegawai::whereNull('pgw_deleted_at');
      $totalRecords = (clone $baseQuery)->count();

      $query = (clone $baseQuery)->with(['skpd', 'satker']);

      if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
          $q->where('pgw_nama', 'like', "%{$searchValue}%")
            ->orWhere('pgw_nip', 'like', "%{$searchValue}%")
            ->orWhere('pgw_kd_skpd', 'like', "%{$searchValue}%")
            ->orWhere('pgw_kd_satker', 'like', "%{$searchValue}%")
            ->orWhereHas('skpd', function ($sq) use ($searchValue) {
              $sq->where('opd_nama', 'like', "%{$searchValue}%")
                ->orWhere('opd_kode', 'like', "%{$searchValue}%");
            })
            ->orWhereHas('satker', function ($stkQ) use ($searchValue) {
              $stkQ->where('stk_nama', 'like', "%{$searchValue}%")
                ->orWhere('stk_kode_satker', 'like', "%{$searchValue}%");
            });
        });
      }

      $filteredRecords = $query->count();

      // Ordering
      $orderColumnIndex = $request->input('order.0.column');
      $orderDir = $request->input('order.0.dir', 'asc');
      $columns = [
        0 => 'pgw_id',
        1 => 'pgw_nip',
        2 => 'pgw_nama',
        3 => 'pgw_kd_skpd',
        4 => 'pgw_kd_satker',
        5 => 'pgw_id',
      ];

      $orderBy = $columns[$orderColumnIndex] ?? 'pgw_id';
      $pegawais = $query->orderBy($orderBy, $orderDir)
        ->skip($start)
        ->take($length)
        ->get();

      $data = [];
      $no = $start + 1;
      foreach ($pegawais as $pegawai) {
        $nipHtml = '<span class="badge bg-label-primary fs-6 fw-bold">' . e($pegawai->pgw_nip ?: '-') . '</span>';
        $namaHtml = '<span class="fw-semibold text-heading">' . e($pegawai->pgw_nama) . '</span>';

        $skpdNama = $pegawai->skpd ? $pegawai->skpd->opd_nama : '-';
        $skpdKode = $pegawai->pgw_kd_skpd ?: '-';
        $skpdHtml = '<div>
          <span class="badge bg-label-info me-1">' . e($skpdKode) . '</span>
          <span class="small">' . e($skpdNama) . '</span>
        </div>';

        // Tampilkan Nama Satker beserta kodenya
        $satkerKodeClean = trim($pegawai->pgw_kd_satker ?? '');
        $satkerNama = $pegawai->satker ? trim($pegawai->satker->stk_nama) : null;

        if ($satkerNama) {
          $satkerHtml = '<div>
            <span class="fw-semibold text-dark d-block">' . e($satkerNama) . '</span>
            <small class="text-muted"><i class="icon-base bx bx-barcode me-1"></i>' . e($satkerKodeClean) . '</small>
          </div>';
        } elseif (!empty($satkerKodeClean)) {
          $satkerHtml = '<span class="badge bg-label-secondary">' . e($satkerKodeClean) . '</span>';
        } else {
          $satkerHtml = '<span class="text-muted small">-</span>';
        }

        $actionsHtml = '
          <div class="d-inline-flex gap-1">
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-primary edit-pegawai-btn"
              data-id="' . $pegawai->pgw_id . '"
              data-nip="' . e($pegawai->pgw_nip) . '"
              data-nama="' . e($pegawai->pgw_nama) . '"
              data-kd_skpd="' . e($pegawai->pgw_kd_skpd) . '"
              data-kd_satker="' . e($satkerKodeClean) . '"
              title="Edit Pegawai">
              <i class="icon-base bx bx-edit-alt"></i>
            </button>
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-danger delete-pegawai-btn"
              data-id="' . $pegawai->pgw_id . '"
              data-nip="' . e($pegawai->pgw_nip) . '"
              data-nama="' . e($pegawai->pgw_nama) . '"
              title="Hapus Pegawai">
              <i class="icon-base bx bx-trash"></i>
            </button>
          </div>
        ';

        $data[] = [
          'no' => $no++,
          'pgw_nip' => $nipHtml,
          'pgw_nama' => $namaHtml,
          'skpd' => $skpdHtml,
          'satker' => $satkerHtml,
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

    return view('content.pegawai.index', compact('skpdList'));
  }

  /**
   * Store a newly created resource in storage (AJAX JSON)
   */
  public function store(Request $request)
  {
    if (Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }

    $validated = $request->validate([
      'pgw_nip' => ['required', 'string', 'max:255', 'unique:pegawai,pgw_nip'],
      'pgw_nama' => ['required', 'string', 'max:255'],
      'pgw_kd_skpd' => ['nullable', 'string', 'max:10'],
      'pgw_kd_satker' => ['nullable', 'string', 'max:50'],
    ], [
      'pgw_nip.required' => 'NIP Pegawai wajib diisi.',
      'pgw_nip.unique' => 'NIP sudah terdaftar, gunakan NIP lain.',
      'pgw_nama.required' => 'Nama Pegawai wajib diisi.',
    ]);

    $pegawai = Pegawai::create([
      'pgw_nip' => $validated['pgw_nip'],
      'pgw_nama' => $validated['pgw_nama'],
      'pgw_kd_skpd' => $validated['pgw_kd_skpd'] ?? null,
      'pgw_kd_satker' => $validated['pgw_kd_satker'] ?? null,
      'pgw_created_at' => now(),
      'pgw_created_by' => Auth::id(),
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Pegawai ' . $pegawai->pgw_nama . ' berhasil ditambahkan!',
      ]);
    }

    return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
  }

  /**
   * Update the specified resource in storage (AJAX JSON)
   */
  public function update(Request $request, $id)
  {
    if (Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }

    $pegawai = Pegawai::findOrFail($id);

    $validated = $request->validate([
      'pgw_nip' => ['required', 'string', 'max:255', 'unique:pegawai,pgw_nip,' . $pegawai->pgw_id . ',pgw_id'],
      'pgw_nama' => ['required', 'string', 'max:255'],
      'pgw_kd_skpd' => ['nullable', 'string', 'max:10'],
      'pgw_kd_satker' => ['nullable', 'string', 'max:50'],
    ], [
      'pgw_nip.required' => 'NIP Pegawai wajib diisi.',
      'pgw_nip.unique' => 'NIP sudah terdaftar pada pegawai lain.',
      'pgw_nama.required' => 'Nama Pegawai wajib diisi.',
    ]);

    $pegawai->update([
      'pgw_nip' => $validated['pgw_nip'],
      'pgw_nama' => $validated['pgw_nama'],
      'pgw_kd_skpd' => $validated['pgw_kd_skpd'] ?? null,
      'pgw_kd_satker' => $validated['pgw_kd_satker'] ?? null,
      'pgw_updated_at' => now(),
      'pgw_updated_by' => Auth::id(),
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Data Pegawai ' . $pegawai->pgw_nama . ' berhasil diperbarui!',
      ]);
    }

    return redirect()->route('pegawai.index')->with('success', 'Data Pegawai berhasil diperbarui.');
  }

  /**
   * Remove the specified resource from storage (Soft delete / AJAX JSON)
   */
  public function destroy(Request $request, $id)
  {
    if (Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }

    $pegawai = Pegawai::findOrFail($id);
    $nama = $pegawai->pgw_nama;

    // Soft delete menggunakan kolom tracking pgw_deleted_at & pgw_deleted_by
    $pegawai->update([
      'pgw_deleted_at' => now(),
      'pgw_deleted_by' => Auth::id(),
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Pegawai ' . $nama . ' berhasil dihapus!',
      ]);
    }

    return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
  }
}
