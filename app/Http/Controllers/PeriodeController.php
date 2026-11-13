<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodeController extends Controller
{
    public function store(Request $request)
    {
        // Ambil periode terakhir
        $periodeAkhir = DB::table('periode')
            ->orderBy('periode_selesai', 'desc')
            ->first();

        if ($periodeAkhir) {
            // Periode mulai = hari setelah periode terakhir selesai
            $periodeMulai = Carbon::parse($periodeAkhir->periode_selesai)->addDay();
        } else {
            // Jika belum ada periode, mulai dari hari ini
            $periodeMulai = Carbon::now();
        }

        // Periode selesai = 14 hari dari periode mulai
        $periodeSelesai = $periodeMulai->copy()->addDays(13);

        // Ambil tahun dari periode mulai
        $tahunPeriode = $periodeMulai->year;

        DB::table('periode')->insert([
            'tahun_periode' => $tahunPeriode,
            'periode_mulai' => $periodeMulai->format('Y-m-d'),
            'periode_selesai' => $periodeSelesai->format('Y-m-d'),
        ]);

        return redirect()->back()->with('success', 'Periode baru berhasil dibuat');
    }

    public function storeTahun(Request $request)
    {
        // Ambil tahun terakhir
        $tahunTerakhir = DB::table('periode')
            ->orderBy('tahun_periode', 'desc')
            ->value('tahun_periode');

        // Tahun baru = tahun terakhir + 1, atau tahun sekarang jika belum ada
        $tahunBaru = $tahunTerakhir ? $tahunTerakhir + 1 : Carbon::now()->year;

        // Buat periode pertama untuk tahun baru (1 Januari - 14 Januari)
        $periodeMulai = Carbon::createFromDate($tahunBaru, 1, 1);
        $periodeSelesai = $periodeMulai->copy()->addDays(13);

        DB::table('periode')->insert([
            'tahun_periode' => $tahunBaru,
            'periode_mulai' => $periodeMulai->format('Y-m-d'),
            'periode_selesai' => $periodeSelesai->format('Y-m-d'),
        ]);

        return redirect()->back()->with('success', 'Tahun baru berhasil dibuat dengan periode pertama');
    }
}
