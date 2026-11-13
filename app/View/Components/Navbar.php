<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

class Navbar extends Component
{
    public $periode;
    public $tahunList;
    public $tahunSekarang;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->periode = DB::table('periode')->get();
        $this->tahunList = DB::table('periode')
            ->select('tahun_periode')
            ->distinct()
            ->orderBy('tahun_periode', 'desc')
            ->pluck('tahun_periode');
        $this->tahunSekarang = $this->tahunList->first() ?? date('Y');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.navbar');
    }
}
