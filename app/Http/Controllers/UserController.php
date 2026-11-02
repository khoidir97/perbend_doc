<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  /**
   * Pastikan hanya role Admin yang dapat mengakses controller ini
   */
  public function __construct()
  {
    if (Auth::check() && Auth::user()->role != 0) {
      abort(403, 'Akses terbatas hanya untuk Administrator.');
    }
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

      $totalRecords = User::count();

      $query = User::with('skpd');

      if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
          $q->where('name', 'like', "%{$searchValue}%")
            ->orWhere('email', 'like', "%{$searchValue}%")
            ->orWhere('kd_skpd', 'like', "%{$searchValue}%")
            ->orWhereHas('skpd', function ($qSkpd) use ($searchValue) {
              $qSkpd->where('opd_nama', 'like', "%{$searchValue}%")
                    ->orWhere('opd_kode', 'like', "%{$searchValue}%");
            });
        });
      }

      // Filter Role jika ada
      if ($request->filled('role')) {
        $query->where('role', $request->input('role'));
      }

      $filteredRecords = $query->count();

      // Ordering
      $orderColumnIndex = $request->input('order.0.column');
      $orderDir = $request->input('order.0.dir', 'desc');
      $columns = [
        0 => 'id',
        1 => 'name',
        2 => 'email',
        3 => 'role',
        4 => 'kd_skpd',
        5 => 'id',
      ];

      $orderBy = $columns[$orderColumnIndex] ?? 'id';
      $users = $query->orderBy($orderBy, $orderDir)
                     ->skip($start)
                     ->take($length)
                     ->get();

      $currentAuthId = Auth::id();

      $data = [];
      $no = $start + 1;
      foreach ($users as $user) {
        // Kolom Nama dengan avatar
        $firstChar = strtoupper(substr($user->name, 0, 1));
        $isSelf = ($user->id === $currentAuthId) ? '<span class="badge bg-label-success ms-1 fs-tiny">Akun Anda</span>' : '';
        $nameHtml = '
          <div class="d-flex align-items-center">
            <div class="avatar avatar-sm me-3">
              <span class="avatar-initial rounded-circle bg-label-primary fw-bold">' . e($firstChar) . '</span>
            </div>
            <div>
              <span class="fw-semibold text-heading">' . e($user->name) . '</span>
              ' . $isSelf . '
            </div>
          </div>
        ';

        // Kolom Role badge
        if ($user->role == 0) {
          $roleHtml = '<span class="badge bg-label-primary"><i class="icon-base bx bx-shield-quarter me-1"></i>Admin</span>';
        } elseif ($user->role == 1) {
          $roleHtml = '<span class="badge bg-label-info"><i class="icon-base bx bx-user me-1"></i>OPD</span>';
        } else {
          $roleHtml = '<span class="badge bg-label-secondary">-</span>';
        }

        // Kolom SKPD
        if ($user->skpd) {
          $skpdHtml = '
            <div class="d-flex flex-column">
              <span class="fw-semibold text-dark text-wrap" style="max-width: 280px;">' . e($user->skpd->opd_nama) . '</span>
              <small class="text-muted"><i class="icon-base bx bx-buildings me-1"></i>Kode: ' . e($user->kd_skpd) . '</small>
            </div>
          ';
        } elseif ($user->kd_skpd) {
          $skpdHtml = '<span class="badge bg-label-warning">Kode: ' . e($user->kd_skpd) . '</span>';
        } else {
          $skpdHtml = '<span class="badge bg-label-secondary">Semua / Belum Diset</span>';
        }

        // Tombol Aksi
        $deleteBtn = '';
        if ($user->id !== $currentAuthId) {
          $deleteBtn = '
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-danger delete-user-btn"
              data-id="' . $user->id . '"
              data-name="' . e($user->name) . '"
              title="Hapus User">
              <i class="icon-base bx bx-trash"></i>
            </button>
          ';
        }

        $actionsHtml = '
          <div class="d-inline-flex gap-1">
            <button type="button" 
              class="btn btn-sm btn-icon btn-outline-primary edit-user-btn"
              data-id="' . $user->id . '"
              data-name="' . e($user->name) . '"
              data-email="' . e($user->email) . '"
              data-role="' . $user->role . '"
              data-kd_skpd="' . e($user->kd_skpd ?? '') . '"
              title="Edit User">
              <i class="icon-base bx bx-edit-alt"></i>
            </button>
            ' . $deleteBtn . '
          </div>
        ';

        $data[] = [
          'no' => $no++,
          'name' => $nameHtml,
          'email' => e($user->email),
          'role' => $roleHtml,
          'skpd' => $skpdHtml,
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
    return view('content.users.index', compact('skpdList'));
  }

  /**
   * Store a newly created resource in storage (AJAX JSON)
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255', 'unique:users,name'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:5'],
      'kd_skpd' => ['nullable', 'string', 'max:100'],
      'role' => ['required', 'in:0,1'],
    ], [
      'name.required' => 'Nama pengguna (username) wajib diisi.',
      'name.unique' => 'Nama pengguna sudah digunakan.',
      'email.required' => 'Email wajib diisi.',
      'email.email' => 'Format email tidak valid.',
      'email.unique' => 'Email sudah terdaftar.',
      'password.required' => 'Password wajib diisi.',
      'password.min' => 'Password minimal 5 karakter.',
      'role.required' => 'Role wajib dipilih.',
    ]);

    $user = User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
      'kd_skpd' => $validated['kd_skpd'] ?: null,
      'role' => $validated['role'],
    ]);

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'User ' . $user->name . ' berhasil ditambahkan!',
      ]);
    }

    return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
  }

  /**
   * Update the specified resource in storage (AJAX JSON)
   */
  public function update(Request $request, User $user)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255', 'unique:users,name,' . $user->id],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
      'password' => ['nullable', 'string', 'min:5'],
      'kd_skpd' => ['nullable', 'string', 'max:100'],
      'role' => ['required', 'in:0,1'],
    ], [
      'name.required' => 'Nama pengguna (username) wajib diisi.',
      'name.unique' => 'Nama pengguna sudah digunakan.',
      'email.required' => 'Email wajib diisi.',
      'email.email' => 'Format email tidak valid.',
      'email.unique' => 'Email sudah terdaftar.',
      'password.min' => 'Password minimal 5 karakter.',
      'role.required' => 'Role wajib dipilih.',
    ]);

    $user->name = $validated['name'];
    $user->email = $validated['email'];
    $user->kd_skpd = $validated['kd_skpd'] ?: null;
    $user->role = $validated['role'];

    if (!empty($validated['password'])) {
      $user->password = Hash::make($validated['password']);
    }

    $user->save();

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Data user ' . $user->name . ' berhasil diperbarui!',
      ]);
    }

    return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
  }

  /**
   * Remove the specified resource from storage (AJAX JSON)
   */
  public function destroy(Request $request, User $user)
  {
    if (Auth::id() === $user->id) {
      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
        ], 422);
      }
      return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    }

    $name = $user->name;
    $user->delete();

    if ($request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'User ' . $name . ' berhasil dihapus!',
      ]);
    }

    return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
  }
}
