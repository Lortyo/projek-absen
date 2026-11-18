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
            )->orderBy('tukang.id_tukang', 'asc');
        $rekapGaji = $query->get();

        return view('home', compact('rekapGaji', 'periode', 'tahunList', 'tahunSekarang', 'periodeSekarang'));
    }

    public function saveTransaksi(Request $request)
    {
        // Bersihkan tanda titik pemisah ribuan jika ada
        $request->merge([
            'gaji' => $request->filled('gaji') ? str_replace('.', '', $request->gaji) : null,
            'bonbonan' => $request->filled('bonbonan') ? str_replace('.', '', $request->bonbonan) : null,
            'bon' => $request->filled('bon') ? str_replace('.', '', $request->bon) : null,
            'bon_lain' => $request->filled('bon_lain') ? str_replace('.', '', $request->bon_lain) : null,
            'bonus' => $request->filled('bonus') ? str_replace('.', '', $request->bonus) : null,
            'hasil_akhir' => $request->filled('hasil_akhir') ? str_replace('.', '', $request->hasil_akhir) : null,
        ]);

        $request->validate([
            'id_tukang' => 'required|integer',
            'id_periode' => 'required|integer',
            'gaji' => 'nullable|numeric|min:0',
            'absen' => 'nullable|numeric|min:0',
            'absen_2' => 'nullable|numeric|min:0',
            'bonbonan' => 'nullable|numeric|min:0',
            'bon' => 'nullable|numeric|min:0',
            'bon_lain' => 'nullable|numeric|min:0',
            'keterangan_bon_lain' => 'nullable|string|max:255',
            'bonus' => 'nullable|numeric|min:0',
            'hasil_akhir' => 'nullable|numeric',
        ]);

        if ($request->filled('gaji')) {
            DB::table('tukang')->where('id_tukang', $request->id_tukang)->update([
                'gaji' => $request->gaji,
            ]);
        }

        $tukang = DB::table('tukang')->where('id_tukang', $request->id_tukang)->first();
        $gaji = $request->filled('gaji') ? (float)$request->gaji : ($tukang ? $tukang->gaji : 0);
        $totalAbsen = (float)($request->absen ?? 0) + (float)($request->absen_2 ?? 0);
        $totalGaji = $totalAbsen * $gaji;
        $totalPotongan = (int)($request->bonbonan ?? 0) + (int)($request->bon ?? 0) + (int)($request->bon_lain ?? 0);
        $hasilAkhir = $totalGaji + (int)($request->bonus ?? 0) - $totalPotongan;

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

    public function setAllBonbonan(Request $request)
    {
        $idPeriode = $request->input('id_periode');
        if (!$idPeriode) {
            return redirect()->back()->with('error', 'Periode tidak valid');
        }

        $nominalBonbonan = 1000000;
        $allTukang = DB::table('tukang')->get();

        foreach ($allTukang as $t) {
            $existing = DB::table('transaksi_gaji')
                ->where('id_tukang', $t->id_tukang)
                ->where('id_periode', $idPeriode)
                ->first();

            $absen = $existing ? ($existing->absen ?? 0) : 0;
            $absen2 = $existing ? ($existing->absen_2 ?? 0) : 0;
            $bon = $existing ? ($existing->bon ?? 0) : 0;
            $bonLain = $existing ? ($existing->bon_lain ?? 0) : 0;
            $bonus = $existing ? ($existing->bonus ?? 0) : 0;

            $totalAbsen = (float)$absen + (float)$absen2;
            $totalGaji = $totalAbsen * (float)$t->gaji;
            $totalPotongan = $nominalBonbonan + (int)$bon + (int)$bonLain;
            $hasilAkhir = $totalGaji + (int)$bonus - $totalPotongan;

            DB::table('transaksi_gaji')->updateOrInsert(
                [
                    'id_tukang' => $t->id_tukang,
                    'id_periode' => $idPeriode,
                ],
                [
                    'bonbonan' => $nominalBonbonan,
                    'hasil_akhir' => $hasilAkhir,
                ]
            );
        }

        return redirect()->back()->with('success', 'Semua bonbonan berhasil diatur menjadi Rp 1.000.000');
    }
}