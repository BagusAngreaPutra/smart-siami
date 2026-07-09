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
        $instrumentQuery = Instrument::query()->with('standard')->orderBy('standard_id')->orderBy('urutan')->orderBy('kode');

        if ($request->filled('instrument_standard_id')) {
            $instrumentQuery->where('standard_id', $request->integer('instrument_standard_id'));
        }

        if ($request->filled('instrument_status')) {
            $instrumentQuery->where('is_active', $request->string('instrument_status')->toString() === 'aktif');
        }

        if ($request->filled('accreditation_body')) {
            $instrumentQuery->where('accreditation_body', $request->string('accreditation_body')->toString());
        }

        return view('admin.standard-instruments.index', [
            'instruments' => $instrumentQuery->paginate(10, ['*'], 'instrument_page')->withQueryString(),
            'standardOptions' => Standard::query()->orderBy('urutan')->orderBy('kode')->get(),
            'accreditationBodyOptions' => Instrument::query()
                ->whereNotNull('accreditation_body')
                ->where('accreditation_body', '!=', '')
                ->distinct()
                ->orderBy('accreditation_body')
                ->pluck('accreditation_body'),
            'jenisJawabanOptions' => Instrument::jenisJawabanOptions(),
            'truncate' => fn (string $value): string => Str::limit($value, 80),
        ]);
    }
}
