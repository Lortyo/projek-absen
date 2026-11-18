<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiController extends Controller
{
    public function index(Request $request)
    {
        $periode = DB::table('periode')->orderByDesc('periode_mulai')->get();

        $tahunList = $periode->pluck('tahun_periode')->unique()->sortDesc()->values();

        if ($request->has('id_periode') && $request->id_periode) {
            $periodeSekarang = $periode->firstWhere('id_periode', $request->id_periode);
        } else {
            $periodeSekarang = $periode->first();
        }

        $tahunSekarang = $periodeSekarang ? $periodeSekarang->tahun_periode : ($tahunList->first() ?? now()->year);
        
        $idPeriode = $periodeSekarang->id_periode ?? null;
        $query = DB::table('tukang')
            ->leftJoin('transaksi_gaji', function ($join) use ($idPeriode) {
                $join->on('tukang.id_tukang', '=', 'transaksi_gaji.id_tukang')
                ->where('transaksi_gaji.id_periode','=', $idPeriode);
            })
            ->select(
                'tukang.id_tukang',
                'tukang.nama',
                'tukang.gaji',
                'transaksi_gaji.id_transaksi',
                'transaksi_gaji.absen',
                'transaksi_gaji.absen_2',
                'transaksi_gaji.bonbonan',
                'transaksi_gaji.bon',
                'transaksi_gaji.bon_lain',
                'transaksi_gaji.keterangan_bon_lain',
                'transaksi_gaji.bonus',
                'transaksi_gaji.hasil_akhir'
            )->orderBy('tukang.nama', 'asc');
        $rekapGaji = $query->get();

        return view('home', compact('rekapGaji', 'periode', 'tahunList', 'tahunSekarang', 'periodeSekarang'));
    }

    public function saveTransaksi(Request $request)
    {
        $request->validate([
            'id_tukang' => 'required|integer',
            'id_periode' => 'required|integer',
            'absen' => 'nullable|numeric|min:0',
            'absen_2' => 'nullable|numeric|min:0',
            'bonbonan' => 'nullable|numeric|min:0',
            'bon' => 'nullable|numeric|min:0',
            'bon_lain' => 'nullable|numeric|min:0',
            'keterangan_bon_lain' => 'nullable|string|max:255',
            'bonus' => 'nullable|numeric|min:0',
            'hasil_akhir' => 'nullable|numeric',
        ]);

        $tukang = DB::table('tukang')->where('id_tukang', $request->id_tukang)->first();
        $gaji = $tukang ? $tukang->gaji : 0;
        $totalAbsen = (float)($request->absen ?? 0) + (float)($request->absen_2 ?? 0);
        $totalGaji = $totalAbsen * $gaji;
        $totalPotongan = (int)($request->bonbonan ?? 0) + (int)($request->bon ?? 0) + (int)($request->bon_lain ?? 0);
        $hasilAkhirOtomatis = $totalGaji + (int)($request->bonus ?? 0) - $totalPotongan;

        $hasilAkhir = $request->filled('hasil_akhir') ? (int)$request->hasil_akhir : $hasilAkhirOtomatis;

        DB::table('transaksi_gaji')->updateOrInsert(
            [
                'id_tukang' => $request->id_tukang,
                'id_periode' => $request->id_periode,
            ],
            [
                'absen' => $request->absen,
                'absen_2' => $request->absen_2,
                'bonbonan' => $request->bonbonan,
                'bon' => $request->bon,
                'bon_lain' => $request->bon_lain,
                'keterangan_bon_lain' => $request->keterangan_bon_lain,
                'bonus' => $request->bonus,
                'hasil_akhir' => $hasilAkhir,
            ]
        );

        return redirect()->back()->with('success', 'Data absensi & transaksi berhasil disimpan');
    }
}