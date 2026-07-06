<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\Standard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StandardInstrumentController extends Controller
{
    public function index(Request $request): View
    {
        $standardQuery = Standard::query()->withCount('instruments')->orderBy('urutan')->orderBy('kode');

        if ($request->filled('standard_status')) {
            $standardQuery->where('is_active', $request->string('standard_status')->toString() === 'aktif');
        }

        $instrumentQuery = Instrument::query()->with('standard')->orderBy('standard_id')->orderBy('urutan')->orderBy('kode');

        if ($request->filled('instrument_standard_id')) {
            $instrumentQuery->where('standard_id', $request->integer('instrument_standard_id'));
        }

        if ($request->filled('instrument_status')) {
            $instrumentQuery->where('is_active', $request->string('instrument_status')->toString() === 'aktif');
        }

        return view('admin.standard-instruments.index', [
            'activeTab' => $request->query('tab', 'standards'),
            'standards' => $standardQuery->paginate(10, ['*'], 'standard_page')->withQueryString(),
            'instruments' => $instrumentQuery->paginate(10, ['*'], 'instrument_page')->withQueryString(),
            'standardOptions' => Standard::query()->orderBy('urutan')->orderBy('kode')->get(),
            'jenisJawabanOptions' => Instrument::jenisJawabanOptions(),
            'truncate' => fn (string $value): string => Str::limit($value, 80),
        ]);
    }
}
