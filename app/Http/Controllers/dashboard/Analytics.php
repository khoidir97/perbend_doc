<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\Pegawai;
use App\Models\Satker;
use App\Models\Skpd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Analytics extends Controller
{
  public function index()
  {
    $currentUser = Auth::user();
    $isAdmin = ($currentUser->role == 0);

    // Query dasar dokumen aktif (tidak terhapus)
    $docQuery = Dokumen::whereNull('dok_deleted_at');

    // Jika bukan admin, sesuaikan dengan SKPD / Satker user yang login
    if (!$isAdmin) {
      $userKdSatker = trim($currentUser->kd_satker ?? '');
      $userKdSkpd = trim($currentUser->kd_skpd ?? '');

      if (!empty($userKdSatker)) {
        $docQuery->where('dok_kd_satker', $userKdSatker);
      } else {
        $docQuery->where('dok_skpd', $userKdSkpd);
      }
    }

    // Statistik Dokumen
    $totalDokumen = (clone $docQuery)->count();
    $totalVerif = (clone $docQuery)->where('dok_status', 1)->count();
    $totalBelumVerif = (clone $docQuery)->where('dok_status', 0)->count();
    $persenVerif = $totalDokumen > 0 ? round(($totalVerif / $totalDokumen) * 100) : 0;

    // Statistik Master Data
    if ($isAdmin) {
      $totalSkpd = Skpd::count();
      $totalSatker = Satker::whereNull('stk_deleted_at')->count();
      $totalPegawai = Pegawai::whereNull('pgw_deleted_at')->count();
      $totalUser = User::count();
    } else {
      $userKdSkpd = trim($currentUser->kd_skpd ?? '');
      $userKdSatker = trim($currentUser->kd_satker ?? '');

      $totalSkpd = !empty($userKdSkpd) ? 1 : 0;

      $satkerQ = Satker::whereNull('stk_deleted_at')->where('stk_kode_opd', $userKdSkpd);
      if (!empty($userKdSatker)) {
        $satkerQ->where('stk_kode_satker', $userKdSatker);
      }
      $totalSatker = $satkerQ->count();

      $pegawaiQ = Pegawai::whereNull('pgw_deleted_at')->where('pgw_kd_skpd', $userKdSkpd);
      if (!empty($userKdSatker)) {
        $pegawaiQ->where('pgw_kd_satker', $userKdSatker);
      }
      $totalPegawai = $pegawaiQ->count();

      $totalUser = User::where('kd_skpd', $userKdSkpd)->count();
    }

    // Dokumen Terbaru (Top 5)
    $recentDokumen = (clone $docQuery)
      ->with(['skpd', 'satker', 'pegawai'])
      ->orderBy('dok_created_at', 'desc')
      ->take(5)
      ->get();

    return view('content.dashboard.dashboards-analytics', compact(
      'currentUser',
      'isAdmin',
      'totalDokumen',
      'totalVerif',
      'totalBelumVerif',
      'persenVerif',
      'totalSkpd',
      'totalSatker',
      'totalPegawai',
      'totalUser',
      'recentDokumen'
    ));
  }
}
