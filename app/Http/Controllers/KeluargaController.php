<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Keluarga;
use App\Models\Hubkel;
use App\Models\HistorisJiwa;
use App\Models\PegawaiDB2;
use App\Models\KeluargaKeterangan;
use Carbon\Carbon;

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

            // Ambil data dokumen keterangan keluarga (nosuratnikah, noaktalahir, nosuratkematian)
            $keteranganList = KeluargaKeterangan::where('nip', $cleanNip)->get()->keyBy(function ($k) {
                return trim($k->kdhubkel);
            });

            // Format keterangan hubungan keluarga dan sertakan nomor dokumen pendukung
            $keluarga->transform(function ($item) use ($keteranganList) {
                $kd = trim($item->KDHUBKEL);
                $ket = $keteranganList->get($kd);
                $item->nosuratnikah = $ket ? $ket->nosuratnikah : null;
                $item->noaktalahir = $ket ? $ket->noaktalahir : null;
                $item->nosuratkematian = $ket ? $ket->nosuratkematian : null;

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
            'OLD_KDHUBKEL' => ['nullable', 'string', 'max:2'],
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
            'nosuratnikah' => ['nullable', 'string', 'max:100'],
            'noaktalahir' => ['nullable', 'string', 'max:100'],
            'nosuratkematian' => ['nullable', 'string', 'max:100'],
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
            $oldKdhubkel = !empty($validated['OLD_KDHUBKEL']) ? trim($validated['OLD_KDHUBKEL']) : null;

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
            // Jika dalam mode edit (ada OLD_KDHUBKEL), cek berdasarkan OLD_KDHUBKEL terlebih dahulu
            $targetQueryKd = $oldKdhubkel ?: $kdhubkel;
            $existing = Keluarga::where('NIP', $nip)
                ->where('KDHUBKEL', $targetQueryKd)
                ->first();

            // Jika tidak ditemukan dengan OLD_KDHUBKEL, coba cek dengan KDHUBKEL baru
            if (!$existing && $oldKdhubkel) {
                $existing = Keluarga::where('NIP', $nip)
                    ->where('KDHUBKEL', $kdhubkel)
                    ->first();
                $targetQueryKd = $kdhubkel;
            }

            // Jika Tanggal Cerai atau Tanggal Wafat terisi, pastikan KDTUNJANG otomatis 1 (Tidak Tertunjang)
            $kdTunjang = isset($validated['KDTUNJANG']) ? (int) $validated['KDTUNJANG'] : 1;
            if (!empty($validated['TGLCERAI']) || !empty($validated['TGLWAFAT'])) {
                $kdTunjang = 1;
            }

            // Validasi Usia Anak untuk Status Tertunjang:
            // Anak tidak boleh tertunjang jika usianya >= 21 tahun, kecuali TGLSKS terisi (maksimal sampai usia 25 tahun). Lebih dari 25 tahun tidak boleh tertunjang.
            $isAnak = (bool) preg_match('/^[1-5][1-9A-G]$/i', $kdhubkel);
            if ($isAnak && !empty($validated['TGLLHR'])) {
                $usia = Carbon::parse($validated['TGLLHR'])->age;
                if ($usia > 25) {
                    $kdTunjang = 1;
                } elseif ($usia >= 21 && empty($validated['TGLSKS'])) {
                    $kdTunjang = 1;
                }
            }

            // Validasi Maksimal 5 Tertunjang per Keluarga:
            // Jika user memilih Tertunjang (KDTUNJANG = 2), periksa apakah kuota 5 orang sudah penuh
            if ($kdTunjang === 2) {
                $countTertunjangLain = Keluarga::where('NIP', $nip)
                    ->where('KDTUNJANG', 2)
                    ->when($targetQueryKd, function ($q) use ($targetQueryKd) {
                        $q->where('KDHUBKEL', '!=', $targetQueryKd);
                    })
                    ->count();

                if ($countTertunjangLain >= 5) {
                    $kdTunjang = 1; // Kunci otomatis menjadi Tidak Tertunjang karena sudah mencapai batas 5 orang
                }
            }

            // Ambil data untuk tabel keluarga_keterangan
            $nosuratnikah = !empty($validated['nosuratnikah']) ? trim($validated['nosuratnikah']) : null;
            $noaktalahir = !empty($validated['noaktalahir']) ? trim($validated['noaktalahir']) : null;
            $nosuratkematian = !empty($validated['nosuratkematian']) ? trim($validated['nosuratkematian']) : null;

            // Hapus field non-keluarga dan OLD_KDHUBKEL dari payload tabel keluarga
            unset($validated['OLD_KDHUBKEL'], $validated['nosuratnikah'], $validated['noaktalahir'], $validated['nosuratkematian']);

            // Susun payload dengan aturan yang ditentukan pengguna
            $dataPayload = array_merge($validated, [
                'NIP' => $nip,
                'KDHUBKEL' => $kdhubkel,
                'kdstawin' => !empty($validated['kdstawin']) ? (int) $validated['kdstawin'] : 1,
                'KDJENKEL' => !empty($validated['KDJENKEL']) ? (int) $validated['KDJENKEL'] : 1,
                'KDTUNJANG' => $kdTunjang,
                'INPUTER' => 'aplikasi',
                'KDDATI1' => '04',
                'KDDATI2' => '11',
                'UPDSTAMP' => now(),
            ]);

            if ($existing) {
                Keluarga::where('NIP', $nip)
                    ->where('KDHUBKEL', $targetQueryKd)
                    ->update($dataPayload);

                $message = 'Data anggota keluarga (' . $validated['NMKEL'] . ') berhasil diperbarui di tabel keluarga!';
            } else {
                Keluarga::create($dataPayload);
                $message = 'Data anggota keluarga (' . $validated['NMKEL'] . ') berhasil disimpan ke tabel keluarga!';
            }

            // Simpan / Perbarui data ke tabel keluarga_keterangan (nosuratnikah, noaktalahir, nosuratkematian)
            $existingKet = KeluargaKeterangan::where('nip', $nip)
                ->where('kdhubkel', $targetQueryKd)
                ->first();

            if (!$existingKet && $oldKdhubkel) {
                $existingKet = KeluargaKeterangan::where('nip', $nip)
                    ->where('kdhubkel', $kdhubkel)
                    ->first();
                $targetKetKd = $kdhubkel;
            } else {
                $targetKetKd = $targetQueryKd;
            }

            $ketPayload = [
                'nip' => $nip,
                'kdhubkel' => $kdhubkel,
                'kddati1' => !empty($validated['KDDATI1']) ? $validated['KDDATI1'] : '04',
                'kddati2' => !empty($validated['KDDATI2']) ? $validated['KDDATI2'] : '11',
                'nosuratnikah' => $nosuratnikah,
                'noaktalahir' => $noaktalahir,
                'nosuratkematian' => $nosuratkematian,
                'updater' => 'aplikasi',
                'updstamp' => now(),
            ];

            if ($existingKet) {
                KeluargaKeterangan::where('nip', $nip)
                    ->where('kdhubkel', $targetKetKd)
                    ->update($ketPayload);
            } else {
                KeluargaKeterangan::create($ketPayload);
            }

            // Sync ke tabel historis_jiwa dan mstpegawai
            $this->syncHistorisJiwaDanMstPegawai($nip, $dataPayload);

            $dataPayload['nosuratnikah'] = $nosuratnikah;
            $dataPayload['noaktalahir'] = $noaktalahir;
            $dataPayload['nosuratkematian'] = $nosuratkematian;

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
     * Sinkronisasi data historis_jiwa (TMTGAJI bulan berikutnya) dan mstpegawai
     * Menghitung ulang JISTRI dan JANAK yang berstatus tertunjang (KDTUNJANG = 2)
     */
    protected function syncHistorisJiwaDanMstPegawai($nip, array $keluargaPayload)
    {
        try {
            $cleanNip = trim($nip);

            // TMTGAJI adalah tanggal 1 bulan berikutnya (misal Nov -> Des)
            $targetTmtGaji = Carbon::now()->addMonthNoOverflow()->startOfMonth()->toDateString();

            // Hitung jumlah ISTRI dan ANAK yang berstatus tertunjang (KDTUNJANG = 2)
            $keluargaList = Keluarga::where('NIP', $cleanNip)->get();

            $jIstri = 0;
            $jAnak = 0;

            foreach ($keluargaList as $k) {
                if ((int)$k->KDTUNJANG === 2) {
                    $hub = trim($k->KDHUBKEL);
                    // Istri / Suami (KDHUBKEL = 10, 20, 30, 40)
                    if (in_array($hub, ['10', '20', '30', '40'])) {
                        $jIstri++;
                    } 
                    // Anak (KDHUBKEL = 11-19, 21-29, 31-39, 41-49, atau anak tiri/angkat 1A-1G, 2A-2G, dst)
                    elseif (preg_match('/^[1-5][1-9A-G]$/', $hub)) {
                        $jAnak++;
                    }
                }
            }

            // 1. SINKRONISASI KE TABEL HISTORIS_JIWA
            $hist = HistorisJiwa::where('NIP', $cleanNip)
                ->where('TMTGAJI', $targetTmtGaji)
                ->first();

            if ($hist) {
                // Jika sudah ada, update JANAK, JISTRI, dan kdstawin
                HistorisJiwa::where('NIP', $cleanNip)
                    ->where('TMTGAJI', $targetTmtGaji)
                    ->update([
                        'JISTRI' => $jIstri,
                        'JANAK' => $jAnak,
                        'kdstawin' => $keluargaPayload['kdstawin'] ?? $hist->kdstawin ?? 1,
                        'TGLUPDATE' => Carbon::now(),
                    ]);
            } else {
                // Jika belum ada, lakukan insert untuk bulan berikutnya
                HistorisJiwa::create([
                    'TMTGAJI' => $targetTmtGaji,
                    'NIP' => $cleanNip,
                    'JISTRI' => $jIstri,
                    'JANAK' => $jAnak,
                    'KETERANGAN' => 'UPDATE APLIKASI',
                    'TGLUPDATE' => Carbon::now(),
                    'KDDATI1' => $keluargaPayload['KDDATI1'] ?? '04',
                    'KDDATI2' => $keluargaPayload['KDDATI2'] ?? '11',
                    'kdstawin' => $keluargaPayload['kdstawin'] ?? 1,
                    'TMT' => null,
                ]);
            }

            // 2. SINKRONISASI KE TABEL MSTPEGAWAI (PARAMETER HANYA NIP)
            // JANAK dan JISTRI selalu disinkronkan berdasarkan total tertunjang
            $updatePegawai = [
                'JISTRI' => $jIstri,
                'JANAK' => $jAnak,
                'UPDSTAMP' => Carbon::now(),
            ];

            // KUNCI DATA PEGAWAI:
            // Kolom identitas pegawai (KDJENKEL, KDSTAWIN, NAMA, GLRDEPAN, GLRBELAKANG, TGLLHR)
            // HANYA boleh di-update ke mstpegawai jika KDHUBKEL = '00' (data pegawai itu sendiri).
            // Jika mengedit data anak, istri/suami, dll (KDHUBKEL != '00'), data identitas pegawai di mstpegawai TIDAK boleh diubah.
            if (isset($keluargaPayload['KDHUBKEL']) && trim($keluargaPayload['KDHUBKEL']) === '00') {
                if (!empty($keluargaPayload['KDJENKEL'])) {
                    $updatePegawai['KDJENKEL'] = (int) $keluargaPayload['KDJENKEL'];
                }
                if (isset($keluargaPayload['kdstawin']) && $keluargaPayload['kdstawin'] !== '') {
                    $updatePegawai['KDSTAWIN'] = (int) $keluargaPayload['kdstawin'];
                }
                if (!empty($keluargaPayload['NMKEL'])) {
                    $updatePegawai['NAMA'] = $keluargaPayload['NMKEL'];
                }
                if (isset($keluargaPayload['GLRDEPAN'])) {
                    $updatePegawai['GLRDEPAN'] = $keluargaPayload['GLRDEPAN'];
                }
                if (isset($keluargaPayload['GLRBELAKANG'])) {
                    $updatePegawai['GLRBELAKANG'] = $keluargaPayload['GLRBELAKANG'];
                }
                if (!empty($keluargaPayload['TGLLHR'])) {
                    $updatePegawai['TGLLHR'] = $keluargaPayload['TGLLHR'];
                }
                if (!empty($keluargaPayload['KDDATI1'])) {
                    $updatePegawai['KDDATI1'] = $keluargaPayload['KDDATI1'];
                }
                if (!empty($keluargaPayload['KDDATI2'])) {
                    $updatePegawai['KDDATI2'] = $keluargaPayload['KDDATI2'];
                }
            }

            // Update ke tabel mstpegawai berdasarkan parameter NIP saja
            PegawaiDB2::where('NIP', $cleanNip)->update($updatePegawai);
        } catch (\Throwable $e) {
            // Log error tanpa membatalkan proses simpan keluarga
            \Log::error("Gagal sync historis_jiwa / mstpegawai untuk NIP {$nip}: " . $e->getMessage());
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
                // Hapus juga data keterangan terkait di tabel keluarga_keterangan
                KeluargaKeterangan::where('nip', $cleanNip)
                    ->where('kdhubkel', $cleanKd)
                    ->delete();

                // Sync ulang historis_jiwa dan mstpegawai setelah data keluarga dihapus
                $this->syncHistorisJiwaDanMstPegawai($cleanNip, ['KDDATI1' => '04', 'KDDATI2' => '11', 'kdstawin' => 1]);

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

                $ketPasangan = KeluargaKeterangan::where('nip', $cleanNip)->where('kdhubkel', $pasangan->KDHUBKEL)->first();

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
                        'nosuratnikah' => $ketPasangan ? $ketPasangan->nosuratnikah : null,
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
