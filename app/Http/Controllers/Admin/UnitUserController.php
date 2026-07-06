<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitUserController extends Controller
{
    public function index(Request $request): View
    {
        $unitQuery = Unit::query()->orderBy('kode');

        if ($request->filled('unit_jenis_unit')) {
            $unitQuery->where('jenis_unit', $request->string('unit_jenis_unit')->toString());
        }

        if ($request->filled('unit_status')) {
            $unitQuery->where('is_active', $request->string('unit_status')->toString() === 'aktif');
        }

        $userQuery = User::query()->with('unit')->orderBy('name');

        if ($request->filled('user_role')) {
            $userQuery->where('role', $request->string('user_role')->toString());
        }

        if ($request->filled('user_status')) {
            $userQuery->where('is_active', $request->string('user_status')->toString() === 'aktif');
        }

        if ($request->filled('user_unit_id')) {
            $userQuery->where('unit_id', $request->integer('user_unit_id'));
        }

        return view('admin.unit-users.index', [
            'activeTab' => $request->query('tab', 'units'),
            'units' => $unitQuery->paginate(10, ['*'], 'unit_page')->withQueryString(),
            'users' => $userQuery->paginate(10, ['*'], 'user_page')->withQueryString(),
            'unitOptions' => Unit::query()->where('is_active', true)->orderBy('kode')->get(),
            'jenisUnitOptions' => Unit::jenisUnitOptions(),
            'roleOptions' => UserRole::cases(),
        ]);
    }
}
