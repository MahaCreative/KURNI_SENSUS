<?php

namespace App\Http\Controllers;

use App\Models\DetailDusun;
use App\Models\Dusun;
use App\Models\Penduduk;
use App\Models\PengikutPindah;
use App\Models\Pindah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PindahController extends Controller
{
    public function index(Request $request)
    {
        $query = Pindah::query();
        $pindah = $query->with(['pengikut'])->paginate();
        $pindahMasuk = Pindah::where('kategori_pindah', 'masuk')->count();
        $pindahKeluar = Pindah::where('kategori_pindah', 'keluar')->count();

        $jumlahPendudukMasuk = $this->getPendudukMasukByMonth();
        $jumlahPendudukKeluar = $this->getPendudukKeluarByMonth();

        $permintaanDiterima = Pindah::where('menunggu_konfirmasi', '=', 'di terima')->count();
        $permintaanDitolak = Pindah::where('menunggu_konfirmasi', '=', 'di tolak')->count();
        $permintaanMenungguKonfirmasi = Pindah::where('menunggu_konfirmasi', '=', 'menunggu konfirmasi')->count();
        $jumlahPermintaan = [
            ["nama" => "Di Terima", 'jumlah' => $permintaanDiterima],
            ["nama" => "Di Tolak", 'jumlah' => $permintaanDitolak],
            ["nama" => "Menunggu Konfirmasi", 'jumlah' => $permintaanMenungguKonfirmasi],
        ];

        return inertia('Pindah/Index', compact(
            'pindah',
            'pindahMasuk',
            'pindahKeluar',
            'jumlahPendudukMasuk',
            'jumlahPendudukKeluar',
            'jumlahPermintaan',
        ));
    }
    public function formulirPindahKeluar(Request $request, $id)
    {
        $penduduk = Penduduk::with(['agama', 'pekerjaan', 'statusHubunganDalamKeluarga', 'detail_dusun' => function ($q) {
            $q->with('dusun');
        }])->findOrFail($request->id);
        return inertia('Pindah/FormulirPindahKeluar', compact('penduduk'));
    }
    public function storeKeluar(Request $request)
    {

        // $attr = $request->validate([
        //     'nik'                               => ['required', 'digits:16'],
        //     'kk'                                => ['required', 'digits:16'],
        //     'nama'                              => ['required', 'string', 'max:64'],
        //     'jenis_kelamin'                     => ['required', 'numeric'],
        //     'tempat_lahir'                      => ['required', 'string',],
        //     'tanggal_lahir'                     => ['required', 'date',],
        //     'agama_id'                          => ['required', 'numeric'],
        //     'pendidikan_id'                     => ['nullable', 'numeric'],
        //     'pekerjaan_id'                      => ['nullable', 'numeric'],
        //     'darah_id'                          => ['nullable', 'numeric'],
        //     'status_perkawinan_id'              => ['required', 'numeric'],
        //     'status_hubungan_dalam_keluarga_id' => ['required', 'numeric'],
        //     'detail_dusun_id' => ['required', 'numeric'],
        //     "desa_asal" => ['required'],
        //     "dusun_asal" => ['required'],
        //     "rt_asal" => ['required'],
        //     "rw_asal" => ['required'],
        //     "alamat_asal" => ['required'],
        //     "desa_tujuan" => ['required'],
        //     "dusun_tujuan" => ['required'],
        //     "rt_tujuan" => ['nullable', 'numeric'],
        //     "rw_tujuan" => ['nullable', 'numeric'],
        //     "alamat_tujuan" => ['required'],
        //     "tgl_pindah" => ['required'],
        //     "alasan_pindah" => ['required'],
        // ]);
        $pinda = Pindah::create([
            'nik' => $request->nik,
            'kk' => $request->kk,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'agama_id' => $request->agama_id,
            'pendidikan_id' => $request->pendidikan_id,
            'pekerjaan_id' => $request->pekerjaan_id,
            'darah_id' => $request->darah_id,
            'status_perkawinan_id' => $request->status_perkawinan_id,
            'status_hubungan_dalam_keluarga_id' => $request->status_hubungan_dalam_keluarga_id,
            "desa_asal" => $request->desa_asal,
            "dusun_asal" => $request->dusun_asal,
            "rt_asal" => $request->rt_asal,
            "rw_asal" => $request->rw_asal,
            "alamat_asal" => $request->alamat_asal,
            "desa_tujuan" => $request->desa_tujuan,
            "dusun_tujuan" => $request->dusun_tujuan,
            "rt_tujuan" => $request->rt_tujuan,
            "rw_tujuan" => $request->rw_tujuan,
            "alamat_tujuan" => $request->alamat_tujuan,
            "tgl_pindah" => $request->tgl_pindah,
            "alasan_pindah" => $request->alasan_pindah,
            'kategori_pindah' => 'keluar',
            'menunggu_konfirmasi' => 'di terima'
        ]);
        if ($pinda) {
            if ($request->pengikut) {
                foreach ($request->pengikut as $pengikut) {
                    $pengikut = PengikutPindah::create([
                        'pindah_id' => $pinda->id,
                        'nik' => $pengikut['nik'],
                        'nama' => $pengikut['nama'],
                        'status_hubungan_dalam_keluarga_id' => $pengikut['status_hubungan_dalam_keluarga_id'],
                    ]);
                }
            }
        }
    }
    public function formulirPindahMasuk(Request $request)
    {
        return inertia('Pindah/FormulirPindahMasuk');
    }

    public function storemasuk(Request $request)
    {
        // dd($request->all());
        // $validator = Validator::make(
        //     $request->all(),
        //     [
        //         'nik' =>  ['required', 'digits:16', 'unique:pindahs,nik'],
        //         // 'kk' =>  ['required', 'digits:16', 'unique:pindahs,kk'],
        //         // 'dusun' =>  ['required'],
        //         // 'nama' =>  ['required'],
        //         // 'jenis_kelamin' =>  ['required'],
        //         // 'tempat_lahir' =>  ['required'],
        //         // 'tanggal_lahir' =>  ['required'],
        //         // 'agama_id' =>  ['required'],
        //         // 'pendidikan_id' =>  ['required'],
        //         // 'pekerjaan_id' =>  ['required'],
        //         // 'darah_id' =>  ['required'],
        //         // 'status_perkawinan_id' =>  ['required'],
        //         // 'status_hubungan_dalam_keluarga_id' =>  ['required'],
        //         // 'desa_asal' =>  ['required'],
        //         // 'dusun_asal' =>  ['required'],
        //         // 'rt_asal' =>  ['nullable', 'numeric'],
        //         // 'rw_asal' =>  ['nullable', 'numeric'],
        //         // 'alamat_asal' =>  ['required'],
        //         // 'desa_tujuan' =>  ['required'],
        //         // 'dusun_tujuan' =>  ['required'],
        //         // 'detail_dusun_id' =>  ['required'],
        //         // 'alamat_tujuan' =>  ['required'],
        //         // 'tgl_pindah' =>  ['required'],
        //         // 'alasan_pindah' =>  ['required'],
        //         // 'pengikut' =>  ['required'],
        //         // 'nik_pengikut' =>  ['required'],
        //         // 'nama_pengikut' =>  ['required'],
        //         // 'shdk_pengikut' =>  ['required'],
        //         // 'nama_shdk_pengikut' =>  ['required'],
        //     ]
        // );
        // if ($validator->fails()) {
        //     return redirect()->back()->with(['type' => 'error', 'message' => $validator->errors()->all()]);
        // }
        $tujuan = DetailDusun::findOrFail($request->detail_dusun_id);
        $pindah = Pindah::create([
            'nik' => $request->nik,
            'kk' => $request->kk,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'agama_id' => $request->agama_id,
            'pendidikan_id' => $request->pendidikan_id,
            'pekerjaan_id' => $request->pekerjaan_id,
            'darah_id' => $request->darah_id,
            'status_perkawinan_id' => $request->status_perkawinan_id,
            'status_hubungan_dalam_keluarga_id' => $request->status_hubungan_dalam_keluarga_id,
            "desa_asal" => $request->desa_asal,
            "dusun_asal" => $request->dusun_asal,
            "rt_asal" => $request->rt_asal,
            "rw_asal" => $request->rw_asal,
            "alamat_asal" => $request->alamat_asal,
            "desa_tujuan" => $request->desa_tujuan,
            "dusun_tujuan" => $request->dusun_tujuan,
            "rt_tujuan" => $tujuan->rt,
            "rw_tujuan" => $tujuan->rw,
            "alamat_tujuan" => $request->alamat_tujuan,
            "tgl_pindah" => $request->tgl_pindah,
            "alasan_pindah" => $request->alasan_pindah,
            'kategori_pindah' => 'masuk',
            'menunggu_konfirmasi' => 'di terima'
        ]);
        // if ($pindah) {
        if (count($request->pengikut) > 0) {
            foreach ($request->pengikut as $item) {
                $pengikut = PengikutPindah::create([
                    'pindah_id' => $pindah->id,
                    'nik' => $item['nik'],
                    'nama' => $item['nama'],
                    'status_hubungan_dalam_keluarga_id' => $item['shdk'],
                ]);
            }
        }
        return redirect()->route('pindah')->with(['type' => 'success', 'message' => 'Berhasil menambahkan data pindahan baru']);
        // }
    }

    public function delete(Request $request)
    {
        $pindah = Pindah::findOrFail($request->id);
        $nama = $pindah->nama;
        $pindah->delete();
        return redirect()->back()->with(['type' => 'success`', 'message' => 'Berhasil menghapus data perpindahan penduduk dengan nama ' . $nama]);
    }

    public function cetak_suket(Request $request, $id)
    {
        $pindah = Pindah::with(['pengikut' => function ($q) {
            $q->with('statusHubunganDalamKeluarga');
        }, 'pekerjaan', 'pendidikan', 'statusPerkawinan', 'agama'])->first();
        return inertia('Pindah/CetakSuket', compact('pindah'));
    }



    // grafik function
    function getPendudukMasukByMonth()
    {

        // Mendapatkan tanggal 1 tahun yang lalu dari sekarang
        $oneYearAgo = Carbon::now()->subYear()->startOfYear();

        // Array bulan sebagai referensi
        $bulanRef = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Query untuk menghitung jumlah penduduk masuk per bulan
        $pendudukMasuk = DB::table('pindahs')
            ->select(DB::raw("DATE_FORMAT(tgl_pindah, '%M') AS month"), DB::raw('COUNT(*) AS jumlah'))
            ->where('tgl_pindah', '>=', $oneYearAgo)
            ->where('kategori_pindah', 'masuk')
            ->groupBy(DB::raw("DATE_FORMAT(tgl_pindah, '%M')"))
            ->pluck('jumlah', 'month');

        foreach ($bulanRef as $bulan) {
            $result[] = [
                'month' => ucfirst(strtolower($bulan)), // Mengonversi nama bulan menjadi lowercase
                'total' => $pendudukMasuk[$bulan] ?? 0
            ];
        }

        return $result;
    }
    function getPendudukKeluarByMonth()
    {


        $oneYearAgo = Carbon::now()->subYear()->startOfYear();

        $bulanRef = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];


        $pendudukMasuk = DB::table('pindahs')
            ->select(DB::raw("DATE_FORMAT(tgl_pindah, '%M') AS month"), DB::raw('COUNT(*) AS jumlah'))
            ->where('tgl_pindah', '>=', $oneYearAgo)
            ->where('kategori_pindah', 'keluar')
            ->groupBy(DB::raw("DATE_FORMAT(tgl_pindah, '%M')"))
            ->pluck('jumlah', 'month');

        foreach ($bulanRef as $bulan) {
            $result[] = [
                'month' => ucfirst(strtolower($bulan)), // Mengonversi nama bulan menjadi lowercase
                'total' => $pendudukMasuk[$bulan] ?? 0
            ];
        }

        return $result;
    }
}