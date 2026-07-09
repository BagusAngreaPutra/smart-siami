<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        Notification::archiveExpired();

        $query = Notification::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->latest();

        if ($request->query('status') === 'unread') {
            $query->unread();
        }

        if ($request->query('status') === 'read') {
            $query->where('is_read', true);
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'selectedStatus' => $request->query('status', 'all'),
        ]);
    }

    public function open(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        return redirect($notification->url_tujuan ?: route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('status', 'Semua notifikasi sudah ditandai dibaca.');
    }

    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }
}
