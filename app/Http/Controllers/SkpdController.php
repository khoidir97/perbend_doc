<?php

namespace App\Http\Controllers;

use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkpdController extends Controller
{
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

      $totalRecords = Skpd::count();

      $query = Skpd::query();

      if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
          $q->where('opd_kode', 'like', "%{$searchValue}%")
            ->orWhere('opd_nama', 'like', "%{$searchValue}%");
        });
      }

      $filteredRecords = $query->count();

      // Ordering
      $orderColumnIndex = $request->input('order.0.column');
      $orderDir = $request->input('order.0.dir', 'asc');
      $columns = [
        0 => 'opd_id',
        1 => 'opd_kode',
        2 => 'opd_nama',
        3 => 'opd_id',
      ];

      $orderBy = $columns[$orderColumnIndex] ?? 'opd_kode';
      $skpds = $query->orderBy($orderBy, $orderDir)
        ->skip($start)
        ->take($length)
        ->get();

      $data = [];
      $no = $start + 1;
      foreach ($skpds as $skpd) {
        $kodeHtml = '<span class="badge bg-label-primary fs-6 fw-bold">' . e($skpd->opd_kode) . '</span>';
        $namaHtml = '<span class="fw-semibold text-heading">' . e($skpd->opd_nama) . '</span>';

        $actionsHtml = '
          <div class="d-inline-flex gap-1">
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-primary edit-skpd-btn"
              data-id="' . $skpd->opd_id . '"
              data-kode="' . e($skpd->opd_kode) . '"
              data-nama="' . e($skpd->opd_nama) . '"
              title="Edit SKPD">
              <i class="icon-base bx bx-edit-alt"></i>
            </button>
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-danger delete-skpd-btn"
              data-id="' . $skpd->opd_id . '"
              data-kode="' . e($skpd->opd_kode) . '"
              data-nama="' . e($skpd->opd_nama) . '"
              title="Hapus SKPD">
              <i class="icon-base bx bx-trash"></i>
            </button>
          </div>
        ';

        $data[] = [
          'no' => $no++,
          'opd_kode' => $kodeHtml,
          'opd_nama' => $namaHtml,
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

    return view('content.skpd.index');
  }

  /**
   * Store a newly created resource in storage (AJAX JSON)
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'opd_kode' => ['required', 'string', 'max:100', 'unique:skpd,opd_kode'],
      'opd_nama' => ['required', 'string', 'max:255'],
    ], [
      'opd_kode.required' => 'Kode SKPD/OPD wajib diisi.',
      'opd_kode.unique' => 'Kode SKPD/OPD sudah ada, gunakan kode lain.',
      'opd_nama.required' => 'Nama SKPD/OPD wajib diisi.',
    ]);

    $skpd = Skpd::create([
      'opd_kode' => $validated['opd_kode'],
      'opd_nama' => $validated['opd_nama'],
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'SKPD ' . $skpd->opd_nama . ' berhasil ditambahkan!',
      ]);
    }

    return redirect()->route('skpd.index')->with('success', 'SKPD berhasil ditambahkan.');
  }

  /**
   * Update the specified resource in storage (AJAX JSON)
   */
  public function update(Request $request, $id)
  {
    $skpd = Skpd::findOrFail($id);

    $validated = $request->validate([
      'opd_kode' => ['required', 'string', 'max:100', 'unique:skpd,opd_kode,' . $skpd->opd_id . ',opd_id'],
      'opd_nama' => ['required', 'string', 'max:255'],
    ], [
      'opd_kode.required' => 'Kode SKPD/OPD wajib diisi.',
      'opd_kode.unique' => 'Kode SKPD/OPD sudah ada, gunakan kode lain.',
      'opd_nama.required' => 'Nama SKPD/OPD wajib diisi.',
    ]);

    $skpd->update([
      'opd_kode' => $validated['opd_kode'],
      'opd_nama' => $validated['opd_nama'],
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Data SKPD ' . $skpd->opd_nama . ' berhasil diperbarui!',
      ]);
    }

    return redirect()->route('skpd.index')->with('success', 'Data SKPD berhasil diperbarui.');
  }

  /**
   * Remove the specified resource from storage (AJAX JSON)
   */
  public function destroy(Request $request, $id)
  {
    $skpd = Skpd::findOrFail($id);

    // Cek apakah ada user yang masih terhubung dengan SKPD ini
    if ($skpd->users()->exists()) {
      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'SKPD tidak dapat dihapus karena masih ada user yang terhubung ke SKPD ini.',
        ], 422);
      }
      return redirect()->route('skpd.index')->with('error', 'SKPD tidak dapat dihapus karena masih digunakan.');
    }

    $nama = $skpd->opd_nama;
    $skpd->delete();

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'SKPD ' . $nama . ' berhasil dihapus!',
      ]);
    }

    return redirect()->route('skpd.index')->with('success', 'SKPD berhasil dihapus.');
  }
}
