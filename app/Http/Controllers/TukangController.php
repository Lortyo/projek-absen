<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TukangController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'gaji' => 'required|numeric'
        ]);

        DB::table('tukang')->insert([
            'nama' => $request->nama,
            'gaji' => $request->gaji
        ]);

        return redirect()->back()->with('success', 'Tukang berhasil ditambahkan');
    }
}
