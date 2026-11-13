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

        $query = DB::table('transaksi_gaji')
            ->join('tukang', 'transaksi_gaji.id_tukang', '=', 'tukang.id_tukang')
            ->select('transaksi_gaji.*', 'tukang.nama');

        if ($periodeSekarang) {
            $query->where('transaksi_gaji.id_periode', $periodeSekarang->id_periode);
        }

        $rekapGaji = $query->get();

        return view('home', compact('rekapGaji', 'periode', 'tahunList', 'tahunSekarang', 'periodeSekarang'));
    }
}