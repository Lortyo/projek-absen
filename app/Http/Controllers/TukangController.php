<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TukangController extends Controller
{
    public function store(Request $request)
    {
        $items = $request->input('tukang');

        if (is_array($items)) {
            $dataToInsert = [];
            foreach ($items as $item) {
                $nama = trim($item['nama'] ?? '');
                $gajiRaw = trim($item['gaji'] ?? '');
                if ($nama !== '') {
                    $gaji = $gajiRaw !== '' ? (int)str_replace('.', '', $gajiRaw) : 0;
                    $dataToInsert[] = [
                        'nama' => $nama,
                        'gaji' => $gaji,
                    ];
                }
            }

            if (!empty($dataToInsert)) {
                DB::table('tukang')->insert($dataToInsert);
                return redirect()->back()->with('success', count($dataToInsert) . ' tukang berhasil ditambahkan');
            }

            return redirect()->back()->with('error', 'Masukkan minimal satu nama tukang');
        }

        // Fallback untuk single input
        $nama = trim($request->nama ?? '');
        $gaji = (int)str_replace('.', '', $request->gaji ?? '0');

        $request->validate([
            'nama' => 'required|string|max:255',
            'gaji' => 'required|numeric'
        ]);

        DB::table('tukang')->insert([
            'nama' => $nama,
            'gaji' => $gaji
        ]);

        return redirect()->back()->with('success', 'Tukang berhasil ditambahkan');
    }
}
