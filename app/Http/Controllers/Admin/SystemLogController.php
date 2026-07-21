<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', 'in:'.implode(',', array_keys(SystemLog::eventOptions()))],
            'actor' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $logs = SystemLog::query()
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('actor_name', 'like', "%{$search}%")
                        ->orWhere('actor_email', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%");
                });
            })
            ->when($validated['event'] ?? null, fn ($query, string $event) => $query->where('event', $event))
            ->when($validated['actor'] ?? null, fn ($query, string $actor) => $query->where('actor_email', $actor))
            ->when($validated['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $actors = SystemLog::query()
            ->whereNotNull('actor_email')
            ->select(['actor_name', 'actor_email'])
            ->distinct()
            ->orderBy('actor_name')
            ->get();

        return view('admin.system-logs.index', [
            'logs' => $logs,
            'actors' => $actors,
            'eventOptions' => SystemLog::eventOptions(),
        ]);
    }

    public function show(SystemLog $systemLog): View
    {
        return view('admin.system-logs.show', compact('systemLog'));
    }

    public function destroy(SystemLog $systemLog): RedirectResponse
    {
        $systemLog->delete();

        return redirect()->route('admin.system-logs')->with('status', 'Jejak sistem berhasil dihapus.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:delete'],
            'system_log_ids' => ['required', 'array', 'min:1'],
            'system_log_ids.*' => ['integer', 'exists:system_logs,id'],
        ]);

        $deleted = SystemLog::query()
            ->whereIn('id', $validated['system_log_ids'])
            ->delete();

        return back()->with('status', "{$deleted} jejak sistem berhasil dihapus.");
    }
}
