<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Keluarga;
use App\Models\Hubkel;

class KeluargaController extends Controller
{
    /**
     * Mengambil daftar referensi Hubungan Keluarga dari database kedua (hubkel_tbl)
     * Format kode 11 - 19 disesuaikan menjadi "ANAK KE 1" s/d "ANAK KE 9"
     */
    public function getHubkel()
    {
        try {
            $list = Hubkel::orderBy('KDHUBKEL', 'asc')->get();

            // Format khusus kode 11 - 19
            $list->transform(function ($item) {
                $kd = trim($item->KDHUBKEL);
                if (is_numeric($kd) && (int)$kd >= 11 && (int)$kd <= 19) {
                    $anakKe = (int) substr($kd, 1);
                    $item->NMHUBKEL_LABEL = "ANAK KE {$anakKe}";
                } else {
                    $item->NMHUBKEL_LABEL = $item->NMHUBKEL;
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $list
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat referensi hubungan keluarga dari database kedua: ' . $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Mengambil susunan anggota keluarga berdasarkan NIP dari database kedua (tabel keluarga)
     */
    public function getByNip($nip)
    {
        $cleanNip = trim($nip);

        try {
            $keluarga = Keluarga::with('hubkel')
                ->where('NIP', $cleanNip)
                ->orderBy('KDHUBKEL', 'asc')
                ->get();

            // Format keterangan hubungan keluarga untuk 11 - 19
            $keluarga->transform(function ($item) {
                $kd = trim($item->KDHUBKEL);
                if (is_numeric($kd) && (int)$kd >= 11 && (int)$kd <= 19) {
                    $anakKe = (int) substr($kd, 1);
                    $item->label_hubkel = "ANAK KE {$anakKe}";
                } elseif ($item->hubkel) {
                    $item->label_hubkel = $item->hubkel->NMHUBKEL;
                } else {
                    $item->label_hubkel = ($kd == '00') ? 'DIRI SENDIRI' : (($kd == '10') ? 'ISTERI / SUAMI' : $kd);
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'nip' => $cleanNip,
                'count' => $keluarga->count(),
                'data' => $keluarga
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keluarga dari database kedua: ' . $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Menyimpan atau memperbarui data anggota keluarga ke database kedua
     * Sesuai ketentuan:
     * - INPUTER di-hardcode 'aplikasi'
     * - KDDATI1 di-hardcode '04'
     * - KDDATI2 di-hardcode '11'
     * - kdstawin: 1 = tidak kawin, 2 = kawin, 3 = janda/duda
     * - KDJENKEL: 1 = laki-laki, 2 = perempuan
     * - KDTUNJANG: 0 = TIDAK TERTUNJANG, 1 = TERTUNJANG, 2 = CALON TERTUNJANG, 3 = TERCATAT
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'NIP' => ['required', 'string', 'max:18'],
            'KDHUBKEL' => ['required', 'string', 'max:2'],
            'NMKEL' => ['required', 'string', 'max:50'],
            'GLRDEPAN' => ['nullable', 'string', 'max:20'],
            'GLRBELAKANG' => ['nullable', 'string', 'max:20'],
            'KDJENKEL' => ['nullable', 'integer'],
            'TGLLHR' => ['nullable', 'date'],
            'kdstawin' => ['nullable', 'integer'],
            'TGLNIKAH' => ['nullable', 'date'],
            'TGLCERAI' => ['nullable', 'date'],
            'TGLWAFAT' => ['nullable', 'date'],
            'TGLSKS' => ['nullable', 'date'],
            'TATSKS' => ['nullable', 'date'],
            'NOSKS' => ['nullable', 'string', 'max:50'],
            'KDTUNJANG' => ['nullable', 'integer'],
            'NIPSUAMIISTRI' => ['nullable', 'string', 'max:18'],
            'PEKERJAAN' => ['nullable', 'string', 'max:40'],
            'KDDATI1' => ['nullable', 'string', 'max:2'],
            'KDDATI2' => ['nullable', 'string', 'max:2'],
        ], [
            'NIP.required' => 'NIP Pegawai wajib diisi.',
            'KDHUBKEL.required' => 'Hubungan Keluarga wajib dipilih.',
            'NMKEL.required' => 'Nama Anggota Keluarga wajib diisi.',
        ]);

        try {
            $nip = trim($validated['NIP']);
            $kdhubkel = trim($validated['KDHUBKEL']);

            // Bersihkan field tanggal kosong menjadi null agar aman dari MySQL strict mode
            $dateFields = ['TGLLHR', 'TGLNIKAH', 'TGLCERAI', 'TGLWAFAT', 'TGLSKS', 'TATSKS'];
            foreach ($dateFields as $df) {
                if (empty($validated[$df])) {
                    $validated[$df] = null;
                }
            }

            // Jika Tanggal Awal SKS tidak diisi, pastikan keduanya null di database
            if (empty($validated['TGLSKS'])) {
                $validated['TGLSKS'] = null;
                $validated['TATSKS'] = null;
            } elseif (!empty($validated['TGLSKS']) && empty($validated['TATSKS'])) {
                // Jika TGLSKS diisi namun TATSKS belum ada, otomatis buat 1 tahun setelahnya
                $validated['TATSKS'] = date('Y-m-d', strtotime('+1 year', strtotime($validated['TGLSKS'])));
            }

            // Cek apakah data NIP + KDHUBKEL sudah ada di tabel keluarga
            $existing = Keluarga::where('NIP', $nip)
                ->where('KDHUBKEL', $kdhubkel)
                ->first();

            // Susun payload dengan aturan yang ditentukan pengguna
            $dataPayload = array_merge($validated, [
                'NIP' => $nip,
                'KDHUBKEL' => $kdhubkel,
                'kdstawin' => !empty($validated['kdstawin']) ? (int) $validated['kdstawin'] : 1,
                'KDJENKEL' => !empty($validated['KDJENKEL']) ? (int) $validated['KDJENKEL'] : 1,
                'KDTUNJANG' => isset($validated['KDTUNJANG']) ? (int) $validated['KDTUNJANG'] : 0,
                'INPUTER' => 'aplikasi',
                'KDDATI1' => '04',
                'KDDATI2' => '11',
                'UPDSTAMP' => now(),
            ]);

            if ($existing) {
                Keluarga::where('NIP', $nip)
                    ->where('KDHUBKEL', $kdhubkel)
                    ->update($dataPayload);

                $message = 'Data anggota keluarga (' . $validated['NMKEL'] . ') berhasil diperbarui di tabel keluarga!';
            } else {
                Keluarga::create($dataPayload);
                $message = 'Data anggota keluarga (' . $validated['NMKEL'] . ') berhasil disimpan ke tabel keluarga!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $dataPayload
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data ke tabel keluarga: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus data anggota keluarga dari database kedua
     */
    public function destroy(Request $request, $nip, $kdhubkel)
    {
        try {
            $cleanNip = trim($nip);
            $cleanKd = trim($kdhubkel);

            $deleted = Keluarga::where('NIP', $cleanNip)
                ->where('KDHUBKEL', $cleanKd)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data anggota keluarga berhasil dihapus dari tabel keluarga!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data anggota keluarga tidak ditemukan.'
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data dari tabel keluarga: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari data pasangan ASN berdasarkan NIP dari Model Keluarga (Database Kedua)
     */
    public function cariPasangan($nip)
    {
        $cleanNip = trim($nip);

        try {
            // 1. Cari data di Model Keluarga (Database Kedua), utamakan kepala keluarga (KDHUBKEL = '00')
            $pasangan = Keluarga::where('NIP', $cleanNip)
                ->where('KDHUBKEL', '00')
                ->first();

            // Jika tidak ada KDHUBKEL = '00', ambil record pertama NIP tersebut di tabel keluarga
            if (!$pasangan) {
                $pasangan = Keluarga::where('NIP', $cleanNip)->first();
            }

            if ($pasangan) {
                $gelarD = $pasangan->GLRDEPAN ? trim($pasangan->GLRDEPAN) . ' ' : '';
                $gelarB = $pasangan->GLRBELAKANG ? ', ' . trim($pasangan->GLRBELAKANG) : '';
                $namaLengkap = $gelarD . $pasangan->NMKEL . $gelarB;

                return response()->json([
                    'success' => true,
                    'source' => 'keluarga',
                    'data' => [
                        'NIP' => $pasangan->NIP,
                        'NMKEL' => $pasangan->NMKEL,
                        'GLRDEPAN' => $pasangan->GLRDEPAN,
                        'GLRBELAKANG' => $pasangan->GLRBELAKANG,
                        'nama_lengkap' => $namaLengkap,
                        'KDJENKEL' => $pasangan->KDJENKEL,
                        'PEKERJAAN' => $pasangan->PEKERJAAN,
                        'TGLLHR' => $pasangan->TGLLHR,
                        'TGLNIKAH' => $pasangan->TGLNIKAH,
                    ]
                ]);
            }

            // 2. Fallback: Cari di Model Pegawai (Database Pertama) jika di tabel keluarga belum ada
            $pegawai = \App\Models\Pegawai::where('pgw_nip', $cleanNip)->first();
            if ($pegawai) {
                return response()->json([
                    'success' => true,
                    'source' => 'pegawai',
                    'data' => [
                        'NIP' => $pegawai->pgw_nip,
                        'NMKEL' => $pegawai->pgw_nama,
                        'GLRDEPAN' => '',
                        'GLRBELAKANG' => '',
                        'nama_lengkap' => $pegawai->pgw_nama,
                        'KDJENKEL' => null,
                        'PEKERJAAN' => 'PNS',
                        'TGLLHR' => null,
                        'TGLNIKAH' => null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data pasangan dengan NIP ' . $cleanNip . ' tidak ditemukan di tabel keluarga.'
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari NIP pasangan: ' . $th->getMessage()
            ], 500);
        }
    }
}
